<?php

namespace App\Services;

use App\Enums\IllustrationType;
use App\Models\Illustration;
use App\Models\IllustrationPrice;
use Illuminate\Support\Number;
use Illuminate\Support\Collection;

class IllustrationService
{
    public function getOrderDetail(Collection $illustrations): Collection
    {
        return $illustrations->map(function (Illustration $illustration) {
            $type = IllustrationType::tryFrom($illustration->type);

            $result = match ($type) {
                IllustrationType::BUST => $this->getBustDetails($illustration),
                IllustrationType::FULL_LENGTH => $this->getFullLengthDetails($illustration),
                IllustrationType::ANIMAL => $this->getAnimalDetails($illustration),
                default => []
            };

            $result['price'] = [
                'name' => 'Prix',
                'price' => Number::currency($illustration->price, 'EUR', locale: 'fr'),
            ];

            $result['pose'] = $this->getPose($illustration->pose);
            $result['background'] = $this->getBackground($illustration->background);

            if ($illustration->addTracking) {
                $result['addTracking'] = [
                    'name' => 'Demander le suivi',
                    'price' => Number::currency($this->getPrice('options_add_tracking'), 'EUR', locale: 'fr'),
                ];
            }

            if ($illustration->print) {
                $result['print'] = [
                    'name' => 'Imprimer l\'illustration',
                    'price' => Number::currency($this->getPrice('options_print'), 'EUR', locale: 'fr'),
                ];
            }

            return $result;
        });
    }

    private function getBustDetails(Illustration $illustration): array
    {
        $result = [
            'type' => [
                'name' => 'Buste',
                'price' => Number::currency($this->getPrice('bust_base'), 'EUR', locale: 'fr'),
            ]
        ];

        if ($illustration->nbHumans > 0) {
            $result['nbHumans'] = [
                'name' => "Personnes en plus ({$illustration->nbHumans})",
                'price' => Number::currency($this->getPrice('bust_add_human') * $illustration->nbHumans, 'EUR', locale: 'fr'),
            ];
        }

        if ($illustration->nbAnimals > 0) {
            $result['nbAnimals'] = [
                'name' => "Compagnons en plus ({$illustration->nbAnimals})",
                'price' => Number::currency($this->getPrice('bust_add_animal') * $illustration->nbAnimals, 'EUR', locale: 'fr'),
            ];
        }

        return $result;
    }

    private function getFullLengthDetails(Illustration $illustration): array
    {
        $result = [
            'type' => [
                'name' => 'Portrait en pied',
                'price' => Number::currency($this->getPrice('fl_base'), 'EUR', locale: 'fr'),
            ]
        ];

        if ($illustration->nbHumans > 0) {
            $result['nbHumans'] = [
                'name' => "Personnes en plus ({$illustration->nbHumans})",
                'price' => Number::currency($this->getPrice('fl_add_human') * $illustration->nbHumans, 'EUR', locale: 'fr'),
            ];
        }

        if ($illustration->nbAnimals > 0) {
            $result['nbAnimals'] = [
                'name' => "Compagnons en plus ({$illustration->nbAnimals})",
                'price' => Number::currency($this->getPrice('fl_add_animal') * $illustration->nbAnimals, 'EUR', locale: 'fr'),
            ];
        }

        return $result;
    }

    private function getAnimalDetails(Illustration $illustration): array
    {
        $result = [
            'type' => [
                'name' => 'Compagnon',
                'price' => Number::currency($this->getPrice('animal_base'), 'EUR', locale: 'fr'),
            ]
        ];

        if ($illustration->nbAnimals > 0) {
            $result['nbAnimals'] = [
                'name' => "Compagnons en plus ({$illustration->nbHumans})",
                'price' => Number::currency($this->getPrice('animal_add_one') * $illustration->nbHumans, 'EUR', locale: 'fr'),
            ];
        }

        if ($illustration->nbAnimals > 1) {
            $result['nbAnimalsToy'] = [
                'name' => "Jouets en plus ({$illustration->nbAnimals})",
                'price' => Number::currency($this->getPrice('animal_toy') * $illustration->nbAnimals, 'EUR', locale: 'fr'),
            ];
        }

        return $result;
    }

    private function getPose(string $pose): array
    {
        $key = match ($pose) {
            'SIMPLE' => 'option_pose_simple',
            'COMPLEX' => 'option_pose_complex',
            default => null,
        };

        if (!$key) {
            return [];
        }

        return [
            'name' => $pose === 'SIMPLE' ? 'Pose simple' : 'Pose complexe',
            'price' => Number::currency($this->getPrice($key), 'EUR', locale: 'fr'),
        ];
    }

    private function getBackground(string $background): array
    {
        $key = match ($background) {
            'GRADIENT' => 'option_bg_gradient',
            'SIMPLE' => 'option_bg_simple',
            'COMPLEX' => 'option_bg_complex',
            default => null,
        };

        if (!$key) {
            return [];
        }

        return [
            'name' => match ($background) {
                'GRADIENT' => 'Fond gradient / uni',
                'SIMPLE' => 'Fond simple',
                'COMPLEX' => 'Fond complexe',
                default => '',
            },
            'price' => Number::currency($this->getPrice($key), 'EUR', locale: 'fr'),
        ];
    }

    private function getPrice(string $key): float
    {
        return IllustrationPrice::where('key', $key)->first()->price;
    }
}
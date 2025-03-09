<?php

namespace App\Services;

use App\Enums\IllustrationType;
use App\Models\Illustration;
use App\Settings\IllustrationSettings;
use Illuminate\Support\Number;
use Illuminate\Support\Collection;

class IllustrationService
{
    public function getOrderDetail(Collection $illustrations): Collection
    {
        $settings = app(IllustrationSettings::class);

        return $illustrations->map(function (Illustration $illustration) use ($settings) {
            $type = IllustrationType::tryFrom($illustration->type);

            $result = match ($type) {
                IllustrationType::BUST => $this->getBustDetails($illustration, $settings),
                IllustrationType::FULL_LENGTH => $this->getFullLengthDetails($illustration, $settings),
                IllustrationType::ANIMAL => $this->getAnimalDetails($illustration, $settings),
                default => []
            };

            $result['price'] = [
                'name' => 'Prix',
                'price' => Number::currency($illustration->price, 'EUR', locale: 'fr'),
            ];

            $result['pose'] = $this->getPose($illustration->pose, $settings);
            $result['background'] = $this->getBackground($illustration->background, $settings);

            if ($illustration->addTracking) {
                $result['addTracking'] = [
                    'name' => 'Demander le suivi',
                    'price' => Number::currency($settings->options_add_tracking, 'EUR', locale: 'fr'),
                ];
            }

            if ($illustration->print) {
                $result['print'] = [
                    'name' => 'Imprimer l\'illustration',
                    'price' => Number::currency($settings->options_print, 'EUR', locale: 'fr'),
                ];
            }

            return $result;
        });
    }

    private function getBustDetails(Illustration $illustration, IllustrationSettings $settings): array
    {
        $result = [
            'type' => [
                'name' => 'Buste',
                'price' => Number::currency($settings->bust_base, 'EUR', locale: 'fr'),
            ]
        ];

        if ($illustration->nbHumans > 0) {
            $result['nbHumans'] = [
                'name' => "Personnes en plus ({$illustration->nbHumans})",
                'price' => Number::currency($settings->bust_add_human * $illustration->nbHumans, 'EUR', locale: 'fr'),
            ];
        }

        if ($illustration->nbAnimals > 0) {
            $result['nbAnimals'] = [
                'name' => "Compagnons en plus ({$illustration->nbAnimals})",
                'price' => Number::currency($settings->bust_add_animal * $illustration->nbAnimals, 'EUR', locale: 'fr'),
            ];
        }

        return $result;
    }

    private function getFullLengthDetails(Illustration $illustration, IllustrationSettings $settings): array
    {
        $result = [
            'type' => [
                'name' => 'Portrait en pied',
                'price' => Number::currency($settings->fl_base, 'EUR', locale: 'fr'),
            ]
        ];

        if ($illustration->nbHumans > 0) {
            $result['nbHumans'] = [
                'name' => "Personnes en plus ({$illustration->nbHumans})",
                'price' => Number::currency($settings->fl_add_human * $illustration->nbHumans, 'EUR', locale: 'fr'),
            ];
        }

        if ($illustration->nbAnimals > 0) {
            $result['nbAnimals'] = [
                'name' => "Compagnons en plus ({$illustration->nbAnimals})",
                'price' => Number::currency($settings->fl_add_animal * $illustration->nbAnimals, 'EUR', locale: 'fr'),
            ];
        }

        return $result;
    }

    private function getAnimalDetails(Illustration $illustration, IllustrationSettings $settings): array
    {
        $result = [
            'type' => [
                'name' => 'Compagnon',
                'price' => Number::currency($settings->animal_base, 'EUR', locale: 'fr'),
            ]
        ];

        if ($illustration->nbAnimals > 0) {
            $result['nbAnimals'] = [
                'name' => "Compagnons en plus ({$illustration->nbHumans})",
                'price' => Number::currency($settings->animal_add_one * $illustration->nbHumans, 'EUR', locale: 'fr'),
            ];
        }

        if ($illustration->nbAnimals > 1) {
            $result['nbAnimalsToy'] = [
                'name' => "Jouets en plus ({$illustration->nbAnimals})",
                'price' => Number::currency($settings->animal_toy * $illustration->nbAnimals, 'EUR', locale: 'fr'),
            ];
        }

        return $result;
    }

    private function getPose(string $pose, IllustrationSettings $settings): array
    {
        switch ($pose) {
            case 'SIMPLE':
                return [
                    'name' => 'Pose simple',
                    'price' => Number::currency($settings->option_pose_simple, 'EUR', locale: 'fr'),
                ];
                break;
            case 'COMPLEX':
                return [
                    'name' => 'Pose complexe',
                    'price' => Number::currency($settings->option_pose_complex, 'EUR', locale: 'fr'),
                ];
                break;

            default:
                return [];
        }
    }

    private function getBackground(string $background, IllustrationSettings $settings): array
    {
        switch ($background) {
            case 'GRADIENT':
                return [
                    'name' => 'Fond gradient / uni',
                    'price' => Number::currency($settings->option_bg_gradient, 'EUR', locale: 'fr'),
                ];
                break;
            case 'SIMPLE':
                return [
                    'name' => 'Fond simple',
                    'price' => Number::currency($settings->option_bg_simple, 'EUR', locale: 'fr'),
                ];
                break;
            case 'COMPLEX':
                return [
                    'name' => 'Fond complexe',
                    'price' => Number::currency($settings->option_bg_complex, 'EUR', locale: 'fr'),
                ];
                break;
            default:
                return [];
        }
    }
}
<?php

namespace App\Casts;

use Illuminate\Contracts\Database\Eloquent\CastsAttributes;
use Illuminate\Database\Eloquent\Model;

class MoneyCast implements CastsAttributes
{
    /**
     * Cast the given value.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function get(Model $model, string $key, mixed $value, array $attributes): object
    {
        return new class((int) $value) implements \JsonSerializable {
            public function __construct(public readonly int $cents) {}

            // Raw cents for calculations
            public function cents(): int
            {
                return $this->cents;
            }

            // Euros as float for calculations
            public function euros(): float
            {
                return $this->cents / 100;
            }

            // Formatted string for display
            public function formatted(): string
            {
                return number_format($this->euros(), 2, ',', ' ') . '€';
            }

            // For JSON serialization - return object with all values
            public function jsonSerialize(): array
            {
                return [
                    'cents' => $this->cents,
                    'euros' => $this->euros(),
                    'formatted' => $this->formatted(),
                ];
            }

            // Auto-convert to string
            public function __toString(): string
            {
                return $this->formatted();
            }
        };
    }

    /**
     * Prepare the given value for storage.
     *
     * @param  array<string, mixed>  $attributes
     */
    public function set(Model $model, string $key, mixed $value, array $attributes): int
    {
        // Handle null/empty values
        if ($value === null || $value === '') {
            return 0;
        }

        // Handle money object
        if (is_object($value) && method_exists($value, 'cents')) {
            return $value->cents();
        }

        // Handle string/float euro values (like "25.50" or 25.50)
        if (is_string($value) || is_float($value)) {
            // Convert to string first to avoid float precision issues
            $stringValue = (string) $value;

            // If it contains a decimal point, treat as euros
            if (str_contains($stringValue, '.') || str_contains($stringValue, ',')) {
                // Replace comma with dot for parsing
                $stringValue = str_replace(',', '.', $stringValue);
                $euros = (float) $stringValue;
                return (int) round($euros * 100);
            }
        }

        // Otherwise treat as cents (integer)
        return (int) $value;
    }
}

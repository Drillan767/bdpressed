<?php

namespace App\Services;

class MoneyService
{
    /**
     * Create a Money object like the MoneyCast does
     * This ensures consistency across the application
     */
    public function createMoneyObject(int $cents)
    {
        return new class($cents) implements \JsonSerializable
        {
            public function __construct(public readonly int $cents) {}

            public function cents(): int
            {
                return $this->cents;
            }

            public function euros(): float
            {
                return $this->cents / 100;
            }

            public function formatted(): string
            {
                return number_format($this->euros(), 2, ',', ' ').'€';
            }

            public function jsonSerialize(): array
            {
                return [
                    'cents' => $this->cents,
                    'euros' => $this->euros(),
                    'formatted' => $this->formatted(),
                ];
            }

            public function __toString(): string
            {
                return $this->formatted();
            }
        };
    }

    /**
     * Add two money amounts
     */
    public function add($money1, $money2)
    {
        $cents1 = is_object($money1) ? $money1->cents() : (int) $money1;
        $cents2 = is_object($money2) ? $money2->cents() : (int) $money2;
        
        return $this->createMoneyObject($cents1 + $cents2);
    }

    /**
     * Subtract two money amounts
     */
    public function subtract($money1, $money2)
    {
        $cents1 = is_object($money1) ? $money1->cents() : (int) $money1;
        $cents2 = is_object($money2) ? $money2->cents() : (int) $money2;
        
        return $this->createMoneyObject($cents1 - $cents2);
    }
}
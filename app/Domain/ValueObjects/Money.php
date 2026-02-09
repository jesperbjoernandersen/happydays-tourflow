<?php

namespace App\Domain\ValueObjects;

use InvalidArgumentException;

/**
 * Money Value Object
 *
 * Represents a monetary amount with currency.
 * Immutable - all operations return new instances.
 */
class Money
{
    /**
     * @param float|int $amount The monetary amount
     * @param string $currency The currency code (EUR, DKK, USD, etc.)
     */
    public function __construct(
        float|int $amount,
        string $currency = 'EUR'
    ) {
        $this->validateAmount($amount);
        $this->validateCurrency($currency);

        $this->amount = $amount;
        $this->currency = $currency;
    }

    /**
     * Get the amount
     *
     * @return float|int
     */
    public function getAmount(): float|int
    {
        return $this->amount;
    }

    /**
     * Get the currency
     *
     * @return string
     */
    public function getCurrency(): string
    {
        return $this->currency;
    }

    /**
     * Add another Money object to this one
     *
     * @param Money $other The Money object to add
     * @return Money New instance with the sum
     * @throws InvalidArgumentException If currencies don't match
     */
    public function add(Money $other): Money
    {
        if ($this->currency !== $other->currency) {
            throw new InvalidArgumentException(
                'Cannot add money with different currencies: ' . $this->currency . ' vs ' . $other->currency
            );
        }

        return new self($this->amount + $other->amount, $this->currency);
    }

    /**
     * Multiply the amount by a multiplier
     *
     * @param int $multiplier The multiplier
     * @return Money New instance with the multiplied amount
     */
    public function multiply(int $multiplier): Money
    {
        return new self($this->amount * $multiplier, $this->currency);
    }

    /**
     * Format the money as a string
     *
     * @return string Formatted money string
     */
    public function format(): string
    {
        return number_format($this->amount, 2, ',', '.') . ' ' . $this->currency;
    }

    /**
     * Check if the amount is zero
     *
     * @return bool True if amount is zero
     */
    public function isZero(): bool
    {
        return $this->amount === 0.0 || $this->amount === 0;
    }

    /**
     * Create a zero money instance
     *
     * @param string $currency The currency code
     * @return Money
     */
    public static function zero(string $currency = 'EUR'): Money
    {
        return new self(0, $currency);
    }

    /**
     * Validate the amount
     *
     * @param float|int $amount
     * @throws InvalidArgumentException
     */
    private function validateAmount(float|int $amount): void
    {
        if (!is_numeric($amount)) {
            throw new InvalidArgumentException('Amount must be numeric');
        }

        if ($amount < 0) {
            throw new InvalidArgumentException('Amount cannot be negative');
        }
    }

    /**
     * Validate the currency
     *
     * @param string $currency
     * @throws InvalidArgumentException
     */
    private function validateCurrency(string $currency): void
    {
        if (strlen($currency) !== 3) {
            throw new InvalidArgumentException('Currency must be a 3-letter code (EUR, DKK, USD, etc.)');
        }
    }

    /**
     * Convert to string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->format();
    }
}

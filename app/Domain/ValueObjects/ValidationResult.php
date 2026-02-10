<?php

namespace App\Domain\ValueObjects;

/**
 * ValidationResult Value Object
 *
 * Represents the result of a booking validation.
 * Contains validation status, errors, and warnings.
 */
class ValidationResult
{
    /**
     * @param bool $isValid Whether the booking data is valid
     * @param array $errors Array of validation errors with field and message
     * @param array $warnings Array of validation warnings with field and message
     */
    public function __construct(
        bool $isValid,
        array $errors = [],
        array $warnings = []
    ) {
        $this->isValid = $isValid;
        $this->errors = $errors;
        $this->warnings = $warnings;
    }

    /**
     * Check if the validation passed
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->isValid;
    }

    /**
     * Get validation errors
     *
     * @return array
     */
    public function getErrors(): array
    {
        return $this->errors;
    }

    /**
     * Get validation warnings
     *
     * @return array
     */
    public function getWarnings(): array
    {
        return $this->warnings;
    }

    /**
     * Check if there are any errors
     *
     * @return bool
     */
    public function hasErrors(): bool
    {
        return !empty($this->errors);
    }

    /**
     * Check if there are any warnings
     *
     * @return bool
     */
    public function hasWarnings(): bool
    {
        return !empty($this->warnings);
    }

    /**
     * Add an error
     *
     * @param string $field The field that has the error
     * @param string $message The error message
     * @return self
     */
    public function addError(string $field, string $message): self
    {
        $this->errors[] = [
            'field' => $field,
            'message' => $message,
        ];
        return $this;
    }

    /**
     * Add a warning
     *
     * @param string $field The field that has the warning
     * @param string $message The warning message
     * @return self
     */
    public function addWarning(string $field, string $message): self
    {
        $this->warnings[] = [
            'field' => $field,
            'message' => $message,
        ];
        return $this;
    }

    /**
     * Create a valid result
     *
     * @param array $warnings Optional warnings
     * @return self
     */
    public static function valid(array $warnings = []): self
    {
        return new self(true, [], $warnings);
    }

    /**
     * Create an invalid result
     *
     * @param array $errors Validation errors
     * @param array $warnings Optional warnings
     * @return self
     */
    public static function invalid(array $errors, array $warnings = []): self
    {
        return new self(false, $errors, $warnings);
    }

    /**
     * Convert to array
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'isValid' => $this->isValid,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
        ];
    }

    /**
     * Convert to array (for API responses)
     *
     * @return array
     */
    public function toResponseArray(): array
    {
        return [
            'is_valid' => $this->isValid,
            'errors' => $this->errors,
            'warnings' => $this->warnings,
        ];
    }

    /**
     * Convert to string
     *
     * @return string
     */
    public function __toString(): string
    {
        if ($this->isValid) {
            return 'Valid';
        }

        return sprintf(
            'Invalid (%d error(s))',
            count($this->errors)
        );
    }
}

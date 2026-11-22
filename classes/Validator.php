<?php

declare(strict_types=1);

/**
 * Tiny fluent validator. Stores one message per field and never throws.
 *
 * Example:
 *   $validator = (new Validator($data))
 *       ->required('destination_name', 'Destination name')
 *       ->maxLength('destination_name', 120, 'Destination name')
 *       ->email('email', 'Email address');
 *
 *   if ($validator->fails()) { $errors = $validator->errors(); }
 */
final class Validator
{
    /** @var array<string, string> */
    private array $errors = [];

    /** @param array<string, mixed> $data */
    public function __construct(private array $data)
    {
    }

    public function required(string $field, string $label): self
    {
        if ($this->hasError($field)) {
            return $this;
        }

        if ($this->value($field) === '') {
            $this->errors[$field] = $label . ' is required.';
        }

        return $this;
    }

    public function maxLength(string $field, int $max, string $label): self
    {
        if ($this->hasError($field) || $this->value($field) === '') {
            return $this;
        }

        if (mb_strlen($this->value($field), 'UTF-8') > $max) {
            $this->errors[$field] = sprintf('%s must be %d characters or fewer.', $label, $max);
        }

        return $this;
    }

    public function minLength(string $field, int $min, string $label): self
    {
        if ($this->hasError($field) || $this->value($field) === '') {
            return $this;
        }

        if (mb_strlen($this->value($field), 'UTF-8') < $min) {
            $this->errors[$field] = sprintf('%s must be at least %d characters.', $label, $min);
        }

        return $this;
    }

    public function email(string $field, string $label): self
    {
        if ($this->hasError($field) || $this->value($field) === '') {
            return $this;
        }

        if (filter_var($this->value($field), FILTER_VALIDATE_EMAIL) === false) {
            $this->errors[$field] = 'Enter a valid ' . strtolower($label) . '.';
        }

        return $this;
    }

    /**
     * Strict YYYY-MM-DD validation with a sane year range.
     */
    public function date(string $field, string $label): self
    {
        if ($this->hasError($field) || $this->value($field) === '') {
            return $this;
        }

        $value = $this->value($field);
        $date = DateTimeImmutable::createFromFormat('!Y-m-d', $value);
        $valid = $date !== false && $date->format('Y-m-d') === $value;
        $year = $valid ? (int) $date->format('Y') : 0;

        if (!$valid || $year < 1900 || $year > 2100) {
            $this->errors[$field] = $label . ' must be a valid date (YYYY-MM-DD).';
        }

        return $this;
    }

    public function passes(): bool
    {
        return $this->errors === [];
    }

    public function fails(): bool
    {
        return !$this->passes();
    }

    /** @return array<string, string> */
    public function errors(): array
    {
        return $this->errors;
    }

    private function value(string $field): string
    {
        $value = $this->data[$field] ?? '';

        return is_scalar($value) ? trim((string) $value) : '';
    }

    private function hasError(string $field): bool
    {
        return isset($this->errors[$field]);
    }
}

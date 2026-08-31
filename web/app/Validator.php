<?php
final class Validator
{
    private array $errors = [];

    public function required(string $field, $value, string $label): self
    {
        if ($value === '' || $value === null) {
            $this->errors[$field] = $label . ' is required.';
        }
        return $this;
    }

    public function email(string $field, string $value): self
    {
        if ($value !== '' && !filter_var($value, FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = 'Enter a valid email address.';
        }
        return $this;
    }

    public function min(string $field, string $value, int $len, string $label): self
    {
        if ($value !== '' && mb_strlen($value) < $len) {
            $this->errors[$field] = $label . ' must be at least ' . $len . ' characters.';
        }
        return $this;
    }

    public function numeric(string $field, $value, string $label): self
    {
        if ($value !== '' && !is_numeric($value)) {
            $this->errors[$field] = $label . ' must be a number.';
        }
        return $this;
    }

    public function inList(string $field, $value, array $allowed, string $label): self
    {
        if ($value !== '' && !in_array($value, $allowed, true)) {
            $this->errors[$field] = $label . ' has an invalid value.';
        }
        return $this;
    }

    public function passes(): bool
    {
        return empty($this->errors);
    }

    public function errors(): array
    {
        return $this->errors;
    }

    public function firstError(): string
    {
        return $this->errors ? reset($this->errors) : '';
    }
}

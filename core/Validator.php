<?php

class Validator {
    private array $errors = [];
    private array $data;

    public function __construct(array $data) {
        $this->data = $data;
    }

    public function required(string $field, string $label = ''): self {
        $label = $label ?: ucfirst($field);
        if (empty(trim($this->data[$field] ?? ''))) {
            $this->errors[$field] = "{$label} is required.";
        }
        return $this;
    }

    public function email(string $field, string $label = ''): self {
        $label = $label ?: ucfirst($field);
        if (!empty($this->data[$field]) && !filter_var($this->data[$field], FILTER_VALIDATE_EMAIL)) {
            $this->errors[$field] = "{$label} must be a valid email address.";
        }
        return $this;
    }

    public function minLength(string $field, int $min, string $label = ''): self {
        $label = $label ?: ucfirst($field);
        if (!empty($this->data[$field]) && strlen($this->data[$field]) < $min) {
            $this->errors[$field] = "{$label} must be at least {$min} characters.";
        }
        return $this;
    }

    public function maxLength(string $field, int $max, string $label = ''): self {
        $label = $label ?: ucfirst($field);
        if (!empty($this->data[$field]) && strlen($this->data[$field]) > $max) {
            $this->errors[$field] = "{$label} must be no more than {$max} characters.";
        }
        return $this;
    }

    public function matches(string $field, string $matchField, string $label = ''): self {
        $label = $label ?: ucfirst($field);
        if (($this->data[$field] ?? '') !== ($this->data[$matchField] ?? '')) {
            $this->errors[$field] = "{$label} fields do not match.";
        }
        return $this;
    }

    public function passes(): bool {
        return empty($this->errors);
    }

    public function errors(): array {
        return $this->errors;
    }

    public function firstError(): string {
        return reset($this->errors) ?: '';
    }
}

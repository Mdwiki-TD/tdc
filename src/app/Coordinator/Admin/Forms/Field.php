<?php

declare(strict_types=1);

namespace App\Coordinator\Admin\Forms;

/**
 * Base class for a form field (like wtforms.Field).
 *
 * Validator signature: fn(mixed $value, Form $form, Field $field): ?string
 * (return an error message, or null when valid). Validators other than
 * `V::required()` are skipped when the value is empty, so optional fields
 * can still carry format rules (email, length, ...).
 */
abstract class Field
{
    public string $name = '';
    public string $htmlName = '';
    public mixed $data = null;

    /** @var string[] */
    public array $errors = [];

    /** @var string[] errors found while converting raw input (e.g. "abc" -> int) */
    protected array $processErrors = [];

    /**
     * @param array<callable> $validators
     * @param array<string,scalar|bool|null> $attrs extra HTML attributes (placeholder, min, class, ...)
     * @param array<callable> $filters applied to string input before validation
     */
    public function __construct(
        public readonly string $label = '',
        protected array $validators = [],
        protected mixed $default = null,
        protected array $attrs = [],
        protected array $filters = ['trim'],
    ) {
        $this->data = $this->default;
    }

    public static function esc(string $value): string
    {
        return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
    }

    /** Called by Form: gives the field its name and the HTML name (prefix[name]). */
    public function attach(string $name, string $prefix = ''): void
    {
        $this->name     = $name;
        $this->htmlName = $prefix === '' ? $name : "{$prefix}[{$name}]";
    }

    public function id(): string
    {
        return trim((string) preg_replace('/[^A-Za-z0-9_-]+/', '_', $this->htmlName), '_');
    }

    /** Takes a raw POST value: filters it, converts it, stores it in $data. */
    public function processInput(mixed $raw): void
    {
        $this->processErrors = [];

        if (is_string($raw)) {
            foreach ($this->filters as $filter) {
                $raw = $filter($raw);
            }
        }
        $this->data = $this->coerce($raw);
    }

    /** Sets the value from stored data (DB row) instead of from POST. */
    public function setData(mixed $value): void
    {
        $this->processErrors = [];
        $this->data = $this->coerce($value);
    }

    protected function coerce(mixed $raw): mixed
    {
        return is_scalar($raw) ? (string) $raw : '';
    }

    public function isEmpty(): bool
    {
        return $this->data === null || $this->data === '' || $this->data === [];
    }

    public function isRequired(): bool
    {
        foreach ($this->validators as $validator) {
            if ($validator instanceof Required) {
                return true;
            }
        }
        return false;
    }

    public function validate(Form $form): bool
    {
        $this->errors = $this->processErrors;
        if ($this->errors !== []) {
            return false;
        }

        $empty = $this->isEmpty();
        foreach ($this->validators as $validator) {
            if ($empty && !$validator instanceof Required) {
                continue;
            }
            $message = $validator($this->data, $form, $this);
            if (is_string($message) && $message !== '') {
                $this->errors[] = $message;
                break;
            }
        }
        return $this->errors === [];
    }

    abstract protected function control(): string;

    /** Full markup: label + control + errors. */
    public function render(): string
    {
        return "<div class='mb-3'><div class='input-group'>"
            . "<span class='input-group-text'>" . self::esc($this->label) . "</span>"
            . $this->control()
            . "</div>" . $this->renderErrors() . "</div>";
    }

    protected function renderErrors(): string
    {
        if ($this->errors === []) {
            return '';
        }
        return "<div class='invalid-feedback d-block'>" . self::esc(implode(' ', $this->errors)) . "</div>";
    }

    protected function cssClass(string $base): string
    {
        $extra = (string) ($this->attrs['class'] ?? '');
        return trim($base . ($extra !== '' ? " $extra" : '') . ($this->errors !== [] ? ' is-invalid' : ''));
    }

    protected function htmlAttrs(): string
    {
        $out = '';
        foreach ($this->attrs as $key => $value) {
            if ($key === 'class' || $value === false || $value === null) {
                continue;
            }
            $out .= $value === true ? " $key" : " $key='" . self::esc((string) $value) . "'";
        }
        return $out;
    }
}

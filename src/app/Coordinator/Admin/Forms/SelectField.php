<?php

declare(strict_types=1);

namespace App\Coordinator\Admin\Forms;

class SelectField extends Field
{
    /** @param array<int|string,string> $choices value => label */
    public function __construct(
        string $label = '',
        protected array $choices = [],
        array $validators = [],
        mixed $default = null,
        array $attrs = [],
        array $filters = ['trim'],
    ) {
        parent::__construct($label, $validators, $default, $attrs, $filters);
    }

    /** @param array<int|string,string> $choices */
    public function setChoices(array $choices): void
    {
        $this->choices = $choices;
    }

    public function validate(Form $form): bool
    {
        $ok = parent::validate($form);
        if ($ok && !$this->isEmpty() && !in_array((string) $this->data, array_map('strval', array_keys($this->choices)), true)) {
            $this->errors[] = 'Not a valid choice.';
            return false;
        }
        return $ok;
    }

    protected function control(): string
    {
        $options = '';
        foreach ($this->choices as $value => $label) {
            $selected = (string) $value === (string) $this->data ? ' selected' : '';
            $options .= "<option value='" . self::esc((string) $value) . "'$selected>" . self::esc($label) . "</option>";
        }
        return "<select class='" . $this->cssClass('form-select') . "'"
            . " id='" . self::esc($this->id()) . "' name='" . self::esc($this->htmlName) . "'"
            . ($this->isRequired() ? ' required' : '')
            . $this->htmlAttrs() . ">$options</select>";
    }
}

<?php

declare(strict_types=1);

namespace App\Coordinator\Admin\Forms;

class IntegerField extends Field
{
    protected function coerce(mixed $raw): ?int
    {
        if ($raw === null || $raw === '') {
            return null;
        }
        $int = filter_var($raw, FILTER_VALIDATE_INT);
        if ($int === false) {
            $this->processErrors[] = 'Not a valid integer.';
            return null;
        }
        return $int;
    }

    protected function control(): string
    {
        return "<input class='" . $this->cssClass('form-control') . "' type='number'"
            . " id='" . self::esc($this->id()) . "' name='" . self::esc($this->htmlName) . "'"
            . " value='" . self::esc((string) $this->data) . "'"
            . ($this->isRequired() ? ' required' : '')
            . $this->htmlAttrs() . "/>";
    }
}

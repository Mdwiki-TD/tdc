<?php

declare(strict_types=1);

namespace App\Coordinator\Admin\Forms;

class StringField extends Field
{
    protected string $type = 'text';

    protected function control(): string
    {
        return "<input class='" . $this->cssClass('form-control') . "' type='{$this->type}'"
            . " id='" . self::esc($this->id()) . "' name='" . self::esc($this->htmlName) . "'"
            . " value='" . self::esc((string) $this->data) . "'"
            . ($this->isRequired() ? ' required' : '')
            . $this->htmlAttrs() . "/>";
    }
}

<?php

declare(strict_types=1);

namespace App\Coordinator\Admin\Forms;

class HiddenField extends Field
{
    protected function control(): string
    {
        return "<input type='hidden' name='" . self::esc($this->htmlName) . "'"
            . " value='" . self::esc((string) $this->data) . "'/>";
    }

    public function render(): string
    {
        return $this->control();
    }
}

<?php

declare(strict_types=1);

namespace App\Coordinator\Admin\Forms;

/**
 * Checkbox rendered as a Bootstrap switch. Emits a hidden "0" before the
 * checkbox (same convention the admin pages already use), so an unchecked box
 * still posts 0. $data is always int 0 or 1.
 */
class BooleanField extends Field
{
    protected function coerce(mixed $raw): int
    {
        return in_array($raw, [1, '1', true, 'on', 'true', 'yes'], true) ? 1 : 0;
    }

    public function isEmpty(): bool
    {
        return (int) $this->data === 0;
    }

    protected function control(): string
    {
        $name = self::esc($this->htmlName);
        return "<div class='form-check form-switch'>"
            . "<input type='hidden' name='$name' value='0'>"
            . "<input class='" . $this->cssClass('form-check-input') . "' type='checkbox'"
            . " id='" . self::esc($this->id()) . "' name='$name' value='1'"
            . ((int) $this->data === 1 ? ' checked' : '')
            . $this->htmlAttrs() . ">"
            . "</div>";
    }

    public function render(): string
    {
        return "<div class='mb-3'><div class='input-group form-control'>"
            . "<span class='me-3'>" . self::esc($this->label) . ":</span>"
            . $this->control()
            . "</div>" . $this->renderErrors() . "</div>";
    }
}

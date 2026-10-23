<?php

declare(strict_types=1);

namespace App\Coordinator\Admin\Forms;

/** Marker validator: also makes the field render the HTML `required` attribute. */
final class Required
{
    public function __construct(public readonly string $message = 'This field is required.')
    {
    }

    public function __invoke(mixed $value, Form $form, Field $field): ?string
    {
        return $field->isEmpty() ? $this->message : null;
    }
}

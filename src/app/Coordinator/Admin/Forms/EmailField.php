<?php

declare(strict_types=1);

namespace App\Coordinator\Admin\Forms;

class EmailField extends StringField
{
    protected string $type = 'email';

    public function __construct(
        string $label = '',
        array $validators = [],
        mixed $default = null,
        array $attrs = [],
        array $filters = ['trim'],
    ) {
        parent::__construct($label, [V::email(), ...$validators], $default, $attrs, $filters);
    }
}

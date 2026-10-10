<?php

namespace App\Utils;

class SidebarGroup
{
    public function __construct(
        public readonly string $id,
        public readonly string $title,
        public readonly string $icon,
        public readonly array $items,
        public readonly bool $disabled = false,
    ) {
    }
}

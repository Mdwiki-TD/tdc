<?php

namespace App\Utils;

class SidebarItem
{
    public function __construct(
        public readonly string $id,
        public readonly int $admin,
        public readonly string $href,
        public readonly string $title,
        public readonly string $icon,
        public readonly ?int $no_admin = 0,
        public readonly ?int $forCoordinator = 0,
        public readonly ?string $target = "",
    ) {
    }
}

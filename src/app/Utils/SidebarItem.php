<?php

namespace App\Utils;

class SidebarItem
{
    public function __construct(
        public readonly string $id,
        public readonly bool $forAdmin,
        public readonly string $href,
        public readonly string $title,
        public readonly string $icon,
        public readonly ?bool $notForAdmin = false,
        public readonly ?int $forCoordinator = 0,
        public readonly ?string $target = "",
    ) {
    }
}

<?php

namespace App\Utils;

class SidebarItem
{
    public function __construct(
        public readonly string $id,
        public readonly string $href,
        public readonly string $title,
        public readonly string $icon,
        public readonly bool $adminOnly = false,
        public readonly bool $notForAdmin = false,
        public readonly bool $forCoordinator = true,
        public readonly bool $forAnyuser = false,
        public readonly string $target = "",
    ) {
    }
    /**
     * Generate a single navigation list item
     *
     * @param string $filename
     *
     * @return string HTML for the navigation item
     */
    public function generateListItem(string $filename): string
    {
        // Build full URL (unless external link)
        $hrefFull = ($this->target !== '') ? $this->href : "{$filename}?ty={$this->href}";

        $iconTag = (!empty($this->icon)) ? "<i class='bi {$this->icon} me-1'></i>" : "";
        $targetAttr = (!empty($this->target)) ? "target='_blank'" : '';

        $escapedHref = htmlspecialchars($hrefFull, ENT_QUOTES, 'UTF-8');
        $escapedTitle = htmlspecialchars($this->title, ENT_QUOTES, 'UTF-8');

        return <<<HTML
            <a {$targetAttr} class='linknave rounded' href='{$escapedHref}' title='{$escapedTitle}' data-bs-toggle='tooltip' data-bs-placement='right'>
                {$iconTag}
                <span class='hide-on-collapse-inline'>{$escapedTitle}</span>
            </a>
        HTML;
    }

}

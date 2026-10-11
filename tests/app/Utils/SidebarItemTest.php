<?php

declare(strict_types=1);

namespace Tests\App\Utils;

use App\Utils\SidebarItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SidebarItem::class)]
class SidebarItemTest extends TestCase
{
    public function testSidebarItemDefaultsAndProperties(): void
    {
        $item = new SidebarItem(
            id: 'item1',
            href: 'dashboard',
            title: 'Dashboard',
            icon: 'bi-speedometer'
        );

        $this->assertSame('item1', $item->id);
        $this->assertSame('dashboard', $item->href);
        $this->assertSame('Dashboard', $item->title);
        $this->assertSame('bi-speedometer', $item->icon);
        $this->assertFalse($item->adminOnly);
        $this->assertFalse($item->notForAdmin);
        $this->assertTrue($item->forCoordinator);
        $this->assertFalse($item->forAnyuser);
        $this->assertSame('', $item->target);
    }

    public function testGenerateListItemInternalLink(): void
    {
        $item = new SidebarItem(
            id: 'item1',
            href: 'settings',
            title: 'Settings',
            icon: 'bi-gear'
        );

        $html = $item->generateListItem('admin.php');

        $this->assertStringContainsString("href='admin.php?ty=settings'", $html);
        $this->assertStringContainsString("<i class='bi bi-gear me-1'></i>", $html);
        $this->assertStringContainsString("Settings", $html);
        $this->assertStringNotContainsString("target='_blank'", $html);
    }

    public function testGenerateListItemExternalLink(): void
    {
        $item = new SidebarItem(
            id: 'item2',
            href: 'https://example.com',
            title: 'External Site',
            icon: '',
            target: '_blank'
        );

        $html = $item->generateListItem('index.php');

        $this->assertStringContainsString("href='https://example.com'", $html);
        $this->assertStringContainsString("target='_blank'", $html);
        $this->assertStringNotContainsString("<i class='bi", $html);
    }
}

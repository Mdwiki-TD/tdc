<?php

declare(strict_types=1);

namespace Tests\App\Utils;

use App\Utils\SidebarGroup;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(SidebarGroup::class)]
class SidebarGroupTest extends TestCase
{
    public function testSidebarGroupProperties(): void
    {
        $group = new SidebarGroup(
            id: 'grp1',
            title: 'Group One',
            icon: 'bi-folder',
            items: ['item1', 'item2'],
            disabled: false
        );

        $this->assertSame('grp1', $group->id);
        $this->assertSame('Group One', $group->title);
        $this->assertSame('bi-folder', $group->icon);
        $this->assertSame(['item1', 'item2'], $group->items);
        $this->assertFalse($group->disabled);
    }

    public function testSidebarGroupDisabledDefault(): void
    {
        $group = new SidebarGroup(
            id: 'grp2',
            title: 'Group Two',
            icon: 'bi-gear',
            items: []
        );

        $this->assertFalse($group->disabled);
    }
}

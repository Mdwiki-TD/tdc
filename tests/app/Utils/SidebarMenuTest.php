<?php



namespace Tests\App\Utils;

use App\Utils\SidebarMenu;
use PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(SidebarMenu::class)]
class SidebarMenuTest extends TestCase
{
    public function testRenderForCoordinatorUser(): void
    {
        $sidebar = new SidebarMenu('test_script.php', 'last_coord', true);
        $html = $sidebar->render();

        $this->assertStringContainsString('Recent', $html);
        $this->assertStringContainsString('test_script.php?ty=last_coord', $html);
        $this->assertStringContainsString('Coordinators', $html);
        $this->assertStringContainsString('Settings', $html);
        // Non-admin item 'last' should be hidden for coordinators because no_admin = 1
        $this->assertStringNotContainsString('test_script.php?ty=last"', $html);
    }

    public function testRenderForNonCoordinatorUser(): void
    {
        $sidebar = new SidebarMenu('test_script.php', 'last', false);
        $html = $sidebar->render();

        $this->assertStringContainsString('Recent', $html);
        $this->assertStringContainsString('test_script.php?ty=last', $html);
        // Admin items like 'last_coord', 'settings', 'admins' should be hidden for non-coordinators
        $this->assertStringNotContainsString('test_script.php?ty=last_coord', $html);
        $this->assertStringNotContainsString('test_script.php?ty=settings', $html);
        $this->assertStringNotContainsString('test_script.php?ty=admins', $html);
    }

    public function testActiveStateAndCollapseGroup(): void
    {
        $sidebar = new SidebarMenu('test_script.php', 'process', false);
        $html = $sidebar->render();

        $this->assertStringContainsString('<li id=\'process\' class=\'active\'>', $html);
        $this->assertStringContainsString('id="Translations-collapse"', $html);
        $this->assertStringContainsString('class="collapse show"', $html);
    }

    public function testToStringMethod(): void
    {
        $sidebar = new SidebarMenu('test_script.php', 'stat', false);
        $this->assertSame($sidebar->render(), (string)$sidebar);
    }
}

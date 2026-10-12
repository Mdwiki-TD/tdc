<?php

declare(strict_types=1);

namespace Tests\App\Coordinator;

use App\Coordinator\RecentTranslations;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RecentTranslations::class)]
class RecentTranslationsTest extends TestCase
{
    public function testBuildNamespaceFilter(): void
    {
        $recent = new RecentTranslations('ar', 'pages');
        $html = $recent->buildNamespaceFilter();

        $this->assertStringContainsString('id="radio_pages"', $html);
        $this->assertStringContainsString('value="pages"', $html);
        $this->assertStringContainsString('checked', $html);
        $this->assertStringContainsString('Main', $html);
    }

    public function testFilterRecentOptions(): void
    {
        $recent = new RecentTranslations('ar', 'pages');
        $data = [
            ['lang' => 'ar', 'autonym' => 'العربية'],
            ['lang' => 'en', 'autonym' => 'English']
        ];

        $html = $recent->filterRecent('ar', $data);

        $this->assertStringContainsString("value='All'", $html);
        $this->assertStringContainsString("value='ar' selected", $html);
        $this->assertStringContainsString("(ar) العربية", $html);
    }

    public function testRenderDataTableScript(): void
    {
        $recent = new RecentTranslations('ar', 'pages');

        ob_start();
        $recent->renderDataTableScript();
        $output = ob_get_clean();

        $this->assertStringContainsString("<script>", $output);
        $this->assertStringContainsString("$('#last_table')", $output);
    }
}

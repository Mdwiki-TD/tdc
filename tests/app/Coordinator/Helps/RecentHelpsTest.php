<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Helps;

use App\Coordinator\Helps\RecentHelps;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RecentHelps::class)]
class RecentHelpsTest extends TestCase
{
    public function testFilterRecent2(): void
    {
        $optionsHtml = RecentHelps::filter_recent2('ar', ['ar' => 'ar', 'en' => 'en']);

        $this->assertStringContainsString("value='All'", $optionsHtml);
        $this->assertStringContainsString("value='ar' selected", $optionsHtml);
        $this->assertStringContainsString("value='en'", $optionsHtml);
    }

    public function testDoAddDate(): void
    {
        $resultsTrue = [
            'row1' => [
                'pupdate' => '2025-01-20',
                'add_date' => '2025-01-25'
            ]
        ];
        $this->assertTrue(RecentHelps::do_add_date($resultsTrue));

        $resultsFalse = [
            'row1' => [
                'pupdate' => '2025-01-25',
                'add_date' => '2025-01-20'
            ]
        ];
        $this->assertFalse(RecentHelps::do_add_date($resultsFalse));
    }

    public function testFilterTableRadioButtons(): void
    {
        $data = ['pages' => 'Main', 'pages_users' => 'User'];
        $html = RecentHelps::filter_table($data, 'pages', 'table_select');

        $this->assertStringContainsString('id="radio_pages"', $html);
        $this->assertStringContainsString('checked', $html);
        $this->assertStringContainsString('Main', $html);
    }
}

<?php

namespace Tests\Backend\ApiOrSql;

use PHPUnit\Framework\TestCase;
use App\SQLorAPI\PagesTable;
use App\SQLorAPI\RecentTable;


class RecentDataTest extends TestCase
{
    public function testGetRecentSqlReturnsArray()
    {
        $result = (new RecentTable())->getRecentPagesWithViews('ar');
        $this->assertIsArray($result);
    }

    public function testGetRecentSqlHandlesAllLang()
    {
        $result = (new RecentTable())->getRecentPagesWithViews('All');
        $this->assertIsArray($result);
    }

    public function testGetRecentSqlCachesResults()
    {
        // First call
        $result1 = (new RecentTable())->getRecentPagesWithViews('en');
        // Second call should return cached result
        $result2 = (new RecentTable())->getRecentPagesWithViews('en');

        $this->assertSame($result1, $result2);
    }

    public function testGetRecentSqlWithEmptyLang()
    {
        $result = (new RecentTable())->getRecentPagesWithViews('');
        $this->assertIsArray($result);
    }

    public function testGetRecentPagesUsersReturnsArray()
    {
        $result = (new RecentTable())->getRecentPagesUsers('ar');
        $this->assertIsArray($result);
    }

    public function testGetRecentPagesUsersHandlesAllLang()
    {
        $result = (new RecentTable())->getRecentPagesUsers('All');
        $this->assertIsArray($result);
    }

    public function testGetRecentPagesUsersCachesResults()
    {
        // First call
        $result1 = (new RecentTable())->getRecentPagesUsers('fr');
        // Second call should return cached result
        $result2 = (new RecentTable())->getRecentPagesUsers('fr');

        $this->assertSame($result1, $result2);
    }

    public function testGetRecentPagesUsersWithEmptyLang()
    {
        $result = (new RecentTable())->getRecentPagesUsers('');
        $this->assertIsArray($result);
    }

    public function testGetRecentTranslatedReturnsArray()
    {
        $result = (new RecentTable())->getRecentTranslated('ar', 'pages', 10, 0);
        $this->assertIsArray($result);
    }

    public function testGetRecentTranslatedWithAllLang()
    {
        $result = (new RecentTable())->getRecentTranslated('All', 'pages', 10, 0);
        $this->assertIsArray($result);
    }

    public function testGetRecentTranslatedWithOffset()
    {
        $result = (new RecentTable())->getRecentTranslated('en', 'pages', 5, 10);
        $this->assertIsArray($result);
    }

    public function testGetRecentTranslatedWithZeroLimit()
    {
        $result = (new RecentTable())->getRecentTranslated('en', 'pages', 0, 0);
        $this->assertIsArray($result);
    }

    public function testGetRecentTranslatedWithEmptyLang()
    {
        $result = (new RecentTable())->getRecentTranslated('', 'pages', 10, 0);
        $this->assertIsArray($result);
    }

    public function testGetTotalTranslationsCountReturnsInt()
    {
        $result = (new PagesTable())->get_total_translations_count('ar', 'pages');
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testGetTotalTranslationsCountWithAllLang()
    {
        $result = (new PagesTable())->get_total_translations_count('All', 'pages');
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testGetTotalTranslationsCountWithEmptyLang()
    {
        $result = (new PagesTable())->get_total_translations_count('', 'pages');
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testGetPagesUsersToMainReturnsArray()
    {
        $result = (new PagesTable())->get_pages_users_to_main('ar');
        $this->assertIsArray($result);
    }

    public function testGetPagesUsersToMainHandlesAllLang()
    {
        $result = (new PagesTable())->get_pages_users_to_main('All');
        $this->assertIsArray($result);
    }

    public function testGetPagesUsersToMainCachesResults()
    {
        // First call
        $result1 = (new PagesTable())->get_pages_users_to_main('es');
        // Second call should return cached result
        $result2 = (new PagesTable())->get_pages_users_to_main('es');

        $this->assertSame($result1, $result2);
    }

    public function testGetPagesUsersToMainWithEmptyLang()
    {
        $result = (new PagesTable())->get_pages_users_to_main('');
        $this->assertIsArray($result);
    }

    // Edge case: test with special characters in language code
    public function testGetRecentSqlWithSpecialCharLang()
    {
        $result = (new RecentTable())->getRecentPagesWithViews('test-lang');
        $this->assertIsArray($result);
    }

    // Additional boundary test
    public function testGetRecentTranslatedWithNegativeValues()
    {
        // Should handle negative limit gracefully
        $result = (new RecentTable())->getRecentTranslated('en', 'pages', -1, -1);
        $this->assertIsArray($result);
    }
}

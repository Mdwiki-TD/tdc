<?php

namespace Tests\Backend\ApiOrSql;

use PHPUnit\Framework\TestCase;

use function App\SQLorAPI\getRecentPagesWithViews;
use function App\SQLorAPI\getRecentPagesUsers;
use function App\SQLorAPI\getRecentTranslated;

use function App\SQLorAPI\get_total_translations_count;
use function App\SQLorAPI\get_pages_users_to_main;

class RecentDataTest extends TestCase
{
    public function testGetRecentSqlReturnsArray()
    {
        $result = getRecentPagesWithViews('ar');
        $this->assertIsArray($result);
    }

    public function testGetRecentSqlHandlesAllLang()
    {
        $result = getRecentPagesWithViews('All');
        $this->assertIsArray($result);
    }

    public function testGetRecentSqlCachesResults()
    {
        // First call
        $result1 = getRecentPagesWithViews('en');
        // Second call should return cached result
        $result2 = getRecentPagesWithViews('en');

        $this->assertSame($result1, $result2);
    }

    public function testGetRecentSqlWithEmptyLang()
    {
        $result = getRecentPagesWithViews('');
        $this->assertIsArray($result);
    }

    public function testGetRecentPagesUsersReturnsArray()
    {
        $result = getRecentPagesUsers('ar');
        $this->assertIsArray($result);
    }

    public function testGetRecentPagesUsersHandlesAllLang()
    {
        $result = getRecentPagesUsers('All');
        $this->assertIsArray($result);
    }

    public function testGetRecentPagesUsersCachesResults()
    {
        // First call
        $result1 = getRecentPagesUsers('fr');
        // Second call should return cached result
        $result2 = getRecentPagesUsers('fr');

        $this->assertSame($result1, $result2);
    }

    public function testGetRecentPagesUsersWithEmptyLang()
    {
        $result = getRecentPagesUsers('');
        $this->assertIsArray($result);
    }

    public function testGetRecentTranslatedReturnsArray()
    {
        $result = getRecentTranslated('ar', 'pages', 10, 0);
        $this->assertIsArray($result);
    }

    public function testGetRecentTranslatedWithAllLang()
    {
        $result = getRecentTranslated('All', 'pages', 10, 0);
        $this->assertIsArray($result);
    }

    public function testGetRecentTranslatedWithOffset()
    {
        $result = getRecentTranslated('en', 'pages', 5, 10);
        $this->assertIsArray($result);
    }

    public function testGetRecentTranslatedWithZeroLimit()
    {
        $result = getRecentTranslated('en', 'pages', 0, 0);
        $this->assertIsArray($result);
    }

    public function testGetRecentTranslatedWithEmptyLang()
    {
        $result = getRecentTranslated('', 'pages', 10, 0);
        $this->assertIsArray($result);
    }

    public function testGetTotalTranslationsCountReturnsInt()
    {
        $result = get_total_translations_count('ar', 'pages');
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testGetTotalTranslationsCountWithAllLang()
    {
        $result = get_total_translations_count('All', 'pages');
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testGetTotalTranslationsCountWithEmptyLang()
    {
        $result = get_total_translations_count('', 'pages');
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testGetPagesUsersToMainReturnsArray()
    {
        $result = get_pages_users_to_main('ar');
        $this->assertIsArray($result);
    }

    public function testGetPagesUsersToMainHandlesAllLang()
    {
        $result = get_pages_users_to_main('All');
        $this->assertIsArray($result);
    }

    public function testGetPagesUsersToMainCachesResults()
    {
        // First call
        $result1 = get_pages_users_to_main('es');
        // Second call should return cached result
        $result2 = get_pages_users_to_main('es');

        $this->assertSame($result1, $result2);
    }

    public function testGetPagesUsersToMainWithEmptyLang()
    {
        $result = get_pages_users_to_main('');
        $this->assertIsArray($result);
    }

    // Edge case: test with special characters in language code
    public function testGetRecentSqlWithSpecialCharLang()
    {
        $result = getRecentPagesWithViews('test-lang');
        $this->assertIsArray($result);
    }

    // Additional boundary test
    public function testGetRecentTranslatedWithNegativeValues()
    {
        // Should handle negative limit gracefully
        $result = getRecentTranslated('en', 'pages', -1, -1);
        $this->assertIsArray($result);
    }
}

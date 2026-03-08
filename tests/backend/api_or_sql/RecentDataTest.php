<?php

namespace Tests\Backend\ApiOrSql;

use PHPUnit\Framework\TestCase;

use function SQLorAPI\Recent\get_recent_sql;
use function SQLorAPI\Recent\get_recent_pages_users;
use function SQLorAPI\Recent\get_recent_translated;
use function SQLorAPI\Recent\get_total_translations_count;
use function SQLorAPI\Recent\get_pages_users_to_main;

class RecentDataTest extends TestCase
{
    public function testGetRecentSqlReturnsArray()
    {
        $result = get_recent_sql('ar');
        $this->assertIsArray($result);
    }

    public function testGetRecentSqlHandlesAllLang()
    {
        $result = get_recent_sql('All');
        $this->assertIsArray($result);
    }

    public function testGetRecentSqlCachesResults()
    {
        // First call
        $result1 = get_recent_sql('en');
        // Second call should return cached result
        $result2 = get_recent_sql('en');

        $this->assertSame($result1, $result2);
    }

    public function testGetRecentSqlWithEmptyLang()
    {
        $result = get_recent_sql('');
        $this->assertIsArray($result);
    }

    public function testGetRecentPagesUsersReturnsArray()
    {
        $result = get_recent_pages_users('ar');
        $this->assertIsArray($result);
    }

    public function testGetRecentPagesUsersHandlesAllLang()
    {
        $result = get_recent_pages_users('All');
        $this->assertIsArray($result);
    }

    public function testGetRecentPagesUsersCachesResults()
    {
        // First call
        $result1 = get_recent_pages_users('fr');
        // Second call should return cached result
        $result2 = get_recent_pages_users('fr');

        $this->assertSame($result1, $result2);
    }

    public function testGetRecentPagesUsersWithEmptyLang()
    {
        $result = get_recent_pages_users('');
        $this->assertIsArray($result);
    }

    public function testGetRecentTranslatedReturnsArray()
    {
        $result = get_recent_translated('ar', 'pages', 10, 0);
        $this->assertIsArray($result);
    }

    public function testGetRecentTranslatedWithAllLang()
    {
        $result = get_recent_translated('All', 'pages', 10, 0);
        $this->assertIsArray($result);
    }

    public function testGetRecentTranslatedWithOffset()
    {
        $result = get_recent_translated('en', 'pages', 5, 10);
        $this->assertIsArray($result);
    }

    public function testGetRecentTranslatedWithZeroLimit()
    {
        $result = get_recent_translated('en', 'pages', 0, 0);
        $this->assertIsArray($result);
    }

    public function testGetRecentTranslatedWithEmptyLang()
    {
        $result = get_recent_translated('', 'pages', 10, 0);
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
        $result = get_recent_sql('test-lang');
        $this->assertIsArray($result);
    }

    // Additional boundary test
    public function testGetRecentTranslatedWithNegativeValues()
    {
        // Should handle negative limit gracefully
        $result = get_recent_translated('en', 'pages', -1, -1);
        $this->assertIsArray($result);
    }
}
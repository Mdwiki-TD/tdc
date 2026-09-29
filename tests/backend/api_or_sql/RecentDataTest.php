<?php

namespace Tests\Backend\ApiOrSql;

use PHPUnit\Framework\TestCase;
use App\SQLorAPI\PagesTable;
use App\SQLorAPI\RecentTable;


class RecentDataTest extends TestCase
{
    public function testGetRecentSqlReturnsArray()
    {
        $result = (RecentTable::getInstance())->getRecentPagesWithViews('ar');
        $this->assertIsArray($result);
    }

    public function testGetRecentSqlHandlesAllLang()
    {
        $result = (RecentTable::getInstance())->getRecentPagesWithViews('All');
        $this->assertIsArray($result);
    }

    public function testGetRecentSqlCachesResults()
    {
        // First call
        $result1 = (RecentTable::getInstance())->getRecentPagesWithViews('en');
        // Second call should return cached result
        $result2 = (RecentTable::getInstance())->getRecentPagesWithViews('en');

        $this->assertSame($result1, $result2);
    }

    public function testGetRecentSqlWithEmptyLang()
    {
        $result = (RecentTable::getInstance())->getRecentPagesWithViews('');
        $this->assertIsArray($result);
    }

    public function testGetRecentPagesUsersReturnsArray()
    {
        $result = (RecentTable::getInstance())->getRecentPagesUsers('ar');
        $this->assertIsArray($result);
    }

    public function testGetRecentPagesUsersHandlesAllLang()
    {
        $result = (RecentTable::getInstance())->getRecentPagesUsers('All');
        $this->assertIsArray($result);
    }

    public function testGetRecentPagesUsersCachesResults()
    {
        // First call
        $result1 = (RecentTable::getInstance())->getRecentPagesUsers('fr');
        // Second call should return cached result
        $result2 = (RecentTable::getInstance())->getRecentPagesUsers('fr');

        $this->assertSame($result1, $result2);
    }

    public function testGetRecentPagesUsersWithEmptyLang()
    {
        $result = (RecentTable::getInstance())->getRecentPagesUsers('');
        $this->assertIsArray($result);
    }

    public function testGetRecentTranslatedReturnsArray()
    {
        $result = (RecentTable::getInstance())->getRecentTranslated('ar', 'pages', 10, 0);
        $this->assertIsArray($result);
    }

    public function testGetRecentTranslatedWithAllLang()
    {
        $result = (RecentTable::getInstance())->getRecentTranslated('All', 'pages', 10, 0);
        $this->assertIsArray($result);
    }

    public function testGetRecentTranslatedWithOffset()
    {
        $result = (RecentTable::getInstance())->getRecentTranslated('en', 'pages', 5, 10);
        $this->assertIsArray($result);
    }

    public function testGetRecentTranslatedWithZeroLimit()
    {
        $result = (RecentTable::getInstance())->getRecentTranslated('en', 'pages', 0, 0);
        $this->assertIsArray($result);
    }

    public function testGetRecentTranslatedWithEmptyLang()
    {
        $result = (RecentTable::getInstance())->getRecentTranslated('', 'pages', 10, 0);
        $this->assertIsArray($result);
    }

    public function testGetTotalTranslationsCountReturnsInt()
    {
        $result = (PagesTable::getInstance())->getTotalTranslationsCount('ar', 'pages');
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testGetTotalTranslationsCountWithAllLang()
    {
        $result = (PagesTable::getInstance())->getTotalTranslationsCount('All', 'pages');
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testGetTotalTranslationsCountWithEmptyLang()
    {
        $result = (PagesTable::getInstance())->getTotalTranslationsCount('', 'pages');
        $this->assertIsInt($result);
        $this->assertGreaterThanOrEqual(0, $result);
    }

    public function testGetPagesUsersToMainReturnsArray()
    {
        $result = (PagesTable::getInstance())->getPagesUsersToMain('ar');
        $this->assertIsArray($result);
    }

    public function testGetPagesUsersToMainHandlesAllLang()
    {
        $result = (PagesTable::getInstance())->getPagesUsersToMain('All');
        $this->assertIsArray($result);
    }

    public function testGetPagesUsersToMainCachesResults()
    {
        // First call
        $result1 = (PagesTable::getInstance())->getPagesUsersToMain('es');
        // Second call should return cached result
        $result2 = (PagesTable::getInstance())->getPagesUsersToMain('es');

        $this->assertSame($result1, $result2);
    }

    public function testGetPagesUsersToMainWithEmptyLang()
    {
        $result = (PagesTable::getInstance())->getPagesUsersToMain('');
        $this->assertIsArray($result);
    }

    // Edge case: test with special characters in language code
    public function testGetRecentSqlWithSpecialCharLang()
    {
        $result = (RecentTable::getInstance())->getRecentPagesWithViews('test-lang');
        $this->assertIsArray($result);
    }

    // Additional boundary test
    public function testGetRecentTranslatedWithNegativeValues()
    {
        // Should handle negative limit gracefully
        $result = (RecentTable::getInstance())->getRecentTranslated('en', 'pages', -1, -1);
        $this->assertIsArray($result);
    }
}

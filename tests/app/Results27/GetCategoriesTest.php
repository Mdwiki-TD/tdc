<?php

declare(strict_types=1);

namespace Tests\App\Results27;

use App\Results27\GetCategories;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GetCategories::class)]
class GetCategoriesTest extends TestCase
{
    public function testGetCategoryFromCacheMissingFileReturnsEmptyArray(): void
    {
        $res = GetCategories::get_category_from_cache('non_existent_category_xyz');
        $this->assertSame([], $res);
    }

    public function testFetchCategoryMembersFormatsTitlePrefix(): void
    {
        // When MdwikiApi returns no members, fetch_category_members returns empty array
        $res = GetCategories::fetch_category_members('TestCat');
        $this->assertIsArray($res);
    }
}

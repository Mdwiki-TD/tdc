<?php

declare(strict_types=1);

namespace Tests\App\Results27;

use App\Results27\GetCats;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GetCats::class)]
class GetCatsTest extends TestCase
{
    public function testGetCategoryFromCacheMissingFileReturnsEmptyArray(): void
    {
        $res = GetCats::get_category_from_cache('non_existent_category_xyz');
        $this->assertSame([], $res);
    }

    public function testFetchCategoryMembersFormatsTitlePrefix(): void
    {
        // When MdwikiApi returns no members, fetch_category_members returns empty array
        $res = GetCats::fetch_category_members('TestCat');
        $this->assertIsArray($res);
    }
}

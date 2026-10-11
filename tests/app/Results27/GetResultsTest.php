<?php

declare(strict_types=1);

namespace Tests\App\Results27;

use App\Results27\GetResults;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(GetResults::class)]
class GetResultsTest extends TestCase
{
    public function testGetCatExistsAndMissing(): void
    {
        $res = GetResults::get_cat_exists_and_missing('non_existent_cat', 0, 'en', false);

        $this->assertArrayHasKey('len_of_exists', $res);
        $this->assertArrayHasKey('missing', $res);
        $this->assertIsArray($res['missing']);
    }
}

<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Helps;

use App\Coordinator\Helps\Sugust;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Sugust::class)]
class SugustTest extends TestCase
{
    public function testGetSugustEmptyTitle(): void
    {
        $result = Sugust::get_sugust('', 'ar');

        $this->assertSame('', $result['sugust']);
        $this->assertSame(0, $result['time']);
    }

    public function testGetSugustNullInputs(): void
    {
        $result = Sugust::get_sugust(null, null);

        $this->assertSame('', $result['sugust']);
        $this->assertSame(0, $result['time']);
    }
}

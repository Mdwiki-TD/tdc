<?php

declare(strict_types=1);

namespace Tests\App\Tables;

use App\Tables\MainTables;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MainTables::class)]
class MainTablesTest extends TestCase
{
    public function testGetRefCountWordViewsAndAssessments(): void
    {
        $refLead = MainTables::getRefCount('NonExistentTitle', 'lead');
        $refAll = MainTables::getRefCount('NonExistentTitle', 'all');
        $wordLead = MainTables::getWord('NonExistentTitle', 'lead');
        $wordAll = MainTables::getWord('NonExistentTitle', 'all');
        $views = MainTables::getViews('NonExistentTitle');
        $assessment = MainTables::getAssessments('NonExistentTitle');

        $this->assertSame(0, $refLead);
        $this->assertSame(0, $refAll);
        $this->assertSame(0, $wordLead);
        $this->assertSame(0, $wordAll);
        $this->assertSame(0, $views);
        $this->assertNull($assessment);
    }
}

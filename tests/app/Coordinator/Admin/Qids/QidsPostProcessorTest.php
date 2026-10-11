<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\Qids;

use App\Coordinator\Admin\Qids\QidsPostProcessor;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(QidsPostProcessor::class)]
class QidsPostProcessorTest extends TestCase
{
    public function testProcessEmptyPost(): void
    {
        $processor = new QidsPostProcessor();

        $ref = new \ReflectionClass($processor);
        $method = $ref->getMethod('process');
        $method->setAccessible(true);
        $method->invoke($processor, []);

        $res = $processor->handle([]);
        $this->assertFalse($res['success']);
    }
}

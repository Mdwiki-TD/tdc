<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\WikiRefsOptions;

use App\Coordinator\Admin\WikiRefsOptions\WikiRefsOptionsEditPostHandler;
use App\User\SessionManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(WikiRefsOptionsEditPostHandler::class)]
class WikiRefsOptionsEditPostHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        SessionManager::ensureStarted();
    }

    public function testProcessEmptyPost(): void
    {
        $processor = new WikiRefsOptionsEditPostHandler();

        $ref = new \ReflectionClass($processor);
        $method = $ref->getMethod('process');
        $method->setAccessible(true);
        $method->invoke($processor, []);

        $res = $processor->handle([]);
        $this->assertFalse($res['success']);
    }
}

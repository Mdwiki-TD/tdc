<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\Translated;

use App\Coordinator\Admin\Translated\EditPagePostHandler;
use App\User\SessionManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EditPagePostHandler::class)]
class EditPagePostHandlerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        SessionManager::ensureStarted();
    }

    public function testProcessEmptyPost(): void
    {
        $processor = new EditPagePostHandler('1', 'pages');

        $ref = new \ReflectionClass($processor);
        $method = $ref->getMethod('process');
        $method->setAccessible(true);
        $method->invoke($processor, []);

        $res = $processor->handle([]);
        $this->assertFalse($res['success']);
    }
}

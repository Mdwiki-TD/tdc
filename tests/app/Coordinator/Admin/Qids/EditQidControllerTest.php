<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\Qids;

use App\Coordinator\Admin\Qids\EditQidController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(EditQidController::class)]
class EditQidControllerTest extends TestCase
{
    public function testGetCardTitle(): void
    {
        $controller = new EditQidController();

        $ref = new \ReflectionClass($controller);
        $method = $ref->getMethod('getCardTitle');
        $method->setAccessible(true);
        $title = $method->invoke($controller);

        $this->assertStringContainsString('Qid', $title);
    }
}

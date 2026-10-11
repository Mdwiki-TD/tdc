<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\Users;

use App\Coordinator\Admin\Users\MsgController;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(MsgController::class)]
class MsgControllerTest extends TestCase
{
    public function testMsgControllerInstance(): void
    {
        $controller = new MsgController();
        $this->assertInstanceOf(MsgController::class, $controller);
    }
}

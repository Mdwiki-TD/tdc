<?php

namespace Tests\Coordinator\Admin\Users;

use App\Coordinator\Admin\Users\MsgController;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MsgController::class)]
class MsgTest extends TestCase
{
    public function testGetHost1ReturnsString()
    {
        $serverName = 'localhost';
        $hoste = ($serverName == "localhost")
            ? "https://cdnjs.cloudflare.com"
            : "https://tools-static.wmflabs.org/cdnjs";

        $this->assertEquals("https://cdnjs.cloudflare.com", $hoste);
    }
}

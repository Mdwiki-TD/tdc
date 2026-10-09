<?php



namespace Tests\App;

use App\NotFound;
use PHPUnit\Framework\TestCase;

use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(NotFound::class)]
class NotFoundTest extends TestCase
{
    public function testRenderOutputs404Card(): void
    {
        ob_start();
        NotFound::render();
        $output = ob_get_clean();

        $this->assertStringContainsString('404', $output);
        $this->assertStringContainsString('Page not found.', $output);
        $this->assertStringContainsString('card border-danger', $output);
    }
}

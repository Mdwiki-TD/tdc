<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\Common;

use App\Coordinator\Admin\Common\AbstractController;
use App\User\SessionManager;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractController::class)]
class AbstractControllerTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        SessionManager::ensureStarted();
    }

    public function testCreateCsrfTokenFieldAndEchoCard(): void
    {
        $controller = new class extends AbstractController {
            protected function createPostProcessor(): object
            {
                return new \stdClass();
            }

            protected function renderFormCard(): void
            {
                $this->echoCard('Test Header', '<p>Test Body</p>');
            }
        };

        $tokenHtml = $controller->createCsrfTokenField();
        $this->assertStringContainsString('name=\'csrf_token\'', $tokenHtml);

        ob_start();
        $controller->echoCard('Test Card', 'Card Content');
        $output = ob_get_clean();

        $this->assertStringContainsString('<h4>Test Card</h4>', $output);
        $this->assertStringContainsString('Card Content', $output);
    }
}

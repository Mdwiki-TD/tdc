<?php

namespace Tests\Coordinator\Admin;

require_once __DIR__ . '/../../../src/app/coordinator/admin/BasePopupController.php';

use App\Coordinator\Admin\BasePopupController;
use PHPUnit\Framework\TestCase;

class TestPopupController extends BasePopupController
{
    public function handleRequest(): void
    {
        $this->checkAuthorization();
        $this->renderHeaderScripts('<meta name="test" content="1"/>', 'test-container');
        $this->renderCard('Test Title', '<p>Test Body</p>');
    }

    public function testHeaderScripts(string $extraHtml = '', string $containerId = ''): void
    {
        $this->renderHeaderScripts($extraHtml, $containerId);
    }

    public function testCard(string $title, string $bodyHtml): void
    {
        $this->renderCard($title, $bodyHtml);
    }
}

class BasePopupControllerTest extends TestCase
{
    public function testRenderHeaderScriptsOutputsExpectedHtml()
    {
        $controller = new TestPopupController();

        ob_start();
        $controller->testHeaderScripts('<script>console.log("hello");</script>', 'my-popup');
        $output = ob_get_clean();

        $this->assertStringContainsString('</div>', $output);
        $this->assertStringContainsString('<script>console.log("hello");</script>', $output);
        $this->assertStringContainsString('$("#mainnav").hide();', $output);
        $this->assertStringContainsString('$("#maindiv").hide();', $output);
        $this->assertStringContainsString('<div id=\'my-popup\' class="container-fluid">', $output);
    }

    public function testRenderCardOutputsBootstrapCard()
    {
        $controller = new TestPopupController();

        ob_start();
        $controller->testCard('Card Header Title', '<form>Form Content</form>');
        $output = ob_get_clean();

        $this->assertStringContainsString("<div class='card'>", $output);
        $this->assertStringContainsString("<div class='card-header'>", $output);
        $this->assertStringContainsString('<h4>Card Header Title</h4>', $output);
        $this->assertStringContainsString("<div class='card-body'>", $output);
        $this->assertStringContainsString('<form>Form Content</form>', $output);
    }
}

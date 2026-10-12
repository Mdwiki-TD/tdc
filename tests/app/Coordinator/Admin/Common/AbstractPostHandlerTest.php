<?php

declare(strict_types=1);

namespace Tests\App\Coordinator\Admin\Common;

use App\Coordinator\Admin\Common\AbstractPostHandler;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AbstractPostHandler::class)]
class AbstractPostHandlerTest extends TestCase
{
    public function testCloseButtonHtmlAndBoolIntAndMessages(): void
    {
        $handler = new class extends AbstractPostHandler {
            protected function process(array $post): void
            {
                $this->addError('Error msg');
                $this->addText('Success msg');
            }

            public function testBoolInt(mixed $val): int
            {
                return $this->boolInt($val);
            }
        };

        $this->assertSame('', $handler->getCloseButtonHtml());
        $this->assertSame(1, $handler->testBoolInt('1'));
        $this->assertSame(0, $handler->testBoolInt('invalid'));

        ob_start();
        $handler->RenderIndexMesseges([
            'errors'    => ['Test Error'],
            'texts'     => ['Test Text'],
            'csrfError' => true
        ]);
        $output = ob_get_clean();

        $this->assertStringContainsString('Test Error', $output);
        $this->assertStringContainsString('Test Text', $output);
        $this->assertStringContainsString('Invalid or Reused CSRF Token!', $output);
    }
}

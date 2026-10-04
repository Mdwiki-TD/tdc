<?php



namespace Tests\App\Utils;

use App\Utils\Html;
use PHPUnit\Framework\TestCase;

class HtmlTest extends TestCase
{
    public function testMakeModalFade(): void
    {
        $html = Html::make_modal_fade('Logout', 'Are you sure?', 'logoutModal', '<button>Yes</button>');

        $this->assertStringContainsString('id="logoutModal"', $html);
        $this->assertStringContainsString('Logout', $html);
        $this->assertStringContainsString('Are you sure?', $html);
        $this->assertStringContainsString('<button>Yes</button>', $html);
    }

    public function testMakeDropdown(): void
    {
        $options = ['cat1', 'cat2', 'cat3'];
        $html = Html::makeDropdown($options, 'cat2', 'cat_select', 'all');

        $this->assertStringContainsString('id="cat_select"', $html);
        $this->assertStringContainsString('value=\'all\'', $html);
        $this->assertStringContainsString('value=\'cat2\' selected', $html);
        $this->assertStringContainsString('value=\'cat1\'', $html);
    }

    public function testMakeColSm4(): void
    {
        $html = Html::makeColSm4('Card Title', '<table>Content</table>', 6, '<div>Extra</div>', 'SubTitle');

        $this->assertStringContainsString('col-md-6', $html);
        $this->assertStringContainsString('Card Title', $html);
        $this->assertStringContainsString('<table>Content</table>', $html);
        $this->assertStringContainsString('<div>Extra</div>', $html);
        $this->assertStringContainsString('SubTitle', $html);
    }

    public function testMakeCol(): void
    {
        $html = Html::makeCol('Main Title', '<table>Table 1</table>', '<table>Table 2</table>');

        $this->assertStringContainsString('col-lg-3 col-md-12 col-sm-12', $html);
        $this->assertStringContainsString('Main Title', $html);
        $this->assertStringContainsString('Table 1', $html);
        $this->assertStringContainsString('Table 2', $html);
    }
}

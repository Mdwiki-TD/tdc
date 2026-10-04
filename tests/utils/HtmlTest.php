<?php

namespace Tests\Utils\Html;

use PHPUnit\Framework\TestCase;
use App\Utils\Html;

class HtmlTest extends TestCase
{
    public function testMakeModalFade()
    {
        $result = Html::make_modal_fade("Modal Title", "Modal Body", "modal_id1", "<button>X</button>");
        $this->assertStringContainsString("Modal Title", $result);
        $this->assertStringContainsString("Modal Body", $result);
        $this->assertStringContainsString("id=\"modal_id1\"", $result);
        $this->assertStringContainsString("<button>X</button>", $result);
    }

    public function testMakeDropdown()
    {
        $tab = ['Category 1', 'Category 2'];
        $result = Html::makeDropdown($tab, 'Category 2', 'dropdown_id', 'all');

        $this->assertStringContainsString("id=\"dropdown_id\"", $result);
        $this->assertStringContainsString("<option value='all' >All</option>", $result);
        $this->assertStringContainsString("<option value='Category 1' >Category 1</option>", $result);
        $this->assertStringContainsString("<option value='Category 2' selected>Category 2</option>", $result);
    }

    public function testMakeColSm4()
    {
        $result = Html::makeColSm4("Header", "Table Data", 6, "<div>Footer</div>", "Subtitle");
        $this->assertStringContainsString("col-md-6", $result);
        $this->assertStringContainsString("Header", $result);
        $this->assertStringContainsString("Subtitle", $result);
        $this->assertStringContainsString("Table Data", $result);
        $this->assertStringContainsString("<div>Footer</div>", $result);
    }
}

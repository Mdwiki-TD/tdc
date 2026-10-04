<?php

namespace Tests\Utils\Html;

use PHPUnit\Framework\TestCase;
use App\Utils\Html2;

class Html2Test extends TestCase
{
    public function testBannerAlert()
    {
        $result = Html2::banner_alert("Test Alert Error");
        $this->assertStringContainsString("Test Alert Error", $result);
        $this->assertStringContainsString("alert alert-danger", $result);
    }

    public function testMakeProjectToUser()
    {
        $ProjectsTitleToId = [
            'Project Alpha' => 1,
            'Project Beta' => 2
        ];

        $result = Html2::make_project_to_user('Project Beta', $ProjectsTitleToId);
        $this->assertStringContainsString("<option value='Uncategorized'>Uncategorized</option>", $result);
        $this->assertStringContainsString("<option value='Project Alpha' >Project Alpha</option>", $result);
        $this->assertStringContainsString("<option value='Project Beta' selected>Project Beta</option>", $result);
    }

    public function testMakeInputGroup()
    {
        $result = Html2::make_input_group("Username", "usr_id", "John <script>", "required");
        $this->assertStringContainsString("<span class='input-group-text'>Username</span>", $result);
        $this->assertStringContainsString("name='usr_id'", $result);
        $this->assertStringContainsString("value='John &lt;script&gt;'", $result);
        $this->assertStringContainsString("required", $result);
        $this->assertStringContainsString("col-md-3", $result);
    }

    public function testMakeInputGroupNoCol()
    {
        $result = Html2::make_input_group_no_col("Email", "email_id", "test@example.com", "");
        $this->assertStringContainsString("<span class='input-group-text'>Email</span>", $result);
        $this->assertStringContainsString("name='email_id'", $result);
        $this->assertStringContainsString("value='test@example.com'", $result);
        $this->assertStringNotContainsString("col-md-3", $result);
    }

    public function testMakeCard()
    {
        $result = Html2::makeCard("Card Title", "<p>Card Content</p>");
        $this->assertStringContainsString("Card Title", $result);
        $this->assertStringContainsString("<p>Card Content</p>", $result);
    }

    public function testMakeColSmBody()
    {
        $result = Html2::make_col_sm_body("Main Title", "Sub Title", "Body Data", 5);
        $this->assertStringContainsString("col-md-5", $result);
        $this->assertStringContainsString("Main Title", $result);
        $this->assertStringContainsString("Sub Title", $result);
        $this->assertStringContainsString("Body Data", $result);
    }

    public function testMakeDrop()
    {
        $options = [
            'Display A' => 'val_a',
            'Display B' => 'val_b'
        ];
        $result = Html2::make_drop($options, 'val_b');
        $this->assertStringContainsString("<option value='val_a' >Display A</option>", $result);
        $this->assertStringContainsString("<option value='val_b' selected>Display B</option>", $result);
    }

    public function testMakeDatalistOptions()
    {
        $options = [
            'English' => 'en',
            'French' => 'fr'
        ];
        $result = Html2::make_datalist_options($options);
        $this->assertStringContainsString("<option value='en'>English</option>", $result);
        $this->assertStringContainsString("<option value='fr'>French</option>", $result);
    }

    public function testDivAlert()
    {
        $result = Html2::div_alert(["Message 1", "Message 2"], "danger");
        $this->assertStringContainsString("alert alert-danger", $result);
        $this->assertStringContainsString("Message 1", $result);
        $this->assertStringContainsString("Message 2", $result);

        $resultEmpty = Html2::div_alert([]);
        $this->assertEquals("", $resultEmpty);

        $resultInvalidType = Html2::div_alert(["Msg"], "unknown");
        $this->assertStringContainsString("alert alert-secondary", $resultInvalidType);
    }
}

<?php

namespace Tests\Utils\Html;

use PHPUnit\Framework\TestCase;
use App\Utils\Html;

class HtmlTest extends TestCase
{
    public function testBannerAlert()
    {
        $result = Html::banner_alert("Test Alert Error");
        $this->assertStringContainsString("Test Alert Error", $result);
        $this->assertStringContainsString("alert alert-danger", $result);
    }

    public function testMakeModalFade()
    {
        $result = Html::make_modal_fade("Modal Title", "Modal Body", "modal_id1", "<button>X</button>");
        $this->assertStringContainsString("Modal Title", $result);
        $this->assertStringContainsString("Modal Body", $result);
        $this->assertStringContainsString("id=\"modal_id1\"", $result);
        $this->assertStringContainsString("<button>X</button>", $result);
    }

    public function testMakeMailIconNew()
    {
        $tab = [
            'user' => 'JohnDoe',
            'lang' => 'en',
            'target' => 'Test_Target',
            'pupdate' => '2023-10-10',
            'title' => 'Page_Title'
        ];
        $result = Html::make_mail_icon_new($tab, 'test_func');
        $this->assertStringContainsString("test_func(this)", $result);
        $this->assertStringContainsString("user=JohnDoe", $result);
        $this->assertStringContainsString("lang=en", $result);
        $this->assertStringContainsString("target=Test_Target", $result);
    }

    public function testMakeProjectToUser()
    {
        $ProjectsTitleToId = [
            'Project Alpha' => 1,
            'Project Beta' => 2
        ];

        $result = Html::make_project_to_user('Project Beta', $ProjectsTitleToId);
        $this->assertStringContainsString("<option value='Uncategorized'>Uncategorized</option>", $result);
        $this->assertStringContainsString("<option value='Project Alpha' >Project Alpha</option>", $result);
        $this->assertStringContainsString("<option value='Project Beta' selected>Project Beta</option>", $result);
    }

    public function testMakeInputGroup()
    {
        $result = Html::make_input_group("Username", "usr_id", "John <script>", "required");
        $this->assertStringContainsString("<span class='input-group-text'>Username</span>", $result);
        $this->assertStringContainsString("name='usr_id'", $result);
        $this->assertStringContainsString("value='John &lt;script&gt;'", $result);
        $this->assertStringContainsString("required", $result);
        $this->assertStringContainsString("col-md-3", $result);
    }

    public function testMakeInputGroupNoCol()
    {
        $result = Html::make_input_group_no_col("Email", "email_id", "test@example.com", "");
        $this->assertStringContainsString("<span class='input-group-text'>Email</span>", $result);
        $this->assertStringContainsString("name='email_id'", $result);
        $this->assertStringContainsString("value='test@example.com'", $result);
        $this->assertStringNotContainsString("col-md-3", $result);
    }

    public function testHtml::MakeDropdown()
    {
        $tab = ['Category 1', 'Category 2'];
        $result = Html::makeDropdown($tab, 'Category 2', 'dropdown_id', 'all');

        $this->assertStringContainsString("id=\"dropdown_id\"", $result);
        $this->assertStringContainsString("<option value='all' >All</option>", $result);
        $this->assertStringContainsString("<option value='Category 1' >Category 1</option>", $result);
        $this->assertStringContainsString("<option value='Category 2' selected>Category 2</option>", $result);
    }

    public function testHtml::MakeCard()
    {
        $result = Html::makeCard("Card Title", "<p>Card Content</p>");
        $this->assertStringContainsString("Card Title", $result);
        $this->assertStringContainsString("<p>Card Content</p>", $result);
    }

    public function testHtml::MakeColSm4()
    {
        $result = Html::makeColSm4("Header", "Table Data", 6, "<div>Footer</div>", "Subtitle");
        $this->assertStringContainsString("col-md-6", $result);
        $this->assertStringContainsString("Header", $result);
        $this->assertStringContainsString("Subtitle", $result);
        $this->assertStringContainsString("Table Data", $result);
        $this->assertStringContainsString("<div>Footer</div>", $result);
    }

    public function testMakeColSmBody()
    {
        $result = Html::make_col_sm_body("Main Title", "Sub Title", "Body Data", 5);
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
        $result = Html::make_drop($options, 'val_b');
        $this->assertStringContainsString("<option value='val_a' >Display A</option>", $result);
        $this->assertStringContainsString("<option value='val_b' selected>Display B</option>", $result);
    }

    public function testMakeDatalistOptions()
    {
        $options = [
            'English' => 'en',
            'French' => 'fr'
        ];
        $result = Html::make_datalist_options($options);
        $this->assertStringContainsString("<option value='en'>English</option>", $result);
        $this->assertStringContainsString("<option value='fr'>French</option>", $result);
    }

    public function testMakeMdwikiTitle()
    {
        $result1 = Html::make_mdwiki_title("Main Page");
        $this->assertStringContainsString("<a target='_blank' href='https://mdwiki.org/wiki/Main_Page'>Main Page</a>", $result1);

        $result2 = Html::make_mdwiki_title("");
        $this->assertEquals("", $result2);
    }

    public function testMakeCatUrl()
    {
        $result = Html::make_cat_url("Test Category");
        $this->assertStringContainsString("<a target='_blank' href='https://mdwiki.org/wiki/Category:Test_Category'>Test Category</a>", $result);

        $result2 = Html::make_cat_url("");
        $this->assertEquals("", $result2);
    }

    public function testMakeTalkUrl()
    {
        $result = Html::make_talk_url("ar", "Mr. User");
        $this->assertStringContainsString("<a target='_blank' href='//ar.wikipedia.org/w/index.php?title=User_talk:Mr.%20User'>talk</a>", $result);
    }

    public function testMakeMdwikiUserUrl()
    {
        $result = Html::make_mdwiki_user_url("Test User");
        $this->assertStringContainsString("<a href='https://mdwiki.org/wiki/User:Test_User' taget='_blank'>Test User</a>", $result);
    }

    public function testMakeTargetUrl()
    {
        $result = Html::make_target_url("Target Page", "ar", "Display Name", true);
        $this->assertStringContainsString("<a target='_blank' href='https://ar.wikipedia.org/wiki/Target_Page'>Display Name</a>", $result);
        $this->assertStringContainsString("(DELETED)", $result);

        $result2 = Html::make_target_url("Page Without Display", "en");
        $this->assertStringContainsString(">Page Without Display</a>", $result2);
    }

    public function testDivAlert()
    {
        $result = Html::div_alert(["Message 1", "Message 2"], "danger");
        $this->assertStringContainsString("alert alert-danger", $result);
        $this->assertStringContainsString("Message 1", $result);
        $this->assertStringContainsString("Message 2", $result);

        $resultEmpty = Html::div_alert([]);
        $this->assertEquals("", $resultEmpty);

        $resultInvalidType = Html::div_alert(["Msg"], "unknown");
        $this->assertStringContainsString("alert alert-secondary", $resultInvalidType);
    }

    public function testMakeEditIconNew()
    {
        // Save state
        $origReq = $_REQUEST;
        $origCookie = $_COOKIE;

        $_REQUEST['test'] = 1;

        $params = ['id' => 123];
        $result = Html::make_edit_icon_new("TestTarget", $params, "Edit Record");

        $this->assertStringContainsString("index.php?ty=TestTarget", $result);
        $this->assertStringContainsString("id=123", $result);
        $this->assertStringContainsString("test=1", $result);
        $this->assertStringContainsString("pup_window_new(this)", $result);
        $this->assertStringContainsString(">Edit Record</a>", $result);

        // Restore state
        $_REQUEST = $origReq;
        $_COOKIE = $origCookie;
    }
}

<?php

namespace Tests\Utils\Html;

use PHPUnit\Framework\TestCase;
use Tables\SqlTables\TablesSql;

use function Utils\Html\banner_alert;
use function Utils\Html\login_card;
use function Utils\Html\make_modal_fade;
use function Utils\Html\make_mail_icon_new;
use function Utils\Html\make_project_to_user;
use function Utils\Html\make_input_group;
use function Utils\Html\make_input_group_no_col;
use function Utils\Html\makeDropdown;
use function Utils\Html\makeCard;
use function Utils\Html\makeColSm4;
use function Utils\Html\make_col_sm_body;
use function Utils\Html\make_drop;
use function Utils\Html\make_datalist_options;
use function Utils\Html\make_mdwiki_title;
use function Utils\Html\make_cat_url;
use function Utils\Html\make_talk_url;
use function Utils\Html\make_mdwiki_user_url;
use function Utils\Html\make_target_url;
use function Utils\Html\div_alert;
use function Utils\Html\make_edit_icon_new;

class HtmlTest extends TestCase
{
    public function testBannerAlert()
    {
        $result = banner_alert("Test Alert Error");
        $this->assertStringContainsString("Test Alert Error", $result);
        $this->assertStringContainsString("alert alert-danger", $result);
    }

    public function testLoginCard()
    {
        $result = login_card();
        $this->assertStringContainsString("Login", $result);
        $this->assertStringContainsString("onclick='login()'", $result);
    }

    public function testMakeModalFade()
    {
        $result = make_modal_fade("Modal Title", "Modal Body", "modal_id1", "<button>X</button>");
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
        $result = make_mail_icon_new($tab, 'test_func');
        $this->assertStringContainsString("test_func(this)", $result);
        $this->assertStringContainsString("user=JohnDoe", $result);
        $this->assertStringContainsString("lang=en", $result);
        $this->assertStringContainsString("target=Test_Target", $result);
    }

    public function testMakeProjectToUser()
    {
        TablesSql::$s_projects_title_to_id = [
            'Project Alpha' => 1,
            'Project Beta' => 2
        ];

        $result = make_project_to_user('Project Beta');
        $this->assertStringContainsString("<option value='Uncategorized'>Uncategorized</option>", $result);
        $this->assertStringContainsString("<option value='Project Alpha' >Project Alpha</option>", $result);
        $this->assertStringContainsString("<option value='Project Beta' selected>Project Beta</option>", $result);
    }

    public function testMakeInputGroup()
    {
        $result = make_input_group("Username", "usr_id", "John <script>", "required");
        $this->assertStringContainsString("<span class='input-group-text'>Username</span>", $result);
        $this->assertStringContainsString("name='usr_id'", $result);
        $this->assertStringContainsString("value='John &lt;script&gt;'", $result);
        $this->assertStringContainsString("required", $result);
        $this->assertStringContainsString("col-md-3", $result);
    }

    public function testMakeInputGroupNoCol()
    {
        $result = make_input_group_no_col("Email", "email_id", "test@example.com", "");
        $this->assertStringContainsString("<span class='input-group-text'>Email</span>", $result);
        $this->assertStringContainsString("name='email_id'", $result);
        $this->assertStringContainsString("value='test@example.com'", $result);
        $this->assertStringNotContainsString("col-md-3", $result);
    }

    public function testMakeDropdown()
    {
        $tab = ['Category 1', 'Category 2'];
        $result = makeDropdown($tab, 'Category 2', 'dropdown_id', 'all');

        $this->assertStringContainsString("id=\"dropdown_id\"", $result);
        $this->assertStringContainsString("<option value='all' >All</option>", $result);
        $this->assertStringContainsString("<option value='Category 1' >Category 1</option>", $result);
        $this->assertStringContainsString("<option value='Category 2' selected>Category 2</option>", $result);
    }

    public function testMakeCard()
    {
        $result = makeCard("Card Title", "<p>Card Content</p>");
        $this->assertStringContainsString("Card Title", $result);
        $this->assertStringContainsString("<p>Card Content</p>", $result);
    }

    public function testMakeColSm4()
    {
        $result = makeColSm4("Header", "Table Data", 6, "<div>Footer</div>", "Subtitle");
        $this->assertStringContainsString("col-md-6", $result);
        $this->assertStringContainsString("Header", $result);
        $this->assertStringContainsString("Subtitle", $result);
        $this->assertStringContainsString("Table Data", $result);
        $this->assertStringContainsString("<div>Footer</div>", $result);
    }

    public function testMakeColSmBody()
    {
        $result = make_col_sm_body("Main Title", "Sub Title", "Body Data", 5);
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
        $result = make_drop($options, 'val_b');
        $this->assertStringContainsString("<option value='val_a' >Display A</option>", $result);
        $this->assertStringContainsString("<option value='val_b' selected>Display B</option>", $result);
    }

    public function testMakeDatalistOptions()
    {
        $options = [
            'English' => 'en',
            'French' => 'fr'
        ];
        $result = make_datalist_options($options);
        $this->assertStringContainsString("<option value='en'>English</option>", $result);
        $this->assertStringContainsString("<option value='fr'>French</option>", $result);
    }

    public function testMakeMdwikiTitle()
    {
        $result1 = make_mdwiki_title("Main Page");
        $this->assertStringContainsString("<a target='_blank' href='https://mdwiki.org/wiki/Main_Page'>Main Page</a>", $result1);

        $result2 = make_mdwiki_title("");
        $this->assertEquals("", $result2);
    }

    public function testMakeCatUrl()
    {
        $result = make_cat_url("Test Category");
        $this->assertStringContainsString("<a target='_blank' href='https://mdwiki.org/wiki/Category:Test_Category'>Test Category</a>", $result);

        $result2 = make_cat_url("");
        $this->assertEquals("", $result2);
    }

    public function testMakeTalkUrl()
    {
        $result = make_talk_url("ar", "Mr. User");
        $this->assertStringContainsString("<a target='_blank' href='//ar.wikipedia.org/w/index.php?title=User_talk:Mr.%20User'>talk</a>", $result);
    }

    public function testMakeMdwikiUserUrl()
    {
        $result = make_mdwiki_user_url("Test User");
        $this->assertStringContainsString("<a href='https://mdwiki.org/wiki/User:Test_User'>Test User</a>", $result);
    }

    public function testMakeTargetUrl()
    {
        $result = make_target_url("Target Page", "ar", "Display Name", true);
        $this->assertStringContainsString("<a target='_blank' href='https://ar.wikipedia.org/wiki/Target_Page'>Display Name</a>", $result);
        $this->assertStringContainsString("(DELETED)", $result);

        $result2 = make_target_url("Page Without Display", "en");
        $this->assertStringContainsString(">Page Without Display</a>", $result2);
    }

    public function testDivAlert()
    {
        $result = div_alert(["Message 1", "Message 2"], "danger");
        $this->assertStringContainsString("alert alert-danger", $result);
        $this->assertStringContainsString("Message 1", $result);
        $this->assertStringContainsString("Message 2", $result);

        $resultEmpty = div_alert([]);
        $this->assertEquals("", $resultEmpty);

        $resultInvalidType = div_alert(["Msg"], "unknown");
        $this->assertStringContainsString("alert alert-secondary", $resultInvalidType);
    }

    public function testMakeEditIconNew()
    {
        // Save state
        $origReq = $_REQUEST;
        $origCookie = $_COOKIE;

        $_REQUEST['test'] = 1;

        $params = ['id' => 123];
        $result = make_edit_icon_new("TestTarget", $params, "Edit Record");

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

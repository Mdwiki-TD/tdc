<?php

namespace Tests\Utils\Html;

use PHPUnit\Framework\TestCase;
use App\Utils\HtmlUrls;

class HtmlUrlsTest extends TestCase
{
    public function testMakeMailIconNew()
    {
        $tab = [
            'user' => 'JohnDoe',
            'lang' => 'en',
            'target' => 'Test_Target',
            'pupdate' => '2023-10-10',
            'title' => 'Page_Title'
        ];
        $result = HtmlUrls::make_mail_icon_new($tab, 'test_func');
        $this->assertStringContainsString("test_func(this)", $result);
        $this->assertStringContainsString("user=JohnDoe", $result);
        $this->assertStringContainsString("lang=en", $result);
        $this->assertStringContainsString("target=Test_Target", $result);
    }

    public function testMakeMdwikiTitle()
    {
        $result1 = HtmlUrls::make_mdwiki_title("Main Page");
        $this->assertStringContainsString("<a target='_blank' href='https://mdwiki.org/wiki/Main_Page'>Main Page</a>", $result1);

        $result2 = HtmlUrls::make_mdwiki_title("");
        $this->assertEquals("", $result2);
    }

    public function testMakeCatUrl()
    {
        $result = HtmlUrls::make_cat_url("Test Category");
        $this->assertStringContainsString("<a target='_blank' href='https://mdwiki.org/wiki/Category:Test_Category'>Test Category</a>", $result);

        $result2 = HtmlUrls::make_cat_url("");
        $this->assertEquals("", $result2);
    }

    public function testMakeTalkUrl()
    {
        $result = HtmlUrls::make_talk_url("ar", "Mr. User");
        $this->assertStringContainsString("<a target='_blank' href='//ar.wikipedia.org/w/index.php?title=User_talk:Mr.%20User'>talk</a>", $result);
    }

    public function testMakeMdwikiUserUrl()
    {
        $result = HtmlUrls::make_mdwiki_user_url("Test User");
        $this->assertStringContainsString("<a href='https://mdwiki.org/wiki/User:Test_User' taget='_blank'>Test User</a>", $result);
    }

    public function testMakeTargetUrl()
    {
        $result = HtmlUrls::make_target_url("Target Page", "ar", "Display Name", true);
        $this->assertStringContainsString("<a target='_blank' href='https://ar.wikipedia.org/wiki/Target_Page'>Display Name</a>", $result);
        $this->assertStringContainsString("(DELETED)", $result);

        $result2 = HtmlUrls::make_target_url("Page Without Display", "en");
        $this->assertStringContainsString(">Page Without Display</a>", $result2);
    }

    public function testMakeEditIconNew()
    {
        // Save state
        $origReq = $_REQUEST;
        $origCookie = $_COOKIE;

        $_REQUEST['test'] = 1;

        $params = ['id' => 123];
        $result = HtmlUrls::make_edit_icon_new("TestTarget", $params, "Edit Record");

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

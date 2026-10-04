<?php

declare(strict_types=1);

namespace Tests\App\Utils;

use App\Utils\HtmlUrls;
use PHPUnit\Framework\TestCase;

class HtmlUrlsTest extends TestCase
{
    public function testMakeMdwikiHref(): void
    {
        $this->assertNull(HtmlUrls::make_mdwiki_href(null));
        $this->assertSame('https://mdwiki.org/wiki/Main_Page', HtmlUrls::make_mdwiki_href('Main Page'));
    }

    public function testMakeMdwikiUserUrl(): void
    {
        $this->assertSame('', HtmlUrls::make_mdwiki_user_url(''));
        $html = HtmlUrls::make_mdwiki_user_url('John Doe');
        $this->assertStringContainsString('https://mdwiki.org/wiki/User:John_Doe', $html);
        $this->assertStringContainsString('John Doe', $html);
    }

    public function testMakeMdwikiArticleUrlBlank(): void
    {
        $this->assertNull(HtmlUrls::make_mdwiki_article_url_blank(null));
        $html = HtmlUrls::make_mdwiki_article_url_blank('Test Title', 'Custom Name');
        $this->assertStringContainsString('href=\'https://mdwiki.org/wiki/Test_Title\'', $html);
        $this->assertStringContainsString('>Custom Name</a>', $html);
    }

    public function testMakeMdwikiCatUrl(): void
    {
        $this->assertNull(HtmlUrls::make_mdwiki_cat_url(null));
        $html = HtmlUrls::make_mdwiki_cat_url('Category:Medicine');
        $this->assertStringContainsString('href=\'https://mdwiki.org/wiki/Category:Medicine\'', $html);
    }

    public function testMakeWikipediaUrlBlank(): void
    {
        $this->assertNull(HtmlUrls::make_wikipedia_url_blank(null, 'en'));

        $html = HtmlUrls::make_wikipedia_url_blank('Heart_attack', 'en', 'Heart Attack', true);
        $this->assertStringContainsString('https://en.wikipedia.org/wiki/Heart_attack', $html);
        $this->assertStringContainsString('(DELETED)', $html);
    }

    public function testMakeWikidataUrlBlank(): void
    {
        $this->assertSame('default_val', HtmlUrls::make_wikidata_url_blank('', '', 'default_val'));

        $html = HtmlUrls::make_wikidata_url_blank('Q12345', 'Item Q12345');
        $this->assertStringContainsString('https://wikidata.org/wiki/Q12345', $html);
        $this->assertStringContainsString('Item Q12345', $html);
    }

    public function testMakeTalkUrl(): void
    {
        $html = HtmlUrls::make_talk_url('en', 'TestUser');
        $this->assertStringContainsString('//en.wikipedia.org/w/index.php?title=User_talk:TestUser', $html);
    }

    public function testMakeTargetUrl(): void
    {
        $html = HtmlUrls::make_target_url('Article_Title', 'fr', 'Display Name', false);
        $this->assertStringContainsString('https://fr.wikipedia.org/wiki/Article_Title', $html);
        $this->assertStringContainsString('Display Name', $html);
    }

    public function testMakeEditIconNew(): void
    {
        $params = ['id' => 123];
        $html = HtmlUrls::make_edit_icon_new('edit_page', $params, 'Edit Article');

        $this->assertStringContainsString('pup-target=\'index.php?ty=edit_page&amp;id=123&amp;nonav=1\'', $html);
        $this->assertStringContainsString('Edit Article', $html);
    }

    public function testMakeMailIconNew(): void
    {
        $record = [
            'user' => 'UserA',
            'lang' => 'en',
            'target' => 'TargetPage',
            'pupdate' => '2023-01-01',
            'title' => 'TitleA'
        ];
        $html = HtmlUrls::make_mail_icon_new($record);

        $this->assertStringContainsString('index.php?ty=msg&amp;user=UserA', $html);
        $this->assertStringContainsString('onclick=\'pup_window_new(this)\'', $html);
    }
}

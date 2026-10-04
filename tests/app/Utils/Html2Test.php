<?php



namespace Tests\App\Utils;

use App\Utils\Html2;
use PHPUnit\Framework\TestCase;

class Html2Test extends TestCase
{
    public function testBannerAlert(): void
    {
        $html = Html2::banner_alert('Warning message');

        $this->assertStringContainsString('alert alert-danger', $html);
        $this->assertStringContainsString('Warning message', $html);
    }

    public function testMakeProjectToUser(): void
    {
        $projects = ['Project A' => 1, 'Project B' => 2];
        $html = Html2::make_project_to_user('Project A', $projects);

        $this->assertStringContainsString('<option value=\'Uncategorized\'>Uncategorized</option>', $html);
        $this->assertStringContainsString('<option value=\'Project A\' selected>Project A</option>', $html);
        $this->assertStringContainsString('<option value=\'Project B\' >Project B</option>', $html);
    }

    public function testMakeInputGroup(): void
    {
        $html = Html2::make_input_group('Username', 'user_id', 'John', 'required');

        $this->assertStringContainsString('<span class=\'input-group-text\'>Username</span>', $html);
        $this->assertStringContainsString('name=\'user_id\' value=\'John\' required', $html);
    }

    public function testMakeInputGroupNoCol(): void
    {
        $html = Html2::make_input_group_no_col('Email', 'email_id', 'john@example.com', '');

        $this->assertStringContainsString('<span class=\'input-group-text\'>Email</span>', $html);
        $this->assertStringContainsString('name=\'email_id\' value=\'john@example.com\'', $html);
    }

    public function testMakeCard(): void
    {
        $html = Html2::makeCard('Card Title', '<div>Card Body</div>');

        $this->assertStringContainsString('Card Title', $html);
        $this->assertStringContainsString('<div>Card Body</div>', $html);
    }

    public function testMakeColSmBody(): void
    {
        $html = Html2::make_col_sm_body('Main Title', 'Subtitle', '<div>Table</div>', 6);

        $this->assertStringContainsString('col-md-6', $html);
        $this->assertStringContainsString('Main Title', $html);
        $this->assertStringContainsString('Subtitle', $html);
        $this->assertStringContainsString('<div>Table</div>', $html);
    }

    public function testMakeDrop(): void
    {
        $options = ['Option 1' => 'opt1', 'Option 2' => 'opt2'];
        $html = Html2::make_drop($options, 'opt2');

        $this->assertStringContainsString('<option value=\'opt1\' >Option 1</option>', $html);
        $this->assertStringContainsString('<option value=\'opt2\' selected>Option 2</option>', $html);
    }

    public function testMakeDatalistOptions(): void
    {
        $languages = ['English' => 'en', 'Spanish' => 'es'];
        $html = Html2::make_datalist_options($languages);

        $this->assertStringContainsString('<option value=\'en\'>English</option>', $html);
        $this->assertStringContainsString('<option value=\'es\'>Spanish</option>', $html);
    }

    public function testDivAlert(): void
    {
        $messages = ['Message 1', 'Message 2'];
        $html = Html2::div_alert($messages, 'success');

        $this->assertStringContainsString('alert alert-success', $html);
        $this->assertStringContainsString('Message 1<br>Message 2<br>', $html);
    }

    public function testDivAlertEmptyReturnsEmpty(): void
    {
        $this->assertSame('', Html2::div_alert([]));
    }
}

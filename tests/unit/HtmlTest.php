<?php

declare(strict_types=1);

namespace Tables\SqlTables {
    class TablesSql
    {
        public static array $s_projects_title_to_id = [];
    }
}

namespace {
    use Tests\Support\TestRunner;
    use function Tests\Support\assertSame;
    use function Tests\Support\assertStringContains;
    use function Utils\Html\banner_alert;
    use function Utils\Html\makeDropdown;
    use function Utils\Html\makeCard;
    use function Utils\Html\make_form_check_input;
    use function Utils\Html\make_input_group;
    use function Utils\Html\make_input_group_no_col;
    use function Utils\Html\make_mail_icon_new;
    use function Utils\Html\make_project_to_user;

    require_once __DIR__ . '/../../src/utils/html.php';

    return static function (TestRunner $runner): void {
        $runner->add('make_mail_icon_new builds a clickable mail link', static function (): void {
            $tab = [
                'user' => 'DocEditor',
                'lang' => 'fr',
                'target' => 'Cancer',
                'pupdate' => '2023-01-02',
                'title' => 'Cancer (medicine)',
            ];
            $html = make_mail_icon_new($tab);
            assertStringContains("class='btn btn-outline-primary btn-sm spannowrap'", $html);
            assertStringContains("pup-target='index.php?ty=Emails/msg&user=DocEditor&lang=fr&target=Cancer&date=2023-01-02&title=Cancer+%28medicine%29&nonav=1'", $html);
            assertStringContains("onclick='pup_window_new(this)'", $html);
        });

        $runner->add('make_form_check_input renders label and checkbox state', static function (): void {
            $html = make_form_check_input('Enabled', 'publish', '1', '0', 'checked');
            assertStringContains("<label class='form-check-label' for='publish'>Enabled</label>", $html);
            assertStringContains("<input class='form-check-input' type='checkbox' name='publish' value='1' checked>", $html);
        });

        $runner->add('make_input_group escapes provided values', static function (): void {
            $html = make_input_group('Title', 'title', 'A "quote" & text', 'required');
            assertStringContains('A &quot;quote&quot; &amp; text', $html);
            assertStringContains("name='title'", $html);
        });

        $runner->add('make_input_group_no_col shares escaping behaviour', static function (): void {
            $html = make_input_group_no_col('Title', 'title', "B<'text'>", '');
            assertStringContains('B&lt;&#039;text&#039;&gt;', $html);
            assertStringContains("class='input-group mb-3'", $html);
        });

        $runner->add('makeDropdown marks the selected option and includes helper item', static function (): void {
            $html = makeDropdown(['Alpha', 'Beta'], 'Beta', 'category', 'all');
            assertStringContains("<option value='all' >All</option>", $html);
            assertStringContains("<option value='Beta' selected>Beta</option>", $html);
        });

        $runner->add('make_project_to_user builds option list with selection', static function (): void {
            \Tables\SqlTables\TablesSql::$s_projects_title_to_id = [
                'Project A' => 10,
                'Project B' => 20,
            ];
            $html = make_project_to_user('Project B');
            assertStringContains("<option value='Project A' >Project A</option>", $html);
            assertStringContains("<option value='Project B' selected>Project B</option>", $html);
        });

        $runner->add('banner_alert wraps the message in an alert container', static function (): void {
            $html = banner_alert('Review required');
            assertStringContains("alert alert-danger", $html);
            assertStringContains("bi bi-exclamation-triangle", $html);
            assertStringContains('Review required', $html);
        });

        $runner->add('makeCard creates a bootstrap card with provided content', static function (): void {
            $html = makeCard('Summary', '<p>Content</p>');
            assertStringContains("<div class=\"card\">", $html);
            assertStringContains('Summary', $html);
            assertStringContains('<p>Content</p>', $html);
        });
    };
}

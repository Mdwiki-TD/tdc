<?php
// src/app/coordinator/admin/settings/index.php

namespace App\Coordinator\Admin\settings;

use App\User\CurrentUser;
use function App\SQLorAPI\Funcs\get_td_or_sql_settings;
use function App\csrf\generate_csrf_token;

require_once __DIR__ . '/post.php';

/**
 * Class SettingsIndexController
 * Renders the editable site settings form. On POST, delegates to
 * SettingsPostProcessor first, then always renders the current state
 * of the form below it.
 */
class SettingsIndexController
{
    /**
     * Handles authentication and executes controller output.
     */
    public function handleRequest(): void
    {
        // Check user authorization
        if (!CurrentUser::getInstance()->isCoordinator()) {
            header('Location: /index.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $postProcessor = new SettingsPostProcessor();
            $postProcessor->handle();
        }

        $settings = get_td_or_sql_settings();

        $text = $this->buildSettingsTable($settings);

        $this->renderCard($text);
    }

    /**
     * Builds the settings table markup, skipping ignored rows and
     * rendering a checkbox or a text input depending on the setting type.
     */
    private function buildSettingsTable(array $tabe): string
    {
        $nn = 0;
        $tab = <<<HTML
            <table class='table table-striped compact table-mobile-responsive table-mobile-sided'>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Key</th>
                        <th>Option</th>
                        <th>Value</th>
                    </tr>
                </thead>
                <tbody>
        HTML;

        foreach ($tabe as $key => $v) {
            $ignored = $v['ignored'] ?? 0;

            if ($ignored == 1 || $ignored == "1") {
                continue;
            }

            $id        = $v['id'] ?? '';
            $title     = $v['title'] ?? '';
            $displayed = $v['displayed'] ?? '';
            $value     = $v['value'] ?? '';

            $nn++;

            $type = $v['type'] ?? $v['Type'] ?? '';

            $valueLine = <<<HTML
                <input class='form-control' size='4' name='rows[$nn][value]' value='$value'/>
            HTML;

            if ($type == 'check') {
                $checked = ($value == 1 || $value == "1") ? 'checked' : '';
                $valueLine = <<<HTML
                    <div class='form-check form-switch'>
                        <input type='hidden' name='rows[$nn][value]' value='0'>
                        <input class='form-check-input' type='checkbox' name='rows[$nn][value]' value='1' $checked>
                    </div>
                HTML;
            }

            $tr = <<<HTML
                <tr>
                    <input name='rows[$nn][id]' value='$id' type="hidden"/>
                    <td data-order='$nn' data-content='#'>
                        $nn
                    </td>
                    <td data-content='Key'>
                        $title
                    </td>
                    <td data-content='Option'>
                        $displayed
                        <!-- <input class='form-control' name='rows[$nn][title]' value="$title" type="hidden"/>
                        <input class='form-control' name='rows[$nn][displayed]' value='$displayed' type="hidden"/> -->
                    </td>
                    <td data-content='Value'>
                        $valueLine
                        <!-- <input class='form-control' name='rows[$nn][type]' value='$type' type="hidden"/> -->
                    </td>
                </tr>
            HTML;

            $tab .= $tr;
        }

        return <<<HTML
            <div class='form-group'>
                    $tab
                    </tbody>
                </table>
            </div>
        HTML;
    }

    /**
     * Renders the settings card with the editable form.
     */
    private function renderCard(string $text): void
    {
        $csrfToken = generate_csrf_token();

        echo <<<HTML
            <div class='card'>
                <div class='card-header'>
                    <h4>Settings:</h4>
                </div>
                <div class='card-body'>
                    <div class='row'>
                        <form action='index.php' method="POST">
                            <input name='csrf_token' value="$csrfToken" type="hidden"/>
                            <input name='ty' value='settings' type="hidden"/>
                        $text
                        <button type='submit' class='btn btn-outline-primary'>Save</button>
                    </form>
                </div>
            </div>
        HTML;
    }
}

// Instantiate and execute controller
$controller = new SettingsIndexController();
$controller->handleRequest();

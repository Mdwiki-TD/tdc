<?php
// src/app/Coordinator/Admin/settings/index.php

namespace App\Coordinator\Admin\Settings;

use App\SQLorAPI\SettingsTable;
use App\Coordinator\Admin\Common\AbstractController;
use App\Coordinator\Admin\Settings\SettingsPostProcessor;
use App\Utils\HtmlUrls;
/**
 * Class SettingsIndexController
 * Renders the editable site settings form. On POST, delegates to
 * SettingsPostProcessor first, then always renders the current state
 * of the form below it.
 */
class SettingsIndexController extends AbstractController
{
    public bool $forAdminOnly = true;
    public function __construct()
    {
        parent::__construct();
    }
    protected function createPostProcessor(): object
    {
        return new SettingsPostProcessor();
    }
    public function renderFormCard(): void
    {
        $settings = (SettingsTable::getInstance())->getSettings();

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
        $table = <<<HTML
            <table class='table table-striped compact table-mobile-responsive table-mobile-sided'>
                <thead>
                    <tr>
                        <th>#</th>
                        <!-- <th>Key</th> -->
                        <th>Setting</th>
                        <th>Enabled</th>
                        <th>Edit</th>
                    </tr>
                </thead>
                <tbody>
        HTML;

        foreach ($tabe as $key => $v) {
            $ignored = $v['ignored'] ?? 0;

            if ($ignored == 1 || $ignored == "1") {
                continue;
            }

            $id = htmlspecialchars((string)($v['id'] ?? ''), ENT_QUOTES, 'UTF-8');
            $displayed = htmlspecialchars((string)($v['displayed'] ?? ''), ENT_QUOTES, 'UTF-8');
            $type = strtolower((string)($v['Type'] ?? $v['type'] ?? 'check'));

            $nn++;

            if ($type === 'check') {
                $value = $this->boolInt($v['value'] ?? 0);
                $checked = $this->isChecked($value);
                $valueLine = <<<HTML
                    <div class='form-check form-switch'>
                        <input type='hidden' name='rows[$nn][value]' value='0'>
                        <input class='form-check-input' type='checkbox' name='rows[$nn][value]' value='1' $checked>
                    </div>
                HTML;
            } else {
                $value = htmlspecialchars((string)($v['value'] ?? ''), ENT_QUOTES, 'UTF-8');
                $valueLine = <<<HTML
                    <input class='form-control' type='text' name='rows[$nn][value]' value='$value'>
                HTML;
            }

            // Add link to EditSettingsController
            // $editIcon = HtmlUrls::make_edit_icon_new('edit_setting', ['id' => $id], 'Edit');
            $editUrl = "index.php?ty=edit_setting&id={$id}&nonav=1";
            $escapedUrl = htmlspecialchars($editUrl, ENT_QUOTES, 'UTF-8');
            $editIcon = <<<HTML
                <a class='btn btn-outline-primary btn-sm' pup-target='{$escapedUrl}' onclick='pup_window_new(this)'>Edit</a>
            HTML;

            $tr = <<<HTML
                <tr>
                    <input name='rows[$nn][id]' value='$id' type="hidden"/>
                    <td data-order='$nn' data-content='#'> $nn </td>
                    <td data-content='Option'> $displayed </td>
                    <td data-content='Value'> $valueLine </td>
                    <td> $editIcon </td>
                </tr>
            HTML;

            $table .= $tr;
        }

        return <<<HTML
            <div class='form-group'>
                    $table
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

        $body = <<<HTML
            <div class='row'>
                <form action='index.php' method="POST">
                    {$this->createCsrfTokenField()}
                    <input name='ty' value='settings' type="hidden"/>
                    $text
                    <button type='submit' class='btn btn-outline-primary'>Save</button>
                </form>
            </div>
        HTML;

        $this->echoCard("Settings:", $body);
    }
}



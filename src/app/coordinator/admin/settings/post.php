<?php
// src/app/coordinator/admin/settings/post.php

namespace App\Coordinator\Admin\settings;

use App\User\CurrentUser;
use function App\APICalls\MdwikiSql\update_settings_value;
use function App\csrf\verify_csrf_token;

/**
 * Class SettingsPostProcessor
 * Handles bulk update of settings values.
 */
class SettingsPostProcessor
{
    /**
     * Validates and processes the incoming submission.
     */
    public function handle(): void
    {
        // Check user authorization
        if (!CurrentUser::getInstance()->isCoordinator()) {
            header('Location: /index.php');
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            exit;
        }

        $closeBtn = $this->getCloseButtonHtml();

        if (!verify_csrf_token()) {
            echo "<div class='alert alert-danger' role='alert'>Invalid or Reused CSRF Token!</div>";
            echo $closeBtn;
            return;
        }

        $this->processRows($_POST['rows'] ?? []);
    }

    /**
     * Updates each submitted setting's value.
     */
    private function processRows(array $rows): void
    {
        foreach ($rows as $key => $table) {
            $id    = $table['id'] ?? '';
            $value = $table['value'] ?? '';
            $title     = $table["title"] ?? '';
            $displayed = $table["displayed"] ?? '';
            $type      = $table["type"] ?? '';

            // if (empty($title) || empty($displayed) || empty($type)) continue;
            // $re = update_settings($id, $title, $displayed, $value, $type);
            // Don't use empty() for value because it can be 0 or "0" which is valid,
            // but empty() would treat it as empty.
            if (empty($id) || $value === "") {
                continue;
            }

            $re = update_settings_value($id, $value);
        }
    }

    /**
     * Generates a close button HTML block.
     */
    private function getCloseButtonHtml(): string
    {
        return <<<HTML
            <div class="aligncenter">
                <a class="btn btn-outline-primary" onclick="window.close()">Close</a>
            </div>
        HTML;
    }
}

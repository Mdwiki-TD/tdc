<?php
// src/app/coordinator/admin/settings/settings_post.php

namespace App\Coordinator\Admin\settings;

use App\Coordinator\Admin\Common\AbstractPostHandler;
use function App\MdwikiSql\update_settings_value;
use function App\csrf\verify_csrf_token;

/**
 * Class SettingsPostProcessor
 * Handles bulk update of settings values.
 */
class SettingsPostProcessor extends AbstractPostHandler
{
    protected bool $isPopUpPage = false;

	public function process(array $post): void
	{
        $this->processRows($post['rows'] ?? []);
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
}

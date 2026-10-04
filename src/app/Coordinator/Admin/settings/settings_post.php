<?php
// src/app/Coordinator/Admin/settings/settings_post.php

namespace App\Coordinator\Admin\Settings;

use App\Coordinator\Admin\Common\AbstractPostHandler;

/**
 * Class SettingsPostProcessor
 * Handles bulk update of settings values.
 */
class SettingsPostProcessor extends AbstractPostHandler
{
    protected bool $isPopUpPage = false;

    public function __construct()
    {
        parent::__construct();
    }
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

            $re = $this->update_settings_value($id, $value);
        }
    }

    private function update_settings_value($id, $value)
    {
        // Create a new database object
        if ($id == 0 || $id == '0' || empty($id)) {
            return;
        }

        $query = <<<SQL
            UPDATE settings SET value = ? WHERE id = ?
        SQL;
        $params = [$value, $id];

        // Prepare and execute the SQL query with parameter binding
        $results = $this->db->executeQuery($query, $params);

        return $results;
    }

}

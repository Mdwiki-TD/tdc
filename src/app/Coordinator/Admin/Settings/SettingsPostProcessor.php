<?php
// src/app/Coordinator/Admin/settings/settings_post.php

namespace App\Coordinator\Admin\Settings;

use App\SQLorAPI\SettingsTable;
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
        if (!$this->validateAdmin()) {
            $this->exit();
            return;
        }
        $this->processRows($post['rows'] ?? []);
    }

    /**
     * Updates each submitted setting's value.
     */
    private function processRows(array $rows): void
    {
        $settings = SettingsTable::getInstance();

        foreach ($rows as $key => $table) {
            $id = $table['id'] ?? '';
            $value = $table['value'] ?? '';

            $data = $settings->getSettingsByid($id);
            if (empty($data)) {
                $this->addError("Invalid setting ID: $id.");
                continue;
            }

            // if (empty($title) || empty($displayed) || empty($type)) continue;
            // $re = update_settings($id, $title, $displayed, $value, $type);
            // Don't use empty() for value because it can be 0 or "0" which is valid,
            // but empty() would treat it as empty.
            if ($value === "") {
                continue;
            }
            if ($value == $data['value']) {
                // $this->addWarning("No changes made to setting ID: $id.");
                continue;
            }

            $result = $this->update_settings_value($id, $value);

			if ($result === false) {
				$this->addError("Failed to update setting with id: $id.");
			} else {
				$this->addText("setting updated successfully, id: $id.");
			}
        }
    }

    private function update_settings_value(mixed $id, mixed $value): bool
    {
        // Create a new database object
        if ($id == 0 || $id == '0' || empty($id)) {
            return false;
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

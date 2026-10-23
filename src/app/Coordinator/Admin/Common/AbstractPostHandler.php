<?php
// src/app/Coordinator/Admin/common/AbstractPostHandler.php

namespace App\Coordinator\Admin\Common;

use App\CSRFManager;
use App\Coordinator\Admin\Common\BaseController;


/**
 * Class AbstractPostHandler
 *
 * Base class for controllers' POST handlers. Centralizes the common
 * bits: CSRF verification, error/success message collection, and a
 * small helper for parsing checkbox-style 0/1 ints. Subclasses only
 * need to implement process() with their own delete/update/insert logic.
 */
abstract class AbstractPostHandler extends BaseController
{
    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Subclasses implement their own delete/update/insert logic here,
     * filling $this->errors / $this->texts as needed.
     */
    abstract protected function process(array $post): void;

    public function deleteRow(string $id, string $tableName, string $recordName = "User", string $columnName = "id"): void
    {
        $qua2 = "DELETE FROM {$tableName} WHERE {$columnName} = ?";

        $result = $this->db->executeQuery($qua2, [$id]);
        $this->verifyDeleteResult($result, $id, $recordName, $columnName);
    }

    public function deleteById(string $table, string $idColumn, mixed $id, string $label): void
    {
        $this->deleteRow((string)$id, $table, $label, $idColumn);
    }

    public function reportResult(bool $result, string $okMsg, string $failMsg): void
    {
        if ($result === false) {
            $this->addError($failMsg);
        } else {
            $this->addText($okMsg);
        }
    }
    /**
     * Verifies CSRF, then delegates to process() if valid.
     *
     * @return array{success: bool, csrfError: bool, errors: string[], texts: string[]}
     */
    final public function handle(array $post): array
    {
        if (!$this->validateCoordinator()) {
            $this->addError("User is not authorized.");
            $this->exit();
            return $this->result();
        }
        $manager = new CSRFManager();
        if (!$manager->verifyToken($_POST['csrf_token'] ?? null)) {
            $this->csrfError = true;
            return $this->result();
        }

        // Delegate POST requests to the POST processor
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            error_log('Invalid request method');
            $this->addError("Invalid request method.");
            return $this->result();
        }

        $this->process($post);

        return $this->result();
    }

    public function verifyDeleteResult(bool $deleted, string|int $id, string $recordName, string $columnName = "id"): void
    {
        if ($deleted === false) {
            $this->addError("Failed to delete {$recordName} ({$columnName}: {$id}).");
            // $this->addError("Failed to delete {$recordName} with {$columnName} ($id).");
        } else {
            $this->addText("{$recordName} ({$columnName}: {$id}) deleted successfully.");
            // $this->addText("{$recordName} with {$columnName} {$id} deleted.");
        }
    }
    public function verifyUpdateResult(bool $updated, string|int $id, string $recordName, string $columnName = "id"): void
    {
        if ($updated === false) {
            $this->addError("Failed to update {$recordName} ({$columnName}: {$id}).");
            // $this->addError("Failed to update {$recordName} with {$columnName} ($id).");
        } else {
            $this->addText("{$recordName} ({$columnName}: {$id}) updated successfully.");
            // $this->addText("{$recordName} with {$columnName} {$id} updated.");
        }
    }
    public function verifyInsertResult(bool $result, string|int $id, string $recordName, string $columnName = "id"): void
    {
        if ($result === false) {
            $this->addError("Failed to insert {$recordName} ({$columnName}: {$id}).");
            // $this->addError("Failed to insert {$recordName} with {$columnName} ($id).");
        } else {
            $this->addText("{$recordName} ({$columnName}: {$id}) inserted successfully.");
            // $this->addText("{$recordName} with {$columnName} {$id} inserted.");
        }
    }
}

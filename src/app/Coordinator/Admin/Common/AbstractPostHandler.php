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

    /**
     * Subclasses implement their own delete/update/insert logic here,
     * filling $this->errors / $this->texts as needed.
     */
    abstract protected function process(array $post): void;

}

<?php
// src/app/Coordinator/Admin/common/AbstractControllerNoPost.php

namespace App\Coordinator\Admin\Common;

use App\Coordinator\Admin\Common\BaseController;

/**
 * Class AbstractControllerNoPost
 *
 * Base class for controllers handlers.
 */
abstract class AbstractControllerNoPost extends BaseController
{
    public bool     $forCoordinators = true;

    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Handles authentication and executes controller output.
     */

    // abstract function handleRequest(): void;
    abstract function processRequest(): void;

    public function handleRequest(): void
    {
        if ($this->forCoordinators && !$this->validateCoordinator()) {
            $this->exit();
            return;
        }
        $this->processRequest();
    }
    /**
     * Renders the card wrapping the form.
     */
    public function echoBs5Card(string $header, string $body, string $bodyClass = ""): void
    {
        echo <<<HTML
            <div class='card'>
                <div class='card-header'>
                    $header
                </div>
                <div class='card-body $bodyClass'>
                    $body
                </div>
            </div>
        HTML;
    }
}

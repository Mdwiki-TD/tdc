<?php
// src/app/Coordinator/Admin/common/AbstractControllerNoPost.php

namespace App\Coordinator\Admin\Common;

use App\MdwikiSql\Database;
use App\User\CurrentUser;
use App\Coordinator\Admin\Common\BaseController;

/**
 * Class AbstractControllerNoPost
 *
 * Base class for controllers handlers.
 */
abstract class AbstractControllerNoPost extends BaseController
{
    public Database $db;

    public function __construct()
    {
        $this->db = new Database();
    }
    /**
     * Handles authentication and executes controller output.
     */

    abstract function handleRequest(): void;

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

<?php
// src/app/Coordinator/Admin/common/AbstractController.php

namespace App\Coordinator\Admin\Common;

use App\MdwikiSql\Database;
use App\User\CurrentUser;
use App\CSRFManager;
use App\Coordinator\Admin\Common\BaseController;

/**
 * Class AbstractController
 *
 * Base class for controllers handlers.
 */
abstract class AbstractController extends BaseController
{
    public bool $forAdminOnly = false;

    public function __construct()
    {
        parent::__construct();
    }
    /**
     * Handles authentication and executes controller output.
     */

    public function handleRequest(): void
    {
        if ($this->forAdminOnly && !$this->validateAdmin()) {
            $this->exit();
            return;
        }

        // Validate coordinator
        if (!$this->validateCoordinator()) {
            $this->exit();
            return;
        }

        // Delegate POST requests to the POST processor
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostRequest();
        }
        // Handle GET request and render view
        $this->renderFormCard();
    }
    abstract protected function createPostProcessor(): object;

    abstract protected function renderFormCard(): void;

    public function handlePostRequest(): void
    {
        // Instantiate and execute processor
        $postProcessor = $this->createPostProcessor();
        $result = $postProcessor->handle($_POST);
        $postProcessor->RenderMesseges($result);

        if ($postProcessor->shouldShowForm()) {
            $this->renderFormCard();
        }
    }

    public function createCsrfTokenField(): string
    {
        $csrfToken = (new CSRFManager())->generateToken();

        return <<<HTML
            <input name='csrf_token' value="$csrfToken" type="hidden"/>
        HTML;
    }

    /**
     * Renders the card wrapping the form.
     */
    public function echoCard(string $headerTitle, string $body): void
    {
        echo <<<HTML
            <div class='card'>
                <div class='card-header'>
                    <h4>$headerTitle</h4>
                </div>
                <div class='card-body'>
                    $body
                </div>
            </div>
        HTML;
    }
}

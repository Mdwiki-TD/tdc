<?php
// src/app/coordinator/admin/common/AbstractController.php

namespace App\Coordinator\Admin\Common;

use App\User\CurrentUser;
use function App\csrf\generate_csrf_token;

/**
 * Class AbstractController
 *
 * Base class for controllers handlers.
 */
abstract class AbstractController
{
    public function validateCoordinator(): void
    {
        // Validate user authorization
        if (!CurrentUser::getInstance()->isCoordinator()) {
            // return to home page
            header('Location: /index.php');
            exit;
        }
    }
    /**
     * Handles authentication and executes controller output.
     */

    public function handleRequest(): void
    {
        $this->validateCoordinator();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostRequest();
        }

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
        $csrfToken = generate_csrf_token();

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

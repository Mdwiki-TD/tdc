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
     * Generates a close button HTML block.
     */
    public function getCloseButtonHtml(): string
    {
        return <<<HTML
            <div class="aligncenter">
                <a class="btn btn-outline-primary" onclick="window.close()">Close</a>
            </div>
        HTML;
    }
    public function createCsrfTokenField(): string
    {
		$csrfToken = generate_csrf_token();

        return <<<HTML
            <input name='csrf_token' value="$csrfToken" type="hidden"/>
        HTML;
    }

    /**
     * Renders UI scripts to isolate the modal/page layout.
     */
    public function renderHeaderScripts(): void
    {
        echo '</div><script>
            $("#mainnav").hide();
            $("#maindiv").hide();
        </script>
        <div class="container-fluid">';
    }
    /**
     * Standardized template method for popup window controllers.
     * Validates authorization, renders header scripts, and handles POST/GET routing.
     */
    public function handlePopupRequest(callable $formRenderer, ?callable $postHandler = null): void
    {
        $this->validateCoordinator();
        $this->renderHeaderScripts();

        if ($_SERVER['REQUEST_METHOD'] === 'POST' && $postHandler !== null) {
            $postHandler();
        } else {
            $formRenderer();
        }
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

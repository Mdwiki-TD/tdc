<?php
// src/app/coordinator/admin/common/AbstractControllerNoPost.php

namespace App\Coordinator\Admin\Common;

use App\User\CurrentUser;

/**
 * Class AbstractControllerNoPost
 *
 * Base class for controllers handlers.
 */
abstract class AbstractControllerNoPost
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

    abstract function handleRequest(): void;


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

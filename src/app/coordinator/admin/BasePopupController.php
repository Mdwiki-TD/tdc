<?php
// src/app/coordinator/admin/BasePopupController.php

namespace App\Coordinator\Admin;

use App\User\CurrentUser;

/**
 * Class BasePopupController
 * Abstract base controller for popup modal windows in the coordinator admin section.
 * Provides common functionality such as coordinator authorization check,
 * rendering script tags that hide background navigation elements, and card container wrappers.
 */
abstract class BasePopupController
{
    /**
     * Checks if current user is authorized as coordinator.
     * Redirects to index page and terminates execution if unauthorized.
     */
    protected function checkAuthorization(): void
    {
        if (!CurrentUser::getInstance()->isCoordinator()) {
            header('Location: /index.php');
            exit;
        }
    }

    /**
     * Renders header scripts that hide main navigation and main div elements,
     * opening a container-fluid element for the popup content.
     *
     * @param string $extraHtml Optional additional HTML/assets (e.g. scripts/stylesheets)
     * @param string $containerId Optional DOM ID for the opening container-fluid div
     */
    protected function renderHeaderScripts(string $extraHtml = '', string $containerId = ''): void
    {
        echo "</div>";

        if ($extraHtml !== '') {
            echo $extraHtml;
        }

        $idAttr = ($containerId !== '') ? " id='" . htmlspecialchars($containerId, ENT_QUOTES, 'UTF-8') . "'" : '';

        echo <<<HTML
        <script>
            $("#mainnav").hide();
            $("#maindiv").hide();
        </script>
        <div{$idAttr} class="container-fluid">
        HTML;
    }

    /**
     * Renders a Bootstrap card container with a header title and body content.
     *
     * @param string $title Header title HTML/text
     * @param string $bodyHtml Inner body HTML content
     */
    protected function renderCard(string $title, string $bodyHtml): void
    {
        echo <<<HTML
            <div class='card'>
                <div class='card-header'>
                    <h4>{$title}</h4>
                </div>
                <div class='card-body'>
                    {$bodyHtml}
                </div>
            </div>
        HTML;
    }

    /**
     * Abstract request handler to be implemented by child popup controllers.
     */
    abstract public function handleRequest(): void;
}

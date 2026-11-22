<?php
// src/app/Coordinator/Admin/common/AbstractEditController.php

namespace App\Coordinator\Admin\Common;

/**
 * Class AbstractEditController
 *
 * Base class for controllers handlers.
 */
abstract class AbstractEditController extends AbstractController
{
    public function __construct()
    {
        parent::__construct();
    }

    abstract protected function buildForm(): string;

    abstract protected function getCardTitle(): string;

    // ---------- template method ----------
    /**
     * Executes authorization check and handles the incoming request.
     */
    final public function handleRequest(): void
    {
        if (!$this->validateCoordinator()) {
            $this->exit();
            return;
        }
        $this->renderHeaderScripts();

        // Delegate POST requests to the POST processor
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostRequest();
            return;
        }
        // Handle GET request and render view
        $this->renderFormCard();
    }

    protected function renderFormCard(): void
    {
        if (!$this->verifyRequest()) {
            return;
        }
        $this->beforeCard();
        $this->echoCard($this->getCardTitle(), $this->buildForm());
    }

    /** Hook: e.g. fix_page prints a duplicate-page alert before the card. */
    protected function beforeCard(): void
    {
    }
    protected function verifyRequest(): bool
    {
        return true;
    }

    /**
     * Renders UI scripts to isolate the modal/page layout.
     */
    public function renderHeaderScripts(): void
    {
        echo '</div><div class="container-fluid">';
    }
}

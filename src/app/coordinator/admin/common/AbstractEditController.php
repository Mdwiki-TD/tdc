<?php
// src/app/coordinator/admin/common/AbstractEditController.php

namespace App\Coordinator\Admin\Common;

use function App\csrf\generate_csrf_token;

/**
 * Base for popup "edit" pages: validate → isolate layout → (POST | GET).
 *
 * Child classes implement:
 *   - createPostProcessor(): object   (must expose handle(), RenderMesseges(), optionally shouldShowForm())
 *   - buildForm(): string             (HTML of the <form>)
 *   - getCardTitle(): string
 */
abstract class AbstractEditController extends AbstractController
{
    abstract protected function createPostProcessor(): object;

    abstract protected function buildForm(): string;

    abstract protected function getCardTitle(): string;

    /**
     * Some processors (e.g. EditPagePostHandler) don't re-show the form after POST.
     * Override to return false in that case.
     */
    protected function reshowFormAfterPost(): bool
    {
        return true;
    }
    private function renderHeaderScripts(): void
    {
        echo '</div><script>
            $("#mainnav").hide();
            $("#maindiv").hide();
        </script>
        <div class="container-fluid">';
    }

    // ---------- template method ----------
    /**
     * Executes authorization check and handles the incoming request.
     */
    final public function handleRequest(): void
    {
        $this->validateCoordinator();
        $this->renderHeaderScripts();

        // Delegate POST requests to the POST processor
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->handlePostRequest();
            return;
        }
        // Handle GET request and render view
        $this->renderFormCard();
    }
    public function handlePostRequest(): void
    {
        // Instantiate and execute processor
        $postProcessor = $this->createPostProcessor();
        $result = $postProcessor->handle($_POST);
        $postProcessor->RenderMesseges($result);

        $show = $this->reshowFormAfterPost()
            && (!method_exists($postProcessor, 'shouldShowForm') || $postProcessor->shouldShowForm());

        if ($show) {
            $this->renderFormCard();
        }
    }

    protected function renderFormCard(): void
    {
        $this->beforeCard();
        $this->echoCard($this->getCardTitle(), $this->buildForm());
    }

    /** Hook: e.g. fix_it prints a duplicate-page alert before the card. */
    protected function beforeCard(): void {}

    // ---------- helpers for children ----------

    /** Pre-configured builder: action, csrf, edit=1. */
    protected function newForm(string $ty, array $query = []): FormBuilder
    {
        return FormBuilder::make($ty, $query)
            ->csrf(generate_csrf_token())
            ->hidden('edit', 1);
    }
}

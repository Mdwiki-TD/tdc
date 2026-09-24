<?php
// src/app/coordinator/admin/common/AbstractEditController.php

namespace App\Coordinator\Admin\Common;

/**
 * Class AbstractEditController
 *
 * Base class for controllers handlers.
 */
abstract class AbstractEditController extends AbstractController
{

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

}

<?php
// src/app/coordinator/admin/common/AbstractController.php

namespace App\Coordinator\Admin\Common;

use App\User\CurrentUser;
use function App\Utils\Html\div_alert;

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
            exit;
        }
    }
}

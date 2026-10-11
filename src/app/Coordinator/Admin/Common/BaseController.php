<?php


namespace App\Coordinator\Admin\Common;

use App\User\CurrentUser;
use App\NotFound;

class BaseController
{
    public function __construct()
    {
        // TODO: Implement __construct() method.
    }

    public function validateCoordinator(): bool
    {
        // Validate Coordinator user authorization
        if (!CurrentUser::getInstance()->isCoordinator()) {
            // return to home page
            error_log('User is not authorized');
            return false;
        }
        return true;
    }
    public function validateAdmin(): bool
    {
        // Validate admin user authorization
        if (!CurrentUser::getInstance()->isAdmin()) {
            // return to home page
            error_log('User is not authorized');
            return false;
        }
        return true;
    }
    public function exit()
    {
        // return to home page
        NotFound::render();
        // header('Location: /index.php');
    }
}

<?php

declare(strict_types=1);

namespace Tests\App\Controllers;

use App\Controllers\AppRouter;
use App\User\CurrentUser;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(AppRouter::class)]
class AppRouterTest extends TestCase
{
    private array $origServer;
    private array $origGet;
    private array $origPost;

    protected function setUp(): void
    {
        parent::setUp();
        $this->origServer = $_SERVER;
        $this->origGet = $_GET;
        $this->origPost = $_POST;
    }

    protected function tearDown(): void
    {
        $_SERVER = $this->origServer;
        $_GET = $this->origGet;
        $_POST = $this->origPost;
        parent::tearDown();
    }

    public function testResolveTyNonCoordinator(): void
    {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_GET['ty'] = 'process';

        $currentUser = $this->createMock(CurrentUser::class);
        $currentUser->method('isCoordinator')->willReturn(false);
        $currentUser->method('isAdmin')->willReturn(false);

        $router = new AppRouter($currentUser);
        $ty = $router->resolveTy();

        $this->assertSame('process', $ty);
    }

    public function testResolveTyCoordinatorDefault(): void
    {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        unset($_GET['ty'], $_POST['ty']);

        $currentUser = $this->createMock(CurrentUser::class);
        $currentUser->method('isCoordinator')->willReturn(true);
        $currentUser->method('isAdmin')->willReturn(false);

        $router = new AppRouter($currentUser);
        $ty = $router->resolveTy();

        $this->assertSame('last_coord', $ty);
    }

    public function testHandleRequestSidebarEndpoint(): void
    {
        $_SERVER['SCRIPT_NAME'] = '/index.php';
        $_GET['ty'] = 'sidebar';
        $_GET['nonav'] = '1';

        $currentUser = $this->createMock(CurrentUser::class);
        $currentUser->method('isCoordinator')->willReturn(false);
        $currentUser->method('isAdmin')->willReturn(false);

        $router = new AppRouter($currentUser);

        ob_start();
        $router->handleRequest();
        $output = ob_get_clean();

        $this->assertStringContainsString('list-unstyled', $output);
    }
}

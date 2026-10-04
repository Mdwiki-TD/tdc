<?php



namespace Tests\App;

use App\CSRFManager;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class CSRFManagerTest extends TestCase
{
    private CSRFManager $csrfManager;

    protected function setUp(): void
    {
        parent::setUp();
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }
        $_SESSION = [];
        $_POST = [];
        $this->csrfManager = new CSRFManager();
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
        $_POST = [];
        parent::tearDown();
    }

    public function testGenerateToken(): void
    {
        $token = $this->csrfManager->generateToken();
        $this->assertIsString($token);
        $this->assertSame(64, strlen($token));
        $this->assertSame(1, $this->csrfManager->getTokenCount());
    }

    public function testTokenLimitMaxFifty(): void
    {
        for ($i = 0; $i < 60; $i++) {
            $this->csrfManager->generateToken();
        }
        $this->assertSame(50, $this->csrfManager->getTokenCount());
    }

    public function testVerifyTokenProvidedExplicitly(): void
    {
        $token = $this->csrfManager->generateToken();
        $this->assertTrue($this->csrfManager->verifyToken($token));
        // Single use verification: verifying again should fail
        $this->assertFalse($this->csrfManager->verifyToken($token));
    }

    public function testVerifyTokenFromPostRequest(): void
    {
        $token = $this->csrfManager->generateToken();
        $_POST['csrf_token'] = $token;

        $this->assertTrue($this->csrfManager->verifyToken());
        $this->assertSame(0, $this->csrfManager->getTokenCount());
    }

    public function testVerifyInvalidToken(): void
    {
        $this->csrfManager->generateToken();
        $this->assertFalse($this->csrfManager->verifyToken('invalid_token_1234567890'));
    }

    public function testVerifyTokenWhenNoTokensInSession(): void
    {
        $this->assertFalse($this->csrfManager->verifyToken('some_token'));
    }

    public function testClearTokens(): void
    {
        $this->csrfManager->generateToken();
        $this->csrfManager->generateToken();
        $this->assertSame(2, $this->csrfManager->getTokenCount());

        $this->csrfManager->clearTokens();
        $this->assertSame(0, $this->csrfManager->getTokenCount());
    }
}

<?php

namespace App\Security;

use RuntimeException;

class CSRFManager
{
    private const SESSION_KEY = 'csrf_tokens';
    private const MAX_TOKENS = 50;
    private const TOKEN_LENGTH_BYTES = 32;

    private function ensureSessionActive(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException('CSRFManager requires an active PHP session. Call session_start() first.');
        }
    }

    public function generateToken(): string
    {
        $this->ensureSessionActive();

        try {
            $token = bin2hex(random_bytes(self::TOKEN_LENGTH_BYTES));
        } catch (\Exception $e) {
            throw new RuntimeException('Failed to generate CSRF token: ' . $e->getMessage(), 0, $e);
        }

        if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }

        $_SESSION[self::SESSION_KEY][] = $token;

        if (count($_SESSION[self::SESSION_KEY]) > self::MAX_TOKENS) {
            $_SESSION[self::SESSION_KEY] = array_slice($_SESSION[self::SESSION_KEY], -self::MAX_TOKENS);
        }

        return $token;
    }

    public function verifyToken(?string $submittedToken = null): bool
    {
        $this->ensureSessionActive();

        if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
            error_log('CSRF: No tokens found in session storage.');
            return false;
        }

        $submittedToken = $submittedToken ?? ($_POST['csrf_token'] ?? null);

        if (!$submittedToken || !is_string($submittedToken)) {
            error_log('CSRF: Missing or invalid token payload in request.');
            return false;
        }

        foreach ($_SESSION[self::SESSION_KEY] as $key => $token) {
            if (hash_equals($token, $submittedToken)) {
                unset($_SESSION[self::SESSION_KEY][$key]);
                $_SESSION[self::SESSION_KEY] = array_values($_SESSION[self::SESSION_KEY]);
                return true;
            }
        }

        error_log('CSRF: Invalid or previously consumed token submitted.');
        return false;
    }

    public function getTokenCount(): int
    {
        $this->ensureSessionActive();
        return count($_SESSION[self::SESSION_KEY] ?? []);
    }

    public function clearTokens(): void
    {
        $this->ensureSessionActive();
        $_SESSION[self::SESSION_KEY] = [];
    }
}

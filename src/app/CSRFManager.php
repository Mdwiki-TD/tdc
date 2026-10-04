<?php

namespace App;

use RuntimeException;

/**
 * CSRF (Cross-Site Request Forgery) Protection Manager
 *
 * This class provides a robust object-oriented solution for CSRF protection
 * using single-use, cryptographically secure tokens.
 *
 * @package    TDWIKI\Security
 * @subpackage CSRF
 * @author     Translation Dashboard Team
 * @version    2.0.0
 * @license    GPL-3.0-or-later
 *
 * @see https://owasp.org/www-community/attacks/csrf
 * @see https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html
 */

class CSRFManager
{
    /**
     * Session key used to store active CSRF tokens.
     */
    private const SESSION_KEY = 'csrf_tokens';

    /**
     * Maximum number of tokens stored simultaneously per session.
     */
    private const MAX_TOKENS = 50;

    /**
     * Token length in bytes (32 bytes = 64 hexadecimal characters).
     */
    private const TOKEN_LENGTH_BYTES = 32;

    /**
     * Ensure the PHP session is active before performing operations.
     *
     * @throws RuntimeException If no session is active.
     */
    private function ensureSessionActive(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            throw new RuntimeException('CSRFManager requires an active PHP session. Call session_start() first.');
        }
    }

    /**
     * Generate a new CSRF token and store it in the session
     *
     * Creates a cryptographically secure random token and stores it
     * in the session for later validation. Each token is single-use.
     *
     * Token Characteristics:
     * - 64 hexadecimal characters (32 random bytes)
     * - Generated using cryptographically secure random_bytes()
     * - Unique per generation call
     * - Stored in session for server-side validation
     *
     * @return string The generated hexadecimal token string.
     * @throws RuntimeException If random byte generation fails or session is inactive.
     */
    public function generateToken(): string
    {
        $this->ensureSessionActive();

        try {
            // Generate 32 random bytes and convert to 64 hex characters
            $token = bin2hex(random_bytes(self::TOKEN_LENGTH_BYTES));
        } catch (\Exception $e) {
            throw new RuntimeException('Failed to generate CSRF token: ' . $e->getMessage(), 0, $e);
        }

        // Initialize token array if needed
        if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
        }

        // Store the token for validation
        $_SESSION[self::SESSION_KEY][] = $token;

        // Maintain token limit to avoid session bloating
        if (count($_SESSION[self::SESSION_KEY]) > self::MAX_TOKENS) {
            $_SESSION[self::SESSION_KEY] = array_slice($_SESSION[self::SESSION_KEY], -self::MAX_TOKENS);
        }

        return $token;
    }

    /**
     * Verify a submitted CSRF token against stored tokens.
     *
     * This function validates that:
     * 1. A session exists with stored tokens
     * 2. A token was submitted in the POST request
     * 3. The submitted token matches one of the stored tokens
     * 4. The token is consumed (removed) after successful validation
     *
     * Security Considerations:
     * - Tokens are single-use; they are removed after validation
     * - Empty or missing token lists are treated as validation failures
     * - This prevents session fixation attacks from bypassing CSRF
     *
     * @return bool True if valid and consumed; false otherwise.
     */
    public function verifyToken(?string $submittedToken = null): bool
    {
        $this->ensureSessionActive();

        // Initialize token array if it doesn't exist
        if (!isset($_SESSION[self::SESSION_KEY]) || !is_array($_SESSION[self::SESSION_KEY])) {
            $_SESSION[self::SESSION_KEY] = [];
            // SECURITY FIX: Return false when no tokens exist
            // This prevents bypassing CSRF by clearing the session
            error_log('CSRF: No tokens found in session storage.');
            return false;
        }

        // Auto-extract from POST request if not explicitly provided
        $submittedToken = $submittedToken ?? ($_POST['csrf_token'] ?? null);

        // Reject if no token was submitted
        if (!$submittedToken || !is_string($submittedToken)) {
            error_log('CSRF: Missing or invalid token payload in request.');
            return false;
        }

        foreach ($_SESSION[self::SESSION_KEY] as $key => $token) {
            if (hash_equals($token, $submittedToken)) {
                // Consume token (single-use pattern)
                // Token is valid - remove it to prevent reuse
                unset($_SESSION[self::SESSION_KEY][$key]);

                // Re-index the array to prevent gaps
                $_SESSION[self::SESSION_KEY] = array_values($_SESSION[self::SESSION_KEY]);

                return true;
            }
        }

        // Token not found or already used
        error_log('CSRF: Invalid or previously consumed token submitted.');
        return false;
    }

    /**
     * Get the current count of active CSRF tokens stored in session.
     *
     * @return int Number of active tokens.
     */
    public function getTokenCount(): int
    {
        $this->ensureSessionActive();

        return count($_SESSION[self::SESSION_KEY] ?? []);
    }

    /**
     * Clear all active CSRF tokens from session storage.
     *
     * Useful during logout or session invalidation.
     *
     * @return void
     */
    public function clearTokens(): void
    {
        $this->ensureSessionActive();

        $_SESSION[self::SESSION_KEY] = [];
    }
}

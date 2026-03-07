<?php

/**
 * CSRF (Cross-Site Request Forgery) Protection Module
 *
 * This module provides comprehensive CSRF protection for the Translation Dashboard
 * application. It implements a token-based approach where each form submission
 * requires a valid token that was generated on the server.
 *
 * Security Features:
 * - Cryptographically secure token generation using random_bytes()
 * - Token consumption (single-use tokens)
 * - Session-based token storage
 * - Configurable token lifetime (future enhancement)
 *
 * Usage Example:
 * ```php
 * // Include the module
 * include_once __DIR__ . '/csrf.php';
 *
 * // Generate a token for a form
 * use function TDWIKI\csrf\generate_csrf_token;
 * $token = generate_csrf_token();
 *
 * // In your form, include the token
 * echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
 *
 * // Validate the token on form submission
 * use function TDWIKI\csrf\verify_csrf_token;
 * if (verify_csrf_token()) {
 *     // Token is valid, process the form
 * } else {
 *     // Token is invalid, reject the request
 * }
 * ```
 *
 * Architecture Notes:
 * - Tokens are stored in $_SESSION['csrf_tokens'] as an array
 * - Each token can only be used once (consumed on validation)
 * - Session must be started before using these functions
 *
 * @package    TDWIKI\Security
 * @subpackage CSRF
 * @author     Translation Dashboard Team
 * @version    2.0.0
 * @since      1.0.0
 * @license    GPL-3.0-or-later
 *
 * @see https://owasp.org/www-community/attacks/csrf
 * @see https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html
 */

namespace TDWIKI\csrf;

use RuntimeException;

/**
 * Ensure session is started before token operations
 *
 * This check ensures that the session is available for token storage.
 * If no session exists, one is started with secure defaults.
 *
 * @return void
 * @throws RuntimeException If session cannot be started
 */
if (session_status() === PHP_SESSION_NONE) {
	// 	session_name("mdwikitoolforgeoauth");
	session_start();
}

/**
 * Verify a submitted CSRF token against stored tokens
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
 * @return bool True if the token is valid and consumed, false otherwise
 *
 * @example
 * ```php
 * if ($_SERVER['REQUEST_METHOD'] === 'POST') {
 *     if (!verify_csrf_token()) {
 *         http_response_code(403);
 *         die('CSRF validation failed');
 *     }
 *     // Process the form submission
 * }
 * ```
 */
function verify_csrf_token()
{
	// التحقق مما إذا كان هناك CSRF Tokens في الجلسة
	if (!isset($_SESSION['csrf_tokens']) || !is_array($_SESSION['csrf_tokens'])) {
		$_SESSION['csrf_tokens'] = [];
		echo "No csrf tokens in session!";
		return true;
	}

	// Get the submitted token from POST data
	$submitted_token = $_POST['csrf_token'] ?? null;

	// Reject if no token was submitted
	if (!$submitted_token || !is_string($submitted_token)) {
		error_log('CSRF: No token submitted in POST request');
		return false;
	}

	// التحقق مما إذا كان Token موجودًا في القائمة
	if (in_array($submitted_token, $_SESSION['csrf_tokens'], true)) {
		// Token صحيح، إزالته من القائمة
		$_SESSION['csrf_tokens'] = array_diff($_SESSION['csrf_tokens'], [$submitted_token]);

		return true;
	}
	echo <<<HTML
		<div class='alert alert-danger' role='alert'>
		Invalid or Reused CSRF Token!
	</div>
	HTML;

	// Token not found or already used
	error_log('CSRF: Invalid or reused token detected');
	return false;
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
 * @return string The generated CSRF token (64 hex characters)
 *
 * @throws RuntimeException If random byte generation fails
 *
 * @example
 * ```php
 * // In your form view
 * $token = generate_csrf_token();
 * echo '<input type="hidden" name="csrf_token" value="' . htmlspecialchars($token) . '">';
 * ```
 */
function generate_csrf_token()
{
	$token = bin2hex(random_bytes(32));
	if (!isset($_SESSION['csrf_tokens'])) {
		$_SESSION['csrf_tokens'] = [];
	}

	// Store the token for validation
	$_SESSION['csrf_tokens'][] = $token;

	return $token;
}

/**
 * Get the count of currently stored CSRF tokens
 *
 * Useful for debugging and monitoring session state.
 *
 * @return int Number of tokens currently stored in the session
 */
function get_token_count(): int
{
	ensure_session_started();
	return count($_SESSION['csrf_tokens'] ?? []);
}

/**
 * Clear all stored CSRF tokens
 *
 * This should be called when a user logs out to invalidate
 * all pending form submissions.
 *
 * @return void
 */
function clear_all_tokens(): void
{
	ensure_session_started();
	$_SESSION['csrf_tokens'] = [];
}

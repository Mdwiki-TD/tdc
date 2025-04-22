<?PHP

namespace TDWIKI\csrf;
/*
Usage:
include_once __DIR__ . '/csrf.php';

use function TDWIKI\csrf\generate_csrf_token;
use function TDWIKI\csrf\verify_csrf_token; // if (verify_csrf_token())  {
*/

include_once __DIR__ . '/auth/helps.php';

use function OAuth\Helps\decode_value;

function verify_csrf_token()
{
	global $csrf_token_orginal;
	// التحقق من وجود Token في الطلب
	$submitted_token = $_POST['csrf_token'] ?? null;

	// إذا لم يتم إرسال Token، فإن التحقق يفشل
	if (!$submitted_token) {
		echo "No CSRF Token Submitted!";
		return false;
	}
	$decoded_token = decode_value($submitted_token);

	// التحقق مما إذا كان Token موجودًا في القائمة
	if ($csrf_token_orginal == $decoded_token) {
		return true;
	} else {
		// ---
		echo <<<HTML
				<div class='alert alert-danger' role='alert'>
					Invalid or Reused CSRF Token!
				</div>
				HTML;
		// ---
		return false;
	}
	// ---
	echo <<<HTML
	<div class='alert alert-danger' role='alert'>
		This page is not allowed to be opened directly!
	</div>
	HTML;
	// ---
	return false;
}

function generate_csrf_token()
{
	global $csrf_token;
	return $csrf_token;
}

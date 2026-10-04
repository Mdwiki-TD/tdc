<?php

namespace App\Coordinator\Tools;

use App\Coordinator\Admin\Common\AbstractControllerNoPost;
use App\SQLorAPI\InProcessTable;

/**
 * Class ProcessTotalController
 * Renders user process article counts table.
 */
class ProcessTotalController extends AbstractControllerNoPost
{
	/**
	 * Handles request execution.
	 */
	public function handleRequest(): void
	{
		$text = "";

		$userProcessTab = (InProcessTable::getInstance())->getUsersProcessNew();

		arsort($userProcessTab);

		$n = 0;

		foreach ($userProcessTab as $user => $count) {
			if ($user !== 'test' && !empty($user) && $count > 0) {
				$n++;

				$use = rawurlencode($user);
				$use = str_replace('+', '_', $use);

				$text .= <<<HTML
					<tr>
						<td data-content='#'>
							$n
						</td>
						<td data-content='User'>
							<a href='/Translation_Dashboard/leaderboard.php?user=$use'>$user</a>
						</td>
						<td data-content='Articles'>
							$count
						</td>
					</tr>
				HTML;
			}
		}

		$header = <<<HTML
			<h4>Translations in process</h4>
		HTML;
		$body = <<<HTML
			<table class='table table-striped compact soro table-mobile-responsive table-mobile-sided table_text_left'>
				<thead>
					<tr>
						<th>#</th>
						<th class='spannowrap'>User</th>
						<th>Articles</th>
					</tr>
				</thead>
				<tbody>
					$text
				</tbody>
			</table>
		HTML;

		$this->echoBs5Card($header, $body, '');
	}
}

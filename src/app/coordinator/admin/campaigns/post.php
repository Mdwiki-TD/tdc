<?php
// src/app/coordinator/admin/campaigns/post.php

namespace App\Coordinator\Admin\Campaigns;

use App\Coordinator\Admin\Common\AbstractPostHandler;
use function App\APICalls\MdwikiSql\execute_query;

/**
 * Class CampaignsPostProcessor
 * Handles update/delete of existing campaign categories and insertion
 * of newly added rows.
 */
class CampaignsPostProcessor extends AbstractPostHandler
{
	private string $defaultCat;

	public function __construct()
	{
		$this->defaultCat = $_POST['default_cat'] ?? '';
	}

	public function process(array $post): void
	{
		$this->processExistingRows($post['rows'] ?? []);

		if (isset($post['new'])) {
			$this->processNewRows($post['new']);
		}
	}
	/**
	 * Updates or deletes existing category rows.
	 */
	private function processExistingRows(array $rows): void
	{
		foreach ($rows as $key => $table) {
			$ido = $table['id'] ?? '';

			if (empty($ido)) {
				continue;
			}

			$del = $table['del'] ?? '';

			if (!empty($del) && $del != "0") {
				$qua2 = "DELETE FROM categories WHERE id = ?";
				execute_query($qua2, [$del]);
				continue;
			}

			$camp = $table['camp'];
			$cat1 = $table['cat1'];
			$cat2 = $table['cat2'];
			$dep  = $table['dep'];

			$isDefault = ($this->defaultCat == $ido) ? 1 : 0;

			$qua = "UPDATE categories
                SET
                    campaign = ?,
                    category = ?,
                    category2 = ?,
                    depth = ?,
                    is_default = ?
                WHERE
                    id = ?
            ";

			$params = [$camp, $cat1, $cat2, $dep, $isDefault, $ido];

			execute_query($qua, $params);
		}
	}

	/**
	 * Inserts newly submitted category rows.
	 */
	private function processNewRows(array $newRows): void
	{
		foreach ($newRows as $key => $table) {
			$ido  = $table['id'] ?? '';
			$camp = $table['camp'];
			$cat1 = $table['cat1'];
			$cat2 = $table['cat2'];
			$dep  = $table['dep'];

			$isDefault = ($this->defaultCat == $ido) ? 1 : 0;

			$qua = "INSERT INTO categories (category, campaign, depth, is_default, category2) SELECT ?, ?, ?, ?, ?";
			$params = [$cat1, $camp, $dep, $isDefault, $cat2];

			execute_query($qua, $params);
		}
	}

}

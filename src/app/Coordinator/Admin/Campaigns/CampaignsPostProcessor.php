<?php
// src/app/Coordinator/Admin/campaigns/campaigns_post.php

namespace App\Coordinator\Admin\Campaigns;

use App\Coordinator\Admin\Common\AbstractPostHandler;
use App\SQLorAPI\CategoriesTable;

/**
 * Class CampaignsPostProcessor
 * Handles update/delete of existing campaign categories and insertion
 * of newly added rows.
 */
class CampaignsPostProcessor extends AbstractPostHandler
{
	protected bool $isPopUpPage = false;

	private string          $defaultCat;
	private CategoriesTable $categoriesTable;

	public function __construct()
	{
		parent::__construct();
		$this->defaultCat = $_POST['default_cat'] ?? '';
		$this->categoriesTable = (CategoriesTable::getInstance());
	}

	public function process(array $post): void
	{
		$this->processRows($post['rows'] ?? []);

		if (isset($post['new'])) {
			$this->processNewRows($post['new']);
		}
	}
	/**
	 * Updates or deletes existing category rows.
	 */
	private function processRows(array $rows): void
	{
		$categories = $this->categoriesTable->getCategories();
		$categoriesById = array_column($categories, null, 'id');

		foreach ($rows as $key => $table) {
			$id = $table['id'] ?? '';

			if (empty($id)) {
				continue;
			}
			$dbData = $categoriesById[$id] ?? [];

			if (empty($dbData)) {
				$this->addWarning("Campaign with id $id does not exist.");
				continue;
			}

			$del = $table['del'] ?? '';

			if ($del === $id) {
				$this->deleteRow($id);
				continue;
			}

			// dbData keys: id, category, category2, campaign, depth, is_default

			if (!$this->hasDifferences($dbData, $table)) {
				continue;
			}

			$isDefault = ($this->defaultCat == $id) ? 1 : 0;

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

			$params = [
				$table['campaign'],
				$table['category'],
				$table['category2'],
				$table['depth'],
				$isDefault,
				$id
			];

			$updated = $this->db->executeQuery($qua, $params);
			if ($updated === false) {
				$this->addError("Failed to update Campaign with id $id.");
				continue;
			}

			$this->addText("Campaign with id $id updated.");
		}
	}

	/**
	 * Inserts newly submitted category rows.
	 */
	private function processNewRows(array $newRows): void
	{
		/*
		if (isset($post['new'])) {
			$this->processNewRows($post['new']);
		}
		*/
		foreach ($newRows as $key => $table) {
			$campaign = $table['campaign'];
			$category = $table['category'];
			$isDefault = 0;

			if (empty($campaign) || empty($category)) {
				continue;
			}
			$qua = "INSERT INTO categories (category, campaign, depth, is_default, category2) SELECT ?, ?, ?, ?, ?";
			$params = [
				$category,
				$campaign,
				$table['depth'] ?? 0,
				$isDefault,
				$$table['category2']
			];

			$this->db->executeQuery($qua, $params);
		}
	}
	private function deleteRow(string $id): void
	{
		$deleted = $this->categoriesTable->deleteCategory($id);
		$this->verifyDeleteResult($deleted, $id, 'Campaign');
	}
}

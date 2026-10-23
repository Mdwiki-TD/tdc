<?php
// src/app/Coordinator/Admin/projects/projects_post.php

namespace App\Coordinator\Admin\Projects;

use App\Coordinator\Admin\Common\AbstractPostHandler;

/**
 * Class ProjectsPostProcessor
 * Handles add/update/delete submissions of project rows.
 * Can run standalone (direct request) or be delegated to from
 * ProjectsIndexController when the index form is submitted.
 */
class ProjectsPostProcessor extends AbstractPostHandler
{
	protected bool $isPopUpPage = false;

	public function __construct()
	{
		parent::__construct();
	}

	public function process(array $post): void
	{
		$this->processRows($post['rows'] ?? []);
		if (isset($post['new'])) {
			$this->processNewRows($post['new']);
		}
	}

	private function processNewRows(array $newRows): void
	{
		foreach ($newRows as $key => $table) {
			$gTitle = $table['g_title'] ?? '';
			$gTitle = trim($gTitle);

			if (empty($gTitle)) {
				continue;
			}
			$query = "INSERT INTO projects (g_title) SELECT ? WHERE NOT EXISTS (SELECT 1 FROM projects WHERE g_title = ?)";
			$params = [$gTitle, $gTitle];

			$result = $this->db->executeQuery($query, $params);
			$this->verifyInsertResult($result, $gTitle, 'Project', 'title');
		}
	}


	/**
	 * Processes each submitted project row: delete, add, or update.
	 */
	private function processRows(array $rows): void
	{
		foreach ($rows as $key => $table) {
			$id = $table['g_id'] ?? '';
			$del = $table['del'] ?? '';
			$gTitle = $table['g_title'] ?? '';

			if ($del === $id) {
				$this->deleteRow($id, 'projects', "Project", 'g_id');
				continue;
			}

			$gTitle = trim($gTitle);

			if (empty($gTitle)) {
				continue;
			}

			$query = "UPDATE projects SET g_title = ? WHERE g_id = ?";
			$params = [$gTitle, $id];
			$updated = $this->db->executeQuery($query, $params);
			$this->verifyUpdateResult($updated, $id, 'Project');
		}
	}
}

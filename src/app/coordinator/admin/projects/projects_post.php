<?php
// src/app/coordinator/admin/projects/projects_post.php

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

	public function process(array $post): void
	{
        $this->processRows($post['rows'] ?? []);
	}


	private function insert_to_projects($gTitle, $gId): bool
	{
		$query = "UPDATE projects SET g_title = ? WHERE g_id = ?";
		$params = [$gTitle, $gId];

		if ($gId == 0 || $gId == '0' || empty($gId)) {
			$query = "INSERT INTO projects (g_title) SELECT ? WHERE NOT EXISTS (SELECT 1 FROM projects WHERE g_title = ?)";
			$params = [$gTitle, $gTitle];
		};

		$result = $this->db->executequery($query, $params);

		return $result;
	}


	/**
	 * Processes each submitted project row: delete, add, or update.
	 */
	private function processRows(array $rows): void
	{
		foreach ($rows as $key => $table) {
			$gId  = $table['g_id'] ?? '';
			$del  = $table['del'] ?? '';
			$gTitle = $table['g_title'] ?? '';

			if (!empty($del) && !empty($gId)) {
				$qua2 = "DELETE FROM projects WHERE g_id = ?";
				$this->db->executequery($qua2, [$gId]);

				$this->addText("Project $gTitle deleted.");
				continue;
			}

			$gTitle = trim($gTitle);

			if (empty($gTitle)) {
				continue;
			}

			$this->insert_to_projects($gTitle, $gId);

			if (empty($gId)) {
				$this->addText("Project $gTitle Added.");
			} else {
				$this->addText("Project $gTitle Updated.");
			}
		}
	}

}

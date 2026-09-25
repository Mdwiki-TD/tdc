<?php
// src/app/coordinator/admin/projects/projects_post.php

namespace App\Coordinator\Admin\Projects;

use App\Coordinator\Admin\Common\AbstractPostHandler;
use function App\MdwikiSql\insert_to_projects;
use function App\MdwikiSql\execute_query;

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
				execute_query($qua2, [$gId]);

				$this->addText("Project $gTitle deleted.");
				continue;
			}

			$gTitle = trim($gTitle);

			if (empty($gTitle)) {
				continue;
			}

			insert_to_projects($gTitle, $gId);

			if (empty($gId)) {
				$this->addText("Project $gTitle Added.");
			} else {
				$this->addText("Project $gTitle Updated.");
			}
		}
	}

}

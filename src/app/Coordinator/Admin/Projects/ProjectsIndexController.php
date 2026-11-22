<?php
// src/app/Coordinator/Admin/projects/index.php

namespace App\Coordinator\Admin\Projects;

use App\SQLorAPI\TitlesTable;
use App\Coordinator\Admin\Common\AbstractController;

/**
 * Class ProjectsIndexController
 * Renders the editable projects table. On POST, delegates to
 * ProjectsPostProcessor first (its result is echoed inline), then
 * always renders the current state of the list/form below it.
 */
class ProjectsIndexController extends AbstractController
{
    protected function createPostProcessor(): object
    {
        return new ProjectsPostProcessor();
    }
    public function renderFormCard(): void
    {
        $projects = (TitlesTable::getInstance())->getProjects();

        // Sort projects by g_id
        uasort($projects, function ($a, $b) {
            return $a['g_id'] <=> $b['g_id'];
        });

        $formText = $this->buildProjectRows($projects);
        $formText .= $this->buildAddRowMarkup();

        $this->renderCard($formText);
    }
    /**
     * Builds the editable table rows for each existing project.
     */
    private function buildProjectRows(array $projects): string
    {
        $formText = '';
        $numb = 0;

        foreach ($projects as $gtitleKey => $tab) {
            $numb++;

            $gid = $tab['g_id'] ?? '';
            $gtitle = $tab['g_title'] ?? '';

            $formText .= <<<HTML
                <tr>
                    <td data-content='id'>
                        <span><b>$gid</b></span>
                        <input name='rows[$numb][g_id]' value='$gid' type='hidden'/>
                    </td>
                    <td data-content='Project'>
                        <input class='form-control' name='rows[$numb][g_title]' value='$gtitle'/>
                    </td>
                    <td data-content="delete">
                        <div class='input-group alert alert-warning p-1 mb-1'>
                            <div class="form-check form-switch form-inline">
                                <input class="form-check-input" type="checkbox" name="rows[$numb][del]" value="$gid">
                            </div>
                        </div>
                    </td>
                </tr>
            HTML;
        }

        return $formText;
    }

    /**
     * Builds the trailing empty row used to add a new project.
     */
    private function buildAddRowMarkup(): string
    {
        return <<<HTML
            <tr>
                <td data-content="id">
                    <span><b>Add:</b></span>
                </td>
                <td data-content="user">
                    <input class='form-control td_user_input' name='new[1][g_title]' />
                </td>
                <td data-content="delete">- </td>
            </tr>
        HTML;
    }

    /**
     * Renders the projects card with the editable table form.
     */
    private function renderCard(string $formText): void
    {

        $form = <<<HTML
            <form action="index.php?ty=projects" method="POST">
                {$this->createCsrfTokenField()}
                <input name='ty' value="projects" type="hidden"/>
                <div class="row">
                    <div class="col-md-6 col-sm-12">
                        <table class='table table-striped compact table-mobile-responsive table-mobile-sided'>
                            <thead>
                                <tr>
                                    <th>Id</th>
                                    <th>Project</th>
                                    <th>Delete</th>
                                </tr>
                            </thead>
                            <tbody id="g_tab">
                                $formText
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="form-group d-flex justify-content-between">
                    <button type="submit" class="btn btn-outline-primary">Save</button>
                </div>
            </form>
        HTML;

        $this->echoCard("Projects:", $form);
    }
}



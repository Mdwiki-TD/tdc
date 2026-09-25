<?php
// src/app/coordinator/admin/pages_users_to_main/fix_page.php

namespace App\Coordinator\Admin\PagesUsersToMain;

use App\Coordinator\Admin\Common\AbstractEditController;
use function App\MdwikiSql\fetch_query;

require_once __DIR__ . '/fix_page_post.php';

/**
 * Class FixItController
 * Handles displaying the page edit form and duplicate entry checks (GET requests).
 */
class FixItController extends AbstractEditController
{
    private string $id;
    private string $newTarget;
    private string $newUser;
    /** @var array<string,mixed>|null */
    private ?array $row = null;

    public function __construct()
    {
        $this->id        = $_GET['id'] ?? '';
        $this->newTarget = $_GET['new_target'] ?? '';
        $this->newUser   = $_GET['new_user'] ?? '';
    }

    protected function createPostProcessor(): object
    {
        return new FixItPostProcessor();
    }

    /** Loads the pages_users row once; shared by beforeCard/getCardTitle/buildForm. */
    private function loadRow(): array
    {
        return $this->row ??= (fetch_query("SELECT * FROM pages_users WHERE id = ?", [$this->id])[0] ?? []);
    }

    protected function beforeCard(): void
    {
        $row = $this->loadRow();

        $title = $row['title'] ?? '';
        $lang  = $row['lang'] ?? '';

        // Check if page already exists in main pages table
        $dup = fetch_query(
            "SELECT * FROM pages WHERE title = ? AND lang = ? AND (target != '' AND target IS NOT NULL)",
            [$title, $lang]
        );
        if (!empty($dup)) {
            echo $this->renderDuplicatePageAlert($dup[0]);
        }
    }
    protected function getCardTitle(): string
    {
        $oldTarget = $this->loadRow()['target'] ?? '';
        return "Edit Page ($oldTarget)";
    }

    private function renderDuplicatePageAlert(array $dup): string
    {
        $lang    = $dup['lang'] ?? '';
        $target  = $dup['target'] ?? '';
        $href    = 'https://' . $lang . '.wikipedia.org/wiki/' . rawurlencode($target);
        $user    = $dup['user'] ?? '';
        $pupd    = $dup['pupdate'] ?? '';

        return <<<HTML
            <div class='card mb-3'>
                <div class='card-header alert alert-danger'><h4>Duplicate page already exists in DB:</h4></div>
                <div class='card-body p-1'>
                    <ul class='list-group'>
                        <li class='list-group-item'><span class='fw-bold'>Target:</span>
                            <a target='_blank' rel='noopener' href='$href'>$target</a></li>
                        <li class='list-group-item'><span class='fw-bold'>User:</span> $user</li>
                        <li class='list-group-item'><span class='fw-bold'>Published:</span> $pupd</li>
                    </ul>
                </div>
            </div>
        HTML;
    }
    protected function buildForm(): string
    {
        $row = $this->loadRow();

        $pupdate = $row['pupdate'] ?? '';
        $lang    = $row['lang'] ?? '';
        $title   = $row['title'] ?? '';

        $testLine = isset($_REQUEST['test']) ? '<input type="hidden" name="test" value="1" />' : "";

        $title2  = htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
        $target2 = htmlspecialchars($this->newTarget, ENT_QUOTES, 'UTF-8');

        return <<<HTML
            <form action='index.php?ty=fix_page&nonav=120' method="POST">
                {$this->createCsrfTokenField()}
                <input id='id' name='id' value='{$this->id}' type='hidden'/>
                <input name='edit' value="1" type="hidden"/>
                $testLine
                <div class='container'>
                    <div class='row'>
                        <div class='col-md-3'>
                            <div class='input-group mb-3'>
                                <div class='input-group-prepend'>
                                    <span class='input-group-text'>Title</span>
                                </div>
                                <input class='form-control' type='text' id='title' name='title' value='{$title2}' required/>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class='input-group mb-3'>
                                <div class='input-group-prepend'>
                                    <span class='input-group-text'>lang</span>
                                </div>
                                <input class='form-control lang_input' type='text' id='lang' name='lang' value='{$lang}' required/>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class='input-group mb-3'>
                                <div class='input-group-prepend'>
                                    <span class='input-group-text'>New target</span>
                                </div>
                                <input class='form-control' type='text' id='new_target' name='new_target' value='{$target2}' required/>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class='input-group mb-3'>
                                <div class='input-group-prepend'>
                                    <span class='input-group-text'>New user</span>
                                </div>
                                <input class='form-control' type='text' id='new_user' name='new_user' value='{$this->newUser}' required/>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class='input-group mb-3'>
                                <div class='input-group-prepend'>
                                    <span class='input-group-text'>Published</span>
                                </div>
                                <input class='form-control' type='text' id='pupdate' name='pupdate' value='{$pupdate}' placeholder='YYYY-MM-DD' required/>
                            </div>
                        </div>
                    </div>
                    <div class='row'>
                        <div class='col-12'>
                            <input class='btn btn-outline-primary' type='submit' value='send'/>
                        </div>
                    </div>
                </div>
            </form>
        HTML;
    }
}

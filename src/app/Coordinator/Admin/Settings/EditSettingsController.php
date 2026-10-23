<?php

namespace App\Coordinator\Admin\Settings;

use App\SQLorAPI\SettingsTable;
use App\Coordinator\Admin\Common\AbstractEditController;
use App\Coordinator\Admin\Settings\EditSettingsProcessor;

/**
 *
 * Class EditSettingsController
 * Renders the add/edit form for a single edit_setting entry GET request only;
 * submission is handled by EditSettingsProcessor.
 */
class EditSettingsController extends AbstractEditController
{
    protected bool $isPopUpPage = true;
    private string $id;
    private array  $data;

    public function __construct()
    {
        parent::__construct();
        $this->id = $_GET['id'] ?? '';
        $this->data = $this->getData();
    }

    private function getData(): array
    {
        return (SettingsTable::getInstance())->getSettingsByid($this->id);
    }

    protected function createPostProcessor(): object
    {
        return new EditSettingsProcessor();
    }
    protected function verifyRequest(): bool
    {
        if (!$this->data) {
            $this->addError('No setting found with id ' . $this->id);
            $this->RenderMesseges($this->result());
            return false;
        }
        return true;
    }

    protected function buildForm(): string
    {
        return $this->buildFormHtml($this->data);
    }
    /**
     * Builds the id/title/lead/full edit-or-add form markup.
     */
    private function buildFormHtml(array $data): string
    {
        // title	displayed	value	ignored
        $id = htmlspecialchars((string)($data['id'] ?? ''), ENT_QUOTES, 'UTF-8');
        $title = htmlspecialchars((string)($data['title'] ?? ''), ENT_QUOTES, 'UTF-8');
        $displayed = htmlspecialchars((string)($data['displayed'] ?? ''), ENT_QUOTES, 'UTF-8');
        $type = strtolower((string)($data['Type'] ?? $data['type'] ?? 'check'));

        $ignored = $this->boolInt($data['ignored'] ?? 0);
        $ignoredChecked = $this->isChecked($ignored);

        if ($type === 'check') {
            $value = $this->boolInt($data['value'] ?? 0);
            $enabledChecked = $this->isChecked($value);
            $valueControl = <<<HTML
                <div class='input-group form-control mb-3'>
                    <div class='input-group-prepend'>
                        <span class='me-3'>Enabled:</span>
                    </div>
                    <div class="form-check form-switch form-inline">
                        <input class='form-check-input' type='checkbox' name='rows[1][value]' value='1' $enabledChecked>
                    </div>
                </div>
            HTML;
        } else {
            $value = htmlspecialchars((string)($data['value'] ?? ''), ENT_QUOTES, 'UTF-8');
            $valueControl = <<<HTML
                <div class='input-group mb-3'>
                    <div class='input-group-prepend'>
                        <span class='input-group-text'>Value</span>
                    </div>
                    <input class='form-control' type='text' name='rows[1][value]' value='$value'/>
                </div>
            HTML;
        }

        return <<<HTML
            <form action='index.php?ty=edit_setting' method="POST">
                {$this->createCsrfTokenField()}
                <input name='id' value="{$id}" type="hidden"/>
                <div class='container'>
                    <div class='row'>
                        <div class='col-md-3'>
                            <div class='row'>
                                <div class='col'>
                                    <div class='input-group mb-3'>
                                        <div class='input-group-prepend'>
                                            <span class='input-group-text'>Id</span>
                                        </div>
                                        <input class='form-control' type='text' value='$id' name='rows[1][id]' readonly/>
                                    </div>
                                </div>
                                <div class='col'>
                                    <div class='input-group mb-3'>
                                        <div class='input-group-prepend'>
                                            <span class='input-group-text'>Key</span>
                                        </div>
                                        <input class='form-control' type='text' name='rows[1][title]' value='$title' readonly/>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class='input-group mb-3'>
                                <div class='input-group-prepend'>
                                    <span class='input-group-text'>Displayed</span>
                                </div>
                                <input class='form-control' type='text' name='rows[1][displayed]' value='$displayed' required/>
                            </div>
                        </div>
                        <div class='col-md-3'>
                            <div class='row'>
                                <div class='col'>
                                    $valueControl
                                </div>
                                <div class='col'>
                                    <div class='input-group form-control mb-3'>
                                        <div class='input-group-prepend'>
                                            <span class='me-3'>Ignored:</span>
                                        </div>
                                        <div class="form-check form-switch form-inline">
                                            <input class='form-check-input' type='checkbox' name='rows[1][ignored]' value='1' $ignoredChecked>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class='col-md-2'>
                            <input class='btn btn-outline-primary' type='submit' value='Save'/>
                        </div>
                    </div>
                </div>
            </form>
        HTML;
    }

    protected function getCardTitle(): string
    {
        return 'Edit Setting';
    }


}

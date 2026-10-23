<?php


namespace App\Coordinator\Admin\Common;

use App\User\CurrentUser;
use App\Utils\Html2;
use App\NotFound;
use App\MdwikiSql\Database;

class BaseController
{
    public Database $db;
    protected bool  $csrfError = false;

    protected array $warnings = [];
    protected array $errors   = [];
    protected array $texts    = [];

    // Regular protected property with a default value
    protected bool $isPopUpPage = false;

    protected bool $returnToFormPage = false;

    public function __construct()
    {
        $this->db = new Database();
    }
    public function result(): array
    {
        return [
            'success'   => !$this->csrfError && empty($this->errors),
            'csrfError' => $this->csrfError,
            'errors'    => $this->errors,
            'warnings'  => $this->warnings,
            'texts'     => $this->texts,
        ];
    }
    protected function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    protected function addWarning(string $message): void
    {
        $this->warnings[] = $message;
    }

    protected function addText(string $message): void
    {
        $this->texts[] = $message;
    }


    public function validateCoordinator(): bool
    {
        // Validate Coordinator user authorization
        if (!CurrentUser::getInstance()->isCoordinator()) {
            // return to home page
            error_log('User is not authorized');
            return false;
        }
        return true;
    }
    public function validateAdmin(): bool
    {
        // Validate admin user authorization
        if (!CurrentUser::getInstance()->isAdmin()) {
            // return to home page
            error_log('User is not authorized');
            return false;
        }
        return true;
    }
    public function exit()
    {
        // return to home page
        NotFound::render();
        // header('Location: /index.php');
    }

    public function RenderIndexMesseges(array $result): void
    {
        echo Html2::div_alert($result['errors'], 'danger');
        echo Html2::div_alert($result['warnings'], 'warning');

        if ($result['csrfError']) {
            $this->DisplayCsrfAlert();
        }

        echo Html2::div_alert($result['texts'], 'success');
    }
    public function DisplayCsrfAlert(): void
    {
        echo "<div class='alert alert-danger' role='alert'>Invalid or Reused CSRF Token!</div>";
    }
    public function RenderMesseges(array $result): void
    {
        $this->RenderIndexMesseges($result);

        if (!$this->shouldShowForm()) {
            echo $this->getCloseButtonHtml();
        }
    }
    public function shouldShowForm(): bool
    {
        return $this->returnToFormPage;
    }
    /**
     * Generates a close button HTML block.
     */
    public function getCloseButtonHtml(): string
    {
        if (!$this->isPopUpPage)
            return "";

        return <<<HTML
            <div class="aligncenter">
                <a class="btn btn-outline-primary" onclick="window.close()">Close</a>
            </div>
        HTML;
    }
    /**
     * Parses a checkbox-style value into a strict 0/1 int.
     */
    protected function boolInt(mixed $value): int
    {
        return filter_var(
            $value,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 0, 'max_range' => 1]]
        ) ?: 0;
    }
    protected function isChecked(mixed $value): string
    {
        return ($value == 1 || $value == "1") ? 'checked' : '';
    }
}

<?php
// src/app/coordinator/admin/common/AbstractPostHandler.php

namespace App\Coordinator\Admin\Common;

use App\User\CurrentUser;
use function App\csrf\verify_csrf_token;
use function App\Utils\Html\div_alert;

/**
 * Class AbstractPostHandler
 *
 * Base class for controllers' POST handlers. Centralizes the common
 * bits: CSRF verification, error/success message collection, and a
 * small helper for parsing checkbox-style 0/1 ints. Subclasses only
 * need to implement process() with their own delete/update/insert logic.
 */
abstract class AbstractPostHandler
{
    protected bool $returnToFormPage = false;
    protected bool $csrfError = false;

    protected array $errors = [];
    protected array $texts = [];

    public function validateCoordinator(): void
    {
        // Validate user authorization
        if (!CurrentUser::getInstance()->isCoordinator()) {
            // return to home page
            header('Location: /index.php');
            exit;
        }
    }

    protected function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    protected function addText(string $message): void
    {
        $this->texts[] = $message;
    }

	/**
	 * Generates a close button HTML block.
	 */
	public function getCloseButtonHtml(): string
	{
		return <<<HTML
            <div class="aligncenter">
                <a class="btn btn-outline-primary" onclick="window.close()">Close</a>
            </div>
        HTML;
	}

    public function DisplayCsrfAlert(): void
    {
        echo "<div class='alert alert-danger' role='alert'>Invalid or Reused CSRF Token!</div>";
    }

    /**
     * Verifies CSRF, then delegates to process() if valid.
     *
     * @return array{success: bool, csrfError: bool, errors: string[], texts: string[]}
     */
    final public function handle(array $post): array
    {
        if (!verify_csrf_token()) {
            $this->csrfError = true;
            return $this->result();
        }
        // TODO: Check if this needed
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            exit;
        }

        $this->process($post);

        return $this->result();
    }

    /**
     * Subclasses implement their own delete/update/insert logic here,
     * filling $this->errors / $this->texts as needed.
     */
    abstract protected function process(array $post): void;

    /**
     * Parses a checkbox-style value into a strict 0/1 int.
     */
    protected function boolInt($value): int
    {
        return filter_var(
            $value,
            FILTER_VALIDATE_INT,
            ['options' => ['min_range' => 0, 'max_range' => 1]]
        ) ?: 0;
    }

    private function result(): array
    {
        return [
            'success'   => !$this->csrfError && empty($this->errors),
            'csrfError' => $this->csrfError,
            'errors'    => $this->errors,
            'texts'     => $this->texts,
        ];
    }
    public function RenderIndexMesseges(array $result): void
    {
        echo div_alert($result['errors'], 'danger');

        if ($result['csrfError']) {
            $this->DisplayCsrfAlert();
        }

        echo div_alert($result['texts'], 'success');
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
}

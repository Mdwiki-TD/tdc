<?php
// src/app/coordinator/admin/common/AbstractPostHandler.php

namespace App\Coordinator\Admin\Common;

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
    protected array $errors = [];
    protected array $texts = [];
    protected bool $csrfError = false;

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

    protected function addError(string $message): void
    {
        $this->errors[] = $message;
    }

    protected function addText(string $message): void
    {
        $this->texts[] = $message;
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
    public function DisplayCsrfAlert(): void
    {
        echo "<div class='alert alert-danger' role='alert'>Invalid or Reused CSRF Token!</div>";
    }
    public function RenderMesseges(array $result): void
    {
        echo div_alert($result['errors'], 'danger');

        if ($result['csrfError']) {
            $this->DisplayCsrfAlert();
            return;
        }

        echo div_alert($result['texts'], 'success');
    }
}

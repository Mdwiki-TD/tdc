<?php

declare(strict_types=1);

namespace App\Coordinator\Admin\Forms;

/**
 * Declarative form (like Flask-WTF's FlaskForm).
 *
 *   class TtRowForm extends Form {
 *       protected function fields(): array {
 *           return [
 *               'title' => new StringField('Title', [V::required()]),
 *               'lead'  => new BooleanField('Lead'),
 *           ];
 *       }
 *   }
 *   $form = new TtRowForm($_POST);      // bound to submitted data
 *   if ($form->validate()) { $data = $form->data(); }
 *
 * Extension points:
 *   - validate_<field>(Field $field): ?string   per-field inline validator
 *   - validateForm(): void                      cross-field checks (call addError())
 *
 * Do not override the constructor in subclasses.
 */
abstract class Form
{
    /** @var array<string,Field> */
    private array $fields = [];

    /** @var string[] */
    private array $formErrors = [];

    private bool $submitted;

    /** @return array<string,Field> name => Field */
    abstract protected function fields(): array;

    /**
     * @param array<string,mixed>|null $data     submitted data (null = unbound form)
     * @param string                   $prefix   HTML name prefix, e.g. "rows[3]"
     * @param array<string,mixed>      $defaults initial values (e.g. a DB row) when unbound
     */
    public function __construct(?array $data = null, string $prefix = '', array $defaults = [])
    {
        foreach ($this->fields() as $name => $field) {
            $field->attach($name, $prefix);
            $this->fields[$name] = $field;

            if (array_key_exists($name, $defaults)) {
                $field->setData($defaults[$name]);
            }
        }

        $this->submitted = $data !== null;
        if ($data !== null) {
            foreach ($this->fields as $name => $field) {
                $field->processInput($data[$name] ?? null);
            }
        }
    }

    public function __get(string $name): Field
    {
        return $this->fields[$name] ?? throw new \OutOfBoundsException("Unknown form field '$name'.");
    }

    public function __isset(string $name): bool
    {
        return isset($this->fields[$name]);
    }

    /** @return array<string,Field> */
    public function getFields(): array
    {
        return $this->fields;
    }

    public function isSubmitted(): bool
    {
        return $this->submitted;
    }

    /** True when the form was submitted AND passed validation. */
    public function validateOnSubmit(): bool
    {
        return $this->submitted && $this->validate();
    }

    public function validate(): bool
    {
        $this->formErrors = [];
        $ok = true;

        foreach ($this->fields as $name => $field) {
            $valid = $field->validate($this);

            $inline = 'validate_' . $name;
            if ($valid && method_exists($this, $inline) && !$field->isEmpty()) {
                $message = $this->$inline($field);
                if (is_string($message) && $message !== '') {
                    $field->errors[] = $message;
                    $valid = false;
                }
            }
            $ok = $ok && $valid;
        }

        $this->validateForm();

        return $ok && $this->formErrors === [];
    }

    /** Hook for cross-field validation. */
    protected function validateForm(): void
    {
    }

    public function addError(string $message): void
    {
        $this->formErrors[] = $message;
    }

    /** @return array<string,mixed> cleaned values, name => value */
    public function data(): array
    {
        $out = [];
        foreach ($this->fields as $name => $field) {
            $out[$name] = $field->data;
        }
        return $out;
    }

    /** @return array<string,string[]> field name => messages ("_form" for form-level) */
    public function errors(): array
    {
        $out = [];
        foreach ($this->fields as $name => $field) {
            if ($field->errors !== []) {
                $out[$name] = $field->errors;
            }
        }
        if ($this->formErrors !== []) {
            $out['_form'] = $this->formErrors;
        }
        return $out;
    }

    /**
     * Flat list ready for AbstractPostHandler::addError().
     * @return string[]
     */
    public function errorMessages(): array
    {
        $messages = [];
        foreach ($this->fields as $name => $field) {
            foreach ($field->errors as $message) {
                $label = $field->label !== '' ? $field->label : $name;
                $messages[] = "$label: $message";
            }
        }
        return [...$messages, ...$this->formErrors];
    }

    /** True when every field (except $ignore) is empty, e.g. the blank "Add:" row. */
    public function isBlank(string ...$ignore): bool
    {
        foreach ($this->fields as $name => $field) {
            if (!in_array($name, $ignore, true) && !$field->isEmpty()) {
                return false;
            }
        }
        return true;
    }

    /** Renders the fields only (no <form> tag): hidden inputs + a row of columns. */
    public function render(string $colClass = 'col-md-3'): string
    {
        $hidden = '';
        $cols = '';
        foreach ($this->fields as $field) {
            if ($field instanceof HiddenField) {
                $hidden .= $field->render();
            } else {
                $cols .= "<div class='$colClass'>" . $field->render() . "</div>";
            }
        }

        $formErrors = '';
        foreach ($this->formErrors as $message) {
            $formErrors .= "<div class='alert alert-danger'>" . Field::esc($message) . "</div>";
        }

        return $hidden . $formErrors . "<div class='row'>$cols</div>";
    }

    /**
     * Full <form>: CSRF token + extra hidden inputs + fields + submit button.
     *
     * @param array<string,scalar> $hidden extra hidden inputs, e.g. ['edit' => 1, 'ty' => 'qids']
     */
    public function renderForm(
        string $action,
        string $submitLabel = 'Save',
        string $colClass = 'col-md-3',
        array $hidden = [],
    ): string {
        $hiddenHtml = "<input name='csrf_token' value='" . Field::esc($this->csrfToken()) . "' type='hidden'/>";
        foreach ($hidden as $name => $value) {
            $hiddenHtml .= "<input name='" . Field::esc((string) $name) . "' value='"
                . Field::esc((string) $value) . "' type='hidden'/>";
        }

        return "<form action='" . Field::esc($action) . "' method='POST'>"
            . $hiddenHtml
            . "<div class='container'>" . $this->render($colClass)
            . "<div class='row'><div class='col-12'>"
            . "<button type='submit' class='btn btn-outline-primary'>" . Field::esc($submitLabel) . "</button>"
            . "</div></div></div></form>";
    }

    /** Uses the project's CSRFManager; override in tests. */
    protected function csrfToken(): string
    {
        return (new \App\CSRFManager())->generateToken();
    }
}

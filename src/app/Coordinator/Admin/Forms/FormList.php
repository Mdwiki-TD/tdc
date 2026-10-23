<?php

declare(strict_types=1);

namespace App\Coordinator\Admin\Forms;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * A collection of row forms: rows[1][...], rows[2][...] (like WTForms FieldList(FormField)).
 *
 *   $rows = FormList::fromPost(TtRowForm::class, $_POST);          // parse POST
 *   $rows = FormList::fromRecords(TtRowForm::class, $dbRows);      // build for display
 *   $rows->blank();                                                // trailing "Add:" row
 *
 * @implements IteratorAggregate<int|string,Form>
 */
final class FormList implements IteratorAggregate, Countable
{
    /** @var array<int|string,Form> */
    private array $forms = [];

    /** @param class-string<Form> $formClass */
    private function __construct(private string $formClass, private string $key)
    {
    }

    /** @param class-string<Form> $formClass */
    public static function fromPost(string $formClass, array $post, string $key = 'rows'): self
    {
        $list = new self($formClass, $key);
        $rows = $post[$key] ?? [];

        if (is_array($rows)) {
            foreach ($rows as $index => $row) {
                if (is_array($row)) {
                    $list->forms[$index] = new $formClass($row, "{$key}[{$index}]");
                }
            }
        }
        return $list;
    }

    /**
     * @param class-string<Form>              $formClass
     * @param array<int|string,array<string,mixed>> $records DB rows
     */
    public static function fromRecords(string $formClass, array $records, string $key = 'rows'): self
    {
        $list = new self($formClass, $key);
        $index = 0;
        foreach ($records as $record) {
            $index++;
            $list->forms[$index] = new $formClass(null, "{$key}[{$index}]", $record);
        }
        return $list;
    }

    /** Appends an empty, unbound form at the next index and returns it. */
    public function blank(): Form
    {
        $numericKeys = array_filter(array_keys($this->forms), 'is_int');
        $index = ($numericKeys === [] ? 0 : max($numericKeys)) + 1;

        return $this->forms[$index] = new ($this->formClass)(null, "{$this->key}[{$index}]");
    }

    /** Validates every row (no short-circuit) and returns true only if all passed. */
    public function validate(): bool
    {
        $ok = true;
        foreach ($this->forms as $form) {
            $ok = $form->validate() && $ok;
        }
        return $ok;
    }

    /** @return string[] */
    public function errorMessages(): array
    {
        $messages = [];
        foreach ($this->forms as $form) {
            array_push($messages, ...$form->errorMessages());
        }
        return $messages;
    }

    /** @return array<int|string,array<string,mixed>> */
    public function data(): array
    {
        return array_map(static fn(Form $f): array => $f->data(), $this->forms);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->forms);
    }

    public function count(): int
    {
        return count($this->forms);
    }
}

# Forms: usage example (TranslateType)

Place the `Forms/` folder at `src/app/Coordinator/Admin/Forms/` (namespace `App\Coordinator\Admin\Forms`). Requires PHP 8.1+ and mbstring.

## 1. Declare the form once (field list = single source of truth)

```php
namespace App\Coordinator\Admin\TranslateType;

use App\Coordinator\Admin\Forms\{Form, HiddenField, StringField, BooleanField, V};

class TranslateTypeRowForm extends Form
{
    protected function fields(): array
    {
        return [
            'id'    => new HiddenField(),
            'title' => new StringField('Title', [V::required('Title is required.')]),
            'lead'  => new BooleanField('Lead'),
            'full'  => new BooleanField('Full'),
        ];
    }
}
```

## 2. Parse + validate POST (replaces the manual `trim / ?? / empty` code)

```php
use App\Coordinator\Admin\Forms\FormList;

protected function process(array $post): void
{
    foreach (FormList::fromPost(TranslateTypeRowForm::class, $post) as $form) {
        if (!$form->validate()) {
            foreach ($form->errorMessages() as $m) {
                $this->addError($m);
            }
            continue;
        }

        ['id' => $id, 'title' => $title, 'lead' => $lead, 'full' => $full] = $form->data();
        // ... existing insert/update logic
    }
}
```

Optional helper for `AbstractPostHandler` so every handler shrinks further:

```php
protected function eachValidRow(string $formClass, array $post, callable $fn, string $key = 'rows'): void
{
    foreach (FormList::fromPost($formClass, $post, $key) as $form) {
        if ($form->validate()) {
            $fn($form->data(), $form);
        } else {
            foreach ($form->errorMessages() as $m) {
                $this->addError($m);
            }
        }
    }
}
```

## 3. Render the same form (replaces the hand-written HTML in Edit controllers)

```php
protected function buildForm(): string
{
    $form = new TranslateTypeRowForm(null, 'rows[1]', [
        'id' => $this->id, 'title' => $this->title, 'lead' => $this->lead, 'full' => $this->full,
    ]);

    return $form->renderForm('index.php?ty=edit_translate_type&nonav=120', 'Save', hidden: ['edit' => 1]);
}
```

For list pages: `FormList::fromRecords(Class::class, $dbRows)`, loop and call `$form->render()`, then `$list->blank()` for the trailing "Add:" row.

## Notes / limits
- Values are always HTML-escaped on render (the current hand-written markup mostly is not).
- CSRF is still verified by `AbstractPostHandler::handle()`; the form only renders the token (via `CSRFManager`).
- Validators other than `V::required()` are skipped when the value is empty.
- `BooleanField` gives 0/1. The "delete" checkbox that posts `value=$id` is not covered; a small `DeleteField` can be added.
- Inline per-field validators: define `validate_<field>(Field $f): ?string`. Cross-field: override `validateForm()` and call `addError()`.

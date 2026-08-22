Inline sub category assignment on the transaction list
=====================================================

Goal
----

Let a user assign a sub category to an **uncategorized** transaction directly from
`/transaction/`, without opening the edit page.

This is the fast path of the monthly workflow: after an import, the long tail of
transactions that no rule matched has to be categorized one by one. Today that costs
two page loads and a form submit per transaction.

Scope
-----

**In scope**: the Sub category cell of rows where `transaction.subCategory is null`.

**Out of scope for this branch** (deliberately, see [Follow-ups](#follow-ups)):

* editing the sub category of an already-categorized row (click-to-edit),
* bulk selection / "apply to selected",
* the "create a rule from this" prompt after a save,
* the uncategorized counter and filter shortcut next to the transaction total.

---

1. UI specification
-------------------

### 1.1 Cell states

The Sub category cell of an uncategorized row moves through three states.

```
  Label                      Amount   Created     Account  Sub category            Actions
 ──────────────────────────────────────────────────────────────────────────────────────────
 ▎Ovh sas - date de valeur…  -34.30   02/04/2025  N26      [Select a category ▾]    ✎  ⚌     (1) idle
 ▎Campus.coach - date de v…  -15.00   12/04/2025  N26      [Groceries         ▾] ⋯  ✎  ⚌     (2) saving
  Ccm loire divatte - date…  -20.00   12/04/2025  N26      Groceries  ✓ saved · undo ✎  ⚌     (3) saved
```

1. **Idle** — a `<select>` scoped to the transaction's own type (see 1.2), plus a submit
   button that JavaScript hides on init (see 3.1).
2. **Saving** — the select is disabled, a spinner/ellipsis appears next to it. The row does
   not move.
3. **Saved** — the select is replaced by the category name, followed by a transient
   `✓ saved` marker and a persistent `undo` link. The amber row marker is removed.

An **error** state replaces the marker with `⚠ <message>` in `text-danger` and re-enables
the select so the user can retry. Nothing is lost.

### 1.2 The picker

* Options are **restricted to the transaction's own transaction type**.
  `Transaction::getType()` returns `Revenues` when `amount > 0`, `Expenses` otherwise, and
  `Transaction::checkSubCategory()` throws on a mismatch — so a cross-type option is not
  merely discouraged, it is unrepresentable. Rendering only valid options removes the
  error case from the UI entirely.
* Options are grouped by top category with `<optgroup>`, mirroring the hierarchy shown on
  `/category/`.
* Options are sorted by name within each group (already the case in
  `SubCategoryRepository::findByTransactionType()`).
* The first option is an empty placeholder, `Select a category`.
* Native `<select>`, styled `custom-select custom-select-sm` (Bootstrap 4.1 is already
  loaded). Native gives free keyboard type-ahead and a real picker on mobile. See
  [§6](#6-third-party-libraries) for why not a combobox widget.

### 1.3 Keyboard flow

This is the point of the feature and constrains the markup:

* Every uncategorized row carries a **live, focusable** select — not a click-to-edit
  affordance — so `Tab` walks from one row needing work to the next.
* Saving happens on `change`, so the pass is *Tab → type the first letters → Tab*, with no
  mouse and no page reload.
* The select must **not** be removed from the tab order while saving, and focus must not be
  stolen or reset by the response handler.

### 1.4 Row-level marker

Rows with no sub category get a `.uncategorized-transaction` class and an amber left
border in `public/styles/main.css`. `templates/transaction/import/validate_transactions.html.twig`
already sets `.existing-transaction` / `.new-transaction` row classes, so this follows an
existing convention.

The class is removed client-side on a successful save, and restored on undo.

### 1.5 No reflow

The usual entry point for a categorizing pass is the existing `Categorized: No` filter — at
which point every row the user categorizes stops matching the active filter. The row must
**not** be removed, hidden, or re-sorted. It stays where it is, dimmed, and disappears only
on the next page load. Reflowing the list under the cursor mid-pass is how people lose
their place and mis-assign the next row.

---

2. Backend
----------

### 2.1 New route

One route, on `TransactionController`:

```php
#[Route('/{id}/sub-category', name: 'transaction_set_sub_category', methods: ['PATCH'])]
public function setSubCategory(
    Request $request,
    Transaction $transaction,
    TransactionSubCategoryAssigner $assigner,
): Response
```

`PATCH` matches the existing convention (`transaction_categorize` and `elasticsearch_export`
are both `PATCH`), and `framework.http_method_override` is already `true`, so the plain-form
fallback can reach it via a `_method` hidden input.

The path segment `sub-category` cannot collide with `transaction_delete` (`/{id}`,
`DELETE`) or `transaction_edit` (`/{id}/edit`).

### 2.2 Request contract

| Parameter | Required | Meaning |
|---|---|---|
| `_token` | yes | CSRF token, id `set-sub-category<transactionId>` |
| `subCategory` | yes | Sub category UUID, or empty string to clear (undo) |

No Symfony Form type is used. A form would bind by field name and invites accidental
widening later; here exactly one property may change and the controller reads exactly one
parameter. See [§8.3](#83-mass-assignment).

### 2.3 New service: `App\Services\TransactionSubCategoryAssigner`

```php
public function assign(Transaction $transaction, ?SubCategory $subCategory): void
```

Responsibilities:

1. set the sub category on the transaction,
2. if `TransactionDiffChecker::subCategoryChanged()` reports a change, set
   `categorizedManually` to `$transaction->isCategorized()`,
3. **validate** the entity (see 2.4) and throw a typed exception on violation,
4. flush.

**Why a service rather than inline controller code.** The `categorizedManually` rule is
already duplicated between `TransactionController::new()` and `TransactionController::edit()`
in slightly different forms; a third inline copy is the point at which the rule stops being
maintainable. It also gives the unit tests in [§5.2](#52-unit-tests) a real target. This
matches the existing service style (`TransactionCategorizer`, `TransactionDiffChecker`,
`RuleChecker`).

This branch wires only the new action to the service. Migrating `new()` and `edit()` onto it
is a separate, mechanical change — noted in [Follow-ups](#follow-ups) to keep this diff
reviewable.

### 2.4 Validation — and why it is mandatory

`Transaction::checkSubCategory()` is a `#[ORM\PreUpdate]` callback that throws a **raw
`\Exception`** when the sub category's transaction type does not match the transaction's.
If an invalid pair reaches `flush()`, the result is an uncaught exception and a 500 with no
usable response body.

So the assigner must validate **before** flushing, using the existing
`TransactionSubCategoryIsLogicalConstraint` via `ValidatorInterface`, and surface the
translated violation message. The constraint validator already calls `checkSubCategory()`
and catches the exception, so no new validation logic is needed — only that it is actually
run on this path.

### 2.5 Response contract

`Accept: application/json` (what the JS sends) → `JsonResponse`:

| Status | Body | Case |
|---|---|---|
| `200` | `{"id": "...", "subCategory": {"id": "...", "name": "Groceries"}, "categorized": true}` | assigned |
| `200` | `{"id": "...", "subCategory": null, "categorized": false}` | cleared (undo) |
| `403` | `{"error": "<translated>"}` | invalid CSRF token |
| `404` | — | unknown transaction id (handled by the argument resolver) |
| `422` | `{"error": "<translated violation message>"}` | unknown sub category id, or type mismatch |

Otherwise (the no-JavaScript fallback, which posts a normal form) → redirect back to
`transaction_index`, preserving the current query string so filters and page number
survive. On error, put the message in the session flash bag.

Content negotiation on `$request->getPreferredFormat()` / the `Accept` header keeps both
paths on one action.

`JsonResponse` from HttpFoundation is enough — see [§6](#6-third-party-libraries).

### 2.6 Elasticsearch

Nothing to do, but worth recording so nobody adds a guard "just in case":

`ElasticsearchSyncStatusUpdater::onFlush()` only flips the `toSyncInElasticsearch` boolean;
the only listener that actually talks to Elasticsearch is
`ElasticsearchTransactionRemover`, bound to `preRemove`. An update therefore has **no
external dependency** and cannot fail because Elasticsearch is down. The transaction is
correctly re-flagged for the next export.

---

3. Frontend
-----------

### 3.1 Progressive enhancement

Each uncategorized cell renders a real, self-sufficient form:

```twig
<form method="post"
      action="{{ path('transaction_set_sub_category', {'id': transaction.id}) }}"
      class="inline-sub-category"
      data-transaction-id="{{ transaction.id }}">
    <input type="hidden" name="_method" value="PATCH">
    <input type="hidden" name="_token" value="{{ csrf_token('set-sub-category' ~ transaction.id) }}">
    <select name="subCategory" class="custom-select custom-select-sm">
        <option value="">{% trans %}Select a category{% endtrans %}</option>
        {% for topCategoryName, subCategories in sub_categories[transaction.type] %}
            <optgroup label="{{ topCategoryName }}">
                {% for subCategory in subCategories %}
                    <option value="{{ subCategory.id }}">{{ subCategory.name }}</option>
                {% endfor %}
            </optgroup>
        {% endfor %}
    </select>
    <button class="btn btn-sm btn-primary">{% trans %}Save{% endtrans %}</button>
</form>
```

The JS module **hides the button on successful init** rather than the template wrapping it in
`<noscript>`. `<noscript>` only covers "JavaScript disabled"; it does not cover "the script
threw". That distinction is not hypothetical here — `templates/sub_category_transaction_rule/form.js.twig`
currently throws a `TypeError` on load and silently disables the rule preview panel. If this
script breaks the same way, the feature degrades to a working form-submit instead of a dead
cell.

This also makes the whole feature reachable from `WebTestCase`, which does not execute
JavaScript — see [§5.1](#51-functional-tests).

### 3.2 The script

New file `public/js/inline_sub_category.js`, an ES module, loaded from
`templates/transaction/index.html.twig`:

```twig
{% block javascripts %}
    {{ parent() }}
    <script type="module">
        import lib from '/js/inline_sub_category.js';
        lib.init();
    </script>
{% endblock %}
```

This follows the existing `public/js/sf_collection.js` precedent (ES module, imported from
a template, no build step). It is deliberately **not** inline JS in the Twig file: the three
templates that do that today (`categorize.html.twig`, `export.html.twig`,
`form.js.twig`) have duplicated helpers, no caching, and no syntax checking.

Behaviour:

1. On `init()`, for each `form.inline-sub-category`: hide the submit button, attach a
   `change` listener on the select.
2. On `change` with a non-empty value: disable the select, show the saving marker, `fetch()`
   the form's `action` with `method: 'POST'`, `body: new FormData(form)` (the `_method`
   override carries the PATCH), and `Accept: application/json`.
3. On `200`: swap the cell to the saved state, drop `.uncategorized-transaction` from the
   `<tr>`, and keep the previous markup in a closure so undo can restore it.
4. On `undo`: re-issue the same request with an empty `subCategory`, then restore the select.
5. On a non-2xx or a network failure: re-enable the select, show the error marker.

**Every insertion of server data into the DOM uses `textContent`, never `innerHTML`.**
See [§8.4](#84-xss).

### 3.3 Controller and template data

`TransactionController::index()` gains a `SubCategoryRepository` argument and passes:

```php
'sub_categories' => [
    TransactionType::EXPENSES => $groupedExpenses,
    TransactionType::REVENUES => $groupedRevenues,
],
```

grouped by top category name, fetched **once per page**, not per row. See
[§7.2](#72-sub-category-options).

---

4. Translations
---------------

New keys in `translations/messages.en.yml` and `translations/messages.fr.yml`:

| Key | English |
|---|---|
| `Select a category` | Select a category |
| `saved` | saved |
| `undo` | undo |
| `Could not save the sub category` | Could not save the sub category |
| `Invalid security token, please reload the page` | Invalid security token, please reload the page |

Strings used by the JS module are passed through `data-` attributes on the table (rendered
by Twig with `|trans`), not hardcoded in the `.js` file — the file lives outside Twig and
cannot call the translator. `composer lint:yaml` covers the files.

---

5. Tests
--------

### 5.1 Functional tests

New `tests/Controller/TransactionSubCategoryTest.php`, `WebTestCase`, following the fixture
style of `TransactionImportControllerTest` (which builds an `Account` and transactions
directly through the `EntityManagerInterface` from the test container; DAMA rolls each test
back).

Fixtures per test: one account, one `Expenses` top category with two sub categories, one
`Revenues` top category with one sub category, one negative and one positive transaction,
both uncategorized.

| # | Test | Asserts |
|---|---|---|
| 1 | The list renders a select for an uncategorized row | `select[name="subCategory"]` exists in that row; the `<tr>` carries `.uncategorized-transaction` |
| 2 | The list renders **no** select for a categorized row | selector absent; plain text present |
| 3 | Options are scoped by type | the negative row offers only the `Expenses` sub categories; the positive row only the `Revenues` one |
| 4 | Options are grouped | `optgroup[label="<top category name>"]` present |
| 5 | **Submitting the form assigns the sub category** | redirect to `transaction_index`; reloading shows the name; `categorizedManually` is `true` in DB |
| 6 | Submitting an empty value clears it | sub category `null`, `categorizedManually` `false` |
| 7 | Filters and page survive the fallback redirect | submit from `/transaction/?item_filter[...]=...&page=2`, assert the redirect target keeps the query string |
| 8 | JSON request returns the JSON contract | `Accept: application/json` → 200 and the documented body |
| 9 | A missing/incorrect CSRF token is rejected | 403, DB unchanged |
| 10 | A cross-type sub category is rejected | 422 with the translated violation, DB unchanged, **no 500** |
| 11 | An unknown sub category id is rejected | 422, DB unchanged |
| 12 | An unknown transaction id | 404 |

Test 5 is the important one: because of the progressive-enhancement design, browser-kit can
drive the *real* user path end to end even though it runs no JavaScript. Test 10 guards the
`PreUpdate` throw described in [§2.4](#24-validation--and-why-it-is-mandatory).

### 5.2 Unit tests

New `tests/Services/TransactionSubCategoryAssignerTest.php`, plain `PHPUnit\Framework\TestCase`
with mocked `EntityManagerInterface`, `TransactionDiffChecker` and `ValidatorInterface` —
matching the mock-based style of `tests/RuleCheckerTest.php`.

| # | Case | Expected |
|---|---|---|
| 1 | assign a sub category to an uncategorized transaction | sub category set, `categorizedManually === true`, one `flush()` |
| 2 | assign `null` (undo) | sub category `null`, `categorizedManually === false` |
| 3 | assign the sub category it already has | no change reported by the diff checker, `categorizedManually` untouched |
| 4 | validator returns a violation | typed exception thrown, **`flush()` never called** |

Case 4 is the one that matters: it pins the ordering "validate, then flush", which is the
whole defence against the 500 in [§2.4](#24-validation--and-why-it-is-mandatory).

### 5.3 JavaScript tests

**None, deliberately.** The repository has no `package.json`, no Node in CI, and no
JavaScript test tooling. Adding Vitest or Jest for one module means adding a Node toolchain
to a PHP pipeline and a second dependency tree to maintain — a cost far larger than the
module it would cover.

The design compensates: the script is a thin transport layer over a form that is fully
tested server-side, and if the script fails the feature still works. Coverage thresholds
(`rregeer/phpunit-coverage-check`) only look at `src`, so this does not move the gate.

### 5.4 Static analysis and style

`composer lint` must stay green at PHPStan level 7 — `phpstan.dist.neon` is currently at
level 7 with a small baseline; new code must not add to the baseline.
`composer lint:twig` covers the template change.

---

6. Third-party libraries
------------------------

The conclusion is **no new dependency, PHP or JavaScript**. Reasoning, since the question is
worth recording:

**API Platform / FOSRestBundle** — rejected. One endpoint returning one small object does not
justify a resource layer, its configuration surface, or its upgrade cost.

**`symfony/serializer`** — already installed, but not needed here. The response is three
scalars; `JsonResponse` with an array literal is clearer than a normalizer and cannot
accidentally leak entity fields (see [§8.5](#85-response-shape)). Left unused on this path.

**FOSJsRoutingBundle** — rejected. The one URL the script needs is already in the form's
`action` attribute, generated by Twig.

**Stimulus / Symfony UX** — rejected *for this branch*, though it is the strongest candidate.
It would give a real controller lifecycle and remove the hand-rolled `init()`, but it
requires AssetMapper or Encore, i.e. an asset pipeline this project does not have. Adopting
one is a project-wide decision affecting all four existing hand-written scripts; making it
here, as a side effect of one cell, would be the wrong place to decide it. Recorded in
[Follow-ups](#follow-ups).

**A select/combobox widget (Select2, Tom Select, Choices.js)** — rejected. A native
`<select>` already provides keyboard type-ahead, a native mobile picker, correct
accessibility semantics for free, and zero bytes. It only becomes limiting past roughly 40
options per type; the default fixture set is below that, and `<optgroup>` grouping raises
the ceiling further. Revisit only if a user's category list actually outgrows it — and note
that a widget would also add a CDN dependency, which cuts against the app being usable
offline.

---

7. Performance
--------------

### 7.1 Existing N+1 on the list

`TransactionController::index()` selects only `transaction`, so the template's
`{{ transaction.account }}` and `{{ transaction.subCategory }}` lazy-load one proxy each per
row. Since this branch touches the same query, fix it here:

```php
->select('transaction', 'account', 'subCategory')
->leftJoin('transaction.account', 'account')
->leftJoin('transaction.subCategory', 'subCategory')
```

Pagerfanta's `QueryAdapter` handles the joins correctly for counting. This turns a
`2n + k` query page into a constant one.

### 7.2 Sub category options

Fetched **once per page render**, not once per row: two calls to
`SubCategoryRepository::findByTransactionType()`, grouped into an array in PHP, then read
from Twig.

`findByTransactionType()` currently does `innerJoin('s.topCategory', 't')` **without**
`addSelect('t')`, so reading `subCategory.topCategory.name` while grouping would lazy-load
one proxy per sub category. Add `->addSelect('t')` (or add a dedicated method if the change
risks affecting the other two callers — `TransactionImportController` and the fixtures).

### 7.3 Page weight

Worst case is 20 rows × the sub categories of one type. At ~60 sub categories per type that
is ~1200 `<option>` elements, roughly 40–60 KB of extra HTML before gzip — acceptable, and
only incurred for rows that are actually uncategorized, which shrinks as the user works.

If it ever becomes a problem, the mitigation that preserves the no-JS fallback is to render
one `<template>` of options per type at the bottom of the page and clone it into the cell on
first focus. **Not** in this branch.

### 7.4 The endpoint

One `SELECT` by primary key, one validation pass, one `UPDATE`. No external calls
([§2.6](#26-elasticsearch)). Nothing to optimise.

---

8. Security
-----------

### 8.1 There is no authentication

`config/packages/security.yaml` declares an in-memory provider with no users and an empty
`access_control`. Every route in this application is anonymous. That is defensible for a
localhost-only tool, but it means **CSRF protection is the only thing standing between a
malicious page in another tab and a write to this database**. It has to be right.

### 8.2 CSRF

* A per-transaction token, id `set-sub-category<transactionId>`, mirroring the existing
  `delete<id>` convention in `TransactionController::delete()`.
* Validated with `isCsrfTokenValid()` **before** any entity is touched.
* Failure returns `403` and changes nothing — it must not fall through to a redirect that
  looks like success.
* Per-transaction rather than one page-wide token so a token captured for one row cannot be
  replayed against another.

### 8.3 Mass assignment

The action reads exactly one request parameter, `subCategory`, and passes a resolved
`SubCategory` entity (or `null`) to the assigner. No Symfony Form, no
`$form->handleRequest()`, no property accessor walking request keys. `label`, `amount`,
`account`, `createdAt` and `tags` are unreachable from this endpoint by construction.

### 8.4 XSS

Transaction labels come from parsed bank statements — externally-influenced text — and sub
category names are user input. The JS module must build DOM nodes and assign `textContent`;
it must never concatenate server data into `innerHTML`.

Related, and worth knowing while working here: `templates/transaction/categorize.html.twig`
and `templates/transaction/export.html.twig` **do** build rows with `innerHTML` from
WebSocket payloads containing transaction labels. That is a pre-existing injection path via
a crafted statement label. Out of scope for this branch; flagged so the new code does not
copy the pattern, and worth its own issue.

### 8.5 Response shape

The JSON body is an explicit array literal of three known values. It never serializes the
`Transaction` entity, so no field added to the entity later can leak through this endpoint
by accident.

### 8.6 IDOR

Not applicable: single-tenant, no user model, nothing to own. Recorded so a reviewer does not
look for a missing ownership check.

---

9. Implementation checklist
---------------------------

Backend

- [ ] `src/Services/TransactionSubCategoryAssigner.php` — assign / validate / flush
- [ ] `src/Exception/` — typed exception carrying the violation message
- [ ] `TransactionController::setSubCategory()` — new `PATCH` route, CSRF, content negotiation
- [ ] `TransactionController::index()` — joins ([§7.1](#71-existing-n1-on-the-list)) + grouped sub categories ([§3.3](#33-controller-and-template-data))
- [ ] `SubCategoryRepository::findByTransactionType()` — `addSelect('t')` ([§7.2](#72-sub-category-options))

Frontend

- [ ] `templates/transaction/index.html.twig` — per-row form, row class, module import
- [ ] `public/js/inline_sub_category.js` — new ES module
- [ ] `public/styles/main.css` — `.uncategorized-transaction`, saved/error markers, cell sizing

Translations

- [ ] `translations/messages.en.yml`, `translations/messages.fr.yml` — keys from [§4](#4-translations)

Tests

- [ ] `tests/Controller/TransactionSubCategoryTest.php` — 12 functional cases
- [ ] `tests/Services/TransactionSubCategoryAssignerTest.php` — 4 unit cases
- [ ] `composer lint` green, PHPStan baseline not extended

---

Follow-ups
----------

Not in this branch, in rough priority order:

1. Click-to-edit for already-categorized rows, reusing this endpoint (it already accepts a
   clear, and the assigner already handles the categorized → categorized transition).
2. Migrate `TransactionController::new()` and `::edit()` onto
   `TransactionSubCategoryAssigner` to remove the `categorizedManually` duplication.
3. The "create a rule from this" link in the saved-state receipt — the moment right after
   choosing a category is when the user knows what the rule should say.
4. `3 transactions · 3 uncategorized`, with the count linking to the `Categorized: No`
   filter — the entry point to this whole workflow currently takes four interactions with
   the filter bar.
5. Bulk selection with an "apply to selected" toolbar.
6. Fix the `TypeError` in `templates/sub_category_transaction_rule/form.js.twig` that
   silently disables the rule preview panel.
7. Replace `innerHTML` row building in `categorize.html.twig` / `export.html.twig`
   ([§8.4](#84-xss)).
8. Decide on an asset pipeline (AssetMapper + Stimulus) for the four hand-written scripts as
   a project-wide change ([§6](#6-third-party-libraries)).

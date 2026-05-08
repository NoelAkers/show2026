# Flutter API — Implementation Plan

This document describes what must be built to implement the API contract defined in `flutter-laravel-API-contract.md`.

---

## Overview

The project currently has no API layer. Everything needs to be created from scratch:

- Token authentication via Laravel Sanctum (new dependency)
- Five REST endpoints consumed by the Flutter "Show Results" app
- No database schema changes required

**Key decisions:**
- Show class lookup uses `id` as the class number (no `number` column needed)
- Placement values are translated at the API layer (`first`/`second`/`third`/`highlyCommended` ↔ `1st`/`2nd`/`3rd`/`highly_commended`). Database and admin UI are unchanged.

---

## Step 1 — Install Laravel Sanctum

```bash
composer require laravel/sanctum
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"
php artisan migrate
```

Add `HasApiTokens` to `app/Models/User.php`:
```php
use Laravel\Sanctum\HasApiTokens;
use HasApiTokens, HasFactory, Notifiable, TwoFactorAuthenticatable;
```

---

## Step 2 — Bootstrap & Routes

**Modify** `bootstrap/app.php` — add `api: __DIR__.'/../routes/api.php'` to `->withRouting(...)`.

**Create** `routes/api.php`:

| Method | URI | Controller | Auth |
|--------|-----|------------|------|
| POST | `/api/login` | `Api\AuthController@login` | Public |
| POST | `/api/logout` | `Api\AuthController@logout` | Sanctum |
| GET | `/api/show-classes?number={id}` | `Api\ShowClassController@lookup` | Sanctum |
| GET | `/api/entries/{number}?show_class_id={id}` | `Api\EntryController@lookup` | Sanctum |
| POST | `/api/results` | `Api\ResultController@store` | Sanctum |

---

## Step 3 — Controllers

Create `app/Http/Controllers/Api/`:

### `AuthController`
- **`login`** — find user by email where `is_judge = true`, verify password, issue Sanctum token, return `{ token, user: { name } }`. Returns 401 if credentials wrong or user is not a judge.
- **`logout`** — delete current token, return 204.

### `ShowClassController`
- **`lookup`** — `ShowClass::findOrFail($request->number)`, return `ShowClassResource`. 404 if not found.

### `EntryController`
- **`lookup`** — find entry by `entry_number` with `exhibitor` and `showClass` eager-loaded. Return `EntryResource` with `belongs_to_class` flag (compares `entry->show_class_id` to `?show_class_id` query param). 404 if entry number doesn't exist.

### `ResultController`
- **`store`** — validate, enforce business rules, write results in a DB transaction. 201 on success, 422 with a descriptive `message` on failure.

---

## Step 4 — Form Requests

Create `app/Http/Requests/Api/`:

**`LoginRequest`**: `email` (required, email), `password` (required, string)

**`StoreResultsRequest`**:
- `show_class_id` — required, exists in `show_classes`
- `results` — required array
- `results.*.entry_number` — required integer
- `results.*.placement` — required, one of: `first`, `second`, `third`, `highlyCommended`

---

## Step 5 — API Resources

Create `app/Http/Resources/`:

**`ShowClassResource`** — returns `id`, `number` (same as `id`), `name`, `description`.

**`EntryResource`** — returns `entry_number`, `exhibitor_name` (from `exhibitor->full_name`), `show_class_id`, `show_class_name` (from `showClass->name`), `belongs_to_class`.

---

## Step 6 — Business Rules (ResultController)

Server must validate independently of the Flutter app:

1. All submitted `entry_number` values must exist in the database — 422 if any missing.
2. Every entry must belong to the submitted `show_class_id` — 422 if not (`"Entry {n} does not belong to this class."`).
3. At most one `first`, one `second`, one `third` in the submission — 422 if duplicated.
4. Check existing DB results for the class — 422 if `first`/`second`/`third` already recorded (`"First place has already been awarded in this class."`).
5. All `Result::create()` calls wrapped in `DB::transaction()`.
6. `entered_by_user_id` set to `$request->user()->id` on each result.

**Placement mapping:**

| API | Database |
|-----|----------|
| `first` | `1st` |
| `second` | `2nd` |
| `third` | `3rd` |
| `highlyCommended` | `highly_commended` |

---

## Files to Create / Modify

| | File |
|---|---|
| Create | `routes/api.php` |
| Modify | `bootstrap/app.php` |
| Modify | `app/Models/User.php` |
| Create | `app/Http/Controllers/Api/AuthController.php` |
| Create | `app/Http/Controllers/Api/ShowClassController.php` |
| Create | `app/Http/Controllers/Api/EntryController.php` |
| Create | `app/Http/Controllers/Api/ResultController.php` |
| Create | `app/Http/Requests/Api/LoginRequest.php` |
| Create | `app/Http/Requests/Api/StoreResultsRequest.php` |
| Create | `app/Http/Resources/ShowClassResource.php` |
| Create | `app/Http/Resources/EntryResource.php` |
| Create | `tests/Feature/Api/AuthTest.php` |
| Create | `tests/Feature/Api/ShowClassLookupTest.php` |
| Create | `tests/Feature/Api/EntryLookupTest.php` |
| Create | `tests/Feature/Api/ResultSubmissionTest.php` |

No migrations required.

---

## Verification

```bash
php artisan route:list --path=api
php artisan test --compact tests/Feature/Api/
```

Test coverage should include: login (valid judge / non-judge / wrong password), all protected endpoints returning 401 without token, class lookup (found / not found), entry lookup (correct class / wrong class / not found), result submission (success / duplicate unique placement / placement already in DB / entry from wrong class).

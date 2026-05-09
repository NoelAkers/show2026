# Show Results — Laravel API Specification

This document describes the API contract that the Laravel backend must implement for the Show Results Flutter app.

---

## General

| Concern | Detail |
|---|---|
| Base path | `/api/` |
| Request headers | `Accept: application/json`, `Content-Type: application/json` |
| Authentication | Bearer token via `Authorization: Bearer <token>` header |
| Auth package | Laravel Sanctum (token-based, not session cookies) |

Unauthenticated requests to any protected endpoint must return `401`.

---

## Endpoints

### POST /api/login

Creates a Sanctum token for a verified judge account.

**Auth required:** No

**Request body:**
```json
{ "email": "judge@example.com", "password": "secret" }
```

**Success — 200:**
```json
{
  "token": "1|abc123...",
  "user": { "name": "Jane Smith" }
}
```

**Error — 401:** Invalid credentials.

---

### POST /api/logout

Invalidates the current Sanctum token.

**Auth required:** Yes

**Request body:** None

**Success — 204:** No content.

> The app deletes its local token regardless of the server response, so this endpoint failing silently is acceptable — but it should exist and invalidate the token when called successfully.

---

### GET /api/show-classes?number={n}

Looks up a show class by its public class number (the number printed in the show schedule and entered by the judge).

**Auth required:** Yes

**Query parameter:** `number` — integer

**Success — 200:**
```json
{
  "id": 12,
  "number": 5,
  "name": "Best Rose",
  "description": "Single stem, any variety"
}
```

`description` may be `null` or omitted.

**Error — 404:** No class with that number exists.

---

### GET /api/entries/{number}?show_class_id={id}

Looks up a single entry by its entry number (scanned from the barcode label) and indicates whether it belongs to the active show class.

**Auth required:** Yes

**Path parameter:** `number` — the entry number from the barcode  
**Query parameter:** `show_class_id` — the `id` of the currently active show class

**Success — 200:**
```json
{
  "entry_number": 1042,
  "exhibitor_name": "Alice Brown",
  "show_class_id": 12,
  "show_class_name": "Best Rose",
  "belongs_to_class": true
}
```

`belongs_to_class` must be:
- `true` — the entry belongs to the requested `show_class_id`
- `false` — the entry exists but belongs to a different class

The app uses this distinction to show a specific "wrong class" warning rather than a generic "not found" error.

**Error — 404:** The entry number does not exist in the system at all.

---

### POST /api/results

Submits all results for a show class as a single batch. This is called once after the judge has reviewed and confirmed all awards.

**Auth required:** Yes

**Request body:**
```json
{
  "show_class_id": 12,
  "results": [
    { "entry_number": 1042, "placement": "first" },
    { "entry_number": 1078, "placement": "second" },
    { "entry_number": 1031, "placement": "third" },
    { "entry_number": 1055, "placement": "highlyCommended" },
    { "entry_number": 1019, "placement": "highlyCommended" }
  ]
}
```

**Placement values:** `first`, `second`, `third`, `highlyCommended`

**Success — 201:** No body required.

**Error — 422:** Validation failure. The app displays the `message` field, so a descriptive message is useful:
```json
{ "message": "First place has already been awarded in this class." }
```

---

## Business Rules

The app enforces these locally before submission, but the server must validate them independently and reject invalid submissions with `422`.

1. At most **one `first`**, one **`second`**, and one **`third`** placement per show class.
2. **Multiple `highlyCommended`** entries are permitted per show class.
3. Each submitted entry number must **belong to the specified `show_class_id`**.
4. All `/api/` endpoints except `/api/login` require a valid Sanctum token.

---

## Database Schema (minimum)

### `show_classes`
| Column | Type | Notes |
|---|---|---|
| `id` | integer | Primary key |
| `number` | integer | Unique; the number shown in the show schedule |
| `name` | string | e.g. "Best Rose" |
| `description` | string, nullable | Optional detail shown to the judge |

### `entries`
| Column | Type | Notes |
|---|---|---|
| `id` | integer | Primary key |
| `entry_number` | integer | Unique; printed on the barcode label |
| `exhibitor_name` | string | |
| `show_class_id` | foreign key | References `show_classes.id` |

### `results`
| Column | Type | Notes |
|---|---|---|
| `id` | integer | Primary key |
| `entry_number` | integer | References the entry |
| `show_class_id` | foreign key | References `show_classes.id` |
| `placement` | string/enum | `first`, `second`, `third`, `highlyCommended` |

---

## Laravel Sanctum Setup Notes

- Install: `composer require laravel/sanctum` and publish the config.
- On login: `$token = $user->createToken('judge-device')->plainTextToken` — return the plain text token in the response.
- On logout: `$request->user()->currentAccessToken()->delete()`.
- Protect all routes except `POST /api/login` with the `auth:sanctum` middleware.

---

## Notes

- The placement value `highlyCommended` is camelCase, matching Dart's enum name serialisation. If your Laravel conventions prefer snake_case (`highly_commended`), this can be adjusted on both sides — coordinate the change before building the submission feature.
- The `user.name` field returned by `/api/login` is not currently used by the app but is included for future display purposes.
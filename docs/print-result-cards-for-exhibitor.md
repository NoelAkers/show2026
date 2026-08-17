# Print Result Cards for an Exhibitor

## Request

Add a button on the exhibitor detail page (`resources/views/admin/exhibitors/show.blade.php`) to (re)print all result cards for that exhibitor — for last-minute fixes where an exhibitor's cards need reprinting without hunting through each of their classes individually.

## Solution

Reused the existing result cards print view (`admin.result-cards`, `ResultCardsController`) rather than building a new print page, following the same pattern already used for the per-class "Print Result Cards" button on `admin/show-classes/show.blade.php` (see `docs/issues.md`-adjacent prior work, GH #52).

- `ResultCardsController::index` gained an optional `exhibitor_id` query filter (alongside the existing `show_class_id` filter), scoping the join query to that exhibitor's entries. Also resolves an `$exhibitor` model for the view.
- `admin/results/cards.blade.php` now shows an exhibitor-scoped heading ("Result Cards — {name} —") and back-link (to the exhibitor's page) when `exhibitor_id` is present, and propagates `exhibitor_id` through the Unprinted/All filter toggle links and the empty-state "View all" link.
- `admin/exhibitors/show.blade.php` gained a "Print Result Cards" button next to the existing "Print Labels" button, linking to `admin.result-cards` with `filter=all&exhibitor_id=...` (opens in a new tab). Only rendered when the exhibitor has at least one entry with a placed result.
- `ExhibitorController::show` now eager-loads `entries.result` (was just `entries`) so that visibility check doesn't trigger N+1 queries.

Because `filter=all` is used (not `unprinted`), the button intentionally surfaces already-printed cards too, so it doubles as a reprint action — the existing "Modified since last print" badge and Mark as Printed flow are unchanged.

### Tests

- `tests/Feature/ResultCardsTest.php` — new test confirming `exhibitor_id` filter scopes results to that exhibitor only.
- `tests/Feature/ExhibitorDetailTest.php` — new tests confirming the button is shown when the exhibitor has a placed result, and hidden when they don't.

All new tests pass; `vendor/bin/pint --dirty` clean. One pre-existing unrelated test failure remains on `main` (`ExhibitorDetailTest`'s "2nd Place" vs `Result::placementLabel()` returning "2nd" — part of the known pre-existing failure set, see prior GH #52 work).

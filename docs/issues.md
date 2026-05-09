## Issues with code
### (No particular order)

---

1. Judges create and edit - validation rules for email doesn't accept ".co.uk" TLD                      [RESOLVED]

   **Root cause:** Not a TLD issue. The email was already in use by another user account (an admin), and
   the `Rule::unique('users')` constraint on the store request rejected it. The `.co.uk` TLD is fully
   supported by Laravel's standard `email` validation rule.

   **Resolution:** The system has been redesigned so that users can hold both admin and judge roles
   simultaneously. An `is_judge` boolean column was added to the `users` table. Judge status is now
   tracked via `is_judge` rather than the `role` column. When adding a judge by email address, if a user
   with that email already exists (e.g. an admin), they are granted judge status without a new account
   being created. The unique email constraint on the store request has been removed. Removing an
   admin-judge revokes their judge status without deleting their account.

2. Sort order in multiple CRUD edit pages doesn't appear to update after changing                       [NOT A BUG]

   **Investigation:** The `sort_order` field is correctly included in `$fillable`, validated in both
   store and update form requests, and saved via `$model->update($request->validated())` in both
   ShowSectionController and ShowClassController. No discrepancy exists between create and update flows.

3. When editing Sections, it seems to not accept any changes. The Name field is highlighted in red even if unchanged, suggesting that it is in error, but there is no message shown to clarify the error. The only option is to Cancel out of the edit.                        [RESOLVED]

   **Root cause:** `UpdateShowSectionRequest` had a broken `ignore()` clause in the unique name rule.
   The route parameter for `Route::resource('show-sections', ...)` is `{show_section}` (snake_case),
   but the code first referenced `$this->showSection` (invalid FormRequest property), then after a
   failed first fix, `$this->route('showSection')` (camelCase — wrong). Both resolve to `null`,
   leaving the unique check without an exclusion so the section's own name failed validation on save.
   `UpdateShowClassRequest` worked because it correctly used `$this->route('show_class')`.

   **Resolution:** Changed to `$this->route('show_section')`. Added a test for saving a section with
   its existing name unchanged, which would have caught this immediately.

4. Exhibitor needs an additional boolean field to indicate if they are a novice. It should default to true. [RESOLVED]

   **Resolution:** Added `is_novice boolean default true` column to the `exhibitors` table via migration.
   Updated the `Exhibitor` model (`#[Fillable]` and cast), `ExhibitorFactory` (default + `novice()`/`notNovice()` states),
   `StoreExhibitorRequest` and `UpdateExhibitorRequest` (validation rule), create/edit views (checkbox), and show view (detail row).
   Two new tests cover the default value on create and the ability to un-mark a novice.

5. Exhibitor may add or remove entries after paying for the initial entries, so they may need to pay an additional amount or receive a refund. The amount already paid and the amount currently owed need to be tracked. It doesn't appear that this logic is currently in the system.                        [RESOLVED]

   **Resolution:** Added `amount_paid_pence` integer column (default 0) to the `exhibitors` table. Added `balancePence()` method to the `Exhibitor` model (fee owed minus amount paid; negative value indicates a refund is owed). Updated `markPaid()` to set `amount_paid_pence` to the current fee, and `markUnpaid()` to reset it to 0. Added a new `updatePayment()` controller action, `UpdateExhibitorPaymentRequest`, and PATCH route for recording partial or corrected payments independently of the paid status flag. The exhibitor show page now displays Fee Owed, Amount Paid, and Balance rows, with an inline form to update the amount paid in pence.

6. Trophies may either be awarded on the basis of points scored across several classes or for a single entry selected by a judge. There needs to be an "is_points_based" boolean for each trophy to indicate which case applies. And a judge id to store the judge responsible if the trophy is not a points-based one.                        [RESOLVED]

   **Resolution:** Added `is_points_based` boolean (default true), `judge_id` nullable FK to `users`, and `winning_entry_id` nullable FK to `entries` to the `trophies` table. The `Trophy::winners()` method now branches on `is_points_based`: points-based trophies use the existing points calculation across assigned classes; judge-awarded trophies return the exhibitor of the stored `winning_entry_id`, or an empty collection if not yet set. The create and edit forms have a new Award Type select; Alpine.js conditionally shows the class checkboxes (points-based) or judge/winning-entry selectors (judge-awarded). Switching a trophy to judge-awarded automatically detaches any class assignments. The trophy index table now shows a Type badge and displays winners correctly for both types.

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

3.  When editing Sections, it seems to not accept any changes. The Name field is highlighted in red even if unchanged, suggesting that it is in error, but there is no message shown to clarify the error. The only option is to Cancel out of the edit.                        [RESOLVED]

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

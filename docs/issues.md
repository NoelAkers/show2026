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

3.  When editing Sections, it seems to not accept any changes. The Name field is highlighted in red even if unchanged, suggesting that it is in error, but there is no message shown to clarify the error. The only option is to Cancel out of the edit. The suggested Resolution below does NOT fix the issue, further investigation required! Note:  `UpdateShowClassRequest` DOES work correctly                                                      []

   **Root cause:** `UpdateShowSectionRequest` referenced `$this->showSection` in the unique rule's
   `ignore()` clause, which is not a valid `FormRequest` property and resolves to `null`. This meant
   the current record was never excluded from the uniqueness check, so even an unchanged name failed
   validation on every save attempt.

   **Resolution:** Changed to `$this->route('showSection')`, matching the pattern used by
   `UpdateShowClassRequest` and `UpdateJudgeRequest`.

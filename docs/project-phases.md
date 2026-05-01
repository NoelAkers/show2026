# Village Show — Project Phases

## Legend
- ✅ Completed
- 🔲 Pending

All feature tests use Pest and should be run with `php artisan test --compact`.

---

## Authentication (Fortify) ✅

Login, logout, password reset, email verification, and 2FA are fully implemented via Laravel Fortify. Tests exist in `tests/Feature/Auth/`.

---

## Phase 1 — Database Structure

Goal: All domain models, migrations, factories, and seeders in place. No UI yet.

### DB Design Reference

| Table | Key Columns |
|-------|-------------|
| `users` | + `role` string(20) default `'admin'`, + `phone` nullable — comment: `'admin'\|'judge'` |
| `show_sections` | `id, name, description (nullable), sort_order (int default 0), timestamps` |
| `show_classes` | `id, show_section_id (FK), name, description (nullable), max_entries_per_exhibitor (tinyint default 1), sort_order (int default 0), timestamps` |
| `exhibitors` | `id, name, email (nullable), phone (nullable), address (nullable), type (string comment:'adult\|junior'), is_resident (bool), has_paid (bool), timestamps` |
| `show_section_user` | pivot: `user_id, show_section_id` — judge-to-section assignments |
| `entries` | `id, show_class_id (FK), exhibitor_id (FK), entry_number (uint unique), timestamps` |
| `results` | `id, entry_id (FK unique), entered_by_user_id (nullable FK→users), placement (nullable string comment:'1st\|2nd\|3rd\|highly_commended'), notes (nullable text), timestamps` |
| `trophies` | `id, name, description (nullable), timestamps` |
| `show_class_trophy` | pivot: `trophy_id, show_class_id` |

**Notes:**
- Points are computed, not stored: `1st→3, 2nd→2, 3rd→1, highly_commended→0, null→0`
- Entry fee is configurable via `config/show.php` (`entry_fee_pence`, default `50`)
- No Enum DB columns; string columns with comments used; PHP Enums planned for future

---

### Phase 1.1 — Extend Users Table 🔲

Add `role` and `phone` to the existing `users` table.

**Files to create/modify:**
- `database/migrations/YYYY_MM_DD_add_role_and_phone_to_users_table.php`
- `app/Models/User.php` — add `role`, `phone` to `$fillable`; add `isAdmin()` / `isJudge()` helpers
- `database/factories/UserFactory.php` — add `admin()` and `judge()` factory states

**Tests:** `tests/Feature/UserRoleTest.php`
- [ ] `User::factory()->admin()->create()` has `role === 'admin'`
- [ ] `User::factory()->judge()->create()` has `role === 'judge'`
- [ ] `isAdmin()` returns `true` only for admin role
- [ ] `isJudge()` returns `true` only for judge role

---

### Phase 1.2 — ShowSection Model 🔲

**Files to create:**
- `database/migrations/YYYY_create_show_sections_table.php`
- `app/Models/ShowSection.php`
- `database/factories/ShowSectionFactory.php`

**Model:**
- `$fillable`: `name, description, sort_order`
- Relationships: `hasMany(ShowClass::class)`, `belongsToMany(User::class)` (judges)
- Scope: `ordered()` → `orderBy('sort_order')`

**Tests:** `tests/Feature/ShowSectionTest.php`
- [ ] Factory creates a valid `ShowSection`
- [ ] `name` is required
- [ ] `sort_order` defaults to 0
- [ ] `ordered()` scope returns sections in `sort_order` ASC

---

### Phase 1.3 — ShowClass Model 🔲

**Files to create:**
- `database/migrations/YYYY_create_show_classes_table.php`
- `app/Models/ShowClass.php`
- `database/factories/ShowClassFactory.php`

**Model:**
- `$fillable`: `show_section_id, name, description, max_entries_per_exhibitor, sort_order`
- Relationships: `belongsTo(ShowSection::class)`, `hasMany(Entry::class)`, `belongsToMany(Trophy::class)`
- Scope: `ordered()`
- Unique index: `[show_section_id, name]`

**Tests:** `tests/Feature/ShowClassTest.php`
- [ ] Factory creates a valid `ShowClass` linked to a `ShowSection`
- [ ] `show_section_id` is required
- [ ] `name` must be unique within a section
- [ ] `max_entries_per_exhibitor` defaults to 1
- [ ] Deleting a `ShowClass` with entries is prevented

---

### Phase 1.4 — Exhibitor Model 🔲

**Files to create:**
- `database/migrations/YYYY_create_exhibitors_table.php`
- `app/Models/Exhibitor.php`
- `database/factories/ExhibitorFactory.php`

**Model:**
- `$fillable`: all non-timestamp columns
- `$casts`: `is_resident → boolean`, `has_paid → boolean`
- Relationships: `hasMany(Entry::class)`
- Helpers: `isAdult()`, `isJunior()`, `totalEntries()`, `chargeableEntries()`, `feeOwedPence()`
- Fee logic: juniors always £0; adults pay for entries 1–10 (`min(count, 10) × config('show.entry_fee_pence')`)
- Factory states: `adult()`, `junior()`, `resident()`, `nonResident()`

**Tests:** `tests/Feature/ExhibitorTest.php`
- [ ] Factory creates valid adult and junior exhibitors
- [ ] `feeOwedPence()` returns 0 for juniors regardless of entry count
- [ ] `feeOwedPence()` charges for entries 1–10 for adults
- [ ] `feeOwedPence()` caps at 10 chargeable entries for adults with 11+ entries
- [ ] `chargeableEntries()` returns 0 for juniors

---

### Phase 1.5 — Judge-Section Pivot 🔲

**Files to create:**
- `database/migrations/YYYY_create_show_section_user_table.php`

**Migration:** composite primary key on `(user_id, show_section_id)`; both FK with cascade on delete.

**Model updates:**
- `User.php` — add `belongsToMany(ShowSection::class)` as `assignedSections()`
- `ShowSection.php` — `belongsToMany(User::class)` as `judges()` (already planned in 1.2)

**Tests:** covered in Phase 2.3 access control tests

---

### Phase 1.6 — Entry Model 🔲

**Files to create:**
- `database/migrations/YYYY_create_entries_table.php`
- `app/Models/Entry.php`
- `database/factories/EntryFactory.php`

**Model:**
- `$fillable`: `show_class_id, exhibitor_id` (entry_number is auto-assigned)
- Relationships: `belongsTo(ShowClass::class)`, `belongsTo(Exhibitor::class)`, `hasOne(Result::class)`
- Boot: auto-assign `entry_number` as `(Entry::max('entry_number') ?? 0) + 1` on `creating`
- Helper: `hasResult()`

**Tests:** `tests/Feature/EntryTest.php`
- [ ] Factory creates a valid `Entry` with an auto-assigned `entry_number`
- [ ] `entry_number` is globally unique (DB unique constraint)
- [ ] Two entries in the same seeding sequence get consecutive numbers
- [ ] `hasResult()` returns `false` when no result exists and `true` when it does

---

### Phase 1.7 — Result Model 🔲

**Files to create:**
- `database/migrations/YYYY_create_results_table.php`
- `app/Models/Result.php`
- `database/factories/ResultFactory.php`

**Model:**
- `$fillable`: `entry_id, entered_by_user_id, placement, notes`
- Relationships: `belongsTo(Entry::class)`, `belongsTo(User::class, 'entered_by_user_id')`
- `points()` — `'1st'→3, '2nd'→2, '3rd'→1, 'highly_commended'→0, null→0`
- `placementLabel()` — human-readable string

**Tests:** `tests/Feature/ResultTest.php`
- [ ] Factory creates a valid `Result`
- [ ] `points()` returns correct values for each placement value
- [ ] `points()` returns 0 for null placement
- [ ] One result per entry enforced (DB unique on `entry_id`)

---

### Phase 1.8 — Trophy Model & Pivot 🔲

**Files to create:**
- `database/migrations/YYYY_create_trophies_table.php`
- `database/migrations/YYYY_create_show_class_trophy_table.php`
- `app/Models/Trophy.php`
- `database/factories/TrophyFactory.php`

**Model:**
- `$fillable`: `name, description`
- Relationships: `belongsToMany(ShowClass::class)`
- `winners()` — returns a collection of `['exhibitor' => Exhibitor, 'points' => int]` for the top scorer(s) across this trophy's classes

**Tests:** `tests/Feature/TrophyTest.php`
- [ ] Factory creates a valid `Trophy`
- [ ] `winners()` returns the exhibitor with the most points across assigned classes
- [ ] `winners()` returns all exhibitors when there is a points tie
- [ ] `winners()` returns an empty collection when no results are entered
- [ ] A `ShowClass` can be assigned to multiple trophies

---

### Phase 1.9 — Config & Database Seeder 🔲

**Files to create/modify:**
- `config/show.php` — `entry_fee_pence` (default `50` = £0.50 per entry)
- `database/seeders/DatabaseSeeder.php` — seed 3 sections, 5–10 classes each, 20 exhibitors (mix of adult/junior/resident), 2 judge users with assigned sections, entries across classes

**Verification:** `php artisan migrate:fresh --seed` completes without errors

---

## Phase 2 — Roles & Access Control

Goal: Role-based routing. Admins can access all admin routes. Judges can access only their result-entry area. Guests are redirected to login.

---

### Phase 2.1 — Role Middleware 🔲

**Files to create:**
- `app/Http/Middleware/RequireAdmin.php` — abort 403 if `auth()->user()->role !== 'admin'`
- `app/Http/Middleware/RequireJudge.php` — passes for both `'admin'` and `'judge'` roles; aborts 403 otherwise
- Register both aliases in `bootstrap/app.php`

**Tests:** `tests/Feature/MiddlewareTest.php`
- [ ] Guest is redirected to `/login` on `GET /admin/show-sections`
- [ ] Judge user receives 403 on an admin-only route
- [ ] Admin user can access admin routes
- [ ] Judge user can access judge routes
- [ ] Admin user can also access judge routes (admin is a superset)

---

### Phase 2.2 — Admin Route Group 🔲

**Files to modify:**
- `routes/web.php` — wrap all admin routes in `prefix('admin')->name('admin.')->middleware(['auth', 'admin'])` group

**Status:** No additional tests beyond Phase 2.1

---

### Phase 2.3 — Judge Landing & Login Redirect 🔲

After login, judges are redirected to `/judge/sections`; admins go to `/dashboard`.

**Files to create/modify:**
- `app/Providers/FortifyServiceProvider.php` — update `Fortify::redirectsUsersTo()` callback to check role
- `app/Http/Controllers/Judge/SectionController.php` — index: shows sections assigned to the logged-in judge
- `resources/views/judge/sections/index.blade.php`
- `routes/web.php` — add `/judge` prefix group with `middleware(['auth', 'judge'])`

**Tests:** `tests/Feature/JudgeAccessTest.php`
- [ ] Judge user is redirected to `/judge/sections` after login
- [ ] Admin user is redirected to `/dashboard` after login
- [ ] Judge sees only their assigned sections on `/judge/sections`
- [ ] Judge sees an empty-state message when assigned to no sections
- [ ] Judge receives 403 on `GET /admin/show-sections`

---

## Phase 3 — Show Structure Management

Goal: Admin can create, edit, delete, and list show sections and classes.

Uses **Laravel Controllers** (not Livewire) per project conventions.

---

### Phase 3.1 — Show Sections CRUD 🔲

**Files to create:**
- `app/Http/Controllers/Admin/ShowSectionController.php` — `index, create, store, edit, update, destroy`
- `app/Http/Requests/Admin/StoreShowSectionRequest.php`
- `app/Http/Requests/Admin/UpdateShowSectionRequest.php`
- `resources/views/admin/show-sections/index.blade.php`
- `resources/views/admin/show-sections/create.blade.php`
- `resources/views/admin/show-sections/edit.blade.php`
- Sidebar navigation link added

**Validation:** `name` required unique; `sort_order` required integer ≥ 0.

**Delete rule:** Blocked if the section has any classes (return back with error message).

**Tests:** `tests/Feature/Admin/ShowSectionCrudTest.php`
- [ ] Admin can view sections list at `GET /admin/show-sections`
- [ ] Admin can create a section with valid data
- [ ] Creating a section with a duplicate name fails validation
- [ ] Admin can update an existing section
- [ ] Admin can delete a section that has no classes
- [ ] Admin cannot delete a section that has classes (error message returned)
- [ ] Guest is redirected to `/login` on all section routes
- [ ] Judge receives 403 on all section routes

---

### Phase 3.2 — Show Classes CRUD 🔲

**Files to create:**
- `app/Http/Controllers/Admin/ShowClassController.php` — `index, create, store, edit, update, destroy` (scoped to section)
- `app/Http/Requests/Admin/StoreShowClassRequest.php`
- `app/Http/Requests/Admin/UpdateShowClassRequest.php`
- `resources/views/admin/show-classes/index.blade.php`
- `resources/views/admin/show-classes/create.blade.php`
- `resources/views/admin/show-classes/edit.blade.php`

**Routes:** Nested under sections — `admin/show-sections/{section}/classes`

**Validation:** `name` unique within section; `max_entries_per_exhibitor` integer ≥ 1; `sort_order` integer ≥ 0.

**Delete rule:** Blocked if the class has any entries.

**Tests:** `tests/Feature/Admin/ShowClassCrudTest.php`
- [ ] Admin can view class list for a section
- [ ] Admin can create a class with valid data
- [ ] Duplicate class name within the same section fails validation
- [ ] Same class name in two different sections is allowed
- [ ] Admin can update a class
- [ ] Admin can delete a class with no entries
- [ ] Admin cannot delete a class with entries
- [ ] Guest / judge access returns redirect / 403

---

## Phase 4 — Exhibitor Management

Goal: Admin can register, edit, delete, and list exhibitors, with fee calculation and payment tracking.

---

### Phase 4.1 — Exhibitor List 🔲

**Files to create:**
- `app/Http/Controllers/Admin/ExhibitorController.php` — `index` action
- `resources/views/admin/exhibitors/index.blade.php` — searchable, filterable table

**Features:** Search by name; filter by `type`, `is_resident`, `has_paid`; display fee owed per row.

**Tests:** `tests/Feature/Admin/ExhibitorListTest.php`
- [ ] Admin sees exhibitor list at `GET /admin/exhibitors`
- [ ] Search by name filters results correctly
- [ ] Filter by type (adult/junior) works
- [ ] Filter by residency (resident/non-resident) works
- [ ] Filter by payment status (paid/unpaid) works
- [ ] Fee owed is displayed in each row

---

### Phase 4.2 — Exhibitor Create & Edit 🔲

**Files to create:**
- `app/Http/Requests/Admin/StoreExhibitorRequest.php`
- `app/Http/Requests/Admin/UpdateExhibitorRequest.php`
- `resources/views/admin/exhibitors/create.blade.php`
- `resources/views/admin/exhibitors/edit.blade.php`

**Validation:** `name` required; `email` nullable email; `type` required in `[adult, junior]`; `is_resident` boolean.

**Tests:** `tests/Feature/Admin/ExhibitorCrudTest.php`
- [ ] Admin can create an exhibitor with all fields
- [ ] Admin can create an exhibitor with only required fields (nullables omitted)
- [ ] `type` must be `adult` or `junior` (validation error otherwise)
- [ ] Admin can update exhibitor details
- [ ] All existing fields are pre-populated on the edit form

---

### Phase 4.3 — Exhibitor Delete 🔲

**Tests:** _(appended to `ExhibitorCrudTest.php`)_
- [ ] Admin can delete an exhibitor with no entries
- [ ] Admin cannot delete an exhibitor with entries (error message shown)

---

### Phase 4.4 — Fee Calculation Display 🔲

_(Logic lives in `Exhibitor` model — Phase 1.4. This phase covers the display layer.)_

**Files to create:**
- `resources/views/admin/exhibitors/show.blade.php` — exhibitor detail with fee summary section

**Tests:** `tests/Feature/Admin/ExhibitorFeeTest.php`
- [ ] Junior exhibitor with 15 entries shows £0.00 fee
- [ ] Adult exhibitor with 5 entries shows fee for 5 entries
- [ ] Adult exhibitor with 10 entries shows fee for 10 entries
- [ ] Adult exhibitor with 12 entries shows fee for 10 entries only (capped)
- [ ] Fee summary shows: total entries, chargeable entries, total fee owed

---

### Phase 4.5 — Payment Tracking 🔲

**Files to modify:**
- `app/Http/Controllers/Admin/ExhibitorController.php` — add `markPaid` / `markUnpaid` actions
- `resources/views/admin/exhibitors/index.blade.php` — paid/unpaid badge + toggle action
- `routes/web.php` — `PATCH /admin/exhibitors/{exhibitor}/mark-paid`

**Tests:** `tests/Feature/Admin/ExhibitorPaymentTest.php`
- [ ] Admin can mark an exhibitor as paid
- [ ] Admin can mark a paid exhibitor as unpaid
- [ ] Payment status persists after page reload
- [ ] Exhibitor list reflects the correct paid/unpaid status

---

## Phase 5 — Judge Management

Goal: Admin can create judge accounts, assign them to sections, and manage their details.

---

### Phase 5.1 — Judge List 🔲

**Files to create:**
- `app/Http/Controllers/Admin/JudgeController.php` — `index`
- `resources/views/admin/judges/index.blade.php` — table: name, email, assigned sections, results entered

**Tests:** `tests/Feature/Admin/JudgeListTest.php`
- [ ] Admin sees judge list at `GET /admin/judges`
- [ ] Each row shows: name, email, assigned sections, number of results entered

---

### Phase 5.2 — Judge Create & Edit 🔲

**Files to create:**
- `app/Http/Requests/Admin/StoreJudgeRequest.php`
- `app/Http/Requests/Admin/UpdateJudgeRequest.php`
- `resources/views/admin/judges/create.blade.php`
- `resources/views/admin/judges/edit.blade.php`

**Create logic:** Creates a `User` with `role='judge'` and a random temporary password; syncs `section_ids` via pivot.

**Validation:** `name` required; `email` required unique; `phone` nullable; `section_ids` nullable array of valid section IDs.

**Tests:** `tests/Feature/Admin/JudgeCrudTest.php`
- [ ] Admin can create a judge account with name, email, and phone
- [ ] Created user has `role === 'judge'`
- [ ] Judge email must be unique in `users` table
- [ ] Admin can assign sections when creating a judge
- [ ] Admin can update judge name, email, and phone
- [ ] Admin can update section assignments (add and remove)

---

### Phase 5.3 — Section Assignment 🔲

_(Covered in Phase 5.2 tests. This sub-phase ensures the assignment UI is complete.)_

**Tests:** _(appended to `JudgeCrudTest.php`)_
- [ ] A judge can be assigned to multiple sections simultaneously
- [ ] Section assignments appear on both the judge detail and the section detail
- [ ] Removing all section assignments from a judge works correctly

---

## Phase 6 — Entry Management

Goal: Admin can record entries (exhibitor + class), view entries per class and per exhibitor, and delete entries.

---

### Phase 6.1 — Record Entries 🔲

**Files to create:**
- `app/Http/Controllers/Admin/EntryController.php` — `store` (scoped to `ShowClass`)
- `app/Http/Requests/Admin/StoreEntryRequest.php`
- `resources/views/admin/show-classes/show.blade.php` — class detail page: entries list + add entry form

**Custom validation rule:** Exhibitor has not exceeded `max_entries_per_exhibitor` for this class.

**Routes:** `POST /admin/show-classes/{class}/entries`

**Tests:** `tests/Feature/Admin/EntryManagementTest.php`
- [ ] Admin can record an entry linking an exhibitor to a class
- [ ] `entry_number` is auto-assigned and globally unique
- [ ] Exhibitor cannot exceed `max_entries_per_exhibitor` for a class (validation error returned)
- [ ] Entry appears in the class entry list after creation
- [ ] Exhibitor's chargeable fee recalculates after adding an entry

---

### Phase 6.2 — View Entries per Class 🔲

_(Covered by the class show page in Phase 6.1.)_

**Tests:** _(appended to `EntryManagementTest.php`)_
- [ ] Entry list shows: entry number, exhibitor name, exhibitor type, result status
- [ ] Result status indicator is correct (no result / result entered)

---

### Phase 6.3 — View All Entries for an Exhibitor 🔲

**Files to create/modify:**
- `resources/views/admin/exhibitors/show.blade.php` — full entry history section

**Tests:** `tests/Feature/Admin/ExhibitorDetailTest.php`
- [ ] Admin views exhibitor detail at `GET /admin/exhibitors/{exhibitor}`
- [ ] Entry list shows: class name, section name, entry number, result placement
- [ ] Fee summary is current and correct

---

### Phase 6.4 — Delete Entry 🔲

**Files to modify:**
- `app/Http/Controllers/Admin/EntryController.php` — add `destroy` action

**Route:** `DELETE /admin/show-classes/{class}/entries/{entry}`

**Tests:** _(appended to `EntryManagementTest.php`)_
- [ ] Admin can delete an entry that has no result
- [ ] Admin cannot delete an entry that already has a result (error shown)
- [ ] Exhibitor's fee recalculates after deleting an entry

---

## Phase 7 — Results & Judging

Goal: Judges enter and edit results for their assigned sections. Admins can manage all results. Points leaderboard available.

---

### Phase 7.1 — Judge Results Entry View 🔲

**Files to create:**
- `app/Http/Controllers/Judge/ResultController.php` — `index` (classes in assigned sections), `store`, `update`
- `resources/views/judge/results/index.blade.php` — entries in a class with inline result form per entry

**Valid placements:** `1st`, `2nd`, `3rd`, `highly_commended`, or clear (null)

**Uniqueness rule:** A class may have at most one `1st`, one `2nd`, and one `3rd`; any number of `highly_commended`.

**Routes:** `/judge/sections/{section}/classes/{class}/results` under `judge` middleware group

**Tests:** `tests/Feature/Judge/ResultEntryTest.php`
- [ ] Judge sees only classes in their assigned sections
- [ ] Judge can enter a placement and notes for an entry
- [ ] Judge cannot assign a second `1st` place in the same class
- [ ] Judge cannot assign a second `2nd` place in the same class
- [ ] Judge cannot assign a second `3rd` place in the same class
- [ ] Multiple `highly_commended` in one class is allowed
- [ ] Judge can clear a placement (set to null)
- [ ] Entered result is immediately visible to admin

---

### Phase 7.2 — Edit Result (Judge) 🔲

_(Store + update in Phase 7.1 controller.)_

**Tests:** _(appended to `ResultEntryTest.php`)_
- [ ] Judge can change an existing placement
- [ ] Uniqueness rule is enforced when updating (swapping placements between entries)
- [ ] Exhibitor points total changes after updating a result

---

### Phase 7.3 — Admin Results Management 🔲

**Files to create:**
- `app/Http/Controllers/Admin/ResultController.php` — `store`, `update`, `destroy` (any class)
- Inline result entry added to `resources/views/admin/show-classes/show.blade.php`

**Behaviour:** Same uniqueness constraints as judge; `entered_by_user_id` set to the acting admin's ID.

**Tests:** `tests/Feature/Admin/ResultManagementTest.php`
- [ ] Admin can enter a result for any class in any section
- [ ] Admin can update any result
- [ ] Admin cannot create a duplicate `1st`/`2nd`/`3rd` in a class
- [ ] `entered_by_user_id` is set to the admin's user ID
- [ ] Judge receives 403 when accessing admin result routes

---

### Phase 7.4 — Points Leaderboard 🔲

**Files to create:**
- `app/Http/Controllers/Admin/LeaderboardController.php`
- `resources/views/admin/leaderboard/index.blade.php`

**Logic:** Aggregate `results.placement` → points per exhibitor; optional section filter via query param.

**Tests:** `tests/Feature/Admin/LeaderboardTest.php`
- [ ] Leaderboard shows exhibitors in descending points order
- [ ] Section filter shows only points from that section's classes
- [ ] Exhibitor with no results shows 0 points
- [ ] Tied exhibitors appear at the same rank position

---

## Phase 8 — Trophies

Goal: Admin configures trophies (assigning classes); system auto-calculates winners.

---

### Phase 8.1 — Trophy CRUD 🔲

**Files to create:**
- `app/Http/Controllers/Admin/TrophyController.php` — `index, create, store, edit, update, destroy`
- `app/Http/Requests/Admin/StoreTrophyRequest.php`
- `app/Http/Requests/Admin/UpdateTrophyRequest.php`
- `resources/views/admin/trophies/index.blade.php`
- `resources/views/admin/trophies/create.blade.php`
- `resources/views/admin/trophies/edit.blade.php`

**Validation:** `name` required; `class_ids` nullable array of valid `ShowClass` IDs.

**Tests:** `tests/Feature/Admin/TrophyCrudTest.php`
- [ ] Admin can create a trophy with a name and class assignments
- [ ] Admin can create a trophy with no class assignments
- [ ] Admin can update name, description, and class assignments
- [ ] A class can be assigned to multiple trophies simultaneously
- [ ] Admin can delete a trophy
- [ ] Guest / judge access returns redirect / 403

---

### Phase 8.2 — Trophy Winner Calculation 🔲

_(Logic in `Trophy::winners()` — Phase 1.8.)_

**Tests:** `tests/Feature/TrophyWinnerTest.php`
- [ ] Winner is the exhibitor with the most points across the trophy's assigned classes
- [ ] All tied exhibitors are returned when points are equal
- [ ] Adding a new result updates the winner output
- [ ] Trophy with no assigned classes returns empty winner list
- [ ] Trophy with assigned classes but no results returns empty winner list

---

### Phase 8.3 — Trophy List with Winners 🔲

**Files to modify:**
- `resources/views/admin/trophies/index.blade.php` — current winner(s) per row

**Tests:** _(appended to `TrophyCrudTest.php`)_
- [ ] Trophy index shows the current winner for each trophy
- [ ] "No winner yet" displayed when no results entered
- [ ] All tied winners are listed when there is a tie

---

## Phase 9 — Public Display

Goal: Public-facing pages (no login required) showing the show schedule, results, and trophy winners.

---

### Phase 9.1 — Public Show Schedule 🔲

**Files to create:**
- `app/Http/Controllers/Public/ScheduleController.php`
- `resources/views/public/schedule/index.blade.php`
- `resources/views/layouts/public.blade.php` — new public layout (no auth nav)

**Route:** `GET /` or `GET /schedule` (no auth middleware)

**Data shown:** Sections in sort_order → classes in sort_order. No exhibitor contact details, no fees, no payment status.

**Tests:** `tests/Feature/Public/ScheduleTest.php`
- [ ] Public schedule is accessible without login
- [ ] Sections are listed in `sort_order` ASC
- [ ] Classes are listed within their section in `sort_order` ASC
- [ ] No exhibitor contact details are exposed in the response
- [ ] No payment status information is exposed

---

### Phase 9.2 — Public Results 🔲

**Files to create:**
- `app/Http/Controllers/Public/ResultController.php`
- `resources/views/public/results/index.blade.php`

**Data shown:** Exhibitor name + placement only. Points hidden. Classes with no results show "Results pending."

**Tests:** `tests/Feature/Public/PublicResultsTest.php`
- [ ] Public results page accessible without login
- [ ] Placement labels (1st / 2nd / 3rd / Highly Commended) shown per result
- [ ] Points totals are NOT present anywhere in the response
- [ ] Class with no results shows "Results pending"
- [ ] Exhibitor email, phone, and address are NOT present in the response

---

### Phase 9.3 — Public Trophy Winners 🔲

**Files to create:**
- `app/Http/Controllers/Public/TrophyController.php`
- `resources/views/public/trophies/index.blade.php`

**Data shown:** Trophy name + winner name (or "To be announced").

**Tests:** `tests/Feature/Public/PublicTrophiesTest.php`
- [ ] Public trophies page accessible without login
- [ ] Trophy name and winner's name shown
- [ ] "To be announced" shown when no results have been entered
- [ ] All tied winners listed when there is a tie

---

## Phase 10 — Admin Dashboard

Goal: Replace placeholder dashboard with live show stats.

---

### Phase 10.1 — Dashboard Stats 🔲

**Files to create/modify:**
- `app/Http/Controllers/DashboardController.php` (create; point `GET /dashboard` route here)
- `resources/views/dashboard.blade.php` — replace placeholder with stats grid

**Stats displayed:**
- Total sections / total classes
- Total exhibitors (adult / junior breakdown)
- Total entries
- Results entered vs. outstanding (total entries minus entries with a result)
- Exhibitors paid / unpaid (adult exhibitors only)

**Tests:** `tests/Feature/DashboardTest.php` _(extend existing)_
- [ ] Dashboard accessible to admin at `GET /dashboard`
- [ ] Correct section count displayed
- [ ] Correct class count displayed
- [ ] Correct exhibitor count displayed (adult/junior split)
- [ ] Correct entry count displayed
- [ ] Correct results entered vs. outstanding counts displayed
- [ ] Correct paid/unpaid exhibitor count displayed
- [ ] Judge accessing `GET /dashboard` is redirected to `/judge/sections`

---

## Verification (End-to-End)

1. `php artisan migrate:fresh --seed` — all migrations run cleanly with demo data
2. `php artisan test --compact` — all tests green
3. Log in as admin → create section → create class → create exhibitor → record entry → enter result → confirm fee and points update
4. Log in as judge → confirm only assigned sections visible → enter a result
5. Visit `/schedule` and `/results` without logging in — pages load and contain no private data
6. Visit `/trophies` publicly — winner shown once results are entered

---

## Appendix: Completion Status

| Phase | Description | Status |
|-------|-------------|--------|
| Auth (Fortify) | Login, logout, password reset, 2FA | ✅ Complete |
| Phase 1.1 | Extend users table (role + phone) | 🔲 Pending |
| Phase 1.2 | ShowSection model | 🔲 Pending |
| Phase 1.3 | ShowClass model | 🔲 Pending |
| Phase 1.4 | Exhibitor model | 🔲 Pending |
| Phase 1.5 | Judge-Section pivot | 🔲 Pending |
| Phase 1.6 | Entry model | 🔲 Pending |
| Phase 1.7 | Result model | 🔲 Pending |
| Phase 1.8 | Trophy model & pivot | 🔲 Pending |
| Phase 1.9 | Config & seeder | 🔲 Pending |
| Phase 2.1 | Role middleware | 🔲 Pending |
| Phase 2.2 | Admin route group | 🔲 Pending |
| Phase 2.3 | Judge landing & login redirect | 🔲 Pending |
| Phase 3.1 | Show Sections CRUD | 🔲 Pending |
| Phase 3.2 | Show Classes CRUD | 🔲 Pending |
| Phase 4.1 | Exhibitor list | 🔲 Pending |
| Phase 4.2 | Exhibitor create & edit | 🔲 Pending |
| Phase 4.3 | Exhibitor delete | 🔲 Pending |
| Phase 4.4 | Fee calculation display | 🔲 Pending |
| Phase 4.5 | Payment tracking | 🔲 Pending |
| Phase 5.1 | Judge list | 🔲 Pending |
| Phase 5.2 | Judge create & edit | 🔲 Pending |
| Phase 5.3 | Section assignment | 🔲 Pending |
| Phase 6.1 | Record entries | 🔲 Pending |
| Phase 6.2 | View entries per class | 🔲 Pending |
| Phase 6.3 | View all entries for an exhibitor | 🔲 Pending |
| Phase 6.4 | Delete entry | 🔲 Pending |
| Phase 7.1 | Judge results entry view | 🔲 Pending |
| Phase 7.2 | Edit result (judge) | 🔲 Pending |
| Phase 7.3 | Admin results management | 🔲 Pending |
| Phase 7.4 | Points leaderboard | 🔲 Pending |
| Phase 8.1 | Trophy CRUD | 🔲 Pending |
| Phase 8.2 | Trophy winner calculation | 🔲 Pending |
| Phase 8.3 | Trophy list with winners | 🔲 Pending |
| Phase 9.1 | Public show schedule | 🔲 Pending |
| Phase 9.2 | Public results | 🔲 Pending |
| Phase 9.3 | Public trophy winners | 🔲 Pending |
| Phase 10.1 | Admin dashboard stats | 🔲 Pending |

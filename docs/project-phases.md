# Village Show — Project Phases

## Legend
- ✅ Completed
- 🔲 Pending

All feature tests use Pest and should be run with `php artisan test --compact`.

---

## Authentication (Fortify) ✅

Login, logout, password reset, email verification, and 2FA are fully implemented via Laravel Fortify. Tests exist in `tests/Feature/Auth/`.

---

## Phase 1 — Database Structure ✅

Goal: All domain models, migrations, factories, and seeders in place. No UI yet.

### DB Design Reference

| Table | Key Columns |
|-------|-------------|
| `users` | + `role` string(20) default `'admin'`, + `phone` nullable — comment: `'admin'\|'judge'` |
| `show_sections` | `id, name, description (nullable), sort_order (int default 0), timestamps` |
| `show_classes` | `id, show_section_id (FK), name, description (nullable), max_entries_per_exhibitor (tinyint default 5), sort_order (int default 0), timestamps` |
| `exhibitors` | `id, first_name, last_name, full_name, sort_name, email (nullable), phone (nullable), address (nullable), type (string comment:'adult\|junior'), is_resident (bool), has_paid (bool), timestamps` |
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

### Phase 1.1 — Extend Users Table ✅

Add `role` and `phone` to the existing `users` table.

**Files to create/modify:**
- `database/migrations/2026_05_01_202102_add_role_and_phone_to_users_table.php`
- `app/Models/User.php` — add `role`, `phone` to `$fillable`; add `isAdmin()` / `isJudge()` helpers
- `database/factories/UserFactory.php` — add `admin()` and `judge()` factory states

**Tests:** `tests/Feature/UserRoleTest.php`
- [x] `User::factory()->admin()->create()` has `role === 'admin'`
- [x] `User::factory()->judge()->create()` has `role === 'judge'`
- [x] `isAdmin()` returns `true` only for admin role
- [x] `isJudge()` returns `true` only for judge role

---

### Phase 1.2 — ShowSection Model ✅

**Files to create:**
- `database/migrations/2026_05_02_134049_create_show_sections_table.php`
- `app/Models/ShowSection.php`
- `database/factories/ShowSectionFactory.php`

**Model:**
- `$fillable`: `name, description, sort_order`
- Relationships: `hasMany(ShowClass::class)`, `belongsToMany(User::class)` (judges)
- Scope: `ordered()` → `orderBy('sort_order')`

**Tests:** `tests/Feature/ShowSectionTest.php`
- [x] Factory creates a valid `ShowSection`
- [x] `name` is required
- [x] `sort_order` defaults to 0
- [x] `ordered()` scope returns sections in `sort_order` ASC

---

### Phase 1.3 — ShowClass Model ✅

**Files to create:**
- `database/migrations/2026_05_02_134214_create_show_classes_table.php`
- `app/Models/ShowClass.php`
- `database/factories/ShowClassFactory.php`

**Model:**
- `$fillable`: `show_section_id, name, description, max_entries_per_exhibitor, sort_order`
- Relationships: `belongsTo(ShowSection::class)`, `hasMany(Entry::class)`, `belongsToMany(Trophy::class)`
- Scope: `ordered()`
- Unique index: `[show_section_id, name]`

**Tests:** `tests/Feature/ShowClassTest.php`
- [x] Factory creates a valid `ShowClass` linked to a `ShowSection`
- [x] `show_section_id` is required
- [x] `name` must be unique within a section
- [x] Same class name in two different sections is allowed
- [x] `max_entries_per_exhibitor` defaults to 5
- [x] Deleting a `ShowClass` with entries is prevented (tested via EntryTest)

---

### Phase 1.4 — Exhibitor Model ✅

**Files to create:**
- `database/migrations/2026_05_02_134329_create_exhibitors_table.php`
- `app/Models/Exhibitor.php`
- `database/factories/ExhibitorFactory.php`

**Model:**
- `$fillable`: all non-timestamp columns
- `$casts`: `is_resident → boolean`, `has_paid → boolean`
- Relationships: `hasMany(Entry::class)`
- Helpers: `isAdult()`, `isJunior()`, `totalEntries()`, `chargeableEntries()`, `feeOwedPence()`
- Fee logic: juniors always £0; adults pay for entries 1–10 (`min(count, 10) × config('show.entry_fee_pence')`)
- Factory states: `adult()`, `junior()`, `resident()`, `nonResident()`

**Tests:** `tests/Feature/ExhibitorTest.php` + `tests/Feature/EntryTest.php`
- [x] Factory creates valid adult and junior exhibitors
- [x] `feeOwedPence()` returns 0 for juniors regardless of entry count (EntryTest)
- [x] `feeOwedPence()` charges for entries 1–10 for adults (EntryTest)
- [x] `feeOwedPence()` caps at 10 chargeable entries for adults with 11+ entries (EntryTest)
- [x] `chargeableEntries()` returns 0 for juniors (EntryTest)

---

### Phase 1.5 — Judge-Section Pivot ✅

**Files to create:**
- `database/migrations/2026_05_02_134439_create_show_section_user_table.php`

**Migration:** composite primary key on `(user_id, show_section_id)`; both FK with cascade on delete.

**Model updates:**
- `User.php` — `belongsToMany(ShowSection::class)` as `assignedSections()`
- `ShowSection.php` — `belongsToMany(User::class)` as `judges()`

**Tests:** covered in Phase 2.3 access control tests

---

### Phase 1.6 — Entry Model ✅

**Files to create:**
- `database/migrations/2026_05_02_134504_create_entries_table.php`
- `app/Models/Entry.php`
- `database/factories/EntryFactory.php`

**Model:**
- `$fillable`: `show_class_id, exhibitor_id` (entry_number is auto-assigned)
- Relationships: `belongsTo(ShowClass::class)`, `belongsTo(Exhibitor::class)`, `hasOne(Result::class)`
- Boot: auto-assign `entry_number` as `(Entry::max('entry_number') ?? 0) + 1` on `creating`
- Helper: `hasResult()`

**Tests:** `tests/Feature/EntryTest.php`
- [x] Factory creates a valid `Entry` with an auto-assigned `entry_number`
- [x] `entry_number` is globally unique (DB unique constraint)
- [x] Two entries in the same seeding sequence get consecutive numbers
- [x] `hasResult()` returns `false` when no result exists and `true` when it does (ResultTest)

---

### Phase 1.7 — Result Model ✅

**Files to create:**
- `database/migrations/2026_05_02_134635_create_results_table.php`
- `app/Models/Result.php`
- `database/factories/ResultFactory.php`

**Model:**
- `$fillable`: `entry_id, entered_by_user_id, placement, notes`
- Relationships: `belongsTo(Entry::class)`, `belongsTo(User::class, 'entered_by_user_id')`
- `points()` — `'1st'→3, '2nd'→2, '3rd'→1, 'highly_commended'→0, null→0`
- `placementLabel()` — human-readable string

**Tests:** `tests/Feature/ResultTest.php`
- [x] Factory creates a valid `Result`
- [x] `points()` returns correct values for each placement value
- [x] `points()` returns 0 for null placement
- [x] One result per entry enforced (DB unique on `entry_id`)

---

### Phase 1.8 — Trophy Model & Pivot ✅

**Files to create:**
- `database/migrations/2026_05_02_134730_create_trophies_table.php`
- `database/migrations/2026_05_02_134731_create_show_class_trophy_table.php`
- `app/Models/Trophy.php`
- `database/factories/TrophyFactory.php`

**Model:**
- `$fillable`: `name, description`
- Relationships: `belongsToMany(ShowClass::class)`
- `winners()` — returns a collection of `['exhibitor' => Exhibitor, 'points' => int]` for the top scorer(s) across this trophy's classes

**Tests:** `tests/Feature/TrophyTest.php`
- [x] Factory creates a valid `Trophy`
- [x] `winners()` returns the exhibitor with the most points across assigned classes
- [x] `winners()` returns all exhibitors when there is a points tie
- [x] `winners()` returns an empty collection when no results are entered
- [x] A `ShowClass` can be assigned to multiple trophies

---

### Phase 1.9 — Config & Database Seeder ✅

**Files to create/modify:**
- `config/show.php` — `entry_fee_pence` (default `50` = £0.50 per entry)
- `database/seeders/DatabaseSeeder.php` — 3 sections, 5–10 classes each, 20 exhibitors (mix of adult/junior/resident), 2 judge users with assigned sections, entries across classes

**Verification:** `php artisan migrate:fresh --seed` completes without errors ✅

---

## Phase 2 — Roles & Access Control ✅

Goal: Role-based routing. Admins can access all admin routes. Judges can access only their result-entry area. Guests are redirected to login.

---

### Phase 2.1 — Role Middleware ✅

**Files created:**
- `app/Http/Middleware/RequireAdmin.php` — aborts 403 if `user()->role !== 'admin'`
- `app/Http/Middleware/RequireJudge.php` — passes for `'admin'` and `'judge'`; aborts 403 otherwise
- `bootstrap/app.php` — aliases `admin` → `RequireAdmin`, `judge` → `RequireJudge`

**Tests:** `tests/Feature/MiddlewareTest.php`
- [x] Guest is redirected to `/login` on `GET /admin/show-sections`
- [x] Judge user receives 403 on an admin-only route
- [x] Admin user can access admin routes
- [x] Judge user can access judge routes
- [x] Admin user can also access judge routes (admin is a superset)

---

### Phase 2.2 — Admin Route Group ✅

**Files modified:**
- `routes/web.php` — `prefix('admin')->name('admin.')->middleware(['auth', 'admin'])` group with placeholder `show-sections` route; `prefix('judge')->name('judge.')->middleware(['auth', 'judge'])` group

**Status:** No additional tests beyond Phase 2.1

---

### Phase 2.3 — Judge Landing & Login Redirect ✅

After login, judges are redirected to `/judge/sections`; admins go to `/dashboard`.

**Files created/modified:**
- `app/Providers/FortifyServiceProvider.php` — binds `LoginResponse` contract; redirects by role
- `app/Http/Controllers/Judge/SectionController.php` — `index` returns judge's assigned sections in sort order
- `resources/views/judge/sections/index.blade.php` — Flux table; empty-state for no assignments
- `routes/web.php` — judge route group added

**Tests:** `tests/Feature/JudgeAccessTest.php`
- [x] Judge user is redirected to `/judge/sections` after login
- [x] Admin user is redirected to `/dashboard` after login
- [x] Judge sees only their assigned sections on `/judge/sections`
- [x] Judge sees an empty-state message when assigned to no sections
- [x] Judge receives 403 on `GET /admin/show-sections`

---

## Phase 3 — Show Structure Management ✅

Goal: Admin can create, edit, delete, and list show sections and classes.

Uses **Laravel Controllers** (not Livewire) per project conventions.

---

### Phase 3.1 — Show Sections CRUD ✅

**Files created:**
- `app/Http/Controllers/Admin/ShowSectionController.php` — `index, create, store, edit, update, destroy`
- `app/Http/Requests/Admin/StoreShowSectionRequest.php`
- `app/Http/Requests/Admin/UpdateShowSectionRequest.php`
- `resources/views/admin/show-sections/index.blade.php`
- `resources/views/admin/show-sections/create.blade.php`
- `resources/views/admin/show-sections/edit.blade.php`
- Sidebar "Show Management → Sections" link added (admin-only)

**Validation:** `name` required unique; `description` nullable; `sort_order` required integer ≥ 0.

**Delete rule:** Blocked if the section has any classes (flash `error` and redirect back).

**Tests:** `tests/Feature/Admin/ShowSectionCrudTest.php`
- [x] Admin can view sections list at `GET /admin/show-sections`
- [x] Admin can create a section with valid data
- [x] Creating a section with a duplicate name fails validation
- [x] Admin can update an existing section
- [x] Admin can delete a section that has no classes
- [x] Admin cannot delete a section that has classes (error message returned)
- [x] Sections list shows the assigned judge's name
- [x] Sections list shows "TBC" when no judge is assigned
- [x] Judge column links to the judges index
- [x] Guest is redirected to `/login` on section routes
- [x] Judge receives 403 on section routes

---

### Phase 3.2 — Show Classes CRUD ✅

**Files created/modified:**
- `app/Http/Controllers/Admin/ShowClassController.php` — `index, create, store, edit, update, destroy` (scoped to section)
- `app/Http/Requests/Admin/StoreShowClassRequest.php`
- `app/Http/Requests/Admin/UpdateShowClassRequest.php`
- `resources/views/admin/show-classes/index.blade.php`
- `resources/views/admin/show-classes/create.blade.php`
- `resources/views/admin/show-classes/edit.blade.php`
- `resources/views/admin/show-sections/index.blade.php` — section name is a link to its classes list

**Routes:** `Route::resource('show-sections.show-classes', …)` → `admin/show-sections/{show_section}/show-classes`

**Validation:** `name` unique within section (scoped `Rule::unique`); `max_entries_per_exhibitor` integer ≥ 1; `sort_order` integer ≥ 0.

**Delete rule:** Blocked if the class has any entries (flash `error` and redirect back).

**Tests:** `tests/Feature/Admin/ShowSectionCrudTest.php` + `tests/Feature/Admin/ShowClassCrudTest.php`
- [x] Section name in sections list links to the class list for that section
- [x] Admin can view class list for a section
- [x] Admin can create a class with valid data
- [x] Duplicate class name within the same section fails validation
- [x] Same class name in two different sections is allowed
- [x] Admin can update a class
- [x] Admin can delete a class with no entries
- [x] Admin cannot delete a class with entries
- [x] Guest is redirected to `/login` on class routes
- [x] Judge receives 403 on class routes

---

## Phase 4 — Exhibitor Management ✅

Goal: Admin can register, edit, delete, and list exhibitors, with fee calculation and payment tracking.

---

### Phase 4.1 — Exhibitor List ✅

**Files created:**
- `app/Http/Controllers/Admin/ExhibitorController.php` — `index` with search + filters
- `resources/views/admin/exhibitors/index.blade.php` — searchable, filterable table with fee column and pay toggle
- Sidebar "Exhibitors" link added (admin-only)

**Features:** Search by name/email; filter by `type`, `is_resident`, `has_paid`; fee owed and paid/unpaid badge per row.

**Tests:** `tests/Feature/Admin/ExhibitorCrudTest.php`
- [x] Admin sees exhibitor list at `GET /admin/exhibitors`
- [x] Search by name filters results correctly
- [x] Filter by type (adult/junior) works

---

### Phase 4.2 — Exhibitor Create & Edit ✅

**Files created:**
- `app/Http/Requests/Admin/StoreExhibitorRequest.php`
- `app/Http/Requests/Admin/UpdateExhibitorRequest.php`
- `resources/views/admin/exhibitors/create.blade.php`
- `resources/views/admin/exhibitors/edit.blade.php`

**Validation:** `first_name` + `last_name` required; `full_name` and `sort_name` computed on save; `email` nullable email; `type` required in `[adult, junior]`; `is_resident` boolean.

**Tests:** `tests/Feature/Admin/ExhibitorCrudTest.php`
- [x] Admin can create an exhibitor (full_name and sort_name computed correctly)
- [x] Admin can update exhibitor details

---

### Phase 4.3 — Exhibitor Delete ✅

**Tests:** `tests/Feature/Admin/ExhibitorCrudTest.php`
- [x] Admin can delete an exhibitor with no entries
- [x] Admin cannot delete an exhibitor with entries (error message shown)

---

### Phase 4.4 — Fee Calculation Display ✅

_(Logic lives in `Exhibitor` model — Phase 1.4. This phase covers the display layer.)_

**Files created:**
- `resources/views/admin/exhibitors/show.blade.php` — exhibitor detail with fee summary section

**Tests:** `tests/Feature/Admin/ExhibitorCrudTest.php`
- [x] Admin can view exhibitor detail page
- [x] Fee summary shows total entries, chargeable entries, and total fee owed (e.g. £1.50 for 3 adult entries)

---

### Phase 4.5 — Payment Tracking ✅

**Files created/modified:**
- `app/Http/Controllers/Admin/ExhibitorController.php` — `markPaid` / `markUnpaid` actions
- `resources/views/admin/exhibitors/index.blade.php` — paid/unpaid badge + toggle action per row
- `resources/views/admin/exhibitors/show.blade.php` — pay/unpay button in fee summary
- `routes/web.php` — `PATCH /admin/exhibitors/{exhibitor}/mark-paid` and `mark-unpaid`

**Tests:** `tests/Feature/Admin/ExhibitorCrudTest.php`
- [x] Admin can mark an exhibitor as paid
- [x] Admin can mark a paid exhibitor as unpaid
- [x] Guest is redirected from exhibitor routes

---

## Phase 5 — Judge Management ✅

Goal: Admin can create judge accounts, assign them to sections, and manage their details.

---

### Phase 5.1 — Judge List ✅

**Files created:**
- `app/Http/Controllers/Admin/JudgeController.php` — `index, create, store, edit, update, destroy`
- `resources/views/admin/judges/index.blade.php` — table: name, email, phone, assigned sections count
- Sidebar "Judges" link added (admin-only, `academic-cap` icon)

**Tests:** `tests/Feature/Admin/JudgeCrudTest.php`
- [x] Admin sees judge list at `GET /admin/judges`
- [x] Each row shows name, email, phone, and assigned sections count

---

### Phase 5.2 — Judge Create & Edit ✅

**Files created:**
- `app/Http/Requests/Admin/StoreJudgeRequest.php`
- `app/Http/Requests/Admin/UpdateJudgeRequest.php`
- `resources/views/admin/judges/create.blade.php`
- `resources/views/admin/judges/edit.blade.php`

**Create logic:** Creates a `User` with `role='judge'` and `Str::password(16)` temporary password; syncs `section_ids` via pivot.

**Validation:** `name` required; `email` required unique (ignores self on update); `phone` nullable; `section_ids` nullable array of valid section IDs.

**Tests:** `tests/Feature/Admin/JudgeCrudTest.php`
- [x] Admin can create a judge account with name, email, and phone
- [x] Created user has `role === 'judge'`
- [x] Judge email must be unique in `users` table
- [x] Admin can assign sections when creating a judge
- [x] Admin can update judge name, email, and phone
- [x] Admin can update section assignments (add and remove)
- [x] Admin can delete a judge

---

### Phase 5.3 — Section Assignment ✅

**Tests:** `tests/Feature/Admin/JudgeCrudTest.php`
- [x] A judge can be assigned to multiple sections simultaneously
- [x] Removing all section assignments from a judge works correctly
- [x] Guest is redirected from judge routes

---

## Phase 6 — Entry Management ✅

Goal: Admin can record entries (exhibitor + class), view entries per class and per exhibitor, and delete entries.

---

### Phase 6.1 — Record Entries ✅

**Files created:**
- `app/Http/Controllers/Admin/EntryController.php` — `store` (scoped to `ShowClass`)
- `app/Http/Requests/Admin/StoreEntryRequest.php`
- `resources/views/admin/show-classes/show.blade.php` — class detail page: entries list + add entry form

**Custom validation rule:** Exhibitor has not exceeded `max_entries_per_exhibitor` for this class.

**Routes:** `POST /admin/show-sections/{show_section}/show-classes/{show_class}/entries`

**Tests:** `tests/Feature/EntryManagementTest.php`
- [x] Admin can record an entry linking an exhibitor to a class
- [x] `entry_number` is auto-assigned and globally unique
- [x] Exhibitor cannot exceed `max_entries_per_exhibitor` for a class (validation error returned)
- [x] Entry appears in the class entry list after creation
- [x] Exhibitor's chargeable fee recalculates after adding an entry

---

### Phase 6.2 — View Entries per Class ✅

_(Covered by the class show page in Phase 6.1.)_

**Tests:** _(appended to `EntryManagementTest.php`)_
- [x] Entry list shows: entry number, exhibitor name, exhibitor type, result status
- [x] Result status indicator is correct (no result / result entered)

---

### Phase 6.3 — View All Entries for an Exhibitor ✅

**Files modified:**
- `resources/views/admin/exhibitors/show.blade.php` — full entry history section added

**Tests:** `tests/Feature/ExhibitorDetailTest.php`
- [x] Admin views exhibitor detail at `GET /admin/exhibitors/{exhibitor}`
- [x] Entry list shows: class name, section name, entry number, result placement
- [x] Fee summary is current and correct

---

### Phase 6.4 — Delete Entry ✅

**Files modified:**
- `app/Http/Controllers/Admin/EntryController.php` — `destroy` action added

**Route:** `DELETE /admin/show-sections/{show_section}/show-classes/{show_class}/entries/{entry}`

**Tests:** _(appended to `EntryManagementTest.php`)_
- [x] Admin can delete an entry that has no result
- [x] Admin cannot delete an entry that already has a result (error shown)
- [x] Exhibitor's fee recalculates after deleting an entry

---

### Phase 6.5 — Exhibitor-centric Entry Creation ✅

**Files created/modified:**
- `app/Http/Requests/Admin/StoreExhibitorEntryRequest.php` — validates `show_class_id` and `quantity`; closure checks existing + quantity ≤ max_entries_per_exhibitor
- `app/Http/Controllers/Admin/ExhibitorController.php` — `addEntry`, `storeEntry`, and `labels` actions
- `resources/views/admin/exhibitors/add-entry.blade.php` — cascading Section → Class dropdowns (Alpine.js); quantity number input whose max updates dynamically to the exhibitor's remaining capacity in the chosen class; existing entries table
- `resources/views/admin/exhibitors/labels.blade.php` — standalone print-optimised page (no app chrome); 3-column label grid with section name, class name, exhibitor name, entry number, and a CODE128 barcode; "Print" button triggers `window.print()`
- `routes/web.php` — `GET/POST exhibitors/{exhibitor}/add-entry`; `GET exhibitors/{exhibitor}/labels`
- `resources/views/admin/exhibitors/index.blade.php` — "Add Entries" button per row
- `resources/views/admin/exhibitors/show.blade.php` — "Add Entry" and "Print Labels" buttons in header
- `public/vendor/jsbarcode.min.js` — self-hosted JsBarcode bundle (copied from npm package `jsbarcode@3.11.6`)
- `resources/js/labels.js` + `vite.config.js` — Vite entry point ready to replace the self-hosted bundle once Node ≥ 20 is available

**Behaviour:**
- Admin selects section → class list filters to that section; selects class → quantity input activates with max set to remaining capacity for that exhibitor in that class
- Submitting creates N entries in one request; the system immediately redirects to the labels page showing only the newly created entries, pre-filtered for printing
- "Print All Labels" links on the exhibitor show and add-entry pages open the labels page (no filter) in a new tab for reprinting

**Tests:** `tests/Feature/Admin/ExhibitorAddEntryTest.php`
- [x] Add entries page loads for an exhibitor
- [x] Admin can add an entry by selecting section and class
- [x] Admin can add multiple entries at once (quantity > 1)
- [x] Successful entry redirects to labels page
- [x] Entry appears in the exhibitor's entry list after creation
- [x] Exhibitor cannot exceed max_entries_per_exhibitor via add entry page
- [x] Labels page loads and shows entry numbers
- [x] Labels page filters to specified entries when IDs provided
- [x] Guest is redirected to login (add-entry and labels)

---

## Phase 7 — Results & Judging

Goal: Judges enter and edit results for their assigned sections. Admins can manage all results. Points leaderboard available.

---

### Phase 7.1 — Judge Results Entry View ✅

**Files created:**
- `app/Http/Controllers/Judge/ResultController.php` — `index`, `store`, `update`
- `app/Http/Controllers/Judge/SectionController.php` — `show` added (classes listing for a section)
- `app/Http/Requests/StoreResultRequest.php` — shared; validates entry_id, placement, notes; enforces 1st/2nd/3rd uniqueness per class
- `resources/views/judge/sections/index.blade.php` — updated with "Enter Results" links
- `resources/views/judge/classes/index.blade.php` — NEW: classes within an assigned section
- `resources/views/judge/results/index.blade.php` — entries with inline placement + notes form per entry

**Valid placements:** `1st`, `2nd`, `3rd`, `highly_commended`, or clear (null)

**Uniqueness rule:** A class may have at most one `1st`, one `2nd`, and one `3rd`; any number of `highly_commended`.

**Routes:** `GET /judge/sections/{show_section}/classes`, `GET/POST /judge/sections/{show_section}/classes/{show_class}/results` under `judge` middleware group

**Tests:** `tests/Feature/Judge/ResultEntryTest.php`
- [x] Judge sees only classes in their assigned sections
- [x] Judge can enter a placement and notes for an entry
- [x] Judge cannot assign a second `1st` place in the same class
- [x] Judge cannot assign a second `2nd` place in the same class
- [x] Judge cannot assign a second `3rd` place in the same class
- [x] Multiple `highly_commended` in one class is allowed
- [x] Judge can clear a placement (set to null)
- [x] Entered result is immediately visible to admin

---

### Phase 7.2 — Edit Result (Judge) ✅

_(Store + update in Phase 7.1 controller.)_

**Files created:**
- `app/Http/Requests/UpdateResultRequest.php` — shared; validates placement, notes; excludes current result from uniqueness check

**Tests:** _(appended to `ResultEntryTest.php`)_
- [x] Judge can change an existing placement
- [x] Uniqueness rule is enforced when updating (swapping placements between entries)
- [x] Exhibitor points total changes after updating a result

---

### Phase 7.3 — Admin Results Management ✅

**Files created:**
- `app/Http/Controllers/Admin/ResultController.php` — `store`, `update`, `destroy` (any class)
- `resources/views/admin/show-classes/show.blade.php` — inline placement dropdown + Save per entry row; Delete Result / Delete Entry in actions column

**Behaviour:** Same uniqueness constraints as judge; `entered_by_user_id` set to the acting admin's ID.

**Tests:** `tests/Feature/Admin/ResultManagementTest.php`
- [x] Admin can enter a result for any class in any section
- [x] Admin can update any result
- [x] Admin cannot create a duplicate `1st`/`2nd`/`3rd` in a class
- [x] `entered_by_user_id` is set to the admin's user ID
- [x] Judge receives 403 when accessing admin result routes

---

### Phase 7.4 — Points Leaderboard ✅

**Files created:**
- `app/Http/Controllers/Admin/LeaderboardController.php`
- `resources/views/admin/leaderboard/index.blade.php`
- Sidebar "Leaderboard" link added (trophy icon, admin-only)

**Logic:** Aggregate `results.placement` → points per exhibitor in PHP; optional section filter via query param; tied exhibitors share rank.

**Tests:** `tests/Feature/Admin/LeaderboardTest.php`
- [x] Leaderboard shows exhibitors in descending points order
- [x] Section filter shows only points from that section's classes
- [x] Exhibitor with no results shows 0 points
- [x] Tied exhibitors appear at the same rank position

---

## Phase 8 — Trophies ✅

Goal: Admin configures trophies (assigning classes); system auto-calculates winners.

---

### Phase 8.1 — Trophy CRUD ✅

**Files created:**
- `app/Http/Controllers/Admin/TrophyController.php` — `index, create, store, edit, update, destroy`
- `app/Http/Requests/Admin/StoreTrophyRequest.php`
- `app/Http/Requests/Admin/UpdateTrophyRequest.php`
- `resources/views/admin/trophies/index.blade.php`
- `resources/views/admin/trophies/create.blade.php`
- `resources/views/admin/trophies/edit.blade.php`
- `routes/web.php` — `Route::resource('trophies', TrophyController::class)` inside admin group
- Sidebar "Trophies" link added (`gift` icon, admin-only)

**Validation:** `name` required; `class_ids` nullable array of valid `ShowClass` IDs.

**Tests:** `tests/Feature/Admin/TrophyCrudTest.php`
- [x] Admin can create a trophy with a name and class assignments
- [x] Admin can create a trophy with no class assignments
- [x] Admin can update name, description, and class assignments
- [x] A class can be assigned to multiple trophies simultaneously
- [x] Admin can delete a trophy
- [x] Guest / judge access returns redirect / 403

---

### Phase 8.2 — Trophy Winner Calculation ✅

_(Logic in `Trophy::winners()` — Phase 1.8.)_

**Tests:** `tests/Feature/TrophyWinnerTest.php`
- [x] Winner is the exhibitor with the most points across the trophy's assigned classes
- [x] All tied exhibitors are returned when points are equal
- [x] Adding a new result updates the winner output
- [x] Trophy with no assigned classes returns empty winner list
- [x] Trophy with assigned classes but no results returns empty winner list

---

### Phase 8.3 — Trophy List with Winners ✅

**Files modified:**
- `resources/views/admin/trophies/index.blade.php` — current winner(s) per row

**Tests:** _(appended to `TrophyCrudTest.php`)_
- [x] Trophy index shows the current winner for each trophy
- [x] "No winner yet" displayed when no results entered
- [x] All tied winners are listed when there is a tie

---

## Phase 9 — Public Display ✅

Goal: Public-facing pages (no login required) showing the show schedule, results, and trophy winners.

---

### Phase 9.1 — Public Show Schedule ✅

**Files created:**
- `app/Http/Controllers/Public/ScheduleController.php`
- `resources/views/public/schedule/index.blade.php`
- `resources/views/layouts/public.blade.php` — public layout with nav (Schedule / Results / Trophies)

**Route:** `GET /public/schedule` (no auth middleware)

**Data shown:** Sections in sort_order → classes in sort_order. No exhibitor contact details, no fees, no payment status.

**Tests:** `tests/Feature/Public/ScheduleTest.php`
- [x] Public schedule is accessible without login
- [x] Sections are listed in `sort_order` ASC
- [x] Classes are listed within their section in `sort_order` ASC
- [x] No exhibitor contact details are exposed in the response
- [x] No payment status information is exposed

---

### Phase 9.2 — Public Results ✅

**Files created:**
- `app/Http/Controllers/Public/ResultController.php`
- `resources/views/public/results/index.blade.php`

**Data shown:** Exhibitor name + placement only. Points hidden. Classes with no placed results show "Results pending."

**Tests:** `tests/Feature/Public/PublicResultsTest.php`
- [x] Public results page accessible without login
- [x] Placement labels (1st / 2nd / 3rd / Highly Commended) shown per result
- [x] Points totals are NOT present anywhere in the response
- [x] Class with no results shows "Results pending."
- [x] Exhibitor email, phone, and address are NOT present in the response

---

### Phase 9.3 — Public Trophy Winners ✅

**Files created:**
- `app/Http/Controllers/Public/TrophyController.php`
- `resources/views/public/trophies/index.blade.php`

**Data shown:** Trophy name + winner name (or "To be announced.").

**Tests:** `tests/Feature/Public/PublicTrophiesTest.php`
- [x] Public trophies page accessible without login
- [x] Trophy name and winner's name shown
- [x] "To be announced." shown when no results have been entered
- [x] All tied winners listed when there is a tie

---

## Phase 10 — Admin Dashboard ✅

Goal: Replace placeholder dashboard with live show stats.

---

### Phase 10.1 — Dashboard Stats ✅

**Files created/modified:**
- `app/Http/Controllers/DashboardController.php` — invokable; redirects judges; passes stats to view
- `resources/views/dashboard.blade.php` — stats grid (sections, classes, exhibitors, entries, results, payments)

**Stats displayed:**
- Total sections + class count sub-label
- Total exhibitors with adult / junior breakdown
- Total entries
- Results entered (placed) vs. outstanding
- Adult exhibitors paid / unpaid

**Tests:** `tests/Feature/DashboardTest.php` _(extended)_
- [x] Dashboard accessible to admin at `GET /dashboard`
- [x] Correct section count displayed
- [x] Correct class count displayed
- [x] Correct exhibitor count displayed (adult/junior split)
- [x] Correct entry count displayed
- [x] Correct results entered vs. outstanding counts displayed
- [x] Correct paid/unpaid exhibitor count displayed
- [x] Judge accessing `GET /dashboard` is redirected to `/judge/sections`

---

## Verification (End-to-End)

1. `php artisan migrate:fresh --seed` — all migrations run cleanly with demo data
2. `php artisan test --compact` — all tests green
3. Log in as admin → create section → create class → create exhibitor → record entry → enter result → confirm fee and points update
4. Log in as judge → confirm only assigned sections visible → enter a result
5. Visit `/public/schedule` and `/public/results` without logging in — pages load and contain no private data
6. Visit `/public/trophies` publicly — winner shown once results are entered

---

## Appendix: Completion Status

| Phase | Description | Status |
|-------|-------------|--------|
| Auth (Fortify) | Login, logout, password reset, 2FA | ✅ Complete |
| Phase 1.1 | Extend users table (role + phone) | ✅ Complete |
| Phase 1.2 | ShowSection model | ✅ Complete |
| Phase 1.3 | ShowClass model | ✅ Complete |
| Phase 1.4 | Exhibitor model | ✅ Complete |
| Phase 1.5 | Judge-Section pivot | ✅ Complete |
| Phase 1.6 | Entry model | ✅ Complete |
| Phase 1.7 | Result model | ✅ Complete |
| Phase 1.8 | Trophy model & pivot | ✅ Complete |
| Phase 1.9 | Config & seeder | ✅ Complete |
| Phase 2.1 | Role middleware | ✅ Complete |
| Phase 2.2 | Admin route group | ✅ Complete |
| Phase 2.3 | Judge landing & login redirect | ✅ Complete |
| Phase 3.1 | Show Sections CRUD | ✅ Complete |
| Phase 3.2 | Show Classes CRUD | ✅ Complete |
| Phase 4.1 | Exhibitor list | ✅ Complete |
| Phase 4.2 | Exhibitor create & edit | ✅ Complete |
| Phase 4.3 | Exhibitor delete | ✅ Complete |
| Phase 4.4 | Fee calculation display | ✅ Complete |
| Phase 4.5 | Payment tracking | ✅ Complete |
| Phase 5.1 | Judge list | ✅ Complete |
| Phase 5.2 | Judge create & edit | ✅ Complete |
| Phase 5.3 | Section assignment | ✅ Complete |
| Phase 6.1 | Record entries | ✅ Complete |
| Phase 6.2 | View entries per class | ✅ Complete |
| Phase 6.3 | View all entries for an exhibitor | ✅ Complete |
| Phase 6.4 | Delete entry | ✅ Complete |
| Phase 7.1 | Judge results entry view | ✅ Complete |
| Phase 7.2 | Edit result (judge) | ✅ Complete |
| Phase 7.3 | Admin results management | ✅ Complete |
| Phase 7.4 | Points leaderboard | ✅ Complete |
| Phase 8.1 | Trophy CRUD | ✅ Complete |
| Phase 8.2 | Trophy winner calculation | ✅ Complete |
| Phase 8.3 | Trophy list with winners | ✅ Complete |
| Phase 9.1 | Public show schedule | ✅ Complete |
| Phase 9.2 | Public results | ✅ Complete |
| Phase 9.3 | Public trophy winners | ✅ Complete |
| Phase 10.1 | Admin dashboard stats | ✅ Complete |

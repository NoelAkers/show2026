# Village Show Project — Suggested Phases

## Context
The Laravel 13 + Livewire 4 + Flux UI foundation is complete (auth, 2FA, settings, navigation scaffold). Nothing domain-specific has been built yet. These phases build the village show MVP from the ground up, in an order that matches natural data dependencies.

---

## Phase 1: Show Structure
**Goal:** Admins can define the hierarchical show schedule.

- `ShowSection` model + migration (`id, name, description, order`)
- `ShowClass` model + migration (`id, show_section_id, name, description, max_entries_per_exhibitor, order`)
- Factories and seeders for both
- Livewire CRUD pages: sections list/create/edit, classes list/create/edit
- Sidebar navigation links added
- Dashboard updated with section/class counts
- Tests covering CRUD for both models

**Outcome:** A complete show schedule can be created and browsed.

---

## Phase 2: People — Exhibitors & Judges
**Goal:** Admins can register the participants.

- `Exhibitor` model + migration (`id, name, email, phone, address`)
- `Judge` model + migration (`id, name, email, phone, specialism`)
- Factories and seeders for both
- Livewire CRUD pages: exhibitors list/create/edit, judges list/create/edit
- Navigation links
- Tests

**Outcome:** All participants are in the system and searchable.

---

## Phase 3: Entries & Results
**Goal:** The core of the show — recording who entered what and who won.

- `Entry` model + migration (`id, show_class_id, exhibitor_id, entry_number`) — tracks an exhibitor's submission to a class
- `Result` model + migration (`id, entry_id, judge_id, placement, notes`) — records the judged outcome
- Factories for both
- Judge-to-class assignment (could be a pivot `show_class_judge` or a field on ShowClass for MVP)
- Livewire: entry management per class, result entry per class
- Tests

**Outcome:** The full lifecycle of an exhibit — entered, judged, placed — is tracked.

---

## Phase 4: Public Display & Dashboard
**Goal:** A polished view for visitors and a useful summary for admins.

- Public results page (no login required): show schedule → section → class → results
- Admin dashboard with stats (total entries, results outstanding, top exhibitors by wins)
- Print-friendly results view or basic export
- Seed realistic demo data

**Outcome:** Show results can be shared with the public; admins have a meaningful overview.

---

## Verification (end-to-end)
1. Seed the database (`php artisan db:seed`) and navigate the full admin flow
2. Run `php artisan test --compact` — all tests green
3. Visit the public results page as a guest and confirm no auth is required
4. Confirm dashboard stats reflect seeded data

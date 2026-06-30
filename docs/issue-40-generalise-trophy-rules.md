# Issue #40 — Generalise Points-Based Trophy Rules

## What's Already in Place

The `Exhibitor` model already has all three relevant attributes: `is_resident` (boolean), `is_novice` (boolean), and `type` (enum: `adult`/`junior`). They exist but are never used in competition logic — only for admin filtering and fee calculations. So Phase 1 is mostly a matter of wiring them into trophy eligibility, not adding new schema.

The critical choke point is `Trophy::winners()` in `app/Models/Trophy.php` around line 74 — this is where the eligibility filter needs to be applied.

---

## Issue 1: The Leaderboard Requirement is Ambiguous

> "make the leaderboard reflect the additional restrictions by not counting points from ineligible exhibitors"

The current leaderboard (`app/Http/Controllers/Admin/LeaderboardController.php`) is a general whole-show view filterable by section. It deliberately shows *everyone*. Restrictions only make sense in the context of a specific trophy (e.g. resident-only vs novice-only are different leaderboards).

**Question:** Do you want the general leaderboard to stay as-is (showing everyone), with restriction logic only applied in `Trophy::winners()`? Or do you want a per-trophy leaderboard view? These are quite different in scope.

**Answer**
I'd misremembered the choices from the dropdown in the leaderboard and thought they were related to points-based trophies! Actually, the general whole-show view filterable by section isn't super useful. I don't mind retaining it but a per-trophy leaderboard would be really useful, probably limited to max 10 entries and only those with points. Maybe this could be accessed from the trophy index? Where it would be very helpful is as a final manual check that the winner of a "restricted" trophy has self-classified correctly - there have been instances of trophies restricted to novices or residents initially being allocated to people who have been wrongly classified.

---

## Issue 2: Data Model Options for Phase 1

**Option A — JSON column on `trophies`**
```php
// trophies.restrictions = {"resident_only": true, "novice_only": true}
```
Simple but not queryable and harder to extend without care.

**Option B — Enum + many-to-many pivot (recommended)**
```php
enum TrophyRestriction: string {
    case Resident = 'resident';
    case Novice = 'novice';
    case Junior = 'junior';
}
```
With a `trophy_restrictions` pivot table. Clean, queryable, and extending it for Phase 2 just means adding enum cases. The filtering in `winners()` becomes a set of `->when(...)` clauses on the existing query.

**Option C — Generic rules engine (EAV)**
A `trophy_rules` table with `field`, `operator`, `value` rows. Truly configurable without code changes, but complex to validate, build a UI for, and debug. Probably overkill for a village show.

**Recommendation:** Option B for Phase 1. It's already 80% of what Phase 2 needs for this show's known restriction types.

**Comment**

I agree with this approach as long as it doesn't then involve a lot of refactoring for Phase 2. I would like to know more about Option C if you could point me to some background reading
on generic rules engines. There are a lot of show type events out there (some of them larger, prestigious national events) and many of them have nothing beyond a manual or very basic Access database or Excel spreadsheet system to help with administration and calculation of results. I think this codebase could be adapted to work with the majority of these, but it would only be taken up if configuration of "business rules" was possible without code changes.

---

## Issue 3: The `is_novice` Lifecycle

The issue says a novice is "an exhibitor who has not previously won a trophy at this or a similar show." The field exists as a manually set boolean — there's no automated mechanism to flip it when someone wins their first trophy.

**Question:** Is the plan to keep `is_novice` as a manually managed admin field, or should the system automatically mark an exhibitor as no-longer-novice when they win a novice-eligible trophy? The automated route is more robust but adds complexity (and raises the question of "similar shows" which are outside this system).

**Answer**
The `is_novice` status is generally interpreted as "status at start of show", i.e. if a novice by that definition gets the most points in two novice-only trophy categories at the same show, winning the first doesn't remove their novice status and make them ineligible for the second. We also currently don't retain data about exhibitors for future shows after the current show has finished, for GDPR reasons. So we would basically accept the exhibitors own classification when they first register, not change it based on the in-show results. We would manually change it if we became aware that they had been a previous trophy winner at an earlier version of our or a similar show.

---

## Issue 4: Junior Class Restrictions vs Trophy Restrictions

The issue mentions "some classes are only eligible for juniors." This is a *class-level* restriction (on `ShowClass`), distinct from a *trophy-level* restriction. Currently there's no flag on `ShowClass` for this. Worth treating as a separate concern — the issue as written seems focused on trophy restrictions, and class-level eligibility is a different problem.

**Comment** 
This is correct and may be implemented later. It's not been an issue for earlier shows but putting it in place would make the codebase more generally useful. We currently don't have a points-based trophy restricted to juniors, but this must be a common situation elsewhere, so again implementing it alongside other restrictions would make the codebase more widely applicable

---

## Summary

Phase 1 is well-scoped and feasible. The main things to clarify before building:

1. Should the general leaderboard change, or only `Trophy::winners()`?
2. Should `is_novice` be auto-managed or stay manual?
3. Confirm junior class restrictions are out of scope for this issue.

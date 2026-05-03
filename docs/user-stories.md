# Village Show — User Stories

## Overview

This document contains user stories for the Village Show MVP — a web application for managing a village show's sections, classes, exhibitors, judges, entries, results, and trophies.

**User Types:**
- **Admin** — Show organiser with full administrative access
- **Judge** — Authenticated judge who enters results for their assigned sections
- **Public Visitor** — Anyone viewing the show schedule or results without an account

---

## 1. Authentication & Access

### US-1.1: Admin Login
**As an** Admin
**I want to** log in with my email and password
**So that** I can manage the show

**Acceptance Criteria:**
- [ ] Login form accepts email and password
- [ ] Invalid credentials show an appropriate error message
- [ ] Successful login redirects to the admin dashboard
- [ ] Session persists until logout

**Expected Result:** Admin is authenticated and redirected to the dashboard.

---

### US-1.2: Judge Login
**As a** Judge
**I want to** log in with my email and password
**So that** I can enter results for my assigned sections

**Acceptance Criteria:**
- [ ] Judge uses the same login form as Admin
- [ ] Successful login redirects to the judge's result-entry view
- [ ] Judge can only see and interact with sections they are assigned to
- [ ] Judge cannot access admin-only areas (show structure, exhibitor management, etc.)

**Expected Result:** Judge is authenticated and can enter results for assigned sections only.

---

### US-1.3: Password Reset
**As a** registered user (Admin or Judge)
**I want to** reset my password if I forget it
**So that** I can regain access to my account

**Acceptance Criteria:**
- [ ] "Forgot password" link on the login page
- [ ] User enters their email address
- [ ] Password reset link sent to that email (valid for 60 minutes)
- [ ] User sets a new password via the reset link
- [ ] Confirmation shown after successful reset

**Expected Result:** User receives a reset email and can set a new password.

---

### US-1.4: Logout
**As a** logged-in user
**I want to** log out of the system
**So that** my session is ended securely

**Acceptance Criteria:**
- [ ] Logout option available in navigation
- [ ] Session is invalidated on logout
- [ ] User is redirected to the login page

**Expected Result:** User is logged out and session is cleared.

---

## 2. Show Structure — Sections

### US-2.1: Create Show Section
**As an** Admin
**I want to** create a show section (e.g. Vegetables, Baking, Art)
**So that** I can organise the show into logical categories

**Acceptance Criteria:**
- [ ] Form collects: name, description (optional), display order
- [ ] Section name must be unique
- [ ] Section appears in the sections list after creation
- [ ] Sections can be reordered

**Expected Result:** New section is created and visible in the admin section list.

---

### US-2.2: Edit Show Section
**As an** Admin
**I want to** edit an existing show section
**So that** I can correct mistakes or update details

**Acceptance Criteria:**
- [ ] Existing values pre-filled in the edit form
- [ ] Name, description, and order can all be updated
- [ ] Changes are reflected immediately in the list

**Expected Result:** Section is updated with new details.

---

### US-2.3: Delete Show Section
**As an** Admin
**I want to** delete a show section
**So that** I can remove sections added by mistake

**Acceptance Criteria:**
- [ ] Delete requires confirmation
- [ ] Cannot delete a section that has classes associated with it (or prompts cascading delete)
- [ ] Section is removed from the list

**Expected Result:** Section is removed from the system.

---

### US-2.4: List Show Sections
**As an** Admin
**I want to** view all show sections in order
**So that** I can see the full show structure at a glance

**Acceptance Criteria:**
- [ ] Sections listed in display order
- [ ] Each row shows: name, description, number of classes, actions (edit/delete)
- [ ] Link to manage classes within each section

**Expected Result:** Admin sees an ordered list of all sections with key info.

---

## 3. Show Structure — Classes

### US-3.1: Create Show Class
**As an** Admin
**I want to** create a show class within a section (e.g. "Victoria Sponge" in "Baking")
**So that** exhibitors have specific categories to enter

**Acceptance Criteria:**
- [ ] Form collects: name, description (optional), max entries per exhibitor, display order
- [ ] Class belongs to a section (pre-selected when creating from within a section)
- [ ] Class name must be unique within its section
- [ ] Class appears in the section's class list

**Expected Result:** New class is created and listed under the correct section.

---

### US-3.2: Edit Show Class
**As an** Admin
**I want to** edit an existing show class
**So that** I can update details or adjust limits

**Acceptance Criteria:**
- [ ] Existing values pre-filled in the edit form
- [ ] All fields (name, description, max entries, order) can be updated

**Expected Result:** Class is updated with the new details.

---

### US-3.3: Delete Show Class
**As an** Admin
**I want to** delete a show class
**So that** I can remove classes added in error

**Acceptance Criteria:**
- [ ] Delete requires confirmation
- [ ] Cannot delete a class that has entries associated with it
- [ ] Class is removed from its section's list

**Expected Result:** Class is removed from the system.

---

### US-3.4: List Show Classes for a Section
**As an** Admin
**I want to** view all classes within a section
**So that** I can manage the classes and see entry counts

**Acceptance Criteria:**
- [ ] Classes listed in display order
- [ ] Each row shows: name, description, max entries per exhibitor, entry count, actions
- [ ] Link to manage entries within each class

**Expected Result:** Admin sees all classes in a section with entry counts.

---

## 4. Exhibitors

### US-4.1: Register an Exhibitor
**As an** Admin
**I want to** register an exhibitor
**So that** they can enter the show

**Acceptance Criteria:**
- [ ] Form collects: name, email (optional), phone (optional), address, type (adult/junior), residency (resident/non-resident)
- [ ] Exhibitor type (adult/junior) determines fee rules
- [ ] Residency status captured for reporting purposes
- [ ] Exhibitor appears in the exhibitor list

**Expected Result:** Exhibitor is created and available to associate with entries.

---

### US-4.2: Edit Exhibitor
**As an** Admin
**I want to** edit an exhibitor's details
**So that** I can correct mistakes

**Acceptance Criteria:**
- [ ] Existing values pre-filled
- [ ] All fields can be updated

**Expected Result:** Exhibitor record is updated.

---

### US-4.3: Delete Exhibitor
**As an** Admin
**I want to** delete an exhibitor record
**So that** I can remove incorrectly added entries

**Acceptance Criteria:**
- [ ] Delete requires confirmation
- [ ] Cannot delete an exhibitor who has entries (or prompts cascading delete)

**Expected Result:** Exhibitor is removed from the system.

---

### US-4.4: List Exhibitors
**As an** Admin
**I want to** view all exhibitors
**So that** I can search and manage participants

**Acceptance Criteria:**
- [ ] Searchable by name
- [ ] Filterable by type (adult/junior) and residency (resident/non-resident)
- [ ] Each row shows: name, type, residency, total entries, fee owed, payment status
- [ ] Link to view/manage an exhibitor's entries

**Expected Result:** Admin sees a complete, searchable exhibitor list.

---

### US-4.5: View Exhibitor Fee Summary
**As an** Admin
**I want to** view the fee owed for an exhibitor
**So that** I can collect the correct amount from them

**Acceptance Criteria:**
- [ ] Fee rules applied automatically:
  - Junior exhibitors: all entries free (£0)
  - Adult exhibitors: entries 1–10 charged at the per-entry rate; entries 11+ are free
- [ ] Fee summary shows: total entries, chargeable entries, total fee owed
- [ ] Summary visible on the exhibitor detail page and list

**Expected Result:** Admin sees the correct fee owed per exhibitor based on entry count and type.

---

### US-4.6: Record Exhibitor Payment
**As an** Admin
**I want to** mark an exhibitor as having paid
**So that** I can track who has settled their entry fees

**Acceptance Criteria:**
- [ ] "Mark as paid" action on the exhibitor record
- [ ] Payment status displayed in the exhibitor list (paid/unpaid)
- [ ] Payment status persists across sessions

**Expected Result:** Exhibitor is marked as paid and the list reflects this.

---

## 5. Judges

### US-5.1: Create Judge Account
**As an** Admin
**I want to** create a judge account
**So that** the judge can log in and enter results

**Acceptance Criteria:**
- [ ] Form collects: name, email, phone (optional)
- [ ] A temporary password is set (or an invite email is sent) so the judge can log in
- [ ] Judge account is created with "judge" role
- [ ] Judge appears in the judge list

**Expected Result:** Judge has an account and can authenticate.

---

### US-5.2: Assign Judge to Section(s)
**As an** Admin
**I want to** assign a judge to one or more show sections
**So that** they are responsible for judging those sections

**Acceptance Criteria:**
- [ ] Multi-select of available sections on the judge edit form (or a dedicated assignment UI)
- [ ] A judge can be assigned to multiple sections
- [ ] Assignment is visible on both the judge detail and the section detail

**Expected Result:** Judge is linked to the sections they will judge.

---

### US-5.3: Edit Judge Details
**As an** Admin
**I want to** edit a judge's details
**So that** I can keep their information up to date

**Acceptance Criteria:**
- [ ] Existing values pre-filled
- [ ] Name, email, phone, and section assignments can all be updated

**Expected Result:** Judge record is updated.

---

### US-5.4: List Judges
**As an** Admin
**I want to** view all judges
**So that** I can manage them and see their assignments

**Acceptance Criteria:**
- [ ] Each row shows: name, email, assigned sections, number of results entered
- [ ] Link to edit each judge

**Expected Result:** Admin sees all judges and their section assignments.

---

## 6. Entries

### US-6.1: Record an Entry
**As an** Admin
**I want to** record an exhibitor's entry into a show class
**So that** their exhibit is tracked in the system

**Acceptance Criteria:**
- [ ] Entry links an exhibitor to a show class
- [ ] An entry number is assigned (auto-incremented per class or globally unique)
- [ ] An exhibitor cannot exceed the class's max_entries_per_exhibitor limit
- [ ] Entry appears in the class's entry list
- [ ] Exhibitor's total entry count and fee are recalculated immediately

**Expected Result:** Entry is recorded and the exhibitor's fee summary updates.

---

### US-6.2: Delete an Entry
**As an** Admin
**I want to** delete a mistakenly recorded entry
**So that** the entry list is accurate

**Acceptance Criteria:**
- [ ] Delete requires confirmation
- [ ] Cannot delete an entry that already has a result
- [ ] Exhibitor's total entry count and fee are recalculated after deletion

**Expected Result:** Entry is removed and the fee summary recalculates.

---

### US-6.3: View Entries for a Class
**As an** Admin
**I want to** view all entries in a show class
**So that** I can see who entered and manage the entry list

**Acceptance Criteria:**
- [ ] List shows: entry number, exhibitor name, exhibitor type, result (if entered)
- [ ] Indicates if results are fully entered, partially entered, or not started

**Expected Result:** Admin sees all entries for a class with their result status.

---

### US-6.4: View All Entries for an Exhibitor
**As an** Admin
**I want to** view all entries submitted by a specific exhibitor
**So that** I can verify their entries and fee

**Acceptance Criteria:**
- [ ] List shows: show class, section, entry number, result (if any)
- [ ] Total entry count and fee summary shown

**Expected Result:** Admin sees the complete entry history for an exhibitor.

---

### US-6.5: Record Entries from the Exhibitor Record
**As an** Admin
**I want to** add entries for an exhibitor directly from their record
**So that** I can quickly process an exhibitor who arrives at the desk with a list of classes they want to enter.

**Acceptance Criteria:**
- [ ] "Add Entries" button on each row of the exhibitors list
- [ ] "Add Entry" button on the exhibitor detail page
- [ ] Dedicated per-exhibitor page with cascading Section → Class dropdowns (section chosen first; class list updates to show only classes in that section)
- [ ] All max_entries_per_exhibitor validation still applies
- [ ] After adding each entry the page reloads at the top, showing a success flash; existing entries list below updates to include the new entry
- [ ] Admin can add multiple entries in sequence without leaving the page

**Expected Result:** Admin can process an exhibitor's full entry list from a single page without navigating to each class.

---

## 7. Results & Judging

### US-7.1: Enter Result for an Entry (Judge)
**As a** Judge
**I want to** enter a result for an entry in my assigned section
**So that** the outcome of judging is recorded

**Acceptance Criteria:**
- [ ] Judge sees only entries in their assigned section(s)
- [ ] For each entry, judge can set placement:
  - 1st (3 points)
  - 2nd (2 points)
  - 3rd (1 point)
  - Highly Commended (0 points)
  - No placement
- [ ] Optional notes field per result
- [ ] A class cannot have more than one 1st, one 2nd, or one 3rd place result
- [ ] Result is saved immediately and visible to admin

**Expected Result:** Result is recorded against the entry and points assigned.

---

### US-7.2: Edit a Result (Judge)
**As a** Judge
**I want to** edit a result I've already entered
**So that** I can correct mistakes before results are finalised

**Acceptance Criteria:**
- [ ] Existing placement pre-selected in the edit form
- [ ] Changing a placement updates the exhibitor's points total
- [ ] Placement uniqueness rule still enforced on edit

**Expected Result:** Result is updated and exhibitor points recalculate.

---

### US-7.3: Enter / Edit Result (Admin)
**As an** Admin
**I want to** enter or edit any result
**So that** I can assist judges or make corrections

**Acceptance Criteria:**
- [ ] Admin can access result entry for any class in any section
- [ ] Same constraints apply as for judge result entry
- [ ] Result linked to the relevant judge (or marked as "admin entered")

**Expected Result:** Admin can manage all results regardless of judge assignment.

---

### US-7.4: View Results for a Class
**As an** Admin or Judge
**I want to** view all results for a show class
**So that** I can see placings at a glance

**Acceptance Criteria:**
- [ ] Results listed with: entry number, exhibitor name, placement, points, notes
- [ ] Unplaced entries shown separately
- [ ] Results sorted by placement (1st, 2nd, 3rd, Highly Commended, unplaced)

**Expected Result:** User sees the complete results for a class in placement order.

---

### US-7.5: View Points Leaderboard
**As an** Admin
**I want to** view a leaderboard of exhibitors ranked by total points
**So that** I can see who is leading the show overall

**Acceptance Criteria:**
- [ ] Shows total points per exhibitor across all classes
- [ ] Filterable by section
- [ ] Updates as results are entered

**Expected Result:** Admin sees a real-time points ranking.

---

## 8. Trophies

### US-8.1: Create a Trophy
**As an** Admin
**I want to** create a trophy and define which show classes count towards it
**So that** the system can automatically determine the winner

**Acceptance Criteria:**
- [ ] Form collects: trophy name, description (optional)
- [ ] Admin can assign one or more show classes to the trophy (multi-select)
- [ ] A show class can count towards multiple trophies
- [ ] Trophy appears in the trophy list

**Expected Result:** Trophy is created and linked to the relevant classes.

---

### US-8.2: Edit Trophy
**As an** Admin
**I want to** edit a trophy's name, description, or class assignments
**So that** I can adjust the trophy criteria

**Acceptance Criteria:**
- [ ] Existing values pre-filled
- [ ] Class assignments can be added or removed

**Expected Result:** Trophy is updated with the new details and class assignments.

---

### US-8.3: Delete Trophy
**As an** Admin
**I want to** delete a trophy
**So that** I can remove trophies added in error

**Acceptance Criteria:**
- [ ] Delete requires confirmation
- [ ] Trophy is removed from the list

**Expected Result:** Trophy is deleted from the system.

---

### US-8.4: View Trophy Winner
**As an** Admin
**I want to** see the current winner of each trophy
**So that** I can announce trophy winners at the show

**Acceptance Criteria:**
- [ ] For each trophy, the exhibitor with the most points across the trophy's assigned classes is identified
- [ ] In case of a tie, all tied exhibitors are listed
- [ ] Trophy winner updates automatically as results are entered
- [ ] Trophy list shows: trophy name, assigned classes, current winner, points total

**Expected Result:** Admin sees the current winner (or tied winners) for every trophy.

---

## 9. Public Display

### US-9.1: View Public Show Schedule
**As a** Public Visitor
**I want to** browse the show sections and classes without logging in
**So that** I can see what categories are in the show

**Acceptance Criteria:**
- [ ] Sections listed in display order
- [ ] Classes listed within each section in display order
- [ ] No login required
- [ ] No admin-only information (e.g. exhibitor contact details, payment status) is shown

**Expected Result:** Visitor sees a clean, read-only view of the show structure.

---

### US-9.2: View Public Results
**As a** Public Visitor
**I want to** view results for each show class
**So that** I can see who won

**Acceptance Criteria:**
- [ ] Results visible per class: exhibitor name, placement (1st/2nd/3rd/Highly Commended)
- [ ] Points are NOT shown on the public view (placements only)
- [ ] Classes with no results yet shown as "Results pending"
- [ ] No login required

**Expected Result:** Visitor can see show results without an account.

---

### US-9.3: View Public Trophy Winners
**As a** Public Visitor
**I want to** see which exhibitors won each trophy
**So that** I can find out the trophy results

**Acceptance Criteria:**
- [ ] Trophy list visible publicly
- [ ] Each trophy shows the winner's name (or "To be announced" if results are incomplete)
- [ ] No login required

**Expected Result:** Visitor can see trophy winners without an account.

---

## 10. Admin Dashboard

### US-10.1: View Dashboard Overview
**As an** Admin
**I want to** see key stats on the dashboard
**So that** I can monitor show progress at a glance

**Acceptance Criteria:**
- [ ] Dashboard shows:
  - Total sections and classes
  - Total exhibitors (adult / junior breakdown)
  - Total entries
  - Results entered vs. results outstanding
  - Number of exhibitors paid / unpaid
- [ ] Stats update in real time as data changes

**Expected Result:** Admin has an at-a-glance view of show status.

---

## Appendix: User Story Status

| ID | Story | Priority | Status |
|----|-------|----------|--------|
| US-1.1 | Admin Login | High | Pending |
| US-1.2 | Judge Login | High | Pending |
| US-1.3 | Password Reset | Medium | Pending |
| US-1.4 | Logout | High | Pending |
| US-2.1 | Create Show Section | High | Pending |
| US-2.2 | Edit Show Section | Medium | Pending |
| US-2.3 | Delete Show Section | Low | Pending |
| US-2.4 | List Show Sections | High | Pending |
| US-3.1 | Create Show Class | High | Pending |
| US-3.2 | Edit Show Class | Medium | Pending |
| US-3.3 | Delete Show Class | Low | Pending |
| US-3.4 | List Show Classes | High | Pending |
| US-4.1 | Register Exhibitor | High | Pending |
| US-4.2 | Edit Exhibitor | Medium | Pending |
| US-4.3 | Delete Exhibitor | Low | Pending |
| US-4.4 | List Exhibitors | High | Pending |
| US-4.5 | View Exhibitor Fee Summary | High | Pending |
| US-4.6 | Record Exhibitor Payment | High | Pending |
| US-5.1 | Create Judge Account | High | Pending |
| US-5.2 | Assign Judge to Sections | High | Pending |
| US-5.3 | Edit Judge Details | Medium | Pending |
| US-5.4 | List Judges | Medium | Pending |
| US-6.1 | Record an Entry | High | Pending |
| US-6.2 | Delete an Entry | Medium | Pending |
| US-6.3 | View Entries for a Class | High | Pending |
| US-6.4 | View All Entries for Exhibitor | Medium | Pending |
| US-6.5 | Record Entries from Exhibitor Record | High | Pending |
| US-7.1 | Enter Result (Judge) | High | Pending |
| US-7.2 | Edit Result (Judge) | Medium | Pending |
| US-7.3 | Enter / Edit Result (Admin) | High | Pending |
| US-7.4 | View Results for a Class | High | Pending |
| US-7.5 | View Points Leaderboard | Medium | Pending |
| US-8.1 | Create Trophy | High | Pending |
| US-8.2 | Edit Trophy | Medium | Pending |
| US-8.3 | Delete Trophy | Low | Pending |
| US-8.4 | View Trophy Winner | High | Pending |
| US-9.1 | Public Show Schedule | High | Pending |
| US-9.2 | Public Results | High | Pending |
| US-9.3 | Public Trophy Winners | Medium | Pending |
| US-10.1 | Admin Dashboard Overview | Medium | Pending |

# Bayangan Archive — Quality Assurance and Defense Test Plan

**Purpose:** Provide repeatable checks for the knowledge-management workflow, public archive, interactive learning, and system reliability.

**Important:** This document is a test plan, not a claim that every scenario has passed. The current development pass has PHP syntax checks and JavaScript syntax checks; live MySQL/browser testing must still be performed in the team's own XAMPP environment.

## A. Before testing

- [ ] Make a database backup from phpMyAdmin or the Admin Backup page.
- [ ] Extract the new package into a separate test folder first; do not overwrite the working copy before backup.
- [ ] Confirm `config/db.php` points to the correct `Bayangan_Archive` database and the local MySQL credentials.
- [ ] Open the system through `http://localhost/<folder-name>/` (not by double-clicking PHP files).
- [ ] Use a test pottery record and clearly label test content. Never present fabricated fieldwork as authentic Bayangan heritage data.
- [ ] Confirm that an authorized admin account can sign in and public pages remain accessible without a public account.

## B. Knowledge capture and provenance

| ID | Test | Expected result | Evidence to capture |
|---|---|---|---|
| KC-01 | Add a pottery record as Draft with available documentation details | Record saves and remains private from the public archive | Admin record list and public search |
| KC-02 | Publish a pottery record with required fields completed | Record appears in the public archive without a separate Verification Center step | Admin status and public archive |
| KC-03 | Add a detailed source in Source & Provenance | Source is linked to the selected pottery record | Source table and pottery ID/name |
| KC-04 | Mark a source Verified only after checking the evidence | Source status is saved; internal notes remain private | Public detail page and admin source record |
| KC-05 | Add or edit a traditional term with meaning, context, and source notes | Term saves and appears publicly only when published | Admin and public terminology pages |
| KC-06 | Check Knowledge Gaps | Page reports missing documentation fields; it does not block publication | Knowledge Gaps page |
| KC-07 | Attempt to create a camera-based Heritage Quest | Admin form rejects unsupported target type | Validation message |

## C. Connected records and retrieval

- [ ] Search the archive by pottery name, material, use, maker, local name, and cultural significance.
- [ ] Open a pottery detail page and follow its artisan profile link.
- [ ] Confirm related pottery and terminology links open the expected pages.
- [ ] Confirm only published pottery and terminology records appear in public search.
- [ ] Confirm verified source entries show their source type/title, approved informant identifier (if permitted), date/location, and citation/evidence description.
- [ ] Confirm a pottery record with no verified detailed source displays a clear “no separately verified source records” message instead of inventing evidence.

## D. Heritage map

- [ ] Search for an existing pottery name and verify the matching list is filtered.
- [ ] Search by local name, location, maker, material, use, or cultural significance.
- [ ] Confirm a pottery marker appears only when valid latitude/longitude values have been saved.
- [ ] Search a place not present in pottery records; confirm geocoding results are labeled as place-search results, not verified heritage records.
- [ ] Repeat with internet disabled; the page should show an understandable search error rather than claim a place was found.

## E. Learning and analytics

- [ ] Complete at least one quiz/game and confirm the saved attempt has player label, game name, difficulty, score, total, points, and date/time.
- [ ] Check that average score rate is calculated only from attempts with `total > 0`.
- [ ] Confirm analytics displays an empty-state message when there are no attempts or no activity during the last seven days.
- [ ] Confirm public player activity count is based on the last 30 days.
- [ ] Confirm learning-completion counts match the underlying saved completion records.
- [ ] Do not describe these usage metrics as proof that learning improved unless a separate approved learning evaluation supports that claim.

## F. Cross-browser profile, saved collection, and learning progress

| ID | Test | Expected result | Evidence to capture |
|---|---|---|---|
| XP-01 | In Browser A, create a public player profile and safely record the nickname/recovery code | Profile creation shows a one-time recovery code; code is not shown again automatically | Profile creation screen (hide code in shared screenshots) |
| XP-02 | In Browser A, save two published pots to My Collection | Both items appear in My Collection and are stored in `Public_saved_pots` for the active `guest_key` | Collection page and matching database rows |
| XP-03 | Complete a learning-path activity in Browser A | Completion appears in `Public_learning_path_progress` and the path progress count updates | Learning path page and database row |
| XP-04 | In Browser B, open Games and restore the exact nickname + recovery code | The active public profile key changes to the saved profile; existing game scores/XP/achievements remain associated with that profile | Games page and My Learning dashboard |
| XP-05 | In Browser B, open My Collection | The same published pots saved in Browser A appear without relying on Browser A's localStorage | My Collection screenshot |
| XP-06 | In Browser B, open the same learning path | Previously completed path activities appear completed and the count matches Browser A | Learning path screenshot |
| XP-07 | Remove a saved pot in Browser B, then refresh/reopen in Browser A after restoring the same profile | Removed pot is no longer in the database-backed collection; do not test Browser A with a different profile | Collection in both browsers and database row |
| XP-08 | Restore a different profile on a browser that has old local-only data | Old local-only collection/progress must not be automatically imported into the restored profile | Profile recovery screen and resulting collection |
| XP-09 | Test with network temporarily disconnected | The interface should indicate local-only/offline behavior rather than claim the server sync succeeded | Status message and browser network state |

**Important:** Cross-browser synchronization requires both browsers to reach the same deployed PHP/MySQL database and requires restoring the same profile. The nickname alone is not enough; the recovery code is required. Keep that code private.

## G. Security, stability, and data preservation

- [ ] Open an invalid or unpublished `pot.php?id=...`; expect a 404-style “Pot not found” page and no database error.
- [ ] Try to reply to a comment from another pottery record; expect validation to reject it.
- [ ] Try to report a comment belonging to a different pottery record; expect validation to reject it.
- [ ] Verify forms reject missing required fields and invalid status values.
- [ ] Confirm CSRF-protected admin forms can be submitted normally and reject invalid/missing tokens.
- [ ] Upload supported and unsupported image/video types and confirm the system handles them as documented.
- [ ] Back up, update code, and verify that existing database records remain present. Never drop tables to deploy this package.
- [ ] Test logout and browser Back behavior for admin pages on the actual local setup.

## H. Suggested defense demonstration (5–8 minutes)

1. Show the public archive and search for an existing pottery record.
2. Open the detail page and point out the linked artisan, related knowledge, and provenance fields.
3. As admin, add a clearly labeled test record as Draft.
4. Show the Admin-managed source/provenance fields and explain that Knowledge Gaps is a documentation-completeness report, not a publication gate.
5. Publish the record and confirm it appears in the public archive; do not claim verification unless its source was actually checked.
6. Return to the public archive and search for the newly published record.
7. Complete a game activity and show the saved attempt and analytics summary.
8. Finish by showing the Knowledge Gaps page and explaining how it helps the team prioritize missing documentation. The separate Knowledge Verification Center is retired.

## I. Evaluation evidence to collect

- Approved test cases and pass/fail results.
- Screenshots of key workflows before and after actions.
- Actual counts and data from the team's test database.
- Respondent evaluation using the adviser/panel-approved instrument.
- Fieldwork/source permission documentation where applicable.
- A list of known limitations and fixes completed before final defense.

## Current known boundary

A database status cannot establish that cultural information is historically correct by software alone. The research team still needs to confirm content with authorized informants, cite its source, respect consent and privacy, and report any unverified or incomplete knowledge honestly.

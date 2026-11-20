# Bayangan Archive — Consolidated Upgrade and QA Report
Date: 2026-11-06
Source archive: BayanganArchive_AdminActivityLog_NoDemoAccounts_2026-11-06(1).zip

## Changes made in this package

1. Corrected the shared navigation Logout anchor closing tag.
2. Restricted Heritage Quest target types to the supported non-camera activities: explore, term, process, daily_challenge, game, and pottery. The Admin form now uses a dropdown instead of unrestricted text input. Camera quests cannot be newly created/published through this form. Existing camera quests are retained as Draft by the existing schema update; no historical quest rows were deleted.
3. Retired the separate Knowledge Verification Center from fresh-install schema creation and runtime migration. The legacy `Knowledge_reviews` table is not dropped from existing databases. The backup routine skips optional tables that are absent, and includes the legacy table if it exists in an older database.
4. Updated README instructions so they no longer direct users to a Verification Center or imply that it gates publication.
5. Removed the inaccessible student, researcher, and artisan role dashboards, the artisan account submission page, and the Admin Submissions review page. Public knowledge contributions remain under Community.
6. Updated the fresh-install SQL to create Admin-only user roles and removed the retired `Submissions` table from fresh installs. Existing databases are not altered or dropped; legacy `Submissions` and `Knowledge_reviews` tables remain untouched and are preserved in backups when present.
7. Updated Analytics so saved-pot totals and the Most Saved Pots report include both account Favorites (if any legacy records remain) and anonymous guest-player collections.

## Scope intentionally preserved

- Public browsing without a regular visitor account; Admin-only login.
- Optional guest player identity/progress for games.
- Pottery Archive, category management, Learning Materials, Tutorials, Traditional Terminology, Interactive Lab, games, Community, analytics, activity logs, provenance/source records, backup, and accessibility.
- No QR Code, reservation, Gallery, Documents page, player-management module, camera-based quest, 3D viewer, AI recommendation, or Virtual Heritage Exhibition was added. Public community contribution forms remain available without requiring an artisan account.

## Automated checks performed

- PHP syntax lint: all 70 PHP files passed (`php -l`).
- JavaScript syntax check: `assets/app.js` passed (`node --check`).
- ZIP integrity test.
- Static checks for the retired Verification Center navigation/workflow references in the current README and active PHP code.
- Static checks that quest target validation and the logout anchor fix are present.

## Tests not possible in this environment

No MySQL/MariaDB server or PHP MySQL PDO driver is available in this execution environment. Therefore these actions were not live-tested: database import/migrations, Admin login, CRUD transactions, uploads, public page rendering, game interaction/scoring, analytics queries, backup restoration, mobile browser behavior, and every button in a real browser. These must be checked on the group's XAMPP installation before replacing the working copy or deploying online.

## Deployment safety

1. Keep the previous ZIP and a full database export.
2. Test this package in a separate XAMPP folder/database first.
3. Import the updated `database.sql` only into a fresh test database. For an existing project database, do not drop tables; the app migration intentionally does not drop legacy tables.
4. Run the manual acceptance tests in `CAPSTONE_QUALITY_ASSURANCE_2026-10-12.md` and record screenshots/results.
5. Do not overwrite the production/live site until database and browser tests pass.

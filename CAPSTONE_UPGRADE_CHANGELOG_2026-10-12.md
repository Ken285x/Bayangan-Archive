# Bayangan Archive — Capstone Quality Upgrade

## Implemented in this package

- Added an admin Source & Provenance management page (`admin/sources.php`) to attach interview, observation, recording, photo, document, or other evidence records to a pottery record.
- Added a dashboard link to Source & Provenance.
- Public pottery detail pages show separately stored source records only when those source records are marked Verified. Internal source review notes are not exposed publicly.
- Updated the Verification Center to validate record IDs, require a primary source before verifying pottery, require source/informant or fieldwork notes before verifying a traditional term, and show review history.
- Fixed the Verification Center's CSRF function call to use the helper defined by the project (`verify_csrf()`).
- Added publication checks for new/draft pottery and terminology records. A new/draft pottery record requires a source/informant and verified status before publishing; a new/draft term requires fieldwork/source evidence and verified status.
- Moved verification status editing out of the ordinary content forms so review decisions are recorded through the Verification Center.
- Core knowledge edits to already published pottery records or terms reset verification to Pending and move the record to Draft until it is reviewed again.
- Extended admin analytics with verified/pending source counts, active public players in the last 30 days, average saved game score rate, perfect attempts, total scored items, per-game score rate, and seven-day game activity.
- Extended map record search to include local name, material, use/purpose, and cultural significance in addition to name, maker, and location.
- Extended the Knowledge Gaps report to recognize verified source records and link directly to source management and verification.
- Updated the admin dashboard count to show active admins rather than every legacy account role.
- Hardened pottery detail interactions so invalid/unpublished IDs are rejected before view logging, comment replies must belong to the same pottery discussion, and reported comments must belong to the same record.
- Added `CAPSTONE_QUALITY_ASSURANCE_2026-10-12.md` with manual acceptance tests and a defense demonstration sequence.

## Data safety and compatibility

- The package is based on the existing Bayangan Archive project and retains existing modules/files and the existing database name.
- No existing tables are dropped by these changes. `Pot_sources` and `Knowledge_reviews` are already part of the project's SQL and runtime migration setup.
- Existing published records are not automatically hidden just because they predate the new workflow. Review them through Knowledge Gaps and Verification Center. The stricter publication gate applies to new/draft records and to records returned to Draft after core knowledge changes.
- The app cannot verify cultural accuracy by itself. Use real, permitted fieldwork/source information and do not mark a record Verified without checking it.

## Validation performed during packaging

- PHP syntax lint passed for all 72 PHP files.
- JavaScript syntax check passed for `assets/app.js` and the inline heritage map script.
- ZIP integrity check passed for the final package.
- Live XAMPP/MySQL/browser integration tests were not run in this packaging environment. Complete the manual test plan in `CAPSTONE_QUALITY_ASSURANCE_2026-10-12.md` before using the package in the final defense.

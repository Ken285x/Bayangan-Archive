# Bayangan Archive — Dynamic CMS Final

This version is designed to address the panel concern that the system is static.

## Dynamic modules

- Access model: public browsing and community participation without a regular user account; Admin-only login and administrator account management

- Pottery Archive: records, photos, videos, search, status, edit/delete
- Learning Materials: lessons, photos, videos, order, status, edit/delete
- Game CMS: identification/quiz questions, images, choices, answers, explanations, matching pairs, arrange steps
- Administrator Accounts: create/edit/activate/deactivate administrator accounts with access levels
- Game Results: scores saved to MySQL for logged-in users
- Backup: includes all CMS tables and game tables

## Database

Import only `database.sql` in phpMyAdmin. There is no patch SQL file in this package.

## Dynamic proof for panel

1. Log in as Admin.
2. Open Admin Dashboard → Pottery Records.
3. Add an actual pottery record and upload an actual photo.
4. Set status to Published.
5. Open Public Archive without changing PHP code.
6. Confirm the new record appears.
7. Open Admin Dashboard → Game Content.
8. Add an actual question and actual pottery photo.
9. Publish it.
10. Open the corresponding game and confirm the new content appears.

The page layout and game mechanics remain coded in PHP/HTML/CSS/JavaScript, which is normal. The research content and records are database-driven and are not hardcoded into the public archive/game data.

## Developer code comments

This version includes guide comments throughout the source files. See `CODE_GUIDE.md` for the main files and search labels to use when making changes.

## Jigsaw learning reveal

Admins can set a pre-game clue and link each Jigsaw image to a published Pot Record. After completion, the system displays an animated result and reveals the linked pottery details.


## Thesis-level extensions in this build
- Advanced archive search and filters
- Documentation provenance and verification metadata
- Artisan knowledge contribution and admin approval workflow
- Dedicated Tutorial Library with completion tracking
- User learning progress and saved collections
- Notifications
- Admin analytics and audit/activity logs
- Interactive games with scores, points, hints, achievements, and Jigsaw completion effects
- Global image/video preview before upload


## Public Access Upgrade
Students, researchers, pot makers, and visitors no longer need accounts. Only administrators log in. Public interaction includes comments/replies, community questions and knowledge contributions, heritage map, maker stories, public games, and guest-player game records. Game progress can use an optional guest player name; visitors do not need a regular account. Only administrator accounts are created and managed by the system.


## Traditional Terminology
The system includes a public Traditional Terminology Library for verified Bayangan-local pottery vocabulary. Admins with `super` or `content` permission can add the local word, pronunciation guide, meaning, usage example, source/informant, documentation note, and a pronunciation recording. The browser recorder uses the device microphone and stores the resulting audio through the normal upload workflow.


## Interactive Heritage Lab upgrade
The current build includes an interactive public learning experience at `interactive.php` and an Admin CMS at `admin/interactive.php`.

### Included interactive features
- Traditional Terminology with pronunciation audio, English/Filipino meanings, photos, and verification status.
- Browser-local Knowledge XP, levels, badges, quest progress, and learning history.
- Heritage Quest missions.
- Click-through interactive pot-making process explorer.
- Traditional Terminology audio/identification challenge.
- Knowledge Explorer search across published pottery and terminology records.
- Visual Knowledge Map connecting archive, terms, process, pots, stories, tutorials, and games.
- Artisan Story / Ask the Artisan presentation with optional recorded audio.
- Knowledge of the Day / Daily Challenge.
- User feedback collection.
- Anonymous interaction event analytics for the admin dashboard.

The system does not include a 3D viewer or AI assistant in this upgrade. Demo/starter interactive process and quest records must be reviewed against actual fieldwork before final defense/public release.

## Documentation sources and completeness

The separate Knowledge Verification Center workflow has been retired. Adding or editing a pottery record or traditional term does not require a separate verification-center step. Admins may still record provenance information and source references where available. The Knowledge Gaps page is a documentation-completeness report; it is not an approval gate and does not block publication. Do not label a source as verified unless it has actually been checked.

## Source & provenance module

`admin/sources.php` uses the existing `Pot_sources` table. It supports adding, editing, verifying, and deleting source records tied to a pottery record. No separate manual SQL migration is expected when using this package because `config/db.php` already creates the table if missing. Always back up the database before deploying code updates.

## Analytics interpretation

The Analytics page reports saved game attempts, average score rate (score divided by total items), perfect attempts, game accuracy by activity, recent seven-day game activity, learning completions, public players active in the last 30 days, and source/provenance counts. These are **system usage indicators**. They do not prove improved learning on their own; a claim of learning improvement needs a suitable pre-test/post-test or another approved evaluation design and real respondent data.

## Validation and acceptance test guide

See `CAPSTONE_QUALITY_ASSURANCE_2026-10-12.md` for the manual test checklist and evidence to collect before defense. Syntax checks do not replace a live XAMPP/MySQL/browser test.


## Unified Pottery + Public Artisan Submissions (2026-11-17)

- Public artisans can submit pottery at `submit_pottery.php` without registering or signing in.
- Submissions are stored separately as `pending`, `approved`, or `rejected`; only approved submissions are converted into normal `Pots` and `Archive_metadata` records.
- Admin review is available at `admin/artisan_submissions.php` and from the Pottery Records management page. New submissions create Admin notifications through the existing notification helper.
- Approved artisan entries use the same public `archive.php` listing and `pot.php` detail page as Admin-entered records. Existing Pots, uploads, comments, ratings, corrections, categories, and learning/game data are not deleted or rewritten.
- Pottery search now uses one keyword field and a field selector (including All Fields). Optional price and contact details remain optional.
- Existing installations create the new submissions table automatically through the additive `ba_ensure_artisan_submissions()` migration. Fresh installations also receive the table from `database.sql`.

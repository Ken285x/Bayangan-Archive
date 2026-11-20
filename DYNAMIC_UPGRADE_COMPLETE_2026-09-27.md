# Dynamic Upgrade — 2026-09-27

This build keeps the existing Bayangan Archive structure and adds the dynamic behavior needed for a public learning/knowledge-management workflow.

## Included
- Public global search across pottery, learning materials, tutorials, terminology, process stages, and artisan stories.
- Public learner profile without mandatory account: nickname, XP, game history, learning completion, daily challenge history, streak, and public achievements are stored by browser/session key.
- Public game results remain in `Game_results` and now also keep a persistent `guest_key` for public-player continuity.
- Public learning completion is stored in `Public_learning_progress` for lessons and tutorials.
- Daily Challenge saves one attempt per public player per day and awards XP for a correct answer.
- Public game achievements work without an account through `Public_player_achievements`.
- Admin notification count is visible in the navigation.
- Media paths use the shared `media_url()` helper to avoid duplicate application prefixes.
- Existing community contribution/correction review remains available to Admin.
- Existing audit/version-history/verification modules are retained.

## Intentionally retained exclusions
- No QR code feature.
- No printable pottery documentation feature.
- No virtual heritage exhibition.
- No 3D viewer.
- No AI assistant/recommendation engine.
- No payment processing or e-commerce.
- No availability indicator.

## Testing performed on the package
- PHP syntax checks on modified PHP files.
- Source-level inspection of database migrations, public/admin flows, media URL handling, and game save logic.
- JavaScript source remains in the existing game/interactivity files; browser/XAMPP live MySQL testing is not possible in this build environment.

## XAMPP note
The first successful page load after importing the database runs additive migrations in `config/db.php`. Existing records are not intentionally deleted or overwritten by these migrations.
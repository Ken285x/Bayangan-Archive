# Bayangan Archive requested fixes — 2026-10-11

- Added public player profile recovery using nickname + recovery code. Recovery hashes are stored in `Public_players.recovery_hash`; plaintext recovery code is displayed only on creation. Existing game records, XP, achievements, learning completion, and interactive-lab progress stay attached to the same `guest_key`; interactive progress is synced through `Public_interactive_progress`.
- Added an automatic schema migration in `config/db.php` and included the new field in `database.sql`.
- Corrected Games navigation active mapping so Home and Games do not share the same active class; active brown styling remains brown on hover/focus.
- Removed the public and admin Artisan Stories interface, dashboard count, sidebar link, global search story results, and story panel from artisan profiles; the old URL redirects to Artisan Profiles. Existing Artisan_stories table/records are intentionally preserved for data safety.
- Changed seller phone contact to `tel:` Call Seller only; removed WhatsApp link. Email contacts continue to use email.
- Heritage Quest now checks activity requirements before granting points; a click alone does not mark a mission complete. Requirements are based on actual in-page activity types and successful saved game results.

Static syntax checks are included; please verify against the project's actual XAMPP database and browser before deployment.

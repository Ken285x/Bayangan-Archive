BAYANGAN ARCHIVE - ADMIN AUDIT TRAIL AND POTTERY FORM CLEANUP
Update date: 2026-11-06

CHANGES
1. Added a dedicated Admin Activity Log page at admin/activity_logs.php.
   - Available from the Admin Dashboard.
   - Shows administrator name, action, record details, and database timestamp.
   - Includes search and date filters with pagination.
2. Expanded the Activity_logs records with actor_name and actor_role snapshots.
   - Existing databases are upgraded automatically by config/db.php using non-destructive
     ADD COLUMN checks.
   - New database imports include these fields in database.sql.
   - The actor name/role are stored with each event so the record remains identifiable
     if the Admin account is later deleted.
3. Added audit entries for administrator sign-in/sign-out, pottery CRUD, categories,
   learning materials, tutorials, terminology, game content, interactive content,
   community moderation, source/provenance management, administrator account
   management, artisan submission review, pottery-version restoration, player
   recovery-code reset, and database backup download.
4. Removed Map Latitude, Map Longitude, and the editable Record Version field from
   the Add/Edit Pottery form.
5. Pottery revisions remain automatic: when an existing pottery record is updated,
   a snapshot is stored as Revision 1, Revision 2, etc. The Admin version-history
   restore feature remains available.
6. Removed the public-facing Record Version label from the pottery detail page.

DATABASE SAFETY
- Existing Archive_metadata latitude, longitude, and version_label columns are NOT
  dropped. They are retained for compatibility with existing records and the map page.
- Existing Pot_versions records are NOT deleted.
- The only automatic database schema change is adding actor_name and actor_role to
  Activity_logs if they are missing.

CHECKS
- PHP syntax lint was run against all PHP files in this project; all passed.
- This is a static syntax check only. Live XAMPP/MySQL behavior, browser buttons,
  uploads, and all feature workflows still need to be tested on the user's computer.

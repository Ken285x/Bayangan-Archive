# Cross-browser player progress update — 2026-10-12

## What changed
- Saved pottery IDs are now stored in `Public_saved_pots` and associated with the active public player profile (`guest_key`). Browser-local cache keys are scoped to that profile to reduce accidental mixing between players.
- `collection.php` loads the database collection, imports older `bayangan_saved_pots` localStorage values without deleting database entries when it is safe to do so, and supports removing saved pots.
- Archive and pottery-detail Save to My Collection buttons now sync add/remove operations to the database. localStorage remains as an offline/legacy fallback.
- Learning-path completion is now stored in `Public_learning_path_progress` and synchronized with a profile-scoped local cache. Older local-only path progress is imported only when the current browser is not in a restored-profile session.
- Both tables are created additively by `config/db.php`; no existing table is dropped or existing data deleted.

## How to use on another browser/device
1. Open Games.
2. Use **Restore Existing Profile** with the exact nickname and recovery code.
3. After the profile is restored, open My Collection or a learning path while online.
4. The system loads saved items/completions from MySQL for that profile.

## Important limitations
- A public visitor who has not restored a profile uses the current browser session's temporary public player key. Their data is not guaranteed to be accessible on another browser until they create and safely retain a recovery code.
- Game records/XP already written to MySQL remain tied to the same `guest_key` and should reappear after successful profile recovery.
- Older data that existed only in a browser's localStorage can be imported only when that browser is still available and opened while the correct profile is active. It cannot be recovered from a different browser if the original local storage was lost.
- This update requires a working MySQL connection and PHP session/cookie support. Live browser integration against the user's own XAMPP database is still required.

- Game completion activities and terminology-learning XP now use the same profile-scoped interactive progress API, so successful syncs are retained in MySQL.
- Admin backup table list includes the new saved-pot and learning-path progress tables.

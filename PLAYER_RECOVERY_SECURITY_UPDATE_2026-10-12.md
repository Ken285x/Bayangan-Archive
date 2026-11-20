# Player profile recovery safety update — 2026-10-12

## Included
- Retains the optional “Remember this device for 30 days” feature using an HttpOnly random token whose hash is stored server-side.
- Adds a Super Admin-only Player Profile Recovery page.
- Recovery reset requires the admin's current password, an explicit ownership-verification confirmation, and a verification note.
- A new random recovery code is shown once and stored as a password hash. The code itself is not written to the audit log.
- All remembered-device tokens for the profile are revoked during a reset.
- Existing profile identity and progress records are preserved; only the recovery hash and device tokens are changed.
- Logs the reset and verification note in `Activity_logs`.

## Safety limitation
Nickname alone is not proof of ownership. Admins must independently verify the claimant before using this tool. If ownership cannot be established reliably, do not reset the profile.

## Deployment
Replace the application files with this package. The existing `Public_players`, `Public_player_devices`, and `Activity_logs` tables are created/ensured by `config/db.php` in this package. Keep database backups before deployment.

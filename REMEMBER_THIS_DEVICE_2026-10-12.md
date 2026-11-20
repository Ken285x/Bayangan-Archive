# Remember This Device — Bayangan Archive

- The Games page now has an optional “Remember this device for 30 days” checkbox on profile creation and profile recovery.
- When checked, the server generates a random device token, stores only its SHA-256 hash in `Public_player_devices`, and places the raw token in an HttpOnly, SameSite=Lax cookie.
- When the browser returns, the token is checked against the database and expiry before restoring the player session. Nickname and recovery code remain necessary to restore on a new browser/device.
- Unchecking the option clears the remember cookie on that browser. Existing server tokens expire after 30 days.
- This feature requires cookies and database connectivity. On localhost HTTP, the cookie is not marked Secure; on HTTPS it is marked Secure.
- A remembered browser is convenient, not a replacement for a recovery code. Anyone with access to an unlocked, remembered browser may use that profile.

## Manual test checklist
1. Create a profile, tick Remember This Device, save the recovery code.
2. Close and reopen the browser, revisit the site, and check that the same player profile is active.
3. Untick the option on another test profile and confirm it does not persist after the session ends.
4. Recover the same profile in a different browser with nickname + recovery code and tick the option.
5. Confirm My Collection and supported progress load from the database.
6. Clear the remembered-device cookie; confirm the profile is no longer automatically restored, but remains recoverable with its code.
7. Confirm an expired/invalid token does not restore a profile.

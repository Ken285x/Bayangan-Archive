# Bayangan Archive — Navigation Cleanup

## Scope
This cleanup preserves the existing **TOP + LEFT** navigation layout and does not remove or reorder any feature link.

### Top navigation
- Constrained flex/grid area so tabs cannot push the Admin controls off-screen.
- Horizontal overflow is enabled.
- Native Chrome/Edge scrollbar remains visible.
- A synchronized custom scrollbar remains below the tabs as a clear visual fallback.
- Mouse wheel over the tabs moves horizontally when overflow exists.
- Tabs remain compact and keyboard/focus accessible.

### Left navigation
- Sidebar remains fixed on desktop.
- Sidebar content has its own vertical scroll area.
- Scrollbar is visible and independently usable.
- Existing sidebar groups and links are preserved.

## Cleanup performed
- Removed duplicate navigation CSS blocks.
- Removed conflicting rules that hid the top scrollbar.
- Kept one authoritative navigation CSS definition.
- Added cache-busting to `style.css`.
- Corrected three case-only navigation targets to match actual filenames on disk:
  - `tutorials.php`
  - `learning_paths.php`
  - `artisan_profiles.php`
- PHP syntax check: all PHP files passed.
- JavaScript syntax check: `assets/app.js` passed.

## Excluded features
No QR Code, Printable Pottery Documentation, or Virtual Heritage Exhibition feature was added.

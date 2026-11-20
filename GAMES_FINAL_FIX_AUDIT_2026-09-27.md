# Bayangan Archive — Games Final Fix Audit
Date: 2026-09-27

## Game 05 — Jigsaw / Drag & Slide
- Every visible puzzle box contains an image segment.
- Every tile is draggable with mouse or touch.
- A tile may be dropped on ANY other puzzle box.
- Dropping on another box swaps the two image positions with an animation.
- No blank/empty slide target is required.
- Easy: 3x3 (9 tiles); Medium: 4x4 (16 tiles); Hard: 5x5 (25 tiles).
- Completion is detected when every image segment is in its correct position.
- Restart, Check, Hint, timer, moves, score, completion and next-puzzle actions retained.

## Game 03 — Pottery Matching
- The board has two separate groups: Pottery Images and Pot Names.
- Easy: 3 image boxes + 3 name boxes.
- Medium: 4 image boxes + 4 name boxes.
- Hard: 6 image boxes + 6 name boxes.
- Admin creates each pair by entering the pot name and uploading its pottery image.
- Image and name positions are randomized independently.
- Player clicks an image, then its matching pot name.
- Correct pairs become locked/marked as matched.
- Incorrect choices are visibly indicated and can be tried again.
- Hint highlights the correct name for the selected image.
- Restart, difficulty buttons, hint, exit, score and game-result saving retained.

## Validation
- All PHP files: syntax check passed.
- Jigsaw inline JavaScript: syntax check passed.
- Matching inline JavaScript: syntax check passed.
- assets/app.js: syntax check passed.
- Existing database schema retained; no new table required for these fixes.
- Existing Admin CRUD, CSRF handling and upload validation retained.
- Existing Back/Delete-scroll/Logout protections retained from the base package.

## Explicitly not included
- QR Code
- Printable Pottery Documentation
- Virtual Heritage Exhibition

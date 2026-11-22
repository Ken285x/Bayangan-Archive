# Bayangan Archive — Games Corrected Build — 2026-09-27

## Pottery Match
- All cards are mixed into one board.
- All cards start face down.
- No Image/Name labels are shown before reveal.
- Clicking a card flips it.
- One Admin-created pair consists of one pottery image and one exact pot name.
- Matching requires opposite card types with the same Admin pair ID.
- Incorrect pairs flip back automatically.
- Correct pairs stay revealed and receive a success effect.
- Admin controls the image, pot name, difficulty, hint, and publication status.
- Image fronts use `object-fit: contain` so the full uploaded picture remains visible across aspect ratios.

## Jigsaw
- Every grid cell contains a visible image segment; there is no empty blank box.
- Any tile can be dragged to any other grid cell.
- Dropping swaps the two tile positions.
- Pointer capture makes mouse/touch dragging more reliable.
- Click-two-tiles swap is retained as an accessibility/device fallback.
- Drop target and selected tile states are visible.
- Easy 3x3, Medium 4x4, Hard 5x5 remain available.
- Restart, Check, Hint, Timer, Moves, completion, score saving, replay, and next puzzle remain available.

## Validation
- PHP syntax checked for all PHP files in the package.
- Inline JavaScript syntax checked for Jigsaw and Matching.
- Existing game APIs and database tables retained.
- No QR Code, Printable Pottery Documentation, or Virtual Heritage Exhibition added.

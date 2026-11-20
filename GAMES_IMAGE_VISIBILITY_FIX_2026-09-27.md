# Games Image Visibility Fix — 2026-09-27

Fixed the game image path handling and rendering.

- Added `media_url()` so stored upload URLs are not prefixed with APP_BASE twice.
- Jigsaw tiles now render a real `<img>` element for every tile, clipped to each puzzle cell.
- Matching image cards render the actual uploaded image with `object-fit: contain`.
- Upload dimensions remain unrestricted by CSS; portrait, landscape, square, and large images fit inside the game card/tile.
- Existing gameplay behavior is preserved.

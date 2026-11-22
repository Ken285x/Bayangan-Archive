# Bayangan Archive — Contact Seller & Media Layout Update (2026-10-11)

## Changes
- Replaced public reservation calls-to-action on pottery details with seller contact information and contact links.
- Removed Reservation Cart and Reservation Status from the public navigation.
- Removed reservation activity from the public homepage activity feed.
- Kept public archive search/filter, artisan profiles, view logging, public favorites via My Collection/localStorage, and Suggest a Correction.
- Added pottery thumbnails to artisan profile cards. Profile pottery images are constrained to responsive thumbnail frames and use object-fit: contain to prevent oversized images.
- Tutorial and documentation media now stop other playing audio/video when a new media player starts.
- Added contact actions for email, telephone/WhatsApp-style phone numbers, and verified HTTP(S) URLs; plain contact text remains visible when it cannot safely be interpreted as a link.

## Notes
- The legacy reservation PHP files and database tables are retained in the source package for data safety, but their links/actions are removed from the public-facing navigation and pottery details. Do not delete old reservation tables if they contain records you may need.
- Contact information must be entered and published with the artisan's permission.
- Test the project in XAMPP after extracting and importing/using its database. This package was syntax-checked; it was not connected to the user's live XAMPP database for end-to-end browser testing.

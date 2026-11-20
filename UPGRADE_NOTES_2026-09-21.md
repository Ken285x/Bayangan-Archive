> Historical note: This file records an earlier development snapshot. Use the current README and CONSOLIDATED_UPGRADE_QA_2026-11-06.md for the active feature scope; the Verification Center and role-based student/researcher/artisan dashboards described below are retired.

# Bayangan Archive — Dynamic Thesis Upgrade

This build upgrades the existing navigation/reservation baseline without removing the existing public archive, learning, tutorials, games, interactive lab, community, reservations, or admin CMS.

## Added / upgraded
- Advanced Knowledge Search
- Pottery version history with restore action
- Knowledge Verification Center for pottery and terminology
- Artisan Profiles derived from published pottery records and linked artisan stories
- Related Knowledge on pottery detail pages
- My Learning Dashboard
- Admin Analytics 2.0 indicators for sources, verified records, and knowledge gaps
- Knowledge Gap Dashboard
- Heritage Map retained as the interactive map module
- Community contribution/correction workflow retained
- Accessibility settings: text size, contrast, reduced motion
- System feedback/evaluation page
- Compact visual design and smaller controls

## Game fixes
### Jigsaw
- Uses legal-move shuffling so generated puzzles remain solvable.
- Drag-to-slide interaction is retained; tiles are not click-to-swap.
- The logical slide slot is visually filled with an image segment so the player does not see an empty white box.
- Easy = 3x3, Medium = 4x4, Hard = 5x5.
- Hint, timer, moves, score, achievements, completion information, and next-puzzle flow remain.

### Matching
- Matching is now Picture ↔ Pot/Material Name.
- Admin uploads the pottery/material picture and enters the correct name/material.
- The right-side picture is not required for the new design.

## Important content note
Starter/demo data in the archive must be replaced or verified using the project's actual field interviews, observations, photographs, videos, audio recordings, and source documentation before public deployment or final defense claims.

## Installation
1. Back up the current Bayangan Archive folder and database.
2. Replace the project files with this build.
3. Import the updated `database.sql` for a fresh installation, or let `config/db.php` run its additive migrations on an existing database.
4. Open the Admin Dashboard and review Verification Center and Knowledge Gaps.
5. Add verified artisan stories, pottery data, game pictures/names, and actual field documentation.
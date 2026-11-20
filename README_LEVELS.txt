BAYANGAN ARCHIVE - GAME DIFFICULTY UPDATE

This version adds:
- Easy, Medium, and Hard difficulty levels to all 4 games.
- Admin-selectable difficulty when creating/editing game content.
- Large database-driven question/content bank; admins can add as many published items as needed.
- Up to 10 random published items are selected per game attempt for the selected difficulty.
- Difficulty-based scoring is enforced server-side:
  Easy: 10 points per correct quiz answer/matching pair; 15 points per correct arrange step.
  Medium: 15 points per correct quiz answer/matching pair; 20 points per correct arrange step.
  Hard: 20 points per correct quiz answer/matching pair; 25 points per correct arrange step.
- Game records now store the difficulty level.
- Existing hints, leaderboard, points, and game history remain supported.

IMPORTANT:
1. Import the updated database.sql into a fresh bayangan_archive database for the cleanest test.
2. If using an older database, config/db.php automatically adds the new difficulty columns.
3. Starter questions are DEMO/TEST CONTENT. Replace or verify them using actual Bayangan fieldwork documentation before final defense.

JIGSAW PUZZLE UPDATE
- Game 05: Jigsaw Puzzle
- Admin can add, edit, publish, or delete the pottery images used by the jigsaw game.
- Easy: 3x3 = 9 pieces, 10 points when completed.
- Medium: 4x4 = 16 pieces, 15 points when completed.
- Hard: 5x5 = 25 pieces, 20 points when completed.
- Pieces are randomized for each attempt and can be dragged or selected/clicked into slots.
- Logged-in players have their score, difficulty, time, and hints recorded in game_results.
- Jigsaw achievements include Jigsaw Starter, Puzzle Explorer, Puzzle Master, Hard Mode Hero, and Speed Solver.
- Achievement records are visible on the Progress page.

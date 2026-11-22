# Bayangan Archive – Code Guide

## Where to edit common features
- `config/db.php` – database connection and automatic schema migrations.
- `config/auth.php` – login/session/role access.
- `config/activity.php` – audit logs and notifications helpers.
- `assets/style.css` – all visual design, responsive layout, game effects, previews.
- `assets/app.js` – navigation, file previews, game completion effects.
- `admin/pots.php` – pottery records and documentation/provenance metadata.
- `archive.php` – archive search/filter and saved collection.
- `pot.php` – full pottery information, provenance, and related content.
- `learning.php` – lessons and completion tracking.
- `tutorials.php` / `admin/tutorials.php` – tutorial library.
- `admin/games.php` – game content and Jigsaw CMS.
- `games/jigsaw.php` – Jigsaw gameplay and completion reveal.
- `api/save_game.php` – server-side score, points, and achievement recording.
- `progress.php` – user learning/game progress and saved pots.
- `admin/analytics.php` – admin analytics and activity log.
- `database.sql` – full database schema for a clean installation.

## Search labels
Use VS Code Ctrl+F for: `GAME EFFECT HELPERS`, `FILE PREVIEW HELPERS`, `ACHIEVEMENT ENGINE`, `ANALYTICS QUERIES`, `THESIS DATA MANAGEMENT`, `Documentation & Provenance`, and `Tutorial Library`.

## Important
Starter data are demo/testing content. Replace them with verified fieldwork data before final defense.

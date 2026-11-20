# Bayangan Archive — Database Capitalization Setup

## Target names

Database:

`Bayangan_Archive`

All application table names are written with the required first-letter capitalization, for example:

- `Users`
- `Pots`
- `Learning_materials`
- `Game_results`
- `Jigsaw_puzzles`
- `Archive_metadata`
- `Traditional_terms`
- `Artisan_profiles`
- `Learning_paths`

The application PHP queries use the same table-name convention.

## Important XAMPP / Windows note

If phpMyAdmin still displays `bayangan_archive`, `users`, `pots`, etc. after importing this SQL, the project files are not the cause. On Windows, MySQL commonly runs with `lower_case_table_names=1`, which stores database/table names in lowercase. In that mode, an SQL file cannot force phpMyAdmin to display the original capitalization.

The required server setting for preserving the written capitalization is `lower_case_table_names=2`. This is a MySQL server initialization setting, not a PHP setting. Do **not** blindly change it on an already-initialized server: MySQL documents that this setting cannot simply be changed after server initialization.

## Safe procedure for a new/clean XAMPP database server

1. Stop MySQL in XAMPP.
2. Back up any databases you need before changing server configuration.
3. Configure the MySQL/MariaDB server for `lower_case_table_names=2` **before initializing the server/data directory**.
4. Start MySQL and verify with:

```sql
SHOW VARIABLES LIKE 'lower_case_table_names';
```

The value should be `2` for a server that preserves the written case.

5. Import `database.sql`.
6. Confirm in phpMyAdmin that the database is shown as `Bayangan_Archive` and the tables retain their written capitalization.

## If your existing XAMPP server already shows value 1

Do not edit `my.ini` and restart blindly. Changing this setting on an already-initialized MySQL 8 server is not a supported in-place change and can prevent the server from starting or create data-dictionary inconsistencies.

For an existing server that must keep its current data, the safe choice is to leave the server setting unchanged and use the supplied SQL/application files. The application remains compatible because MySQL compares identifiers without case sensitivity in this mode, but phpMyAdmin may continue displaying lowercase names.

If the panel requirement specifically requires the **visual capitalization in phpMyAdmin**, the database must be imported into a server/data directory that was initialized with the appropriate case-preserving setting.

## Do not use this in the SQL editor

```sql
DROP DATABASE bayangan_archive;
```

Your phpMyAdmin currently has DROP DATABASE statements disabled. Use phpMyAdmin's database Operations page or drop the selected tables instead.
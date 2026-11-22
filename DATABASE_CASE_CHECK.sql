-- Bayangan Archive database-name/table-name case diagnostic
-- Run this in phpMyAdmin after connecting to the server.

SHOW VARIABLES LIKE 'lower_case_table_names';

SELECT DATABASE() AS current_database;

SHOW TABLES;

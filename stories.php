<?php
// Artisan Stories has been retired from the public interface; preserve stored records in the database.
require_once 'config/app.php';
header('Location: ' . url('artisan_profiles.php'), true, 302);
exit;

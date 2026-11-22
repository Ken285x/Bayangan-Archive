<?php
/** Compatibility route: public learning progress is now handled by My Learning. */
require_once 'config/app.php';
header('Location: '.url('my_learning.php'), true, 302);
exit;

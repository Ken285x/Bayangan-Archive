<?php
/**
 * BAYANGAN ARCHIVE CODE GUIDE
 * File: logout.php
 * Purpose: Main system page.
 * Edit this file when you need to change the feature handled by this page.
 * Search for the section comments below before changing database queries,
 * form fields, access rules, or page output.
 */

require_once 'config/auth.php';
require_once 'config/db.php';
require_once 'config/activity.php';
if (logged_in()) {
    log_activity($pdo,(int)user()['id'],'Administrator logout','Signed out');
}
$_SESSION = [];
if (ini_get('session.use_cookies')) {
    $p = session_get_cookie_params();
    setcookie(session_name(), '', time()-42000, $p['path'], $p['domain'], $p['secure'], $p['httponly']);
}
session_destroy();
// Prevent a browser from restoring the authenticated page from its cache/history.
header('Clear-Site-Data: "cache"');
header('Location: '.url('login.php').'?logged_out=1');
exit;

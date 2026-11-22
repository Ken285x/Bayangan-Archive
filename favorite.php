<?php
/**
 * BAYANGAN ARCHIVE CODE GUIDE
 * File: favorite.php
 * Purpose: Saves/removes a user's favorite pottery record and returns to the previous page.
 */
require_once 'config/auth.php';
require_login();
require_once 'config/db.php';
require_once 'config/csrf.php';
if($_SERVER['REQUEST_METHOD']!=='POST') { http_response_code(400); exit('Invalid request.'); }
verify_csrf();
$potId=(int)($_POST['pot_id']??0);
$check=$pdo->prepare('SELECT id FROM Favorites WHERE user_id=? AND pot_id=? AND tutorial_id IS NULL');
$check->execute([user()['id'],$potId]);
if($check->fetch()){
    $pdo->prepare('DELETE FROM Favorites WHERE user_id=? AND pot_id=? AND tutorial_id IS NULL')->execute([user()['id'],$potId]);
}else{
    $pdo->prepare('INSERT INTO Favorites(user_id,pot_id) VALUES(?,?)')->execute([user()['id'],$potId]);
}
// Favorite changes are visitor preferences, not administrator audit events.
header('Location: '.($_SERVER['HTTP_REFERER']??url('archive.php')));
exit;
?>

<?php
/**
 * BAYANGAN ARCHIVE CODE GUIDE
 * File: Notifications.php
 * Purpose: Displays user-specific system Notifications.
 */
require_once 'config/auth.php'; require_login(); require_once 'config/db.php';
$pdo->prepare('UPDATE Notifications SET is_read=1 WHERE user_id=?')->execute([user()['id']]);
$q=$pdo->prepare('SELECT * FROM Notifications WHERE user_id=? ORDER BY created_at DESC LIMIT 50'); $q->execute([user()['id']]); $rows=$q->fetchAll(); $title='Notifications'; include 'partials/header.php';
?>
<div class="page-band"><span class="eyebrow">USER CENTER</span><h1>Notifications</h1><p>Updates about Achievements, contributions, and system activity.</p></div>
<div class="stack"><?php foreach($rows as $r): ?><article class="card notification-card"><span class="badge"><?=e(ucfirst($r['type']))?></span><h3><?=e($r['title'])?></h3><p><?=e($r['message'])?></p><small><?=e($r['created_at'])?></small></article><?php endforeach; ?><?php if(!$rows): ?><div class="empty"><h2>No Notifications yet.</h2><p>System updates will appear here.</p></div><?php endif; ?></div>
<?php include 'partials/footer.php'; ?>
<?php
/** Public learner dashboard. No account required; progress is tied to this browser session. */
require_once 'config/auth.php';
require_once 'config/db.php';
require_once 'config/csrf.php';
require_once 'config/public_profile.php';
$profileNotice = handle_public_profile_post($pdo);

$guestKey = public_player_key();

$pdo->prepare("INSERT INTO Public_players(guest_key,player_name) VALUES(?,?) ON DUPLICATE KEY UPDATE player_name=VALUES(player_name),last_seen_at=CURRENT_TIMESTAMP")
    ->execute([$guestKey, public_player_name()]);

$g = $pdo->prepare('SELECT * FROM Public_players WHERE guest_key=?');
$g->execute([$guestKey]);
$profile = $g->fetch() ?: ['xp'=>0,'total_earned'=>0,'total_spent'=>0,'current_streak'=>0];

$q = $pdo->prepare('SELECT COUNT(*) FROM Public_learning_progress WHERE guest_key=?');
$q->execute([$guestKey]); $guestCompleted=(int)$q->fetchColumn();
$q = $pdo->prepare('SELECT COUNT(*) FROM Public_learning_path_progress WHERE guest_key=?');
$q->execute([$guestKey]); $pathActivitiesCompleted=(int)$q->fetchColumn();
$q = $pdo->prepare('SELECT COUNT(*) FROM Game_results WHERE user_id IS NULL AND guest_key=?');
$q->execute([$guestKey]); $games=(int)$q->fetchColumn();
$q = $pdo->prepare('SELECT COUNT(*) FROM Public_challenge_attempts WHERE guest_key=?');
$q->execute([$guestKey]); $challenges=(int)$q->fetchColumn();
$q = $pdo->prepare('SELECT COUNT(*) FROM Public_player_achievements pa WHERE pa.guest_key=?');
$q->execute([$guestKey]); $badges=(int)$q->fetchColumn();
$q = $pdo->prepare("SELECT a.title,a.description,a.icon,pa.earned_at FROM Public_player_achievements pa JOIN Achievements a ON a.id=pa.achievement_id WHERE pa.guest_key=? ORDER BY pa.earned_at DESC");
$q->execute([$guestKey]); $Achievements=$q->fetchAll();

$rows=$pdo->prepare('SELECT game_name,difficulty,score,total,points_earned,hints_used,played_at FROM Game_results WHERE user_id IS NULL AND guest_key=? ORDER BY played_at DESC LIMIT 20');
$rows->execute([$guestKey]); $gameRows=$rows->fetchAll();

$title='My Learning'; include 'partials/header.php';
?>
<div class="dashboard-head">
    <div><span class="eyebrow">PERSONAL LEARNING · NO ACCOUNT REQUIRED</span><h1>My Learning Dashboard</h1><p>Your learning progress is connected to your public player profile. Create or restore the profile in Games to use it on another device.</p></div>
    <a class="btn small" href="games/index.php">🎮 Play Games</a>
</div>
<?php if(!empty($profileNotice['message'])): ?><div class="alert success-box"><?=e($profileNotice['message'])?></div><?php endif; ?>
<?php if(!empty($profileNotice['error'])): ?><div class="alert">⚠ <?=e($profileNotice['error'])?></div><?php endif; ?>
<section class="card">
    <div class="section-heading-row"><div><span class="eyebrow">PLAYER PROFILE</span><h2><?=e(public_player_name())?></h2><p>To create a recovery code or restore a profile from another device, open the <a href="games/index.php">Games page</a>.</p></div></div>
</section>
<div class="stat-grid">
    <div class="stat"><b><?=e($profile['xp'])?></b><div>Knowledge XP</div></div>
    <div class="stat"><b><?=e($guestCompleted)?></b><div>Learning Items Completed</div></div>
    <div class="stat"><b><?=e($pathActivitiesCompleted)?></b><div>Learning Path Steps</div></div>
    <div class="stat"><b><?=e($games)?></b><div>Game Attempts</div></div>
    <div class="stat"><b><?=e($challenges)?></b><div>Daily Challenges</div></div>
    <div class="stat"><b><?=e($badges)?></b><div>Badges</div></div>
    <div class="stat"><b><?=e($profile['current_streak'])?></b><div>Challenge Streak</div></div>
</div>
<section class="card">
    <h2>📈 Learning Status</h2>
    <div class="progress-track"><span style="width:<?=min(100,(int)$guestCompleted*10)?>%"></span></div>
    <p class="muted">Complete lessons, tutorials, daily challenges, interactive activities, and games to build your learning record.</p>
    <div class="actions"><a class="btn small" href="learning.php">📚 Learning Materials</a><a class="btn small secondary" href="tutorials.php">🎬 Tutorials</a><a class="btn small secondary" href="interactive.php">✨ Learning Activities</a><a class="btn small secondary" href="challenge.php">🎯 Daily Challenge</a></div>
</section>
<section class="card"><h2>🏅 Achievements</h2><?php if(!$Achievements): ?><div class="empty compact">No badges yet. Complete games and learning activities.</div><?php else: ?><div class="achievement-grid"><?php foreach($Achievements as $a): ?><div class="achievement-card"><div class="achievement-icon"><?=e($a['icon'])?></div><div><b><?=e($a['title'])?></b><p><?=e($a['description'])?></p><small><?=e($a['earned_at'])?></small></div></div><?php endforeach; ?></div><?php endif; ?></section>
<section class="card"><h2>🎮 Recent Game Records</h2><?php if(!$gameRows): ?><div class="empty compact">No public game records yet. Start a game to create your first record.</div><?php else: ?><div class="table-wrap"><table class="table"><thead><tr><th>Game</th><th>Level</th><th>Score</th><th>XP</th><th>Date</th></tr></thead><tbody><?php foreach($gameRows as $r): ?><tr><td><?=e($r['game_name'])?></td><td><?=e(ucfirst($r['difficulty']))?></td><td><?=e($r['score'])?> / <?=e($r['total'])?></td><td>+<?=e($r['points_earned'])?></td><td><?=e($r['played_at'])?></td></tr><?php endforeach; ?></tbody></table></div><?php endif; ?></section>
<?php include 'partials/footer.php'; ?>

<?php
/**
 * BAYANGAN ARCHIVE CODE GUIDE
 * File: index.php
 * Purpose: Main system page.
 * Edit this file when you need to change the feature handled by this page.
 * Search for the section comments below before changing database queries,
 * form fields, access rules, or page output.
 */

require_once 'config/db.php';
require_once 'config/auth.php';
$Pots=(int)$pdo->query("SELECT COUNT(*) FROM Pots WHERE status='published'")->fetchColumn();
$learn=(int)$pdo->query("SELECT COUNT(*) FROM Learning_materials WHERE status='published'")->fetchColumn();
$Tutorials=(int)$pdo->query("SELECT COUNT(*) FROM Tutorials WHERE status='published'")->fetchColumn();
$terms=(int)$pdo->query("SELECT COUNT(*) FROM Traditional_terms WHERE status='published'")->fetchColumn();
$quests=(int)$pdo->query("SELECT COUNT(*) FROM Heritage_quests WHERE status='published'")->fetchColumn();
$title='Home';
include 'partials/header.php';
?>
<section class="hero">
    <div class="hero-copy">
        <span class="eyebrow">● Digital Cultural Heritage · Lezo, Aklan</span>
        <h1>Keep the craft alive. <em style="color:#e3a77e">One story at a time.</em>
        </h1>
        <p>Bayangan Archive is a digital knowledge management system for documenting, organizing, learning, and sharing traditional pot-making practices.</p>
        <div class="actions">
            <a class="btn light" href="archive.php">🏺 Explore Collection</a>
            <a class="btn" href="learning.php">📚 Start Learning</a>
            <a class="btn secondary" href="interactive.php">✨ Learning Activities</a>
        </div>
        <div class="hero-links">
            <a href="archive.php">Pottery Collection</a>
            <a href="learning.php">Learning Materials</a>
            <a href="tutorials.php">Tutorial Library</a>
            <a href="games/index.php">Interactive Games</a>
            <a href="interactive.php">Learning Activities</a>
        </div>
    </div>
    <div class="hero-art">
        <div class="pot-scene">
            <div class="sun">
            </div>
            <div class="big-pot">
                <i class="pot-line">
                </i>
                <i class="pot-line two">
                </i>
            </div>
        </div>
        <div class="hero-stat">
            <b><?=$Pots?></b> published pottery records<br>
            <small><?=$learn?> learning topics · <?=$Tutorials?> Tutorials · <?=$terms?> local terms · <?=$quests?> quest missions</small>
        </div>
    </div>
</section>
<div class="section-head">
    <div>
        <span class="eyebrow">Explore</span>
        <h2>Everything in one place</h2>
        <p>Designed around your flowchart: archive → learn → play → progress.</p>
    </div>
</div>
<div class="feature-grid">
    <a class="feature-card" href="archive.php">
        <div class="feature-icon">🏺</div>
        <h3>Pottery Collection</h3>
        <p>Browse documented Pots with descriptions, uses, materials, cultural significance, photos, videos, and local maker information.</p>
        <span class="badge">Browse Collection →</span>
    </a>
    <a class="feature-card" href="learning.php">
        <div class="feature-icon">📖</div>
        <h3>Pot-Making Practices</h3>
        <p>Follow the learning sequence from materials and clay preparation through shaping, drying, firing, and cultural value.</p>
        <span class="badge">Learn the Process →</span>
    </a>
    <a class="feature-card" href="tutorials.php">
        <div class="feature-icon">🎬</div>
        <h3>Tutorial Library</h3>
        <p>Find tutorial videos and supporting information in one dedicated page for Users who want to learn the pot-making process.</p>
        <span class="badge">Watch Tutorials →</span>
    </a>
    <a class="feature-card" href="games/index.php">
        <div class="feature-icon">🎮</div>
        <h3>Interactive Games</h3>
        <p>Test knowledge with Pot Identification, Pottery Quiz, Matching, and Arrange-the-Steps activities.</p>
        <span class="badge">Play & Learn →</span>
    </a>
</div>
<div class="section-head">
    <div>
        <span class="eyebrow">Knowledge Management</span>
        <h2>Open to Everyone</h2>
    </div>
</div>
<div class="grid"><div class="card"><span class="badge">PUBLIC ACCESS</span><h3>Explore without an account</h3><p>Visitors, students, researchers, and pot makers can browse, learn, discuss, ask questions, play games, and contribute knowledge without logging in.</p></div><div class="card"><span class="badge">INTERACTION</span><h3>Talk and share</h3><p>Comment on pottery records, reply to other visitors, ask questions, and share additional knowledge with the community.</p></div><div class="card"><span class="badge">DISCOVERY</span><h3>Find the people and places</h3><p>Explore artisan profiles and the Map of Bayangan to connect pottery records with people and documented locations.</p></div><div class="card"><span class="badge">LEARN & PLAY</span><h3>Challenge yourself</h3><p>Use Tutorials, learning materials, daily challenges, and continuous games including the new sliding Jigsaw Puzzle.</p></div><div class="card"><span class="badge">COMMUNITY</span><h3>🏆 Leaderboard</h3><p>Play under a nickname and compare community game performance through the public leaderboard.</p><a class="btn small" href="leaderboard.php">View Leaderboard →</a></div><div class="card"><span class="badge">ADMIN</span><h3>🔐 Controlled management</h3><p>Only administrators log in to publish documentation, manage games, answer questions, review contributions, and maintain the archive.</p></div></div>
<div class="section-head"><div><span class="eyebrow">LIVE COMMUNITY</span><h2>What's Happening</h2><p>Recent public activity from the knowledge community.</p></div></div>
<section class="card activity-feed"><div class="feed-grid">
<?php $feed=$pdo->query("SELECT 'pot' type,p.name title,p.created_at dt FROM Pots p WHERE p.status='published' UNION ALL SELECT 'comment',CONCAT('A discussion was started on ',p.name),c.created_at FROM Pot_comments c JOIN Pots p ON p.id=c.pot_id WHERE p.status='published' ORDER BY dt DESC LIMIT 8")->fetchAll(); ?>
<?php foreach($feed as $f): ?><div class="feed-item"><span class="feed-icon"><?= $f['type']==='pot'?'🏺':'💬' ?></span><div><b><?=e($f['title'])?></b><small><?=e(date('M d, Y h:i A',strtotime($f['dt'])))?></small></div></div><?php endforeach; if(!$feed): ?><div class="empty compact"><p>No recent public activity yet.</p></div><?php endif; ?>
</div></section>
<div class="section-head"><div><span class="eyebrow">Featured Today</span><h2>Pot of the Day</h2></div></div>
<?php $daily=$pdo->query("SELECT p.*,m.local_name FROM Pots p LEFT JOIN Archive_metadata m ON m.pot_id=p.id WHERE p.status='published' ORDER BY p.id")->fetchAll(); $dailyPot=$daily? $daily[crc32(date('Y-m-d'))%count($daily)]:null; ?>
<?php if($dailyPot): ?><section class="card daily-pot"><div><span class="badge">🏺 POT OF THE DAY</span><h2><?=e($dailyPot['name'])?></h2><?php if($dailyPot['local_name']): ?><p><b>Local name:</b> <?=e($dailyPot['local_name'])?></p><?php endif; ?><p><?=e(mb_strimwidth($dailyPot['description'],0,320,'…'))?></p><div class="actions"><a class="btn small" href="pot.php?id=<?=$dailyPot['id']?>">Explore Pot →</a><a class="btn small secondary" href="challenge.php">Take Daily Challenge</a></div></div><?php if($dailyPot['image_path']): ?><img src="<?=e(media_url($dailyPot['image_path']))?>" alt="<?=e($dailyPot['name'])?>"><?php endif; ?></section><?php endif; ?><?php
include 'partials/footer.php';
?>

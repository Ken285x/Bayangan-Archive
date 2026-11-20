<?php
/**
 * BAYANGAN ARCHIVE CODE GUIDE
 * File: tutorials.php
 * Purpose: Dedicated public page for tutorial videos and tutorial data.
 * Edit this file when you need to change the tutorial listing, search,
 * category filter, or tutorial viewer layout.
 */

// Load the database connection and shared authentication helpers.
require_once 'config/db.php';
require_once 'config/auth.php';
require_once 'config/csrf.php';

// Read the optional search/category filters from the URL.
$search = trim($_GET['q'] ?? '');
$category = trim($_GET['category'] ?? '');

// Build the tutorial query using prepared statements for safe searching.
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='complete_tutorial' && hash_equals(csrf_token(),$_POST['csrf_token']??'')){ $tid=(int)($_POST['tutorial_id']??0); if($tid>0){ if(logged_in()) $pdo->prepare('INSERT IGNORE INTO Learning_progress(user_id,tutorial_id) VALUES(?,?)')->execute([user()['id'],$tid]); else { $key=public_player_key(); $pdo->prepare("INSERT INTO Public_players(guest_key,player_name) VALUES(?,?) ON DUPLICATE KEY UPDATE last_seen_at=CURRENT_TIMESTAMP")->execute([$key,public_player_name()]); $pdo->prepare('INSERT IGNORE INTO Public_learning_progress(guest_key,tutorial_id) VALUES(?,?)')->execute([$key,$tid]); } } }
$sql = "SELECT * FROM Tutorials WHERE status='published'";
$params = [];
if ($search !== '') {
    $sql .= " AND (title LIKE ? OR description LIKE ? OR content LIKE ? OR category LIKE ?)";
    $term = '%' . $search . '%';
    $params = [$term, $term, $term, $term];
}
if ($category !== '') {
    $sql .= " AND category = ?";
    $params[] = $category;
}
$sql .= " ORDER BY sort_order ASC, id DESC";

// Retrieve the published Tutorials that match the user's filters.
$st = $pdo->prepare($sql);
$st->execute($params);
$Tutorials = $st->fetchAll();

// Retrieve categories for the tutorial filter dropdown.
$categories = $pdo->query("SELECT DISTINCT category FROM Tutorials WHERE status='published' AND category<>'' ORDER BY category")->fetchAll(PDO::FETCH_COLUMN);
$groupedTutorials = [];
foreach ($Tutorials as $tutorialRecord) { $groupedTutorials[$tutorialRecord['category'] ?: 'Other Tutorial'][] = $tutorialRecord; }
$visibleCategories = array_keys($groupedTutorials);

$title = 'Tutorials';
include 'partials/header.php';
?>

<!-- Dedicated tutorial page header. -->
<div class="page-band tutorial-hero">
    <span class="eyebrow">03 · Tutorial Library</span>
    <h1>Pot-Making Tutorials</h1>
    <p>Looking specifically for Tutorials? This page gathers the published tutorial videos and supporting information in one place.</p>
</div>

<!-- Search and category controls for finding a specific tutorial quickly. -->
<form class="tutorial-filters card" method="get">
    <div class="field">
        <label for="tutorial-search">Search Tutorials</label>
        <input class="input" id="tutorial-search" name="q" value="<?=e($search)?>" placeholder="Search title, topic, or description...">
    </div>
    <div class="field">
        <label for="tutorial-category">Category</label>
        <select class="select" id="tutorial-category" name="category">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?=e($cat)?>" <?= $category === $cat ? 'selected' : '' ?>><?=e($cat)?></option>
            <?php endforeach; ?>
        </select>
    </div>
    <div class="actions" style="align-self:end">
        <button class="btn" type="submit">🔎 Find Tutorial</button>
        <?php if ($search !== '' || $category !== ''): ?>
            <a class="btn secondary" href="tutorials.php">Clear</a>
        <?php endif; ?>
    </div>
</form>

<!-- Quick category links reduce scrolling and help visitors identify each video's purpose. -->
<?php if ($visibleCategories): ?>
<nav class="archive-category-nav tutorial-category-nav" aria-label="Jump to tutorial category">
    <a class="btn small secondary" href="#tutorial-results">All tutorial topics</a>
    <?php foreach ($visibleCategories as $categoryLink): ?><a class="btn small secondary" href="#tutorial-<?=e(substr(sha1($categoryLink),0,10))?>"><?=e($categoryLink)?></a><?php endforeach; ?>
</nav>
<?php endif; ?>
<!-- Display tutorials grouped by their purpose/category. -->
<div id="tutorial-results">
<?php if (!$Tutorials): ?>
    <div class="card empty-state">
        <div class="feature-icon">🎬</div>
        <h2>No Tutorials found</h2>
        <p>There are no published tutorials matching your search yet. Ask the Admin to add or publish tutorial content.</p>
    </div>
<?php else: ?>
    <?php foreach ($groupedTutorials as $tutorialGroup => $tutorialGroupItems): ?>
    <section class="pottery-category-section tutorial-category-section" id="tutorial-<?=e(substr(sha1($tutorialGroup),0,10))?>">
        <div class="pottery-category-heading">
            <div><span class="eyebrow">TUTORIAL TOPIC</span><h2><?=e($tutorialGroup)?></h2></div>
            <span class="badge"><?=count($tutorialGroupItems)?> tutorial<?=count($tutorialGroupItems)!==1?'s':''?></span>
        </div>
        <div class="tutorial-grid">
            <?php foreach ($tutorialGroupItems as $tutorial): ?>
            <article class="tutorial-card">
                <div class="tutorial-media">
                    <?php if (!empty($tutorial['video_path'])): ?>
                        <video data-single-player controls preload="metadata" poster="<?=e(media_url($tutorial['image_path'] ?? ''))?>">
                            <source src="<?=e(media_url($tutorial['video_path']))?>">
                            Your browser does not support HTML5 video.
                        </video>
                        <span class="tutorial-play-badge">▶ VIDEO TUTORIAL</span>
                    <?php elseif (!empty($tutorial['image_path'])): ?>
                        <img src="<?=e(media_url($tutorial['image_path']))?>" alt="<?=e($tutorial['title'])?>" loading="lazy">
                    <?php else: ?>
                        <div class="tutorial-placeholder">🎬</div>
                    <?php endif; ?>
                </div>
                <div class="tutorial-body">
                    <span class="badge"><?=e($tutorial['category'])?></span>
                    <h2><?=e($tutorial['title'])?></h2>
                    <?php if (!empty($tutorial['description'])): ?><p class="tutorial-description"><?=nl2br(e($tutorial['description']))?></p><?php endif; ?>
                    <?php if (!empty($tutorial['content'])): ?>
                        <details class="tutorial-details"><summary>📖 View Tutorial Information</summary><div><?=nl2br(e($tutorial['content']))?></div></details>
                    <?php endif; ?>
                    <form method="post" class="tutorial-complete-form"><?=csrf_field()?><input type="hidden" name="action" value="complete_tutorial"><input type="hidden" name="tutorial_id" value="<?=$tutorial['id']?>"><button class="btn small secondary" type="submit">✓ Mark Tutorial Complete</button></form>
                </div>
            </article>
            <?php endforeach; ?>
        </div>
    </section>
    <?php endforeach; ?>
<?php endif; ?>
</div>

<div class="notice tutorial-note">
    <b>Knowledge verification:</b> Tutorial content should be based on documented and verified local pot-making practices before publication by the Admin.
</div>

<?php
// Load the shared footer and global JavaScript.
include 'partials/footer.php';
?>

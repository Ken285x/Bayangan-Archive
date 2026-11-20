<?php
/**
 * BAYANGAN ARCHIVE CODE GUIDE
 * File: archive.php
 * Purpose: Public pottery archive with search, filters, responsive cards, and reliable detail links.
 */

require_once 'config/db.php';
require_once 'config/auth.php';
require_once 'config/schema_updates.php';
ba_ensure_pot_type_column($pdo);
ba_ensure_content_categories($pdo);
ba_ensure_artisan_submissions($pdo);
$q=trim($_GET['q']??''); $searchField=trim($_GET['field']??'all');
$material=trim($_GET['material']??'');
$use=trim($_GET['use']??'');
$maker=trim($_GET['maker']??'');
$media=trim($_GET['media']??'');
$potType=trim($_GET['type']??'');
$sql="SELECT p.*,m.local_name,m.verification_status FROM Pots p LEFT JOIN Archive_metadata m ON m.pot_id=p.id WHERE p.status='published'";
$args=[];
if ($q!==''){
    $searchColumns = [
        'name' => ['p.name'],
        'local_name' => ['m.local_name'],
        'materials' => ['p.materials'],
        'purpose' => ['p.uses_purpose'],
        'maker' => ['p.pot_maker'],
        'description' => ['p.description'],
        'significance' => ['p.cultural_significance'],
        'all' => ['p.name','p.pot_type','m.local_name','p.materials','p.uses_purpose','p.pot_maker','p.description','p.cultural_significance','p.where_to_buy','p.estimated_price','p.contact_info','p.availability_note']
    ];
    if (!isset($searchColumns[$searchField])) $searchField='all';
    $sql .= ' AND (' . implode(' OR ', array_map(static fn($column) => $column . ' LIKE ?', $searchColumns[$searchField])) . ')';
    $like="%$q%";
    $args=array_fill(0,count($searchColumns[$searchField]),$like);
}
if ($material!==''){ $sql.=' AND p.materials LIKE ?'; $args[]='%'.$material.'%'; }
if ($use!==''){ $sql.=' AND p.uses_purpose LIKE ?'; $args[]='%'.$use.'%'; }
if ($maker!==''){ $sql.=' AND p.pot_maker LIKE ?'; $args[]='%'.$maker.'%'; }
if ($potType!==''){ $sql.=' AND EXISTS (SELECT 1 FROM Pot_categories pc JOIN Pottery_categories c ON c.id=pc.category_id WHERE pc.pot_id=p.id AND c.name=?)'; $args[]=$potType; }
if ($media==='video'){ $sql.=" AND p.video_path IS NOT NULL AND p.video_path<>''"; }
if ($media==='image'){ $sql.=" AND p.image_path IS NOT NULL AND p.image_path<>''"; }
$sql.=" ORDER BY p.created_at DESC";
$st=$pdo->prepare($sql);
$st->execute($args);
$Pots=$st->fetchAll();
$potTypes = $pdo->query("SELECT DISTINCT c.name FROM Pottery_categories c JOIN Pot_categories pc ON pc.category_id=c.id JOIN Pots p ON p.id=pc.pot_id WHERE p.status='published' ORDER BY c.name")->fetchAll(PDO::FETCH_COLUMN);
$groupedPots = [];
foreach ($Pots as $potRecord) {
    $catSt=$pdo->prepare('SELECT c.name FROM Pot_categories pc JOIN Pottery_categories c ON c.id=pc.category_id WHERE pc.pot_id=? ORDER BY c.sort_order,c.name');
    $catSt->execute([(int)$potRecord['id']]);
    $assigned=$catSt->fetchAll(PDO::FETCH_COLUMN);
    if (!$assigned) $assigned=[$potRecord['pot_type'] ?: 'Other Pottery'];
    $potRecord['_category_names']=$assigned;
    foreach ($assigned as $categoryName) $groupedPots[$categoryName][]=$potRecord;
}
ksort($groupedPots,SORT_NATURAL|SORT_FLAG_CASE);
$title='Pottery Collection';
include 'partials/header.php';
?>
<div class="page-band archive-page-band">
    <div>
        <span class="eyebrow">01 · POTTERY COLLECTION</span>
        <h1>Explore the Pottery Collection</h1>
        <p>Explore published documentation of traditional pottery. Search by pot name, material, use, maker, local name, or cultural significance.</p>
    </div>
    <div class="archive-summary"><strong><?=count($Pots)?></strong><span>documented record<?=count($Pots)!==1?'s':''?></span></div>
</div>
<form class="filters archive-advanced-filters" method="get" style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
    <input class="input" style="flex:1 1 260px;min-width:220px" name="q" value="<?=e($q)?>" placeholder="Search pottery…" aria-label="Search keyword">
    <select class="select" name="field" aria-label="Search field" style="flex:0 1 210px">
        <option value="all" <?=$searchField==='all'?'selected':''?>>All Fields</option>
        <option value="name" <?=$searchField==='name'?'selected':''?>>Pot Name</option>
        <option value="local_name" <?=$searchField==='local_name'?'selected':''?>>Local Name</option>
        <option value="materials" <?=$searchField==='materials'?'selected':''?>>Material</option>
        <option value="purpose" <?=$searchField==='purpose'?'selected':''?>>Purpose</option>
        <option value="maker" <?=$searchField==='maker'?'selected':''?>>Pot Maker</option>
        <option value="description" <?=$searchField==='description'?'selected':''?>>Description</option>
        <option value="significance" <?=$searchField==='significance'?'selected':''?>>Cultural Significance</option>
    </select>
    <button class="btn" type="submit">🔎 Search</button>
    <select class="select" name="type" aria-label="Pottery category"><option value="">All pottery types</option><?php foreach ($potTypes as $typeOption): ?><option value="<?=e($typeOption)?>" <?=$potType===$typeOption?'selected':''?>><?=e($typeOption)?></option><?php endforeach; ?></select>
    <select class="select" name="media" aria-label="Media filter"><option value="">All media</option><option value="image" <?= $media==='image'?'selected':'' ?>>With image</option><option value="video" <?= $media==='video'?'selected':'' ?>>With video</option></select>
    <?php if ($q!==''||$media!==''||$potType!==''): ?><a class="btn secondary" href="archive.php">Clear</a><?php endif; ?>
</form>
<p class="muted" style="margin-top:8px"><a href="submit_pottery.php">＋ Submit pottery as an artisan</a></p>
<?php if (!$Pots): ?>
<div class="empty"><h2>No records found</h2><p>Try another keyword or clear the search.</p></div>
<?php else: ?>
<nav class="archive-category-nav" aria-label="Jump to pottery category">
    <a class="btn small <?= $potType===''?'':'secondary' ?>" href="archive.php">All types</a>
    <?php foreach ($potTypes as $typeNav): ?><a class="btn small secondary" href="archive.php?type=<?=rawurlencode($typeNav)?>"><?=e($typeNav)?></a><?php endforeach; ?>
</nav>
<?php foreach ($groupedPots as $groupName => $groupPots): ?>
<section class="pottery-category-section" id="type-<?=e(substr(sha1($groupName),0,10))?>">
    <div class="pottery-category-heading"><div><span class="eyebrow">POTTERY TYPE</span><h2><?=e($groupName)?></h2></div><span class="badge"><?=count($groupPots)?> record<?=count($groupPots)!==1?'s':''?></span></div>
    <div class="grid archive-grid">
<?php foreach ($groupPots as $p): ?>
<article class="card archive-card">
    <div class="archive-cover">
<?php if (!empty($p['image_path'])): ?>
        <img src="<?=e(media_url($p['image_path']))?>" alt="<?=e($p['name'])?>" loading="lazy" onerror="this.style.display='none';this.nextElementSibling.hidden=false">
        <div class="archive-image-fallback" hidden><span>🏺</span><small>Image unavailable</small></div>
<?php else: ?>
        <div class="archive-image-fallback"><span>🏺</span><small>No photo uploaded</small></div>
<?php endif; ?>
        <span class="archive-media-badge"><?=!empty($p['video_path'])?'🎬 Video':'📷 Photo'?></span>
    </div>
    <div class="archive-body">
        <div class="archive-body-top">
            <span class="badge"><?=e(implode(' · ', $p['_category_names'] ?? [$p['pot_type'] ?? 'Other Pottery']))?></span>
<button class="icon-btn" type="button" title="Save this pot to My Collection" aria-label="Save <?=e($p['name'])?> to My Collection" data-save-pot="<?=$p['id']?>">♡</button>
        </div>
        <h3><?=e($p['name'])?></h3>
<?php if (!empty($p['local_name'])): ?><div class="archive-local-name">Local name: <b><?=e($p['local_name'])?></b></div><?php endif; ?>
        <p class="archive-description"><?=e($p['description'])?></p>
        <p class="archive-use"><b>Uses:</b> <?=e($p['uses_purpose']?:'Not specified')?></p>
        <div class="archive-actions"><a class="btn small archive-detail-btn" href="pot.php?id=<?=$p['id']?>">View Details <span>→</span></a><a class="btn small secondary" href="community.php?pot_id=<?=$p['id']?>">Discuss</a></div>
    </div>
</article>
<?php endforeach; ?>
    </div>
</section>
<?php endforeach; ?>
<?php endif; ?>
<?php include 'partials/footer.php'; ?>

<?php
/** Global knowledge search across pottery, learning, tutorials, terminology, and process. */
require_once 'config/db.php'; require_once 'config/auth.php';
$q=trim($_GET['q']??''); $type=trim($_GET['type']??'all');
$results=[];
if($q!==''){
    $like='%'.$q.'%';
    if($type==='all'||$type==='pottery'){
        $st=$pdo->prepare("SELECT id,name,description,image_path,pot_maker,'Pottery' type FROM Pots WHERE status='published' AND (name LIKE ? OR description LIKE ? OR materials LIKE ? OR pot_maker LIKE ? OR uses_purpose LIKE ? OR cultural_significance LIKE ?) ORDER BY id DESC LIMIT 30");
        $st->execute([$like,$like,$like,$like,$like,$like]); foreach($st->fetchAll() as $r){$r['url']='pot.php?id='.$r['id'];$results[]=$r;}
    }
    if($type==='all'||$type==='learning'){
        $st=$pdo->prepare("SELECT id,title,content,image_path,'Learning Material' type FROM Learning_materials WHERE status='published' AND (title LIKE ? OR content LIKE ? OR category LIKE ?) ORDER BY id DESC LIMIT 20");$st->execute([$like,$like,$like]); foreach($st->fetchAll() as $r){$r['name']=$r['title'];$r['description']=$r['content'];$r['url']='learning.php';$results[]=$r;}
    }
    if($type==='all'||$type==='tutorial'){
        $st=$pdo->prepare("SELECT id,title,description,image_path,'Tutorial' type FROM Tutorials WHERE status='published' AND (title LIKE ? OR description LIKE ? OR content LIKE ? OR category LIKE ?) ORDER BY id DESC LIMIT 20");$st->execute([$like,$like,$like,$like]); foreach($st->fetchAll() as $r){$r['name']=$r['title'];$r['url']='tutorials.php?q='.urlencode($q);$results[]=$r;}
    }
    if($type==='all'||$type==='term'){
        $st=$pdo->prepare("SELECT id,term,meaning,english_meaning,filipino_meaning,audio_path,'Traditional Term' type FROM Traditional_terms WHERE status='published' AND (term LIKE ? OR meaning LIKE ? OR english_meaning LIKE ? OR filipino_meaning LIKE ? OR category LIKE ?) ORDER BY sort_order,term LIMIT 20");$st->execute([$like,$like,$like,$like,$like]); foreach($st->fetchAll() as $r){$r['name']=$r['term'];$r['description']=$r['meaning'];$r['image_path']=null;$r['url']='terminology.php?q='.urlencode($q);$results[]=$r;}
    }
    if($type==='all'||$type==='process'){
        $st=$pdo->prepare("SELECT id,title,detail AS description,image_path,'Process Stage' type FROM Interactive_steps WHERE status='published' AND (title LIKE ? OR detail LIKE ? OR short_description LIKE ?) ORDER BY sort_order,id LIMIT 20");$st->execute([$like,$like,$like]); foreach($st->fetchAll() as $r){$r['name']=$r['title'];$r['url']='interactive.php#process';$results[]=$r;}
    }

}
$title='Global Search'; include 'partials/header.php';
?>
<div class="page-band"><span class="eyebrow">KNOWLEDGE RETRIEVAL</span><h1>🔎 Global Search</h1><p>Search across pottery records, learning materials, tutorials, traditional terminology, and process stages.</p></div>
<form class="card form-grid compact-grid" method="get"><div class="field full"><label for="global-q">Search</label><input class="input" id="global-q" name="q" value="<?=e($q)?>" autofocus placeholder="Search pot, material, maker, local term, technique, tutorial…"></div><div class="field"><label>Content Type</label><select class="select" name="type"><option value="all" <?=$type==='all'?'selected':''?>>Everything</option><option value="pottery" <?=$type==='pottery'?'selected':''?>>Pottery</option><option value="learning" <?=$type==='learning'?'selected':''?>>Learning</option><option value="tutorial" <?=$type==='tutorial'?'selected':''?>>Tutorials</option><option value="term" <?=$type==='term'?'selected':''?>>Terminology</option><option value="process" <?=$type==='process'?'selected':''?>>Process</option></select></div><div class="actions" style="align-self:end"><button class="btn small">🔎 Search</button><a class="btn small secondary" href="search.php">Clear</a></div></form>
<?php if($q===''): ?><div class="empty"><h2>Search the knowledge base</h2><p>Try keywords such as a pottery name, clay, firing, a pot maker, or a documented local term.</p></div><?php else: ?>
<div class="section-kicker">RESULTS</div><h2><?=count($results)?> result<?=count($results)!==1?'s':''?> for “<?=e($q)?>”</h2>
<div class="archive-grid">
<?php foreach($results as $r): ?><article class="pot-card"><a href="<?=e($r['url'])?>" class="pot-card-media"><?php if(!empty($r['image_path'])):?><img src="<?=e(media_url($r['image_path']))?>" alt="<?=e($r['name'])?>"><?php else:?><div class="media-placeholder">🏺</div><?php endif;?></a><div class="pot-card-body"><span class="badge"><?=e($r['type'])?></span><h3><?=e($r['name'])?></h3><p><?=e(mb_strimwidth(strip_tags((string)($r['description']??'')),0,180,'…'))?></p><a class="btn small" href="<?=e($r['url'])?>">Open →</a></div></article><?php endforeach; ?>
</div>
<?php if(!$results): ?><div class="empty"><h2>No matching knowledge found</h2><p>Try another keyword or choose Everything.</p></div><?php endif; ?>
<?php endif; ?>
<?php include 'partials/footer.php'; ?>

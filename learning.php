<?php
/**
 * BAYANGAN ARCHIVE CODE GUIDE
 * File: learning.php
 * Purpose: Main system page.
 * Edit this file when you need to change the feature handled by this page.
 * Search for the section comments below before changing database queries,
 * form fields, access rules, or page output.
 */

require_once 'config/db.php';
require_once 'config/auth.php';
require_once 'config/csrf.php';
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='complete_lesson' && hash_equals(csrf_token(),$_POST['csrf_token']??'')){
    $mid=(int)($_POST['material_id']??0);
    if($mid>0){
        if(logged_in()) $pdo->prepare('INSERT IGNORE INTO Learning_progress(user_id,material_id) VALUES(?,?)')->execute([user()['id'],$mid]);
        else { $key=public_player_key(); $pdo->prepare("INSERT INTO Public_players(guest_key,player_name) VALUES(?,?) ON DUPLICATE KEY UPDATE last_seen_at=CURRENT_TIMESTAMP")->execute([$key,public_player_name()]); $pdo->prepare('INSERT IGNORE INTO Public_learning_progress(guest_key,material_id) VALUES(?,?)')->execute([$key,$mid]); }
    }
}
$st=$pdo->query("SELECT * FROM Learning_materials WHERE status='published' ORDER BY sort_order,id");
$items=$st->fetchAll();
$title='Learning Materials';
include 'partials/header.php';
?>
<div class="page-band">
    <span class="eyebrow">02 · Traditional Pot-Making Practices</span>
    <h1>Learn the Process</h1>
    <p>Work through the documented learning materials in sequence. Multimedia can be added by administrators as verified field documentation becomes available.</p>
</div>
<div class="steps">
<?php
foreach ($items as $i=>$x):
?>
<article class="step" data-step="<?=($i+1)?>">
    <span class="badge"><?=e($x['category'])?></span>
    <h2><?=e($x['title'])?></h2>
    <p><?=nl2br(e($x['content']))?></p>
<?php
if (!empty($x['image_path'])):
?>
<img class="media" style="max-height:360px;margin:10px 0" src="<?=e(media_url($x['image_path']))?>" alt="<?=e($x['title'])?>">
<?php
endif;
?>
<?php
if (!empty($x['video_path'])):
?>
<video class="media" controls src="<?=e(media_url($x['video_path']))?>">
</video>
<?php
endif;
?>
<form method="post" class="lesson-complete-form"><?=csrf_field()?><input type="hidden" name="action" value="complete_lesson"><input type="hidden" name="material_id" value="<?=$x['id']?>"><button class="btn small secondary" type="submit">✓ Mark as Completed</button></form></article>
<?php
endforeach;
?>
</div>
<br>
<div class="notice">
    <b>Verification reminder:</b> Content should be reviewed and verified by authorized Users/artisans before publication. The system is designed to preserve documented local knowledge rather than replace the artisan's account.</div>
<?php
include 'partials/footer.php';
?>

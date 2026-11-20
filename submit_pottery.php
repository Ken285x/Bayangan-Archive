<?php
require_once 'config/db.php';
require_once 'config/auth.php';
require_once 'config/csrf.php';
require_once 'config/upload.php';
require_once 'config/activity.php';
require_once 'config/schema_updates.php';
ba_ensure_content_categories($pdo);
ba_ensure_artisan_submissions($pdo);
$error=''; $success=false;
$categories=$pdo->query('SELECT id,name FROM Pottery_categories ORDER BY sort_order,name')->fetchAll(PDO::FETCH_ASSOC);
if ($_SERVER['REQUEST_METHOD']==='POST') {
    try {
        verify_csrf();
        if (!empty($_POST['website'] ?? '')) throw new RuntimeException('Submission could not be accepted.');
        $name=trim($_POST['name']??''); $description=trim($_POST['description']??'');
        $artisan=trim($_POST['artisan_name']??'');
        $categoryId=(int)($_POST['category_id']??0);
        if ($name==='' || $description==='' || $artisan==='') throw new RuntimeException('Please complete artisan name, pot name, and description.');
        $cat=$pdo->prepare('SELECT name FROM Pottery_categories WHERE id=?'); $cat->execute([$categoryId]); $potType=$cat->fetchColumn();
        if (!$potType) throw new RuntimeException('Please select a valid pottery category.');
        $email=trim($_POST['artisan_email']??'');
        if ($email!=='' && !filter_var($email,FILTER_VALIDATE_EMAIL)) throw new RuntimeException('Please enter a valid email address or leave it blank.');
        $image=save_upload('image',['jpg','jpeg','png','webp'],10000000);
        $video=save_upload('video',['mp4','webm','mov'],50000000);
        $date=trim($_POST['documented_date']??'');
        $pdo->prepare("INSERT INTO Artisan_pottery_submissions
        (artisan_name,artisan_email,name,pot_type,local_name,description,uses_purpose,materials,cultural_significance,pot_maker,where_to_buy,estimated_price,contact_info,availability_note,location,documented_date,source_type,source_name,documented_by,notes,image_path,video_path)
        VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)")->execute([
            $artisan,$email?:null,$name,$potType,trim($_POST['local_name']??''),$description,
            trim($_POST['uses_purpose']??''),trim($_POST['materials']??''),trim($_POST['cultural_significance']??''),
            trim($_POST['pot_maker']??'')?:$artisan,trim($_POST['where_to_buy']??''),trim($_POST['estimated_price']??''),
            trim($_POST['contact_info']??''),trim($_POST['availability_note']??''),trim($_POST['location']??''),
            $date?:null,trim($_POST['source_type']??''),trim($_POST['source_name']??'')?:$artisan,
            trim($_POST['documented_by']??''),trim($_POST['notes']??''),$image,$video
        ]);
        $sid=(int)$pdo->lastInsertId();
        notify_admins($pdo,'New pottery submission','A new pottery submission “'.$name.'” from '.$artisan.' is waiting for review.','pottery_submission');
        $success=true;
    } catch(Throwable $e) { $error=$e->getMessage(); }
}
$title='Submit Pottery';
include 'partials/header.php';
?>
<div class="page-band"><span class="eyebrow">COMMUNITY CONTRIBUTION</span><h1>Submit Pottery</h1><p>Artisans may share pottery information without creating an account. Submissions are reviewed by the Admin before appearing in the public collection.</p></div>
<?php if($success): ?>
<div class="alert success-box"><h2>Submission received</h2><p>Thank you. Your pottery entry is now <b>Pending</b> and will appear in the Pottery Collection only after Admin approval.</p><a class="btn" href="archive.php">Return to Pottery Collection</a></div>
<?php else: ?>
<?php if($error): ?><div class="alert">⚠ <?=e($error)?></div><?php endif; ?>
<form method="post" enctype="multipart/form-data" class="card form-grid"><?=csrf_field()?>
<div class="field"><label>Artisan Name *</label><input class="input" name="artisan_name" required maxlength="160" value="<?=e($_POST['artisan_name']??'')?>"></div>
<div class="field"><label>Email (optional)</label><input class="input" type="email" name="artisan_email" value="<?=e($_POST['artisan_email']??'')?>"></div>
<div class="field"><label>Pot Name *</label><input class="input" name="name" required maxlength="160" value="<?=e($_POST['name']??'')?>"></div>
<div class="field"><label>Pottery Category *</label><select class="select" name="category_id" required><option value="">Select category</option><?php foreach($categories as $c): ?><option value="<?=$c['id']?>" <?=((int)($_POST['category_id']??0)===(int)$c['id'])?'selected':''?>><?=e($c['name'])?></option><?php endforeach; ?></select></div>
<div class="field"><label>Local Name</label><input class="input" name="local_name" value="<?=e($_POST['local_name']??'')?>"></div>
<div class="field"><label>Pot Maker / Artisan</label><input class="input" name="pot_maker" value="<?=e($_POST['pot_maker']??'')?>"></div>
<div class="field full"><label>Description *</label><textarea name="description" rows="4" required><?=e($_POST['description']??'')?></textarea></div>
<div class="field"><label>Materials</label><textarea name="materials" rows="3"><?=e($_POST['materials']??'')?></textarea></div>
<div class="field"><label>Purpose / Uses</label><textarea name="uses_purpose" rows="3"><?=e($_POST['uses_purpose']??'')?></textarea></div>
<div class="field full"><label>Cultural Significance</label><textarea name="cultural_significance" rows="4"><?=e($_POST['cultural_significance']??'')?></textarea></div>
<div class="field"><label>Where to Buy (optional)</label><input class="input" name="where_to_buy" value="<?=e($_POST['where_to_buy']??'')?>"></div>
<div class="field"><label>Estimated Price (optional)</label><input class="input" name="estimated_price" value="<?=e($_POST['estimated_price']??'')?>"></div>
<div class="field"><label>Contact Number (optional)</label><input class="input" type="tel" name="contact_info" value="<?=e($_POST['contact_info']??'')?>"></div>
<div class="field"><label>Availability</label><input class="input" name="availability_note" value="<?=e($_POST['availability_note']??'')?>"></div>
<div class="field"><label>Location</label><input class="input" name="location" value="<?=e($_POST['location']??'')?>" placeholder="Bayangan, Lezo, Aklan"></div>
<div class="field"><label>Date documented</label><input class="input" type="date" name="documented_date" value="<?=e($_POST['documented_date']??'')?>"></div>
<div class="field"><label>Source Type</label><input class="input" name="source_type" value="<?=e($_POST['source_type']??'')?>" placeholder="Interview, observation, etc."></div>
<div class="field"><label>Source / Informant</label><input class="input" name="source_name" value="<?=e($_POST['source_name']??'')?>"></div>
<div class="field"><label>Documented By</label><input class="input" name="documented_by" value="<?=e($_POST['documented_by']??'')?>"></div>
<div class="field full"><label>Additional Notes</label><textarea name="notes" rows="3"><?=e($_POST['notes']??'')?></textarea></div>
<div class="field"><label>Pottery Photo</label><input class="input" type="file" name="image" accept=".jpg,.jpeg,.png,.webp"><small>JPG, PNG, WEBP · up to 10 MB</small></div>
<div class="field"><label>Video (optional)</label><input class="input" type="file" name="video" accept=".mp4,.webm,.mov"><small>MP4, WEBM, MOV · up to 50 MB</small></div>
<div class="field" style="position:absolute;left:-10000px" aria-hidden="true"><label>Website</label><input name="website" tabindex="-1" autocomplete="off"></div>
<div class="field full"><label style="display:flex;gap:8px;align-items:flex-start"><input type="checkbox" required> I confirm that the information and media may be reviewed and that I have permission to share any contact details provided.</label></div>
<div class="actions"><button class="btn" type="submit">Submit for Admin Approval</button><a class="btn secondary" href="archive.php">Cancel</a></div>
</form>
<?php endif; ?>
<?php include 'partials/footer.php'; ?>

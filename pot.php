<?php

require_once 'config/db.php';
require_once 'config/auth.php';
require_once 'config/csrf.php'; require_once 'config/activity.php';
require_once 'config/schema_updates.php';
ba_ensure_pot_type_column($pdo);
ba_ensure_content_categories($pdo);
$id=(int)($_GET['id']??0);
// Validate the published record before recording views or processing any public interaction.
$availabilityCheck = $pdo->prepare("SELECT id FROM Pots WHERE id=? AND status='published'");
$availabilityCheck->execute([$id]);
if (!$availabilityCheck->fetch()) {
    http_response_code(404);
    $title='Not Found';
    include 'partials/header.php';
    echo '<div class="empty"><h1>Pot not found</h1><p>This record may be unpublished, archived, or no longer available.</p><a class="btn" href="archive.php">Back to Archive</a></div>';
    include 'partials/footer.php';
    exit;
}
$comment_error='';
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='comment') {
    try { verify_csrf(); $name=trim($_POST['visitor_name']??''); $comment=trim($_POST['comment']??''); $parent=(int)($_POST['parent_id']??0);
        if(mb_strlen($name)<2 || mb_strlen($comment)<2) throw new RuntimeException('Please enter your name and a meaningful comment.');
        $check=$pdo->prepare("SELECT id FROM Pots WHERE id=? AND status='published'"); $check->execute([$id]); if(!$check->fetch()) throw new RuntimeException('Pottery record is unavailable.');
        if ($parent > 0) { $parentCheck=$pdo->prepare('SELECT id FROM Pot_comments WHERE id=? AND pot_id=?'); $parentCheck->execute([$parent,$id]); if(!$parentCheck->fetch()) throw new RuntimeException('The reply target is not part of this pottery discussion. Refresh and try again.'); }
        $pdo->prepare('INSERT INTO Pot_comments(pot_id,parent_id,visitor_name,comment) VALUES(?,?,?,?)')->execute([$id,$parent?:null,$name,$comment]);
        notify_admins($pdo, $parent > 0 ? 'New pottery reply' : 'New pottery comment', $name.' commented on '.$id.'.', 'community');
        header('Location: pot.php?id='.$id.'#discussion'); exit;
    } catch(Throwable $e){ $comment_error=$e->getMessage(); }
}
// Dynamic public engagement: count a view, accept ratings/reviews, and collect correction suggestions.
// Record this published pottery visit in the active public player's Heritage Quest progress.
// Merge with the existing saved state so other progress fields are preserved.
try {
    $progressKey = public_player_key();
    $progressQuery = $pdo->prepare('SELECT state_json FROM Public_interactive_progress WHERE guest_key=? LIMIT 1');
    $progressQuery->execute([$progressKey]);
    $progressRaw = $progressQuery->fetchColumn();
    $progressState = $progressRaw ? json_decode((string)$progressRaw, true) : [];
    if (!is_array($progressState)) $progressState = [];
    $viewedPots = isset($progressState['potsViewed']) && is_array($progressState['potsViewed']) ? $progressState['potsViewed'] : [];
    $viewedPots = array_values(array_unique(array_map('strval', $viewedPots)));
    if (!in_array((string)$id, $viewedPots, true)) $viewedPots[] = (string)$id;
    $progressState['potsViewed'] = array_slice($viewedPots, 0, 1000);
    $progressState['xp'] = max(0, (int)($progressState['xp'] ?? 0));
    $progressState['games'] = max(0, (int)($progressState['games'] ?? 0));
    $progressJson = json_encode($progressState, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    if ($progressJson !== false && strlen($progressJson) <= 100000) {
        $saveProgress = $pdo->prepare('INSERT INTO Public_interactive_progress(guest_key,state_json) VALUES(?,?) ON DUPLICATE KEY UPDATE state_json=VALUES(state_json),updated_at=CURRENT_TIMESTAMP');
        $saveProgress->execute([$progressKey, $progressJson]);
    }
} catch (Throwable $progressError) {
    // Do not interrupt pottery record viewing if optional quest-progress tracking fails.
}

$viewKey = substr(hash('sha256', ($_SERVER['REMOTE_ADDR'] ?? 'visitor').'|'.session_id()),0,64);
$pdo->prepare('INSERT INTO Pot_views(pot_id,visitor_key) VALUES(?,?)')->execute([$id,$viewKey]);
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='rating') {
    try { verify_csrf(); $name=trim($_POST['visitor_name']??''); $rating=(int)($_POST['rating']??0); $review=trim($_POST['review']??'');
        if(mb_strlen($name)<2 || $rating<1 || $rating>5) throw new RuntimeException('Please provide your name and choose a rating from 1 to 5.');
        $pdo->prepare('INSERT INTO Pot_ratings(pot_id,visitor_name,rating,review) VALUES(?,?,?,?)')->execute([$id,$name,$rating,$review?:null]);
        notify_admins($pdo, 'New pottery rating / review', $name.' submitted a '.$rating.'-star rating for pottery record #'.$id.($review!==''?' with a review.':'.'), 'community');
        header('Location: pot.php?id='.$id.'#reviews'); exit;
    } catch(Throwable $e){ $comment_error=$e->getMessage(); }
}
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='correction') {
    try { verify_csrf(); $name=trim($_POST['visitor_name']??''); $suggestion=trim($_POST['suggestion']??'');
        if(mb_strlen($name)<2 || mb_strlen($suggestion)<5) throw new RuntimeException('Please provide your name and a useful correction suggestion.');
        $pdo->prepare('INSERT INTO Pot_corrections(pot_id,visitor_name,suggestion) VALUES(?,?,?)')->execute([$id,$name,$suggestion]);
        notify_admins($pdo, 'New correction suggestion', $name.' suggested a correction for pottery record #'.$id.'.', 'community');
        header('Location: pot.php?id='.$id.'#correction'); exit;
    } catch(Throwable $e){ $comment_error=$e->getMessage(); }
}
if ($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='report_comment') {
    try { verify_csrf(); $name=trim($_POST['visitor_name']??''); $cid=(int)($_POST['comment_id']??0); $reason=trim($_POST['reason']??'Other'); if(mb_strlen($name)<2||!$cid) throw new RuntimeException('Please provide your name and the comment to report.'); $reportCheck=$pdo->prepare('SELECT id FROM Pot_comments WHERE id=? AND pot_id=?'); $reportCheck->execute([$cid,$id]); if(!$reportCheck->fetch()) throw new RuntimeException('That comment does not belong to this pottery discussion.'); $pdo->prepare('INSERT INTO Comment_reports(comment_id,visitor_name,reason,details) VALUES(?,?,?,?)')->execute([$cid,$name,$reason,trim($_POST['details']??'')?:null]);
        notify_admins($pdo, 'New comment report', $name.' reported a comment on pottery record #'.$id.'.', 'community'); header('Location: pot.php?id='.$id.'#discussion'); exit; } catch(Throwable $e){$comment_error=$e->getMessage();}
}
$st=$pdo->prepare("SELECT p.*,m.local_name,m.location,m.documented_date,m.source_type,m.source_name,m.verification_status,m.documented_by,m.notes FROM Pots p LEFT JOIN Archive_metadata m ON m.pot_id=p.id WHERE p.id=? AND p.status='published'");
$st->execute([$id]);
$p=$st->fetch();
if (!$p){
    http_response_code(404);
    $title='Not Found';
    include 'partials/header.php';
    echo '<div class="empty"><h1>Pot not found</h1><p>This record may be unpublished, archived, or no longer available.</p><a class="btn" href="archive.php">Back to Archive</a></div>';
    include 'partials/footer.php';
    exit;
}
// Only reviewed source records are shown publicly; internal review notes stay private.
$sourceStmt = $pdo->prepare("SELECT source_type,source_title,informant,documented_date,location,citation_text FROM Pot_sources WHERE pot_id=? AND verification_status='verified' ORDER BY documented_date DESC,id DESC");
$sourceStmt->execute([$id]);
$verifiedSources = $sourceStmt->fetchAll();
$title=$p['name'];
include 'partials/header.php';
?>
<div class="pot-detail-hero">
    <div class="pot-detail-hero-copy">
        <a class="back-link" href="archive.php">← Back to Archive</a>
        <span class="eyebrow">Pottery Archive · Published Record</span>
        <h1><?=e($p['name'])?></h1>
<?php if (!empty($p['local_name'])): ?><p class="pot-local-label">Local name: <strong><?=e($p['local_name'])?></strong></p><?php endif; ?>
        <p class="pot-lead"><?=nl2br(e($p['description']))?></p>
        <div class="pot-hero-actions">
            <span class="verification-pill <?=e($p['verification_status']??'pending')?>">● <?=e(ucfirst($p['verification_status']??'pending'))?></span>
<button class="btn small secondary" type="button" data-save-pot="<?=$p['id']?>">♡ Save to My Collection</button>
        </div>
    </div>
</div>

<div class="pot-detail-layout">
    <main>
        <section class="pot-media-card card">
            <div class="section-kicker">Visual Documentation</div>
            <div class="pot-main-image-wrap">
<?php if (!empty($p['image_path'])): ?>
                <img class="pot-main-image" src="<?=e(media_url($p['image_path']))?>" alt="<?=e($p['name'])?>">
<?php else: ?>
                <div class="pot-no-image"><span>🏺</span><b>No pottery photo was uploaded.</b><small>The rest of the record is still available below.</small></div>
<?php endif; ?>
            </div>
<?php if (!empty($p['video_path'])): ?>
            <div class="video-section">
                <div class="section-kicker">Documentation Video</div>
                <video class="pot-video" data-single-player controls preload="metadata" src="<?=e(media_url($p['video_path']))?>"></video>
            </div>
<?php endif; ?>
        </section>

        <section class="card seller-contact-card" id="seller-contact">
            <div class="section-kicker">Seller Information</div>
            <h2>Interested in this Pot?</h2>
            <p>Contact the pot maker directly to ask about this pottery. Bayangan Archive does not process reservations or payments.</p>
            <?php $contact=trim((string)($p['contact_info']??'')); $digits=preg_replace('/[^0-9+]/','',$contact); $hasPhone=$contact!=='' && preg_match('/^[+0-9() .-]{7,}$/',$contact); ?>
            <div class="seller-contact-summary">
                <div><span>Pot Maker</span><strong><?=e($p['pot_maker']?:'Artisan not specified')?></strong></div>
                <div><span>Contact Number</span><strong><?= $hasPhone ? e($contact) : 'Not provided' ?></strong></div>
                <div><span>Estimated Price</span><strong><?=e($p['estimated_price']?:'Contact seller for price')?></strong></div>
                <div><span>Where to Find</span><strong><?=e($p['where_to_buy']?:'Ask the seller')?></strong></div>
                <div><span>Location</span><strong><?=e($p['location']?:'Not specified')?></strong></div>
            </div>
            <div class="actions seller-contact-actions">
                <?php if($hasPhone): ?><a class="btn small" href="tel:<?=e($digits)?>">☎ Call Seller</a>
                <?php else: ?><p class="muted">A phone number is not available for this seller yet. Please check back later or contact the archive administrator.</p><?php endif; ?>
                <?php if($p['pot_maker']): ?><a class="btn small secondary" href="artisan_profile.php?name=<?=urlencode($p['pot_maker'])?>">🧑‍🎨 View Seller Profile</a><?php endif; ?>
            </div>
            <small class="muted">Contact details should be published only with the artisan's permission.</small>
        </section>

        <section class="card pot-information-card">
            <div class="section-kicker">Pottery Information</div>
            <h2>About This Pottery</h2>
            <div class="info-grid">
                <div class="info-item"><span>Pottery Categories</span><strong><?php $potCategorySt=$pdo->prepare('SELECT c.name FROM Pot_categories pc JOIN Pottery_categories c ON c.id=pc.category_id WHERE pc.pot_id=? ORDER BY c.sort_order,c.name'); $potCategorySt->execute([(int)$p['id']]); $potCategoryNames=$potCategorySt->fetchAll(PDO::FETCH_COLUMN); echo e(implode(', ', $potCategoryNames) ?: ($p['pot_type'] ?? 'Other Pottery')); ?></strong></div>
                <div class="info-item"><span>Uses / Purpose</span><strong><?=nl2br(e($p['uses_purpose']?:'Not specified'))?></strong></div>
                <div class="info-item"><span>Materials</span><strong><?=nl2br(e($p['materials']?:'Not specified'))?></strong></div>
                <div class="info-item info-item-wide"><span>History & Cultural Significance</span><strong><?=nl2br(e($p['cultural_significance']?:'Not specified'))?></strong></div>
                <div class="info-item"><span>Local Pot Maker / Artisan</span><strong><?=e($p['pot_maker']?:'Not specified')?></strong></div>
                <div class="info-item"><span>Local Name</span><strong><?=e($p['local_name']?:'Not specified')?></strong></div>
            </div>
        </section>

        <section class="card provenance-card pot-provenance-card">
            <div class="section-kicker">Documentation & Provenance</div>
            <h2>Record Details</h2>
            <div class="metadata-grid pot-metadata-grid">
                <div><b>Location</b><span><?=e($p['location']?:'Not specified')?></span></div>
                <div><b>Documented Date</b><span><?=e($p['documented_date']?:'Not specified')?></span></div>
                <div><b>Source Type</b><span><?=e($p['source_type']?:'Not specified')?></span></div>
                <div><b>Source / Informant</b><span><?=e($p['source_name']?:'Not specified')?></span></div>
                <div><b>Verification</b><span><?=e(ucfirst($p['verification_status']??'pending'))?></span></div>
                <div><b>Documented By</b><span><?=e($p['documented_by']?:'Not specified')?></span></div>
                <div><b>Publication Status</b><span>Published</span></div>
            </div>
<?php if (!empty($p['notes'])): ?>
            <div class="documentation-notes"><b>Documentation Notes</b><p><?=nl2br(e($p['notes']))?></p></div>
<?php endif; ?>
            <div class="verified-source-list">
                <h3>Verified Supporting Sources</h3>
                <?php if ($verifiedSources): ?>
                    <?php foreach ($verifiedSources as $source): ?>
                        <article class="verified-source-item">
                            <b><?=e($source['source_title'] ?: $source['source_type'])?></b>
                            <span><?=e($source['source_type'])?></span>
                            <?php if (!empty($source['informant'])): ?><small>Source / informant: <?=e($source['informant'])?></small><?php endif; ?>
                            <?php if (!empty($source['documented_date']) || !empty($source['location'])): ?><small><?=e($source['documented_date'] ?: 'Date not recorded')?><?=!empty($source['location'])?' · '.e($source['location']):''?></small><?php endif; ?>
                            <?php if (!empty($source['citation_text'])): ?><p><?=nl2br(e($source['citation_text']))?></p><?php endif; ?>
                        </article>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="muted">No separately verified source records have been published for this pottery yet.</p>
                <?php endif; ?>
            </div>
        </section>
        <section class="card" id="discussion">
            <div class="section-kicker">Community Discussion</div>
            <h2>Share a Comment or Question</h2>
            <p>Comments appear immediately so other visitors can read and reply. Please share respectful, relevant information.</p>
<?php
$comments=$pdo->prepare("SELECT * FROM Pot_comments WHERE pot_id=? AND parent_id IS NULL ORDER BY created_at ASC");
$comments->execute([$p['id']]);
$commentRows=$comments->fetchAll();
?>
            <div class="discussion-list">
<?php foreach($commentRows as $c): ?>
                <article class="comment-item">
                    <div><strong><?=e($c['visitor_name'])?></strong><small><?=e($c['created_at'])?></small></div>
                    <p><?=nl2br(e($c['comment']))?></p>
                    <form method="post" class="reply-form"><input type="hidden" name="action" value="comment"><?=csrf_field()?><input type="hidden" name="parent_id" value="<?=$c['id']?>"><input class="input small" name="visitor_name" required minlength="2" placeholder="Your name"><input class="input small" name="comment" required minlength="2" placeholder="Reply to this comment..."><button class="btn small secondary">↩ Reply</button></form><details class="report-box"><summary>Report</summary><form method="post" class="stack"><input type="hidden" name="action" value="report_comment"><?=csrf_field()?><input type="hidden" name="comment_id" value="<?=$c['id']?>"><input class="input small" name="visitor_name" required placeholder="Your name"><select class="select small" name="reason"><option>Spam</option><option>Offensive content</option><option>Wrong information</option><option>Irrelevant</option><option>Other</option></select><input class="input small" name="details" placeholder="Optional details"><button class="btn small danger">Submit Report</button></form></details>
                    <?php $re=$pdo->prepare('SELECT * FROM Pot_comments WHERE parent_id=? ORDER BY created_at ASC'); $re->execute([$c['id']]); foreach($re->fetchAll() as $rc): ?><div class="comment-reply"><strong><?=e($rc['visitor_name'])?></strong><small><?=e($rc['created_at'])?></small><p><?=nl2br(e($rc['comment']))?></p></div><?php endforeach; ?>
                </article>
<?php endforeach; if(!$commentRows): ?><div class="empty"><p>No comments yet. Be the first to start the discussion.</p></div><?php endif; ?>
            </div>
            <?php if($comment_error): ?><div class="alert">⚠ <?=e($comment_error)?></div><?php endif; ?>
            <form method="post" class="form-grid compact-grid">
                <?=csrf_field()?>
                <input type="hidden" name="action" value="comment">
                <input type="hidden" name="parent_id" value="0">
                <div class="field"><label>Your Name / Nickname</label><input class="input" name="visitor_name" required minlength="2"></div>
                <div class="field full"><label>Comment</label><textarea name="comment" rows="3" required minlength="2" placeholder="Ask a question or share what you know..."></textarea></div>
                <div class="actions"><button class="btn small" type="submit">💬 Post Comment</button></div>
            </form>
        </section>

        <section class="card" id="reviews"><div class="section-kicker">⭐ VISITOR FEEDBACK</div><h2>Ratings & Reviews</h2>
<?php $ratings=$pdo->prepare('SELECT * FROM Pot_ratings WHERE pot_id=? ORDER BY created_at DESC'); $ratings->execute([$p['id']]); $ratingRows=$ratings->fetchAll(); $avg=$ratingRows?round(array_sum(array_column($ratingRows,'rating'))/count($ratingRows),1):0; ?>
<div class="rating-summary"><strong>⭐ <?=e($avg?:'—')?></strong><span><?=count($ratingRows)?> review<?=count($ratingRows)==1?'':'s'?></span></div>
<?php foreach($ratingRows as $r): ?><article class="review-item"><b><?=e($r['visitor_name'])?></b><span><?=str_repeat('★',(int)$r['rating']).str_repeat('☆',5-(int)$r['rating'])?></span><?php if($r['review']): ?><p><?=nl2br(e($r['review']))?></p><?php endif; ?><small><?=e($r['created_at'])?></small></article><?php endforeach; ?>
<form method="post" class="form-grid compact-grid"><?=csrf_field()?><input type="hidden" name="action" value="rating"><div class="field"><label>Name / Nickname</label><input class="input" name="visitor_name" required></div><div class="field"><label>Rating</label><select class="select" name="rating" required><option value="">Choose</option><option value="5">★★★★★ 5</option><option value="4">★★★★☆ 4</option><option value="3">★★★☆☆ 3</option><option value="2">★★☆☆☆ 2</option><option value="1">★☆☆☆☆ 1</option></select></div><div class="field full"><label>Review (optional)</label><textarea name="review" rows="2"></textarea></div><div class="actions"><button class="btn small">Submit Review</button></div></form></section>
        <section class="card"><div class="section-kicker">🔗 RELATED KNOWLEDGE</div><h2>Explore Related Content</h2><div class="related-links">
<?php $rp=$pdo->prepare("SELECT id,name FROM Pots WHERE status='published' AND id<>? AND (pot_maker=? OR materials LIKE ?) ORDER BY id DESC LIMIT 4");$rp->execute([$p['id'],$p['pot_maker'],'%'.($p['materials']??'').'%']);foreach($rp->fetchAll() as $rr): ?><a class="related-card" href="pot.php?id=<?=$rr['id']?>">🏺 <b><?=e($rr['name'])?></b><span>View pottery →</span></a><?php endforeach;if(!$p['pot_maker']): ?><p class="muted">Browse the collection for more documented pottery.</p><?php endif;?><?php if($p['pot_maker']): ?><a class="related-card" href="artisan_profile.php?name=<?=urlencode($p['pot_maker'])?>">🧑‍🎨 <b><?=e($p['pot_maker'])?></b><span>View artisan profile →</span></a><?php endif; ?><?php $terms=$pdo->prepare("SELECT id,term,meaning FROM Traditional_terms WHERE status='published' AND (term LIKE ? OR meaning LIKE ?) ORDER BY sort_order LIMIT 3");$terms->execute(['%'.($p['name']??'').'%','%'.($p['materials']??'').'%']);foreach($terms->fetchAll() as $t): ?><a class="related-card" href="terminology.php?q=<?=urlencode($t['term'])?>">🗣 <b><?=e($t['term'])?></b><span>Learn term →</span></a><?php endforeach; ?>
</div></section>
        <section class="card" id="correction"><div class="section-kicker">✏️ KNOWLEDGE MAINTENANCE</div><h2>Suggest a Correction</h2><p>Help improve the accuracy of this documented record.</p><form method="post" class="stack"><?=csrf_field()?><input type="hidden" name="action" value="correction"><input class="input" name="visitor_name" required placeholder="Your name / nickname"><textarea name="suggestion" rows="3" required placeholder="What should be corrected or clarified?"></textarea><button class="btn small secondary">Send Correction</button></form></section>
    </main>
    <aside class="pot-detail-sidebar">
        <div class="detail-stat-card">
            <span class="detail-stat-icon">🏺</span>
            
            <strong><?=e($p['name'])?></strong>
            <small>Published for public viewing</small>
        </div>
        <div class="detail-nav-card">
            <h3>Explore More</h3>
            <a href="archive.php">Browse all pottery records <span>→</span></a>
            <a href="learning.php">Learn about pottery <span>→</span></a>
            <a href="tutorials.php">Open Tutorials <span>→</span></a>
            <a href="games/index.php">Try interactive games <span>→</span></a>
        </div>
    </aside>
</div>
<?php include 'partials/footer.php'; ?>

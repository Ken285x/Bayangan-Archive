<?php
require_once 'config/auth.php';
require_once 'config/db.php';
require_once 'config/csrf.php';
require_once 'config/activity.php';
$msg=''; $err='';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        verify_csrf();
        $name = trim((string)($_POST['visitor_name'] ?? '')) ?: 'Anonymous';
        $rating = (int)($_POST['rating'] ?? 0);
        $feedback = trim((string)($_POST['feedback'] ?? ''));
        if ($rating < 1 || $rating > 5) throw new RuntimeException('Please choose a rating from 1 to 5.');
        if ($feedback === '') throw new RuntimeException('Please enter your feedback before submitting.');
        $pdo->prepare('INSERT INTO Knowledge_feedback(visitor_name,content_type,content_id,rating,feedback) VALUES(?,?,?,?,?)')
            ->execute([$name, 'system', null, $rating, $feedback]);
        notify_admins($pdo, 'New system feedback', $name.' submitted feedback with a '.$rating.'-star rating.', 'feedback');
        $msg='Thank you. Your feedback was submitted successfully.';
    } catch (Throwable $e) { $err=$e->getMessage(); }
}
$title='System Feedback';
include 'partials/header.php';
?>
<div class="dashboard-head"><div><span class="eyebrow">CONTINUOUS IMPROVEMENT</span><h1>Bayangan Archive Feedback</h1><p>Tell the project team what works well and what should be improved.</p></div></div><section class="card feedback-card"><form method="post" class="form-grid compact-grid"><?=csrf_field()?><div class="field"><label>Name / Nickname</label><input class="input" name="visitor_name" placeholder="Optional"></div><div class="field"><label>Overall Rating</label><select class="select" name="rating" required><option value="">Choose</option><?php for($i=5;$i>=1;$i--): ?><option value="<?=$i?>"><?=str_repeat('★',$i).str_repeat('☆',5-$i)?> <?=$i?></option><?php endfor;?></select></div><div class="field full"><label>Feedback</label><textarea class="textarea" name="feedback" rows="5" placeholder="What should we improve?"></textarea></div><div class="actions"><button class="btn small">Submit Feedback</button></div></form><?php if($msg):?><div class="alert success-box">✓ <?=e($msg)?></div><?php endif;?><?php if($err):?><div class="alert">⚠ <?=e($err)?></div><?php endif;?></section>
<?php include 'partials/footer.php'; ?>
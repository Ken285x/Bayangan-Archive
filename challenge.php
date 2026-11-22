<?php
/** Daily challenge with public-player persistence. */
require_once 'config/db.php';
require_once 'config/auth.php';
require_once 'config/csrf.php';

$day=date('Y-m-d');
$items=$pdo->query("SELECT * FROM Game_questions WHERE status='published' ORDER BY id")->fetchAll();
$question=$items ? $items[crc32($day)%count($items)] : null;
$guestKey=public_player_key();
$already=null;
if($question){
    $q=$pdo->prepare('SELECT * FROM Public_challenge_attempts WHERE guest_key=? AND challenge_date=? LIMIT 1');
    $q->execute([$guestKey,$day]); $already=$q->fetch();
}
if($_SERVER['REQUEST_METHOD']==='POST' && ($_POST['action']??'')==='answer' && $question){
    try{
        verify_csrf();
        $choice=strtoupper(trim($_POST['choice']??''));
        if (!in_array($choice, ['A','B','C','D'], true)) throw new RuntimeException('Pumili ng isa sa apat na sagot.');
        $correct=$choice===$question['correct_choice'];
        $points=$correct?25:0;
        $pdo->prepare("INSERT INTO Public_players(guest_key,player_name) VALUES(?,?) ON DUPLICATE KEY UPDATE player_name=VALUES(player_name),last_seen_at=CURRENT_TIMESTAMP")->execute([$guestKey,public_player_name()]);
        if(!$already){
            $pdo->beginTransaction();
            $pdo->prepare('INSERT INTO Public_challenge_attempts(guest_key,challenge_date,question_id,selected_choice,correct,points) VALUES(?,?,?,?,?,?)')->execute([$guestKey,$day,$question['id'],$choice,$correct?1:0,$points]);
            if($correct){
                $streakQ=$pdo->prepare('SELECT last_challenge_date,current_streak FROM Public_players WHERE guest_key=?'); $streakQ->execute([$guestKey]); $pl=$streakQ->fetch();
                $streak=1;
                if($pl && $pl['last_challenge_date'] && $pl['last_challenge_date']===date('Y-m-d',strtotime('-1 day'))) $streak=(int)$pl['current_streak']+1;
                $pdo->prepare('UPDATE Public_players SET xp=xp+?,total_earned=total_earned+?,current_streak=?,last_challenge_date=? WHERE guest_key=?')->execute([$points,$points,$streak,$day,$guestKey]);
            } else {
                $pdo->prepare('UPDATE Public_players SET last_challenge_date=? WHERE guest_key=?')->execute([$day,$guestKey]);
            }
            $pdo->commit();
            $already=['correct'=>$correct?1:0,'points'=>$points,'selected_choice'=>$choice];
            $challengeSaved=true;
        }
    }catch(Throwable $e){ if($pdo->inTransaction())$pdo->rollBack(); $err=$e->getMessage(); }
}
$title='Daily Pottery Challenge'; include 'partials/header.php';
?>
<div class="page-band"><div><span class="eyebrow">DAILY COMMUNITY CHALLENGE</span><h1>🔥 Pottery Challenge</h1><p>One shared question for the day. Your result is saved to your public learning profile in this browser.</p></div></div>
<?php if(!empty($err)): ?><div class="alert">⚠ <?=e($err)?></div><?php endif; ?>
<?php if(!$question): ?>
<div class="empty"><h2>Challenge unavailable</h2><p>Add a published quiz or identification question from Admin → Game Content.</p></div>
<?php else: ?>
<section class="card challenge-card"><span class="badge">TODAY · <?=e($day)?></span>
<?php if($question['image_path']): ?><img class="media challenge-image" src="<?=e(media_url($question['image_path']))?>" alt="Pottery challenge image"><?php endif; ?>
<h2><?=e($question['question'])?></h2>
<?php if($already): ?>
<div class="notice <?=((int)$already['correct']===1)?'success-box':''?>"><?php if ((int)$already['correct'] === 1): ?>🎉 Correct! +<?= e($already['points']) ?> XP.<?php else: ?>✗ Incorrect.<?php endif; ?><br><?php if (!empty($already['selected_choice'])): ?>Your answer: <?= e($already['selected_choice']) ?>. <?= e($question['choice_' . strtolower($already['selected_choice'])] ?? '') ?>.<?php else: ?>Your answer was previously submitted.<?php endif; ?> Correct answer: <?= e($question['correct_choice']) ?>. <?= e($question['choice_' . strtolower($question['correct_choice'])] ?? '') ?><br><?= e($question['explanation'] ?? 'Review the documented learning materials for this topic.') ?></div>
<?php else: ?>
<form method="post" class="challenge-options"><?=csrf_field()?><input type="hidden" name="action" value="answer">
<?php foreach(['A','B','C','D'] as $k): $field='choice_'.strtolower($k); ?><button class="choice challenge-choice" type="submit" name="choice" value="<?=$k?>"><?=e($k)?>. <?=e($question[$field])?></button><?php endforeach; ?>
</form>
<?php endif; ?></section>
<?php endif; ?>
<div class="actions"><a class="btn small secondary" href="my_learning.php">📊 My Learning</a><a class="btn small secondary" href="leaderboard.php">🏆 Leaderboard</a></div>
<?php include 'partials/footer.php'; ?>

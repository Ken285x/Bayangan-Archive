<?php
/**
 * BAYANGAN ARCHIVE CODE GUIDE
 * File: interactive.php
 * Purpose: Main interactive learning activities.
 *
 * This page intentionally avoids 3D viewers and AI. It combines database-driven
 * content with JavaScript interactions so the archive behaves like a learning
 * platform instead of a static gallery.
 */

require_once 'config/db.php';
require_once 'config/auth.php';
require_once 'config/csrf.php';
require_once 'config/activity.php';

$feedbackSaved = false;
$feedbackError = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'feedback') {
    try {
        verify_csrf();
        $type = trim((string)($_POST['content_type'] ?? 'interactive_experience'));
        $id = (int)($_POST['content_id'] ?? 0) ?: null;
        $name = trim((string)($_POST['visitor_name'] ?? '')) ?: 'Anonymous';
        $rating = (int)($_POST['rating'] ?? 0);
        $feedback = trim((string)($_POST['feedback'] ?? ''));
        if ($rating < 1 || $rating > 5) throw new RuntimeException('Please choose a rating from 1 to 5.');
        if ($feedback === '') throw new RuntimeException('Please enter feedback before submitting.');
        $pdo->prepare('INSERT INTO Knowledge_feedback(visitor_name,content_type,content_id,rating,feedback) VALUES(?,?,?,?,?)')
            ->execute([$name, $type, $id, $rating, $feedback]);
        notify_admins($pdo, 'New learning activity feedback', $name.' submitted feedback about the interactive learning activities.', 'feedback');
        $feedbackSaved = true;
    } catch (Throwable $e) { $feedbackError = $e->getMessage(); }
}

$steps = $pdo->query(
    "SELECT s.*, t.term, t.meaning AS term_meaning
     FROM Interactive_steps s
     LEFT JOIN Traditional_terms t ON t.id = s.term_id
     WHERE s.status = 'published'
     ORDER BY s.sort_order, s.id"
)->fetchAll();

$terms = $pdo->query(
    "SELECT * FROM Traditional_terms
     WHERE status = 'published'
     ORDER BY sort_order, term"
)->fetchAll();

$Pots = $pdo->query(
    "SELECT p.id, p.name, p.description, p.image_path, p.pot_maker,
            m.local_name, m.location, m.verification_status
     FROM Pots p
     LEFT JOIN Archive_metadata m ON m.pot_id = p.id
     WHERE p.status = 'published'
     ORDER BY p.name"
)->fetchAll();

$quests = $pdo->query(
    "SELECT * FROM Heritage_quests
     WHERE status = 'published'
     ORDER BY sort_order, id"
)->fetchAll();

$today = date('Y-m-d');
$dailyItems = $pdo->query(
    "SELECT id, question, choice_a, choice_b, choice_c, choice_d, correct_choice, explanation, image_path
     FROM Game_questions
     WHERE status = 'published'
     ORDER BY id"
)->fetchAll();
$dailyQuestion = $dailyItems
    ? $dailyItems[crc32($today) % count($dailyItems)]
    : null;

$termCount = count($terms);
$potCount = count($Pots);
$stepCount = count($steps);

$title = 'Interactive Learning Activities';
include 'partials/header.php';
?>

<section class="interactive-hero">
    <div>
        <span class="eyebrow">BAYANGAN ARCHIVE · INTERACTIVE LEARNING</span>
        <h1>Explore. Learn. Play. Preserve.</h1>
        <p>Turn documented Bayangan pottery knowledge into an active learning journey through challenges, terminology, process exploration, maps, games, and community feedback.</p>
        <div class="actions">
            <a class="btn" href="#quest">Start Learning Activities</a>
            <a class="btn secondary" href="#process">Explore the Process</a>
            <a class="btn secondary" href="#terminology-challenge">Learn a Local Term</a>
        </div>
    </div>
    <div class="interactive-hero-stats">
        <div><b><?= $potCount ?></b><span>Pottery Records</span></div>
        <div><b><?= $termCount ?></b><span>Local Terms</span></div>
        <div><b><?= $stepCount ?></b><span>Process Steps</span></div>
        <div><b><?= count($quests) ?></b><span>Quest Missions</span></div>
    </div>
</section>

<?php if (!empty($feedbackError)): ?><div class="alert">⚠ <?= e($feedbackError) ?></div><?php endif; ?>
<?php if (!empty($feedbackSaved)): ?>
    <div class="alert success-box">✓ Thank you. Your learning experience feedback was saved.</div>
<?php endif; ?>

<section class="interactive-progress card" id="my-progress">
    <div class="progress-head">
        <div>
            <span class="eyebrow">YOUR LOCAL PROGRESS</span>
            <h2>My Knowledge Journey</h2>
            <p>Your progress is stored in this browser, so public visitors can use the experience without creating an account.</p>
        </div>
        <button class="btn small secondary" type="button" id="resetInteractiveProgress">Reset Progress</button>
    </div>
    <div class="journey-stats">
        <div><b id="xpValue">0</b><span>Knowledge XP</span></div>
        <div><b id="levelValue">1</b><span>Level</span></div>
        <div><b id="termsLearnedValue">0</b><span>Terms Learned</span></div>
        <div><b id="questsDoneValue">0</b><span>Quests Done</span></div>
        <div><b id="badgesValue">0</b><span>Badges</span></div>
    </div>
    <div class="progress-track"><span id="xpBar"></span></div>
    <div class="badge-strip" id="badgeStrip"></div>
</section>

<section class="interactive-section" id="quest">
    <div class="section-head-inline">
        <div>
            <span class="eyebrow">01 · LEARNING CHALLENGE</span>
            <h2>Complete missions to explore the archive</h2>
        </div>
        <span class="badge" id="questStatus">0 / <?= count($quests) ?> complete</span>
    </div>
    <div class="quest-grid">
        <?php foreach ($quests as $quest): ?>
            <article class="quest-card card" data-quest-id="<?= $quest['id'] ?>" data-quest-type="<?= e($quest['target_type']) ?>" data-points="<?= (int)$quest['points'] ?>" data-badge="<?= e($quest['badge'] ?? '') ?>">
                <div class="quest-icon">🧭</div>
                <span class="badge">+<?= (int)$quest['points'] ?> XP</span>
                <h3><?= e($quest['title']) ?></h3>
                <p><?= e($quest['description']) ?></p>
                <?php if (!empty($quest['badge'])): ?><small>Badge: <?= e($quest['badge']) ?></small><?php endif; ?>
                <button class="btn small quest-action" type="button">Mark Mission Complete</button>
            </article>
        <?php endforeach; ?>
    </div>
</section>

<section class="interactive-section" id="process">
    <div class="section-head-inline">
        <div>
            <span class="eyebrow">02 · INTERACTIVE PROCESS</span>
            <h2>Experience the pot-making sequence</h2>
            <p>Click each stage to reveal the documented learning material. Media can be maintained by the administrator.</p>
        </div>
        <span class="badge"><span id="processCounter">0</span> / <?= $stepCount ?> explored</span>
    </div>

    <div class="process-layout">
        <div class="process-timeline">
            <?php foreach ($steps as $i => $step): ?>
                <button class="process-step <?= $i === 0 ? 'active' : '' ?>" type="button" data-step="<?= $i ?>">
                    <span><?= $i + 1 ?></span>
                    <strong><?= e($step['title']) ?></strong>
                    <small><?= e($step['short_description'] ?? '') ?></small>
                </button>
            <?php endforeach; ?>
        </div>
        <div class="process-stage card">
            <?php foreach ($steps as $i => $step): ?>
                <article class="process-panel <?= $i === 0 ? 'active' : '' ?>" data-panel="<?= $i ?>">
                    <div class="process-number">STEP <?= $i + 1 ?></div>
                    <h3><?= e($step['title']) ?></h3>
                    <p><?= nl2br(e($step['detail'])) ?></p>
                    <?php if (!empty($step['image_path'])): ?>
                        <img class="process-media" src="<?= e($step['image_path']) ?>" alt="<?= e($step['title']) ?>" loading="lazy">
                    <?php endif; ?>
                    <?php if (!empty($step['video_path'])): ?>
                        <video class="process-media" controls preload="metadata" src="<?= e($step['video_path']) ?>"></video>
                    <?php endif; ?>
                    <?php if (!empty($step['term'])): ?>
                        <div class="linked-knowledge"><b>Related local term:</b> <?= e($step['term']) ?><br><small><?= e($step['term_meaning']) ?></small></div>
                    <?php endif; ?>
                    <?php $hasKnowledgeCheck = !empty($step['check_question']) && !empty($step['check_choice_a']) && !empty($step['check_choice_b']) && !empty($step['check_choice_c']) && !empty($step['check_choice_d']) && in_array($step['check_correct_choice'], ['A','B','C','D'], true); ?>
                    <?php if ($hasKnowledgeCheck): ?>
                        <div class="process-knowledge-check" data-process-check="<?= $i ?>" data-step-id="<?= (int)$step['id'] ?>">
                            <h4>Knowledge Check</h4><p><?= e($step['check_question']) ?></p>
                            <div class="simulator-options process-check-options">
                                <?php foreach (['A','B','C','D'] as $letter): $choiceField = 'check_choice_' . strtolower($letter); ?>
                                    <button class="simulator-option process-check-option" type="button" data-process-answer="<?= $letter ?>"><?= $letter ?>. <?= e($step[$choiceField]) ?></button>
                                <?php endforeach; ?>
                            </div>
                            <p class="process-check-feedback" data-process-check-feedback aria-live="polite"></p>
                        </div>
                    <?php else: ?>
                        <div class="notice">Knowledge check is not yet configured for this stage. Please ask the administrator to add its question and choices.</div>
                    <?php endif; ?>
                    <div class="actions">
                        <button class="btn small" type="button" data-process-complete="<?= $i ?>" disabled>✓ Review Stage First</button>
                    </div>
                </article>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<section class="interactive-section" id="simulator">
    <div class="section-head-inline">
        <div>
            <span class="eyebrow">02B · POT-MAKING SIMULATOR</span>
            <h2>Make the right decision at each stage</h2>
            <p>Choose the action that matches the documented learning sequence. The choices are a learning interaction, not a replacement for hands-on artisan instruction.</p>
        </div>
        <span class="badge" id="simulatorScore">0 / 0</span>
    </div>
    <div class="simulator-card card">
        <div class="simulator-progress"><span id="simulatorBar"></span></div>
        <div id="simulatorQuestion"></div>
        <div class="simulator-options" id="simulatorOptions"></div>
        <div id="simulatorFeedback"></div>
        <button class="btn small" type="button" id="simulatorNext" hidden>Next Stage →</button>
    </div>
</section>

<section class="interactive-section" id="terminology-challenge">
    <div class="section-head-inline">
        <div>
            <span class="eyebrow">03 · TRADITIONAL TERMINOLOGY CHALLENGE</span>
            <h2>Listen, identify, and learn</h2>
            <p>Only verified/approved local terminology should be published for actual fieldwork use.</p>
        </div>
        <a class="btn small secondary" href="terminology.php">Open Full Dictionary →</a>
    </div>

    <div class="term-challenge card">
        <div class="challenge-audio-wrap">
            <div class="challenge-term-icon">🗣️</div>
            <div>
                <span class="eyebrow">AUDIO CHALLENGE</span>
                <h3 id="termChallengeTitle">Press Start</h3>
                <audio id="termChallengeAudio" controls preload="none" hidden></audio>
                <p id="termChallengeMeaning" class="muted">Listen to a pronunciation recording, then choose the matching local term.</p>
            </div>
        </div>
        <div id="termChoices" class="term-choice-grid"></div>
        <div class="actions">
            <button class="btn small" type="button" id="startTermChallenge">▶ Start Challenge</button>
            <button class="btn small secondary" type="button" id="nextTermChallenge" hidden>Next Term →</button>
            <button class="btn small secondary" type="button" id="startPronunciationPractice">🎙 Practice Pronunciation</button>
        </div>
        <div class="pronunciation-practice" id="pronunciationPractice" hidden>
            <p class="muted">Listen first, then record yourself. The system lets you replay your recording but does not automatically score pronunciation.</p>
            <div class="actions">
                <button class="btn small" type="button" id="recordPractice">● Record</button>
                <button class="btn small danger" type="button" id="stopPractice" disabled>■ Stop</button>
                <button class="btn small secondary" type="button" id="replayPractice" disabled>▶ Replay</button>
            </div>
            <span id="practiceStatus" class="recording-status">Ready to practice.</span>
            <audio id="practiceAudio" controls hidden></audio>
        </div>
        <div id="termChallengeResult"></div>
    </div>
</section>

<section class="interactive-section" id="knowledge-explorer">
    <div class="section-head-inline">
        <div>
            <span class="eyebrow">04 · KNOWLEDGE EXPLORER</span>
            <h2>Search across pottery, terms, and process</h2>
        </div>
    </div>
    <div class="card explorer-box">
        <div class="actions knowledge-search-controls">
            <input class="input" id="knowledgeSearch" type="search" placeholder="Search a pot, local term, technique, material, maker, or process…" aria-label="Search the knowledge explorer">
            <button class="btn small" type="button" id="knowledgeSearchButton">🔎 Search</button>
        </div>
        <div class="explorer-results" id="knowledgeResults" aria-live="polite"></div>
    </div>
</section>

<section class="interactive-section" id="knowledge-map">
    <div class="section-head-inline">
        <div>
            <span class="eyebrow">05 · BAYANGAN MAP</span>
            <h2>See how the knowledge connects</h2>
            <p>Each node represents a part of the knowledge management chain. Select a node to explore related records.</p>
        </div>
    </div>
    <div class="knowledge-graph card">
        <button type="button" class="graph-node center" data-graph="archive">🏺 Archive</button>
        <button type="button" class="graph-node n1" data-graph="terms">🗣 Terms</button>
        <button type="button" class="graph-node n2" data-graph="process">🔥 Process</button>
        <button type="button" class="graph-node n3" data-graph="Pots">🏺 Pots</button>
        <button type="button" class="graph-node n5" data-graph="Tutorials">🎬 Tutorials</button>
        <button type="button" class="graph-node n6" data-graph="games">🎮 Games</button>
        <div class="graph-info" id="graphInfo"><b>Archive</b><p>Choose a node to see what knowledge is connected to it.</p></div>
    </div>
</section>


<section class="interactive-section" id="daily">
    <div class="section-head-inline">
        <div>
            <span class="eyebrow">06 · KNOWLEDGE OF THE DAY</span>
            <h2>One challenge to keep the archive active</h2>
        </div>
        <span class="badge"><?= e($today) ?></span>
    </div>
    <?php if ($dailyQuestion): ?>
        <div class="daily-interactive card" data-correct="<?= e($dailyQuestion['correct_choice']) ?>" data-correct-label="<?= e($dailyQuestion['choice_' . strtolower((string)$dailyQuestion['correct_choice'])] ?? '') ?>" data-explanation="<?= e($dailyQuestion['explanation'] ?? '') ?>">
            <?php if (!empty($dailyQuestion['image_path'])): ?><img src="<?= e($dailyQuestion['image_path']) ?>" alt="Daily pottery challenge" loading="lazy"><?php endif; ?>
            <div>
                <span class="badge">🎯 DAILY CHALLENGE</span>
                <h3><?= e($dailyQuestion['question']) ?></h3>
                <div class="daily-options">
                    <?php foreach (['A','B','C','D'] as $choice): $field = 'choice_' . strtolower($choice); ?>
                        <button class="daily-choice" type="button" data-choice="<?= $choice ?>"><?= $choice ?>. <?= e($dailyQuestion[$field]) ?></button>
                    <?php endforeach; ?>
                </div>
                <div id="dailyResult"></div>
            </div>
        </div>
    <?php else: ?>
        <div class="empty">Add a published game question to activate the daily challenge.</div>
    <?php endif; ?>
</section>

<section class="interactive-section" id="feedback">
    <div class="section-head-inline">
        <div>
            <span class="eyebrow">07 · USER FEEDBACK</span>
            <h2>Help improve the learning experience</h2>
        </div>
    </div>
    <form class="card feedback-form" method="post">
        <?= csrf_field() ?>
        <input type="hidden" name="action" value="feedback">
        <input type="hidden" name="content_type" value="interactive_experience">
        <div class="form-grid compact-grid">
            <div class="field"><label>Your name / nickname (optional)</label><input class="input" name="visitor_name" maxlength="120"></div>
            <div class="field"><label>Rating</label><select class="select" name="rating"><option value="5">★★★★★ Very helpful</option><option value="4">★★★★ Helpful</option><option value="3">★★★ Okay</option><option value="2">★★ Needs improvement</option><option value="1">★ Not helpful</option></select></div>
            <div class="field full"><label>What should we improve?</label><textarea name="feedback" rows="3" placeholder="Tell us which activity was clear, confusing, or interesting."></textarea></div>
        </div>
        <button class="btn small" type="submit">Send Feedback</button>
    </form>
</section>

<script>
window.BAYANGAN_INTERACTIVE = {
    terms: <?= json_encode($terms, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    Pots: <?= json_encode($Pots, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    quests: <?= json_encode($quests, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>,
    stepCount: <?= $stepCount ?>,
    eventUrl: <?= json_encode(url('api/interactive_event.php')) ?>,
    dailyDate: <?= json_encode($today) ?>,
    processCheckUrl: <?= json_encode(url('api/process_check.php')) ?>,
    progressUrl: <?= json_encode(url('api/interactive_progress.php')) ?>,
    playerKey: <?= json_encode(public_player_key()) ?>,
    csrfToken: <?= json_encode(csrf_token()) ?>,
};
</script>

<?php include 'partials/footer.php'; ?>

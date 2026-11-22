<?php
/**
 * BAYANGAN ARCHIVE CODE GUIDE
 * File: terminology.php
 * Purpose: Public Traditional Terminology Library.
 */

require_once 'config/auth.php';
require_once 'config/db.php';

$q = trim((string)($_GET['q'] ?? ''));
$category = trim((string)($_GET['category'] ?? ''));

$where = ["status = 'published'"];
$args = [];

if ($q !== '') {
    $where[] = '(term LIKE ? OR meaning LIKE ? OR english_meaning LIKE ? OR filipino_meaning LIKE ? OR category LIKE ? OR context_example LIKE ?)';
    $like = '%' . $q . '%';
    $args = [$like, $like, $like, $like, $like, $like];
}

if ($category !== '') {
    $where[] = 'category = ?';
    $args[] = $category;
}

$sql = 'SELECT * FROM Traditional_terms WHERE ' . implode(' AND ', $where) . ' ORDER BY sort_order, term';
$stmt = $pdo->prepare($sql);
$stmt->execute($args);
$terms = $stmt->fetchAll();

$categories = $pdo->query(
    "SELECT DISTINCT category FROM Traditional_terms WHERE status = 'published' ORDER BY category"
)->fetchAll(PDO::FETCH_COLUMN);

$title = 'Traditional Terminology';
include 'partials/header.php';
?>

<section class="page-hero compact-hero">
    <div>
        <span class="eyebrow">BAYANGAN LOCAL KNOWLEDGE</span>
        <h1>Traditional Terminology</h1>
        <p>Explore documented Bayangan-local pottery words with meaning, context, source, verification, and recorded pronunciation.</p>
        <div class="actions">
            <a class="btn small" href="interactive.php#terminology-challenge">🎙 Listen & Learn</a>
            <a class="btn small secondary" href="interactive.php#knowledge-explorer">🔎 Knowledge Explorer</a>
        </div>
    </div>
    <div class="term-hero-icon" aria-hidden="true">🗣️🏺</div>
</section>

<div class="card terminology-tools">
    <form method="get" class="search-row">
        <div class="field grow">
            <label for="termSearch">Search local word or meaning</label>
            <input class="input" id="termSearch" name="q" value="<?= e($q) ?>" placeholder="Search a Bayangan pottery term…">
        </div>
        <div class="field">
            <label for="termCategory">Category</label>
            <select class="select" id="termCategory" name="category">
                <option value="">All categories</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= e($cat) ?>" <?= $category === $cat ? 'selected' : '' ?>><?= e($cat) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <button class="btn small" type="submit">🔎 Search</button>
        <?php if ($q !== '' || $category !== ''): ?>
            <a class="btn small secondary" href="<?= e(url('terminology.php')) ?>">Clear</a>
        <?php endif; ?>
    </form>
</div>

<div class="section-head-inline">
    <div>
        <span class="eyebrow">LOCAL VOCABULARY</span>
        <h2><?= count($terms) ?> <?= count($terms) === 1 ? 'Term' : 'Terms' ?> Found</h2>
    </div>
    <div class="term-help">🔊 Listen to the actual pronunciation recording when available.</div>
</div>

<?php if (!$terms): ?>
    <div class="empty">
        <h2>No published terminology found.</h2>
        <p>Ask the administrator to add or publish verified terminology from fieldwork.</p>
    </div>
<?php else: ?>
    <div class="terminology-grid">
        <?php foreach ($terms as $term): ?>
            <article class="card term-card" id="term-<?= (int)$term['id'] ?>">
                <?php if (!empty($term['photo_path'])): ?>
                    <img class="term-photo" src="<?= e($term['photo_path']) ?>" alt="<?= e($term['term']) ?>" loading="lazy">
                <?php endif; ?>

                <div class="term-card-top">
                    <span class="badge"><?= e($term['category']) ?></span>
                    <span class="term-number">#<?= e($term['sort_order']) ?></span>
                </div>

                <h2><?= e($term['term']) ?></h2>

                <?php if ($term['pronunciation']): ?>
                    <p class="term-pronunciation">
                        Pronunciation: <strong><?= e($term['pronunciation']) ?></strong>
                    </p>
                <?php endif; ?>

                <div class="term-meaning">
                    <span>MEANING</span>
                    <p><?= nl2br(e($term['meaning'])) ?></p>
                </div>

                <?php if (!empty($term['english_meaning'])): ?>
                    <div class="term-translation">
                        <span>ENGLISH</span>
                        <p><?= nl2br(e($term['english_meaning'])) ?></p>
                    </div>
                <?php endif; ?>

                <?php if (!empty($term['filipino_meaning'])): ?>
                    <div class="term-translation">
                        <span>FILIPINO</span>
                        <p><?= nl2br(e($term['filipino_meaning'])) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($term['context_example']): ?>
                    <div class="term-example">
                        <span>USED IN CONTEXT</span>
                        <p><?= nl2br(e($term['context_example'])) ?></p>
                    </div>
                <?php endif; ?>

                <?php if ($term['source_informant'] || $term['fieldwork_note']): ?>
                    <div class="term-source">
                        <?php if ($term['source_informant']): ?>
                            <div><b>Source / Informant:</b> <?= e($term['source_informant']) ?></div>
                        <?php endif; ?>
                        <?php if ($term['fieldwork_note']): ?>
                            <div><b>Documentation:</b> <?= e($term['fieldwork_note']) ?></div>
                        <?php endif; ?>
                        <div><b>Verification:</b> <?= e($term['verification_status'] ?? 'pending') ?></div>
                    </div>
                <?php endif; ?>

                <div class="term-actions">
                    <?php if ($term['audio_path']): ?>
                        <audio class="term-audio" controls preload="none" src="<?= e($term['audio_path']) ?>"></audio>
                        <span class="term-audio-label">🔊 Recorded pronunciation</span>
                    <?php else: ?>
                        <span class="muted">🔇 No pronunciation recording yet.</span>
                    <?php endif; ?>
                    <button class="btn small secondary learn-term-button" type="button" data-term-id="<?= $term['id'] ?>">✓ Learn This Term</button>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<div class="card term-preservation-note">
    <span class="eyebrow">KNOWLEDGE PRESERVATION NOTE</span>
    <h2>Why Traditional Terminology matters</h2>
    <p>Local words carry practical knowledge and cultural meaning. Recording the word, its meaning, how it is used, who provided the information, and how it is pronounced helps Bayangan pottery knowledge remain understandable and retrievable for future learners and researchers.</p>
    <p class="muted"><b>Verification:</b> Publish local terms only after they are supported by actual fieldwork, interviews, documentation, or an approved source.</p>
</div>

<script>
/* Mark terms as learned in the active player profile and sync them to MySQL. */
const BA_TERM_STATE_KEY = 'bayangan_interactive_progress_v1_' + <?=json_encode(public_player_key())?>;
const BA_TERM_LEGACY_KEY = 'bayangan_interactive_progress_v1';
const BA_TERM_ALLOW_LEGACY = <?=!empty($_SESSION['public_profile_restored']) ? 'false' : 'true'?>;
const BA_TERM_PROGRESS_API = <?=json_encode(url('api/interactive_progress.php'))?>;
const BA_TERM_CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
document.querySelectorAll('.learn-term-button').forEach(function (button) {
    button.addEventListener('click', async function () {
        button.disabled = true;
        const termId = String(button.dataset.termId);
        let state = {};
        try { state = JSON.parse(localStorage.getItem(BA_TERM_STATE_KEY) || '{}'); } catch (error) { state = {}; }
        if (BA_TERM_ALLOW_LEGACY && (!Array.isArray(state.terms) || state.terms.length===0)) { try { const legacy=JSON.parse(localStorage.getItem(BA_TERM_LEGACY_KEY)||'{}'); state={...legacy,...state}; ['terms','quests','steps','badges','daily','activities','potsViewed','processViewed'].forEach(field=>{state[field]=[...new Set([...(Array.isArray(legacy[field])?legacy[field]:[]),...(Array.isArray(state[field])?state[field]:[])].map(String))]}); state.xp=Math.max(Number(legacy.xp)||0,Number(state.xp)||0); } catch(error) {} }
        try {
            const response = await fetch(BA_TERM_PROGRESS_API, {credentials:'same-origin', cache:'no-store'});
            const remote = await response.json();
            if (response.ok && remote.ok && remote.state && typeof remote.state === 'object') {
                const r = remote.state;
                state = {...r, ...state};
                ['terms','quests','steps','badges','daily','activities','potsViewed','processViewed'].forEach(field => {
                    state[field] = [...new Set([...(Array.isArray(r[field])?r[field]:[]), ...(Array.isArray(state[field])?state[field]:[])].map(String))];
                });
                state.xp = Math.max(Number(r.xp)||0, Number(state.xp)||0);
                state.games = Math.max(Number(r.games)||0, Number(state.games)||0);
            }
        } catch (error) { /* Keep local state when offline. */ }
        state.terms = Array.isArray(state.terms) ? state.terms.map(String) : [];
        state.xp = Math.max(0, Number(state.xp)||0);
        if (!state.terms.includes(termId)) {
            state.terms.push(termId); state.xp += 10; button.textContent = '✓ Learned +10 XP';
        } else { button.textContent = '✓ Already Learned'; }
        try { localStorage.setItem(BA_TERM_STATE_KEY, JSON.stringify(state)); } catch (error) {}
        try {
            const saved = await fetch(BA_TERM_PROGRESS_API, {method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':BA_TERM_CSRF},body:JSON.stringify({state})});
            if (!saved.ok) throw new Error('sync failed');
            if (BA_TERM_ALLOW_LEGACY) { try { localStorage.removeItem(BA_TERM_LEGACY_KEY); } catch(error) {} }
        } catch (error) { button.title = 'Saved on this browser. Server sync will retry when online.'; }
    });
});
</script>

<?php include 'partials/footer.php'; ?>

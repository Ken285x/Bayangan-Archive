<?php
require_once 'config/auth.php';
$title = 'Accessibility Settings';
include 'partials/header.php';
?>

<div class="dashboard-head">
    <div>
        <span class="eyebrow">ACCESSIBILITY</span>
        <h1>Display &amp; Accessibility</h1>
        <p>Adjust the public interface for easier reading and navigation. Preferences are saved in this browser.</p>
    </div>
</div>

<section class="card accessibility-card" aria-labelledby="accessibilityTitle">
    <h2 id="accessibilityTitle">Reading &amp; Navigation Preferences</h2>
    <p class="muted">Choose a setting below. Your preference stays on this browser until you change it.</p>

    <div class="accessibility-controls" role="group" aria-label="Accessibility preferences">
        <button class="btn small" id="fontNormal" type="button" aria-pressed="false">A Normal Text</button>
        <button class="btn small" id="fontLarge" type="button" aria-pressed="false">A+ Large Text</button>
        <button class="btn small" id="fontXL" type="button" aria-pressed="false">A++ Extra Large</button>
        <button class="btn small secondary" id="contrast" type="button" aria-pressed="false">◐ High Contrast</button>
        <button class="btn small secondary" id="reduceMotion" type="button" aria-pressed="false">▣ Reduce Motion</button>
    </div>

    <p class="accessibility-status" id="accessibilityStatus" role="status" aria-live="polite">Accessibility preferences are ready.</p>

    <div class="notice" style="margin-top:15px">
        <b>Media accessibility:</b> provide captions for videos and transcripts/pronunciation text for audio recordings when publishing field documentation.
    </div>
</section>

<?php include 'partials/footer.php'; ?>

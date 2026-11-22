<?php
/** Public map focused on documented pottery locations in Bayangan. */
require_once 'config/db.php';
require_once 'config/auth.php';
$st = $pdo->query("SELECT p.id,p.name,p.pot_maker,p.materials,p.uses_purpose,p.cultural_significance,m.local_name,m.location,m.latitude,m.longitude,p.image_path FROM Pots p LEFT JOIN Archive_metadata m ON m.pot_id=p.id WHERE p.status='published' ORDER BY m.location,p.name");
$Pots = $st->fetchAll(PDO::FETCH_ASSOC);
$title = 'Map';
include 'partials/header.php';
?>
<div class="page-band">
    <div>
        <span class="eyebrow">BAYANGAN MAP</span>
        <h1>Explore Pottery Locations in Bayangan</h1>
        <p>Search for a pottery record or look up a place to explore it on the map.</p>
    </div>
</div>
<section class="card map-search-card" aria-label="Search map">
    <form id="heritageMapSearchForm" class="map-search-form" role="search">
        <label class="sr-only" for="heritageMapSearch">Search Bayangan pottery records</label>
        <input class="input" id="heritageMapSearch" type="search" placeholder="Search a pottery name, pot maker, or documented Bayangan location…" autocomplete="off">
        <button class="btn" type="submit">🔎 Search</button>
        <button class="btn secondary" type="button" id="heritageMapClear">Clear</button>
    </form>
    <p id="heritageMapSearchStatus" class="map-search-status" role="status" aria-live="polite">Search only the pottery records and locations documented in Bayangan Archive.</p>
</section>
<div class="map-layout">
    <section class="card map-card">
        <div id="heritageMap" class="heritage-map" aria-label="Interactive map"></div>
        <div class="map-note">Pottery markers appear when an administrator has saved coordinates for the record. The map displays Bayangan pottery records with saved location coordinates.</div>
    </section>
    <aside class="card location-list">
        <h2>📍 Locations <span id="heritageMapResultCount" class="map-result-count"></span></h2>
        <div id="heritageMapLocationItems">
            <?php foreach ($Pots as $p): ?>
                <a class="location-item" data-pot-id="<?= (int)$p['id'] ?>" href="pot.php?id=<?= (int)$p['id'] ?>">
                    <span class="location-pin">📍</span>
                    <span><b><?= e($p['name']) ?></b><small><?= e($p['location'] ?: 'Location not specified') ?><?= $p['pot_maker'] ? ' · ' . e($p['pot_maker']) : '' ?></small></span>
                </a>
            <?php endforeach; ?>
            <?php if (!$Pots): ?><div class="empty compact">No published pottery records yet.</div><?php endif; ?>
        </div>
        <div id="heritageMapNoResults" class="empty compact" hidden>No pottery records match this search.</div>
    </aside>
</div>
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css">
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
<script>
(() => {
    const records = <?= json_encode($Pots, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT) ?>;
    const mapElement = document.getElementById('heritageMap');
    if (!mapElement || typeof L === 'undefined') return;

    const map = L.map(mapElement).setView([11.95, 122.13], 11);
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '© OpenStreetMap contributors'
    }).addTo(map);

    const markerLayer = L.featureGroup().addTo(map);
    const markerById = new Map();
    const searchForm = document.getElementById('heritageMapSearchForm');
    const searchInput = document.getElementById('heritageMapSearch');
    const searchStatus = document.getElementById('heritageMapSearchStatus');
    const clearButton = document.getElementById('heritageMapClear');
    const resultCount = document.getElementById('heritageMapResultCount');
    const noResults = document.getElementById('heritageMapNoResults');
    const listItems = Array.from(document.querySelectorAll('.location-item[data-pot-id]'));

    records.forEach((pot) => {
        if (pot.latitude === null || pot.latitude === '' || pot.longitude === null || pot.longitude === '') return;
        const lat = Number(pot.latitude);
        const lng = Number(pot.longitude);
        if (!Number.isFinite(lat) || !Number.isFinite(lng) || lat < -90 || lat > 90 || lng < -180 || lng > 180) return;
        const marker = L.marker([lat, lng]);
        const popup = document.createElement('div');
        const name = document.createElement('strong');
        name.textContent = pot.name || 'Pottery record';
        const location = document.createElement('div');
        location.textContent = pot.location || 'Location not specified';
        const link = document.createElement('a');
        link.href = 'pot.php?id=' + encodeURIComponent(pot.id);
        link.textContent = 'View Details';
        popup.append(name, location, link);
        marker.bindPopup(popup);
        marker.addTo(markerLayer);
        markerById.set(String(pot.id), marker);
    });

    function filterArchive(query) {
        const q = query.trim().toLocaleLowerCase();
        const matched = records.filter((pot) => [pot.name, pot.local_name, pot.location, pot.pot_maker, pot.materials, pot.uses_purpose, pot.cultural_significance].some((value) => String(value || '').toLocaleLowerCase().includes(q)));
        const matchIds = new Set(matched.map((pot) => String(pot.id)));
        listItems.forEach((item) => { item.hidden = q !== '' && !matchIds.has(item.dataset.potId); });
        resultCount.textContent = q ? '(' + matched.length + ')' : '(' + records.length + ')';
        noResults.hidden = matched.length > 0 || q === '';
        markerLayer.clearLayers();
        matched.forEach((pot) => {
            const marker = markerById.get(String(pot.id));
            if (marker) marker.addTo(markerLayer);
        });
        return matched;
    }

    searchForm.addEventListener('submit', (event) => {
        event.preventDefault();
        const query = searchInput.value.trim();
        if (!query) {
            filterArchive('');
            searchStatus.textContent = 'Enter a pottery name, pot maker, or documented Bayangan location.';
            map.setView([11.95, 122.13], 11);
            return;
        }
        const matched = filterArchive(query);
        const matchedMarkers = matched.map((pot) => markerById.get(String(pot.id))).filter(Boolean);
        if (matched.length > 0) {
            if (matchedMarkers.length > 0) {
                map.fitBounds(L.featureGroup(matchedMarkers).getBounds().pad(0.18), { maxZoom: 15 });
                searchStatus.textContent = 'Found ' + matched.length + ' pottery record(s). The map is showing records with saved coordinates.';
            } else {
                searchStatus.textContent = 'Found ' + matched.length + ' pottery record(s), but these records do not have map coordinates. Check the location list or check the documented location list.';
            }
            return;
        }

        searchStatus.textContent = 'No saved Bayangan pottery record matched “' + query + '”. Try a pottery name, pot maker, or documented location.';
    });

    clearButton.addEventListener('click', () => {
        searchInput.value = '';
        filterArchive('');
        searchStatus.textContent = 'Search only the pottery records and locations documented in Bayangan Archive.';
        map.setView([11.95, 122.13], 11);
        searchInput.focus();
    });

    resultCount.textContent = '(' + records.length + ')';
    window.addEventListener('load', () => setTimeout(() => map.invalidateSize(), 100));
})();
</script>
<?php include 'partials/footer.php'; ?>

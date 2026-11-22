<?php
/** Public saved collection. Syncs to the active public player profile and supports legacy local saves. */
require_once 'config/db.php'; require_once 'config/auth.php';
$Pots=$pdo->query("SELECT id,name,description,image_path FROM Pots WHERE status='published' ORDER BY id DESC")->fetchAll();
$title='My Collection';include 'partials/header.php';
?>
<div class="page-band"><span class="eyebrow">PERSONALIZED DISCOVERY</span><h1>♥ My Saved Collection</h1><p>Your saved pottery is synced to your public player profile. Create or restore your profile from the Games page to access it on another browser or device.</p><a class="btn small secondary" href="games/index.php">🎮 Create / Restore Player Profile</a></div>
<div id="collectionSyncStatus" class="muted" role="status" aria-live="polite">Loading your saved collection…</div>
<div id="savedCollection" class="grid2"><div class="card empty"><h2>Loading collection…</h2></div></div>
<script>
const BA_POTS = <?=json_encode(array_map(fn($x)=>['id'=>(int)$x['id'],'name'=>$x['name'],'description'=>mb_strimwidth((string)$x['description'],0,180,'…'),'image'=>$x['image_path']],$Pots),JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES|JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)?>;
const BA_COLLECTION_API = <?=json_encode(url('api/saved_collection.php'))?>;
const BA_CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';
const BA_LEGACY_KEY = 'bayangan_saved_pots';
const BA_PROFILE_SUFFIX = <?=json_encode(public_player_key())?>;
const BA_LOCAL_KEY = 'bayangan_saved_pots_' + BA_PROFILE_SUFFIX;
const BA_PENDING_REMOVALS_KEY = 'bayangan_saved_pots_removed_' + BA_PROFILE_SUFFIX;
const baPendingRemovals = () => {try{return baNormalizeIds(JSON.parse(localStorage.getItem(BA_PENDING_REMOVALS_KEY)||'[]'))}catch(e){return []}};
const baWritePendingRemovals = ids => {try{const clean=baNormalizeIds(ids);if(clean.length)localStorage.setItem(BA_PENDING_REMOVALS_KEY,JSON.stringify(clean));else localStorage.removeItem(BA_PENDING_REMOVALS_KEY)}catch(e){}};
const BA_ALLOW_LEGACY_IMPORT = <?=!empty($_SESSION['public_profile_restored']) ? 'false' : 'true'?>;
const baNormalizeIds = values => [...new Set((Array.isArray(values)?values:[]).map(String).filter(v=>/^\d+$/.test(v)&&Number(v)>0))];
const baEscape = value => String(value??'').replace(/[&<>\"']/g, c=>({'&':'&amp;','<':'&lt;','>':'&gt;','\"':'&quot;',"'":'&#39;'}[c]));
let baSavedIds=[];
function baRenderCollection(){
 const box=document.getElementById('savedCollection');
 const list=BA_POTS.filter(p=>baSavedIds.includes(String(p.id)));
 box.innerHTML=list.length?list.map(p=>`<article class="card"><div class="collection-img">${p.image?`<img src="${baEscape(p.image)}" alt="${baEscape(p.name)}">`:'🏺'}</div><h2>${baEscape(p.name)}</h2><p>${baEscape(p.description)}</p><div class="actions"><a class="btn small" href="pot.php?id=${p.id}">View Details →</a><button class="btn small secondary" type="button" data-remove-saved-pot="${p.id}">Remove ♥</button></div></article>`).join(''):`<div class="card empty"><h2>No saved pots yet</h2><p>Open a pottery record and choose “Save to My Collection”. Your saved items will appear here.</p><a class="btn small" href="archive.php">Browse Pottery Collection</a></div>`;
 box.querySelectorAll('[data-remove-saved-pot]').forEach(btn=>btn.addEventListener('click',async()=>{
  const id=String(btn.dataset.removeSavedPot); baSavedIds=baSavedIds.filter(x=>x!==id); try{localStorage.setItem(BA_LOCAL_KEY,JSON.stringify(baSavedIds))}catch(e){} baWritePendingRemovals([...baPendingRemovals(),id]); baRenderCollection();
  try{const r=await fetch(BA_COLLECTION_API,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':BA_CSRF},body:JSON.stringify({action:'remove',pot_id:Number(id)})});const d=await r.json();if(!r.ok||!d.ok)throw new Error();baSavedIds=baNormalizeIds(d.pot_ids);baWritePendingRemovals(baPendingRemovals().filter(x=>x!==id));localStorage.setItem(BA_LOCAL_KEY,JSON.stringify(baSavedIds));baRenderCollection();document.getElementById('collectionSyncStatus').textContent='Collection synced.'}catch(e){document.getElementById('collectionSyncStatus').textContent='Removed on this browser. Server sync is unavailable; try again when online.'}
 }));
}
document.addEventListener('DOMContentLoaded',async()=>{
 let local=[];let importedLegacy=false;try{local=baNormalizeIds(JSON.parse(localStorage.getItem(BA_LOCAL_KEY)||'[]'))}catch(e){}
 if(BA_ALLOW_LEGACY_IMPORT){try{const legacy=baNormalizeIds(JSON.parse(localStorage.getItem(BA_LEGACY_KEY)||'[]'));if(legacy.length){local=baNormalizeIds([...local,...legacy]);importedLegacy=true}}catch(e){}}
 try{
  const r=await fetch(BA_COLLECTION_API,{credentials:'same-origin',cache:'no-store'});const d=await r.json();if(!r.ok||!d.ok)throw new Error();
  const excluded=new Set(baPendingRemovals());let remote=baNormalizeIds(d.pot_ids||[]);for(const id of excluded){const rr=await fetch(BA_COLLECTION_API,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':BA_CSRF},body:JSON.stringify({action:'remove',pot_id:Number(id)})});const rd=await rr.json();if(!rr.ok||!rd.ok)throw new Error();remote=baNormalizeIds(rd.pot_ids||[]);baWritePendingRemovals(baPendingRemovals().filter(x=>x!==id))}const merged=baNormalizeIds([...remote,...local]).filter(id=>!excluded.has(id));
  if(merged.some(id=>!remote.includes(id))){const sr=await fetch(BA_COLLECTION_API,{method:'POST',credentials:'same-origin',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':BA_CSRF},body:JSON.stringify({action:'sync',pot_ids:merged})});const sd=await sr.json();if(!sr.ok||!sd.ok)throw new Error();baSavedIds=baNormalizeIds(sd.pot_ids||[])}else baSavedIds=remote.filter(id=>!excluded.has(id));
  localStorage.setItem(BA_LOCAL_KEY,JSON.stringify(baSavedIds));if(importedLegacy){try{localStorage.removeItem(BA_LEGACY_KEY)}catch(e){}}document.getElementById('collectionSyncStatus').textContent='Collection synced to your active player profile.';
 }catch(e){baSavedIds=local;document.getElementById('collectionSyncStatus').textContent='Offline or not synced. Showing this browser’s saved copy; reconnect to sync it to your profile.'}
 baRenderCollection();
});
document.addEventListener('bayangan:collection-synced',event=>{if(event.detail&&Array.isArray(event.detail.potIds)){baSavedIds=baNormalizeIds(event.detail.potIds);try{localStorage.setItem(BA_LOCAL_KEY,JSON.stringify(baSavedIds))}catch(e){}baRenderCollection()}});
</script>
<?php include 'partials/footer.php'; ?>

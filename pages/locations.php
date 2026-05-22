<?php
// pages/locations.php – Geospatial Deployment Hub (HRD only)
$pdo = db();
$locations = $pdo->query("SELECT l.*, 
    (SELECT token FROM qr_tokens WHERE location_id = l.id AND is_active = 1 LIMIT 1) as active_token 
    FROM locations l ORDER BY l.name ASC")->fetchAll();
?>

<!-- Include Leaflet CSS/JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<div class="space-y-8 performance-page-container">
    
    <!-- ══ Header Section ══ -->
    <header class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-3xl font-bold">hub</span>
                </div>
                <h1 data-theme-text class="text-4xl font-bold  leading-none">Office Perimeters</h1>
            </div>
            <p data-theme-muted class="text-[10px] font-bold   ml-1 opacity-50">Geofencing & Token Deployment Hub</p>
        </div>
        
        <button onclick="openEditModal()" class="px-6 py-3.5 bg-primary text-white rounded-lg font-bold text-xs   flex items-center gap-2 shadow-xl shadow-primary/20 active:scale-95 transition-all">
            <span class="material-symbols-outlined text-lg">add_location_alt</span>
            <span>Register Site</span>
        </button>
    </header>

    <!-- ══ Site Deployment Grid ══ -->
    <div class="grid grid-cols-1 gap-8 mb-20">
        <?php foreach ($locations as $loc): 
            $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=" . urlencode($loc['active_token'] ?? '');
        ?>
        <div data-theme-card class="bg-surface rounded-lg border border-border shadow-sm overflow-hidden flex flex-col lg:flex-row transition-all hover:shadow-2xl group">
            
            <!-- INFO PANEL: Left Side -->
            <div class="p-10 lg:w-2/3 flex flex-col justify-between relative shadow-inner">
                <div class="absolute top-0 left-0 w-32 h-32 bg-primary/[0.02] rounded-br-[4rem]"></div>
                
                <div class="relative">
                    <div class="flex flex-wrap justify-between items-start gap-4 mb-8">
                        <div>
                            <h2 data-theme-text class="text-2xl font-bold  mb-2"><?= h($loc['name']) ?></h2>
                            <div class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                                <span data-theme-muted class="text-[9px] font-bold   opacity-40">Verified Operational Perimeter</span>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <button onclick='openEditModal(<?= json_encode($loc) ?>)' class="w-12 h-12 rounded-lg bg-surface2 border border-border flex items-center justify-center text-on-surface hover:bg-primary hover:text-white transition-all shadow-sm">
                                <span class="material-symbols-outlined text-xl">edit_location</span>
                            </button>
                            <button onclick="confirmDeleteLoc(<?= $loc['id'] ?>, '<?= h($loc['name']) ?>')" class="w-12 h-12 rounded-lg bg-rose-500/5 border border-rose-500/10 flex items-center justify-center text-rose-500 hover:bg-rose-500 hover:text-white transition-all shadow-sm">
                                <span class="material-symbols-outlined text-xl">location_off</span>
                            </button>
                            <div class="px-5 py-3 rounded-lg bg-surface2 border border-border text-[10px] font-bold text-on-surface  ">
                                SITE-<?= $loc['id'] ?>
                            </div>
                        </div>
                    </div>

                    <!-- Geofencing Breakdown -->
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 mb-8 p-6 bg-surface2 rounded-lg border-none">
                        <div class="space-y-1">
                            <p data-theme-muted class="text-[8px] font-bold   opacity-30">Coordinates</p>
                            <p data-theme-text class="text-[10px] font-bold italic truncate"><?= $loc['latitude'] ?>, <?= $loc['longitude'] ?></p>
                        </div>
                        <div class="space-y-1">
                            <p data-theme-muted class="text-[8px] font-bold   opacity-30">Radius</p>
                            <p data-theme-text class="text-[10px] font-bold"><?= $loc['radius_meters'] ?>m Safe Zone</p>
                        </div>
                        <div class="space-y-1">
                            <p data-theme-muted class="text-[8px] font-bold   opacity-30">Clock-In Window</p>
                            <p class="text-[10px] font-bold text-emerald-500 font-mono "><?= substr($loc['check_in_start'],0,5) ?> - <?= substr($loc['check_in_end'],0,5) ?></p>
                        </div>
                        <div class="space-y-1">
                            <p data-theme-muted class="text-[8px] font-bold   opacity-30">Clock-Out Window</p>
                            <p class="text-[10px] font-bold text-primary font-mono "><?= substr($loc['check_out_start'],0,5) ?> - <?= substr($loc['check_out_end'],0,5) ?></p>
                        </div>
                    </div>
                </div>

                <div class="mt-4 flex items-center gap-4 bg-primary/[0.03] p-5 rounded-lg border border-primary/10">
                    <span class="material-symbols-outlined text-primary font-bold">key</span>
                    <div class="min-w-0">
                        <p data-theme-muted class="text-[8px] font-bold   opacity-30 mb-0.5">Deployment Token (Active)</p>
                        <p data-theme-text class="text-[10px] font-bold font-mono truncate"><?= $loc['active_token'] ?: 'UNSET' ?></p>
                    </div>
                </div>
            </div>

            <!-- QR PANEL: Right Side -->
            <div class="p-10 lg:w-1/3 bg-surface2/30 flex flex-col items-center justify-center gap-8 relative overflow-hidden">
                <div class="absolute -right-8 -bottom-8 w-40 h-40 bg-primary/[0.02] rounded-full blur-3xl"></div>
                
                <div class="w-56 h-56 p-4 bg-white rounded-lg shadow-2xl relative group/qr overflow-hidden border-[12px] border-surface">
                    <img src="<?= $qr_url ?>" alt="QR Code" class="w-full h-full object-contain">
                    <a href="<?= $qr_url ?>&download=1" target="_blank" class="absolute inset-0 bg-black/90 backdrop-blur-md opacity-0 group-hover/qr:opacity-100 transition-all duration-300 flex flex-col items-center justify-center rounded-lg text-white">
                         <span class="material-symbols-outlined text-5xl mb-3 text-primary">download</span>
                         <span class="text-[10px] font-bold  ">Digital Export</span>
                    </a>
                </div>
                
                <button onclick="promptRegenerate(<?= $loc['id'] ?>, '<?= h($loc['name']) ?>')" class="w-full py-4 bg-rose-500/5 text-rose-500 border border-rose-500/10 rounded-lg text-[10px] font-bold   hover:bg-rose-500 hover:text-white transition-all active:scale-95 flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-lg">refresh</span>
                    <span>Invalidate & Rotate Token</span>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- MODAL: Edit/Add Location -->
<div id="editModal" style="display:none;" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/95 backdrop-blur-md p-4 lg:p-10" onclick="if(event.target===this)closeEditModal()">
    <div data-theme-card class="w-full bg-surface rounded-lg border border-border shadow-2xl overflow-hidden flex flex-col animate-in fade-in zoom-in duration-300 h-full lg:h-auto max-h-[95vh]">
        
        <div class="p-8 flex justify-between items-center shadow-sm">
            <div>
                <h3 id="modal-title" data-theme-text class="text-xl font-bold  leading-none mb-1 text-primary">Perimeter Registry</h3>
                <p data-theme-muted class="text-[10px] font-bold   opacity-40">System Geolocation Calibration</p>
            </div>
            <button onclick="closeEditModal()" class="w-12 h-12 rounded-lg flex items-center justify-center text-on-surface/40 hover:bg-surface2 transition-colors"><span class="material-symbols-outlined font-bold">close</span></button>
        </div>

        <form method="POST" action="?page=locations&action=save-location" class="flex flex-col lg:flex-row h-full overflow-hidden">
            <input type="hidden" name="id" id="edit_id">
            
            <!-- Left: Map Selection -->
            <div class="w-full lg:w-3/5 h-64 lg:h-[600px] relative bg-black/20">
                <div id="map" class="w-full h-full z-10"></div>
                <div class="absolute inset-0 pointer-events-none z-20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary text-6xl opacity-30 animate-pulse">location_searching</span>
                </div>
                <!-- Controls -->
                <div class="absolute bottom-10 left-10 z-20 flex flex-col gap-3">
                    <button type="button" onclick="useCurrentLocation()" class="px-6 py-4 bg-white text-black rounded-lg text-[11px] font-bold   flex items-center gap-3 shadow-2xl hover:bg-primary hover:text-white transition-all active:scale-95 border-4 border-white">
                        <span class="material-symbols-outlined text-lg">my_location</span>
                        <span>Sync Current Position</span>
                    </button>
                </div>
            </div>

            <!-- Right: Configs -->
            <div class="w-full lg:w-2/5 p-10 overflow-y-auto space-y-8 bg-surface">
                
                <div class="space-y-6">
                    <div class="space-y-2">
                        <label data-theme-muted class="text-[10px] font-bold   opacity-40 block ml-1">Site Designation</label>
                        <input name="name" id="edit_name" type="text" required class="w-full px-6 py-4 bg-surface2 border-border rounded-lg text-xs font-bold outline-none focus:ring-4 focus:ring-primary/10 transition-all placeholder:opacity-20" placeholder="e.g. Jakarta Main HQ">
                    </div>

                    <div class="grid grid-cols-2 gap-6">
                        <div class="space-y-2">
                            <label data-theme-muted class="text-[10px] font-bold   opacity-40 block ml-1">Latitude</label>
                            <input name="latitude" id="edit_lat" type="text" required class="w-full px-5 py-4 bg-surface2 border-border rounded-lg text-xs font-bold font-mono outline-none">
                        </div>
                        <div class="space-y-2">
                            <label data-theme-muted class="text-[10px] font-bold   opacity-40 block ml-1">Longitude</label>
                            <input name="longitude" id="edit_lng" type="text" required class="w-full px-5 py-4 bg-surface2 border-border rounded-lg text-xs font-bold font-mono outline-none">
                        </div>
                    </div>

                    <div class="space-y-4">
                        <div class="flex justify-between items-center px-1">
                            <label data-theme-muted class="text-[10px] font-bold   opacity-40">Boundary Radius</label>
                            <span id="radius_val" class="text-xs font-bold text-primary">50m</span>
                        </div>
                        <input name="radius_meters" id="edit_radius" type="range" min="10" max="1000" step="10" value="50" oninput="updateRadius(this.value)" class="w-full accent-primary h-2 bg-surface2 rounded-full appearance-none cursor-pointer">
                    </div>
                </div>

                <div class="pt-8">
                    <p data-theme-text class="text-[11px] font-bold   mb-6 flex items-center gap-2">
                        <span class="material-symbols-outlined text-lg text-primary">history_toggle_off</span>
                        <span>Operational Windows</span>
                    </p>
                    <div class="grid grid-cols-2 gap-8">
                        <div class="space-y-3">
                            <label data-theme-muted class="text-[9px] font-bold  opacity-40 block ml-1">Clock-In Hub</label>
                            <input name="check_in_start" id="edit_in_start" type="time" class="w-full px-4 py-3 bg-surface2 border-border rounded-lg text-xs font-bold">
                            <input name="check_in_end" id="edit_in_end" type="time" class="w-full px-4 py-3 bg-surface2 border-border rounded-lg text-xs font-bold">
                        </div>
                        <div class="space-y-3">
                            <label data-theme-muted class="text-[9px] font-bold  opacity-40 block ml-1">Clock-Out Hub</label>
                            <input name="check_out_start" id="edit_out_start" type="time" class="w-full px-4 py-3 bg-surface2 border-border rounded-lg text-xs font-bold">
                            <input name="check_out_end" id="edit_out_end" type="time" class="w-full px-4 py-3 bg-surface2 border-border rounded-lg text-xs font-bold">
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full py-5 bg-primary text-white rounded-full text-xs font-bold   shadow-2xl shadow-primary/30 active:scale-95 transition-all mt-4">
                    Confirm Deployment
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Lokasi -->
<div id="deleteLocModal" style="display:none;" class="fixed inset-0 z-[310] flex items-center justify-center bg-black/90 backdrop-blur-md p-4" onclick="if(event.target===this)closeModal('deleteLocModal')">
    <div data-theme-card class="w-full max-w-sm bg-surface rounded-lg border border-border shadow-2xl overflow-hidden p-8 text-center">
        <div class="w-16 h-16 bg-rose-500/10 text-rose-500 rounded-lg flex items-center justify-center mx-auto mb-6">
            <span class="material-symbols-outlined text-3xl font-bold">wrong_location</span>
        </div>
        <h3 data-theme-text class="text-xl font-bold  mb-2">Decommission Site?</h3>
        <p data-theme-muted class="text-xs font-medium opacity-60 mb-8 leading-relaxed">This will permanently remove <span id="del_loc_name" class="font-bold text-on-surface"></span> from the active perimeter list. Verification services for this site will be disabled.</p>
        
        <div class="grid grid-cols-2 gap-3">
            <button onclick="closeModal('deleteLocModal')" class="py-3.5 bg-surface2 text-on-surface font-bold text-[10px] rounded-lg border border-border">Maintain Site</button>
            <a id="del_loc_btn" href="#" class="py-3.5 bg-rose-500 text-white font-bold text-[10px] rounded-lg shadow-lg shadow-rose-500/20">Decommission</a>
        </div>
    </div>
</div>

<!-- Security Modal: Admin Verification -->
<div id="secureModal" style="display:none;" class="fixed inset-0 z-[300] flex items-center justify-center bg-black/98 backdrop-blur-2xl p-6" onclick="if(event.target===this)this.style.display='none'">
    <div data-theme-card class="w-full max-w-sm bg-surface rounded-lg border border-border shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
        <div class="p-8 bg-rose-500/5 flex items-center gap-4">
             <div class="w-12 h-12 rounded-lg bg-rose-500/10 flex items-center justify-center text-rose-500">
                <span class="material-symbols-outlined text-2xl font-bold">security_update_warning</span>
             </div>
             <div>
                 <h3 class="text-sm font-bold text-rose-500  ">Authority Verification</h3>
                 <p class="text-[9px] font-bold  opacity-30">Reset Token Protocol</p>
             </div>
        </div>
        <form method="POST" action="?page=locations&action=generate-qr" class="p-10 space-y-8 text-center">
            <input type="hidden" name="location_id" id="modal_loc_id">
            <p data-theme-muted class="text-xs font-bold leading-relaxed opacity-60">This will immediately invalidate the current QR at <span id="modal_loc_name" data-theme-text class="font-bold"></span>. All physical deployments for this site must be replaced.</p>
            
            <div class="space-y-6">
                <input name="admin_password" type="password" required class="w-full px-6 py-5 bg-surface2 border-border border rounded-lg text-center text-sm font-bold outline-none focus:ring-4 focus:ring-rose-500/10" placeholder="VERIFY PASSWORD">
                <button type="submit" class="w-full py-5 bg-rose-500 text-white rounded-full text-xs font-bold   shadow-2xl shadow-rose-500/30 active:scale-95 transition-all">
                    Authorize & Rotate
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let map = null, marker = null, circle = null;

function initMap(lat = -6.2088, lng = 106.8456, radius = 50) {
    if (!map) {
        map = L.map('map').setView([lat, lng], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap',
            className: document.documentElement.classList.contains('dark') ? 'map-dark' : ''
        }).addTo(map);
        map.on('click', e => updateMarker(e.latlng.lat, e.latlng.lng));
    } else { map.setView([lat, lng], 16); }
    updateMarker(lat, lng, radius);
}

function useCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(pos => {
            updateMarker(pos.coords.latitude, pos.coords.longitude);
            map.flyTo([pos.coords.latitude, pos.coords.longitude], 18);
        });
    }
}

function updateMarker(lat, lng, r = null) {
    const radius = r || document.getElementById('edit_radius').value;
    if (!marker) marker = L.marker([lat, lng]).addTo(map); else marker.setLatLng([lat, lng]);
    if (!circle) circle = L.circle([lat, lng], { color: '#00f2ff', fillColor: '#00f2ff', fillOpacity: 0.1, radius }).addTo(map);
    else { circle.setLatLng([lat, lng]); circle.setRadius(radius); }
    document.getElementById('edit_lat').value = lat.toFixed(8);
    document.getElementById('edit_lng').value = lng.toFixed(8);
}

function updateRadius(v) { document.getElementById('radius_val').innerText = v + 'm'; if (circle) circle.setRadius(v); }

function openEditModal(d = null) {
    document.getElementById('edit_id').value = d?d.id:'';
    document.getElementById('modal-title').innerText = d?'Edit Site':'New Site';
    document.getElementById('edit_name').value = d?d.name:'';
    document.getElementById('edit_lat').value = d?d.latitude:-6.2088;
    document.getElementById('edit_lng').value = d?d.longitude:106.8456;
    document.getElementById('edit_radius').value = d?d.radius_meters:50;
    document.getElementById('radius_val').innerText = (d?d.radius_meters:50) + 'm';
    document.getElementById('edit_in_start').value = d?d.check_in_start:'07:00';
    document.getElementById('edit_in_end').value = d?d.check_in_end:'10:00';
    document.getElementById('edit_out_start').value = d?d.check_out_start:'16:00';
    document.getElementById('edit_out_end').value = d?d.check_out_end:'20:00';
    document.getElementById('editModal').style.display = 'flex';
    setTimeout(() => { initMap(parseFloat(document.getElementById('edit_lat').value), parseFloat(document.getElementById('edit_lng').value), parseInt(document.getElementById('edit_radius').value)); map.invalidateSize(); }, 400);
}

function closeEditModal() { document.getElementById('editModal').style.display = 'none'; }
function promptRegenerate(id, name) { document.getElementById('modal_loc_id').value = id; document.getElementById('modal_loc_name').innerText = name; document.getElementById('secureModal').style.display = 'flex'; }
function confirmDeleteLoc(id, name) {
    document.getElementById('del_loc_name').innerText = name;
    document.getElementById('del_loc_btn').href = `?page=locations&action=delete&id=${id}`;
    openModal('deleteLocModal');
}
</script>

<style>
    .leaflet-container { font-family: inherit; z-index: 10; border-radius: 0; }
    html.dark .map-dark { filter: invert(100%) hue-rotate(180deg) brightness(85%) contrast(85%); }
</style>

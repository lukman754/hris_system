<?php
// pages/locations.php – Manajemen QR Office & Geofencing
$pdo = db();
$locations = $pdo->query("SELECT l.*, 
    (SELECT token FROM qr_tokens WHERE location_id = l.id AND is_active = 1 LIMIT 1) as active_token 
    FROM locations l ORDER BY l.name ASC")->fetchAll();
?>

<!-- Include Leaflet CSS/JS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>

<section class="mb-8 flex justify-between items-end">
    <div>
        <h1 class="text-3xl font-black tracking-tighter text-on-surface leading-[1.1] mb-2">Office Perimeter</h1>
        <p class="text-[10px] font-black text-secondary opacity-60 uppercase tracking-[0.3em]">QR Token & Geofencing Management</p>
    </div>
    <button onclick="openEditModal()" class="flex items-center gap-2 px-6 py-3 bg-primary text-white rounded-full text-[10px] font-black uppercase tracking-widest shadow-lg shadow-primary/20 hover:scale-105 transition-transform active:scale-95">
        <span class="material-symbols-outlined text-base">add_location_alt</span>
        Register New Site
    </button>
</section>

<div class="grid grid-cols-1 gap-8 mb-20 px-1">
    <?php foreach ($locations as $loc): 
        $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=" . urlencode($loc['active_token'] ?? '');
    ?>
    <div class="bg-surface-container-low rounded-[3rem] border border-outline-variant/10 shadow-sm overflow-hidden flex flex-col lg:flex-row transition-all hover:bg-surface-container hover:shadow-xl group">
        
        <!-- INFO PANEL: Left Side -->
        <div class="p-8 md:p-10 lg:w-2/3 flex flex-col justify-between border-b lg:border-b-0 lg:border-r border-outline-variant/5">
            <div>
                <div class="flex flex-wrap justify-between items-start gap-4 mb-8">
                    <div>
                        <h2 class="text-2xl font-black text-on-surface tracking-tighter mb-2"><?= h($loc['name']) ?></h2>
                        <div class="flex items-center gap-2">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                            <span class="text-[9px] font-black text-secondary uppercase tracking-[0.2em] opacity-60">Verified Office Perimeter</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick='openEditModal(<?= json_encode($loc) ?>)' class="w-11 h-11 rounded-full bg-surface-container-high border border-outline-variant/5 flex items-center justify-center text-on-surface hover:bg-primary hover:text-white transition-all shadow-sm">
                            <span class="material-symbols-outlined text-lg">edit</span>
                        </button>
                        <div class="px-5 py-2.5 rounded-full bg-surface-container-high border border-outline-variant/5 text-[10px] font-black text-on-surface uppercase tracking-widest">
                            loc-<?= $loc['id'] ?>
                        </div>
                    </div>
                </div>

                <!-- Geofencing & Operational Ledger -->
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 mb-8 p-6 bg-surface-container/30 rounded-[2rem] border border-outline-variant/5">
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-secondary opacity-30 uppercase tracking-widest">Coordinates</p>
                        <p class="text-[11px] font-black text-on-surface italic"><?= $loc['latitude'] ?>, <?= $loc['longitude'] ?></p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-secondary opacity-30 uppercase tracking-widest">Safe Radius</p>
                        <p class="text-[11px] font-black text-on-surface"><?= $loc['radius_meters'] ?> Meters</p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-secondary opacity-30 uppercase tracking-widest">Check-In</p>
                        <p class="text-[11px] font-black text-emerald-500 font-mono"><?= substr($loc['check_in_start'],0,5) ?> - <?= substr($loc['check_in_end'],0,5) ?></p>
                    </div>
                    <div class="space-y-1">
                        <p class="text-[9px] font-black text-secondary opacity-30 uppercase tracking-widest">Check-Out</p>
                        <p class="text-[11px] font-black text-blue-500 font-mono"><?= substr($loc['check_out_start'],0,5) ?> - <?= substr($loc['check_out_end'],0,5) ?></p>
                    </div>
                </div>
            </div>

            <div class="mt-4 flex items-center gap-4 bg-black/10 p-4 rounded-2xl border border-white/5">
                <span class="material-symbols-outlined text-primary opacity-60">lock_open</span>
                <div class="min-w-0">
                    <p class="text-[8px] font-black text-secondary opacity-30 uppercase tracking-tighter">Current Deployment Token</p>
                    <p class="text-[10px] font-bold text-on-surface/50 font-mono truncate"><?= $loc['active_token'] ?: 'UNSET' ?></p>
                </div>
            </div>
        </div>

        <!-- QR PANEL: Right Side -->
        <div class="p-8 md:p-10 lg:w-1/3 bg-surface-container-high/20 flex flex-col items-center justify-center gap-6">
            <div class="w-48 h-48 sm:w-56 sm:h-56 p-4 bg-white rounded-[2.5rem] shadow-2xl relative group/qr overflow-hidden border-8 border-surface-container-high">
                <img src="<?= $qr_url ?>" alt="QR Code" class="w-full h-full object-contain">
                <a href="<?= $qr_url ?>&download=1" target="_blank" class="absolute inset-0 bg-black/80 backdrop-blur-sm opacity-0 group-hover/qr:opacity-100 transition-opacity flex flex-col items-center justify-center rounded-[2rem] text-white">
                     <span class="material-symbols-outlined text-4xl mb-2">download</span>
                     <span class="text-[8px] font-black uppercase tracking-[0.3em]">Export PNG</span>
                </a>
            </div>
            
            <div class="w-full flex flex-col gap-3">
                <button onclick="promptRegenerate(<?= $loc['id'] ?>, '<?= h($loc['name']) ?>')" class="w-full py-4 bg-error/5 text-error border border-error/10 rounded-full text-[10px] font-black uppercase tracking-widest hover:bg-error hover:text-white transition-all active:scale-95 flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-base">refresh</span>
                    Regenerate Token
                </button>
            </div>
        </div>

    </div>
    <?php endforeach; ?>
</div>

<!-- MODAL: Edit/Add Location -->
<div id="editModal" class="hidden fixed inset-0 z-[200] flex items-center justify-center bg-black/80 backdrop-blur-md p-4 sm:p-10" onclick="if(event.target===this)closeEditModal()">
    <div class="w-full bg-surface-container rounded-[3rem] border border-outline-variant/20 shadow-2xl overflow-hidden flex flex-col animate-in fade-in zoom-in duration-300 h-full sm:h-auto max-h-[95vh]">
        
        <div class="p-6 flex justify-between items-center border-b border-outline-variant/10">
            <div>
                <h3 id="modal-title" class="text-sm font-black text-primary uppercase tracking-widest">Register New Perimeter</h3>
                <p class="text-[9px] text-secondary font-bold uppercase opacity-50">Set coordinates and operational windows</p>
            </div>
            <button onclick="closeEditModal()" class="material-symbols-outlined text-on-surface/40 hover:bg-surface-container-high p-2 rounded-full transition-colors">close</button>
        </div>

        <form method="POST" action="?page=locations&action=save-location" class="flex flex-col md:flex-row h-full overflow-hidden">
            <input type="hidden" name="id" id="edit_id">
            
            <!-- Left: Map Selection -->
            <div class="w-full md:w-3/5 h-64 md:h-auto bg-black/10 relative">
                <div id="map" class="w-full h-full z-10"></div>
                <!-- Crosshair overlay -->
                <div class="absolute inset-0 pointer-events-none z-20 flex items-center justify-center">
                    <span class="material-symbols-outlined text-primary text-4xl opacity-40">location_searching</span>
                </div>
                <!-- Controls Overlay -->
                <div class="absolute bottom-6 left-6 z-20 flex flex-col gap-3">
                    <button type="button" onclick="useCurrentLocation()" class="bg-white text-black px-5 py-3 rounded-2xl text-[10px] font-black uppercase tracking-widest flex items-center gap-2 shadow-2xl hover:bg-primary hover:text-white transition-all transform hover:scale-105 active:scale-95 border border-white/20">
                        <span class="material-symbols-outlined text-base">my_location</span>
                        My Current Position
                    </button>
                    <div class="bg-black/40 backdrop-blur-md px-4 py-2 rounded-xl text-[9px] font-black text-white uppercase tracking-widest border border-white/10 w-fit">
                        Click map to pick location
                    </div>
                </div>
            </div>

            <!-- Right: Configs -->
            <div class="w-full md:w-2/5 p-8 overflow-y-auto space-y-6 bg-surface-container">
                
                <div class="space-y-4">
                    <div>
                        <label class="text-[9px] font-black text-secondary uppercase mb-2 block px-2">Location Name</label>
                        <input name="name" id="edit_name" type="text" required class="w-full px-5 py-4 rounded-2xl bg-surface-container-low border border-outline-variant/20 text-sm font-bold text-on-surface focus:border-primary focus:ring-0 transition-colors" placeholder="e.g. Jakarta HQ">
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-[9px] font-black text-secondary uppercase mb-2 block px-2">Latitude</label>
                            <input name="latitude" id="edit_lat" type="text" required class="w-full px-5 py-3 rounded-2xl bg-surface-container-low border border-outline-variant/20 text-xs font-bold text-on-surface font-mono" placeholder="-6.xxx">
                        </div>
                        <div>
                            <label class="text-[9px] font-black text-secondary uppercase mb-2 block px-2">Longitude</label>
                            <input name="longitude" id="edit_lng" type="text" required class="w-full px-5 py-3 rounded-2xl bg-surface-container-low border border-outline-variant/20 text-xs font-bold text-on-surface font-mono" placeholder="106.xxx">
                        </div>
                    </div>

                    <div>
                        <label class="text-[9px] font-black text-secondary uppercase mb-2 block px-2 flex justify-between">
                            <span>Attendance Radius</span>
                            <span id="radius_val" class="text-primary font-black">50m</span>
                        </label>
                        <input name="radius_meters" id="edit_radius" type="range" min="10" max="1000" step="10" value="50" oninput="updateRadius(this.value)" class="w-full accent-primary">
                    </div>
                </div>

                <div class="pt-4 border-t border-outline-variant/10">
                    <p class="text-[10px] font-black text-primary uppercase tracking-widest mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-base">schedule</span>
                        Operation Windows
                    </p>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-3">
                            <label class="text-[9px] font-black text-secondary uppercase px-2">Check-In</label>
                            <input name="check_in_start" id="edit_in_start" type="time" class="w-full px-4 py-3 rounded-xl bg-surface-container-low border border-outline-variant/10 text-xs font-bold text-on-surface">
                            <input name="check_in_end" id="edit_in_end" type="time" class="w-full px-4 py-3 rounded-xl bg-surface-container-low border border-outline-variant/10 text-xs font-bold text-on-surface">
                        </div>
                        <div class="space-y-3">
                            <label class="text-[9px] font-black text-secondary uppercase px-2">Check-Out</label>
                            <input name="check_out_start" id="edit_out_start" type="time" class="w-full px-4 py-3 rounded-xl bg-surface-container-low border border-outline-variant/10 text-xs font-bold text-on-surface">
                            <input name="check_out_end" id="edit_out_end" type="time" class="w-full px-4 py-3 rounded-xl bg-surface-container-low border border-outline-variant/10 text-xs font-bold text-on-surface">
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full py-5 bg-primary text-white rounded-full text-xs font-black uppercase shadow-xl shadow-primary/30 active:scale-95 transition-all mt-4">
                    Save Site Configuration
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Security Modal: Admin Verification -->
<div id="secureModal" class="hidden fixed inset-0 z-[300] flex items-center justify-center bg-black/80 backdrop-blur-md p-6" onclick="if(event.target===this)this.classList.add('hidden')">
    <div class="w-full max-w-sm bg-surface-container rounded-[2.5rem] border border-outline-variant/20 shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
        <div class="p-6 bg-error/10 border-b border-outline-variant/10 flex items-center gap-4">
             <div class="w-10 h-10 rounded-full bg-error/20 flex items-center justify-center text-error">
                <span class="material-symbols-outlined">security_update_warning</span>
             </div>
             <h3 class="text-sm font-black text-error uppercase tracking-widest">Confim Reset</h3>
        </div>
        <form method="POST" action="?page=locations&action=generate-qr" class="p-8 space-y-6">
            <input type="hidden" name="location_id" id="modal_loc_id">
            <p class="text-xs text-secondary font-bold leading-relaxed px-1 italic">Mereset QR akan menyebabkan QR fisik di <span id="modal_loc_name" class="text-on-surface not-italic"></span> menjadi TIDAK BERGUNA.</p>
            
            <div class="space-y-4">
                <input name="admin_password" type="password" required class="w-full px-5 py-4 rounded-2xl bg-surface-container-low border border-outline-variant/20 text-sm font-bold text-on-surface focus:border-error focus:ring-0 transition-colors" placeholder="Enter Admin Password">
                <button type="submit" class="w-full py-5 bg-error text-white rounded-full text-xs font-black uppercase shadow-xl shadow-error/30 active:scale-95 transition-all">
                    Invalidate & Generate New
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let map = null;
let marker = null;
let circle = null;

function initMap(lat = -6.2088, lng = 106.8456, radius = 50) {
    if (!map) {
        map = L.map('map').setView([lat, lng], 16);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '© OpenStreetMap'
        }).addTo(map);

        map.on('click', function(e) {
            updateMarker(e.latlng.lat, e.latlng.lng);
        });
    } else {
        map.setView([lat, lng], 16);
    }

    updateMarker(lat, lng, radius);
}

function useCurrentLocation() {
    if ("geolocation" in navigator) {
        navigator.geolocation.getCurrentPosition((pos) => {
            const lat = pos.coords.latitude;
            const lng = pos.coords.longitude;
            updateMarker(lat, lng);
            map.flyTo([lat, lng], 18);
        }, (err) => alert("Gagal mengambil lokasi: " + err.message));
    } else {
        alert("Geolocation tidak didukung di browser ini.");
    }
}

function updateMarker(lat, lng, radius = null) {
    if (radius === null) radius = document.getElementById('edit_radius').value;
    
    if (!marker) {
        marker = L.marker([lat, lng]).addTo(map);
    } else {
        marker.setLatLng([lat, lng]);
    }

    if (!circle) {
        circle = L.circle([lat, lng], {
            color: '#1a73e8',
            fillColor: '#1a73e8',
            fillOpacity: 0.15,
            radius: radius
        }).addTo(map);
    } else {
        circle.setLatLng([lat, lng]);
        circle.setRadius(radius);
    }

    document.getElementById('edit_lat').value = lat.toFixed(8);
    document.getElementById('edit_lng').value = lng.toFixed(8);
}

function updateRadius(val) {
    document.getElementById('radius_val').innerText = val + 'm';
    if (circle) circle.setRadius(val);
}

function openEditModal(data = null) {
    document.getElementById('edit_id').value = data ? data.id : '';
    document.getElementById('modal-title').innerText = data ? 'Edit Perimeter Site' : 'Register New Perimeter';
    document.getElementById('edit_name').value = data ? data.name : '';
    document.getElementById('edit_lat').value = data ? data.latitude : -6.2088;
    document.getElementById('edit_lng').value = data ? data.longitude : 106.8456;
    document.getElementById('edit_radius').value = data ? data.radius_meters : 50;
    document.getElementById('radius_val').innerText = (data ? data.radius_meters : 50) + 'm';
    
    document.getElementById('edit_in_start').value = data ? data.check_in_start : '07:00';
    document.getElementById('edit_in_end').value = data ? data.check_in_end : '10:00';
    document.getElementById('edit_out_start').value = data ? data.check_out_start : '16:00';
    document.getElementById('edit_out_end').value = data ? data.check_out_end : '20:00';

    document.getElementById('editModal').classList.remove('hidden');
    
    setTimeout(() => {
        initMap(
            parseFloat(document.getElementById('edit_lat').value), 
            parseFloat(document.getElementById('edit_lng').value),
            parseInt(document.getElementById('edit_radius').value)
        );
        map.invalidateSize();
    }, 400);
}

function closeEditModal() {
    document.getElementById('editModal').classList.add('hidden');
}

function promptRegenerate(id, name) {
    document.getElementById('modal_loc_id').value = id;
    document.getElementById('modal_loc_name').innerText = name;
    document.getElementById('secureModal').classList.remove('hidden');
}

// Coordinate manual input listener
document.getElementById('edit_lat').addEventListener('change', function() {
    updateMarker(parseFloat(this.value), parseFloat(document.getElementById('edit_lng').value));
});
document.getElementById('edit_lng').addEventListener('change', function() {
    updateMarker(parseFloat(document.getElementById('edit_lat').value), parseFloat(this.value));
});
</script>

<style>
    .leaflet-container { font-family: inherit; z-index: 1; }
    .bg-surface-container-low { background-color: rgb(var(--color-surface-container-low)); }
</style>

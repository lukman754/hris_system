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

<div class="flex flex-col xl:flex-row gap-8">
    
    <!-- LEFT SIDE: Main content (Locations list) -->
    <div class="w-full xl:w-6/12 2xl:w-7/12 space-y-6 print:hidden">
        <!-- Header Section -->
        <header class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-12 h-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-3xl font-bold">location_on</span>
                    </div>
                    <h1 data-theme-text class="text-3xl font-bold leading-none">Office Perimeters</h1>
                </div>
                <p data-theme-muted class="text-[10px] font-bold ml-1 opacity-50">Geofencing & Token Deployment Hub</p>
            </div>
            <button onclick="openEditModal()" class="px-6 py-3.5 bg-primary text-white rounded-lg font-bold text-xs flex items-center gap-2 shadow-xl shadow-primary/20 active:scale-95 transition-all">
                <span class="material-symbols-outlined text-lg">add_location_alt</span>
                <span>Register Site</span>
            </button>
        </header>

        <!-- Site Deployment Grid -->
        <div class="grid grid-cols-1 gap-6">
            <?php foreach ($locations as $loc): 
                $qr_url = "https://api.qrserver.com/v1/create-qr-code/?size=400x400&data=" . urlencode($loc['active_token'] ?? '');
            ?>
            <div class="bg-[var(--surface)] rounded-lg flex flex-col overflow-hidden" style="border:1px solid var(--border); box-shadow:var(--shadow);">
                <!-- Card Header -->
                <div class="px-5 py-4 border-b bg-[var(--surface2)] flex justify-between items-start" style="border-color:var(--border);">
                    <div>
                        <h3 class="font-bold text-[15px] text-[var(--text-primary)] leading-tight"><?= h($loc['name']) ?></h3>
                        <div class="flex items-center gap-1 mt-1.5">
                            <span class="w-1.5 h-1.5 rounded-full bg-green-500 animate-pulse"></span>
                            <span class="text-[10px] font-bold text-[var(--text-muted)]">Verified Perimeter</span>
                        </div>
                    </div>
                    <div class="flex items-center gap-2">
                        <button onclick='openEditModal(<?= json_encode($loc) ?>)' class="w-7 h-7 flex items-center justify-center rounded bg-[var(--surface)] border text-[var(--text-muted)] hover:text-[var(--primary)] transition-colors" style="border-color:var(--border);" title="Edit Site">
                            <span class="material-symbols-outlined text-[16px]">edit_location</span>
                        </button>
                        <button onclick="confirmDeleteLoc(<?= $loc['id'] ?>, '<?= h($loc['name']) ?>')" class="w-7 h-7 flex items-center justify-center rounded bg-[var(--surface)] border text-red-500 hover:bg-red-500 hover:text-white transition-colors" style="border-color:var(--border);" title="Decommission Site">
                            <span class="material-symbols-outlined text-[16px]">location_off</span>
                        </button>
                    </div>
                </div>

                <!-- Card Body -->
                <div class="p-5 flex flex-col md:flex-row gap-6">
                    <!-- Info Section -->
                    <div class="flex-1 space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <p class="text-[10px] text-[var(--text-muted)] mb-0.5">Coordinates</p>
                                <p class="text-[12px] font-medium text-[var(--text-primary)] truncate" title="<?= $loc['latitude'] ?>, <?= $loc['longitude'] ?>"><?= substr($loc['latitude'], 0, 8) ?>, <?= substr($loc['longitude'], 0, 8) ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-[var(--text-muted)] mb-0.5">Radius</p>
                                <p class="text-[12px] font-medium text-[var(--text-primary)]"><?= $loc['radius_meters'] ?>m Zone</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-[var(--text-muted)] mb-0.5">Clock-In Window</p>
                                <p class="text-[12px] font-medium text-[var(--text-primary)]"><?= substr($loc['check_in_start'],0,5) ?> - <?= substr($loc['check_in_end'],0,5) ?></p>
                            </div>
                            <div>
                                <p class="text-[10px] text-[var(--text-muted)] mb-0.5">Clock-Out Window</p>
                                <p class="text-[12px] font-medium text-[var(--text-primary)]"><?= substr($loc['check_out_start'],0,5) ?> - <?= substr($loc['check_out_end'],0,5) ?></p>
                            </div>
                        </div>
                        
                        <div class="pt-2 border-t" style="border-color:var(--border);">
                            <p class="text-[10px] text-[var(--text-muted)] mb-1">Active Deployment Token</p>
                            <div class="bg-[var(--surface2)] px-3 py-2 rounded border font-mono text-[11px] text-[var(--text-primary)] break-all" style="border-color:var(--border);">
                                <?= $loc['active_token'] ?: 'UNSET' ?>
                            </div>
                        </div>
                    </div>

                    <!-- Action Section -->
                    <div class="w-full md:w-32 flex flex-col items-center justify-center gap-3 shrink-0">
                        <button onclick="previewQR('<?= h(addslashes($loc['name'])) ?>', '<?= $loc['active_token'] ?>')" class="w-full flex items-center justify-center gap-1 py-2 bg-[var(--primary)] text-white border border-[var(--primary)] rounded text-[11px] font-bold hover:opacity-90 transition-opacity shadow-lg shadow-blue-500/20">
                            <span class="material-symbols-outlined text-[16px]">visibility</span> Preview A4
                        </button>
                        <div class="flex flex-col gap-1 w-full">
                            <a href="<?= $qr_url ?>&download=1" target="_blank" class="w-full flex items-center justify-center gap-1 py-1.5 bg-[var(--surface2)] text-[var(--text-primary)] border rounded text-[10px] font-bold hover:bg-[var(--primary)] hover:text-white transition-colors" style="border-color:var(--border);">
                                <span class="material-symbols-outlined text-[14px]">download</span> Download
                            </a>
                            <button onclick="promptRegenerate(<?= $loc['id'] ?>, '<?= h(addslashes($loc['name'])) ?>')" class="w-full flex items-center justify-center gap-1 py-1.5 bg-[var(--surface2)] text-red-500 border rounded text-[10px] font-bold hover:bg-red-500 hover:text-white transition-colors" style="border-color:var(--border);">
                                <span class="material-symbols-outlined text-[14px]">refresh</span> Rotate
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- RIGHT SIDE: Sticky A4 Print Preview -->
    <div class="w-full xl:w-6/12 2xl:w-5/12 print:w-full print:h-auto print:p-0 print:overflow-visible">
        <div class="sticky top-6 flex flex-col gap-4">
             <!-- Print Toolbar -->
             <div class="p-5 bg-[var(--surface)] border rounded-lg flex flex-col sm:flex-row sm:items-center justify-between gap-4 print:hidden" style="border-color:var(--border); box-shadow:var(--shadow);">
                 <div>
                     <h3 class="font-bold text-[15px] text-[var(--text-primary)]">Print Preview</h3>
                     <p id="setting-office-name" class="text-[11px] text-[var(--text-muted)] mt-0.5">Pilih lokasi di sebelah kiri untuk melihat preview</p>
                 </div>
                 <div class="flex items-center gap-3">
                     <label class="flex items-center gap-2 cursor-pointer" title="Sertakan Header Logo">
                         <input type="checkbox" checked onchange="togglePrintHeader(this.checked)" class="w-4 h-4 rounded border-[var(--border)] text-[var(--primary)] focus:ring-[var(--primary)] bg-[var(--surface2)]">
                         <span class="text-[11px] font-medium text-[var(--text-primary)]">Header</span>
                     </label>
                     <label class="flex items-center gap-2 cursor-pointer" title="Sertakan Instruksi Footer">
                         <input type="checkbox" checked onchange="togglePrintFooter(this.checked)" class="w-4 h-4 rounded border-[var(--border)] text-[var(--primary)] focus:ring-[var(--primary)] bg-[var(--surface2)]">
                         <span class="text-[11px] font-medium text-[var(--text-primary)]">Footer</span>
                     </label>
                     <button onclick="window.print()" class="px-4 py-2 ml-2 bg-[var(--primary)] text-white rounded text-[12px] font-bold hover:opacity-90 transition-opacity flex items-center gap-1.5 shadow-md">
                         <span class="material-symbols-outlined text-[16px]">print</span> Print A4
                     </button>
                 </div>
             </div>

             <!-- A4 Paper Wrapper for Scaling -->
             <div class="w-full overflow-hidden flex justify-center bg-gray-200 dark:bg-black/20 rounded-lg border border-[var(--border)] print:border-none print:bg-white print:rounded-none" style="height: 600px;">
                 <div class="w-full h-full overflow-y-auto flex justify-center pt-8 pb-16 print:p-0 print:overflow-visible">
                     
                     <!-- The actual paper/page to print -->
                     <div id="print-qr-area" class="bg-white text-black p-12 shadow-2xl print:shadow-none print:transform-none flex flex-col items-center justify-center text-center opacity-50 transition-opacity duration-300">
                         
                         <!-- LOGO & COMPANY NAME -->
                         <div id="print-header" class="flex flex-col items-center mb-8 w-full">
                             <div class="w-28 h-28 rounded-full bg-[#2563EB] text-white flex items-center justify-center mb-4">
                                 <span class="material-symbols-outlined text-[64px]">flight</span>
                             </div>
                             <h1 class="text-[36px] font-extrabold tracking-wide text-gray-900 mb-1 leading-tight">PERKASA ABADI LOGISTIK</h1>
                             <h2 class="text-xl font-bold tracking-widest text-gray-500 uppercase">ABSENSI KARYAWAN</h2>
                         </div>

                         <!-- QR CODE -->
                         <div class="my-6 p-6 border-8 border-gray-950 rounded-[2rem] bg-white w-[420px] h-[420px] flex items-center justify-center">
                             <img id="print-qr-img" src="https://api.qrserver.com/v1/create-qr-code/?size=380x380&data=SELECT_LOCATION" class="w-[380px] h-[380px] object-contain opacity-20" alt="QR Code" />
                         </div>

                         <!-- METADATA -->
                         <div class="w-full max-w-[500px] text-left border-y-2 border-dashed border-gray-400 py-6 my-6 space-y-4 text-lg font-semibold text-gray-700">
                             <div class="flex justify-between items-center">
                                 <span class="text-gray-500">Lokasi :</span>
                                 <span id="print-office-name" class="text-gray-900 text-2xl font-bold">---</span>
                             </div>
                             <div class="flex justify-between items-center">
                                 <span class="text-gray-500">Dibuat :</span>
                                 <span id="print-date" class="text-gray-900 text-xl font-bold">---</span>
                             </div>
                             <div class="flex justify-between items-center">
                                 <span class="text-gray-500">Status :</span>
                                 <span class="text-green-600 text-xl font-extrabold">Aktif</span>
                             </div>
                         </div>

                         <!-- FOOTER INSTRUCTION -->
                         <div id="print-footer" class="mt-6 space-y-3 text-center w-full max-w-[500px]">
                             <p class="text-2xl text-gray-900 font-extrabold leading-snug">Scan melalui aplikasi presensi<br/>dan pastikan GPS aktif.</p>
                             <p class="text-lg text-gray-400 font-semibold leading-relaxed mt-2">QR dapat berubah sewaktu-waktu<br/>sesuai kebijakan perusahaan.</p>
                         </div>

                         <!-- TOKEN -->
                         <div id="print-token" class="mt-8 text-xl font-mono tracking-widest text-gray-600 font-bold bg-gray-100 px-8 py-3 rounded-full border-2 border-gray-300">
                             Token : ----
                         </div>
                         
                     </div>
                 </div>
             </div>
        </div>
    </div>
</div>

<!-- MODAL: Edit/Add Location -->
<div id="editModal" style="display:none;" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/50 p-4" onclick="if(event.target===this)closeEditModal()">
    <div class="w-full max-w-4xl bg-[var(--surface)] rounded-lg flex flex-col h-[90vh] md:h-[600px] shadow-2xl" style="border:1px solid var(--border);">
        <div class="px-6 py-4 border-b bg-[var(--surface2)] flex justify-between items-center rounded-t-lg" style="border-color:var(--border);">
            <div>
                <h3 id="modal-title" class="font-bold text-[16px] text-[var(--text-primary)]">Perimeter Registry</h3>
                <p class="text-[11px] text-[var(--text-muted)]">System Geolocation Calibration</p>
            </div>
            <button onclick="closeEditModal()" class="text-[var(--text-muted)] hover:text-[var(--primary)] transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form method="POST" action="?page=locations&action=save-location" class="flex flex-col md:flex-row flex-1 overflow-hidden">
            <input type="hidden" name="id" id="edit_id">
            
            <!-- Left: Map Selection -->
            <div class="w-full md:w-1/2 h-64 md:h-full relative border-b md:border-b-0 md:border-r" style="border-color:var(--border);">
                <div id="map" class="w-full h-full z-10"></div>
                <div class="absolute bottom-4 left-4 z-20">
                    <button type="button" onclick="useCurrentLocation()" class="px-3 py-2 bg-white text-black rounded text-[11px] font-bold flex items-center gap-2 shadow hover:bg-gray-50 transition-colors border border-gray-200">
                        <span class="material-symbols-outlined text-[16px]">my_location</span>
                        Sync Current Position
                    </button>
                </div>
            </div>

            <!-- Right: Configs -->
            <div class="w-full md:w-1/2 p-6 overflow-y-auto space-y-5">
                <div class="space-y-1.5">
                    <label class="text-[11px] font-bold text-[var(--text-muted)]">Site Designation</label>
                    <input name="name" id="edit_name" type="text" required class="w-full px-3 py-2 bg-[var(--surface)] border rounded text-[13px] text-[var(--text-primary)] focus:outline-none focus:border-[var(--primary)]" style="border-color:var(--border);" placeholder="e.g. Jakarta Main HQ">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-[var(--text-muted)]">Latitude</label>
                        <input name="latitude" id="edit_lat" type="text" required class="w-full px-3 py-2 bg-[var(--surface)] border rounded text-[13px] text-[var(--text-primary)] font-mono focus:outline-none focus:border-[var(--primary)]" style="border-color:var(--border);">
                    </div>
                    <div class="space-y-1.5">
                        <label class="text-[11px] font-bold text-[var(--text-muted)]">Longitude</label>
                        <input name="longitude" id="edit_lng" type="text" required class="w-full px-3 py-2 bg-[var(--surface)] border rounded text-[13px] text-[var(--text-primary)] font-mono focus:outline-none focus:border-[var(--primary)]" style="border-color:var(--border);">
                    </div>
                </div>

                <div class="space-y-1.5">
                    <div class="flex justify-between items-center">
                        <label class="text-[11px] font-bold text-[var(--text-muted)]">Boundary Radius</label>
                        <span id="radius_val" class="text-[11px] font-bold text-[var(--primary)]">50m</span>
                    </div>
                    <input name="radius_meters" id="edit_radius" type="range" min="10" max="1000" step="10" value="50" oninput="updateRadius(this.value)" class="w-full accent-[var(--primary)] h-1.5 bg-[var(--surface2)] rounded-full appearance-none cursor-pointer">
                </div>

                <div class="pt-4 border-t" style="border-color:var(--border);">
                    <p class="text-[12px] font-bold text-[var(--text-primary)] mb-3 flex items-center gap-1">
                        <span class="material-symbols-outlined text-[16px]">history_toggle_off</span> Operational Windows
                    </p>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-[var(--text-muted)] block">Clock-In Hub</label>
                            <input name="check_in_start" id="edit_in_start" type="time" class="w-full px-2.5 py-1.5 bg-[var(--surface)] border rounded text-[12px] text-[var(--text-primary)]" style="border-color:var(--border);">
                            <input name="check_in_end" id="edit_in_end" type="time" class="w-full px-2.5 py-1.5 bg-[var(--surface)] border rounded text-[12px] text-[var(--text-primary)]" style="border-color:var(--border);">
                        </div>
                        <div class="space-y-2">
                            <label class="text-[10px] font-bold text-[var(--text-muted)] block">Clock-Out Hub</label>
                            <input name="check_out_start" id="edit_out_start" type="time" class="w-full px-2.5 py-1.5 bg-[var(--surface)] border rounded text-[12px] text-[var(--text-primary)]" style="border-color:var(--border);">
                            <input name="check_out_end" id="edit_out_end" type="time" class="w-full px-2.5 py-1.5 bg-[var(--surface)] border rounded text-[12px] text-[var(--text-primary)]" style="border-color:var(--border);">
                        </div>
                    </div>
                </div>

                <button type="submit" class="w-full py-2.5 bg-[var(--primary)] text-white rounded text-[13px] font-bold hover:opacity-90 transition-opacity mt-4">
                    Confirm Deployment
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Hapus Lokasi -->
<div id="deleteLocModal" style="display:none;" class="fixed inset-0 z-[310] flex items-center justify-center bg-black/50 p-4" onclick="if(event.target===this)closeModal('deleteLocModal')">
    <div class="w-full max-w-sm bg-[var(--surface)] rounded-lg p-6 text-center shadow-2xl" style="border:1px solid var(--border);">
        <div class="w-12 h-12 bg-red-100 text-red-600 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="material-symbols-outlined text-[24px]">wrong_location</span>
        </div>
        <h3 class="text-[16px] font-bold text-[var(--text-primary)] mb-2">Decommission Site?</h3>
        <p class="text-[12px] text-[var(--text-muted)] mb-6">This will permanently remove <span id="del_loc_name" class="font-bold text-[var(--text-primary)]"></span>. Verification services for this site will be disabled.</p>
        
        <div class="grid grid-cols-2 gap-3">
            <button onclick="closeModal('deleteLocModal')" class="py-2 bg-[var(--surface2)] text-[var(--text-primary)] border rounded text-[12px] font-bold hover:bg-[var(--surface)] transition-colors" style="border-color:var(--border);">Cancel</button>
            <a id="del_loc_btn" href="#" class="py-2 bg-red-500 text-white rounded text-[12px] font-bold hover:bg-red-600 transition-colors">Decommission</a>
        </div>
    </div>
</div>

<!-- Security Modal: Admin Verification -->
<div id="secureModal" style="display:none;" class="fixed inset-0 z-[300] flex items-center justify-center bg-black/50 p-4" onclick="if(event.target===this)this.style.display='none'">
    <div class="w-full max-w-sm bg-[var(--surface)] rounded-lg shadow-2xl overflow-hidden" style="border:1px solid var(--border);">
        <div class="p-5 border-b bg-red-50" style="border-color:var(--border);">
             <div class="flex items-center gap-3 text-red-600">
                <span class="material-symbols-outlined">security_update_warning</span>
                <h3 class="text-[14px] font-bold">Authority Verification</h3>
             </div>
        </div>
        <form method="POST" action="?page=locations&action=generate-qr" class="p-6">
            <input type="hidden" name="location_id" id="modal_loc_id">
            <p class="text-[12px] text-[var(--text-muted)] mb-5">This will invalidate the current QR at <span id="modal_loc_name" class="font-bold text-[var(--text-primary)]"></span>. All physical deployments must be replaced.</p>
            
            <input name="admin_password" type="password" required class="w-full px-4 py-2 bg-[var(--surface2)] border rounded text-center text-[13px] focus:outline-none focus:border-red-500 mb-4" style="border-color:var(--border);" placeholder="Admin Password">
            
            <button type="submit" class="w-full py-2 bg-red-500 text-white rounded text-[13px] font-bold hover:bg-red-600 transition-colors">
                Authorize & Rotate
            </button>
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
    document.getElementById('edit_id').value = d ? d.id : '';
    document.getElementById('modal-title').innerText = d ? 'Edit Site' : 'New Site';
    document.getElementById('edit_name').value = d ? d.name : '';
    document.getElementById('edit_lat').value = d ? d.latitude : -6.2088;
    document.getElementById('edit_lng').value = d ? d.longitude : 106.8456;
    document.getElementById('edit_radius').value = d ? d.radius_meters : 50;
    document.getElementById('radius_val').innerText = (d ? d.radius_meters : 50) + 'm';
    document.getElementById('edit_in_start').value = d ? d.check_in_start : '07:00';
    document.getElementById('edit_in_end').value = d ? d.check_in_end : '10:00';
    document.getElementById('edit_out_start').value = d ? d.check_out_start : '16:00';
    document.getElementById('edit_out_end').value = d ? d.check_out_end : '20:00';
    document.getElementById('editModal').style.display = 'flex';
    setTimeout(() => { 
        initMap(parseFloat(document.getElementById('edit_lat').value), parseFloat(document.getElementById('edit_lng').value), parseInt(document.getElementById('edit_radius').value)); 
        map.invalidateSize(); 
    }, 400);
}

function closeEditModal() { document.getElementById('editModal').style.display = 'none'; }
function promptRegenerate(id, name) { document.getElementById('modal_loc_id').value = id; document.getElementById('modal_loc_name').innerText = name; document.getElementById('secureModal').style.display = 'flex'; }
function confirmDeleteLoc(id, name) {
    document.getElementById('del_loc_name').innerText = name;
    document.getElementById('del_loc_btn').href = `?page=locations&action=delete&id=${id}`;
    document.getElementById('deleteLocModal').style.display = 'flex';
}
function closeModal(id) { document.getElementById(id).style.display = 'none'; }

function togglePrintHeader(show) {
    document.getElementById('print-header').style.display = show ? 'flex' : 'none';
}

function togglePrintFooter(show) {
    document.getElementById('print-footer').style.display = show ? 'block' : 'none';
}

function previewQR(officeName, token) {
    if (!token) {
        alert("Token belum tersedia untuk lokasi ini.");
        return;
    }
    
    // Update labels
    document.getElementById('setting-office-name').innerText = officeName + " (Siap Dicetak)";
    document.getElementById('print-office-name').innerText = officeName;
    
    // Update date
    const dateOpts = { year: 'numeric', month: 'long', day: 'numeric' };
    document.getElementById('print-date').innerText = new Date().toLocaleDateString('id-ID', dateOpts);
    
    // Update QR Image and remove opacity
    const qrImg = document.getElementById('print-qr-img');
    qrImg.src = "https://api.qrserver.com/v1/create-qr-code/?size=800x800&data=" + encodeURIComponent(token);
    qrImg.classList.remove('opacity-20');
    
    // Update token text
    document.getElementById('print-token').innerText = "Token : ****" + token.slice(-4);
    
    // Make preview active (remove opacity-50)
    document.getElementById('print-qr-area').classList.remove('opacity-50');
    
    // Scroll to top right in mobile view
    if(window.innerWidth < 1280) {
        document.getElementById('setting-office-name').scrollIntoView({behavior: 'smooth', block: 'center'});
    }
}
</script>

<!-- Custom Print QR Styles -->
<style>
    /* Screen Preview Styles */
    #print-qr-area {
        width: 210mm;
        height: 297mm; /* Standard A4 size */
        margin: auto;
        background: white;
        transform-origin: top center;
        /* Using zoom instead of transform scale avoids container overflow issues */
        zoom: 0.55; 
    }
    
    /* Fallback for Firefox which didn't support zoom historically (though it does now) */
    @-moz-document url-prefix() {
        #print-qr-area {
            transform: scale(0.55);
            margin-bottom: -130mm;
        }
    }

    .leaflet-container { font-family: inherit; z-index: 10; border-radius: 0; }
    html.dark .map-dark { filter: invert(100%) hue-rotate(180deg) brightness(85%) contrast(85%); }

    /* Print Styles (Overrides Screen Preview Styles) */
    @media print {
        html, body {
            height: 297mm !important;
            overflow: hidden !important;
            margin: 0 !important;
            padding: 0 !important;
            background: white !important;
        }
        body * {
            visibility: hidden !important;
        }
        #print-qr-area, #print-qr-area * {
            visibility: visible !important;
            color: black !important;
            opacity: 1 !important;
        }
        #print-qr-area {
            position: fixed !important;
            left: 0 !important;
            top: 0 !important;
            width: 210mm !important;
            height: 297mm !important;
            margin: 0 !important;
            padding: 5mm 20mm 10mm 20mm !important; /* Reduced top padding */
            background: white !important;
            box-shadow: none !important;
            
            /* Reset screen preview scaling */
            zoom: 1 !important;
            transform: none !important;
            -webkit-transform: none !important;
            -moz-transform: none !important;

            /* Flex layout to spread content perfectly */
            display: flex !important;
            flex-direction: column !important;
            align-items: center !important;
            justify-content: space-between !important;
            box-sizing: border-box !important;
        }
        
        /* Keep original premium large QR Code size */
        #print-qr-area .my-6.p-6.border-8 {
            width: 420px !important;
            height: 420px !important;
            margin-top: 10px !important;
            margin-bottom: 10px !important;
            padding: 24px !important;
            border-width: 8px !important;
            border-radius: 2rem !important;
        }
        #print-qr-area #print-qr-img {
            width: 380px !important;
            height: 380px !important;
        }
        
        /* Keep original premium header size, optimize margins */
        #print-qr-area #print-header {
            margin-bottom: 15px !important;
        }
        #print-qr-area #print-header .w-28.h-28 {
            width: 112px !important; /* Original w-28 */
            height: 112px !important; /* Original h-28 */
            margin-bottom: 8px !important;
        }
        #print-qr-area #print-header .w-28.h-28 span {
            font-size: 64px !important; /* Original text-[64px] */
        }
        #print-qr-area #print-header h1 {
            font-size: 36px !important; /* Original text-[36px] */
        }
        #print-qr-area #print-header h2 {
            font-size: 20px !important; /* Original text-xl */
        }
        
        /* Keep original metadata text size, optimize spacing */
        #print-qr-area .py-6.my-6 {
            padding-top: 10px !important; /* Reduced from py-6 */
            padding-bottom: 10px !important;
            margin-top: 10px !important;  /* Reduced from my-6 */
            margin-bottom: 10px !important;
            font-size: 18px !important; /* Original text-lg */
        }
        #print-qr-area .space-y-4 > :not([hidden]) ~ :not([hidden]) {
            --tw-space-y-reverse: 0 !important;
            margin-top: 0.5rem !important;
            margin-bottom: 0.5rem !important;
        }
        
        /* Keep original footer instructions font sizes, optimize spacing */
        #print-qr-area #print-footer {
            margin-top: 10px !important;
        }
        #print-qr-area #print-footer p.text-2xl {
            font-size: 24px !important; /* Original text-2xl */
            line-height: 1.25 !important;
        }
        #print-qr-area #print-footer p.text-lg {
            font-size: 18px !important; /* Original text-lg */
            line-height: 1.25 !important;
        }
        
        /* Keep original token design, optimize spacing */
        #print-qr-area #print-token {
            margin-top: 12px !important;
            padding: 12px 32px !important; /* Original px-8 py-3 */
            font-size: 20px !important; /* Original text-xl */
        }
        
        @page {
            size: A4 portrait;
            margin: 0;
        }
        .print\:hidden {
            display: none !important;
        }
    }
</style>

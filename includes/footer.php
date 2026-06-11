    </main>

    <!-- Bottom Navigation (Integrated Executive Style) -->
    <nav class="fixed bottom-0 left-0 w-full z-50 px-4 pb-6 pt-3 bg-surface rounded-t-xl border-t border-border shadow-[0_-10px_30px_rgba(0,0,0,0.1)] md:hidden transition-all duration-300">

        <div class="flex justify-between items-center max-w-lg mx-auto">
            
            <!-- Home -->
            <a class="flex-1 flex flex-col items-center justify-center py-2 transition-all active:scale-95 group" href="/hris_system/?page=dashboard">
                <div class="flex flex-col items-center gap-1.5 <?= ($_GET['page'] ?? 'dashboard') === 'dashboard' ? 'text-primary' : 'text-on-surface/30' ?>">
                    <span class="material-symbols-outlined text-[24px]" style="<?= ($_GET['page'] ?? 'dashboard') === 'dashboard' ? "font-variation-settings: 'FILL' 1;" : "" ?>">home</span>
                    <span class="text-[8px] font-bold   transition-opacity <?= ($_GET['page'] ?? 'dashboard') === 'dashboard' ? 'opacity-100' : 'opacity-40' ?>">Home</span>
                </div>
            </a>

            <!-- Calendar -->
            <a class="flex-1 flex flex-col items-center justify-center py-2 transition-all active:scale-95 group" href="/hris_system/?page=calendar">
                <div class="flex flex-col items-center gap-1.5 <?= ($_GET['page'] ?? '') === 'calendar' ? 'text-primary' : 'text-on-surface/30' ?>">
                    <span class="material-symbols-outlined text-[24px]" style="<?= ($_GET['page'] ?? '') === 'calendar' ? "font-variation-settings: 'FILL' 1;" : "" ?>">calendar_month</span>
                    <span class="text-[8px] font-bold   transition-opacity <?= ($_GET['page'] ?? '') === 'calendar' ? 'opacity-100' : 'opacity-40' ?>">Events</span>
                </div>
            </a>

            <!-- CENTER BUTTON: SCAN QR (Employee) or STAFF (HRD) -->
            <?php if (auth_is_hrd()): ?>
            <a class="flex-1 flex justify-center transition-all active:scale-90" href="/hris_system/?page=employees">
                <div class="w-16 h-16 rounded-lg bg-primary flex items-center justify-center text-white shadow-2xl shadow-primary/20 overflow-hidden relative group">
                    <span class="material-symbols-outlined text-[28px] font-bold z-10">groups</span>
                    <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </div>
            </a>
            <?php else: ?>
            <button type="button" class="flex-1 flex justify-center transition-all active:scale-90 border-none bg-transparent cursor-pointer" onclick="openModal('attendanceOptionsModal')">
                <div class="w-16 h-16 rounded-lg bg-primary flex items-center justify-center text-white shadow-2xl shadow-primary/20 overflow-hidden relative group">
                    <span class="material-symbols-outlined text-[28px] font-bold z-10">qr_code_scanner</span>
                    <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </div>
            </button>
            <?php endif; ?>

            <!-- Gaji -->
            <a class="flex-1 flex flex-col items-center justify-center py-2 transition-all active:scale-95 group" href="/hris_system/?page=payroll">
                <div class="flex flex-col items-center gap-1.5 <?= ($_GET['page'] ?? '') === 'payroll' ? 'text-primary' : 'text-on-surface/30' ?>">
                    <span class="material-symbols-outlined text-[24px]" style="<?= ($_GET['page'] ?? '') === 'payroll' ? "font-variation-settings: 'FILL' 1;" : "" ?>">account_balance_wallet</span>
                    <span class="text-[8px] font-bold   transition-opacity <?= ($_GET['page'] ?? '') === 'payroll' ? 'opacity-100' : 'opacity-40' ?>">Gaji</span>
                </div>
            </a>

            <!-- People (Replaced Logout) -->
            <a class="flex-1 flex flex-col items-center justify-center py-2 transition-all active:scale-95 group" href="/hris_system/?page=people">
                <div class="flex flex-col items-center gap-1.5 <?= ($_GET['page'] ?? '') === 'people' ? 'text-primary' : 'text-on-surface/30' ?>">
                    <span class="material-symbols-outlined text-[24px]" style="<?= ($_GET['page'] ?? '') === 'people' ? "font-variation-settings: 'FILL' 1;" : "" ?>">diversity_3</span>
                    <span class="text-[8px] font-bold   transition-opacity <?= ($_GET['page'] ?? '') === 'people' ? 'opacity-100' : 'opacity-40' ?>">People</span>
                </div>
            </a>
            
        </div>
    </nav>

    <?php if (isset($user) && !auth_is_hrd()): ?>
    <!-- MODAL: Attendance Options Selection -->
    <div id="attendanceOptionsModal" style="display:none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-md p-4" onclick="if(event.target===this)closeModal('attendanceOptionsModal')">
        <div data-theme-card class="w-full max-w-sm bg-surface rounded-lg border border-border shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
            <!-- Header -->
            <div class="p-5 pb-1 flex justify-between items-start">
                <div>
                    <h3 data-theme-text class="text-xl font-bold mb-2">Metode Absensi</h3>
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                        <p data-theme-muted class="text-[9px] font-bold opacity-50">Pilih metode verifikasi kehadiran</p>
                    </div>
                </div>
                <button onclick="closeModal('attendanceOptionsModal')" data-theme-surface2 class="w-10 h-10 rounded-full flex items-center justify-center text-on-surface/40 hover:bg-surface2 transition-colors">
                    <span class="material-symbols-outlined font-bold">close</span>
                </button>
            </div>
            
            <div class="p-5 pt-3 space-y-3">
                <!-- QR scanner option -->
                <div onclick="closeModal('attendanceOptionsModal'); openModal('qrModal')" class="flex items-center gap-4 p-4 rounded-lg bg-[var(--surface2)] border border-[var(--border)] cursor-pointer hover:border-primary transition-all group">
                    <div class="w-12 h-12 rounded-lg bg-blue-500/10 text-blue-600 dark:text-blue-400 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-all shrink-0">
                        <span class="material-symbols-outlined text-2xl font-bold">qr_code_scanner</span>
                    </div>
                    <div class="text-left">
                        <h4 data-theme-text class="text-sm font-bold">Scan QR Code</h4>
                        <p data-theme-muted class="text-[9px] opacity-65 mt-0.5">Absen di terminal QR barcode kantor</p>
                    </div>
                </div>
                
                <!-- Selfie option -->
                <div onclick="closeModal('attendanceOptionsModal'); openModal('photoModal')" class="flex items-center gap-4 p-4 rounded-lg bg-[var(--surface2)] border border-[var(--border)] cursor-pointer hover:border-emerald-500 transition-all group">
                    <div class="w-12 h-12 rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 flex items-center justify-center group-hover:bg-emerald-500 group-hover:text-white transition-all shrink-0">
                        <span class="material-symbols-outlined text-2xl font-bold">camera_enhance</span>
                    </div>
                    <div class="text-left">
                        <h4 data-theme-text class="text-sm font-bold">Selfie WFH (Remote)</h4>
                        <p data-theme-muted class="text-[9px] opacity-65 mt-0.5">Absen dengan foto & lokasi GPS</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: QR Scanner -->
    <div id="qrModal" style="display:none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-md p-4" onclick="if(event.target===this){closeModal('qrModal'); stopQR()}">
        <div data-theme-card class="w-full max-w-sm bg-surface rounded-lg border border-border shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
            <!-- Header -->
            <div class="p-5 pb-1 flex justify-between items-start">
                <div>
                    <h3 data-theme-text class="text-xl font-bold  mb-2">QR Terminal</h3>
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                        <p data-theme-muted class="text-[9px] font-bold   opacity-50">Office verification</p>
                    </div>
                </div>
                <button onclick="closeModal('qrModal'); stopQR()" data-theme-surface2 class="w-10 h-10 rounded-full flex items-center justify-center text-on-surface/40 hover:bg-surface2 transition-colors">
                    <span class="material-symbols-outlined font-bold">close</span>
                </button>
            </div>
            
            <div class="p-5 pt-3">
                <div id="qr-reader" class="rounded-lg overflow-hidden bg-black/20 aspect-square border-2 border-dashed border-blue-500/20 flex items-center justify-center scale-[1.02]">
                    <p class="text-[10px] text-secondary opacity-40 px-8 text-center italic font-bold">Initializing camera...</p>
                </div>
                <div class="mt-4 text-center">
                    <p data-theme-muted class="text-[10px] font-bold  leading-relaxed opacity-40">Position code in center frame</p>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Photo Attendance -->
    <div id="photoModal" style="display:none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-md p-4" onclick="if(event.target===this){closeModal('photoModal');stopCamera()}">
        <div data-theme-card class="w-full max-w-sm bg-surface rounded-lg border border-border shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
            <!-- Header -->
            <div class="p-5 pb-1 flex justify-between items-start">
                <div>
                    <h3 data-theme-text class="text-xl font-bold  mb-2">Remote Snapshot</h3>
                    <div class="flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        <p data-theme-muted class="text-[9px] font-bold   opacity-50">WFH Verification</p>
                    </div>
                </div>
                <button onclick="closeModal('photoModal');stopCamera()" data-theme-surface2 class="w-10 h-10 rounded-full flex items-center justify-center text-on-surface/40 hover:bg-surface2 transition-colors">
                    <span class="material-symbols-outlined font-bold">close</span>
                </button>
            </div>

            <div class="p-5 pt-3 space-y-5">
                <div id="location-branding" class="flex flex-col gap-1 px-1">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-xs text-primary">location_on</span>
                        <span id="current-coords" class="text-[9px] font-bold   text-on-surface">Detecting GPS...</span>
                    </div>
                    <p id="current-address" class="text-[10px] font-medium text-on-surface-variant opacity-60 leading-tight">Waiting for location access...</p>
                </div>

                <div class="relative rounded-lg overflow-hidden bg-black/40 aspect-square shadow-inner border border-border">
                    <video id="cameraFeed" class="w-full h-full object-cover" autoplay playsinline></video>
                    <img id="photoPreview" class="hidden absolute inset-0 w-full h-full object-cover" src="" alt="Preview">
                    <div class="absolute inset-0 border-[16px] border-black/5 pointer-events-none rounded-lg"></div>
                </div>
                
                <div id="camera-ctrls" class="flex flex-col gap-3">
                    <button id="btnCapture" onclick="capturePhoto()" disabled class="w-full py-5 bg-emerald-500/50 cursor-not-allowed text-white rounded-full text-xs font-bold shadow-lg shadow-emerald-500/10 transition-all flex items-center justify-center gap-2">
                        <span class="material-symbols-outlined text-lg animate-spin">autorenew</span>
                        Detecting GPS...
                    </button>
                    
                    <form method="POST" action="/hris_system/index.php" id="photoForm" class="hidden flex flex-col gap-3" onsubmit="return validatePhotoSubmit()">
                        <input type="hidden" name="page" value="attendance">
                        <input type="hidden" name="action" value="submit-photo">
                        <input type="hidden" name="photo_data" id="photoData">
                        <input type="hidden" name="lat" id="formLat">
                        <input type="hidden" name="lng" id="formLng">
                        <input type="hidden" name="address" id="formAddress">
                        <button type="submit" class="w-full py-5 bg-primary text-white rounded-full text-xs font-bold  shadow-xl shadow-primary/20 active:scale-95 transition-all flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-lg">send</span>
                            Submit Verification
                        </button>
                        <button type="button" onclick="startCamera()" data-theme-muted class="w-full py-2 text-[10px] font-bold  hover:bg-surface2 rounded-full transition-colors text-center opacity-40">Retake Photo</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL: Photo Preview -->
    <div id="photoPreviewModal" style="display:none;" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/85 backdrop-blur-md p-4" onclick="if(event.target===this)closeModal('photoPreviewModal')">
        <div class="relative max-w-2xl w-full flex flex-col items-center">
            <button onclick="closeModal('photoPreviewModal')" class="absolute -top-10 right-0 text-white/50 hover:text-white flex items-center gap-2 font-bold   text-[9px] transition-colors">
                <span>Dismiss</span>
                <span class="material-symbols-outlined text-base">close</span>
            </button>
            <div class="w-full flex justify-center overflow-hidden rounded-lg">
                <img id="previewImg" src="" class="max-h-[85vh] w-auto h-auto rounded-lg shadow-2xl border-none" alt="Attendance Identity">
            </div>
        </div>
    </div>

    <style>
        #qr-reader video {
            object-fit: cover !important;
            width: 100% !important;
            height: 100% !important;
            transform: none !important;
        }
        #cameraFeed {
            object-fit: cover !important;
            width: 100% !important;
            height: 100% !important;
            transform: scaleX(-1) !important;
        }
    </style>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
    <script>
    let qrScanner = null;
    function openQRScanner() {
        qrScanner = new Html5Qrcode('qr-reader');
        qrScanner.start({facingMode:'environment'}, {fps:10,qrbox:260,disableFlip:true}, (text) => {
            // Mendapatkan Geolocation sebelum mengirim
            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition((pos) => {
                    const lat = pos.coords.latitude;
                    const lng = pos.coords.longitude;
                    stopQR();
                    closeModal('qrModal');
                    window.location.href = `?page=attendance&action=qr&code=${encodeURIComponent(text)}&lat=${lat}&lng=${lng}`;
                }, (err) => {
                    alert("Gagal mendapatkan lokasi. Harap berikan izin GPS untuk absensi.");
                    stopQR();
                    closeModal('qrModal');
                });
            } else {
                alert("Device Anda tidak mendukung GPS Geolocation.");
                stopQR();
                closeModal('qrModal');
            }
        }, ()=>{}).catch(err => {
            document.getElementById('qr-reader').innerHTML = `<p class="text-center text-rose-500 text-xs px-8">${err}</p>`;
        });
    }
    function stopQR() { if (qrScanner) { qrScanner.stop().catch(()=>{}); qrScanner = null; } }

    // Re-expose needed functions for attendance logic
    window.openModal = function(id) {
        const el = document.getElementById(id);
        if (el) {
            el.style.display = 'flex';
            if (id === 'qrModal') setTimeout(openQRScanner, 400);
            if (id === 'photoModal') setTimeout(startCamera, 400);
        }
    };
    window.closeModal = function(id) {
        const el = document.getElementById(id);
        if (el) el.style.display = 'none';
    };

    let stream = null;
    let currentPos = { lat: 0, lng: 0, addr: 'Unknown Location' };
    let watchId = null;

    function disableCaptureButton(text = "Detecting GPS...") {
        const btn = document.getElementById('btnCapture');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = `<span class="material-symbols-outlined text-lg animate-spin">autorenew</span> ${text}`;
            btn.className = "w-full py-5 bg-emerald-500/50 cursor-not-allowed text-white rounded-full text-xs font-bold shadow-lg shadow-emerald-500/10 transition-all flex items-center justify-center gap-2";
        }
    }

    function enableCaptureButton() {
        const btn = document.getElementById('btnCapture');
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = `<span class="material-symbols-outlined text-lg">photo_camera</span> Take Snapshot`;
            btn.className = "w-full py-5 bg-emerald-500 text-white rounded-full text-xs font-bold shadow-lg shadow-emerald-500/20 active:scale-95 transition-all flex items-center justify-center gap-2";
        }
    }

    function validatePhotoSubmit() {
        const lat = document.getElementById('formLat').value;
        const lng = document.getElementById('formLng').value;
        if (!lat || !lng || parseFloat(lat) === 0 || parseFloat(lng) === 0) {
            alert("Gagal mendapatkan koordinat GPS yang valid. Harap aktifkan GPS Anda dan coba lagi.");
            return false;
        }
        return true;
    }

    function startCamera() {
        stopCamera();
        initGeolocation();
        document.getElementById('cameraFeed').classList.remove('hidden');
        document.getElementById('photoPreview').classList.add('hidden');
        document.getElementById('btnCapture').classList.remove('hidden');
        disableCaptureButton("Detecting GPS...");
        document.getElementById('photoForm').classList.add('hidden');

        navigator.mediaDevices.getUserMedia({video:{aspectRatio: 1, facingMode:'user'}})
            .then(s => { 
                stream = s; 
                const video = document.getElementById('cameraFeed');
                video.srcObject = s;
                video.onloadedmetadata = () => video.play();
            })
            .catch(err => alert('Kamera Error: ' + err));
    }

    function initGeolocation() {
        if (!navigator.geolocation) {
            document.getElementById('current-coords').innerText = "GPS Not Supported";
            document.getElementById('current-address').innerText = "Your browser does not support Geolocation.";
            disableCaptureButton("GPS Not Supported");
            return;
        }
        
        // Reset display and disable capture
        document.getElementById('current-coords').innerText = "Detecting GPS...";
        document.getElementById('current-address').innerText = "Waiting for location...";
        disableCaptureButton("Detecting GPS...");

        const geoOptions = {
            enableHighAccuracy: true,
            timeout: 10000,
            maximumAge: 0
        };

        function handleSuccess(pos) {
            if (pos.coords.latitude === 0 && pos.coords.longitude === 0) {
                return; // Ignore dummy/zero coordinates
            }
            currentPos.lat = pos.coords.latitude;
            currentPos.lng = pos.coords.longitude;
            document.getElementById('current-coords').innerText = `${currentPos.lat.toFixed(6)}, ${currentPos.lng.toFixed(6)}`;
            document.getElementById('formLat').value = currentPos.lat;
            document.getElementById('formLng').value = currentPos.lng;
            
            enableCaptureButton();

            // Reverse Geocoding via Nominatim
            fetch(`https://nominatim.openstreetmap.org/reverse?format=json&lat=${currentPos.lat}&lon=${currentPos.lng}&zoom=18&addressdetails=1`)
                .then(res => res.json())
                .then(data => {
                    currentPos.addr = data.display_name || 'Address found';
                    document.getElementById('current-address').innerText = currentPos.addr;
                    document.getElementById('formAddress').value = currentPos.addr;
                }).catch(() => {
                    currentPos.addr = "Location details unavailable";
                    document.getElementById('current-address').innerText = currentPos.addr;
                });
        }

        function handleError(err) {
            console.warn(`Geolocation error (${err.code}): ${err.message}`);
            
            // Fallback to low accuracy if high accuracy timed out or failed
            if (err.code === 3 || err.code === 2) {
                document.getElementById('current-coords').innerText = "Retrying with lower accuracy...";
                navigator.geolocation.getCurrentPosition(handleSuccess, err2 => {
                    let errorMsg = "Permission Denied: Enable GPS to proceed.";
                    if (err2.code === 2) {
                        errorMsg = "Position Unavailable: GPS signal not found.";
                    } else if (err2.code === 3) {
                        errorMsg = "Timeout: Failed to acquire GPS signal.";
                    }
                    document.getElementById('current-address').innerText = errorMsg;
                    disableCaptureButton(errorMsg);
                }, { enableHighAccuracy: false, timeout: 10000 });
            } else {
                let errorMsg = "Permission Denied: Enable GPS to proceed.";
                document.getElementById('current-address').innerText = errorMsg;
                disableCaptureButton(errorMsg);
            }
        }

        // Attempt to get position immediately
        navigator.geolocation.getCurrentPosition(handleSuccess, handleError, geoOptions);

        // Watch position to update coordinates dynamically
        if (watchId !== null) {
            navigator.geolocation.clearWatch(watchId);
        }
        watchId = navigator.geolocation.watchPosition(handleSuccess, handleError, geoOptions);
    }

    function stopCamera() { 
        if (stream) { stream.getTracks().forEach(t => t.stop()); stream = null; } 
        if (watchId !== null) { navigator.geolocation.clearWatch(watchId); watchId = null; }
    }

    function capturePhoto() {
        const video = document.getElementById('cameraFeed');
        const size = Math.min(video.videoWidth, video.videoHeight);
        const canvas = document.createElement('canvas');
        canvas.width = size; 
        canvas.height = size;
        const ctx = canvas.getContext('2d');
        
        // Square Crop from center with horizontal flip (mirroring) to match preview
        const startX = (video.videoWidth - size) / 2;
        const startY = (video.videoHeight - size) / 2;
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, startX, startY, size, size, 0, 0, size, size);
        ctx.setTransform(1, 0, 0, 1, 0, 0); // Reset transform before watermark drawing
        
        const now = new Date();
        const ts = now.toLocaleString('id-ID', { dateStyle: 'medium', timeStyle: 'medium' });
        
        // Watermark Background
        ctx.fillStyle = "rgba(0, 0, 0, 0.7)";
        ctx.fillRect(0, canvas.height - 110, canvas.width, 110);
        
        // Text Styling
        ctx.fillStyle = "white";
        const fontSize = Math.floor(size/32);
        ctx.font = `bold ${fontSize}px sans-serif`;
        
        // Line 1: Timestamp & Coordinates
        ctx.fillText(`${ts} | ${currentPos.lat.toFixed(6)}, ${currentPos.lng.toFixed(6)}`, 20, canvas.height - 75);
        
        // Line 2: Address (Wrapped if too long)
        ctx.font = `${Math.floor(size/45)}px sans-serif`;
        ctx.globalAlpha = 0.8;
        const words = currentPos.addr.split(' ');
        let line = '';
        let y = canvas.height - 45;
        for (let n = 0; n < words.length; n++) {
            let testLine = line + words[n] + ' ';
            let metrics = ctx.measureText(testLine);
            if (metrics.width > canvas.width - 40 && n > 0) {
                ctx.fillText(line.trim(), 20, y);
                line = words[n] + ' ';
                y += fontSize - 5;
            } else {
                line = testLine;
            }
        }
        ctx.fillText(line.trim(), 20, y);

        const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
        document.getElementById('photoPreview').src = dataUrl;
        document.getElementById('photoPreview').classList.remove('hidden');
        document.getElementById('cameraFeed').classList.add('hidden');
        document.getElementById('photoData').value = dataUrl;
        document.getElementById('btnCapture').classList.add('hidden');
        document.getElementById('photoForm').classList.remove('hidden');
    }

    function openPhotoPreview(src) {
        const modal = document.getElementById('photoPreviewModal');
        const img = document.getElementById('previewImg');
        if (modal && img) {
            img.src = src;
            modal.style.display = 'flex';
        }
    }
    </script>
    <?php endif; ?>
</body>
</html>

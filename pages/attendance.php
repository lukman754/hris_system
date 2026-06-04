<?php 
// pages/attendance.php – Absensi Modern Redesign
$my_att  = get_attendance($user['id']);
// Urutkan terbaru di atas
usort($my_att, function($a, $b) {
    return strcmp($b['attendance_date'] . $b['attendance_time'], $a['attendance_date'] . $a['attendance_time']);
});

// Calculate Stats
$today = date('Y-m-d');
$today_records = array_filter($my_att, fn($r) => $r['attendance_date'] === $today);

$has_in = false;
$has_out = false;
$has_pending_in = false;
$has_approved_in = false;
$check_in_time = '--:--';
$check_out_time = '--:--';

// For today's stats, we focus on NON-REJECTED records for flow control
$today_ins = array_filter($today_records, fn($r) => $r['attendance_flow'] === 'in' && $r['approval_status'] !== 'rejected');
$today_outs = array_filter($today_records, fn($r) => $r['attendance_flow'] === 'out' && $r['approval_status'] !== 'rejected');

if (!empty($today_ins)) {
    usort($today_ins, fn($a, $b) => strcmp($a['attendance_time'], $b['attendance_time']));
    $first_in = $today_ins[0];
    $has_in = true;
    $check_in_time = date('H:i', strtotime($first_in['attendance_time']));
    
    // Check pending/approved status
    $has_pending_in = !empty(array_filter($today_ins, fn($r) => $r['approval_status'] === 'pending'));
    $has_approved_in = !empty(array_filter($today_ins, fn($r) => $r['approval_status'] === 'approved'));
}

if (!empty($today_outs)) {
    usort($today_outs, fn($a, $b) => strcmp($b['attendance_time'], $a['attendance_time']));
    $last_out = $today_outs[0];
    $has_out = true;
    $check_out_time = date('H:i', strtotime($last_out['attendance_time']));
}


/**
 * RESTRICTION LOGIC:
 * 1. cannot check-in if there is already a PENDING or APPROVED check-in today.
 * 2. cannot check-out if there is no APPROVED check-in today.
 */
$can_check_in = !$has_in;
$is_pending_in = $has_in && $has_pending_in;
$can_check_out = $has_approved_in && !$has_out;


$this_month = date('Y-m');
$month_records = array_filter($my_att, fn($r) => strpos($r['attendance_date'], $this_month) === 0);

// For Efficiency, we focus ONLY on 'Check-In' sessions to measure punctuality
$month_ins = array_filter($month_records, fn($r) => $r['attendance_flow'] === 'in');
$total_month_in = count($month_ins);

// Success/On-Time are those with status 'valid' or 'early'
$on_time_count = count(array_filter($month_ins, fn($r) => in_array($r['status'], ['valid', 'early'])));
$on_time_rate = $total_month_in > 0 ? round(($on_time_count / $total_month_in) * 100) : 100;

// Greeting logic
$hour = date('H');
$greeting = "Good Morning";
if ($hour >= 12) $greeting = "Good Afternoon";
if ($hour >= 18) $greeting = "Good Evening";
?>

<!-- Dashboard Header -->
<section class="mb-6">
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div>
            <h1 class="text-3xl font-bold  leading-tight mb-1 text-on-surface"><?= $greeting ?>, <?= explode(' ', $user['name'])[0] ?>!</h1>
            <div class="flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                <p class="text-[9px] font-bold   opacity-80 text-on-surface-variant">Attendance Dashboard • <?= date('d M Y') ?></p>
            </div>
        </div>
        <div data-theme-card class="px-4 py-2.5 rounded-lg flex items-center justify-between md:justify-start gap-3 w-full md:w-auto">
            <div class="text-right ml-auto md:ml-0">
                <div id="live-clock" class="text-xl font-bold leading-none  text-on-surface">00:00:00</div>
                <div class="text-[8px] font-bold   mt-1 opacity-50 text-on-surface-variant">Local Server Time</div>
            </div>
            <div class="w-9 h-9 bg-primary/10 rounded-lg flex items-center justify-center shrink-0">
                <span class="material-symbols-outlined text-primary text-xl font-bold">schedule</span>
            </div>
        </div>
    </div>
</section>

<!-- Attendance Methods Grid (Action Cards First) -->
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
    
    <!-- QR Scanner Card -->
    <?php if ($can_check_in || $can_check_out): ?>
    <div onclick="openModal('qrModal')" data-theme-card class="p-5 rounded-lg text-center cursor-pointer group hover:bg-blue-500/5 transition-all relative overflow-hidden active:scale-[0.98]">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-blue-500/5 rounded-full blur-2xl group-hover:bg-blue-500/10 transition-all"></div>
        <div class="w-16 h-16 bg-blue-500/10 text-blue-600 dark:text-blue-400 rounded-lg flex items-center justify-center mx-auto mb-4 group-hover:bg-blue-600 group-hover:text-white transition-all shadow-sm border border-blue-500/10 group-hover:border-blue-600">
            <span class="material-symbols-outlined text-3xl font-bold">qr_code_scanner</span>
        </div>
        <h3 data-theme-text class="text-base font-bold  mb-1">Office Terminal</h3>
        <p data-theme-muted class="text-[8px] font-bold   opacity-50"><?= $has_in ? 'Verify Check-Out' : 'Standard workplace verification' ?></p>
    </div>
    <?php else: ?>
    <div data-theme-card class="bg-surface/50 p-5 rounded-lg border border-border text-center opacity-60 grayscale relative overflow-hidden">
        <div class="w-16 h-16 bg-surface-variant/50 text-on-surface/30 rounded-lg flex items-center justify-center mx-auto mb-4 border border-border">
            <span class="material-symbols-outlined text-3xl font-bold"><?= $has_out ? 'verified' : ($is_pending_in ? 'pending_actions' : 'qr_code_scanner') ?></span>
        </div>
        <h3 data-theme-text class="text-base font-bold  mb-1"><?= $has_out ? 'Sesi Selesai' : ($is_pending_in ? 'Menunggu Approval' : 'Terminal Locked') ?></h3>
        <p data-theme-muted class="text-[8px] font-bold   opacity-50"><?= $has_out ? 'Sampai jumpa besok!' : ($is_pending_in ? 'Check-in diproses HRD' : 'Akses saat ini dibatasi') ?></p>
    </div>
    <?php endif; ?>

    <!-- Photo Check-in Card (WFH) -->
    <?php if ($can_check_in || $can_check_out): ?>
    <div onclick="openModal('photoModal')" data-theme-card class="p-5 rounded-lg text-center cursor-pointer group hover:bg-emerald-500/5 transition-all relative overflow-hidden active:scale-[0.98]">
        <div class="absolute -right-6 -top-6 w-24 h-24 bg-emerald-500/5 rounded-full blur-2xl group-hover:bg-emerald-500/10 transition-all"></div>
        <div class="w-16 h-16 bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 rounded-lg flex items-center justify-center mx-auto mb-4 group-hover:bg-emerald-600 group-hover:text-white transition-all shadow-sm border border-emerald-500/10 group-hover:border-emerald-600">
            <span class="material-symbols-outlined text-3xl font-bold">camera_enhance</span>
        </div>
        <h3 data-theme-text class="text-base font-bold  mb-1">Remote Snapshot</h3>
        <p data-theme-muted class="text-[8px] font-bold   opacity-50"><?= $has_in ? 'Remote Check-Out' : 'Verified remote check-in' ?></p>
    </div>
    <?php else: ?>
    <div data-theme-card class="bg-surface/50 p-5 rounded-lg border border-border text-center opacity-60 grayscale relative overflow-hidden">
        <div class="w-16 h-16 bg-surface-variant/50 text-on-surface/30 rounded-lg flex items-center justify-center mx-auto mb-4 border border-border">
            <span class="material-symbols-outlined text-3xl font-bold"><?= $has_out ? 'verified' : ($is_pending_in ? 'history_edu' : 'camera_enhance') ?></span>
        </div>
        <h3 data-theme-text class="text-base font-bold  mb-1"><?= $has_out ? 'Shift Completed' : ($is_pending_in ? 'Pending Review' : 'Remote Locked') ?></h3>
        <p data-theme-muted class="text-[8px] font-bold   opacity-50"><?= $has_out ? 'Attendance recorded' : ($is_pending_in ? 'Verifikasi foto berlangsung' : 'Action currently unavailable') ?></p>
    </div>
    <?php endif; ?>

</div>

<!-- Summary Cards (Punctuality & Status) -->
<div class="stat-grid-att mb-10">
    <!-- Check-In Status -->
    <div data-theme-card class="stat-card-att">
        <div class="st-header-att">
            <div class="st-icon-att" style="background:rgba(59,130,246,0.1);">
                <span class="material-symbols-outlined text-blue-500 st-sym-att">login</span>
            </div>
            <div class="st-info-att">
                <div data-theme-muted class="st-label-att">Check-In</div>
                <div data-theme-text class="st-val-att"><?= $check_in_time ?></div>
            </div>
        </div>
        <div class="st-footer-att hide-mobile">
            <span class="px-2 py-0.5 rounded-md text-[8px] font-bold   <?= $has_in ? 'bg-emerald-500/10 text-emerald-500' : 'bg-rose-500/10 text-rose-500' ?>">
                <?= $has_in ? 'Verified' : 'Required' ?>
            </span>
        </div>
    </div>

    <!-- Check-Out Status -->
    <div data-theme-card class="stat-card-att">
        <div class="st-header-att">
            <div class="st-icon-att" style="background:rgba(245,158,11,0.1);">
                <span class="material-symbols-outlined text-amber-500 st-sym-att">logout</span>
            </div>
            <div class="st-info-att">
                <div data-theme-muted class="st-label-att">Check-Out</div>
                <div data-theme-text class="st-val-att"><?= $check_out_time ?></div>
            </div>
        </div>
        <div class="st-footer-att hide-mobile">
            <span class="px-2 py-0.5 rounded-md text-[8px] font-bold   <?= $has_out ? 'bg-emerald-500/10 text-emerald-500' : 'bg-surface-container-high text-secondary opacity-30 shadow-none' ?>">
                <?= $has_out ? 'Logged Out' : 'Active Duty' ?>
            </span>
        </div>
    </div>

    <!-- Monthly Performance -->
    <div data-theme-card class="stat-card-att">
        <div class="st-header-att">
            <div class="st-icon-att" style="background:rgba(16,185,129,0.1);">
                <span class="material-symbols-outlined text-emerald-500 st-sym-att">verified_user</span>
            </div>
            <div class="st-info-att">
                <div class="st-label-att text-on-surface-variant">Efficiency</div>
                <div class="st-val-att text-on-surface"><?= $on_time_rate ?>%</div>
            </div>
        </div>
        <div class="st-footer-att" style="padding-top:4px;">
            <div class="flex-1 h-1 bg-surface-variant rounded-full overflow-hidden">
                <div class="h-full bg-emerald-500 rounded-full" style="width: <?= $on_time_rate ?>%"></div>
            </div>
        </div>
    </div>
</div>

<style>
.stat-grid-att {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}
.stat-card-att {
    padding: 14px;
    border-radius: 8px;
    display: flex;
    flex-direction: column;
    justify-content: space-between;
}
.st-header-att {
    display: flex;
    align-items: center;
    gap: 12px;
}
.st-icon-att {
    width: 36px;
    height: 36px;
    border-radius: 6px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}
.st-sym-att {
    font-size: 18px;
    font-weight: 900;
}
.st-label-att {
    font-size: 8px;
    font-weight: 800;
    text-transform: ;
    letter-spacing: .08em;
    opacity: 0.5;
}
.st-val-att {
    font-size: 15px;
    font-weight: 950;
    line-height:1;
}
.st-footer-att {
    margin-top: 10px;
    border-top: 1px solid var(--border);
    padding-top: 8px;
    display: flex;
    justify-content: center;
}

@media (max-width: 640px) {
    .stat-grid-att {
        gap: 6px;
    }
    .stat-card-att {
        padding: 8px 4px;
        border-radius: 12px;
    }
    .st-header-att {
        flex-direction: column;
        gap: 6px;
        text-align: center;
    }
    .st-icon-att {
        width: 28px;
        height: 28px;
    }
    .st-sym-att {
        font-size: 14px;
    }
    .st-label-att {
        font-size: 7px;
    }
    .st-val-att {
        font-size: 12px;
    }
    .st-footer-att {
        margin-top: 6px;
        padding-top: 6px;
    }
    .hide-mobile {
        display: none !important;
    }
}
</style>

<!-- Personal Ledger -->
<section>
    <div class="flex justify-between items-center mb-5 px-1">
        <div>
            <h2 data-theme-muted class="text-[10px] font-bold   opacity-50">Attendance Ledger</h2>
            <p data-theme-muted class="text-[8px] font-bold opacity-30 mt-0.5">Summary of your latest activity</p>
        </div>
        <div data-theme-card class="flex items-center gap-3 px-3 py-1.5 rounded-lg">
            <div class="flex flex-col items-end">
                <span data-theme-text class="text-[10px] font-bold leading-none"><?= count($my_att) ?></span>
                <span data-theme-muted class="text-[6px] font-bold   opacity-30">Total</span>
            </div>
            <div class="w-[1px] h-3 bg-outline-variant/10"></div>
            <div class="flex flex-col items-end">
                <span class="text-[10px] font-bold text-emerald-500 leading-none"><?= count(array_filter($my_att, fn($r) => $r['approval_status'] === 'approved')) ?></span>
                <span data-theme-muted class="text-[6px] font-bold   opacity-30">Valid</span>
            </div>
        </div>
    </div>

    <div data-theme-card class="rounded-lg overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="border-b bg-surface2" style="border-color:var(--border);">
                        <th data-theme-muted class="px-6 py-4 text-[8px] font-bold   opacity-40">Date & Session</th>
                        <th data-theme-muted class="px-4 py-4 text-[8px] font-bold   opacity-40">Location</th>
                        <th data-theme-muted class="px-6 py-4 text-[8px] font-bold   opacity-40 text-right">Time & Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="border-color:var(--border); border-top:none;">
                    <?php if (empty($my_att)): ?>
                        <tr>
                            <td colspan="3" class="p-12 text-center text-surface2" style="color:var(--text-muted); opacity:0.3;">
                                <span class="material-symbols-outlined text-3xl block mb-2">inbox</span>
                                <p data-theme-muted class="text-[9px] italic font-bold  ">Queue empty - Start today?</p>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach (array_slice($my_att, 0, 30) as $rec): 
                            $is_qr = $rec['attendance_type'] === 'qr';
                        ?>
                        <tr class="hover:bg-surface2 transition-colors group" style="border-color:var(--border);">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div data-theme-surface2 class="w-10 h-10 rounded-lg bg-surface2 flex items-center justify-center overflow-hidden group-hover:scale-105 transition-all shadow-sm">
                                        <?php if (!$is_qr && $rec['photo_path']): ?>
                                            <img src="<?= h($rec['photo_path']) ?>" 
                                                 class="w-full h-full object-cover cursor-zoom-in" 
                                                 onclick="openPhotoPreview('<?= h($rec['photo_path']) ?>')">
                                        <?php else: ?>
                                            <span class="material-symbols-outlined text-<?= $is_qr?'blue':'emerald' ?>-500 opacity-40 text-lg"><?= $is_qr?'qr_code':'photo_camera' ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <div>
                                        <div data-theme-text class="text-xs font-bold  leading-none"><?= format_date($rec['attendance_date']) ?></div>
                                        <div data-theme-surface2 class="px-1.5 py-0.5 rounded bg-surface2 text-[7px] font-bold   w-fit mt-1.5" style="color:var(--text-muted);">
                                            <?= $rec['attendance_flow'] === 'in' ? 'Check-In' : 'Check-Out' ?>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-4">
                                <div data-theme-text class="text-xs font-bold leading-tight"><?= h($rec['location']) ?></div>
                                <div data-theme-muted class="text-[8px] font-bold opacity-30 mt-1   italic"><?= $rec['attendance_type'] ?> verified</div>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div data-theme-text class="text-[13px] font-bold  leading-none"><?= date('H:i', strtotime($rec['attendance_time'])) ?></div>
                                <div class="mt-2 text-[8px] font-bold   <?= $rec['approval_status'] === 'approved' ? 'text-emerald-500' : ($rec['approval_status'] === 'pending' ? 'text-amber-500' : 'text-rose-500') ?>">
                                    <?= $rec['approval_status'] ?>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <?php if (count($my_att) > 30): ?>
            <div data-theme-surface2 class="p-4 text-center border-t bg-surface2" style="border-color:var(--border);">
                <button class="text-[9px] font-bold text-primary   hover:opacity-70 transition-opacity">Show All Records</button>
            </div>
        <?php endif; ?>
    </div>
</section>
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
    #qr-reader video, 
    #cameraFeed {
        object-fit: cover !important;
        width: 100% !important;
        height: 100% !important;
        transform: none !important;
    }
</style>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html5-qrcode/2.3.8/html5-qrcode.min.js"></script>
<script>
let qrScanner = null;
function openQRScanner() {
    qrScanner = new Html5Qrcode('qr-reader');
    qrScanner.start({facingMode:'environment'}, {fps:10,qrbox:260}, (text) => {
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

// Live Clock Update
function updateClock() {
    const now = new Date();
    const timeString = now.toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    const clockEl = document.getElementById('live-clock');
    if (clockEl) clockEl.innerText = timeString;
}
setInterval(updateClock, 1000);
updateClock();

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
    
    // Square Crop from center
    const startX = (video.videoWidth - size) / 2;
    const startY = (video.videoHeight - size) / 2;
    ctx.drawImage(video, startX, startY, size, size, 0, 0, size, size);
    
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

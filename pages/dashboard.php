<?php
// pages/dashboard.php
$user_id    = $user['id'];
$today      = date('Y-m-d');

if (auth_is_hrd()) {
    $employees  = get_employees();
    $attendance = get_attendance();
    $leaves     = get_leaves();
} else {
    $employees  = [];
    $attendance = get_attendance($user_id);
    $leaves     = get_leaves($user_id);
    $calendar_events = get_calendar_events();
}

$user_att       = auth_is_hrd() ? array_filter($attendance, fn($a) => $a['user_id'] === $user_id) : $attendance;
$my_today_att   = array_filter($user_att,   fn($a) => $a['attendance_date'] === $today);
$my_monthly_att = array_filter($user_att,   fn($a) => date('Y-m', strtotime($a['attendance_date'])) === date('Y-m'));

$today_in_records = array_filter($my_today_att, fn($a) => ($a['attendance_flow'] ?? '') === 'in');
$today_out_records = array_filter($my_today_att, fn($a) => ($a['attendance_flow'] ?? '') === 'out');
$clock_in      = !empty($today_in_records) ? date('H:i', strtotime(end($today_in_records)['attendance_time'])) : '--:--';
$clock_out     = !empty($today_out_records) ? date('H:i', strtotime(end($today_out_records)['attendance_time'])) : '--:--';
$clocked_in    = $clock_in !== '--:--';

$present_count = count($my_monthly_att);
$days_in_month = (int) date('t');
$att_pct       = $days_in_month > 0 ? round(($present_count / $days_in_month) * 100) : 0;

// Weekly strip
$week_days     = [];
$start_of_week = date('Y-m-d', strtotime('monday this week'));
for ($i = 0; $i < 7; $i++) {
    $date    = date('Y-m-d', strtotime("$start_of_week +$i days"));
    $has_att = array_filter($user_att, fn($a) => $a['attendance_date'] === $date);
    $week_days[] = [
        'day'      => date('D', strtotime($date)),
        'num'      => date('j', strtotime($date)),
        'status'   => !empty($has_att) ? 'present'
                    : (strtotime($date) < strtotime($today) ? 'absent' : 'future'),
        'is_today' => $date === $today,
    ];
}

// History
$latest_history = array_slice(array_values($user_att), 0, 8);

// Pagination for Recent Attendance Activity
$per_page = 8;
$total_records = count($attendance);
$total_pages = max(1, ceil($total_records / $per_page));
$current_page = max(1, min($total_pages, (int)($_GET['p'] ?? 1)));
$offset = ($current_page - 1) * $per_page;
$recent_activities = array_slice($attendance, $offset, $per_page);

// Menu
$all_menu = [];
if (auth_is_hrd()) {
    if (($user['can_attendance'] ?? 0) == 1) {
        $all_menu[] = ['page' => 'attendance',    'icon' => 'fingerprint',    'label' => 'Absensi',   'ib' => 'ib-orange', 'ic' => '#003d9b'];
    }
    $all_menu = array_merge($all_menu, [
        ['page' => 'employees',          'icon' => 'group',           'label' => 'Staff',     'ib' => 'ib-blue',   'ic' => '#3B82F6'],
        ['page' => 'attendance-reports', 'icon' => 'insights',        'label' => 'Laporan',   'ib' => 'ib-green',  'ic' => '#10B981'],
        ['page' => 'leaves',             'icon' => 'calendar_add_on', 'label' => 'Approvals', 'ib' => 'ib-yellow', 'ic' => '#D97706'],
        ['page' => 'photo-approvals',    'icon' => 'photo_camera',    'label' => 'Foto Rec',  'ib' => 'ib-red',    'ic' => '#EF4444'],
        ['page' => 'payroll',            'icon' => 'payments',        'label' => 'Keuangan',  'ib' => 'ib-purple', 'ic' => '#7C3AED'],
        ['page' => 'performance',        'icon' => 'monitoring',      'label' => 'Kinerja',   'ib' => 'ib-indigo', 'ic' => '#4F46E5'],
        ['page' => 'announcements',      'icon' => 'campaign',        'label' => 'Info',      'ib' => 'ib-teal',   'ic' => '#0D9488'],
        ['page' => 'calendar',           'icon' => 'calendar_today',  'label' => 'Jadwal',    'ib' => 'ib-slate',  'ic' => '#64748B'],
    ]);
} else {
    $all_menu = [
        ['page' => 'attendance',    'icon' => 'fingerprint',    'label' => 'Absensi',   'ib' => 'ib-orange', 'ic' => '#003d9b'],
        ['page' => 'leaves',        'icon' => 'calendar_add_on','label' => 'Izin',      'ib' => 'ib-yellow', 'ic' => '#D97706'],
        ['page' => 'performance',   'icon' => 'monitoring',     'label' => 'Kinerja',   'ib' => 'ib-purple', 'ic' => '#7C3AED'],
        ['page' => 'announcements', 'icon' => 'campaign',       'label' => 'Info',      'ib' => 'ib-teal',   'ic' => '#0D9488'],
        ['page' => 'calendar',      'icon' => 'calendar_today', 'label' => 'Jadwal',    'ib' => 'ib-slate',  'ic' => '#64748B'],
        ['page' => 'people',        'icon' => 'diversity_3',    'label' => 'Rekan',     'ib' => 'ib-blue',   'ic' => '#3B82F6'],
    ];
}

$anns = array_slice(get_announcements(), 0, 5);
?>

<style>
/* ── Desktop layout ─────────────────── */
.dash-layout {
    display: flex;
    flex-direction: column;
    gap: 20px;
}
.dash-left {
    display: flex;
    flex-direction: column;
    gap: 14px;
    width: 100%;
}
.bottom-columns {
    display: flex;
    flex-direction: column;
    gap: 20px;
    margin-top: 20px;
}
@media (min-width: 900px) {
    .bottom-columns {
        display: grid;
        grid-template-columns: minmax(0, 2fr) minmax(0, 1fr);
        align-items: start;
    }
}
/* ── Menu Utama Hover Effect ── */
.menu-card {
    background: var(--surface);
    border: 1px solid var(--border);
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
}
.menu-card:hover {
    background: var(--primary) !important;
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(var(--primary-rgb, 0, 61, 155), 0.3) !important;
}
.menu-card:hover .mc-icon {
    background: rgba(255, 255, 255, 0.2) !important;
    color: #fff !important;
}
.menu-card:hover .mc-label {
    color: #fff !important;
}

/* ── Custom Compact Scrollbar & Table ── */
.custom-scrollbar::-webkit-scrollbar {
    height: 4px;
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: transparent;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: var(--border);
    border-radius: 99px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: var(--text-muted);
}
html.dark .custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(255, 255, 255, 0.1);
}
html.dark .custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255, 255, 255, 0.25);
}

.compact-table th {
    padding: 8px 16px !important;
}
.compact-table td {
    padding: 8px 16px !important;
}
</style>

<div class="dash-layout performance-page-container">
    <div class="dash-left">
        <!-- ══ Header Section ══ -->
        <header class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <div class="w-12 h-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-3xl font-bold">dashboard</span>
                    </div>
                    <h1 data-theme-text class="text-3xl font-bold leading-none">
                        <?= auth_is_hrd() ? 'Admin Dashboard' : 'Employee Dashboard' ?>
                    </h1>
                </div>
                <p data-theme-muted class="text-[10px] font-bold ml-1 opacity-50">
                    <?= auth_is_hrd() ? 'Overview of organization workforce and financial performance for Perkasa Abadi Logistik.' : 'Overview of your attendance and activities.' ?>
                </p>
            </div>
        </header>

        <!-- ── Stats ── -->
        <div class="grid <?= auth_is_hrd() ? 'grid-cols-2 md:grid-cols-3 lg:grid-cols-6' : 'grid-cols-2 lg:grid-cols-4' ?> gap-4 mb-6">
            <?php if (auth_is_hrd()): ?>
            <?php 
                $today_presence = count(array_unique(array_column(array_filter($attendance, fn($a) => $a['attendance_date'] === $today), 'user_id'))); 
                $absent = max(0, count($employees) - $today_presence);
                $pct = count($employees) > 0 ? round(($today_presence / count($employees)) * 100, 1) : 0;
            ?>
            <!-- 1. Total Employees -->
            <div class="card" style="background: linear-gradient(135deg, #2563EB, #1D4ED8); border: none; color: white; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; min-h: 110px;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
                    <span style="font-size: 11px; font-weight: 600; color: rgba(255, 255, 255, 0.8); text-transform: uppercase; letter-spacing: 0.02em;">Total Employees</span>
                    <div style="width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.15); color: #ffffff; flex-shrink: 0;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">groups</span>
                    </div>
                </div>
                <div style="display: flex; align-items: baseline; gap: 4px; margin-top: auto;">
                    <span style="font-size: 20px; font-weight: 700; color: #ffffff; line-height: 1;"><?= count($employees) ?></span>
                </div>
                <p style="font-size: 11px; color: rgba(255, 255, 255, 0.7); margin: 4px 0 0; font-style: italic;">Active in company</p>
            </div>
            
            <!-- 2. Present -->
            <div class="card" style="background: linear-gradient(135deg, #10B981, #059669); border: none; color: white; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; min-h: 110px;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
                    <span style="font-size: 11px; font-weight: 600; color: rgba(255, 255, 255, 0.8); text-transform: uppercase; letter-spacing: 0.02em;">Present</span>
                    <div style="width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.15); color: #ffffff; flex-shrink: 0;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">person_check</span>
                    </div>
                </div>
                <div style="display: flex; align-items: baseline; gap: 4px; margin-top: auto;">
                    <span style="font-size: 20px; font-weight: 700; color: #ffffff; line-height: 1;"><?= $today_presence ?></span>
                    <span style="font-size: 11px; color: rgba(255, 255, 255, 0.7);"><?= $pct ?>%</span>
                </div>
                <p style="font-size: 11px; color: rgba(255, 255, 255, 0.7); margin: 4px 0 0; font-style: italic;">Logged in today</p>
            </div>

            <!-- 3. Late Arrivals -->
            <div class="card" style="background: linear-gradient(135deg, #F59E0B, #D97706); border: none; color: white; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; min-h: 110px;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
                    <span style="font-size: 11px; font-weight: 600; color: rgba(255, 255, 255, 0.8); text-transform: uppercase; letter-spacing: 0.02em;">Late Arrivals</span>
                    <div style="width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.15); color: #ffffff; flex-shrink: 0;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">schedule</span>
                    </div>
                </div>
                <div style="display: flex; align-items: baseline; gap: 4px; margin-top: auto;">
                    <span style="font-size: 20px; font-weight: 700; color: #ffffff; line-height: 1;">0</span>
                    <span style="font-size: 9px; color: #ffffff; background: rgba(255, 255, 255, 0.2); padding: 2px 6px; border-radius: 4px; font-weight: 600; line-height: 1;">High</span>
                </div>
                <p style="font-size: 11px; color: rgba(255, 255, 255, 0.7); margin: 4px 0 0; font-style: italic;">Grace period expired</p>
            </div>

            <!-- 4. Absent -->
            <div class="card" style="background: linear-gradient(135deg, #EF4444, #DC2626); border: none; color: white; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; min-h: 110px;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
                    <span style="font-size: 11px; font-weight: 600; color: rgba(255, 255, 255, 0.8); text-transform: uppercase; letter-spacing: 0.02em;">Absent</span>
                    <div style="width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.15); color: #ffffff; flex-shrink: 0;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">person_off</span>
                    </div>
                </div>
                <div style="display: flex; align-items: baseline; gap: 4px; margin-top: auto;">
                    <span style="font-size: 20px; font-weight: 700; color: #ffffff; line-height: 1;"><?= $absent ?></span>
                    <span style="font-size: 11px; color: rgba(255, 255, 255, 0.7);">Unnotified</span>
                </div>
                <p style="font-size: 11px; color: rgba(255, 255, 255, 0.7); margin: 4px 0 0; font-style: italic;">No records found</p>
            </div>

            <!-- 5. On Leave -->
            <div class="card" style="background: linear-gradient(135deg, #0D9488, #0F766E); border: none; color: white; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; min-h: 110px;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
                    <span style="font-size: 11px; font-weight: 600; color: rgba(255, 255, 255, 0.8); text-transform: uppercase; letter-spacing: 0.02em;">On Leave</span>
                    <div style="width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.15); color: #ffffff; flex-shrink: 0;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">event_busy</span>
                    </div>
                </div>
                <div style="display: flex; align-items: baseline; gap: 4px; margin-top: auto;">
                    <span style="font-size: 20px; font-weight: 700; color: #ffffff; line-height: 1;">0</span>
                    <span style="font-size: 11px; color: rgba(255, 255, 255, 0.7);">Approved</span>
                </div>
                <p style="font-size: 11px; color: rgba(255, 255, 255, 0.7); margin: 4px 0 0; font-style: italic;">Annual & Sick leave</p>
            </div>

            <!-- 6. Overtime -->
            <div class="card" style="background: linear-gradient(135deg, #6366F1, #4F46E5); border: none; color: white; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; min-h: 110px; border-left: 4px solid #ffffff;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
                    <span style="font-size: 11px; font-weight: 600; color: rgba(255, 255, 255, 0.8); text-transform: uppercase; letter-spacing: 0.02em;">Overtime</span>
                    <div style="width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.15); color: #ffffff; flex-shrink: 0;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">hourglass_empty</span>
                    </div>
                </div>
                <div style="display: flex; align-items: baseline; gap: 4px; margin-top: auto;">
                    <span style="font-size: 20px; font-weight: 700; color: #ffffff; line-height: 1;">0</span>
                    <span style="font-size: 9px; color: #ffffff; background: rgba(255, 255, 255, 0.2); padding: 2px 6px; border-radius: 4px; font-weight: 600; line-height: 1;">Claimed</span>
                </div>
                <p style="font-size: 11px; color: rgba(255, 255, 255, 0.7); margin: 4px 0 0; font-style: italic;">Pending approval</p>
            </div>
            <?php else: ?>
            <?php
                $pending_leaves_count = count(array_filter($leaves, fn($l) => $l['approval_status'] === 'pending'));
                $approved_leaves_count = count(array_filter($leaves, fn($l) => $l['approval_status'] === 'approved'));
            ?>
            <!-- 1. Clock In Today -->
            <div class="card" style="background: linear-gradient(135deg, #2563EB, #1D4ED8); border: none; color: white; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; min-h: 110px;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
                    <span style="font-size: 11px; font-weight: 600; color: rgba(255, 255, 255, 0.8); text-transform: uppercase; letter-spacing: 0.02em;">Jam Masuk</span>
                    <div style="width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.15); color: #ffffff; flex-shrink: 0;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">login</span>
                    </div>
                </div>
                <div style="display: flex; align-items: baseline; gap: 4px; margin-top: auto;">
                    <span style="font-size: 20px; font-weight: 700; color: #ffffff; line-height: 1;"><?= $clock_in ?></span>
                </div>
                <p style="font-size: 11px; color: rgba(255, 255, 255, 0.7); margin: 4px 0 0; font-style: italic;"><?= $clocked_in ? 'Sudah absen masuk' : 'Belum absen masuk' ?></p>
            </div>

            <!-- 2. Clock Out Today -->
            <div class="card" style="background: linear-gradient(135deg, #F59E0B, #D97706); border: none; color: white; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; min-h: 110px;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
                    <span style="font-size: 11px; font-weight: 600; color: rgba(255, 255, 255, 0.8); text-transform: uppercase; letter-spacing: 0.02em;">Jam Pulang</span>
                    <div style="width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.15); color: #ffffff; flex-shrink: 0;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">logout</span>
                    </div>
                </div>
                <div style="display: flex; align-items: baseline; gap: 4px; margin-top: auto;">
                    <span style="font-size: 20px; font-weight: 700; color: #ffffff; line-height: 1;"><?= $clock_out ?></span>
                </div>
                <p style="font-size: 11px; color: rgba(255, 255, 255, 0.7); margin: 4px 0 0; font-style: italic;"><?= $clock_out !== '--:--' ? 'Sudah absen pulang' : 'Belum absen pulang' ?></p>
            </div>

            <!-- 3. Monthly Presence -->
            <div class="card" style="background: linear-gradient(135deg, #10B981, #059669); border: none; color: white; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; min-h: 110px;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
                    <span style="font-size: 11px; font-weight: 600; color: rgba(255, 255, 255, 0.8); text-transform: uppercase; letter-spacing: 0.02em;">Presensi Bulan Ini</span>
                    <div style="width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.15); color: #ffffff; flex-shrink: 0;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">calendar_month</span>
                    </div>
                </div>
                <div style="display: flex; align-items: baseline; gap: 4px; margin-top: auto;">
                    <span style="font-size: 20px; font-weight: 700; color: #ffffff; line-height: 1;"><?= $present_count ?></span>
                    <span style="font-size: 11px; color: rgba(255, 255, 255, 0.7);">/ <?= $days_in_month ?> hr</span>
                </div>
                <p style="font-size: 11px; color: rgba(255, 255, 255, 0.7); margin: 4px 0 0; font-style: italic;">Kehadiran: <?= $att_pct ?>%</p>
            </div>

            <!-- 4. Leave/Permit Requests -->
            <div class="card" style="background: linear-gradient(135deg, #0D9488, #0F766E); border: none; color: white; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; min-h: 110px;">
                <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
                    <span style="font-size: 11px; font-weight: 600; color: rgba(255, 255, 255, 0.8); text-transform: uppercase; letter-spacing: 0.02em;">Pengajuan Izin</span>
                    <div style="width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.15); color: #ffffff; flex-shrink: 0;">
                        <span class="material-symbols-outlined" style="font-size: 18px;">event_busy</span>
                    </div>
                </div>
                <div style="display: flex; align-items: baseline; gap: 4px; margin-top: auto;">
                    <span style="font-size: 20px; font-weight: 700; color: #ffffff; line-height: 1;"><?= $approved_leaves_count + $pending_leaves_count ?></span>
                </div>
                <p style="font-size: 11px; color: rgba(255, 255, 255, 0.7); margin: 4px 0 0; font-style: italic;"><?= $pending_leaves_count ?> menunggu approval</p>
            </div>
            <?php endif; ?>
        </div>

        <!-- 2. Workforce Overview (Bento Grid Style) -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 mb-6">
            <!-- Left Side: Recent Attendance Activity -->
            <div class="lg:col-span-8 flex flex-col">
                <div class="bg-[var(--surface)] rounded-lg flex flex-col h-full overflow-hidden" style="border:1px solid var(--border); box-shadow:var(--shadow);">
                    <div class="px-5 py-4 border-b bg-[var(--surface2)] flex items-center justify-between" style="border-color:var(--border);">
                        <h3 class="font-bold text-[14px] flex items-center gap-2 text-[var(--text-primary)]">
                            <span class="material-symbols-outlined text-[18px]">list_alt</span>
                            Recent Attendance Activity
                        </h3>
                    </div>
                    <div class="overflow-x-auto custom-scrollbar">
                        <table class="w-full text-left table-zebra whitespace-nowrap compact-table">
                            <thead class="bg-[var(--surface2)]">
                                <tr>
                                    <?php if (auth_is_hrd()): ?>
                                        <th class="px-5 py-3 font-semibold text-[11px] text-[var(--text-muted)] uppercase border-b" style="border-color:var(--border);">Employee</th>
                                        <th class="px-5 py-3 font-semibold text-[11px] text-[var(--text-muted)] uppercase border-b" style="border-color:var(--border);">Activity Type</th>
                                        <th class="px-5 py-3 font-semibold text-[11px] text-[var(--text-muted)] uppercase border-b" style="border-color:var(--border);">Time</th>
                                        <th class="px-5 py-3 font-semibold text-[11px] text-[var(--text-muted)] uppercase border-b text-center" style="border-color:var(--border);">Status</th>
                                        <th class="px-5 py-3 font-semibold text-[11px] text-[var(--text-muted)] uppercase border-b text-right" style="border-color:var(--border);">Action</th>
                                    <?php else: ?>
                                        <th class="px-5 py-3 font-semibold text-[11px] text-[var(--text-muted)] uppercase border-b" style="border-color:var(--border);">Sesi</th>
                                        <th class="px-5 py-3 font-semibold text-[11px] text-[var(--text-muted)] uppercase border-b" style="border-color:var(--border);">Metode</th>
                                        <th class="px-5 py-3 font-semibold text-[11px] text-[var(--text-muted)] uppercase border-b" style="border-color:var(--border);">Waktu</th>
                                        <th class="px-5 py-3 font-semibold text-[11px] text-[var(--text-muted)] uppercase border-b" style="border-color:var(--border);">Lokasi</th>
                                        <th class="px-5 py-3 font-semibold text-[11px] text-[var(--text-muted)] uppercase border-b text-center" style="border-color:var(--border);">Status</th>
                                        <th class="px-5 py-3 font-semibold text-[11px] text-[var(--text-muted)] uppercase border-b text-right" style="border-color:var(--border);">Action</th>
                                    <?php endif; ?>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-[var(--border)]">
                                <?php if (empty($recent_activities)): ?>
                                    <tr>
                                        <td colspan="<?= auth_is_hrd() ? 5 : 6 ?>" class="px-5 py-10 text-center text-[12px] text-[var(--text-muted)]">
                                            Belum ada catatan aktivitas absensi
                                        </td>
                                    </tr>
                                <?php endif; ?>
                                <?php 
                                foreach($recent_activities as $act):
                                    if (auth_is_hrd()):
                                        $emp_name = $act['employee_name'] ?? 'Unknown';
                                ?>
                                <tr class="hover:bg-[var(--surface2)] transition-colors">
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-3">
                                            <div class="w-7 h-7 rounded-full flex items-center justify-center text-[10px] font-bold" style="background:var(--surface2); color:var(--primary);"><?= strtoupper(substr($emp_name, 0, 2)) ?></div>
                                            <div>
                                                <p class="text-[13px] font-medium leading-none text-[var(--text-primary)]"><?= h($emp_name) ?></p>
                                                <p class="text-[10px] text-[var(--text-muted)] mt-1">LOG-<?= str_pad($act['user_id'], 3, '0', STR_PAD_LEFT) ?></p>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-[13px] text-[var(--text-primary)]">
                                        <div class="flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-[16px] text-[var(--text-muted)]">
                                                <?= $act['attendance_type'] === 'qr' ? 'qr_code_2' : 'photo_camera' ?>
                                            </span>
                                            <span>Check-in/out via <?= $act['attendance_type'] === 'qr' ? 'Office QR' : 'Selfie WFH' ?></span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-[13px] text-[var(--text-primary)]"><?= date('d M Y, H:i', strtotime($act['attendance_date'] . ' ' . $act['attendance_time'])) ?></td>
                                    <td class="px-5 py-3 text-center">
                                        <?php if (strtolower($act['approval_status']) === 'approved'): ?>
                                            <span class="badge badge-green">APPROVED</span>
                                        <?php elseif (strtolower($act['approval_status']) === 'rejected'): ?>
                                            <span class="badge badge-red">REJECTED</span>
                                        <?php else: ?>
                                            <span class="badge badge-yellow">PENDING</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <?php if ($act['attendance_type'] !== 'qr' && $act['photo_path']): ?>
                                            <button onclick="openPhotoPreview('<?= h($act['photo_path']) ?>')" class="material-symbols-outlined text-[18px] text-[var(--text-muted)] hover:text-[var(--primary)] transition-colors">visibility</button>
                                        <?php else: ?>
                                            <span class="text-[11px] text-[var(--text-muted)]">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php else: 
                                    $is_qr = $act['attendance_type'] === 'qr';
                                ?>
                                <tr class="hover:bg-[var(--surface2)] transition-colors">
                                    <td class="px-5 py-3">
                                        <div class="flex items-center gap-2">
                                            <span class="material-symbols-outlined text-[18px] <?= $act['attendance_flow'] === 'in' ? 'text-primary' : 'text-amber-500' ?>">
                                                <?= $act['attendance_flow'] === 'in' ? 'login' : 'logout' ?>
                                            </span>
                                            <span class="text-[13px] font-medium text-[var(--text-primary)]">
                                                <?= $act['attendance_flow'] === 'in' ? 'Check-In' : 'Check-Out' ?>
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-[13px] text-[var(--text-primary)]">
                                        <div class="flex items-center gap-1.5">
                                            <span class="material-symbols-outlined text-[16px] text-[var(--text-muted)]">
                                                <?= $is_qr ? 'qr_code_2' : 'photo_camera' ?>
                                            </span>
                                            <span><?= $is_qr ? 'Office QR' : 'Selfie WFH' ?></span>
                                        </div>
                                    </td>
                                    <td class="px-5 py-3 text-[13px] text-[var(--text-primary)]">
                                        <?= date('d M Y, H:i', strtotime($act['attendance_date'] . ' ' . $act['attendance_time'])) ?>
                                    </td>
                                    <td class="px-5 py-3 text-[13px] text-[var(--text-primary)] max-w-[150px] truncate" title="<?= h($act['location']) ?>">
                                        <?= h($act['location']) ?>
                                    </td>
                                    <td class="px-5 py-3 text-center">
                                        <?php if (strtolower($act['approval_status']) === 'approved'): ?>
                                            <span class="badge badge-green">APPROVED</span>
                                        <?php elseif (strtolower($act['approval_status']) === 'rejected'): ?>
                                            <span class="badge badge-red">REJECTED</span>
                                        <?php else: ?>
                                            <span class="badge badge-yellow">PENDING</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="px-5 py-3 text-right">
                                        <?php if (!$is_qr && $act['photo_path']): ?>
                                            <button onclick="openPhotoPreview('<?= h($act['photo_path']) ?>')" class="material-symbols-outlined text-[18px] text-[var(--text-muted)] hover:text-[var(--primary)] transition-colors">visibility</button>
                                        <?php else: ?>
                                            <span class="text-[11px] text-[var(--text-muted)]">-</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                                <?php endif; ?>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    <div class="px-5 py-3 bg-[var(--surface2)] border-t flex items-center justify-between text-[11px] text-[var(--text-muted)] font-medium" style="border-color:var(--border);">
                        <span>Showing <?= $total_records > 0 ? $offset + 1 : 0 ?>-<?= min($offset + $per_page, $total_records) ?> of <?= $total_records ?> entries</span>
                        <?php if ($total_pages > 1): ?>
                        <div class="flex items-center gap-1">
                            <?php 
                            $params = $_GET;
                            unset($params['p']);
                            $query_str = http_build_query($params);
                            $base_url = "?$query_str&p=";
                            ?>
                            <?php if ($current_page > 1): ?>
                                <a href="<?= $base_url . ($current_page - 1) ?>" class="w-5 h-5 flex items-center justify-center hover:bg-[var(--surface)] transition-colors rounded border border-[var(--border)]"><span class="material-symbols-outlined text-[14px]">chevron_left</span></a>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): 
                                if ($total_pages > 5 && ($i > 2 && $i < $total_pages - 1) && abs($i - $current_page) > 1) {
                                    if ($i == 3 || $i == $total_pages - 2) echo '<span class="px-1 opacity-50">...</span>';
                                    continue;
                                }
                            ?>
                                <a href="<?= $base_url . $i ?>" class="w-5 h-5 flex items-center justify-center rounded border transition-colors <?= $i === $current_page ? 'bg-[var(--primary)] text-white border-[var(--primary)]' : 'hover:bg-[var(--surface)] border-[var(--border)] text-[var(--text-muted)]' ?>">
                                    <?= $i ?>
                                </a>
                            <?php endfor; ?>

                            <?php if ($current_page < $total_pages): ?>
                                <a href="<?= $base_url . ($current_page + 1) ?>" class="w-5 h-5 flex items-center justify-center hover:bg-[var(--surface)] transition-colors rounded border border-[var(--border)]"><span class="material-symbols-outlined text-[14px]">chevron_right</span></a>
                            <?php endif; ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Right Side: Approvals & Distribution / Employee panels -->
            <div class="lg:col-span-4 flex flex-col gap-6">
                <?php if (auth_is_hrd()): ?>
                <!-- Action Center -->
                <div class="bg-[var(--surface)] rounded-lg p-5 overflow-hidden" style="border:1px solid var(--border); box-shadow:var(--shadow);">
                    <h3 class="font-bold text-[14px] text-[var(--text-primary)] mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">bolt</span> Action Center
                    </h3>
                    <div class="space-y-2">
                        <a href="?page=payroll" class="w-full flex items-center justify-between bg-blue-600 text-white px-4 py-2.5 rounded text-[13px] font-medium hover:opacity-90 transition-opacity">
                            <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">payments</span> Generate Payroll</div>
                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </a>
                        <a href="?page=employees" class="w-full flex items-center justify-between bg-[var(--surface)] border px-4 py-2.5 rounded text-[13px] font-medium text-[var(--text-primary)] hover:bg-[var(--surface2)] transition-colors" style="border-color:var(--border);">
                            <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">person_add</span> Add New Employee</div>
                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </a>
                        <a href="?page=attendance-reports" class="w-full flex items-center justify-between bg-[var(--surface)] border px-4 py-2.5 rounded text-[13px] font-medium text-[var(--text-primary)] hover:bg-[var(--surface2)] transition-colors" style="border-color:var(--border);">
                            <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">analytics</span> Export Reports</div>
                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </a>
                    </div>
                </div>

                <!-- Pending Approvals -->
                <div class="bg-[var(--surface)] rounded-lg flex flex-col overflow-hidden" style="border:1px solid var(--border); box-shadow:var(--shadow);">
                    <div class="px-5 py-3 border-b bg-[var(--surface2)] flex items-center justify-between" style="border-color:var(--border);">
                        <h3 class="font-bold text-[14px] flex items-center gap-2 text-[var(--primary)]">
                            <span class="material-symbols-outlined text-[18px]">notification_important</span> Pending Approvals
                        </h3>
                        <?php 
                        $pending_list = array_slice(array_filter($leaves, fn($l) => $l['approval_status'] === 'pending'), 0, 2);
                        ?>
                        <span class="badge badge-blue rounded-full px-1.5 py-0.5"><?= count($pending_list) ?></span>
                    </div>
                    <div class="divide-y divide-[var(--border)]">
                        <?php foreach ($pending_list as $l): ?>
                        <div class="p-4 hover:bg-[var(--surface2)] transition-colors flex items-start justify-between">
                            <div>
                                <p class="text-[13px] font-bold text-[var(--text-primary)] leading-tight"><?= h($l['leave_type']) ?> Request</p>
                                <p class="text-[11px] text-[var(--text-muted)] mt-1">Request by <b class="text-[var(--text-primary)]"><?= h($l['employee_name']) ?></b> for 2 days</p>
                                <p class="text-[10px] text-[var(--primary)] mt-2">Requested today</p>
                            </div>
                            <div class="flex gap-2">
                                <a href="?page=leaves&action=approve&id=<?= $l['id'] ?>" class="w-7 h-7 bg-[var(--surface2)] text-green-500 border border-[var(--border)] flex items-center justify-center rounded hover:bg-green-500 hover:text-white transition-colors"><span class="material-symbols-outlined text-[18px]">check</span></a>
                                <a href="?page=leaves&action=reject&id=<?= $l['id'] ?>" class="w-7 h-7 bg-[var(--surface2)] text-red-500 border border-[var(--border)] flex items-center justify-center rounded hover:bg-red-500 hover:text-white transition-colors"><span class="material-symbols-outlined text-[18px]">close</span></a>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if(empty($pending_list)): ?>
                        <div class="p-5 text-center text-[12px] text-[var(--text-muted)]">No pending approvals</div>
                        <?php endif; ?>
                    </div>
                    <a class="block text-center py-3 text-[11px] font-bold text-[var(--primary)] hover:underline border-t bg-[var(--surface)]" style="border-color:var(--border);" href="?page=leaves">View all approval requests</a>
                </div>

                <!-- Department Distribution -->
                <div class="bg-[var(--surface)] rounded-lg p-5 overflow-hidden" style="border:1px solid var(--border); box-shadow:var(--shadow);">
                    <h3 class="font-bold text-[14px] text-[var(--text-primary)] mb-5 flex items-center justify-between">
                        <span>Department Distribution</span>
                        <span class="material-symbols-outlined text-[18px] text-[var(--text-muted)]">pie_chart</span>
                    </h3>
                    <div class="space-y-4">
                        <?php 
                        $colors = ['bg-blue-600', 'bg-gray-600', 'bg-gray-400', 'bg-red-500', 'bg-purple-500'];
                        $i = 0;
                        $depts = array_count_values(array_column($employees, 'department'));
                        arsort($depts);
                        $total_emp = count($employees);
                        foreach (array_slice($depts, 0, 3) as $name => $count): 
                            $pct = $total_emp > 0 ? round(($count/$total_emp)*100) : 0;
                        ?>
                        <div>
                            <div class="flex justify-between text-[11px] font-medium mb-1.5 text-[var(--text-primary)]">
                                <span><?= h($name ?: 'Unassigned') ?></span>
                                <span><?= $pct ?>%</span>
                            </div>
                            <div class="h-1.5 w-full bg-[var(--surface2)] rounded-full overflow-hidden">
                                <div class="h-full <?= $colors[$i++ % 5] ?> rounded-full" style="width: <?= $pct ?>%"></div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php else: ?>
                <!-- Employee Action Center -->
                <div class="bg-[var(--surface)] rounded-lg p-5 overflow-hidden" style="border:1px solid var(--border); box-shadow:var(--shadow);">
                    <h3 class="font-bold text-[14px] text-[var(--text-primary)] mb-4 flex items-center gap-2">
                        <span class="material-symbols-outlined text-[18px]">bolt</span> Action Center
                    </h3>
                    <div class="space-y-2">
                        <a href="?page=attendance" class="w-full flex items-center justify-between bg-blue-600 text-white px-4 py-2.5 rounded text-[13px] font-medium hover:opacity-90 transition-opacity">
                            <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">fingerprint</span> Absensi Sekarang</div>
                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </a>
                        <a href="?page=leaves" class="w-full flex items-center justify-between bg-[var(--surface)] border px-4 py-2.5 rounded text-[13px] font-medium text-[var(--text-primary)] hover:bg-[var(--surface2)] transition-colors" style="border-color:var(--border);">
                            <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">event_busy</span> Ajukan Cuti / Izin</div>
                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </a>
                        <a href="?page=payroll" class="w-full flex items-center justify-between bg-[var(--surface)] border px-4 py-2.5 rounded text-[13px] font-medium text-[var(--text-primary)] hover:bg-[var(--surface2)] transition-colors" style="border-color:var(--border);">
                            <div class="flex items-center gap-2"><span class="material-symbols-outlined text-[18px]">account_balance_wallet</span> Lihat Slip Gaji</div>
                            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
                        </a>
                    </div>
                </div>

                <!-- Leave Status Summary -->
                <div class="bg-[var(--surface)] rounded-lg flex flex-col overflow-hidden" style="border:1px solid var(--border); box-shadow:var(--shadow);">
                    <div class="px-5 py-3 border-b bg-[var(--surface2)] flex items-center justify-between" style="border-color:var(--border);">
                        <h3 class="font-bold text-[14px] flex items-center gap-2 text-[var(--primary)]">
                            <span class="material-symbols-outlined text-[18px]">notification_important</span> Status Pengajuan Izin
                        </h3>
                        <span class="badge badge-blue rounded-full px-1.5 py-0.5"><?= count($leaves) ?></span>
                    </div>
                    <div class="divide-y divide-[var(--border)]">
                        <?php 
                        $employee_leaves = array_slice($leaves, 0, 3);
                        foreach ($employee_leaves as $l): 
                        ?>
                        <div class="p-4 hover:bg-[var(--surface2)] transition-colors flex items-start justify-between">
                            <div>
                                <p class="text-[13px] font-bold text-[var(--text-primary)] leading-tight"><?= h(leave_type_label($l['leave_type'])) ?></p>
                                <p class="text-[11px] text-[var(--text-muted)] mt-1">Tanggal: <?= format_date($l['leave_start']) ?> s/d <?= format_date($l['leave_end']) ?></p>
                                <p class="text-[10px] text-[var(--text-muted)] mt-1 italic">Reason: <?= h($l['leave_reason']) ?></p>
                            </div>
                            <div>
                                <?php if ($l['approval_status'] === 'approved'): ?>
                                    <span class="badge badge-green">APPROVED</span>
                                <?php elseif ($l['approval_status'] === 'rejected'): ?>
                                    <span class="badge badge-red">REJECTED</span>
                                <?php else: ?>
                                    <span class="badge badge-yellow">PENDING</span>
                                <?php endif; ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if(empty($employee_leaves)): ?>
                        <div class="p-5 text-center text-[12px] text-[var(--text-muted)]">Belum ada pengajuan izin</div>
                        <?php endif; ?>
                    </div>
                    <a class="block text-center py-3 text-[11px] font-bold text-[var(--primary)] hover:underline border-t bg-[var(--surface)]" style="border-color:var(--border);" href="?page=leaves">Lihat Semua Pengajuan</a>
                </div>

                <!-- Agenda & Company Calendar -->
                <div class="bg-[var(--surface)] rounded-lg p-5 overflow-hidden" style="border:1px solid var(--border); box-shadow:var(--shadow);">
                    <h3 class="font-bold text-[14px] text-[var(--text-primary)] mb-5 flex items-center justify-between">
                        <span>Agenda & Hari Libur</span>
                        <span class="material-symbols-outlined text-[18px] text-[var(--text-muted)]">calendar_month</span>
                    </h3>
                    <div class="space-y-4">
                        <?php 
                        $upcoming_events = [];
                        $today_time = strtotime($today);
                        foreach ($calendar_events as $ev) {
                            if (strtotime($ev['event_date']) >= $today_time) {
                                $upcoming_events[] = $ev;
                            }
                        }
                        usort($upcoming_events, fn($a, $b) => strcmp($a['event_date'], $b['event_date']));
                        $upcoming_events = array_slice($upcoming_events, 0, 3);
                        
                        foreach ($upcoming_events as $ev): 
                            $is_holiday_ev = $ev['category'] === 'holiday';
                            $category_class = $is_holiday_ev ? 'bg-red-500' : 'bg-blue-600';
                        ?>
                        <div class="flex items-start gap-3">
                            <span class="w-2 h-2 rounded-full mt-1.5 <?= $category_class ?> shrink-0"></span>
                            <div class="flex-1">
                                <div class="flex justify-between text-[11px] font-medium text-[var(--text-primary)]">
                                    <span class="font-bold"><?= h($ev['title']) ?></span>
                                    <span class="text-[9px] text-[var(--text-muted)]"><?= date('d M Y', strtotime($ev['event_date'])) ?></span>
                                </div>
                                <p class="text-[10px] text-[var(--text-muted)] mt-0.5"><?= h($ev['description']) ?></p>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        <?php if(empty($upcoming_events)): ?>
                        <div class="text-center text-[11px] text-[var(--text-muted)]">Tidak ada agenda terdekat</div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div><!-- /dash-left -->
</div><!-- /dash-layout -->

<!-- MODAL: Photo Preview -->
<div id="photoPreviewModal" style="display:none;" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/85 backdrop-blur-md p-4" onclick="if(event.target===this)closeModal('photoPreviewModal')">
    <div class="relative max-w-2xl w-full flex flex-col items-center">
        <button onclick="closeModal('photoPreviewModal')" class="absolute -top-10 right-0 text-white/50 hover:text-white flex items-center gap-2 font-bold text-[9px] transition-colors">
            <span>Dismiss</span>
            <span class="material-symbols-outlined text-base">close</span>
        </button>
        <div class="w-full flex justify-center overflow-hidden rounded-lg">
            <img id="previewImg" src="" class="max-h-[85vh] w-auto h-auto rounded-lg shadow-2xl border-none" alt="Attendance Identity">
        </div>
    </div>
</div>

<script>
function openPhotoPreview(src) {
    const modal = document.getElementById('photoPreviewModal');
    const img = document.getElementById('previewImg');
    if (modal && img) {
        img.src = src;
        modal.style.display = 'flex';
    }
}
</script>

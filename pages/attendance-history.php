<?php
// pages/attendance-history.php - Detailed Employee Attendance History
$pdo = db();

// Access Control
$target_user_id = $_GET['user_id'] ?? $user['id'];
if (!auth_is_hrd() && $target_user_id !== $user['id']) {
    $target_user_id = $user['id']; // Force own profile if not HRD
}

// Fetch Employee Details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$target_user_id]);
$emp = $stmt->fetch();

if (!$emp) {
    echo "<div class='p-8 text-center text-rose-500 font-bold'>Pegawai tidak ditemukan.</div>";
    return;
}

// Filters
$sel_month = (int)($_GET['month'] ?? date('n'));
$sel_year  = (int)($_GET['year']  ?? date('Y'));

// Calculate Metrics using helper
$payroll = calculate_emp_payroll_details($emp, $sel_month, $sel_year);

// Fetch all attendance logs for grouping
$stmt_logs = $pdo->prepare("SELECT * FROM attendance WHERE user_id = ? AND MONTH(attendance_date) = ? AND YEAR(attendance_date) = ? ORDER BY attendance_date ASC, attendance_time ASC");
$stmt_logs->execute([$target_user_id, $sel_month, $sel_year]);
$logs = $stmt_logs->fetchAll();

// Group logs by date
$att_by_date = [];
foreach ($logs as $log) {
    $date = $log['attendance_date'];
    $flow = $log['attendance_flow'];
    $att_by_date[$date][$flow] = $log;
}

// Fetch holidays for checking
$holidays = [];
$stmt_holidays = $pdo->prepare("SELECT event_date, title FROM calendar_events WHERE category = 'holiday' AND MONTH(event_date) = ? AND YEAR(event_date) = ?");
$stmt_holidays->execute([$sel_month, $sel_year]);
$holiday_rows = $stmt_holidays->fetchAll();
foreach ($holiday_rows as $hr) {
    $holidays[$hr['event_date']] = $hr['title'];
}

// Month details
$num_days = (int)date('t', strtotime("$sel_year-$sel_month-01"));
$today_str = date('Y-m-d');
?>

<div class="space-y-8 performance-page-container">

    <!-- ══ Header Section ══ -->
    <header class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
        <div class="flex items-center gap-4">
            <a href="?page=people" class="w-10 h-10 rounded-lg bg-surface border border-border flex items-center justify-center text-on-surface-variant hover:bg-surface2 transition-all">
                <span class="material-symbols-outlined text-lg">arrow_back</span>
            </a>
            <div>
                <div class="flex items-center gap-3 mb-1">
                    <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center shrink-0">
                        <span class="material-symbols-outlined text-2xl font-bold">calendar_month</span>
                    </div>
                    <h1 data-theme-text class="text-2xl font-bold leading-none">Riwayat Absensi</h1>
                </div>
                <p data-theme-muted class="text-[10px] font-bold ml-1 opacity-50">Log absensi individu dan analisis kedisiplinan</p>
            </div>
        </div>

        <!-- Filters Form -->
        <form method="GET" class="flex items-center gap-2 bg-surface p-1 rounded-lg border border-border shadow-sm">
            <input type="hidden" name="page" value="attendance-history">
            <input type="hidden" name="user_id" value="<?= h($target_user_id) ?>">
            
            <select name="month" class="bg-transparent border-none text-[11px] font-bold focus:ring-0 text-on-surface py-1">
                <?php for ($m=1; $m<=12; $m++): ?>
                    <option value="<?= $m ?>" <?= $sel_month === $m ? 'selected' : '' ?>><?= date('F', mktime(0,0,0,$m,1,2024)) ?></option>
                <?php endfor; ?>
            </select>
            <div class="w-px h-4 bg-border"></div>
            <select name="year" class="bg-transparent border-none text-[11px] font-bold focus:ring-0 text-on-surface py-1">
                <?php 
                $cur_y = (int)date('Y');
                for ($y = $cur_y - 2; $y <= $cur_y + 1; $y++): 
                ?>
                    <option value="<?= $y ?>" <?= $sel_year === $y ? 'selected' : '' ?>><?= $y ?></option>
                <?php endfor; ?>
            </select>
            <button type="submit" class="p-1.5 bg-primary text-white rounded-md hover:opacity-90 active:scale-95 transition-all">
                <span class="material-symbols-outlined text-[16px] block">search</span>
            </button>
        </form>
    </header>

    <!-- ══ Employee Summary Card ══ -->
    <div data-theme-card class="bg-surface rounded-lg border border-border overflow-hidden shadow-sm flex flex-col md:flex-row divide-y md:divide-y-0 md:divide-x divide-border">
        <!-- Profile info -->
        <div class="p-6 flex items-center gap-4 min-w-[280px]">
            <div class="w-16 h-16 rounded-lg overflow-hidden shrink-0 border border-border">
                <?php if (!empty($emp['photo_profile'])): ?>
                    <img src="<?= h($emp['photo_profile']) ?>" class="w-full h-full object-cover">
                <?php else: ?>
                    <div class="w-full h-full bg-surface2 flex items-center justify-center text-primary font-bold text-xl">
                        <?= avatar_initials($emp['name']) ?>
                    </div>
                <?php endif; ?>
            </div>
            <div>
                <h3 data-theme-text class="text-base font-bold leading-tight"><?= h($emp['name']) ?></h3>
                <p data-theme-muted class="text-[10px] font-bold opacity-45 mt-1"><?= h($emp['position']) ?> · <?= h($emp['department']) ?></p>
                <span class="inline-block mt-2 px-2 py-0.5 bg-surface2 border border-border text-[9px] font-mono rounded text-[var(--text-muted)]">ID: <?= h($emp['id']) ?></span>
            </div>
        </div>

        <!-- Attendance Stats Grid -->
        <div class="grow p-6 grid grid-cols-2 sm:grid-cols-4 gap-4 text-center">
            <div class="p-3 bg-surface2 rounded-lg border border-border">
                <span data-theme-muted class="block text-[9px] font-bold uppercase tracking-wider opacity-50">Hari Kerja Wajib</span>
                <span data-theme-text class="text-xl font-extrabold mt-1 block"><?= $payroll['expected_workdays'] ?> Hari</span>
            </div>
            <div class="p-3 bg-emerald-500/5 rounded-lg border border-emerald-500/10">
                <span class="block text-[9px] font-bold uppercase tracking-wider text-emerald-500 opacity-80">Hadir</span>
                <span class="text-xl font-extrabold text-emerald-600 dark:text-emerald-400 mt-1 block"><?= $payroll['attended_days'] ?> Hari</span>
            </div>
            <div class="p-3 bg-rose-500/5 rounded-lg border border-rose-500/10">
                <span class="block text-[9px] font-bold uppercase tracking-wider text-rose-500 opacity-80">Absen</span>
                <span class="text-xl font-extrabold text-rose-500 mt-1 block"><?= $payroll['absent_days'] ?> Hari</span>
            </div>
            <div class="p-3 bg-amber-500/5 rounded-lg border border-amber-500/10">
                <span class="block text-[9px] font-bold uppercase tracking-wider text-amber-500 opacity-80">Terlambat (><?= $payroll['late_tolerance_minutes'] ?>m)</span>
                <span class="text-xl font-extrabold text-amber-500 mt-1 block"><?= $payroll['late_days'] ?> Kali</span>
            </div>
        </div>
    </div>

    <!-- ══ Daily Log Table ══ -->
    <div data-theme-card class="bg-surface rounded-lg border border-border shadow-sm overflow-hidden pb-12">
        <div class="px-6 py-4 border-b border-border bg-[var(--surface2)]">
            <h3 data-theme-text class="font-bold text-xs uppercase tracking-wider">Log Detail Bulanan</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse text-xs">
                <thead>
                    <tr class="bg-surface2 text-[10px] font-bold uppercase tracking-wider text-[var(--text-muted)] border-b border-border">
                        <th class="px-6 py-3.5">Tanggal</th>
                        <th class="px-6 py-3.5">Hari</th>
                        <th class="px-6 py-3.5">Check-In</th>
                        <th class="px-6 py-3.5">Check-Out</th>
                        <th class="px-6 py-3.5">Durasi Kerja</th>
                        <th class="px-6 py-3.5">Status Kehadiran</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <?php 
                    for ($d = 1; $d <= $num_days; $d++):
                        $date_str = sprintf('%04d-%02d-%02d', $sel_year, $sel_month, $d);
                        $day_of_week = date('N', strtotime($date_str)); // 1 (Mon) - 7 (Sun)
                        $day_name = date('l', strtotime($date_str));
                        $is_weekend = ($day_of_week > 5);
                        $is_holiday = isset($holidays[$date_str]);
                        $holiday_title = $is_holiday ? $holidays[$date_str] : '';
                        
                        // Default values
                        $check_in_lbl = '--:--';
                        $check_in_badge = '';
                        $check_out_lbl = '--:--';
                        $duration_lbl = '-';
                        $status_lbl = 'Belum Absen';
                        $status_class = 'text-[var(--text-muted)] opacity-60';

                        // Check future date
                        $is_future = ($date_str > $today_str);

                        if ($is_future) {
                            $status_lbl = 'Masa Depan';
                            $status_class = 'text-gray-400 dark:text-neutral-600 italic';
                        } elseif ($is_weekend) {
                            $status_lbl = 'Akhir Pekan';
                            $status_class = 'text-amber-500/60 font-medium';
                        } elseif ($is_holiday) {
                            $status_lbl = 'Libur: ' . $holiday_title;
                            $status_class = 'text-rose-500/60 font-semibold';
                        } else {
                            // Weekday past/present day: check logs
                            $has_in = isset($att_by_date[$date_str]['in']);
                            $has_out = isset($att_by_date[$date_str]['out']);

                            if ($has_in) {
                                $in_log = $att_by_date[$date_str]['in'];
                                $check_in_lbl = date('H:i:s', strtotime($in_log['attendance_time']));
                                
                                // Late details
                                $is_late_flag = ($in_log['status'] ?? '') === 'late';
                                $check_in_time = $in_log['attendance_time'];
                                $work_start_time = $payroll['work_start_time'];
                                
                                if (strtotime($check_in_time) > strtotime($work_start_time)) {
                                    $diff_seconds = strtotime($check_in_time) - strtotime($work_start_time);
                                    $diff_minutes = (int)($diff_seconds / 60);
                                    
                                    if ($diff_minutes > $payroll['late_tolerance_minutes']) {
                                        $check_in_badge = "<span class='px-2 py-0.5 bg-rose-500/10 text-rose-500 rounded text-[9px] font-bold ml-2'>Telat {$diff_minutes}m</span>";
                                    } else {
                                        $check_in_badge = "<span class='px-2 py-0.5 bg-amber-500/10 text-amber-600 rounded text-[9px] font-bold ml-2'>Telat {$diff_minutes}m (Ditoleransi)</span>";
                                    }
                                } else {
                                    $check_in_badge = "<span class='px-2 py-0.5 bg-emerald-500/10 text-emerald-600 rounded text-[9px] font-bold ml-2'>Tepat Waktu</span>";
                                }

                                if ($has_out) {
                                    $out_log = $att_by_date[$date_str]['out'];
                                    $check_out_lbl = date('H:i:s', strtotime($out_log['attendance_time']));
                                    
                                    // Work duration
                                    $in_ts = strtotime($in_log['attendance_date'] . ' ' . $in_log['attendance_time']);
                                    $out_ts = strtotime($out_log['attendance_date'] . ' ' . $out_log['attendance_time']);
                                    if ($out_ts > $in_ts) {
                                        $diff_hr = ($out_ts - $in_ts) / 3600.0;
                                        $duration_lbl = round($diff_hr, 1) . ' jam';
                                    }
                                }

                                $status_lbl = 'Hadir';
                                $status_class = 'text-emerald-500 font-bold';
                            } else {
                                // Absent or Leave
                                // Check if falls in approved leave
                                $is_leave = false;
                                // We query leaves directly inside loop or we can just fetch once. For simplicity, since expected days is small:
                                $stmt_leave = $pdo->prepare("SELECT leave_type FROM leaves WHERE user_id = ? AND approval_status = 'approved' AND ? BETWEEN leave_start AND leave_end LIMIT 1");
                                $stmt_leave->execute([$target_user_id, $date_str]);
                                $leave_row = $stmt_leave->fetch();
                                
                                if ($leave_row) {
                                    $status_lbl = 'Izin: ' . strtoupper($leave_row['leave_type']);
                                    $status_class = 'text-blue-500 font-semibold';
                                } else {
                                    $status_lbl = 'Absen (Tidak Hadir)';
                                    $status_class = 'text-rose-500 font-bold';
                                }
                            }
                        }
                    ?>
                        <tr class="hover:bg-surface2/50 transition-colors">
                            <td class="px-6 py-3.5 font-medium text-[var(--text-primary)]"><?= date('d F Y', strtotime($date_str)) ?></td>
                            <td class="px-6 py-3.5 text-[var(--text-muted)]"><?= $day_name ?></td>
                            <td class="px-6 py-3.5 font-mono">
                                <span><?= $check_in_lbl ?></span>
                                <?= $check_in_badge ?>
                            </td>
                            <td class="px-6 py-3.5 font-mono"><?= $check_out_lbl ?></td>
                            <td class="px-6 py-3.5 font-medium"><?= $duration_lbl ?></td>
                            <td class="px-6 py-3.5">
                                <span class="<?= $status_class ?>"><?= $status_lbl ?></span>
                            </td>
                        </tr>
                    <?php endfor; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

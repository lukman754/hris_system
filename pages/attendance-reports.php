<?php
// pages/attendance-reports.php – Attendance Intelligence Hub (HRD only)
$all_attendance = get_attendance();

// Filter by period shortcut
$period = $_GET['period'] ?? '';
if ($period === 'week') {
    $start = date('Y-m-d', strtotime('monday this week'));
    $end = date('Y-m-d', strtotime('sunday this week'));
    $all_attendance = array_filter($all_attendance, fn($a) => $a['attendance_date'] >= $start && $a['attendance_date'] <= $end);
} elseif ($period === 'month') {
    $start = date('Y-m-01');
    $end = date('Y-m-t');
    $all_attendance = array_filter($all_attendance, fn($a) => $a['attendance_date'] >= $start && $a['attendance_date'] <= $end);
}

// Filter by custom date range
if (!empty($_GET['start_date'])) {
    $all_attendance = array_filter($all_attendance, fn($a) => $a['attendance_date'] >= $_GET['start_date']);
}
if (!empty($_GET['end_date'])) {
    $all_attendance = array_filter($all_attendance, fn($a) => $a['attendance_date'] <= $_GET['end_date']);
}

// Sort
usort($all_attendance, fn($a,$b) => strtotime($b['attendance_date'] . ' ' . $b['attendance_time']) - strtotime($a['attendance_date'] . ' ' . $a['attendance_time']));

// Pagination
$per_page = 15;
$total_records = count($all_attendance);
$total_pages = ceil($total_records / $per_page);
$current_page = max(1, min($total_pages, (int)($_GET['p'] ?? 1)));
$offset = ($current_page - 1) * $per_page;
$attendance = array_slice($all_attendance, $offset, $per_page);
?>

<div class="space-y-8 performance-page-container">
    
    <!-- ══ Header Section ══ -->
    <header class="flex flex-col lg:flex-row lg:items-end justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-3xl font-bold">query_stats</span>
                </div>
                <h1 data-theme-text class="text-4xl font-bold  leading-none">Attendance Logs</h1>
            </div>
            <div class="flex items-center gap-3 ml-1">
                <p data-theme-muted class="text-[10px] font-bold   opacity-50">Enterprise Compliance Activity</p>
                <span class="w-1 h-1 rounded-full bg-border"></span>
                <span class="text-[10px] font-bold text-primary"><?= $total_records ?> Records</span>
            </div>
        </div>
        
        <div class="flex flex-col md:flex-row items-center gap-3">
            <!-- Quick Filters -->
            <div class="flex items-center gap-1.5 bg-surface p-1 rounded-xl border border-border shadow-sm">
                <a href="?page=attendance-reports&period=week" class="px-4 py-2 rounded-lg text-[10px] font-bold transition-all <?= $period === 'week' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-on-surface/50 hover:bg-surface2' ?>">MINGGU</a>
                <a href="?page=attendance-reports&period=month" class="px-4 py-2 rounded-lg text-[10px] font-bold transition-all <?= $period === 'month' ? 'bg-primary text-white shadow-lg shadow-primary/20' : 'text-on-surface/50 hover:bg-surface2' ?>">BULAN</a>
            </div>

            <!-- Custom Date Range -->
            <form method="GET" class="flex items-center gap-2 bg-surface p-1.5 px-3 rounded-xl border border-border shadow-sm">
                <input type="hidden" name="page" value="attendance-reports">
                <div class="flex items-center gap-2">
                    <input type="date" name="start_date" value="<?= $_GET['start_date'] ?? '' ?>" class="bg-transparent text-[10px] font-bold outline-none border-none p-1 focus:ring-0 w-28">
                    <span class="text-[10px] opacity-20 font-bold">TO</span>
                    <input type="date" name="end_date" value="<?= $_GET['end_date'] ?? '' ?>" class="bg-transparent text-[10px] font-bold outline-none border-none p-1 focus:ring-0 w-28">
                </div>
                <div class="w-px h-4 bg-border mx-1"></div>
                <button type="submit" class="bg-on-surface text-surface text-[10px] font-bold px-4 py-1.5 rounded-lg hover:opacity-90 transition-all">
                    APPLY
                </button>
            </form>

            <?php if ($period || !empty($_GET['start_date'])): ?>
                <a href="?page=attendance-reports" class="w-9 h-9 flex items-center justify-center rounded-xl bg-rose-500/10 text-rose-500 border border-rose-500/20 hover:bg-rose-500 hover:text-white transition-all shadow-sm" title="Reset Filters">
                    <span class="material-symbols-outlined text-sm">filter_alt_off</span>
                </a>
            <?php endif; ?>
        </div>
    </header>

    <!-- ══ Activity Ledger Table ══ -->
    <div data-theme-card class="bg-surface rounded-xl border border-border overflow-hidden shadow-sm">
        <div class="p-4 border-b border-border flex justify-between items-center bg-surface2/30">
            <h3 class="text-xs font-bold  opacity-70">Activity Stream</h3>
            <div class="flex items-center gap-2 bg-surface border border-border px-3 py-1.5 rounded-lg w-64">
                <span class="material-symbols-outlined text-sm opacity-30">search</span>
                <input type="text" id="logSearch" placeholder="Filter current view..." oninput="filterLogs(this.value)" class="bg-transparent border-none text-[10px] font-bold w-full focus:ring-0 p-0">
            </div>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-surface2/50 border-b border-border">
                        <th class="px-6 py-4 text-[10px] font-bold uppercase opacity-40">Employee</th>
                        <th class="px-6 py-4 text-[10px] font-bold uppercase opacity-40">Date & Time</th>
                        <th class="px-6 py-4 text-[10px] font-bold uppercase opacity-40">Method</th>
                        <th class="px-6 py-4 text-[10px] font-bold uppercase opacity-40">Location</th>
                        <th class="px-6 py-4 text-[10px] font-bold uppercase opacity-40">Status</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-border">
                    <?php if (empty($attendance)): ?>
                        <tr>
                            <td colspan="5" class="px-6 py-20 text-center">
                                <span class="material-symbols-outlined text-4xl opacity-10 mb-2">database_off</span>
                                <p class="text-xs font-bold opacity-30">No records found for the selected range</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                    <?php foreach ($attendance as $rec): 
                        $st = $rec['approval_status'];
                        $st_clr = $st==='approved'?'bg-emerald-500':($st==='pending'?'bg-amber-500':'bg-rose-500');
                        $st_text = $st==='approved'?'text-emerald-500':($st==='pending'?'text-amber-500':'text-rose-500');
                    ?>
                    <tr class="attendance-row hover:bg-surface2/30 transition-colors" data-content="<?= strtolower($rec['employee_name'] . ' ' . $rec['location']) ?>">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-lg bg-primary/5 text-primary flex items-center justify-center font-bold text-[10px] border border-primary/10">
                                    <?= avatar_initials($rec['employee_name']) ?>
                                </div>
                                <span data-theme-text class="text-xs font-bold"><?= h($rec['employee_name']) ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col">
                                <span data-theme-text class="text-xs font-bold"><?= date('d M Y', strtotime($rec['attendance_date'])) ?></span>
                                <span data-theme-muted class="text-[9px] font-bold opacity-40"><?= h($rec['attendance_time']) ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="material-symbols-outlined text-sm opacity-30"><?= $rec['attendance_type'] === 'qr' ? 'qr_code_2' : 'face' ?></span>
                                <span class="text-[10px] font-bold opacity-60"><?= $rec['attendance_type'] === 'qr' ? 'QR' : 'Photo' ?></span>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span data-theme-text class="text-[10px] font-bold opacity-60 truncate max-w-[150px] inline-block"><?= h($rec['location']) ?></span>
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-2">
                                <span class="w-1.5 h-1.5 rounded-full <?= $st_clr ?>"></span>
                                <span class="text-[9px] font-bold uppercase <?= $st_text ?>"><?= h($st) ?></span>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination Controls -->
        <?php if ($total_pages > 1): ?>
        <div class="p-4 border-t border-border bg-surface2/10 flex justify-between items-center">
            <p class="text-[10px] font-bold opacity-40">Showing <?= $offset + 1 ?> to <?= min($offset + $per_page, $total_records) ?> of <?= $total_records ?> entries</p>
            <div class="flex gap-1">
                <?php 
                $params = $_GET;
                unset($params['p']);
                $query_str = http_build_query($params);
                $base_url = "?$query_str&p=";
                ?>
                
                <?php if ($current_page > 1): ?>
                    <a href="<?= $base_url . ($current_page - 1) ?>" class="w-8 h-8 rounded-lg border border-border flex items-center justify-center hover:bg-surface2 transition-all">
                        <span class="material-symbols-outlined text-sm">chevron_left</span>
                    </a>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): 
                    if ($total_pages > 7 && ($i > 2 && $i < $total_pages - 1) && abs($i - $current_page) > 1) {
                        if ($i == 3 || $i == $total_pages - 2) echo '<span class="px-2 opacity-20">...</span>';
                        continue;
                    }
                ?>
                    <a href="<?= $base_url . $i ?>" class="w-8 h-8 rounded-lg border <?= $i === $current_page ? 'bg-primary border-primary text-white shadow-lg shadow-primary/20' : 'border-border hover:bg-surface2' ?> flex items-center justify-center text-[10px] font-bold transition-all">
                        <?= $i ?>
                    </a>
                <?php endfor; ?>

                <?php if ($current_page < $total_pages): ?>
                    <a href="<?= $base_url . ($current_page + 1) ?>" class="w-8 h-8 rounded-lg border border-border flex items-center justify-center hover:bg-surface2 transition-all">
                        <span class="material-symbols-outlined text-sm">chevron_right</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
function filterLogs(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.attendance-row').forEach(row => {
        row.style.display = row.getAttribute('data-content').includes(q) ? 'block' : 'none';
    });
}
</script>

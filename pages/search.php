<?php
// pages/search.php - Global Search Results
$pdo = db();

$query = trim($_GET['q'] ?? '');
$results_found = false;

$employees = [];
$announcements = [];
$leaves = [];
$attendance = [];

if (!empty($query)) {
    $search_param = "%$query%";

    // 1. Search Employees / Users
    // If HRD, search all users. If regular employee, search all users (since it's a directory search).
    $stmt = $pdo->prepare("SELECT id, name, position, department, email, photo_profile, phone_number FROM users WHERE name LIKE ? OR position LIKE ? OR department LIKE ? OR email LIKE ? ORDER BY name ASC");
    $stmt->execute([$search_param, $search_param, $search_param, $search_param]);
    $employees = $stmt->fetchAll();

    // 2. Search Announcements
    $stmt = $pdo->prepare("SELECT id, title, content, priority, created_at FROM announcements WHERE title LIKE ? OR content LIKE ? ORDER BY created_at DESC");
    $stmt->execute([$search_param, $search_param]);
    $announcements = $stmt->fetchAll();

    // 3. Search Leaves
    if (auth_is_hrd()) {
        $stmt = $pdo->prepare("SELECT l.*, u.name as employee_name FROM leaves l JOIN users u ON l.user_id = u.id WHERE u.name LIKE ? OR l.leave_reason LIKE ? OR l.leave_type LIKE ? ORDER BY l.created_at DESC");
        $stmt->execute([$search_param, $search_param, $search_param]);
    } else {
        $stmt = $pdo->prepare("SELECT l.*, u.name as employee_name FROM leaves l JOIN users u ON l.user_id = u.id WHERE l.user_id = ? AND (l.leave_reason LIKE ? OR l.leave_type LIKE ?) ORDER BY l.created_at DESC");
        $stmt->execute([$user['id'], $search_param, $search_param]);
    }
    $leaves = $stmt->fetchAll();

    // 4. Search Attendance Logs
    if (auth_is_hrd()) {
        $stmt = $pdo->prepare("SELECT a.*, u.name as employee_name FROM attendance a JOIN users u ON a.user_id = u.id WHERE u.name LIKE ? OR a.location LIKE ? OR a.status LIKE ? OR a.attendance_flow LIKE ? ORDER BY a.attendance_date DESC, a.attendance_time DESC");
        $stmt->execute([$search_param, $search_param, $search_param, $search_param]);
    } else {
        $stmt = $pdo->prepare("SELECT a.*, u.name as employee_name FROM attendance a JOIN users u ON a.user_id = u.id WHERE a.user_id = ? AND (a.location LIKE ? OR a.status LIKE ? OR a.attendance_flow LIKE ?) ORDER BY a.attendance_date DESC, a.attendance_time DESC");
        $stmt->execute([$user['id'], $search_param, $search_param, $search_param]);
    }
    $attendance = $stmt->fetchAll();

    if (count($employees) > 0 || count($announcements) > 0 || count($leaves) > 0 || count($attendance) > 0) {
        $results_found = true;
    }
}
?>

<div class="space-y-8 performance-page-container">

    <!-- ══ Header Section ══ -->
    <header class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-3xl font-bold">manage_search</span>
                </div>
                <h1 data-theme-text class="text-3xl font-bold leading-none">Hasil Pencarian</h1>
            </div>
            <p data-theme-muted class="text-[10px] font-bold ml-1 opacity-50">Menampilkan hasil untuk kata kunci: <strong class="text-primary font-bold">"<?= h($query) ?>"</strong></p>
        </div>
    </header>

    <?php if (empty($query)): ?>
        <div class="text-center py-20 bg-surface border border-border rounded-lg shadow-sm">
            <span class="material-symbols-outlined text-6xl opacity-10 mb-4">search</span>
            <h3 data-theme-text class="text-base font-bold">Ketik sesuatu untuk mulai mencari...</h3>
            <p data-theme-muted class="text-xs opacity-50 mt-1">Gunakan kolom pencarian di header bagian atas.</p>
        </div>
    <?php elseif (!$results_found): ?>
        <div class="text-center py-20 bg-surface border border-border rounded-lg shadow-sm">
            <span class="material-symbols-outlined text-6xl opacity-10 mb-4">database_off</span>
            <h3 data-theme-text class="text-base font-bold">Tidak ada hasil yang ditemukan</h3>
            <p data-theme-muted class="text-xs opacity-50 mt-1">Coba gunakan kata kunci lain seperti nama pegawai, departemen, tipe izin, atau lokasi.</p>
        </div>
    <?php else: ?>

        <!-- ══ 1. Pegawai / Team Members ══ -->
        <?php if (count($employees) > 0): ?>
            <div class="space-y-3">
                <h2 data-theme-text class="text-xs font-bold uppercase tracking-wider text-primary ml-2">Pegawai (<?= count($employees) ?>)</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <?php foreach ($employees as $p): ?>
                        <div data-theme-card class="bg-surface p-4 rounded-lg border border-border shadow-sm flex items-center gap-3">
                            <div class="w-10 h-10 rounded-lg overflow-hidden shrink-0 border border-border">
                                <?php if (!empty($p['photo_profile'])): ?>
                                    <img src="<?= h($p['photo_profile']) ?>" class="w-full h-full object-cover">
                                <?php else: ?>
                                    <div class="w-full h-full bg-surface2 flex items-center justify-center text-primary font-bold text-sm">
                                        <?= avatar_initials($p['name']) ?>
                                    </div>
                                <?php endif; ?>
                            </div>
                            <div class="min-w-0 flex-1">
                                <a href="?page=attendance-history&user_id=<?= h($p['id']) ?>" class="text-xs font-bold leading-tight truncate hover:text-primary transition-colors block"><?= h($p['name']) ?></a>
                                <p data-theme-muted class="text-[9px] font-bold opacity-45 mt-0.5 truncate"><?= h($p['position']) ?> · <?= h($p['department']) ?></p>
                            </div>
                            <a href="?page=attendance-history&user_id=<?= h($p['id']) ?>" class="w-8 h-8 rounded-lg bg-surface2 flex items-center justify-center border border-border text-on-surface-variant hover:text-primary hover:bg-primary/10 transition-all shrink-0" title="Liwayat Absensi">
                                <span class="material-symbols-outlined text-base">calendar_month</span>
                            </a>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- ══ 2. Pengumuman ══ -->
        <?php if (count($announcements) > 0): ?>
            <div class="space-y-3 pt-4">
                <h2 data-theme-text class="text-xs font-bold uppercase tracking-wider text-indigo-500 ml-2">Pengumuman (<?= count($announcements) ?>)</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <?php foreach ($announcements as $ann): ?>
                        <div data-theme-card class="bg-surface p-5 rounded-lg border border-border shadow-sm relative overflow-hidden flex flex-col justify-between">
                            <div>
                                <div class="flex justify-between items-start gap-4 mb-2">
                                    <h3 data-theme-text class="text-xs font-bold leading-snug"><?= h($ann['title']) ?></h3>
                                    <?php 
                                    $prio = $ann['priority'];
                                    $prio_clr = $prio === 'important' ? 'badge-red' : ($prio === 'high' ? 'badge-orange' : 'badge-green');
                                    ?>
                                    <span class="badge <?= $prio_clr ?>"><?= strtoupper($prio) ?></span>
                                </div>
                                <p data-theme-muted class="text-[10px] line-clamp-3 leading-relaxed opacity-60"><?= strip_tags($ann['content']) ?></p>
                            </div>
                            <div class="mt-4 pt-3 border-t border-border flex justify-between items-center text-[9px] text-[var(--text-muted)] font-bold">
                                <span><?= format_date($ann['created_at']) ?></span>
                                <a href="?page=announcements" class="text-primary hover:underline flex items-center gap-1">Selengkapnya <span class="material-symbols-outlined text-[10px]">arrow_forward</span></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <!-- ══ 3. Pengajuan Izin ══ -->
        <?php if (count($leaves) > 0): ?>
            <div class="space-y-3 pt-4">
                <h2 data-theme-text class="text-xs font-bold uppercase tracking-wider text-amber-500 ml-2">Pengajuan Izin (<?= count($leaves) ?>)</h2>
                <div data-theme-card class="bg-surface rounded-lg border border-border shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-surface2/50 border-b border-border text-[9px] font-bold uppercase text-[var(--text-muted)]">
                                    <th class="px-6 py-3">Karyawan</th>
                                    <th class="px-6 py-3">Tipe Izin</th>
                                    <th class="px-6 py-3">Periode</th>
                                    <th class="px-6 py-3">Alasan</th>
                                    <th class="px-6 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <?php foreach ($leaves as $lv): 
                                    $st = $lv['approval_status'];
                                    $st_clr = $st==='approved'?'badge-green':($st==='pending'?'badge-yellow':'badge-red');
                                ?>
                                    <tr class="hover:bg-surface2/25 transition-colors">
                                        <td class="px-6 py-3 font-bold"><?= h($lv['employee_name']) ?></td>
                                        <td class="px-6 py-3 uppercase font-semibold text-[10px]"><?= h($lv['leave_type']) ?></td>
                                        <td class="px-6 py-3 font-mono"><?= format_date($lv['leave_start']) ?> - <?= format_date($lv['leave_end']) ?></td>
                                        <td class="px-6 py-3 max-w-[200px] truncate" title="<?= h($lv['leave_reason']) ?>"><?= h($lv['leave_reason']) ?></td>
                                        <td class="px-6 py-3">
                                            <span class="badge <?= $st_clr ?>"><?= strtoupper($st) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ══ 4. Log Absensi ══ -->
        <?php if (count($attendance) > 0): ?>
            <div class="space-y-3 pt-4">
                <h2 data-theme-text class="text-xs font-bold uppercase tracking-wider text-emerald-500 ml-2">Log Absensi (<?= count($attendance) ?>)</h2>
                <div data-theme-card class="bg-surface rounded-lg border border-border shadow-sm overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-surface2/50 border-b border-border text-[9px] font-bold uppercase text-[var(--text-muted)]">
                                    <th class="px-6 py-3">Karyawan</th>
                                    <th class="px-6 py-3">Tanggal & Jam</th>
                                    <th class="px-6 py-3">Flow</th>
                                    <th class="px-6 py-3">Lokasi</th>
                                    <th class="px-6 py-3">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                <?php foreach ($attendance as $att): 
                                    $st = $att['approval_status'];
                                    $st_clr = $st==='approved'?'badge-green':($st==='pending'?'badge-yellow':'badge-red');
                                ?>
                                    <tr class="hover:bg-surface2/25 transition-colors">
                                        <td class="px-6 py-3 font-bold"><?= h($att['employee_name']) ?></td>
                                        <td class="px-6 py-3 font-mono"><?= format_date($att['attendance_date']) ?> · <?= h($att['attendance_time']) ?></td>
                                        <td class="px-6 py-3 uppercase font-semibold text-[10px]"><?= $att['attendance_flow'] === 'in' ? 'Check-In' : 'Check-Out' ?></td>
                                        <td class="px-6 py-3 max-w-[150px] truncate" title="<?= h($att['location']) ?>"><?= h($att['location']) ?></td>
                                        <td class="px-6 py-3">
                                            <span class="badge <?= $st_clr ?>"><?= strtoupper($st) ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>

</div>

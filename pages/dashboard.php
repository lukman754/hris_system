<?php
// pages/dashboard.php
$employees  = get_employees();
$attendance = get_attendance();
$leaves     = get_leaves();
$today      = date('Y-m-d');
$user_id    = $user['id'];

$user_att       = array_filter($attendance, fn($a) => $a['user_id'] === $user_id);
$my_today_att   = array_filter($user_att,   fn($a) => $a['attendance_date'] === $today);
$my_monthly_att = array_filter($user_att,   fn($a) => date('Y-m', strtotime($a['attendance_date'])) === date('Y-m'));

$clock_in      = !empty($my_today_att) ? end($my_today_att)['attendance_time'] : '--:--';
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
/* ── Desktop 2-column layout ─────────────────── */
.dash-layout {
    display: flex;
    flex-direction: column;
    gap: 14px;
}
@media (min-width: 900px) {
    .dash-layout {
        display: grid;
        grid-template-columns: 70% 30%;
        grid-template-rows: auto;
        align-items: start;
        gap: 20px;
    }
    .dash-left  { display: flex; flex-direction: column; gap: 14px; min-width: 0; overflow: hidden; }
    .dash-right { display: flex; flex-direction: column; gap: 14px; position: sticky; top: 80px; min-width: 0; }
}
/* mobile: stack left then right */
.dash-left  { display: flex; flex-direction: column; gap: 14px; }
.dash-right { display: flex; flex-direction: column; gap: 14px; }
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
</style>

<div class="dash-layout performance-page-container">

    <!-- ════════════ LEFT COLUMN ════════════ -->
    <div class="dash-left">

        <!-- ── Hero ── -->
        <section style="background:linear-gradient(135deg, #111111 0%, #1a1a1a 100%); border-radius:12px; color:#fff; padding:18px 20px; position:relative; overflow:hidden; box-shadow:0 20px 40px -10px rgba(0,0,0,0.5);">
            <div style="position:relative; z-index:1;">
                <p style="font-size:10px; font-weight:800; letter-spacing:.15em; text-transform:; color:var(--accent); margin:0 0 6px;">
                    <?= date('l, d F Y') ?>
                </p>
                <h1 style="font-size:22px; font-weight:900; margin:0 0 12px; line-height:1.2; color:#fff;">
                    <?php if (auth_is_hrd()): ?>
                        System Overview Dashboard
                    <?php else: ?>
                        Halo, <?= explode(' ', h($user['name']))[0] ?>! 👋
                    <?php endif; ?>
                </h1>
                
                <?php if (auth_is_hrd()): ?>
                    <div style="display:inline-flex; align-items:center; gap:8px; background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12); border-radius:999px; padding:5px 12px;">
                        <span style="width:7px;height:7px;border-radius:50%;background:#4ADE80;box-shadow:0 0 6px rgba(74,222,128,.7);"></span>
                        <span style="font-size:11px;font-weight:700;color:#fff;">Operational Status: Optimal</span>
                    </div>
                <?php else: ?>
                    <div style="display:inline-flex; align-items:center; gap:8px; background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12); border-radius:999px; padding:5px 12px;">
                        <span style="width:7px;height:7px;border-radius:50%;background:<?= $clocked_in?'#4ADE80':'#6B7280'?>;<?= $clocked_in?'box-shadow:0 0 6px rgba(74,222,128,.7);':'' ?>"></span>
                        <span style="font-size:11px;font-weight:700;color:#fff;"><?= $clocked_in ? "Clock-in: $clock_in" : 'Belum clock-in hari ini' ?></span>
                    </div>
                <?php endif; ?>
            </div>
            <div style="position:absolute;right:-30px;top:-30px;width:160px;height:160px;background:radial-gradient(circle,rgba(178,197,255,.2) 0%,transparent 70%);border-radius:50%;pointer-events:none;"></div>
        </section>

        <!-- ── Stats ── -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(130px, 1fr));gap:12px;">
            <?php if (auth_is_hrd()): ?>
                <div data-theme-card style="border-radius:12px;padding:14px;display:flex;align-items:center;gap:12px;">
                    <div class="ib ib-blue" style="width:40px;height:40px;">
                        <span class="material-symbols-outlined" style="font-size:20px;color:#3B82F6;">groups</span>
                    </div>
                    <div>
                        <p data-theme-muted style="font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:;color:#9CA3AF;margin:0 0 3px;">Total Pegawai</p>
                        <p data-theme-text style="font-size:15px;font-weight:900;color:#111;margin:0;"><?= count($employees) ?></p>
                    </div>
                </div>
                <div data-theme-card style="border-radius:12px;padding:14px;display:flex;align-items:center;gap:12px;">
                    <div class="ib ib-yellow" style="width:40px;height:40px;">
                        <span class="material-symbols-outlined" style="font-size:20px;color:#D97706;">event_busy</span>
                    </div>
                    <div>
                        <p data-theme-muted style="font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:;color:#9CA3AF;margin:0 0 3px;">Pending Approval</p>
                        <?php $pending_leaves = count(array_filter($leaves, fn($l) => $l['approval_status'] === 'pending')); ?>
                        <p data-theme-text style="font-size:15px;font-weight:900;color:#111;margin:0;"><?= $pending_leaves ?></p>
                    </div>
                </div>
                <div data-theme-card style="border-radius:12px;padding:14px;display:flex;align-items:center;gap:12px;">
                    <div class="ib ib-green" style="width:40px;height:40px;">
                        <span class="material-symbols-outlined" style="font-size:20px;color:#10B981;">done_all</span>
                    </div>
                    <div>
                        <p data-theme-muted style="font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:;color:#9CA3AF;margin:0 0 3px;">Hadir Hari Ini</p>
                        <?php $today_presence = count(array_unique(array_column(array_filter($attendance, fn($a) => $a['attendance_date'] === $today), 'user_id'))); ?>
                        <p data-theme-text style="font-size:15px;font-weight:900;color:#111;margin:0;"><?= $today_presence ?></p>
                    </div>
                </div>
            <?php else: ?>
                <div data-theme-card style="border-radius:12px;padding:14px;display:flex;align-items:center;gap:12px;">
                    <div class="ib ib-orange" style="width:40px;height:40px;">
                        <span class="material-symbols-outlined" style="font-size:20px;color:var(--primary);">calendar_month</span>
                    </div>
                    <div>
                        <p data-theme-muted style="font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:;color:#9CA3AF;margin:0 0 3px;">Presensi Bulan Ini</p>
                        <p data-theme-text style="font-size:15px;font-weight:900;color:#111;margin:0;"><?= $present_count ?><span style="font-size:10px;color:#ccc;font-weight:600;"> /<?= $days_in_month ?>hr</span></p>
                    </div>
                </div>
                <div data-theme-card style="border-radius:12px;padding:14px;display:flex;align-items:center;gap:12px;">
                    <div class="ib ib-green" style="width:40px;height:40px;">
                        <span class="material-symbols-outlined" style="font-size:20px;color:#10B981;">monitoring</span>
                    </div>
                    <div>
                        <p data-theme-muted style="font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:;color:#9CA3AF;margin:0 0 3px;">Tingkat Kehadiran</p>
                        <p data-theme-text style="font-size:15px;font-weight:900;color:#111;margin:0;"><?= $att_pct ?>%</p>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── Weekly Strip (Employee) or Insight (HRD) ── -->
        <div data-theme-card style="border-radius:12px;padding:14px 10px;">
            <?php if (auth_is_hrd()): ?>
                <div style="display:flex; justify-content:space-between; align-items:center; padding:0 8px;">
                    <div>
                        <p data-theme-muted style="font-size:9px; font-weight:800; text-transform:uppercase; letter-spacing:.1em; color:var(--accent);">Department Distribution</p>
                        <p data-theme-text style="font-size:12px; font-weight:800; margin-top:2px;">Workforce segmentation is active</p>
                    </div>
                    <div style="display:flex; gap:4px;">
                        <?php 
                        $colors = ['#3B82F6', '#10B981', '#F59E0B', '#EF4444', '#8B5CF6'];
                        $i = 0;
                        $depts = array_count_values(array_column($employees, 'department'));
                        arsort($depts);
                        foreach (array_slice($depts, 0, 5) as $name => $count): ?>
                            <div title="<?= h($name) ?>: <?= $count ?>" style="width:12px; height:24px; background:<?= $colors[$i++ % 5] ?>; border-radius:3px; opacity:<?= 0.3 + ($count / max($depts)) * 0.7 ?>;"></div>
                        <?php endforeach; ?>
                    </div>
                </div>
            <?php else: ?>
                <div style="display:flex;justify-content:space-between;align-items:center;">
                    <?php foreach ($week_days as $wd): ?>
                    <div style="display:flex;flex-direction:column;align-items:center;gap:5px;flex:1;">
                        <span style="font-size:9px;font-weight:800;text-transform:;letter-spacing:.05em;color:<?= $wd['is_today']?'var(--primary)':'#CBD5E1' ?>;">
                            <?= substr($wd['day'], 0, 1) ?>
                        </span>
                        <span style="width:30px;height:30px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;
                            <?= $wd['is_today']
                                ? 'background:var(--primary);color:#fff;box-shadow:0 3px 10px rgba(0,61,155,.4);'
                                : ($wd['status']==='present' ? 'background:#ECFDF5;color:#10B981;' : 'color:#CBD5E1;') ?>">
                            <?= $wd['num'] ?>
                        </span>
                        <span style="width:5px;height:5px;border-radius:50%;background:<?= $wd['status']==='present'?'#10B981':($wd['status']==='absent'?'#FCA5A5':'#E5E7EB') ?>;"></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- ── People Row ── -->
        <div style="min-width:0;overflow:hidden;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <p data-theme-muted style="font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:;color:#9CA3AF;margin:0;">Tim Hari Ini</p>
                <?php $total_hadir = count(array_filter($employees, fn($e) => !empty(array_filter($attendance, fn($a) => $a['user_id'] === $e['id'] && $a['attendance_date'] === $today)))); ?>
                <span class="badge badge-green"><?= $total_hadir ?> / <?= count($employees) ?> hadir</span>
            </div>
            <div style="overflow-x:auto; -webkit-overflow-scrolling:touch; margin:0 -4px; padding:0 4px;">
                <div style="display:flex; gap:14px; width:max-content; padding:4px 2px;">
                <?php foreach ($employees as $emp):
                    $first_name = explode(' ', $emp['name'])[0];
                    $has_today  = !empty(array_filter($attendance, fn($a) => $a['user_id'] === $emp['id'] && $a['attendance_date'] === $today));
                ?>
                <a href="?page=people&id=<?= $emp['id'] ?>" style="display:flex;flex-direction:column;align-items:center;gap:5px;text-decoration:none;">
                     <div style="position:relative;width:44px;height:44px;flex-shrink:0;">
                        <?php if (!empty($emp['photo_profile'])): ?>
                            <img src="<?= h($emp['photo_profile']) ?>" alt="<?= h($first_name) ?>"
                                 style="width:44px;height:44px;border-radius:50%;object-fit:cover;border:2px solid <?= $has_today ? '#10B981' : 'var(--border)' ?>;">
                        <?php else: ?>
                            <div data-theme-surface2 style="width:44px;height:44px;border-radius:50%;background:<?= $has_today ? '#ECFDF5' : 'var(--surface2)' ?>;border:1px solid var(--border);display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:<?= $has_today ? '#10B981' : 'var(--text-muted)' ?>;">
                                <?= strtoupper(substr($first_name, 0, 2)) ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($has_today): ?>
                        <span style="position:absolute;bottom:1px;right:1px;width:10px;height:10px;border-radius:50%;background:#10B981;border:2px solid var(--surface);"></span>
                        <?php endif; ?>
                    </div>
                    <span data-theme-muted style="font-size:9px;font-weight:700;color:var(--text-muted);text-align:center;max-width:48px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"><?= h($first_name) ?></span>
                </a>
                <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- ── Menu Utama ── -->
        <section>
            <p data-theme-muted style="font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:;color:var(--text-muted);margin:0 0 10px 2px;">Menu Utama</p>
            <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:8px;">
                <?php foreach ($all_menu as $item): ?>
                <a href="?page=<?= $item['page'] ?>" class="menu-card" data-menu-card data-ic-bg="<?= $item['ib'] ?>"
                   style="border-radius:10px;padding:10px 5px;display:flex;flex-direction:column;gap:6px;box-shadow:var(--shadow);text-decoration:none;align-items:center;text-align:center;">
                    <span class="mc-icon ib <?= $item['ib'] ?>" style="width:32px;height:32px;border-radius:8px;color:<?= $item['ic'] ?>;transition:all .2s ease;">
                        <span class="material-symbols-outlined" style="font-size:18px;"><?= $item['icon'] ?></span>
                    </span>
                    <span data-theme-text class="mc-label" style="font-size:10px;font-weight:700;line-height:1.2;transition:color .2s ease;"><?= h($item['label']) ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </section>

    </div><!-- /dash-left -->

    <!-- ════════════ RIGHT COLUMN ════════════ -->
    <div class="dash-right">

        <!-- ── Activity Log (Employee) or Request Log (HRD) ── -->
        <section>
            <?php if (auth_is_hrd()): ?>
                <div style="display:flex;justify-content:space-between;align-items:center;margin:0 2px 10px;">
                    <p data-theme-muted style="font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:;color:var(--text-muted);margin:0;">Pending Approvals</p>
                </div>
                <?php 
                $pending_list = array_slice(array_filter($leaves, fn($l) => $l['approval_status'] === 'pending'), 0, 5);
                if (empty($pending_list)): ?>
                    <div data-theme-card style="background:var(--surface);border-radius:12px;padding:36px 20px;text-align:center;border:1px solid var(--border);">
                        <span class="material-symbols-outlined" style="font-size:36px;color:#10B981;display:block;margin-bottom:8px;">task_alt</span>
                        <p data-theme-muted style="font-size:12px;color:var(--text-muted);font-weight:600;margin:0;">Semua approval selesai</p>
                    </div>
                <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                        <?php foreach ($pending_list as $l): ?>
                        <div data-theme-card style="border-radius:12px;padding:11px 13px;display:flex;align-items:center;gap:11px;">
                            <div class="ib ib-yellow" style="width:36px;height:36px;border-radius:9px;">
                                <span class="material-symbols-outlined" style="font-size:17px;color:#D97706;">event_busy</span>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <p data-theme-text style="font-size:11px;font-weight:700;color:var(--text-primary);margin:0;"><?= h($l['employee_name']) ?></p>
                                <p data-theme-muted style="font-size:9px;font-weight:600;color:var(--text-muted);margin:2px 0 0;"><?= h($l['leave_type']) ?> · <?= format_date($l['leave_start']) ?></p>
                            </div>
                            <a href="?page=leaves" class="badge badge-orange">Review</a>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php else: ?>
                <div style="display:flex;justify-content:space-between;align-items:center;margin:0 2px 10px;">
                    <p data-theme-muted style="font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:;color:var(--text-muted);margin:0;">Riwayat Absensi</p>
                </div>
                <?php if (empty($latest_history)): ?>
                    <div data-theme-card style="background:var(--surface);border-radius:12px;padding:36px 20px;text-align:center;border:1px solid var(--border);">
                        <span class="material-symbols-outlined" style="font-size:36px;color:var(--text-muted);display:block;margin-bottom:8px;">event_busy</span>
                        <p data-theme-muted style="font-size:12px;color:var(--text-muted);font-weight:600;margin:0;">Belum ada catatan absensi</p>
                    </div>
                <?php else: ?>
                    <div style="display:flex;flex-direction:column;gap:8px;">
                    <?php foreach ($latest_history as $h):
                        $is_qr  = $h['attendance_type'] === 'qr';
                        $ic_ib  = $is_qr ? 'ib-orange' : 'ib-blue';
                        $ic_col = $is_qr ? '#FF7D00' : '#3B82F6';
                        $ic     = $is_qr ? 'qr_code_scanner' : 'photo_camera';
                        $label  = $is_qr ? 'QR Scan' : 'Face Selfie';
                        $st     = h($h['approval_status']);
                        $st_cls = $st==='approved' ? 'badge-green' : ($st==='pending' ? 'badge-orange' : 'badge-red');
                    ?>
                    <div data-theme-card style="border-radius:12px;padding:11px 13px;display:flex;align-items:center;gap:11px;">
                        <div class="ib <?= $ic_ib ?>" style="width:36px;height:36px;border-radius:9px;">
                            <span class="material-symbols-outlined" style="font-size:17px;color:<?= $ic_col ?>;"><?= $ic ?></span>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <p data-theme-text style="font-size:11px;font-weight:700;color:var(--text-primary);margin:0;"><?= $label ?></p>
                            <p data-theme-muted style="font-size:9px;font-weight:600;color:var(--text-muted);text-transform:;letter-spacing:.04em;margin:2px 0 0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                <?= format_date($h['attendance_date']) ?> · <?= h($h['location']) ?>
                            </p>
                        </div>
                        <div style="text-align:right;flex-shrink:0;">
                            <p data-theme-text style="font-size:12px;font-weight:800;color:var(--text-primary);margin:0;"><?= $h['attendance_time'] ?></p>
                            <span class="badge <?= $st_cls ?>"><?= $st ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </section>

    </div><!-- /dash-right -->


</div><!-- /dash-layout -->

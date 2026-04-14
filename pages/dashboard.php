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
$all_menu = auth_is_hrd() ? [
    ['page' => 'employees',          'icon' => 'group',           'label' => 'Staff',     'ib' => 'ib-blue',   'ic' => '#3B82F6'],
    ['page' => 'attendance-reports', 'icon' => 'insights',        'label' => 'Laporan',   'ib' => 'ib-green',  'ic' => '#10B981'],
    ['page' => 'leaves',             'icon' => 'calendar_add_on', 'label' => 'Approvals', 'ib' => 'ib-yellow', 'ic' => '#D97706'],
    ['page' => 'photo-approvals',    'icon' => 'photo_camera',    'label' => 'Foto Rec',  'ib' => 'ib-red',    'ic' => '#EF4444'],
    ['page' => 'payroll',            'icon' => 'payments',        'label' => 'Keuangan',  'ib' => 'ib-purple', 'ic' => '#7C3AED'],
    ['page' => 'performance',        'icon' => 'monitoring',      'label' => 'Kinerja',   'ib' => 'ib-indigo', 'ic' => '#4F46E5'],
    ['page' => 'announcements',      'icon' => 'campaign',        'label' => 'Info',      'ib' => 'ib-teal',   'ic' => '#0D9488'],
    ['page' => 'calendar',           'icon' => 'calendar_today',  'label' => 'Jadwal',    'ib' => 'ib-slate',  'ic' => '#64748B'],
] : [
    ['page' => 'attendance',    'icon' => 'fingerprint',    'label' => 'Absensi',   'ib' => 'ib-orange', 'ic' => '#003d9b'],
    ['page' => 'leaves',        'icon' => 'calendar_add_on','label' => 'Izin',      'ib' => 'ib-yellow', 'ic' => '#D97706'],
    ['page' => 'performance',   'icon' => 'monitoring',     'label' => 'Kinerja',   'ib' => 'ib-purple', 'ic' => '#7C3AED'],
    ['page' => 'announcements', 'icon' => 'campaign',       'label' => 'Info',      'ib' => 'ib-teal',   'ic' => '#0D9488'],
    ['page' => 'calendar',      'icon' => 'calendar_today', 'label' => 'Jadwal',    'ib' => 'ib-slate',  'ic' => '#64748B'],
    ['page' => 'people',        'icon' => 'diversity_3',    'label' => 'Rekan',     'ib' => 'ib-blue',   'ic' => '#3B82F6'],
];

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
</style>

<div class="dash-layout">

    <!-- ════════════ LEFT COLUMN ════════════ -->
    <div class="dash-left">

        <!-- ── Hero ── -->
        <section style="background:linear-gradient(145deg,#111 55%,#1c1c1c); border-radius:12px; color:#fff; padding:18px 20px; position:relative; overflow:hidden;">
            <div style="position:relative; z-index:1;">
                <p style="font-size:10px; font-weight:800; letter-spacing:.15em; text-transform:uppercase; color:var(--accent); margin:0 0 6px;">
                    <?= date('l, d F Y') ?>
                </p>
                <h1 style="font-size:22px; font-weight:900; margin:0 0 12px; line-height:1.2; color:#fff;">
                    Halo, <?= explode(' ', h($user['name']))[0] ?>! 👋
                </h1>
                <div style="display:inline-flex; align-items:center; gap:8px; background:rgba(255,255,255,.08); border:1px solid rgba(255,255,255,.12); border-radius:999px; padding:5px 12px;">
                    <span style="width:7px;height:7px;border-radius:50%;background:<?= $clocked_in?'#4ADE80':'#6B7280'?>;<?= $clocked_in?'box-shadow:0 0 6px rgba(74,222,128,.7);':'' ?>"></span>
                    <span style="font-size:11px;font-weight:700;color:#fff;"><?= $clocked_in ? "Clock-in: $clock_in" : 'Belum clock-in hari ini' ?></span>
                </div>
            </div>
            <div style="position:absolute;right:-30px;top:-30px;width:160px;height:160px;background:radial-gradient(circle,rgba(178,197,255,.2) 0%,transparent 70%);border-radius:50%;pointer-events:none;"></div>
        </section>

        <!-- ── Stats ── -->
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(130px, 1fr));gap:12px;">
            <div data-theme-card style="background:#fff;border-radius:12px;padding:14px;display:flex;align-items:center;gap:12px;border:1px solid rgba(0,0,0,.06);box-shadow:0 1px 4px rgba(0,0,0,.05);">
                <div class="ib ib-orange" style="width:40px;height:40px;">
                    <span class="material-symbols-outlined" style="font-size:20px;color:var(--primary);">calendar_month</span>
                </div>
                <div>
                    <p data-theme-muted style="font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#9CA3AF;margin:0 0 3px;">Presensi Bulan Ini</p>
                    <p data-theme-text style="font-size:15px;font-weight:900;color:#111;margin:0;"><?= $present_count ?><span style="font-size:10px;color:#ccc;font-weight:600;"> /<?= $days_in_month ?>hr</span></p>
                </div>
            </div>
            <div data-theme-card style="background:#fff;border-radius:12px;padding:14px;display:flex;align-items:center;gap:12px;border:1px solid rgba(0,0,0,.06);box-shadow:0 1px 4px rgba(0,0,0,.05);">
                <div class="ib ib-green" style="width:40px;height:40px;">
                    <span class="material-symbols-outlined" style="font-size:20px;color:#10B981;">monitoring</span>
                </div>
                <div>
                    <p data-theme-muted style="font-size:9px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;color:#9CA3AF;margin:0 0 3px;">Tingkat Kehadiran</p>
                    <p data-theme-text style="font-size:15px;font-weight:900;color:#111;margin:0;"><?= $att_pct ?>%</p>
                </div>
            </div>
        </div>

        <!-- ── Weekly Strip ── -->
        <div data-theme-card style="background:#fff;border-radius:12px;padding:14px 10px;border:1px solid rgba(0,0,0,.06);box-shadow:0 1px 4px rgba(0,0,0,.05);">
            <div style="display:flex;justify-content:space-between;align-items:center;">
                <?php foreach ($week_days as $wd): ?>
                <div style="display:flex;flex-direction:column;align-items:center;gap:5px;flex:1;">
                    <span style="font-size:9px;font-weight:800;text-transform:uppercase;letter-spacing:.05em;color:<?= $wd['is_today']?'var(--primary)':'#CBD5E1' ?>;">
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
        </div>

        <!-- ── People Row ── -->
        <div style="min-width:0;overflow:hidden;">
            <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:10px;">
                <p data-theme-muted style="font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:#9CA3AF;margin:0;">Tim Hari Ini</p>
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
                            <div data-theme-surface2 style="width:44px;height:44px;border-radius:50%;background:<?= $has_today ? '#ECFDF5' : 'var(--surface2)' ?>;border:2px solid <?= $has_today ? '#10B981' : 'var(--border)' ?>;display:flex;align-items:center;justify-content:center;font-size:13px;font-weight:800;color:<?= $has_today ? '#10B981' : 'var(--text-muted)' ?>;">
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
            <p data-theme-muted style="font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:var(--text-muted);margin:0 0 10px 2px;">Menu Utama</p>
            <div style="display:grid;grid-template-columns:repeat(6,1fr);gap:8px;">
                <?php foreach ($all_menu as $item): ?>
                <a href="?page=<?= $item['page'] ?>" data-menu-card data-ic-bg="<?= $item['ib'] ?>"
                   style="background:var(--surface);border-radius:10px;padding:10px 5px;display:flex;flex-direction:column;gap:6px;border:1px solid var(--border);box-shadow:var(--shadow);text-decoration:none;transition:all .2s ease;align-items:center;text-align:center;"
                   onmouseover="this.style.background='var(--primary)';this.querySelector('.mc-icon').style.background='rgba(255,255,255,.2)';this.querySelector('.mc-icon').style.color='#fff';this.querySelector('.mc-label').style.color='#fff';"
                   onmouseout="this.style.background=document.documentElement.classList.contains('dark')?'#1A1A1A':'#fff';this.querySelector('.mc-icon').style.background='';this.querySelector('.mc-label').style.color=document.documentElement.classList.contains('dark')?'#E5E7EB':'#374151';">
                    <span class="mc-icon ib <?= $item['ib'] ?>" style="width:32px;height:32px;border-radius:8px;color:<?= $item['ic'] ?>;transition:all .2s ease;">
                        <span class="material-symbols-outlined" style="font-size:18px;"><?= $item['icon'] ?></span>
                    </span>
                    <span data-theme-text class="mc-label" style="font-size:10px;font-weight:700;color:var(--text-primary);line-height:1.2;transition:color .2s ease;"><?= h($item['label']) ?></span>
                </a>
                <?php endforeach; ?>
            </div>
        </section>

    </div><!-- /dash-left -->

    <!-- ════════════ RIGHT COLUMN ════════════ -->
    <div class="dash-right" style="padding-bottom:48px;">

        <!-- ── Activity Log ── -->
        <section>
            <div style="display:flex;justify-content:space-between;align-items:center;margin:0 2px 10px;">
                <p data-theme-muted style="font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:var(--text-muted);margin:0;">Riwayat Absensi</p>
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
                <div data-theme-card style="background:var(--surface);border-radius:12px;padding:11px 13px;display:flex;align-items:center;gap:11px;border:1px solid var(--border);box-shadow:var(--shadow);">
                    <div class="ib <?= $ic_ib ?>" style="width:36px;height:36px;border-radius:9px;">
                        <span class="material-symbols-outlined" style="font-size:17px;color:<?= $ic_col ?>;"><?= $ic ?></span>
                    </div>
                    <div style="flex:1;min-width:0;">
                        <p data-theme-text style="font-size:11px;font-weight:700;color:var(--text-primary);margin:0;"><?= $label ?></p>
                        <p data-theme-muted style="font-size:9px;font-weight:600;color:var(--text-muted);text-transform:uppercase;letter-spacing:.04em;margin:2px 0 0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
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
        </section>

        <!-- ── Announcements ── -->
        <section>
            <div style="display:flex;justify-content:space-between;align-items:center;margin:0 2px 10px;">
                <p data-theme-muted style="font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:var(--text-muted);margin:0;">Pengumuman</p>
                <?php if (!empty($anns)): ?>
                    <a href="?page=announcements" style="font-size:10px;font-weight:700;color:var(--primary);text-decoration:none;">Lihat Semua →</a>
                <?php endif; ?>
            </div>
            
            <?php if (empty($anns)): ?>
                <div data-theme-card style="background:var(--surface);border-radius:12px;padding:24px 20px;text-align:center;border:1px solid var(--border);opacity:0.6;">
                    <p data-theme-muted style="font-size:9px;font-weight:800;color:var(--text-muted);margin:0;text-transform:uppercase;letter-spacing:.1em;">Tidak ada pengumuman aktif</p>
                </div>
            <?php else: ?>
                <div style="display:flex;flex-direction:column;gap:8px;">
                    <?php foreach ($anns as $ann): ?>
                    <a href="?page=announcements" data-theme-card style="background:var(--surface);border-radius:12px;padding:12px 14px;display:flex;align-items:center;gap:12px;border:1px solid var(--border);text-decoration:none;">
                        <div class="ib ib-orange" style="width:36px;height:36px;border-radius:8px;">
                            <span class="material-symbols-outlined" style="font-size:18px;color:var(--primary);">campaign</span>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <p data-theme-text style="font-size:12px;font-weight:700;color:var(--text-primary);margin:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= h($ann['title']) ?></p>
                            <p data-theme-muted style="font-size:9px;font-weight:600;color:var(--text-muted);margin:3px 0 0;text-transform:uppercase;letter-spacing:.04em;">
                                <?= date('d M Y', strtotime($ann['created_at'])) ?><?php if($ann['priority']==='important'): ?> · <span style="color:#EF4444;">Penting</span><?php endif; ?>
                            </p>
                        </div>
                        <span class="material-symbols-outlined" style="font-size:16px;color:var(--text-muted);flex-shrink:0;">chevron_right</span>
                    </a>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>

    </div><!-- /dash-right -->

</div><!-- /dash-layout -->

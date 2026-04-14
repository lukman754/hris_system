<?php
// pages/leaves.php – Pengajuan & Approval Izin
$leaves  = get_leaves();
$is_hrd  = auth_is_hrd();
$my_uid  = $user['id'];

if ($is_hrd):
    $pending  = array_filter($leaves, fn($l) => $l['approval_status'] === 'pending');
    $approved = array_filter($leaves, fn($l) => $l['approval_status'] === 'approved');
    $rejected = array_filter($leaves, fn($l) => $l['approval_status'] === 'rejected');
?>

<!-- ─── HRD: Approval Izin ─────────────────────── -->
<div class="space-y-6">

    <!-- Stats -->
    <div class="stat-grid">
        <div data-theme-card class="stat-card">
            <div class="ib ib-orange st-icon">
                <span class="material-symbols-outlined icon-size">pending_actions</span>
            </div>
            <div>
                <p data-theme-muted class="st-label">Pending</p>
                <p data-theme-text class="st-val"><?= count($pending) ?></p>
            </div>
        </div>
        <div data-theme-card class="stat-card">
            <div class="ib ib-green st-icon">
                <span class="material-symbols-outlined icon-size">check_circle</span>
            </div>
            <div>
                <p data-theme-muted class="st-label">Approved</p>
                <p data-theme-text class="st-val"><?= count($approved) ?></p>
            </div>
        </div>
        <div data-theme-card class="stat-card">
            <div class="ib ib-red st-icon">
                <span class="material-symbols-outlined icon-size">cancel</span>
            </div>
            <div>
                <p data-theme-muted class="st-label">Rejected</p>
                <p data-theme-text class="st-val"><?= count($rejected) ?></p>
            </div>
        </div>
    </div>

    <div data-theme-card style="background:var(--surface);border-radius:12px;border:1px solid var(--border);box-shadow:var(--shadow);overflow:hidden;">
        <div style="padding:16px 20px;border-bottom:1px solid var(--border);display:flex;justify-content:space-between;align-items:center;background:linear-gradient(to right, rgba(var(--primary-rgb),.02), transparent);">
            <div style="display:flex;align-items:center;gap:10px;">
                <div class="ib ib-blue" style="width:32px;height:32px;border-radius:8px;">
                    <span class="material-symbols-outlined" style="font-size:18px;">list_alt</span>
                </div>
                <h3 data-theme-text style="font-size:14px;font-weight:900;color:var(--text-primary);margin:0;">Antrean Persetujuan</h3>
            </div>
            <span class="badge badge-blue"><?= count($leaves) ?> Total</span>
        </div>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;min-width:600px;">
                <thead>
                    <tr style="background:var(--surface2);">
                        <th style="padding:12px 20px;text-align:left;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);">Karyawan</th>
                        <th style="padding:12px 20px;text-align:left;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);">Tipe</th>
                        <th style="padding:12px 20px;text-align:left;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);">Tanggal</th>
                        <th style="padding:12px 20px;text-align:left;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);">Keterangan</th>
                        <th style="padding:12px 20px;text-align:left;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);">Status</th>
                        <th style="padding:12px 20px;text-align:center;font-size:10px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;color:var(--text-muted);">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(empty($leaves)): ?>
                        <tr><td colspan="6" style="padding:40px;text-align:center;color:var(--text-muted);font-size:13px;font-weight:600;">Belum ada pengajuan izin antar karyawan.</td></tr>
                    <?php endif; ?>
                    <?php foreach ($leaves as $leave): 
                        $st = $leave['approval_status'];
                        $st_cls = $st==='approved'?'badge-green':($st==='pending'?'badge-orange':'badge-red');
                    ?>
                    <tr style="border-bottom:1px solid var(--border);transition:all .2s;" onmouseover="this.style.background='var(--surface2)'" onmouseout="this.style.background='transparent'">
                        <td style="padding:14px 20px;">
                            <div style="font-size:13px;font-weight:800;color:var(--text-primary);"><?= h($leave['employee_name']) ?></div>
                            <div style="font-size:10px;font-weight:600;color:var(--text-muted);"><?= h($leave['department']) ?></div>
                        </td>
                        <td style="padding:14px 20px;">
                            <span class="badge badge-blue" style="font-size:9px;"><?= leave_type_label($leave['leave_type']) ?></span>
                        </td>
                        <td style="padding:14px 20px;font-size:12px;font-weight:700;color:var(--text-primary);">
                            <?= date('d M', strtotime($leave['leave_start'])) ?>
                            <?php if ($leave['leave_start'] !== $leave['leave_end']): ?>
                                – <?= date('d M', strtotime($leave['leave_end'])) ?>
                            <?php endif; ?>
                            <div style="font-size:9px;color:var(--text-muted);font-weight:600;"><?= date('Y', strtotime($leave['leave_start'])) ?></div>
                        </td>
                        <td style="padding:14px 20px;">
                            <p style="font-size:12px;color:var(--text-muted);margin:0;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;" title="<?= h($leave['leave_reason']) ?>">
                                <?= h($leave['leave_reason']) ?>
                            </p>
                        </td>
                        <td style="padding:14px 20px;">
                            <span class="badge <?= $st_cls ?>"><?= $st ?></span>
                        </td>
                        <td style="padding:14px 20px;">
                            <?php if ($leave['approval_status'] === 'pending'): ?>
                            <div style="display:flex;gap:6px;justify-content:center;">
                                <a href="?page=leaves&action=approve&id=<?= $leave['id'] ?>" class="ib ib-green" style="width:32px;height:32px;border-radius:8px;text-decoration:none;" title="Setujui">
                                    <span class="material-symbols-outlined" style="font-size:18px;color:#10B981;">check</span>
                                </a>
                                <a href="?page=leaves&action=reject&id=<?= $leave['id'] ?>" class="ib ib-red" style="width:32px;height:32px;border-radius:8px;text-decoration:none;" title="Tolak">
                                    <span class="material-symbols-outlined" style="font-size:18px;color:#EF4444;">close</span>
                                </a>
                            </div>
                            <?php else: ?>
                                <div style="display:flex;justify-content:center;">
                                    <span class="material-symbols-outlined" style="font-size:20px;color:var(--text-muted);opacity:.5;">task_alt</span>
                                </div>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php else:
    // Employee: hanya tampilkan milik sendiri
    $my_leaves = array_filter($leaves, fn($l) => $l['user_id'] === $my_uid);
?>

<!-- ─── Employee: Pengajuan Izin ───────────────── -->
<div class="space-y-6">

    <!-- Hero Section -->
    <section style="background:linear-gradient(145deg,#111 55%,#1c1c1c); border-radius:14px; color:#fff; padding:24px; position:relative; overflow:hidden;">
        <div style="position:relative; z-index:1; display:flex; justify-content:space-between; align-items:center; gap:20px;">
            <div>
                <p style="font-size:10px; font-weight:800; letter-spacing:.15em; text-transform:uppercase; color:var(--accent); margin:0 0 6px;">Manajemen Absensi</p>
                <h1 style="font-size:22px; font-weight:900; margin:0 0 8px; line-height:1.2; color:#fff;">Pengajuan Izin</h1>
                <p style="font-size:12px; color:rgba(255,255,255,.6); margin:0; max-width:400px; line-height:1.5;">Ajukan izin atau cuti dengan mudah. Pantau status persetujuan Anda secara real-time di sini.</p>
            </div>
            <button onclick="openModal('leaveModal')" style="background:var(--primary); color:#fff; border:none; border-radius:12px; padding:12px 20px; font-size:13px; font-weight:800; cursor:pointer; box-shadow:0 10px 20px rgba(0,61,155,.3); display:flex; align-items:center; gap:8px;">
                <span class="material-symbols-outlined" style="font-size:20px;">add_circle</span>
                Ajukan Izin
            </button>
        </div>
        <div style="position:absolute;right:-30px;top:-30px;width:160px;height:160px;background:radial-gradient(circle,rgba(178,197,255,.15) 0%,transparent 70%);border-radius:50%;pointer-events:none;"></div>
    </section>

    <!-- Stats -->
    <div class="stat-grid">
        <?php
        $stats = [
            ['label' => 'Pending', 'count' => count(array_filter($my_leaves, fn($l) => $l['approval_status'] === 'pending')), 'ib' => 'ib-orange', 'ic' => 'var(--primary)', 'sym' => 'pending_actions'],
            ['label' => 'Approved', 'count' => count(array_filter($my_leaves, fn($l) => $l['approval_status'] === 'approved')), 'ib' => 'ib-green', 'ic' => '#10B981', 'sym' => 'check_circle'],
            ['label' => 'Rejected', 'count' => count(array_filter($my_leaves, fn($l) => $l['approval_status'] === 'rejected')), 'ib' => 'ib-red', 'ic' => '#EF4444', 'sym' => 'cancel'],
        ];
        foreach ($stats as $s):
        ?>
        <div data-theme-card class="stat-card">
            <div class="ib <?= $s['ib'] ?> st-icon">
                <span class="material-symbols-outlined icon-size" style="color:<?= $s['ic'] ?>;"><?= $s['sym'] ?></span>
            </div>
            <div>
                <p data-theme-muted class="st-label"><?= $s['label'] ?></p>
                <p data-theme-text class="st-val"><?= $s['count'] ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- My Leaves List -->
    <div>
        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:12px;padding:0 2px;">
            <p data-theme-muted style="font-size:10px;font-weight:800;letter-spacing:.12em;text-transform:uppercase;color:var(--text-muted);margin:0;">Riwayat Pengajuan</p>
            <span class="badge badge-blue"><?= count($my_leaves) ?> Records</span>
        </div>

        <div style="display:flex;flex-direction:column;gap:10px;">
            <?php if (!empty($my_leaves)): ?>
                <?php foreach ($my_leaves as $i => $leave): 
                    $st = $leave['approval_status'];
                    $st_cls = $st==='approved'?'badge-green':($st==='pending'?'badge-orange':'badge-red');
                ?>
                <div data-theme-card style="background:var(--surface);border-radius:12px;padding:12px;display:flex;align-items:center;gap:12px;border:1px solid var(--border);box-shadow:var(--shadow);position:relative;overflow:hidden;">
                    <!-- Date Badge -->
                    <div style="background:var(--surface2);border-radius:10px;padding:6px;min-width:55px;text-align:center;border:1px solid var(--border);">
                        <div style="font-size:8px;font-weight:800;text-transform:uppercase;color:var(--text-muted);"><?= date('M', strtotime($leave['leave_start'])) ?></div>
                        <div style="font-size:18px;font-weight:950;color:var(--text-primary);line-height:1;margin:2px 0;"><?= date('d', strtotime($leave['leave_start'])) ?></div>
                        <div style="font-size:8px;font-weight:700;color:var(--text-muted);"><?= date('Y', strtotime($leave['leave_start'])) ?></div>
                    </div>

                    <div style="flex:1;min-width:0;">
                        <div style="display:flex;align-items:center;gap:8px;margin-bottom:4px;">
                            <h4 data-theme-text style="font-size:12px;font-weight:900;color:var(--text-primary);margin:0;"><?= leave_type_label($leave['leave_type']) ?></h4>
                            <span class="badge <?= $st_cls ?>" style="font-size:8px;padding:1px 6px;"><?= $st ?></span>
                        </div>
                        <div style="display:flex;flex-wrap:wrap;gap:12px;align-items:center;">
                            <div style="display:flex;align-items:center;gap:4px;font-size:10px;color:var(--text-muted);font-weight:600;">
                                <span class="material-symbols-outlined" style="font-size:14px;color:var(--primary);">calendar_today</span>
                                <?= format_date($leave['leave_start']) ?> – <?= format_date($leave['leave_end']) ?>
                            </div>
                            <div style="display:flex;align-items:center;gap:4px;font-size:10px;color:var(--text-muted);font-weight:600;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;">
                                <span class="material-symbols-outlined" style="font-size:14px;color:var(--primary);">chat_bubble_outline</span>
                                <?= h($leave['leave_reason']) ?>
                            </div>
                        </div>
                    </div>

                    <div style="opacity:.3;">
                        <span class="material-symbols-outlined" style="font-size:20px;">chevron_right</span>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div data-theme-card style="background:var(--surface);border-radius:14px;padding:40px 20px;text-align:center;border:1px solid var(--border);border-style:dashed;">
                    <div class="ib ib-orange" style="width:48px;height:48px;border-radius:12px;margin:0 auto 12px;">
                        <span class="material-symbols-outlined" style="font-size:24px;color:var(--primary);">event_note</span>
                    </div>
                    <p data-theme-text style="font-size:14px;font-weight:900;color:var(--text-primary);margin:0;">Belum ada pengajuan</p>
                    <p data-theme-muted style="font-size:11px;color:var(--text-muted);margin:4px 0 0;">Anda dapat mulai mengajukan izin dengan menekan tombol diatas.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Modal Pengajuan Izin -->
<!-- Modal Pengajuan Izin -->
<div id="leaveModal" style="display:none;" class="modal-backdrop" onclick="if(event.target===this)closeModal('leaveModal')">
    <div data-theme-card class="w-full max-w-lg bg-surface rounded-2xl border border-border shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
        
        <!-- Modal Header -->
        <div class="p-5 pb-1 flex justify-between items-start">
            <div>
                <h3 data-theme-text class="text-xl font-black tracking-tight leading-none mb-2">Formulir Izin</h3>
                <div class="flex items-center gap-2">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                    <p data-theme-muted class="text-[9px] font-black uppercase tracking-[0.2em] opacity-60">Lengkapi detail pengajuan Anda</p>
                </div>
            </div>
            <button onclick="closeModal('leaveModal')" data-theme-surface2 class="w-10 h-10 rounded-full flex items-center justify-center text-on-surface/40 hover:bg-surface2 transition-colors">
                <span class="material-symbols-outlined font-black">close</span>
            </button>
        </div>

        <!-- Modal Body -->
        <form method="POST" action="?page=leaves&action=submit" class="p-5 pt-1 space-y-5">
            <!-- Type Selection (Radio Cards) -->
            <div class="space-y-2.5">
                <label data-theme-muted class="text-[9px] font-black uppercase tracking-widest opacity-50 block">Pilih Tipe Izin</label>
                <div class="grid grid-cols-2 gap-2.5">
                    <?php 
                    $types = [
                        ['val' => 'sick',     'label' => 'Sakit', 'sym' => 'medical_services', 'clr' => 'emerald'],
                        ['val' => 'annual',   'label' => 'Cuti',  'sym' => 'beach_access', 'clr' => 'blue'],
                        ['val' => 'personal', 'label' => 'Urgent', 'sym' => 'priority_high', 'clr' => 'amber'],
                        ['val' => 'other',    'label' => 'Lainnya','sym' => 'more_horiz', 'clr' => 'purple'],
                    ];
                    foreach ($types as $t):
                    ?>
                    <label class="relative cursor-pointer group">
                        <input type="radio" name="leave_type" value="<?= $t['val'] ?>" class="peer sr-only" required <?= $t['val'] === 'sick' ? 'checked' : '' ?>>
                        <div class="radio-card p-4 rounded-2xl border-2 border-border transition-all group-active:scale-[0.98] flex items-center gap-3" style="background:var(--surface);">
                            <div class="icon-box w-10 h-10 rounded-xl bg-<?= $t['clr'] ?>-500/10 text-<?= $t['clr'] ?>-500 flex items-center justify-center shrink-0 transition-colors">
                                <span class="material-symbols-outlined text-xl font-black"><?= $t['sym'] ?></span>
                            </div>
                            <span data-theme-text class="label-text text-xs font-black tracking-tight transition-colors"><?= $t['label'] ?></span>
                        </div>
                    </label>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Date Range -->
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-3">
                    <label data-theme-muted class="text-[9px] font-black uppercase tracking-widest opacity-50 block">Dari Tanggal</label>
                    <input data-theme-text name="leave_start" type="date" class="w-full border border-border rounded-xl px-4 py-3 text-xs font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" style="background:var(--surface2) !important; color:var(--text-primary) !important;" min="<?= date('Y-m-d') ?>" required>
                </div>
                <div class="space-y-3">
                    <label data-theme-muted class="text-[9px] font-black uppercase tracking-widest opacity-50 block">Hingga Tanggal</label>
                    <input data-theme-text name="leave_end" type="date" class="w-full border border-border rounded-xl px-4 py-3 text-xs font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" style="background:var(--surface2) !important; color:var(--text-primary) !important;" min="<?= date('Y-m-d') ?>" required>
                </div>
            </div>

            <!-- Reason -->
            <div class="space-y-2.5">
                <label data-theme-muted class="text-[9px] font-black uppercase tracking-widest opacity-50 block">Alasan & Keterangan</label>
                <textarea data-theme-text name="leave_reason" rows="3" class="w-full border border-border rounded-xl px-4 py-2.5 text-xs font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all resize-none" style="background:var(--surface2) !important; color:var(--text-primary) !important;" placeholder="Tuliskan alasan pengajuan Anda..." required></textarea>
            </div>

            <!-- Actions -->
            <div class="pt-4 flex flex-col gap-3">
                <button type="submit" class="w-full py-5 bg-primary text-white rounded-full text-sm font-black uppercase shadow-xl shadow-primary/20 active:scale-95 transition-all flex items-center justify-center gap-3">
                    <span class="material-symbols-outlined font-black">send</span>
                    Kirim Pengajuan
                </button>
                <button type="button" onclick="closeModal('leaveModal')" data-theme-muted class="w-full py-3 text-xs font-bold uppercase tracking-widest hover:bg-surface2 rounded-full transition-all text-center">
                    Batal
                </button>
            </div>
        </form>
    </div>
</div>

<style>

.stat-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 12px;
}
.stat-card {
    background: var(--surface);
    border-radius: 14px;
    padding: 16px;
    display: flex;
    align-items: center;
    gap: 14px;
    border: 1px solid var(--border);
    box-shadow: var(--shadow);
}
.st-icon {
    width: 40px;
    height: 40px;
    border-radius: 10px;
    flex-shrink: 0;
}
.icon-size {
    font-size: 20px;
}
.st-label {
    font-size: 9px;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: var(--text-muted);
    margin: 0 0 2px;
}
.st-val {
    font-size: 16px;
    font-weight: 950;
    color: var(--text-primary);
    margin: 0;
}

@media (max-width: 640px) {
    .stat-grid {
        gap: 6px;
        grid-template-columns: repeat(3, 1fr);
    }
    .stat-card {
        padding: 5px 2px;
        gap: 4px;
        flex-direction: column;
        text-align: center;
        border-radius: 8px;
    }
    .st-icon {
        width: 24px !important;
        height: 24px !important;
        border-radius: 6px !important;
        margin: 0 auto;
    }
    .icon-size {
        font-size: 14px !important;
    }
    .st-label {
        font-size: 7px !important;
        letter-spacing: 0 !important;
    }
    .st-val {
        font-size: 11px !important;
    }
}
</style>

<?php endif; ?>

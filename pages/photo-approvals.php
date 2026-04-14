<?php
// pages/photo-approvals.php – Approval Foto Absensi (HRD)
$attendance = get_attendance();
$pending = array_filter($attendance, fn($a) => $a['attendance_type']==='photo' && $a['approval_status']==='pending');
?>
<div class="card overflow-hidden">
    <div class="px-6 py-4 border-b">
        <h3 class="font-bold text-gray-900 flex items-center gap-2">
            <i class="fas fa-camera text-purple-500"></i> Approval Foto Absensi
        </h3>
    </div>
    <?php if (!empty($pending)): ?>
    <div class="divide-y divide-gray-100">
        <?php foreach ($pending as $rec): ?>
        <div class="flex items-center gap-4 px-6 py-4 hover:bg-gray-50 transition-colors">
            <div class="w-10 h-10 bg-gradient-to-br <?= avatar_color($rec['employee_name']) ?> rounded-full flex items-center justify-center text-white text-sm font-bold flex-shrink-0">
                <?= avatar_initials($rec['employee_name']) ?>
            </div>
            <div class="flex-1">
                <div class="font-semibold text-sm text-gray-900"><?= h($rec['employee_name']) ?></div>
                <div class="text-xs text-gray-400">
                    <?= format_date($rec['attendance_date']) ?> – <?= h($rec['attendance_time']) ?> – <?= h($rec['location']) ?>
                </div>
                <?php if ($rec['photo_path']): ?>
                <div class="mt-2">
                    <a href="<?= h($rec['photo_path']) ?>" target="_blank" class="inline-block rounded-xl overflow-hidden border-2 border-gray-100 hover:border-indigo-400 transition-all shadow-sm">
                        <img src="<?= h($rec['photo_path']) ?>" class="max-w-[200px] h-auto" alt="Foto Absen">
                    </a>
                </div>
                <?php endif; ?>
            </div>
            <div class="flex gap-2">
                <a href="?page=photo-approvals&action=approve&id=<?= $rec['id'] ?>" class="btn btn-success py-1.5 px-3 text-xs">
                    <i class="fas fa-check"></i> Setuju
                </a>
                <a href="?page=photo-approvals&action=reject&id=<?= $rec['id'] ?>" class="btn btn-danger py-1.5 px-3 text-xs">
                    <i class="fas fa-times"></i> Tolak
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div class="text-center py-16 text-gray-400">
        <i class="fas fa-check-circle text-5xl mb-4 text-emerald-300"></i>
        <p class="font-medium">Tidak ada foto yang menunggu approval</p>
    </div>
    <?php endif; ?>
</div>

<?php
// pages/photo-approvals.php – Visual Verification Console (HRD only)
$attendance = get_attendance();
$pending = array_filter($attendance, fn($a) => $a['attendance_type']==='photo' && $a['approval_status']==='pending');
?>

<div class="space-y-8 performance-page-container">
    
    <!-- ══ Header Section ══ -->
    <header class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-3xl font-bold">photo_camera_front</span>
                </div>
                <h1 data-theme-text class="text-4xl font-bold  leading-none">Visual Identity Control</h1>
            </div>
            <p data-theme-muted class="text-[10px] font-bold   ml-1 opacity-50">Biometric Verification Analytics</p>
        </div>
        
        <div class="flex items-center gap-2">
            <span class="badge badge-rose animate-pulse"><?= count($pending) ?> Action Required</span>
        </div>
    </header>

    <!-- ══ Verification Stream ══ -->
    <?php if (!empty($pending)): ?>
    <div id="photoGrid" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 pb-20">
        <?php foreach ($pending as $rec): ?>
        <div data-theme-card class="bg-surface rounded-lg border border-border shadow-sm overflow-hidden flex flex-col group hover:shadow-2xl transition-all duration-500">
            <!-- Header Info -->
            <div class="p-6 pb-2 flex items-center gap-4">
                <div class="w-12 h-12 rounded-lg bg-surface2 border border-border flex items-center justify-center text-primary font-bold text-lg">
                    <?= avatar_initials($rec['employee_name']) ?>
                </div>
                <div>
                    <h4 data-theme-text class="text-sm font-bold  leading-tight"><?= h($rec['employee_name']) ?></h4>
                    <p data-theme-muted class="text-[9px] font-bold   opacity-30 mt-1"><?= h($rec['employee_name']) ?> · Site Check-in</p>
                </div>
            </div>

            <!-- Photo Area -->
            <div class="px-6 py-4">
                <div class="relative group/img rounded-lg overflow-hidden aspect-[4/3] bg-surface2 shadow-sm border-none">
                    <?php if ($rec['photo_path']): ?>
                        <img src="<?= h($rec['photo_path']) ?>" class="w-full h-full object-cover transition-transform duration-700 group-hover/img:scale-110" alt="Selfie Verification">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent opacity-0 group-hover/img:opacity-100 transition-opacity flex flex-col justify-end p-6">
                             <p class="text-[8px] font-bold text-white/50   mb-1">Captured At</p>
                             <p class="text-[10px] font-bold text-white  "><?= h($rec['attendance_time']) ?> · <?= date('d M Y', strtotime($rec['attendance_date'])) ?></p>
                        </div>
                    <?php else: ?>
                        <div class="w-full h-full flex flex-col items-center justify-center opacity-20">
                            <span class="material-symbols-outlined text-4xl mb-2">no_photography</span>
                            <span class="text-[10px] font-bold  ">Image Missing</span>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Footer Stats -->
            <div class="px-8 pb-4 flex items-center justify-between text-[9px] font-bold   opacity-30">
                <div class="flex items-center gap-2">
                    <span class="material-symbols-outlined text-sm">location_on</span>
                    <span><?= h($rec['location']) ?></span>
                </div>
                <span>ID: <?= $rec['id'] ?></span>
            </div>

            <!-- Action Cluster -->
            <div class="p-6 pt-2 grid grid-cols-2 gap-3">
                <a href="?page=photo-approvals&action=approve&id=<?= $rec['id'] ?>" class="w-full py-4 bg-emerald-500 text-white rounded-lg text-[9px] font-bold   shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2 active:scale-95 transition-all">
                    <span class="material-symbols-outlined text-sm">how_to_reg</span>
                    <span>Authorize</span>
                </a>
                <a href="?page=photo-approvals&action=reject&id=<?= $rec['id'] ?>" class="w-full py-4 bg-rose-500/5 text-rose-500 hover:bg-rose-500 hover:text-white border border-rose-500/10 rounded-lg text-[9px] font-bold   flex items-center justify-center gap-2 active:scale-95 transition-all">
                    <span class="material-symbols-outlined text-sm">block</span>
                    <span>Decline</span>
                </a>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php else: ?>
    <div data-theme-card class="p-32 bg-surface rounded-lg border border-border border-dashed flex flex-col items-center justify-center">
        <div class="w-20 h-20 rounded-full bg-emerald-500/10 text-emerald-500 flex items-center justify-center mb-6">
            <span class="material-symbols-outlined text-4xl font-bold">task_alt</span>
        </div>
        <h3 data-theme-text class="text-xl font-bold mb-2">Queue Clear</h3>
        <p data-theme-muted class="text-xs font-bold   opacity-30">All identity verifications are up to date</p>
    </div>
    <?php endif; ?>
</div>

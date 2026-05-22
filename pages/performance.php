<?php
// pages/performance.php – Penilaian Kinerja
$employees = get_employees();
$reviews = get_performance_reviews();
$criteria = ['work_quality'=>'Kualitas Kerja','productivity'=>'Produktivitas','communication'=>'Komunikasi','teamwork'=>'Kerjasama','initiative'=>'Inisiatif'];

if (!auth_is_hrd()):
    // Tampilan karyawan – review milik sendiri
    $r = $reviews[$user['id']] ?? null;
?>

<!-- Header Section -->
<section class="mb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
    <div>
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined font-bold">monitoring</span>
            </div>
            <h1 data-theme-text class="text-3xl font-bold  leading-none">Performance Summary</h1>
        </div>
        <p data-theme-muted class="text-[10px] font-bold   ml-1">Self Assessment Ledger</p>
    </div>
</section>

<style>
    /* Performance Page Specific Overrides */
    html.dark .performance-page-container {
        --primary: #00f2ff; /* Neon Blue */
        --primary-rgb: 0, 242, 255;
    }
    
    html.dark .performance-page-container [data-theme-card] {
        border-color: rgba(255, 255, 255, 0.02) !important; /* Almost invisible */
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
    }

    /* Replace all blue variants with Neon */
    html.dark .text-primary, html.dark .text-blue-500 { color: #00f2ff !important; }
    html.dark .bg-primary, html.dark .bg-blue-500 { background-color: #00f2ff !important; }
    html.dark .bg-primary\/10, html.dark .bg-blue-500\/10 { background-color: rgba(0, 242, 255, 0.1) !important; }
    html.dark .border-primary\/10, html.dark .border-blue-500\/10 { border-color: rgba(0, 242, 255, 0.1) !important; }
    html.dark .border-primary\/20, html.dark .border-blue-500\/20 { border-color: rgba(0, 242, 255, 0.2) !important; }
    html.dark .shadow-primary\/20, html.dark .shadow-blue-500\/20 { shadow-color: rgba(0, 242, 255, 0.2) !important; }
    
    /* Force border removal in dark mode for this page */
    html.dark .performance-page-container .border { border-color: rgba(255, 255, 255, 0.02) !important; }
    html.dark .performance-page-container .border-b { border-bottom-color: rgba(255, 255, 255, 0.02) !important; }
</style>

<div class="performance-page-container">

<?php if (!$r): ?>
    <div data-theme-card class="bg-surface p-12 text-center rounded-lg border border-border shadow-sm flex flex-col items-center justify-center space-y-4">
        <span class="material-symbols-outlined text-5xl opacity-20">bar_chart_4_bars</span>
        <div>
            <p data-theme-text class="text-sm font-bold  ">Belum ada review untuk Anda</p>
            <p data-theme-muted class="text-[10px] font-bold mt-1">HRD akan mengisi penilaian kinerja Anda secara berkala.</p>
        </div>
    </div>
<?php else: ?>
    <div class="w-full space-y-6">
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <!-- Overall Score Card -->
            <div data-theme-card class="md:col-span-1 bg-surface p-8 rounded-lg border border-border shadow-sm flex flex-col items-center justify-center text-center relative overflow-hidden group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-primary/5 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                <div class="w-20 h-20 rounded-lg bg-primary/10 text-primary flex items-center justify-center mb-4 border border-primary/10">
                    <span class="material-symbols-outlined text-4xl font-bold">military_tech</span>
                </div>
                <div data-theme-muted class="text-[10px] font-bold   mb-1 opacity-50">Overall Performance</div>
                <div class="text-5xl font-bold text-primary  leading-none mb-2"><?= number_format($r['overall'], 1) ?></div>
                <div class="px-3 py-1 bg-primary/10 text-primary rounded-full text-[10px] font-bold  ">
                    <?= $r['overall'] >= 4.5 ? 'Elite' : ($r['overall'] >= 3.5 ? 'Strong' : 'Steady') ?>
                </div>
            </div>

            <!-- Profile Info & Feedback -->
            <div data-theme-card class="md:col-span-2 bg-surface p-8 rounded-lg border border-border shadow-sm flex flex-col justify-between relative overflow-hidden">
                <div class="flex items-center gap-4 mb-6">
                    <div class="w-14 h-14 rounded-lg overflow-hidden border border-border">
                        <?php if (!empty($user['photo_profile'])): ?>
                            <img src="<?= h($user['photo_profile']) ?>" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full bg-primary/10 text-primary flex items-center justify-center font-bold text-lg">
                                <?= avatar_initials($user['name']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <div>
                        <h2 data-theme-text class="text-xl font-bold "><?= h($user['name']) ?></h2>
                        <p data-theme-muted class="text-xs opacity-50"><?= h($user['position']) ?> • <?= h($user['department']) ?></p>
                    </div>
                </div>

                <div class="relative bg-surface2 p-5 rounded-lg border-none shadow-sm">
                    <span class="material-symbols-outlined absolute -top-3 -left-1 text-primary/20 text-4xl rotate-12">format_quote</span>
                    <p data-theme-text class="text-sm italic leading-relaxed opacity-80 font-medium relative z-10">
                        "<?= h($r['feedback'] ?: 'No specific feedback provided yet. Keep up the great work!') ?>"
                    </p>
                    <div data-theme-muted class="text-[9px] font-bold   mt-4 text-right opacity-30">— HR Review Panel</div>
                </div>
            </div>
        </div>

        <!-- Detailed Metrics Grid -->
        <div data-theme-card class="bg-surface p-8 rounded-lg border border-border shadow-sm">
            <div class="flex items-center justify-between mb-8">
                <h3 data-theme-text class="text-sm font-bold  ">Evaluation Metrics</h3>
                <span class="text-[10px] font-bold opacity-30 italic">Updated: <?= date('F Y') ?></span>
            </div>
            
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-x-12 gap-y-8">
                <?php 
                $icons = ['work_quality'=>'task_alt','productivity'=>'bolt','communication'=>'forum','teamwork'=>'groups','initiative'=>'lightbulb'];
                foreach ($criteria as $key => $label): 
                ?>
                <div class="space-y-3 group">
                    <div class="flex justify-between items-center">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-outlined text-sm text-primary opacity-50 group-hover:opacity-100 transition-opacity"><?= $icons[$key] ?? 'check_circle' ?></span>
                            <span data-theme-text class="text-[11px] font-bold   opacity-70"><?= $label ?></span>
                        </div>
                        <span class="text-xs font-bold text-primary"><?= number_format($r[$key], 1) ?></span>
                    </div>
                    <div class="h-2 w-full bg-surface2 rounded-full overflow-hidden p-0.5 border-none">
                        <div class="h-full bg-primary rounded-full transition-all duration-1000 ease-out" style="width:<?= ($r[$key]/5*100) ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
<?php endif; return; endif; ?>

<!-- HRD View -->
<div class="space-y-6">
    <!-- Header Section -->
    <section class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined font-bold">analytics</span>
                </div>
                <h1 data-theme-text class="text-3xl font-bold  leading-none">Employee Reviews</h1>
            </div>
            <p data-theme-muted class="text-[10px] font-bold   ml-1">Team Performance Ledger</p>
        </div>
        
        <button onclick="openModal('reviewModal')" class="group relative px-6 py-3.5 bg-primary text-white rounded-lg font-bold text-xs   flex items-center gap-3 overflow-hidden transition-all hover:scale-[1.02] active:scale-95 shadow-xl shadow-primary/20">
            <span class="material-symbols-outlined text-lg">add_circle</span>
            <span>Tambah Review</span>
            <div class="absolute inset-0 bg-white/10 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
        </button>
    </section>

    <!-- HRD Summary Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <?php 
        $avg_score = !empty($reviews) ? array_sum(array_column($reviews, 'overall')) / count($reviews) : 0;
        $total_reviewed = count($reviews);
        $top_performer = !empty($reviews) ? max(array_column($reviews, 'overall')) : 0;
        ?>
        <div data-theme-card class="bg-surface p-4 rounded-lg border border-border shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-blue-500/10 text-blue-500 flex items-center justify-center grow-0 shrink-0">
                <span class="material-symbols-outlined text-xl">avg_pace</span>
            </div>
            <div>
                <div data-theme-muted class="text-[8px] font-bold   opacity-40">Average Team Score</div>
                <div data-theme-text class="text-lg font-bold"><?= number_format($avg_score, 1) ?></div>
            </div>
        </div>
        <div data-theme-card class="bg-surface p-4 rounded-lg border border-border shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center grow-0 shrink-0">
                <span class="material-symbols-outlined text-xl">reviews</span>
            </div>
            <div>
                <div data-theme-muted class="text-[8px] font-bold   opacity-40">Reviews Completed</div>
                <div data-theme-text class="text-lg font-bold"><?= $total_reviewed ?></div>
            </div>
        </div>
        <div data-theme-card class="bg-surface p-4 rounded-lg border border-border shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center grow-0 shrink-0">
                <span class="material-symbols-outlined text-xl">stars</span>
            </div>
            <div>
                <div data-theme-muted class="text-[8px] font-bold   opacity-40">Top Achievement</div>
                <div data-theme-text class="text-lg font-bold"><?= number_format($top_performer, 1) ?></div>
            </div>
        </div>
        <div data-theme-card class="bg-surface p-4 rounded-lg border border-border shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 rounded-lg bg-rose-500/10 text-rose-500 flex items-center justify-center grow-0 shrink-0">
                <span class="material-symbols-outlined text-xl">pending_actions</span>
            </div>
            <div>
                <div data-theme-muted class="text-[8px] font-bold   opacity-40">Pending Evaluations</div>
                <div data-theme-text class="text-lg font-bold"><?= count($employees) - $total_reviewed ?></div>
            </div>
        </div>
    </div>

    <!-- Review Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">
        <?php foreach ($employees as $emp):
            $r = $reviews[$emp['id']] ?? null;
            if (!$r) continue;
        ?>
        <div data-theme-card class="bg-surface p-6 rounded-lg border border-border shadow-sm group hover:shadow-xl hover:-translate-y-1.5 transition-all duration-500 relative overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-primary/[0.02] rounded-bl-full transform translate-x-12 -translate-y-12"></div>
            
            <div class="flex items-center gap-4 mb-5 pb-4">
                <div class="w-12 h-12 bg-primary/10 rounded-lg flex items-center justify-center text-primary border border-primary/20 font-bold shrink-0 relative">
                    <?= avatar_initials($emp['name']) ?>
                    <?php if ($r['overall'] >= 4.5): ?>
                        <div class="absolute -top-1.5 -right-1.5 w-5 h-5 bg-amber-400 rounded-full border-2 border-surface flex items-center justify-center shadow-sm">
                            <span class="material-symbols-outlined text-[10px] text-white font-bold">star</span>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="min-w-0">
                    <div data-theme-text class="font-bold  text-[15px] truncate"><?= h($emp['name']) ?></div>
                    <div data-theme-muted class="text-[10px] font-bold  opacity-40"><?= h($emp['position']) ?></div>
                </div>
            </div>
            
            <div class="grid grid-cols-2 gap-3 mb-6">
                <div class="p-3 bg-surface2 rounded-lg border-none">
                    <div class="text-[8px] font-bold   opacity-30 mb-0.5">Rating Score</div>
                    <div class="text-xl font-bold text-primary leading-none"><?= number_format($r['overall'], 1) ?></div>
                </div>
                <div class="p-3 bg-surface2 rounded-lg border-none flex flex-col justify-center">
                    <div class="text-[8px] font-bold   opacity-30 mb-1">Standing</div>
                    <span class="text-[9px] font-bold   <?= $r['overall']>=4.5?'text-emerald-500':($r['overall']>=3.5?'text-primary':'text-amber-600') ?>">
                        <?= $r['overall']>=4.5?'Elite':($r['overall']>=3.5?'Strong':'Consistent') ?>
                    </span>
                </div>
            </div>

            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <div data-theme-muted class="text-[9px] font-bold   opacity-40">Core Competencies</div>
                    <div class="h-px bg-border flex-1 mx-4 opacity-30"></div>
                </div>
                <?php foreach (array_slice($criteria,0,3,true) as $key=>$label): ?>
                <div class="space-y-1.5">
                    <div class="flex justify-between items-center text-[9px] font-bold ">
                        <span data-theme-text class="opacity-60"><?= $label ?></span>
                        <span class="text-primary"><?= number_format($r[$key], 1) ?></span>
                    </div>
                    <div class="h-1.5 w-full bg-surface2 rounded-full overflow-hidden p-0.5">
                        <div class="h-full bg-primary rounded-full" style="width:<?= ($r[$key]/5*100) ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="grid grid-cols-2 gap-2 mt-6">
                <button class="py-3 border border-border hover:bg-surface2 rounded-lg text-[10px] font-bold   text-on-surface/40 hover:text-primary transition-all active:scale-95">
                    View Dossier
                </button>
                <button onclick="confirmDeleteReview(<?= $r['id'] ?>, '<?= h($emp['name']) ?>')" class="py-3 bg-rose-500/5 text-rose-500 hover:bg-rose-500 hover:text-white rounded-lg text-[10px] font-bold   transition-all active:scale-95 border border-rose-500/10">
                    <span class="material-symbols-outlined text-sm">delete</span>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal Tambah Review -->
<div id="reviewModal" style="display:none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-md p-4" onclick="if(event.target===this)closeModal('reviewModal')">
    <div data-theme-card class="w-full max-w-lg bg-surface rounded-lg border border-border shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
        <!-- Header -->
        <div class="p-5 pb-1 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined font-bold">edit_note</span>
                </div>
                <div>
                    <h3 data-theme-text class="text-lg font-bold ">Tambah Review</h3>
                    <p data-theme-muted class="text-[9px] font-bold   opacity-50">Evaluation Matrix</p>
                </div>
            </div>
            <button onclick="closeModal('reviewModal')" class="w-10 h-10 rounded-full flex items-center justify-center text-on-surface/40 hover:bg-surface2 transition-colors">
                <span class="material-symbols-outlined font-bold">close</span>
            </button>
        </div>

        <form method="POST" action="?page=performance&action=add" class="p-5 pt-3 space-y-4">
            <div class="space-y-1.5">
                <label data-theme-muted class="text-[9px] font-bold   opacity-50 block">Pilih Karyawan</label>
                <select name="employee_id" class="w-full border border-border rounded-lg px-4 py-2.5 text-xs font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all appearance-none" style="background:var(--surface2)!important; color:var(--text-primary)!important;" required>
                    <option value="">-- Pilih Karyawan --</option>
                    <?php foreach ($employees as $emp): ?>
                    <option value="<?= h($emp['id']) ?>"><?= h($emp['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <?php foreach ($criteria as $key => $label): ?>
                <div class="space-y-1.5">
                    <div class="flex justify-between items-center">
                        <label data-theme-muted class="text-[9px] font-bold   opacity-50 block"><?= $label ?></label>
                        <span id="label-<?= $key ?>" class="text-[10px] font-bold text-primary">4 / 5</span>
                    </div>
                    <input name="<?= $key ?>" type="range" min="1" max="5" step="0.5" value="4" 
                           oninput="document.getElementById('label-<?= $key ?>').innerText = this.value + ' / 5'"
                           class="w-full h-1.5 bg-surface2 rounded-full appearance-none cursor-pointer accent-primary border border-border">
                </div>
                <?php endforeach; ?>
            </div>

            <div class="space-y-1.5">
                <label data-theme-muted class="text-[9px] font-bold   opacity-50 block">Feedback Catatan</label>
                <textarea data-theme-text name="feedback" rows="3" class="w-full border border-border rounded-lg px-4 py-2.5 text-xs font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all resize-none" style="background:var(--surface2)!important; color:var(--text-primary)!important;" placeholder="Tulis catatan evaluasi..."></textarea>
            </div>

            <div class="pt-2 flex flex-col gap-3">
                <button type="submit" class="w-full py-5 bg-primary text-white rounded-full text-xs font-bold  shadow-xl shadow-primary/20 active:scale-95 transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-lg">save</span>
                    Simpan Review
                </button>
                <button type="button" onclick="closeModal('reviewModal')" data-theme-muted class="w-full py-2 text-[10px] font-bold  hover:bg-surface2 rounded-full transition-colors text-center opacity-40">Batal</button>
            </div>
        </form>
    </div>
</div>
<!-- Modal Konfirmasi Hapus Review -->
<div id="deleteReviewModal" style="display:none;" class="fixed inset-0 z-[110] flex items-center justify-center bg-black/90 backdrop-blur-sm p-4" onclick="if(event.target===this)closeModal('deleteReviewModal')">
    <div data-theme-card class="w-full max-w-sm bg-surface rounded-lg border border-border shadow-2xl overflow-hidden p-8 text-center">
        <div class="w-16 h-16 bg-rose-500/10 text-rose-500 rounded-lg flex items-center justify-center mx-auto mb-6">
            <span class="material-symbols-outlined text-3xl font-bold">folder_delete</span>
        </div>
        <h3 data-theme-text class="text-xl font-bold  mb-2">Purge Evaluation?</h3>
        <p data-theme-muted class="text-xs font-medium opacity-60 mb-8 leading-relaxed">This will permanently delete the performance dossier for <span id="del_rev_name" class="font-bold text-on-surface"></span>. This action is recorded in the audit log.</p>
        
        <div class="grid grid-cols-2 gap-3">
            <button onclick="closeModal('deleteReviewModal')" class="py-3.5 bg-surface2 text-on-surface font-bold text-[10px] rounded-lg border border-border">Cancel</button>
            <a id="del_rev_btn" href="#" class="py-3.5 bg-rose-500 text-white font-bold text-[10px] rounded-lg shadow-lg shadow-rose-500/20">Purge Data</a>
        </div>
    </div>
</div>

<script>
function confirmDeleteReview(id, name) {
    document.getElementById('del_rev_name').innerText = name;
    document.getElementById('del_rev_btn').href = `?page=performance&action=delete&id=${id}`;
    openModal('deleteReviewModal');
}
</script>

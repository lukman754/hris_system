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
            <div class="w-10 h-10 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined font-black">monitoring</span>
            </div>
            <h1 data-theme-text class="text-3xl font-black tracking-tighter leading-none">Performance Summary</h1>
        </div>
        <p data-theme-muted class="text-[10px] font-black uppercase tracking-[0.3em] ml-1">Self Assessment Ledger</p>
    </div>
</section>

<?php if (!$r): ?>
    <div data-theme-card class="bg-surface p-12 text-center rounded-2xl border border-border shadow-sm flex flex-col items-center justify-center space-y-4">
        <span class="material-symbols-outlined text-5xl opacity-20">bar_chart_4_bars</span>
        <div>
            <p data-theme-text class="text-sm font-black uppercase tracking-widest">Belum ada review untuk Anda</p>
            <p data-theme-muted class="text-[10px] font-bold mt-1">HRD akan mengisi penilaian kinerja Anda secara berkala.</p>
        </div>
    </div>
<?php else: ?>
    <div data-theme-card class="bg-surface p-5 max-w-2xl mx-auto rounded-2xl border border-border shadow-xl space-y-6">
        <!-- Profile & Overall Score -->
        <div class="flex items-center gap-5 pb-5 border-b border-border">
            <div class="w-16 h-16 bg-primary/10 rounded-2xl flex items-center justify-center text-primary border border-primary/20 shrink-0">
                <span class="material-symbols-outlined text-3xl font-black">person</span>
            </div>
            <div class="flex-1 min-w-0">
                <h3 data-theme-text class="font-black tracking-tight text-lg truncate"><?= h($user['name']) ?></h3>
                <p data-theme-muted class="text-[10px] font-black uppercase tracking-widest opacity-50 mt-1"><?= h($user['position']) ?></p>
            </div>
            <div class="text-center px-4 py-3 bg-surface2 rounded-xl border border-border">
                <div class="text-3xl font-black text-primary leading-none"><?= number_format($r['overall'],1) ?></div>
                <div data-theme-muted class="text-[8px] font-black uppercase tracking-widest mt-1.5 opacity-50">Score</div>
            </div>
        </div>

        <!-- Metrics Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-5">
            <?php foreach ($criteria as $key => $label): ?>
            <div class="space-y-2">
                <div class="flex justify-between items-end">
                    <span data-theme-text class="text-[10px] font-black uppercase tracking-wider opacity-60"><?= $label ?></span>
                    <span data-theme-text class="text-xs font-black text-primary"><?= $r[$key] ?>/5</span>
                </div>
                <div class="h-1.5 w-full bg-surface2 rounded-full overflow-hidden border border-border/50">
                    <div class="h-full bg-primary rounded-full" style="width:<?= ($r[$key]/5*100) ?>%"></div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>

        <!-- Feedback Box -->
        <div class="p-4 bg-primary/5 rounded-xl border border-primary/10">
            <div class="flex items-center gap-2 mb-2">
                <span class="material-symbols-outlined text-primary text-sm font-black">chat_bubble</span>
                <h4 data-theme-text class="text-[10px] font-black uppercase tracking-widest">HR Feedback</h4>
            </div>
            <p data-theme-text class="text-xs leading-relaxed opacity-80 italic font-medium">"<?= h($r['feedback']) ?>"</p>
        </div>
    </div>
<?php endif; return; endif; ?>

<!-- HRD View -->
<div class="space-y-6">
    <!-- Header Section -->
    <section class="flex flex-col md:flex-row md:items-end justify-between gap-4">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined font-black">analytics</span>
                </div>
                <h1 data-theme-text class="text-3xl font-black tracking-tighter leading-none">Employee Reviews</h1>
            </div>
            <p data-theme-muted class="text-[10px] font-black uppercase tracking-[0.3em] ml-1">Team Performance Ledger</p>
        </div>
        
        <button onclick="openModal('reviewModal')" class="group relative px-6 py-3.5 bg-primary text-white rounded-xl font-black text-xs uppercase tracking-widest flex items-center gap-3 overflow-hidden transition-all hover:scale-105 active:scale-95 shadow-xl shadow-primary/20">
            <span class="material-symbols-outlined text-lg">add_circle</span>
            <span>Tambah Review</span>
            <div class="absolute inset-0 bg-white/10 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
        </button>
    </section>

    <!-- Review Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5">
        <?php foreach ($employees as $emp):
            $r = $reviews[$emp['id']] ?? null;
            if (!$r) continue;
        ?>
        <div data-theme-card class="bg-surface p-5 rounded-2xl border border-border shadow-sm space-y-4 transition-all hover:shadow-xl hover:-translate-y-1">
            <div class="flex items-center gap-4">
                <div class="w-11 h-11 bg-primary/10 rounded-xl flex items-center justify-center text-primary border border-primary/20 font-black text-sm shrink-0">
                    <?= avatar_initials($emp['name']) ?>
                </div>
                <div class="min-w-0">
                    <div data-theme-text class="font-black tracking-tight text-sm truncate"><?= h($emp['name']) ?></div>
                    <div data-theme-muted class="text-[9px] font-black uppercase opacity-40"><?= h($emp['position']) ?></div>
                </div>
            </div>
            
            <div class="flex items-center justify-between py-2 bg-surface2 px-4 rounded-xl border border-border">
                <div>
                   <div class="text-2xl font-black text-primary leading-none"><?= number_format($r['overall'],1) ?></div>
                   <div class="text-[8px] font-black uppercase tracking-widest opacity-30 mt-1">Overall</div>
                </div>
                <div class="text-right">
                    <span class="px-2.5 py-1 rounded-lg text-[9px] font-black uppercase tracking-widest <?= $r['overall']>=4.5?'bg-emerald-500/10 text-emerald-500':($r['overall']>=3.5?'bg-primary/10 text-primary':'bg-amber-500/10 text-amber-500') ?>">
                        <?= $r['overall']>=4.5?'Excellent':($r['overall']>=3.5?'Good':'Average') ?>
                    </span>
                </div>
            </div>

            <div class="space-y-3 pt-1">
                <?php foreach (array_slice($criteria,0,3,true) as $key=>$label): ?>
                <div class="space-y-1.5">
                    <div class="flex justify-between text-[8px] uppercase font-black tracking-widest opacity-40">
                        <span data-theme-text><?= $label ?></span>
                        <span class="text-primary"><?= $r[$key] ?></span>
                    </div>
                    <div class="h-1 w-full bg-surface2 rounded-full overflow-hidden">
                        <div class="h-full bg-primary" style="width:<?= ($r[$key]/5*100) ?>%"></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal Tambah Review -->
<div id="reviewModal" style="display:none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-md p-4" onclick="if(event.target===this)closeModal('reviewModal')">
    <div data-theme-card class="w-full max-w-lg bg-surface rounded-2xl border border-border shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
        <!-- Header -->
        <div class="p-5 pb-1 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined font-black">edit_note</span>
                </div>
                <div>
                    <h3 data-theme-text class="text-lg font-black tracking-tighter">Tambah Review</h3>
                    <p data-theme-muted class="text-[9px] font-black uppercase tracking-widest opacity-50">Evaluation Matrix</p>
                </div>
            </div>
            <button onclick="closeModal('reviewModal')" class="w-10 h-10 rounded-full flex items-center justify-center text-on-surface/40 hover:bg-surface2 transition-colors">
                <span class="material-symbols-outlined font-black">close</span>
            </button>
        </div>

        <form method="POST" action="?page=performance&action=add" class="p-5 pt-3 space-y-4">
            <div class="space-y-1.5">
                <label data-theme-muted class="text-[9px] font-black uppercase tracking-widest opacity-50 block">Pilih Karyawan</label>
                <select name="employee_id" class="w-full border border-border rounded-xl px-4 py-2.5 text-xs font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all appearance-none" style="background:var(--surface2)!important; color:var(--text-primary)!important;" required>
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
                        <label data-theme-muted class="text-[9px] font-black uppercase tracking-widest opacity-50 block"><?= $label ?></label>
                        <span id="label-<?= $key ?>" class="text-[10px] font-black text-primary">4 / 5</span>
                    </div>
                    <input name="<?= $key ?>" type="range" min="1" max="5" step="0.5" value="4" 
                           oninput="document.getElementById('label-<?= $key ?>').innerText = this.value + ' / 5'"
                           class="w-full h-1.5 bg-surface2 rounded-full appearance-none cursor-pointer accent-primary border border-border">
                </div>
                <?php endforeach; ?>
            </div>

            <div class="space-y-1.5">
                <label data-theme-muted class="text-[9px] font-black uppercase tracking-widest opacity-50 block">Feedback Catatan</label>
                <textarea data-theme-text name="feedback" rows="3" class="w-full border border-border rounded-xl px-4 py-2.5 text-xs font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all resize-none" style="background:var(--surface2)!important; color:var(--text-primary)!important;" placeholder="Tulis catatan evaluasi..."></textarea>
            </div>

            <div class="pt-2 flex flex-col gap-3">
                <button type="submit" class="w-full py-5 bg-primary text-white rounded-full text-xs font-black uppercase shadow-xl shadow-primary/20 active:scale-95 transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-lg">save</span>
                    Simpan Review
                </button>
                <button type="button" onclick="closeModal('reviewModal')" data-theme-muted class="w-full py-2 text-[10px] font-black uppercase hover:bg-surface2 rounded-full transition-colors text-center opacity-40">Batal</button>
            </div>
        </form>
    </div>
</div>

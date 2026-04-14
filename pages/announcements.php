<?php
// pages/announcements.php – Berita & Pengumuman Modern
$announcements = get_announcements();
$priority_config = [
    'important' => ['bg'=>'bg-rose-500/10', 'text'=>'text-rose-500', 'label'=>'CRITICAL', 'icon'=>'priority_high'],
    'high'      => ['bg'=>'bg-amber-500/10', 'text'=>'text-amber-500', 'label'=>'HIGH', 'icon'=>'notification_important'],
    'normal'    => ['bg'=>'bg-blue-500/10',  'text'=>'text-blue-500',  'label'=>'INFO', 'icon'=>'info'],
];
?>

<!-- Header Section -->
<section class="mb-6 flex flex-col md:flex-row md:items-end justify-between gap-4">
    <div>
        <div class="flex items-center gap-3 mb-2">
            <div class="w-10 h-10 rounded-2xl bg-primary/10 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined font-black">campaign</span>
            </div>
            <h1 data-theme-text class="text-3xl font-black tracking-tighter leading-none">Company Bulletin</h1>
        </div>
        <p data-theme-muted class="text-[10px] font-black uppercase tracking-[0.3em] ml-1">Internal Communications Ledger</p>
    </div>
    
    <?php if (auth_is_hrd()): ?>
        <button onclick="openModal('annModal')" class="group relative px-6 py-3.5 bg-primary text-white rounded-xl font-black text-xs uppercase tracking-widest flex items-center gap-3 overflow-hidden transition-all hover:scale-105 active:scale-95 shadow-xl shadow-primary/20">
            <span class="material-symbols-outlined text-lg">add_circle</span>
            <span>Post Announcement</span>
            <div class="absolute inset-0 bg-white/10 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
        </button>
    <?php endif; ?>
</section>

<!-- Announcements List -->
<div class="flex flex-col gap-4 mb-20">
    <?php if (empty($announcements)): ?>
        <div data-theme-card class="p-20 text-center bg-surface rounded-2xl border border-border flex flex-col items-center justify-center">
            <span class="material-symbols-outlined text-5xl text-on-surface-variant opacity-20 mb-4 block">drafts</span>
            <p data-theme-muted class="text-[10px] font-black uppercase opacity-40 italic tracking-widest">The bulletin board is currently empty.</p>
        </div>
    <?php else: ?>
        <?php foreach ($announcements as $ann): 
            $pc = $priority_config[$ann['priority']] ?? $priority_config['normal'];
        ?>
        <div data-theme-card class="bg-surface p-5 rounded-2xl border border-border shadow-sm flex items-start gap-5 transition-all hover:bg-surface2 group">
            
            <!-- Quick Icon Tier -->
            <div class="hidden sm:flex w-12 h-12 rounded-xl <?= $pc['bg'] ?> shrink-0 items-center justify-center <?= $pc['text'] ?> shadow-sm group-hover:scale-110 transition-transform">
                <span class="material-symbols-outlined font-black text-xl"><?= $pc['icon'] ?></span>
            </div>

            <!-- Content Row -->
            <div class="flex-grow min-w-0">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-2">
                    <div class="flex items-center gap-3">
                        <span class="sm:hidden w-6 h-6 rounded-lg <?= $pc['bg'] ?> flex items-center justify-center <?= $pc['text'] ?>">
                            <span class="material-symbols-outlined text-[10px] font-black"><?= $pc['icon'] ?></span>
                        </span>
                        <h2 data-theme-text class="text-base font-black text-on-surface tracking-tight truncate group-hover:text-primary transition-colors"><?= h($ann['title']) ?></h2>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="hidden lg:flex items-center gap-2 pr-4 border-r border-border">
                             <div class="w-5 h-5 rounded-full bg-surface2 flex items-center justify-center text-[8px] font-black text-primary border border-border">
                                <?= avatar_initials($ann['author']) ?>
                             </div>
                             <p data-theme-muted class="text-[9px] font-black text-on-surface-variant uppercase opacity-40"><?= h($ann['author']) ?></p>
                        </div>
                        <span data-theme-muted class="text-[9px] font-black text-on-surface-variant opacity-30 uppercase tracking-widest"><?= format_date($ann['created_at']) ?></span>
                    </div>
                </div>

                <div data-theme-text class="text-[12px] leading-relaxed opacity-70 font-medium whitespace-pre-wrap mb-3 line-clamp-2 md:line-clamp-3 group-hover:block transition-all"><?= trim(h($ann['content'])) ?></div>

                <div class="flex items-center gap-2">
                    <span class="text-[8px] font-black <?= $pc['text'] ?> uppercase tracking-[0.2em] px-2 py-1 rounded-md bg-primary/5"><?= $pc['label'] ?></span>
                </div>
            </div>
            
            <div class="flex items-center self-center sm:self-start pt-2 opacity-0 group-hover:opacity-100 transition-opacity">
                <span data-theme-muted class="material-symbols-outlined text-base font-black">keyboard_arrow_right</span>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>
</div>

<!-- MODAL: Post Announcement (HRD Only) -->
<?php if (auth_is_hrd()): ?>
<div id="annModal" style="display:none;" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/80 backdrop-blur-md p-6" onclick="if(event.target===this)closeModal('annModal')">
    <div data-theme-card class="w-full max-w-md bg-surface rounded-2xl border border-border shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
        <!-- Header -->
        <div class="p-5 pb-1 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined font-black">post_add</span>
                </div>
                <div>
                    <h3 data-theme-text class="text-lg font-black tracking-tighter">New Bulletin</h3>
                    <p data-theme-muted class="text-[9px] font-black uppercase tracking-widest opacity-50">Publish Announcement</p>
                </div>
            </div>
            <button onclick="closeModal('annModal')" class="w-10 h-10 rounded-full flex items-center justify-center text-on-surface/40 hover:bg-surface2 transition-colors">
                <span class="material-symbols-outlined font-black">close</span>
            </button>
        </div>

        <form method="POST" action="?page=announcements&action=add" class="p-5 pt-3 space-y-4">
            <div class="space-y-1.5">
                <label data-theme-muted class="text-[9px] font-black uppercase tracking-widest opacity-60 block">Bulletin Title</label>
                <input name="title" type="text" required class="w-full px-4 py-3 rounded-xl bg-surface2 border border-border text-xs font-bold text-on-surface focus:border-primary focus:ring-0 transition-colors" style="background:var(--surface2)!important; color:var(--text-primary)!important;" placeholder="Enter headline here...">
            </div>
            
                <div class="grid grid-cols-2 gap-4">
                    <div class="space-y-1.5">
                        <label data-theme-muted class="text-[9px] font-black uppercase tracking-widest opacity-60 block">Priority Tier</label>
                        <select name="priority" class="w-full px-4 py-3 rounded-xl bg-surface2 border border-border text-xs font-bold text-on-surface focus:border-primary focus:ring-0 transition-colors" style="background:var(--surface2)!important; color:var(--text-primary)!important;">
                            <option value="normal">Normal / Info</option>
                            <option value="high">High Priority</option>
                            <option value="important">Critical / Urgent</option>
                        </select>
                    </div>
                    <div class="space-y-1.5">
                        <label data-theme-muted class="text-[9px] font-black uppercase tracking-widest opacity-60 block">Expiry Date</label>
                        <input name="expires_at" type="date" class="w-full px-4 py-3 rounded-xl bg-surface2 border border-border text-xs font-bold text-on-surface focus:border-primary focus:ring-0 transition-colors" style="background:var(--surface2)!important; color:var(--text-primary)!important;" min="<?= date('Y-m-d') ?>">
                        <p class="text-[7px] text-on-surface-variant opacity-40 mt-1 uppercase italic">Leave empty for NO expiry</p>
                    </div>
                </div>

                <div class="space-y-1.5">
                    <label data-theme-muted class="text-[9px] font-black uppercase tracking-widest opacity-60 block">Content Body</label>
                    <textarea name="content" rows="5" required class="w-full px-4 py-3 rounded-xl bg-surface2 border border-border text-xs font-medium text-on-surface focus:border-primary focus:ring-0 transition-colors resize-none" style="background:var(--surface2)!important; color:var(--text-primary)!important;" placeholder="Draft your message here..."></textarea>
                </div>
            </div>

            <div class="pt-2 flex flex-col gap-3">
                <button type="submit" class="w-full py-5 bg-primary text-white rounded-full text-xs font-black uppercase shadow-xl shadow-primary/20 active:scale-95 transition-all flex items-center justify-center gap-2">
                    <span class="material-symbols-outlined text-base">send</span>
                    Publish Now
                </button>
                <button type="button" onclick="closeModal('annModal')" data-theme-muted class="w-full py-2 text-[10px] font-black uppercase hover:bg-surface2 rounded-full transition-colors text-center opacity-40">Batal</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

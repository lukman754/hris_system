<?php
// pages/announcements.php – Enterprise Broadcast Hub (Unified Modern Style)
$announcements = get_announcements();
$priority_config = [
    'important' => ['bg'=>'bg-rose-500/10', 'text'=>'text-rose-500', 'label'=>'CRITICAL', 'icon'=>'priority_high'],
    'high'      => ['bg'=>'bg-amber-500/10', 'text'=>'text-amber-500', 'label'=>'HIGH', 'icon'=>'notification_important'],
    'normal'    => ['bg'=>'bg-blue-500/10',  'text'=>'text-blue-500',  'label'=>'INFO', 'icon'=>'info'],
];

$critical_count = count(array_filter($announcements, fn($a) => $a['priority'] === 'important'));
?>

<div class="space-y-8 performance-page-container">
    
    <!-- ══ Header Section ══ -->
    <header class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-3xl font-bold">campaign</span>
                </div>
                <h1 data-theme-text class="text-4xl font-bold  leading-none">Broadcast Hub</h1>
            </div>
            <p data-theme-muted class="text-[10px] font-bold   ml-1 opacity-50">Enterprise Communications & Alerts</p>
        </div>
        
        <?php if (auth_is_hrd()): ?>
        <button onclick="openModal('annModal')" class="px-6 py-3.5 bg-primary text-white rounded-lg font-bold text-xs   flex items-center gap-2 shadow-xl shadow-primary/20 active:scale-95 transition-all">
            <span class="material-symbols-outlined text-lg">post_add</span>
            <span>Publish Memo</span>
        </button>
        <?php endif; ?>
    </header>

    <!-- ══ Communication Stats ══ -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div data-theme-card class="bg-surface p-5 rounded-lg border border-border flex items-center gap-5">
            <div class="w-12 h-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl font-bold">mark_as_unread</span>
            </div>
            <div>
                <div data-theme-muted class="text-[9px] font-bold   opacity-40">Active Feed</div>
                <div data-theme-text class="text-xl font-bold"><?= count($announcements) ?> Posts</div>
            </div>
        </div>
        <div data-theme-card class="bg-surface p-5 rounded-lg border border-border flex items-center gap-5">
            <div class="w-12 h-12 rounded-lg bg-rose-500/10 text-rose-500 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl font-bold">warning</span>
            </div>
            <div>
                <div data-theme-muted class="text-[9px] font-bold   opacity-40">Critical Alerts</div>
                <div data-theme-text class="text-xl font-bold"><?= $critical_count ?> Active</div>
            </div>
        </div>
        <div data-theme-card class="bg-surface p-5 rounded-lg border border-border flex items-center gap-5 lg:col-span-1">
             <div class="w-12 h-12 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl font-bold">visibility</span>
            </div>
            <div>
                <div data-theme-muted class="text-[9px] font-bold   opacity-40">Staff Reach</div>
                <div data-theme-text class="text-xl font-bold">100%</div>
            </div>
        </div>
        <div data-theme-card class="bg-surface p-5 rounded-lg border border-border flex items-center gap-5">
            <div class="w-12 h-12 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl font-bold">verified_user</span>
            </div>
            <div>
                <div data-theme-muted class="text-[9px] font-bold   opacity-40">Status</div>
                <div data-theme-text class="text-xl font-bold">Official</div>
            </div>
        </div>
    </div>

    <!-- ══ Broadcast Feed ══ -->
    <div class="space-y-4 pb-20">
        <?php if (empty($announcements)): ?>
            <div data-theme-card class="p-20 text-center bg-surface rounded-lg border border-border border-dashed flex flex-col items-center justify-center">
                <span class="material-symbols-outlined text-6xl text-on-surface-variant opacity-10 mb-6">dynamic_feed</span>
                <p data-theme-muted class="text-xs font-bold  opacity-40 ">Feed is currently dormant</p>
            </div>
        <?php else: ?>
            <?php foreach ($announcements as $ann): 
                $pc = $priority_config[$ann['priority']] ?? $priority_config['normal'];
                $is_edited = !empty($ann['updated_at']) && $ann['updated_at'] !== $ann['created_at'];
            ?>
            <div data-theme-card class="group bg-surface p-6 sm:p-8 rounded-lg border border-border transition-all duration-300 hover:shadow-2xl hover:-translate-y-1 cursor-pointer relative overflow-hidden flex flex-col sm:flex-row items-start gap-8" onclick="openDetailModal(<?= h(json_encode($ann)) ?>)">
                <div class="absolute top-0 right-0 w-32 h-32 bg-primary/[0.02] rounded-bl-[4rem]"></div>
                
                <!-- Icon Tier -->
                <div class="w-16 h-16 rounded-lg <?= $pc['bg'] ?> shrink-0 flex items-center justify-center <?= $pc['text'] ?> shadow-sm transition-transform group-hover:scale-110 duration-500">
                    <span class="material-symbols-outlined font-bold text-2xl"><?= $pc['icon'] ?></span>
                </div>

                <!-- Content Area -->
                <div class="grow min-w-0">
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-4">
                        <div>
                            <span class="text-[8px] font-bold <?= $pc['text'] ?>   mb-2 block"><?= $pc['label'] ?> BROADCST</span>
                            <h2 data-theme-text class="text-xl font-bold  text-on-surface line-clamp-1 group-hover:text-primary transition-colors"><?= h($ann['title']) ?></h2>
                        </div>
                        <div class="flex items-center gap-4 bg-surface2 px-4 py-2 rounded-lg border-none shrink-0 shadow-sm">
                             <div class="text-right">
                                <p data-theme-text class="text-[10px] font-bold  leading-none mb-1"><?= h($ann['author']) ?></p>
                                <p data-theme-muted class="text-[8px] font-bold opacity-30  leading-none"><?= date('d M Y', strtotime($ann['created_at'])) ?> · <?= date('H:i', strtotime($ann['created_at'])) ?></p>
                             </div>
                             <div class="w-8 h-8 rounded-full bg-primary text-white flex items-center justify-center text-[10px] font-bold shadow-lg shadow-primary/20">
                                <?= avatar_initials($ann['author']) ?>
                             </div>
                        </div>
                    </div>

                    <p data-theme-text class="text-sm font-medium leading-relaxed opacity-60 line-clamp-2 mb-6"><?= trim(h($ann['content'])) ?></p>

                    <div class="flex items-center justify-between pt-4">
                        <div class="flex items-center gap-3">
                            <span class="text-[9px] font-bold text-primary  ">View Details</span>
                            <?php if($is_edited): ?>
                                <span class="w-1 h-1 rounded-full bg-border"></span>
                                <span data-theme-muted class="text-[7px] font-bold  opacity-20 italic">Revised Document</span>
                            <?php endif; ?>
                        </div>
                        
                        <?php if (auth_is_hrd()): ?>
                        <div class="flex items-center gap-2 opacity-0 group-hover:opacity-100 transition-all" onclick="event.stopPropagation()">
                            <button onclick="openEditModal(<?= h(json_encode($ann)) ?>)" class="w-10 h-10 rounded-lg bg-surface2 text-on-surface-variant flex items-center justify-center hover:bg-primary hover:text-white transition-all">
                                <span class="material-symbols-outlined text-lg">edit</span>
                            </button>
                            <button onclick="confirmDeleteAnn(<?= $ann['id'] ?>)" class="w-10 h-10 rounded-lg bg-rose-500/5 text-rose-500 flex items-center justify-center hover:bg-rose-500 hover:text-white transition-all">
                                <span class="material-symbols-outlined text-lg">delete</span>
                            </button>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<!-- Detailed Broadcast View -->
<div id="detailModal" style="display:none;" class="fixed inset-0 z-[210] flex items-center justify-center bg-black/98 backdrop-blur-2xl p-4 lg:p-10" onclick="if(event.target===this)closeModal('detailModal')">
    <div data-theme-card class="w-full max-w-2xl bg-surface rounded-lg border border-border shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-500">
        <div class="p-10 pb-0 flex justify-between items-start">
            <span id="detailLabel" class="px-4 py-1.5 rounded-full text-[9px] font-bold   mb-4">NOTIFICATION</span>
            <button onclick="closeModal('detailModal')" class="w-12 h-12 rounded-full flex items-center justify-center text-on-surface/20 hover:text-on-surface hover:bg-surface2 transition-all"><span class="material-symbols-outlined font-bold">close</span></button>
        </div>
        <div class="px-10 pb-12">
            <h2 id="detailTitle" data-theme-text class="text-3xl font-bold mb-6 leading-tight ">Broadcast Headline</h2>
            <div class="flex flex-wrap items-center gap-6 mb-10 py-6">
                <div class="flex items-center gap-3">
                    <div id="detailAuthorInitials" class="w-10 h-10 rounded-lg bg-primary text-white flex items-center justify-center text-xs font-bold shadow-xl shadow-primary/20">??</div>
                    <div>
                        <p id="detailAuthor" data-theme-text class="text-[11px] font-bold  leading-none mb-1">Publisher</p>
                        <p data-theme-muted class="text-[9px] font-bold  opacity-30 leading-none">Official Author</p>
                    </div>
                </div>
                <div class="h-10 w-[1px] bg-border/50"></div>
                <div>
                    <p id="detailDateTime" data-theme-text class="text-[11px] font-bold  leading-none mb-1">Timestamp</p>
                    <p data-theme-muted class="text-[9px] font-bold  opacity-30 leading-none">Distribution Time</p>
                </div>
            </div>
            <div id="detailContent" data-theme-text class="text-base font-medium leading-relaxed opacity-70 whitespace-pre-wrap max-h-[40vh] overflow-y-auto pr-4 scroll-smooth">Broadcast Content Body</div>
            
            <div id="detailUpdated" class="mt-8 pt-6 border-none hidden">
                <p data-theme-muted class="text-[9px] font-bold italic opacity-20  ">Document Revision History Available</p>
            </div>
        </div>
    </div>
</div>

<!-- Edit Announcement Modal -->
<div id="editAnnModal" style="display:none;" class="fixed inset-0 z-[200] flex items-center justify-center bg-black/95 backdrop-blur-md p-6" onclick="if(event.target===this)closeModal('editAnnModal')">
    <div data-theme-card class="w-full max-w-md bg-surface rounded-lg border border-border shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
        <div class="p-8 pb-1 flex justify-between items-center">
            <h3 data-theme-text class="text-xl font-bold ">Modify Broadcast</h3>
            <button onclick="closeModal('editAnnModal')" class="w-10 h-10 rounded-full flex items-center justify-center text-on-surface/40 hover:bg-surface2 transition-colors"><span class="material-symbols-outlined font-bold">close</span></button>
        </div>
        <form method="POST" action="?page=announcements&action=edit" class="p-8 pt-4 space-y-6">
            <input type="hidden" name="id" id="edit_ann_id">
            <div class="space-y-2">
                <label data-theme-muted class="text-[9px] font-bold  opacity-40 ml-1">Headline</label>
                <input name="title" id="edit_ann_title" type="text" required class="w-full px-5 py-4 bg-surface2 rounded-lg text-xs font-bold border-border outline-none focus:ring-4 focus:ring-primary/10 transition-all">
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label data-theme-muted class="text-[9px] font-bold  opacity-40 ml-1">Priority</label>
                    <select name="priority" id="edit_ann_priority" class="w-full px-5 py-4 bg-surface2 rounded-lg text-[10px] font-bold   border-border outline-none">
                        <option value="normal">Normal</option><option value="high">High</option><option value="important">Critical</option>
                    </select>
                </div>
                <div class="space-y-2">
                    <label data-theme-muted class="text-[9px] font-bold  opacity-40 ml-1">Expiry</label>
                    <input name="expires_at" id="edit_ann_expires" type="date" class="w-full px-5 py-4 bg-surface2 rounded-lg text-xs font-bold border-border outline-none">
                </div>
            </div>
            <div class="space-y-2">
                <label data-theme-muted class="text-[9px] font-bold  opacity-40 ml-1">Message Body</label>
                <textarea name="content" id="edit_ann_content" rows="6" required class="w-full p-5 bg-surface2 rounded-lg text-xs font-medium leading-relaxed border-border outline-none focus:ring-4 focus:ring-primary/10 resize-none"></textarea>
            </div>
            <button type="submit" class="w-full py-5 bg-primary text-white rounded-full text-xs font-bold   shadow-2xl shadow-primary/20 active:scale-95 transition-all">Save Changes</button>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div id="deleteAnnModal" style="display:none;" class="fixed inset-0 z-[210] flex items-center justify-center bg-black/90 backdrop-blur-sm p-4" onclick="if(event.target===this)closeModal('deleteAnnModal')">
    <div data-theme-card class="w-full max-w-sm bg-surface rounded-lg border border-border shadow-2xl overflow-hidden p-8 text-center">
        <div class="w-16 h-16 bg-rose-500/10 text-rose-500 rounded-lg flex items-center justify-center mx-auto mb-6">
            <span class="material-symbols-outlined text-3xl font-bold">delete_sweep</span>
        </div>
        <h3 data-theme-text class="text-xl font-bold  mb-2">Retract Broadcast?</h3>
        <p data-theme-muted class="text-xs font-medium opacity-60 mb-8 leading-relaxed">This will permanently remove the memo from all employee feeds.</p>
        
        <div class="grid grid-cols-2 gap-3">
            <button onclick="closeModal('deleteAnnModal')" class="py-3.5 bg-surface2 text-on-surface font-bold text-[10px] rounded-lg border border-border">Keep Memo</button>
            <a id="del_ann_btn" href="#" class="py-3.5 bg-rose-500 text-white font-bold text-[10px] rounded-lg shadow-lg shadow-rose-500/20">Retract</a>
        </div>
    </div>
</div>

<script>
function openDetailModal(ann) {
    const pConfig = <?= json_encode($priority_config) ?>;
    const config = pConfig[ann.priority] || pConfig.normal;
    document.getElementById('detailLabel').innerText = config.label;
    document.getElementById('detailLabel').className = `px-4 py-1.5 rounded-full text-[9px] font-bold   mb-4 ${config.bg} ${config.text}`;
    document.getElementById('detailTitle').innerText = ann.title;
    document.getElementById('detailAuthor').innerText = ann.author;
    document.getElementById('detailAuthorInitials').innerText = ann.author.substring(0,2).toUpperCase();
    const date = new Date(ann.created_at);
    document.getElementById('detailDateTime').innerText = `${date.toLocaleDateString('id-ID',{day:'2-digit',month:'short',year:'numeric'})} · ${date.toLocaleTimeString('id-ID',{hour:'2-digit',minute:'2-digit'})} WIB`;
    document.getElementById('detailContent').innerText = ann.content;
    document.getElementById('detailModal').style.display = 'flex';
}

function openEditModal(ann) {
    document.getElementById('edit_ann_id').value = ann.id;
    document.getElementById('edit_ann_title').value = ann.title;
    document.getElementById('edit_ann_priority').value = ann.priority;
    document.getElementById('edit_ann_content').value = ann.content;
    if(ann.expires_at) {
        document.getElementById('edit_ann_expires').value = ann.expires_at.split(' ')[0];
    }
    openModal('editAnnModal');
}

function confirmDeleteAnn(id) {
    document.getElementById('del_ann_btn').href = `?page=announcements&action=delete&id=${id}`;
    openModal('deleteAnnModal');
}
</script>

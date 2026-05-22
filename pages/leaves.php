<?php
// pages/leaves.php – Leave & Absence Control (Unified Modern Style)
$leaves  = get_leaves();
$is_hrd  = auth_is_hrd();
$my_uid  = $user['id'];

if ($is_hrd):
    $pending  = array_filter($leaves, fn($l) => $l['approval_status'] === 'pending');
    $approved = array_filter($leaves, fn($l) => $l['approval_status'] === 'approved');
?>

<div class="space-y-8 performance-page-container">
       <!-- ══ Header Section ══ -->
    <header class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl font-bold">event_note</span>
                </div>
                <h1 data-theme-text class="text-3xl font-bold  leading-none">Duty Control</h1>
            </div>
            <p data-theme-muted class="text-[10px] font-bold   ml-1 opacity-50">Operational Availability Registry</p>
        </div>
        
        <div class="flex items-center gap-3">
             <div class="flex items-center gap-2 px-4 py-2 bg-surface rounded-lg border border-border shadow-sm">
                 <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                 <span data-theme-text class="text-[9px] font-bold   opacity-60"><?= count($pending) ?> Pending Requests</span>
             </div>
        </div>
    </header>

    <!-- ══ Macro Stats ══ -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div data-theme-card class="bg-surface p-5 rounded-lg border border-border flex items-center gap-5">
            <div class="w-12 h-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl font-bold">pending_actions</span>
            </div>
            <div>
                <div data-theme-muted class="text-[9px] font-bold   opacity-40">Awaiting Auth</div>
                <div data-theme-text class="text-xl font-bold"><?= count($pending) ?></div>
            </div>
        </div>
        <div data-theme-card class="bg-surface p-5 rounded-lg border border-border flex items-center gap-5">
            <div class="w-12 h-12 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl font-bold">fact_check</span>
            </div>
            <div>
                <div data-theme-muted class="text-[9px] font-bold   opacity-40">Processed Today</div>
                <div data-theme-text class="text-xl font-bold">24</div>
            </div>
        </div>
        <div data-theme-card class="bg-surface p-5 rounded-lg border border-border flex items-center gap-5">
            <div class="w-12 h-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl font-bold">groups</span>
            </div>
            <div>
                <div data-theme-muted class="text-[9px] font-bold   opacity-40">Staff on Duty</div>
                <div data-theme-text class="text-xl font-bold">Active</div>
            </div>
        </div>
        <div data-theme-card class="bg-surface p-5 rounded-lg border border-border flex items-center gap-5">
            <div class="w-12 h-12 rounded-lg bg-rose-500/10 text-rose-500 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl font-bold">event_busy</span>
            </div>
            <div>
                <div data-theme-muted class="text-[9px] font-bold   opacity-40">Out of Office</div>
                <div data-theme-text class="text-xl font-bold"><?= count($leaves) - count($approved) ?> Mixed</div>
            </div>
        </div>
    </div>

    <!-- ══ Approval Queue Grid ══ -->
    <div id="leaveGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6 pb-20">
        <?php foreach ($leaves as $leave): 
             $st = $leave['approval_status'];
             $is_pending = $st === 'pending';
             $st_clr = $st==='approved'?'emerald':($st==='pending'?'amber':'rose');
        ?>
        <div class="leave-card" data-search-content="<?= strtolower($leave['employee_name'] . ' ' . $leave['department']) ?>">
            <div data-theme-card class="bg-surface p-6 rounded-lg border border-border group hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 relative flex flex-col h-full overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-<?= $st_clr ?>-500/[0.03] rounded-bl-[3rem] transition-transform group-hover:scale-125"></div>
                
                <div class="flex items-center gap-4 mb-5 pb-4">
                    <div class="w-12 h-12 bg-surface2 border border-border rounded-lg flex items-center justify-center text-primary font-bold text-lg">
                        <?= avatar_initials($leave['employee_name']) ?>
                    </div>
                    <div>
                        <h4 data-theme-text class="text-sm font-bold  leading-tight"><?= h($leave['employee_name']) ?></h4>
                        <p data-theme-muted class="text-[8px] font-bold   opacity-30 mt-1"><?= h($leave['department']) ?></p>
                    </div>
                </div>

                <div class="space-y-4 mb-6 grow mt-2">
                    <div class="flex items-center justify-between">
                         <span class="px-2 py-0.5 bg-<?= $st_clr ?>-500/10 text-<?= $st_clr ?>-500 rounded-lg text-[8px] font-bold  "><?= h($st) ?></span>
                         <span data-theme-muted class="text-[8px] font-bold   opacity-30"><?= leave_type_label($leave['leave_type']) ?></span>
                    </div>

                    <div class="p-4 bg-surface2 rounded-lg border-none">
                        <div class="flex items-center gap-3 mb-2">
                            <span class="material-symbols-outlined text-xs text-primary">calendar_month</span>
                            <span data-theme-text class="text-[10px] font-bold"><?= date('d M', strtotime($leave['leave_start'])) ?> – <?= date('d M', strtotime($leave['leave_end'])) ?></span>
                        </div>
                        <p data-theme-muted class="text-[10px] font-medium leading-relaxed italic opacity-70">"<?= h($leave['leave_reason']) ?>"</p>
                    </div>
                </div>

                <?php if ($is_pending): ?>
                <div class="grid grid-cols-2 gap-3 pt-2">
                    <button onclick="confirmApprove(<?= $leave['id'] ?>, '<?= h($leave['employee_name']) ?>')" class="w-full py-3 bg-emerald-500 text-white rounded-lg text-[9px] font-bold   shadow-lg shadow-emerald-500/20 flex items-center justify-center gap-2 active:scale-95 transition-all">
                        <span class="material-symbols-outlined text-sm">check_circle</span>
                        <span>Approve</span>
                    </button>
                    <button onclick="confirmReject(<?= $leave['id'] ?>, '<?= h($leave['employee_name']) ?>')" class="w-full py-3 bg-rose-500/5 text-rose-500 hover:bg-rose-500 hover:text-white border border-rose-500/10 rounded-lg text-[9px] font-bold   flex items-center justify-center gap-2 active:scale-95 transition-all">
                        <span class="material-symbols-outlined text-sm">cancel</span>
                        <span>Reject</span>
                    </button>
                </div>
                <?php else: ?>
                <div class="w-full py-3 bg-surface2 text-on-surface/30 rounded-lg text-[9px] font-bold   text-center border-none shadow-sm">
                    Process Complete
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal Konfirmasi Approve -->
<div id="approveModal" style="display:none;" class="fixed inset-0 z-[110] flex items-center justify-center bg-black/90 backdrop-blur-sm p-4" onclick="if(event.target===this)closeModal('approveModal')">
    <div data-theme-card class="w-full max-w-sm bg-surface rounded-lg border border-border shadow-2xl overflow-hidden p-8 text-center">
        <div class="w-16 h-16 bg-emerald-500/10 text-emerald-500 rounded-lg flex items-center justify-center mx-auto mb-6">
            <span class="material-symbols-outlined text-3xl font-bold">verified_user</span>
        </div>
        <h3 data-theme-text class="text-xl font-bold  mb-2">Authorize Absence?</h3>
        <p data-theme-muted class="text-xs font-medium opacity-60 mb-8 leading-relaxed">Approving this request for <span id="app_name" class="font-bold text-on-surface"></span> will formalize their absence in the roster.</p>
        
        <div class="grid grid-cols-2 gap-3">
            <button onclick="closeModal('approveModal')" class="py-3.5 bg-surface2 text-on-surface font-bold text-[10px] rounded-lg border border-border">Back</button>
            <a id="app_confirm_btn" href="#" class="py-3.5 bg-emerald-500 text-white font-bold text-[10px] rounded-lg shadow-lg shadow-emerald-500/20">Authorize</a>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Reject -->
<div id="rejectModal" style="display:none;" class="fixed inset-0 z-[110] flex items-center justify-center bg-black/90 backdrop-blur-sm p-4" onclick="if(event.target===this)closeModal('rejectModal')">
    <div data-theme-card class="w-full max-w-sm bg-surface rounded-lg border border-border shadow-2xl overflow-hidden p-8 text-center">
        <div class="w-16 h-16 bg-rose-500/10 text-rose-500 rounded-lg flex items-center justify-center mx-auto mb-6">
            <span class="material-symbols-outlined text-3xl font-bold">block</span>
        </div>
        <h3 data-theme-text class="text-xl font-bold  mb-2">Decline Request?</h3>
        <p data-theme-muted class="text-xs font-medium opacity-60 mb-8 leading-relaxed">Declining this request for <span id="rej_name" class="font-bold text-on-surface"></span> will mark it as rejected. The employee will be notified of the refusal.</p>
        
        <div class="grid grid-cols-2 gap-3">
            <button onclick="closeModal('rejectModal')" class="py-3.5 bg-surface2 text-on-surface font-bold text-[10px] rounded-lg border border-border">Cancel</button>
            <a id="rej_confirm_btn" href="#" class="py-3.5 bg-rose-500 text-white font-bold text-[10px] rounded-lg shadow-lg shadow-rose-500/20">Decline</a>
        </div>
    </div>
</div>

<?php else: 
    // Employee View: Redirect to simplified modern list or self-managed roster
    $my_leaves = array_filter($leaves, fn($l) => $l['user_id'] === $my_uid);
?>
<div class="space-y-8 performance-page-container">
    <header class="flex items-end justify-between gap-6">
        <div>
            <h1 data-theme-text class="text-4xl font-bold  leading-none mb-2">My Absences</h1>
            <p data-theme-muted class="text-[10px] font-bold   ml-1 opacity-50">Manage your time off</p>
        </div>
        <button onclick="openModal('leaveModal')" class="px-6 py-3.5 bg-primary text-white rounded-lg font-bold text-xs   flex items-center gap-2 shadow-xl shadow-primary/20 active:scale-95 transition-all">
            <span class="material-symbols-outlined text-lg">add_circle</span>
            <span>Request Leave</span>
        </button>
    </header>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">
        <?php foreach ($my_leaves as $leave): 
            $st = $leave['approval_status'];
            $st_clr = $st==='approved'?'emerald':($st==='pending'?'amber':'rose');
        ?>
        <div data-theme-card class="bg-surface p-6 rounded-lg border border-border relative overflow-hidden flex flex-col">
            <div class="absolute top-0 right-0 w-20 h-20 bg-<?= $st_clr ?>-500/[0.03] rounded-bl-[2.5rem]"></div>
            
            <div class="flex items-center justify-between mb-4">
                 <span class="px-2 py-0.5 bg-<?= $st_clr ?>-500/10 text-<?= $st_clr ?>-500 rounded-lg text-[8px] font-bold  "><?= h($st) ?></span>
                 <span data-theme-muted class="text-[8px] font-bold opacity-30"><?= date('M Y', strtotime($leave['leave_start'])) ?></span>
            </div>
            
            <h4 data-theme-text class="text-lg font-bold  mb-4"><?= leave_type_label($leave['leave_type']) ?></h4>
            
            <div class="grow space-y-3 mb-6">
                <div class="flex items-center gap-2 text-xs font-bold opacity-60">
                    <span class="material-symbols-outlined text-sm">calendar_range</span>
                    <?= date('d M', strtotime($leave['leave_start'])) ?> – <?= date('d M', strtotime($leave['leave_end'])) ?>
                </div>
                <p data-theme-muted class="text-xs italic opacity-40">"<?= h($leave['leave_reason']) ?>"</p>
            </div>
            
            <div class="pt-4 text-right">
                <span class="text-[9px] font-bold   opacity-20"><?= $st === 'pending' ? 'Decision Awaited' : 'Archive' ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal remains mostly similar but themed for Neon -->
<div id="leaveModal" style="display:none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/95 backdrop-blur-md p-4" onclick="if(event.target===this)closeModal('leaveModal')">
    <div data-theme-card class="w-full max-w-lg bg-surface rounded-lg border border-border shadow-2xl overflow-hidden">
        <div class="p-8 pb-1 flex justify-between items-center">
            <h3 data-theme-text class="text-2xl font-bold ">Request Leave</h3>
            <button onclick="closeModal('leaveModal')" class="w-10 h-10 rounded-full flex items-center justify-center text-on-surface/40 hover:bg-surface2"><span class="material-symbols-outlined font-bold">close</span></button>
        </div>
        <form method="POST" action="?page=leaves&action=submit" class="p-8 pt-4 space-y-6">
            <div class="grid grid-cols-2 gap-4">
                <?php foreach (['sick'=>'Sakit','annual'=>'Cuti','personal'=>'Urgent','other'=>'Lainnya'] as $v=>$l): ?>
                <label class="cursor-pointer">
                    <input type="radio" name="leave_type" value="<?= $v ?>" class="sr-only peer" required <?= $v==='sick'?'checked':'' ?>>
                    <div class="p-4 bg-surface2 border-2 border-border peer-checked:border-primary peer-checked:bg-primary/5 rounded-lg transition-all">
                        <span data-theme-text class="text-[10px] font-bold   peer-checked:text-primary"><?= $l ?></span>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-2">
                    <label data-theme-muted class="text-[9px] font-bold  opacity-40">Start Date</label>
                    <input name="leave_start" type="date" class="w-full px-4 py-3 bg-surface2 rounded-lg text-xs font-bold border-border outline-none focus:ring-2 focus:ring-primary/20" required>
                </div>
                <div class="space-y-2">
                    <label data-theme-muted class="text-[9px] font-bold  opacity-40">End Date</label>
                    <input name="leave_end" type="date" class="w-full px-4 py-3 bg-surface2 rounded-lg text-xs font-bold border-border outline-none focus:ring-2 focus:ring-primary/20" required>
                </div>
            </div>
            <textarea name="leave_reason" rows="3" class="w-full p-4 bg-surface2 rounded-lg text-xs font-bold border-border outline-none focus:ring-2 focus:ring-primary/20 resize-none" placeholder="Reason for leave..." required></textarea>
            <button type="submit" class="w-full py-5 bg-primary text-white rounded-full text-xs font-bold   shadow-2xl shadow-primary/20 active:scale-95 transition-all">Submit Request</button>
        </form>
    </div>
</div>
<?php endif; ?>

<script>
function filterLeaves(q) {
    q = q.toLowerCase();
    document.querySelectorAll('.leave-card').forEach(c => {
        c.style.display = c.getAttribute('data-search-content').includes(q) ? 'block' : 'none';
    });
}
function confirmApprove(id, name) {
    document.getElementById('app_name').innerText = name;
    document.getElementById('app_confirm_btn').href = `?page=leaves&action=approve&id=${id}`;
    openModal('approveModal');
}
function confirmReject(id, name) {
    document.getElementById('rej_name').innerText = name;
    document.getElementById('rej_confirm_btn').href = `?page=leaves&action=reject&id=${id}`;
    openModal('rejectModal');
}
</script>

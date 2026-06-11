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
    <header class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-3xl font-bold">event_note</span>
                </div>
                <h1 data-theme-text class="text-3xl font-bold leading-none">Duty Control</h1>
            </div>
            <p data-theme-muted class="text-[10px] font-bold ml-1 opacity-50">Operational Availability Registry</p>
        </div>
        
        <div class="flex items-center gap-3">
             <div class="bg-surface px-4 py-2.5 rounded-lg flex items-center gap-2" style="border:1px solid var(--border);">
                  <span class="w-2 h-2 rounded-full bg-amber-500 animate-pulse"></span>
                  <span class="text-[11px] font-bold text-on-surface"><?= count($pending) ?> Pending Requests</span>
             </div>
        </div>
    </header>

    <!-- ══ Stats Overview ══ -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- 1. Awaiting Auth (Amber) -->
        <div class="card" style="background: linear-gradient(135deg, #F59E0B, #D97706); border: none; color: white; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; min-h: 110px;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
                <span style="font-size: 11px; font-weight: 600; color: rgba(255, 255, 255, 0.8); text-transform: uppercase; letter-spacing: 0.02em;">Awaiting Auth</span>
                <div style="width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.15); color: #ffffff; flex-shrink: 0;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">pending_actions</span>
                </div>
            </div>
            <div style="display: flex; align-items: baseline; gap: 4px; margin-top: auto;">
                <span style="font-size: 20px; font-weight: 700; color: #ffffff; line-height: 1;"><?= count($pending) ?></span>
            </div>
            <p style="font-size: 11px; color: rgba(255, 255, 255, 0.7); margin: 4px 0 0; font-style: italic;">Pending decisions</p>
        </div>
        
        <!-- 2. Processed Today (Green) -->
        <div class="card" style="background: linear-gradient(135deg, #10B981, #059669); border: none; color: white; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; min-h: 110px;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
                <span style="font-size: 11px; font-weight: 600; color: rgba(255, 255, 255, 0.8); text-transform: uppercase; letter-spacing: 0.02em;">Processed Today</span>
                <div style="width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.15); color: #ffffff; flex-shrink: 0;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">fact_check</span>
                </div>
            </div>
            <div style="display: flex; align-items: baseline; gap: 4px; margin-top: auto;">
                <span style="font-size: 20px; font-weight: 700; color: #ffffff; line-height: 1;"><?= count($approved) ?></span>
            </div>
            <p style="font-size: 11px; color: rgba(255, 255, 255, 0.7); margin: 4px 0 0; font-style: italic;">Approved absences</p>
        </div>
        
        <!-- 3. Total Requests (Blue) -->
        <div class="card" style="background: linear-gradient(135deg, #2563EB, #1D4ED8); border: none; color: white; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; min-h: 110px;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
                <span style="font-size: 11px; font-weight: 600; color: rgba(255, 255, 255, 0.8); text-transform: uppercase; letter-spacing: 0.02em;">Total Requests</span>
                <div style="width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.15); color: #ffffff; flex-shrink: 0;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">groups</span>
                </div>
            </div>
            <div style="display: flex; align-items: baseline; gap: 4px; margin-top: auto;">
                <span style="font-size: 20px; font-weight: 700; color: #ffffff; line-height: 1;"><?= count($leaves) ?></span>
            </div>
            <p style="font-size: 11px; color: rgba(255, 255, 255, 0.7); margin: 4px 0 0; font-style: italic;">All leave entries</p>
        </div>
        
        <!-- 4. Declined (Red) -->
        <div class="card" style="background: linear-gradient(135deg, #EF4444, #DC2626); border: none; color: white; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; min-h: 110px;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
                <span style="font-size: 11px; font-weight: 600; color: rgba(255, 255, 255, 0.8); text-transform: uppercase; letter-spacing: 0.02em;">Declined</span>
                <div style="width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.15); color: #ffffff; flex-shrink: 0;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">event_busy</span>
                </div>
            </div>
            <div style="display: flex; align-items: baseline; gap: 4px; margin-top: auto;">
                <span style="font-size: 20px; font-weight: 700; color: #ffffff; line-height: 1;"><?= count(array_filter($leaves, fn($l) => $l['approval_status'] === 'rejected')) ?></span>
            </div>
            <p style="font-size: 11px; color: rgba(255, 255, 255, 0.7); margin: 4px 0 0; font-style: italic;">Rejected requests</p>
        </div>
    </div>

    <!-- ══ Approval Queue Grid ══ -->
    <div id="leaveGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6 pb-20">
        <?php foreach ($leaves as $leave): 
             $st = $leave['approval_status'];
             $is_pending = $st === 'pending';
             $st_clr = $st==='approved'?'emerald':($st==='pending'?'amber':'rose');
             $badge_class = $st==='approved'?'badge-green':($st==='pending'?'badge-yellow':'badge-red');
        ?>
        <div class="leave-card" data-search-content="<?= strtolower($leave['employee_name'] . ' ' . $leave['department']) ?>">
            <div data-theme-card class="bg-surface p-6 rounded-lg border border-border group hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 relative flex flex-col h-full overflow-hidden">
                <div class="absolute top-0 right-0 w-24 h-24 bg-<?= $st_clr ?>-500/[0.03] rounded-bl-[3rem] transition-transform group-hover:scale-125"></div>
                
                <div class="flex items-center gap-4 mb-5 pb-4 border-b border-border">
                    <div class="w-12 h-12 bg-surface2 border border-border rounded-lg flex items-center justify-center text-primary font-bold text-lg shrink-0">
                        <?= avatar_initials($leave['employee_name']) ?>
                    </div>
                    <div>
                        <h4 data-theme-text class="text-sm font-bold leading-tight"><?= h($leave['employee_name']) ?></h4>
                        <p data-theme-muted class="text-[10px] font-bold opacity-40 mt-1"><?= h($leave['department']) ?></p>
                    </div>
                </div>

                <div class="space-y-4 mb-6 grow mt-2">
                    <div class="flex items-center justify-between">
                         <span class="badge <?= $badge_class ?>"><?= h(strtoupper($st)) ?></span>
                         <span data-theme-muted class="text-[10px] font-bold opacity-40"><?= leave_type_label($leave['leave_type']) ?></span>
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
                    <button onclick="confirmApprove(<?= $leave['id'] ?>, '<?= h($leave['employee_name']) ?>')" class="w-full py-2.5 bg-surface-variant text-emerald-600 dark:text-emerald-400 hover:bg-emerald-500 hover:text-white border border-border rounded-lg text-[10px] font-bold flex items-center justify-center gap-2 active:scale-95 transition-all shadow-sm">
                        <span class="material-symbols-outlined text-sm">check_circle</span>
                        <span>Approve</span>
                    </button>
                    <button onclick="confirmReject(<?= $leave['id'] ?>, '<?= h($leave['employee_name']) ?>')" class="w-full py-2.5 bg-surface-variant text-rose-500 hover:bg-rose-500 hover:text-white border border-border rounded-lg text-[10px] font-bold flex items-center justify-center gap-2 active:scale-95 transition-all shadow-sm">
                        <span class="material-symbols-outlined text-sm">cancel</span>
                        <span>Reject</span>
                    </button>
                </div>
                <?php else: ?>
                <div data-theme-surface2 class="w-full py-3 bg-surface2 text-[9px] font-bold text-center rounded-lg border-none shadow-sm" style="color: var(--text-muted); opacity: 0.5;">
                    Process Complete
                </div>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal Konfirmasi Approve -->
<div id="approveModal" style="display:none;" class="fixed inset-0 z-[110] flex items-center justify-center bg-black/50 p-4" onclick="if(event.target===this)closeModal('approveModal')">
    <div class="w-full max-w-sm bg-[var(--surface)] rounded-lg p-6 text-center shadow-2xl" style="border:1px solid var(--border);">
        <div class="w-12 h-12 bg-emerald-500/10 text-emerald-500 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="material-symbols-outlined text-[24px]">verified_user</span>
        </div>
        <h3 class="text-[16px] font-bold text-[var(--text-primary)] mb-2">Authorize Absence?</h3>
        <p class="text-[12px] text-[var(--text-muted)] mb-6">Approving this request for <span id="app_name" class="font-bold text-[var(--text-primary)]"></span> will formalize their absence in the roster.</p>
        
        <div class="grid grid-cols-2 gap-3">
            <button onclick="closeModal('approveModal')" class="py-2 bg-[var(--surface2)] text-[var(--text-primary)] border rounded text-[12px] font-bold hover:bg-[var(--surface)] transition-colors" style="border-color:var(--border);">Back</button>
            <a id="app_confirm_btn" href="#" class="py-2 bg-emerald-500 text-white rounded text-[12px] font-bold hover:bg-emerald-600 transition-colors flex items-center justify-center">Authorize</a>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Reject -->
<div id="rejectModal" style="display:none;" class="fixed inset-0 z-[110] flex items-center justify-center bg-black/50 p-4" onclick="if(event.target===this)closeModal('rejectModal')">
    <div class="w-full max-w-sm bg-[var(--surface)] rounded-lg p-6 text-center shadow-2xl" style="border:1px solid var(--border);">
        <div class="w-12 h-12 bg-rose-500/10 text-rose-500 rounded-full flex items-center justify-center mx-auto mb-4">
            <span class="material-symbols-outlined text-[24px]">block</span>
        </div>
        <h3 class="text-[16px] font-bold text-[var(--text-primary)] mb-2">Decline Request?</h3>
        <p class="text-[12px] text-[var(--text-muted)] mb-6">Declining this request for <span id="rej_name" class="font-bold text-[var(--text-primary)]"></span> will mark it as rejected. The employee will be notified of the refusal.</p>
        
        <div class="grid grid-cols-2 gap-3">
            <button onclick="closeModal('rejectModal')" class="py-2 bg-[var(--surface2)] text-[var(--text-primary)] border rounded text-[12px] font-bold hover:bg-[var(--surface)] transition-colors" style="border-color:var(--border);">Cancel</button>
            <a id="rej_confirm_btn" href="#" class="py-2 bg-rose-500 text-white rounded text-[12px] font-bold hover:bg-rose-600 transition-colors flex items-center justify-center">Decline</a>
        </div>
    </div>
</div>

<?php else: 
    // Employee View: Redirect to simplified modern list or self-managed roster
    $my_leaves = array_filter($leaves, fn($l) => $l['user_id'] === $my_uid);
    $emp_pending  = count(array_filter($my_leaves, fn($l) => $l['approval_status'] === 'pending'));
    $emp_approved = count(array_filter($my_leaves, fn($l) => $l['approval_status'] === 'approved'));
    $emp_rejected = count(array_filter($my_leaves, fn($l) => $l['approval_status'] === 'rejected'));
    $emp_total    = count($my_leaves);
?>
<div class="space-y-8 performance-page-container">
    <!-- ══ Header Section ══ -->
    <header class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-3xl font-bold">event_note</span>
                </div>
                <h1 data-theme-text class="text-3xl font-bold leading-none">My Absences</h1>
            </div>
            <p data-theme-muted class="text-[10px] font-bold ml-1 opacity-50">Manage your time off and leave requests</p>
        </div>
        
        <button onclick="openModal('leaveModal')" class="px-6 py-3.5 bg-primary text-white rounded-lg font-bold text-xs flex items-center gap-2 shadow-xl shadow-primary/20 active:scale-95 transition-all">
            <span class="material-symbols-outlined text-lg">add_circle</span>
            <span>Request Leave</span>
        </button>
    </header>

    <!-- ══ Stats Overview ══ -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- 1. Total Requests (Blue) -->
        <div class="card" style="background: linear-gradient(135deg, #2563EB, #1D4ED8); border: none; color: white; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; min-h: 110px;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
                <span style="font-size: 11px; font-weight: 600; color: rgba(255, 255, 255, 0.8); text-transform: uppercase; letter-spacing: 0.02em;">Total Requests</span>
                <div style="width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.15); color: #ffffff; flex-shrink: 0;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">calendar_month</span>
                </div>
            </div>
            <div style="display: flex; align-items: baseline; gap: 4px; margin-top: auto;">
                <span style="font-size: 20px; font-weight: 700; color: #ffffff; line-height: 1;"><?= $emp_total ?></span>
            </div>
            <p style="font-size: 11px; color: rgba(255, 255, 255, 0.7); margin: 4px 0 0; font-style: italic;">All submitted requests</p>
        </div>

        <!-- 2. Approved (Green) -->
        <div class="card" style="background: linear-gradient(135deg, #10B981, #059669); border: none; color: white; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; min-h: 110px;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
                <span style="font-size: 11px; font-weight: 600; color: rgba(255, 255, 255, 0.8); text-transform: uppercase; letter-spacing: 0.02em;">Approved</span>
                <div style="width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.15); color: #ffffff; flex-shrink: 0;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">check_circle</span>
                </div>
            </div>
            <div style="display: flex; align-items: baseline; gap: 4px; margin-top: auto;">
                <span style="font-size: 20px; font-weight: 700; color: #ffffff; line-height: 1;"><?= $emp_approved ?></span>
            </div>
            <p style="font-size: 11px; color: rgba(255, 255, 255, 0.7); margin: 4px 0 0; font-style: italic;">Authorized absences</p>
        </div>

        <!-- 3. Pending (Amber) -->
        <div class="card" style="background: linear-gradient(135deg, #F59E0B, #D97706); border: none; color: white; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; min-h: 110px;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
                <span style="font-size: 11px; font-weight: 600; color: rgba(255, 255, 255, 0.8); text-transform: uppercase; letter-spacing: 0.02em;">Pending</span>
                <div style="width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.15); color: #ffffff; flex-shrink: 0;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">pending_actions</span>
                </div>
            </div>
            <div style="display: flex; align-items: baseline; gap: 4px; margin-top: auto;">
                <span style="font-size: 20px; font-weight: 700; color: #ffffff; line-height: 1;"><?= $emp_pending ?></span>
            </div>
            <p style="font-size: 11px; color: rgba(255, 255, 255, 0.7); margin: 4px 0 0; font-style: italic;">Awaiting authorization</p>
        </div>

        <!-- 4. Rejected (Red) -->
        <div class="card" style="background: linear-gradient(135deg, #EF4444, #DC2626); border: none; color: white; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; min-h: 110px;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
                <span style="font-size: 11px; font-weight: 600; color: rgba(255, 255, 255, 0.8); text-transform: uppercase; letter-spacing: 0.02em;">Rejected</span>
                <div style="width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.15); color: #ffffff; flex-shrink: 0;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">cancel</span>
                </div>
            </div>
            <div style="display: flex; align-items: baseline; gap: 4px; margin-top: auto;">
                <span style="font-size: 20px; font-weight: 700; color: #ffffff; line-height: 1;"><?= $emp_rejected ?></span>
            </div>
            <p style="font-size: 11px; color: rgba(255, 255, 255, 0.7); margin: 4px 0 0; font-style: italic;">Declined requests</p>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6">
        <?php foreach ($my_leaves as $leave): 
            $st = $leave['approval_status'];
            $st_clr = $st==='approved'?'emerald':($st==='pending'?'amber':'rose');
            $badge_class = $st==='approved'?'badge-green':($st==='pending'?'badge-yellow':'badge-red');
        ?>
        <div data-theme-card class="bg-surface p-6 rounded-lg border border-border hover:shadow-2xl hover:-translate-y-2 transition-all duration-500 relative flex flex-col h-full overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-<?= $st_clr ?>-500/[0.03] rounded-bl-[3rem]"></div>
            
            <div class="flex items-center justify-between mb-5 pb-4 border-b border-border">
                 <span class="badge <?= $badge_class ?>"><?= h(strtoupper($st)) ?></span>
                 <span data-theme-muted class="text-[10px] font-bold opacity-40"><?= date('M Y', strtotime($leave['leave_start'])) ?></span>
            </div>
            
            <div class="space-y-4 mb-6 grow">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 bg-primary/10 text-primary rounded-lg flex items-center justify-center font-bold text-sm shrink-0">
                        <span class="material-symbols-outlined text-base">
                            <?php 
                            switch ($leave['leave_type']) {
                                case 'sick': echo 'medical_services'; break;
                                case 'annual': echo 'beach_access'; break;
                                case 'personal': echo 'notification_important'; break;
                                default: echo 'more_horiz'; break;
                            }
                            ?>
                        </span>
                    </div>
                    <h4 data-theme-text class="text-sm font-bold leading-tight"><?= leave_type_label($leave['leave_type']) ?></h4>
                </div>
                
                <div class="p-4 bg-surface2 rounded-lg border-none">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="material-symbols-outlined text-xs text-primary">calendar_month</span>
                        <span data-theme-text class="text-[10px] font-bold"><?= date('d M', strtotime($leave['leave_start'])) ?> – <?= date('d M', strtotime($leave['leave_end'])) ?></span>
                    </div>
                    <p data-theme-muted class="text-[10px] font-medium leading-relaxed italic opacity-70">"<?= h($leave['leave_reason']) ?>"</p>
                </div>
            </div>
            
            <div class="pt-2 text-right border-t border-border">
                <span class="text-[9px] font-bold opacity-30"><?= $st === 'pending' ? 'Decision Awaited' : 'Archive' ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal Submit Leave -->
<div id="leaveModal" style="display:none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 p-4" onclick="if(event.target===this)closeModal('leaveModal')">
    <div class="w-full max-w-lg bg-[var(--surface)] rounded-lg flex flex-col shadow-2xl animate-fade-in" style="border:1px solid var(--border);">
        <div class="px-6 py-4 border-b bg-[var(--surface2)] flex justify-between items-center rounded-t-lg" style="border-color:var(--border);">
            <div>
                <h3 class="font-bold text-[16px] text-[var(--text-primary)]">Request Leave</h3>
                <p class="text-[11px] text-[var(--text-muted)]">Submit a new leave or absence request</p>
            </div>
            <button onclick="closeModal('leaveModal')" class="text-[var(--text-muted)] hover:text-[var(--primary)] transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>
        <form method="POST" action="?page=leaves&action=submit" class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <?php foreach (['sick'=>'Sakit','annual'=>'Cuti','personal'=>'Urgent','other'=>'Lainnya'] as $v=>$l): ?>
                <label class="cursor-pointer">
                    <input type="radio" name="leave_type" value="<?= $v ?>" class="sr-only peer" required <?= $v==='sick'?'checked':'' ?>>
                    <div class="p-4 bg-[var(--surface2)] border-2 border-transparent peer-checked:border-[var(--primary)] peer-checked:bg-[var(--primary)]/5 rounded-lg transition-all text-center">
                        <span class="text-[12px] font-bold text-[var(--text-primary)] peer-checked:text-[var(--primary)]"><?= $l ?></span>
                    </div>
                </label>
                <?php endforeach; ?>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div class="space-y-1.5">
                    <label class="text-[11px] font-bold text-[var(--text-muted)] block">Start Date</label>
                    <input name="leave_start" type="date" class="form-input" required>
                </div>
                <div class="space-y-1.5">
                    <label class="text-[11px] font-bold text-[var(--text-muted)] block">End Date</label>
                    <input name="leave_end" type="date" class="form-input" required>
                </div>
            </div>
            <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-[var(--text-muted)] block">Reason</label>
                <textarea name="leave_reason" rows="3" class="form-input resize-none" placeholder="Reason for leave..." required></textarea>
            </div>
            <button type="submit" class="w-full py-2.5 bg-[var(--primary)] text-white rounded-lg text-[13px] font-bold hover:opacity-90 active:scale-95 transition-all shadow-lg shadow-blue-500/20 mt-4">
                Submit Request
            </button>
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

<?php
// pages/employees.php – Modern Staff Management (HRD only)
$employees = get_employees();
$total_staff = count($employees);
$depts = array_count_values(array_column($employees, 'department'));
$avg_salary = $total_staff > 0 ? array_sum(array_column($employees, 'salary')) / $total_staff : 0;
?>

<div class="space-y-8 performance-page-container">
    
    <?php if (isset($_SESSION['reset_password_success'])): ?>
    <div class="p-5 rounded-xl bg-blue-50 border border-blue-200/60 dark:bg-blue-950/20 dark:border-blue-900/40 text-blue-700 dark:text-blue-300 text-xs font-semibold flex flex-col md:flex-row md:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <span class="material-symbols-outlined text-blue-500 text-lg">vpn_key</span>
            <div>
                <p class="font-bold">Password Reset Berhasil!</p>
                <p class="text-[10px] opacity-70">Password baru untuk <strong><?= h($_SESSION['reset_password_success']['name']) ?></strong> telah berhasil dibuat.</p>
            </div>
        </div>
        <div class="flex items-center gap-2 bg-white dark:bg-neutral-800 px-4 py-2 rounded-lg border border-blue-100 dark:border-neutral-700">
            <span class="text-[10px] font-bold text-slate-400 uppercase tracking-wider">Password Baru:</span>
            <code class="font-mono text-sm font-bold text-blue-600 dark:text-blue-400 select-all"><?= h($_SESSION['reset_password_success']['password']) ?></code>
        </div>
    </div>
    <?php unset($_SESSION['reset_password_success']); ?>
    <?php endif; ?>
    
    <!-- ══ Header Section ══ -->
    <header class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-3xl font-bold">group</span>
                </div>
                <h1 data-theme-text class="text-3xl font-bold leading-none">Staff Directory</h1>
            </div>
            <p data-theme-muted class="text-[10px] font-bold ml-1 opacity-50">Enterprise Workforce Management for Perkasa Abadi Logistik.</p>
        </div>
        
        <div class="flex items-center gap-3 self-stretch md:self-auto">
             <div class="bg-surface p-1.5 rounded-lg shadow-sm flex items-center gap-2 grow md:w-64" style="border:1px solid var(--border); height:38px;">
                <div class="pl-2 text-on-surface-variant opacity-60">
                    <span class="material-symbols-outlined text-lg">search</span>
                </div>
                <input type="text" id="empSearch" placeholder="Search staff..." oninput="filterStaff(this.value)" class="bg-transparent border-none text-xs font-semibold w-full focus:ring-0 placeholder:text-on-surface-variant placeholder:opacity-50 text-on-surface">
            </div>
            <button onclick="openModal('addEmpModal')" class="px-6 py-3.5 bg-primary text-white rounded-lg font-bold text-xs flex items-center gap-2 shadow-xl shadow-primary/20 active:scale-95 transition-all">
                <span class="material-symbols-outlined text-lg">person_add</span>
                <span>Onboard Staff</span>
            </button>
        </div>
    </header>

    <!-- ══ Stats Overview ══ -->
    <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap:16px; margin-bottom:24px;">
        <div class="card" style="padding:16px; display:flex; flex-direction:column; justify-content:space-between;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                <span style="font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.02em;">Total Workforce</span>
                <span class="material-symbols-outlined" style="color:var(--primary); font-size:20px;">groups</span>
            </div>
            <div style="display:flex; align-items:baseline; gap:4px;">
                <span style="font-size:20px; font-weight:700; color:var(--text-primary); line-height:1;"><?= $total_staff ?></span>
            </div>
        </div>
        
        <div class="card" style="padding:16px; display:flex; flex-direction:column; justify-content:space-between;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                <span style="font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.02em;">Departments</span>
                <span class="material-symbols-outlined" style="color:#10B981; font-size:20px;">account_tree</span>
            </div>
            <div style="display:flex; align-items:baseline; gap:4px;">
                <span style="font-size:20px; font-weight:700; color:var(--text-primary); line-height:1;"><?= count($depts) ?></span>
            </div>
        </div>
        
        <div class="card" style="padding:16px; display:flex; flex-direction:column; justify-content:space-between;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                <span style="font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.02em;">Avg Salary Base</span>
                <span class="material-symbols-outlined" style="color:#F59E0B; font-size:20px;">payments</span>
            </div>
            <div style="display:flex; align-items:baseline; gap:4px;">
                <span style="font-size:18px; font-weight:700; color:var(--text-primary); line-height:1;">Rp <?= number_format($avg_salary/1000000, 1) ?>M</span>
            </div>
        </div>
        
        <div class="card" style="padding:16px; display:flex; flex-direction:column; justify-content:space-between;">
            <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                <span style="font-size:11px; font-weight:600; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.02em;">Active Status</span>
                <span class="material-symbols-outlined" style="color:#8B5CF6; font-size:20px;">verified</span>
            </div>
            <div style="display:flex; align-items:baseline; gap:4px;">
                <span style="font-size:20px; font-weight:700; color:var(--text-primary); line-height:1;">100%</span>
            </div>
        </div>
    </div>

    <!-- ══ Staff Directory Grid ══ -->
    <div id="staffGrid" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 2xl:grid-cols-5 gap-4 pb-20">
        <?php foreach ($employees as $emp): ?>
        <div class="staff-card card flex flex-col" style="padding:0; overflow:hidden;" data-search-content="<?= strtolower($emp['name'] . ' ' . $emp['position'] . ' ' . $emp['department'] . ' ' . ($emp['phone_number'] ?? '')) ?>">
            <div style="padding:20px; display:flex; flex-direction:column; align-items:center; text-center; border-bottom:1px solid var(--border); position:relative;">
                <div class="w-16 h-16 rounded-full overflow-hidden mb-3 border-2" style="border-color:var(--surface2);">
                    <?php if (!empty($emp['photo_profile'])): ?>
                        <img src="<?= h($emp['photo_profile']) ?>" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center font-bold text-lg" style="background:var(--surface2); color:var(--primary);">
                            <?= avatar_initials($emp['name']) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <h3 style="font-size:14px; font-weight:700; color:var(--text-primary); line-height:1.2; margin-bottom:4px;"><?= h($emp['name']) ?></h3>
                <p style="font-size:11px; color:var(--text-muted); font-weight:500;"><?= h($emp['position']) ?></p>
            </div>

            <div style="padding:16px; background:var(--surface2); flex-grow:1;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <span style="font-size:11px; color:var(--text-muted); font-weight:500;">Department</span>
                    <span style="font-size:11px; font-weight:600; color:var(--text-primary);"><?= h($emp['department']) ?></span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
                    <span style="font-size:11px; color:var(--text-muted); font-weight:500;">No. HP</span>
                    <span style="font-size:11px; font-weight:600; color:var(--text-primary);"><?= h($emp['phone_number'] ?: '-') ?></span>
                </div>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                    <span style="font-size:11px; color:var(--text-muted); font-weight:500;">Base Salary</span>
                    <span style="font-size:11px; font-weight:600; color:var(--text-primary);"><?= format_rupiah($emp['salary'] ?? 0) ?></span>
                </div>
            </div>

            <div style="padding:12px 16px; border-top:1px solid var(--border); display:flex; gap:8px;">
                <button onclick='openEditModal(<?= json_encode($emp) ?>)' class="flex-1 py-1.5 flex items-center justify-center gap-1 text-[11px] font-bold rounded bg-[var(--surface2)] text-blue-500 hover:bg-blue-500 hover:text-white transition-colors border" style="border-color:var(--border);">
                    <span class="material-symbols-outlined text-[14px]">edit</span> Update
                </button>
                <button onclick="confirmResetPassword('<?= h($emp['id']) ?>', '<?= h($emp['name']) ?>')" class="py-1.5 px-2 flex items-center justify-center text-[11px] font-bold rounded bg-[var(--surface2)] text-amber-500 hover:bg-amber-500 hover:text-white transition-colors border" style="border-color:var(--border);" title="Reset Password">
                    <span class="material-symbols-outlined text-[14px]">lock_reset</span>
                </button>
                <button onclick="confirmDelete('<?= h($emp['id']) ?>', '<?= h($emp['name']) ?>')" class="py-1.5 px-2 flex items-center justify-center text-[11px] font-bold rounded bg-[var(--surface2)] text-red-500 hover:bg-red-500 hover:text-white transition-colors border" style="border-color:var(--border);" title="Offboard">
                    <span class="material-symbols-outlined text-[14px]">person_remove</span>
                </button>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal Tambah Karyawan -->
<div id="addEmpModal" style="display:none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-md p-4" onclick="if(event.target===this)closeModal('addEmpModal')">
    <div data-theme-card class="w-full max-w-2xl bg-surface rounded-lg border border-border shadow-2xl overflow-hidden">
        <div class="p-6 pb-1 flex justify-between items-center">
            <div>
                <h3 data-theme-text class="text-xl font-bold ">Onboard Staff</h3>
                <p data-theme-muted class="text-[9px] font-bold   opacity-40"> Registry Division</p>
            </div>
            <button onclick="closeModal('addEmpModal')" class="w-10 h-10 rounded-lg flex items-center justify-center text-on-surface/40 hover:bg-surface2 transition-colors">
                <span class="material-symbols-outlined font-bold">close</span>
            </button>
        </div>

        <form method="POST" action="?page=employees&action=add" class="p-6 pt-4 space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-1.5 text-left">
                    <label data-theme-muted class="text-[9px] font-bold  opacity-40 ml-1">Staff Name</label>
                    <input name="name" type="text" class="w-full px-4 py-3 bg-surface2 border-border border rounded-lg text-xs font-bold outline-none" placeholder="Full name" required>
                </div>
                <div class="space-y-1.5 text-left">
                    <label data-theme-muted class="text-[9px] font-bold  opacity-40 ml-1">Work Email</label>
                    <input name="email" type="email" class="w-full px-4 py-3 bg-surface2 border-border border rounded-lg text-xs font-bold outline-none" placeholder="email@company.com" required>
                </div>
                <div class="space-y-1.5 text-left">
                    <label data-theme-muted class="text-[9px] font-bold  opacity-40 ml-1">No. HP / Telepon</label>
                    <input name="phone_number" type="text" class="w-full px-4 py-3 bg-surface2 border-border border rounded-lg text-xs font-bold outline-none" placeholder="e.g. 081234567890">
                </div>
                <div class="space-y-1.5 text-left">
                    <label data-theme-muted class="text-[9px] font-bold  opacity-40 ml-1">Security Access</label>
                    <input name="password" type="password" class="w-full px-4 py-3 bg-surface2 border-border border rounded-lg text-xs font-bold outline-none" placeholder="Set password" required>
                </div>
                <div class="space-y-1.5 text-left">
                    <label data-theme-muted class="text-[9px] font-bold  opacity-40 ml-1">Official Position</label>
                    <input name="position" type="text" class="w-full px-4 py-3 bg-surface2 border-border border rounded-lg text-xs font-bold outline-none" placeholder="e.g. Senior Developer" required>
                </div>
                <div class="space-y-1.5 text-left">
                    <label data-theme-muted class="text-[9px] font-bold  opacity-40 ml-1">Assigned Department</label>
                    <select name="department" class="w-full px-4 py-3 bg-surface2 border-border border rounded-lg text-xs font-bold outline-none" required>
                        <?php foreach (['IT','Marketing','Finance','Sales','Creative','Operations','Human Resources'] as $dept): ?>
                        <option value="<?= $dept ?>"><?= $dept ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="space-y-1.5 text-left">
                    <label data-theme-muted class="text-[9px] font-bold  opacity-40 ml-1">Base Monthly Salary</label>
                    <input name="salary" type="number" class="w-full px-4 py-3 bg-surface2 border-border border rounded-lg text-xs font-bold outline-none" placeholder="0" min="0">
                </div>
                <div class="col-span-full py-2">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <div class="relative inline-flex items-center">
                            <input type="checkbox" name="can_attendance" value="1" checked class="peer sr-only">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                        </div>
                        <span class="text-xs font-bold text-on-surface opacity-70">Izinkan Absensi</span>
                    </label>
                </div>
            </div>

            <div class="pt-4 flex flex-col gap-2">
                <button type="submit" class="w-full py-4 bg-primary text-white rounded-full text-xs font-bold   shadow-2xl shadow-primary/20 active:scale-95 transition-all">
                    Register Staff
                </button>
            </div>
        </form>
    </div>
</div>


<!-- Modal Edit Karyawan -->
<div id="editEmpModal" style="display:none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/80 backdrop-blur-md p-4" onclick="if(event.target===this)closeModal('editEmpModal')">
    <div data-theme-card class="w-full max-w-2xl bg-surface rounded-lg border border-border shadow-2xl overflow-hidden">
        <div class="p-6 pb-1 flex justify-between items-center">
            <div>
                <h3 data-theme-text class="text-xl font-bold ">Modify Staff Record</h3>
                <p data-theme-muted class="text-[9px] font-bold   opacity-40">Administrative Override</p>
            </div>
            <button onclick="closeModal('editEmpModal')" class="w-10 h-10 rounded-lg flex items-center justify-center text-on-surface/40 hover:bg-surface2 transition-colors">
                <span class="material-symbols-outlined font-bold">close</span>
            </button>
        </div>

        <form method="POST" action="?page=employees&action=edit" class="p-6 pt-4 space-y-5">
            <input type="hidden" name="id" id="edit_emp_id">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                <div class="space-y-1.5 text-left">
                    <label data-theme-muted class="text-[9px] font-bold  opacity-40 ml-1">Staff Name</label>
                    <input name="name" id="edit_emp_name" type="text" class="w-full px-4 py-3 bg-surface2 border-border border rounded-lg text-xs font-bold outline-none" required>
                </div>
                <div class="space-y-1.5 text-left">
                    <label data-theme-muted class="text-[9px] font-bold  opacity-40 ml-1">Work Email</label>
                    <input name="email" id="edit_emp_email" type="email" class="w-full px-4 py-3 bg-surface2 border-border border rounded-lg text-xs font-bold outline-none" required>
                </div>
                <div class="space-y-1.5 text-left">
                    <label data-theme-muted class="text-[9px] font-bold  opacity-40 ml-1">No. HP / Telepon</label>
                    <input name="phone_number" id="edit_emp_phone_number" type="text" class="w-full px-4 py-3 bg-surface2 border-border border rounded-lg text-xs font-bold outline-none" placeholder="081234567890">
                </div>
                <div class="space-y-1.5 text-left">
                    <label data-theme-muted class="text-[9px] font-bold  opacity-40 ml-1">Official Position</label>
                    <input name="position" id="edit_emp_position" type="text" class="w-full px-4 py-3 bg-surface2 border-border border rounded-lg text-xs font-bold outline-none" required>
                </div>
                <div class="space-y-1.5 text-left">
                    <label data-theme-muted class="text-[9px] font-bold  opacity-40 ml-1">Assigned Department</label>
                    <select name="department" id="edit_emp_department" class="w-full px-4 py-3 bg-surface2 border-border border rounded-lg text-xs font-bold outline-none" required>
                        <?php foreach (['IT','Marketing','Finance','Sales','Creative','Operations','Human Resources'] as $dept): ?>
                        <option value="<?= $dept ?>"><?= $dept ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="space-y-1.5 text-left">
                    <label data-theme-muted class="text-[9px] font-bold  opacity-40 ml-1">Base Monthly Salary</label>
                    <input name="salary" id="edit_emp_salary" type="number" class="w-full px-4 py-3 bg-surface2 border-border border rounded-lg text-xs font-bold outline-none">
                </div>
                <div class="col-span-full py-2">
                    <label class="flex items-center gap-3 cursor-pointer group">
                        <div class="relative inline-flex items-center">
                            <input type="checkbox" name="can_attendance" id="edit_emp_can_attendance" value="1" class="peer sr-only">
                            <div class="w-11 h-6 bg-gray-200 peer-focus:outline-none rounded-full peer dark:bg-gray-700 peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all dark:border-gray-600 peer-checked:bg-primary"></div>
                        </div>
                        <span class="text-xs font-bold text-on-surface opacity-70">Izinkan Absensi</span>
                    </label>
                </div>
            </div>

            <div class="pt-4 flex flex-col gap-2">
                <button type="submit" class="w-full py-4 bg-primary text-white rounded-lg text-xs font-bold   shadow-2xl shadow-primary/20 active:scale-95 transition-all">
                    Update Staff Profile
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Konfirmasi Hapus -->
<div id="deleteModal" style="display:none;" class="fixed inset-0 z-[110] flex items-center justify-center bg-black/90 backdrop-blur-sm p-4" onclick="if(event.target===this)closeModal('deleteModal')">
    <div data-theme-card class="w-full max-w-sm bg-surface rounded-lg border border-border shadow-2xl overflow-hidden p-8 text-center">
        <div class="w-16 h-16 bg-rose-500/10 text-rose-500 rounded-lg flex items-center justify-center mx-auto mb-6">
            <span class="material-symbols-outlined text-3xl font-bold">person_remove</span>
        </div>
        <h3 data-theme-text class="text-xl font-bold  mb-2">Offboard Staff?</h3>
        <p data-theme-muted class="text-xs font-medium opacity-60 mb-8 leading-relaxed">Are you sure you want to offboard <span id="del_name" class="font-bold text-on-surface"></span>? This process will terminate access and archive the dossier.</p>
        
        <div class="grid grid-cols-2 gap-3">
            <button onclick="closeModal('deleteModal')" class="py-3.5 bg-surface2 text-on-surface font-bold text-[10px] rounded-lg border border-border">Cancel</button>
            <a id="del_confirm_btn" href="#" class="py-3.5 bg-rose-500 text-white font-bold text-[10px] rounded-lg shadow-lg shadow-rose-500/20">Terminate</a>
        </div>
    </div>
</div>

<!-- Modal Konfirmasi Reset Password -->
<div id="resetPasswordModal" style="display:none;" class="fixed inset-0 z-[110] flex items-center justify-center bg-black/90 backdrop-blur-sm p-4" onclick="if(event.target===this)closeModal('resetPasswordModal')">
    <div data-theme-card class="w-full max-w-sm bg-surface rounded-lg border border-border shadow-2xl overflow-hidden p-8 text-center">
        <div class="w-16 h-16 bg-amber-500/10 text-amber-500 rounded-lg flex items-center justify-center mx-auto mb-6">
            <span class="material-symbols-outlined text-3xl font-bold">lock_reset</span>
        </div>
        <h3 data-theme-text class="text-xl font-bold mb-2">Reset Password?</h3>
        <p data-theme-muted class="text-xs font-medium opacity-60 mb-8 leading-relaxed">Apakah Anda yakin ingin mereset password untuk <span id="reset_name" class="font-bold text-on-surface"></span>? Sistem akan membuat password acak baru secara otomatis.</p>
        
        <div class="grid grid-cols-2 gap-3">
            <button onclick="closeModal('resetPasswordModal')" class="py-3.5 bg-surface2 text-on-surface font-bold text-[10px] rounded-lg border border-border">Batal</button>
            <a id="reset_confirm_btn" href="#" class="py-3.5 bg-amber-500 text-white font-bold text-[10px] rounded-lg shadow-lg shadow-amber-500/20 flex items-center justify-center">Reset Sekarang</a>
        </div>
    </div>
</div>

<script>
function filterStaff(query) {
    query = query.toLowerCase();
    document.querySelectorAll('.staff-card').forEach(card => {
        const content = card.getAttribute('data-search-content');
        card.style.display = content.includes(query) ? 'block' : 'none';
    });
}

function openEditModal(emp) {
    document.getElementById('edit_emp_id').value = emp.id;
    document.getElementById('edit_emp_name').value = emp.name;
    document.getElementById('edit_emp_email').value = emp.email;
    document.getElementById('edit_emp_phone_number').value = emp.phone_number || '';
    document.getElementById('edit_emp_position').value = emp.position;
    document.getElementById('edit_emp_department').value = emp.department;
    document.getElementById('edit_emp_salary').value = emp.salary;
    document.getElementById('edit_emp_can_attendance').checked = emp.can_attendance == 1;
    openModal('editEmpModal');
}

function confirmDelete(id, name) {
    document.getElementById('del_name').innerText = name;
    document.getElementById('del_confirm_btn').href = `?page=employees&action=delete&id=${id}`;
    openModal('deleteModal');
}

function confirmResetPassword(id, name) {
    document.getElementById('reset_name').innerText = name;
    document.getElementById('reset_confirm_btn').href = `?page=employees&action=reset-password&id=${id}`;
    openModal('resetPasswordModal');
}
</script>

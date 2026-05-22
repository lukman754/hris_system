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
    <header class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl font-bold">badge</span>
                </div>
                <h1 data-theme-text class="text-3xl font-bold  leading-none">Staff Directory</h1>
            </div>
            <p data-theme-muted class="text-[10px] font-bold   ml-1 opacity-50">Enterprise Workforce Management</p>
        </div>
        
        <div class="flex items-center gap-3">
             <div data-theme-card class="bg-surface p-1.5 rounded-lg shadow-sm flex items-center gap-2 w-full md:max-w-xs border border-border">
                <div class="pl-3 text-on-surface-variant opacity-30">
                    <span class="material-symbols-outlined text-[20px]">search</span>
                </div>
                <input type="text" id="empSearch" placeholder="SEARCH STAFF..." oninput="filterStaff(this.value)" class="bg-transparent border-none text-[10px] font-bold w-full focus:ring-0 placeholder:text-on-surface-variant/20   text-on-surface">
            </div>
            <button onclick="openModal('addEmpModal')" class="px-5 py-3 bg-primary text-white rounded-lg font-bold text-xs   flex items-center gap-2 shadow-xl shadow-primary/20 active:scale-95 transition-all">
                <span class="material-symbols-outlined text-lg">person_add</span>
                <span>Onboard Staff</span>
            </button>
        </div>
    </header>

    <!-- ══ Stats Overview ══ -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div data-theme-card class="p-5 rounded-lg flex items-center gap-5">
            <div class="w-12 h-12 rounded-lg bg-blue-500/10 text-blue-500 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">groups</span>
            </div>
            <div>
                <div data-theme-muted class="text-[9px] font-bold   opacity-40">Total Workforce</div>
                <div data-theme-text class="text-xl font-bold"><?= $total_staff ?></div>
            </div>
        </div>
        <div data-theme-card class="p-5 rounded-lg flex items-center gap-5">
            <div class="w-12 h-12 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">account_tree</span>
            </div>
            <div>
                <div data-theme-muted class="text-[9px] font-bold   opacity-40">Departments</div>
                <div data-theme-text class="text-xl font-bold"><?= count($depts) ?></div>
            </div>
        </div>
        <div data-theme-card class="p-5 rounded-lg flex items-center gap-5">
            <div class="w-12 h-12 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">payments</span>
            </div>
            <div>
                <div data-theme-muted class="text-[9px] font-bold   opacity-40">Avg Salary Base</div>
                <div data-theme-text class="text-lg font-bold ">Rp <?= number_format($avg_salary/1000000, 1) ?>M</div>
            </div>
        </div>
        <div data-theme-card class="p-5 rounded-lg flex items-center gap-5">
            <div class="w-12 h-12 rounded-lg bg-indigo-500/10 text-indigo-500 flex items-center justify-center">
                <span class="material-symbols-outlined text-2xl">verified</span>
            </div>
            <div>
                <div data-theme-muted class="text-[9px] font-bold   opacity-40">Active Status</div>
                <div data-theme-text class="text-xl font-bold">100%</div>
            </div>
        </div>
    </div>

    <!-- ══ Staff Directory Grid ══ -->
    <div id="staffGrid" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 gap-6 pb-20">
        <?php foreach ($employees as $emp): ?>
        <div class="staff-card" data-search-content="<?= strtolower($emp['name'] . ' ' . $emp['position'] . ' ' . $emp['department'] . ' ' . ($emp['phone_number'] ?? '')) ?>">
            <div data-theme-card class="p-6 rounded-lg group hover:shadow-2xl hover:-translate-y-1 transition-all duration-300 relative overflow-hidden flex flex-col h-full">
                <!-- Top Accents -->
                <div class="absolute top-0 right-0 w-24 h-24 bg-primary/[0.03] rounded-bl-3xl -mr-4 -mt-4 transition-transform group-hover:scale-120 duration-500"></div>
                
                <div class="relative flex flex-col items-center text-center mb-6">
                    <!-- Photo -->
                    <div class="w-20 h-20 rounded-lg overflow-hidden mb-4 border border-primary/20 p-1 transition-transform duration-500 bg-surface">
                        <?php if (!empty($emp['photo_profile'])): ?>
                            <img src="<?= h($emp['photo_profile']) ?>" class="w-full h-full object-cover rounded-lg">
                        <?php else: ?>
                            <div class="w-full h-full bg-primary/10 text-primary flex items-center justify-center font-bold text-xl rounded-lg">
                                <?= avatar_initials($emp['name']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <h3 data-theme-text class="text-base font-bold  leading-tight group-hover:text-primary transition-colors"><?= h($emp['name']) ?></h3>
                    <p data-theme-muted class="text-[9px] font-bold   opacity-30 mt-1"><?= h($emp['position']) ?></p>
                </div>

                <div class="space-y-4 mb-6 grow">
                    <div class="flex items-center justify-between px-2">
                        <span data-theme-muted class="text-[8px] font-bold   opacity-30">Department</span>
                        <span class="px-2 py-0.5 bg-surface2 rounded text-[7px] font-bold   text-on-surface"><?= h($emp['department']) ?></span>
                    </div>
                    <div class="flex items-center justify-between px-2">
                        <span data-theme-muted class="text-[8px] font-bold   opacity-30">No. HP</span>
                        <span data-theme-text class="text-[9px] font-bold"><?= h($emp['phone_number'] ?: '-') ?></span>
                    </div>
                    <div class="flex items-center justify-between px-2">
                        <span data-theme-muted class="text-[8px] font-bold   opacity-30">Base Salary</span>
                        <span data-theme-text class="text-[9px] font-bold"><?= format_rupiah($emp['salary'] ?? 0) ?></span>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-2 pt-4">
                    <button onclick='openEditModal(<?= json_encode($emp) ?>)' class="w-full py-2.5 bg-surface2 hover:bg-primary hover:text-white rounded-lg text-[8px] font-bold   transition-all active:scale-95 flex items-center justify-center gap-1.5 border-none shadow-sm">
                        <span class="material-symbols-outlined text-sm">edit</span>
                        <span>Update</span>
                    </button>
                    <button onclick="confirmDelete('<?= h($emp['id']) ?>', '<?= h($emp['name']) ?>')" class="w-full py-2.5 bg-rose-500/5 text-rose-500 hover:bg-rose-500 hover:text-white rounded-lg text-[8px] font-bold   transition-all active:scale-95 flex items-center justify-center gap-1.5 border border-rose-500/10">
                        <span class="material-symbols-outlined text-sm">delete</span>
                        <span>Offboard</span>
                    </button>
                </div>
                <div class="pt-2">
                    <button onclick="confirmResetPassword('<?= h($emp['id']) ?>', '<?= h($emp['name']) ?>')" class="w-full py-2 bg-amber-500/5 text-amber-600 hover:bg-amber-500 hover:text-white rounded-lg text-[8px] font-bold transition-all active:scale-95 flex items-center justify-center gap-1.5 border border-amber-500/10">
                        <span class="material-symbols-outlined text-sm">lock_reset</span>
                        <span>Reset Password</span>
                    </button>
                </div>
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

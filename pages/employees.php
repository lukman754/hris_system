<?php
// pages/employees.php – Manajemen Karyawan (HRD only)
$employees = get_employees();
?>

<div class="space-y-6">

    <!-- Header actions -->
    <div class="card p-5 flex flex-col sm:flex-row gap-4 items-center">
        <div class="flex-1 relative w-full">
            <i class="fas fa-search absolute left-3.5 top-1/2 -translate-y-1/2 text-gray-400"></i>
            <input type="text" id="empSearch" placeholder="Cari nama, jabatan, departemen..."
                   oninput="filterTable('empTable', this.value)"
                   class="form-input pl-10 w-full">
        </div>
        <button onclick="openModal('addEmpModal')" class="btn btn-primary flex-shrink-0">
            <i class="fas fa-user-plus"></i> Tambah Karyawan
        </button>
    </div>

    <!-- Table -->
    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h3 class="font-bold text-gray-900 flex items-center gap-2">
                <i class="fas fa-users text-blue-500"></i> Daftar Karyawan
            </h3>
            <span class="badge badge-info"><?= count($employees) ?> karyawan</span>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table" id="empTable">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Jabatan</th>
                        <th>Departemen</th>
                        <th>Gaji Pokok</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $emp): ?>
                    <tr>
                        <td>
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 bg-gradient-to-br <?= avatar_color($emp['name']) ?> rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0">
                                    <?= avatar_initials($emp['name']) ?>
                                </div>
                                <div>
                                    <div class="font-semibold text-sm text-gray-900"><?= h($emp['name']) ?></div>
                                    <div class="text-xs text-gray-400"><?= h($emp['email']) ?></div>
                                </div>
                            </div>
                        </td>
                        <td class="text-sm text-gray-700"><?= h($emp['position']) ?></td>
                        <td><span class="badge badge-info"><?= h($emp['department']) ?></span></td>
                        <td class="text-sm font-medium text-gray-900"><?= format_rupiah($emp['salary'] ?? 0) ?></td>
                        <td><?= badge('active') ?></td>
                        <td>
                            <div class="flex gap-2">
                                <button onclick="openModal('editEmpModal')" class="btn btn-outline py-1.5 px-3 text-xs">
                                    <i class="fas fa-edit"></i> Edit
                                </button>
                                <button onclick="confirmDelete('<?= h($emp['id']) ?>', '<?= h($emp['name']) ?>')" class="btn btn-danger py-1.5 px-3 text-xs">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal Tambah Karyawan -->
<div id="addEmpModal" class="hidden modal-backdrop" onclick="if(event.target===this)closeModal('addEmpModal')">
    <div class="modal-box">
        <div class="modal-header border-b pb-4">
            <h3 class="text-lg font-bold text-gray-900">Tambah Karyawan Baru</h3>
            <button onclick="closeModal('addEmpModal')" class="text-gray-400 hover:text-gray-600">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <form method="POST" action="?page=employees&action=add" class="modal-body space-y-4">
            <div class="grid grid-cols-2 gap-4">
                <div class="col-span-2">
                    <label class="form-label">Nama Lengkap *</label>
                    <input name="name" type="text" class="form-input" placeholder="Nama karyawan" required>
                </div>
                <div>
                    <label class="form-label">Email *</label>
                    <input name="email" type="email" class="form-input" placeholder="email@company.com" required>
                </div>
                <div>
                    <label class="form-label">Password *</label>
                    <input name="password" type="password" class="form-input" placeholder="Password awal" required>
                </div>
                <div>
                    <label class="form-label">Jabatan *</label>
                    <input name="position" type="text" class="form-input" placeholder="Jabatan / posisi" required>
                </div>
                <div>
                    <label class="form-label">Departemen *</label>
                    <select name="department" class="form-input" required>
                        <?php foreach (['IT','Marketing','Finance','Sales','Creative','Operations','Human Resources'] as $dept): ?>
                        <option value="<?= $dept ?>"><?= $dept ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div>
                    <label class="form-label">Gaji Pokok</label>
                    <input name="salary" type="number" class="form-input" placeholder="0" min="0">
                </div>
                <div>
                    <label class="form-label">Tanggal Bergabung</label>
                    <input name="join_date" type="date" class="form-input" value="<?= date('Y-m-d') ?>">
                </div>
            </div>
            <div class="modal-footer border-t mt-4 pt-4">
                <button type="button" onclick="closeModal('addEmpModal')" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

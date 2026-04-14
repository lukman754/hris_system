<?php
// pages/payroll.php – Penggajian (HRD only)
$employees = get_employees();
$months = [];
for ($i = 1; $i <= 12; $i++) {
    $months[$i] = strftime('%B', mktime(0,0,0,$i,1,2024)) ?: date('F', mktime(0,0,0,$i,1,2024));
}
$sel_month = (int)($_GET['month'] ?? date('n'));
$sel_year  = (int)($_GET['year']  ?? date('Y'));
?>

<div class="space-y-6">

    <!-- Filter -->
    <div class="card p-5">
        <form method="GET" class="flex flex-col sm:flex-row gap-4 items-end">
            <input type="hidden" name="page" value="payroll">
            <div>
                <label class="form-label">Bulan</label>
                <select name="month" class="form-input w-40">
                    <?php for ($m=1;$m<=12;$m++): ?>
                    <option value="<?=$m?>" <?=$sel_month===$m?'selected':''?>>
                        <?= date('F', mktime(0,0,0,$m,1,2024)) ?>
                    </option>
                    <?php endfor; ?>
                </select>
            </div>
            <div>
                <label class="form-label">Tahun</label>
                <select name="year" class="form-input w-24">
                    <?php foreach ([2023,2024,2025] as $y): ?>
                    <option value="<?=$y?>" <?=$sel_year===$y?'selected':''?>><?=$y?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-calculator"></i> Hitung Gaji
            </button>
        </form>
    </div>

    <!-- Table Gaji -->
    <?php
    $total = 0;
    $rows = array_map(function($emp) use (&$total) {
        $salary = $emp['salary'] ?? 0;
        $allowance = 500000;
        $overtime  = rand(0, 8) * 15000;
        $gross     = $salary + $allowance + $overtime;
        $deductions= $gross * 0.06;
        $net       = $gross - $deductions;
        $total    += $net;
        return compact('emp','salary','allowance','overtime','gross','deductions','net');
    }, $employees);
    ?>

    <div class="card overflow-hidden">
        <div class="px-6 py-4 border-b flex items-center justify-between">
            <h3 class="font-bold text-gray-900">
                Penggajian – <?= date('F Y', mktime(0,0,0,$sel_month,1,$sel_year)) ?>
            </h3>
            <span class="font-bold text-emerald-600">Total: <?= format_rupiah($total) ?></span>
        </div>
        <div class="overflow-x-auto">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Karyawan</th>
                        <th>Gaji Pokok</th>
                        <th>Tunjangan</th>
                        <th>Lembur</th>
                        <th>Potongan</th>
                        <th>Gaji Bersih</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($rows as $row): ?>
                    <tr>
                        <td>
                            <div class="font-semibold text-sm"><?= h($row['emp']['name']) ?></div>
                            <div class="text-xs text-gray-400"><?= h($row['emp']['position']) ?></div>
                        </td>
                        <td><?= format_rupiah($row['salary']) ?></td>
                        <td class="text-emerald-700"><?= format_rupiah($row['allowance']) ?></td>
                        <td class="text-blue-700"><?= format_rupiah($row['overtime']) ?></td>
                        <td class="text-red-600">-<?= format_rupiah($row['deductions']) ?></td>
                        <td class="font-bold text-gray-900"><?= format_rupiah($row['net']) ?></td>
                        <td>
                            <a href="?page=payroll&action=slip&id=<?= h($row['emp']['id']) ?>&month=<?=$sel_month?>&year=<?=$sel_year?>"
                               target="_blank" class="btn btn-outline py-1.5 px-3 text-xs">
                                <i class="fas fa-print"></i> Slip
                            </a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php
// pages/payroll.php – Financial Operations Center
$is_hrd   = auth_is_hrd();
$sel_month = (int)($_GET['month'] ?? date('n'));
$sel_year  = (int)($_GET['year']  ?? date('Y'));

if ($is_hrd):
    // ── HRD: semua karyawan
    $employees = get_employees();
    $total_payout = 0;
    $rows = array_map(function($emp) use (&$total_payout) {
        $salary    = $emp['salary'] ?? 0;
        $allowance = 500000;
        $overtime  = rand(0, 8) * 15000;
        $gross     = $salary + $allowance + $overtime;
        $deductions= $gross * 0.06;
        $net       = $gross - $deductions;
        $total_payout += $net;
        return compact('emp','salary','allowance','overtime','gross','deductions','net');
    }, $employees);
    $avg_net = count($rows) > 0 ? $total_payout / count($rows) : 0;
else:
    // ── Karyawan: data diri sendiri
    $pdo        = db();
    $emp_row    = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $emp_row->execute([$user['id']]);
    $my_emp     = $emp_row->fetch();
    $my_salary  = $my_emp['salary'] ?? 0;
    $allowance  = 500000;
    $overtime   = 0; // bisa dikembangkan dari tabel overtime
    $gross      = $my_salary + $allowance + $overtime;
    $deductions = $gross * 0.06;
    $net        = $gross - $deductions;
    // Riwayat gaji: simulasi 6 bulan terakhir
    $history = [];
    for ($i = 0; $i < 6; $i++) {
        $ts     = mktime(0,0,0, date('n') - $i, 1, date('Y'));
        $history[] = [
            'month'      => date('F Y', $ts),
            'month_ts'   => $ts,
            'gross'      => $gross,
            'deductions' => $deductions,
            'net'        => $net,
        ];
    }
endif;
?>

<?php if ($is_hrd): ?>
<div class="space-y-8 performance-page-container">
    
    <!-- ══ Header Section ══ -->
    <header class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl font-bold">account_balance_wallet</span>
                </div>
                <h1 data-theme-text class="text-3xl font-bold  leading-none">Payroll Ledger</h1>
            </div>
            <p data-theme-muted class="text-[10px] font-bold   ml-1 opacity-50">Financial Distribution & Oversight</p>
        </div>
        
        <form method="GET" class="flex flex-col sm:flex-row gap-3 items-end">
            <input type="hidden" name="page" value="payroll">
            <div data-theme-card class="bg-surface p-1.5 rounded-lg shadow-sm flex items-center gap-1 border border-border">
                <select name="month" class="bg-transparent border-none text-[10px] font-bold focus:ring-0   text-on-surface px-4">
                    <?php for ($m=1;$m<=12;$m++): ?>
                    <option value="<?=$m?>" <?=$sel_month===$m?'selected':''?>><?= date('F', mktime(0,0,0,$m,1,2024)) ?></option>
                    <?php endfor; ?>
                </select>
                <div class="w-px h-4 bg-border"></div>
                <select name="year" class="bg-transparent border-none text-[10px] font-bold focus:ring-0   text-on-surface px-4">
                    <?php foreach ([2023,2024,2025] as $y): ?>
                    <option value="<?=$y?>" <?=$sel_year===$y?'selected':''?>><?=$y?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="px-6 py-3.5 bg-primary text-white rounded-lg font-bold text-xs   flex items-center gap-2 shadow-xl shadow-primary/20 active:scale-95 transition-all">
                <span class="material-symbols-outlined text-lg">refresh</span>
                <span>Update Ledger</span>
            </button>
        </form>
    </header>

    <!-- ══ Macro Payout Stats ══ -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div data-theme-card class="bg-surface p-8 rounded-lg border border-border relative overflow-hidden group">
            <div class="absolute -right-4 -top-4 w-32 h-32 bg-emerald-500/5 rounded-full blur-2xl transition-all group-hover:scale-150 duration-700"></div>
            <div class="flex items-center gap-4 mb-4">
                <div class="w-10 h-10 rounded-lg bg-emerald-500/10 text-emerald-500 flex items-center justify-center">
                    <span class="material-symbols-outlined font-bold">token</span>
                </div>
                <span data-theme-muted class="text-[9px] font-bold   opacity-40">Monthly Disbursement</span>
            </div>
            <div data-theme-text class="text-4xl font-bold "><?= format_rupiah($total_payout) ?></div>
            <div class="mt-4 flex items-center gap-2">
                <span class="px-2 py-0.5 bg-emerald-500/10 text-emerald-500 rounded text-[8px] font-bold  ">Confirmed 100%</span>
            </div>
        </div>
        
        <div data-theme-card class="bg-surface p-8 rounded-lg border border-border relative overflow-hidden group">
             <div class="absolute -right-4 -top-4 w-32 h-32 bg-primary/5 rounded-full blur-2xl transition-all group-hover:scale-150 duration-700"></div>
            <div class="flex items-center gap-4 mb-4">
                <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined font-bold">analytics</span>
                </div>
                <span data-theme-muted class="text-[9px] font-bold   opacity-40">Average Net Salary</span>
            </div>
            <div data-theme-text class="text-4xl font-bold "><?= format_rupiah($avg_net) ?></div>
            <div class="mt-4 flex items-center gap-2 text-[9px] font-bold opacity-30  ">Standard Variance across <?= count($rows) ?> Staff</div>
        </div>

        <div data-theme-card class="bg-surface p-8 rounded-lg border border-border relative overflow-hidden group">
             <div class="absolute -right-4 -top-4 w-32 h-32 bg-amber-500/5 rounded-full blur-2xl transition-all group-hover:scale-150 duration-700"></div>
            <div class="flex items-center gap-4 mb-4">
                <div class="w-10 h-10 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center">
                    <span class="material-symbols-outlined font-bold">savings</span>
                </div>
                <span data-theme-muted class="text-[9px] font-bold   opacity-40">Tax & Deductions</span>
            </div>
            <div data-theme-text class="text-4xl font-bold "><?= format_rupiah($total_payout * 0.06) ?></div>
            <div class="mt-4 flex items-center gap-2">
                <span class="px-2 py-0.5 bg-amber-500/10 text-amber-500 rounded text-[8px] font-bold  ">Withheld 6%</span>
            </div>
        </div>
    </div>

    <!-- ══ Payroll Table Reimagined as Ledger Feed ══ -->
    <div class="space-y-4 pb-20">
        <p data-theme-muted class="text-[10px] font-bold   opacity-40 ml-2 mb-4">Staff Payout Breakdown</p>
        
        <?php foreach ($rows as $row): ?>
        <div data-theme-card class="bg-surface p-6 rounded-lg border border-border group hover:shadow-xl transition-all duration-300">
            <div class="flex flex-col lg:flex-row items-center gap-8">
                <!-- Profile -->
                <div class="flex items-center gap-4 min-w-[240px]">
                    <div class="w-14 h-14 rounded-lg bg-surface2 border border-border flex items-center justify-center text-primary font-bold text-xl group-hover:scale-110 transition-transform">
                        <?= avatar_initials($row['emp']['name']) ?>
                    </div>
                    <div>
                        <h4 data-theme-text class="text-base font-bold  leading-tight"><?= h($row['emp']['name']) ?></h4>
                        <p data-theme-muted class="text-[9px] font-bold   opacity-30 mt-1"><?= h($row['emp']['position']) ?></p>
                    </div>
                </div>

                <!-- Breakdown -->
                <div class="grow grid grid-cols-2 md:grid-cols-4 gap-6 w-full lg:w-auto">
                    <div>
                        <div data-theme-muted class="text-[8px] font-bold   opacity-30 mb-1">Base Salary</div>
                        <div data-theme-text class="text-xs font-bold"><?= format_rupiah($row['salary']) ?></div>
                    </div>
                    <div>
                        <div data-theme-muted class="text-[8px] font-bold   opacity-30 mb-1">Allowances</div>
                        <div class="text-xs font-bold text-emerald-500">+<?= format_rupiah($row['allowance']) ?></div>
                    </div>
                    <div>
                        <div data-theme-muted class="text-[8px] font-bold   opacity-30 mb-1">Deductions (6%)</div>
                        <div class="text-xs font-bold text-rose-500">-<?= format_rupiah($row['deductions']) ?></div>
                    </div>
                    <div>
                        <div data-theme-muted class="text-[8px] font-bold   opacity-30 mb-1">Net Payable</div>
                        <div class="text-sm font-bold text-primary "><?= format_rupiah($row['net']) ?></div>
                    </div>
                </div>

                <!-- Action -->
                <div class="shrink-0">
                    <a href="?page=payroll&action=slip&id=<?= h($row['emp']['id']) ?>&month=<?=$sel_month?>&year=<?=$sel_year?>" target="_blank" class="w-12 h-12 flex items-center justify-center bg-surface2 border border-border rounded-lg text-on-surface-variant hover:bg-primary hover:text-white transition-all active:scale-95 group/btn shadow-sm">
                        <span class="material-symbols-outlined text-xl transition-transform group-hover/btn:rotate-12">print</span>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php else: ?>

<!-- ══════════════════════════════════════
     EMPLOYEE VIEW – Halaman Gaji Saya
══════════════════════════════════════ -->
<div class="space-y-8 performance-page-container">

    <!-- Header -->
    <header class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined text-2xl font-bold">account_balance_wallet</span>
                </div>
                <h1 data-theme-text class="text-3xl font-bold leading-none">Gaji Saya</h1>
            </div>
            <p data-theme-muted class="text-[10px] font-bold ml-1 opacity-50">Rincian & Riwayat Penggajian Anda</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="flex items-center gap-2 px-4 py-2 bg-surface rounded-lg border border-border shadow-sm">
                <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                <span data-theme-text class="text-[9px] font-bold opacity-60"><?= date('F Y') ?></span>
            </div>
        </div>
    </header>

    <!-- Kartu Slip Gaji Bulan Ini -->
    <div data-theme-card class="bg-surface rounded-lg border border-border overflow-hidden">

        <!-- Header Slip: tipis & clean -->
        <div class="bg-primary px-5 py-3 flex items-center justify-between">
            <div>
                <p class="text-white/50 text-[8px] font-bold uppercase tracking-widest leading-none">Slip Gaji</p>
                <p class="text-white text-sm font-bold leading-tight mt-0.5"><?= date('F Y') ?></p>
            </div>
            <span class="text-white/40 text-[8px] font-bold uppercase tracking-widest">PT. Perkasa Abadi</span>
        </div>

        <!-- Info Karyawan: compact row -->
        <div class="px-5 py-3 flex items-center gap-3 border-b border-border">
            <div class="w-9 h-9 rounded-lg bg-primary/10 flex items-center justify-center text-primary font-bold text-sm shrink-0">
                <?= avatar_initials($user['name']) ?>
            </div>
            <div class="min-w-0">
                <p data-theme-text class="text-sm font-bold truncate leading-tight"><?= h($user['name']) ?></p>
                <p data-theme-muted class="text-[9px] opacity-40 truncate"><?= h($user['position'] ?? '-') ?> · <?= h($user['department'] ?? '-') ?></p>
            </div>
            <span data-theme-muted class="ml-auto text-[8px] opacity-25 shrink-0"><?= h($user['id']) ?></span>
        </div>

        <!-- Rincian: tabel minimalis -->
        <div class="px-5 py-2 divide-y divide-border">
            <div class="flex justify-between items-center py-2.5">
                <span data-theme-muted class="text-[11px] opacity-50">Gaji Pokok</span>
                <span data-theme-text class="text-[11px] font-bold"><?= format_rupiah($my_salary) ?></span>
            </div>
            <div class="flex justify-between items-center py-2.5">
                <span data-theme-muted class="text-[11px] opacity-50">Tunjangan</span>
                <span class="text-[11px] font-bold text-emerald-500">+<?= format_rupiah($allowance) ?></span>
            </div>
            <div class="flex justify-between items-center py-2.5">
                <span data-theme-muted class="text-[11px] opacity-50">Lembur</span>
                <span class="text-[11px] font-bold text-amber-500">+<?= format_rupiah($overtime) ?></span>
            </div>
            <div class="flex justify-between items-center py-2.5">
                <span data-theme-muted class="text-[11px] opacity-50">Potongan (6%)</span>
                <span class="text-[11px] font-bold text-rose-500">-<?= format_rupiah($deductions) ?></span>
            </div>
        </div>

        <!-- Take Home: strip bawah -->
        <div class="px-5 py-3 bg-primary/5 border-t border-primary/10 flex items-center justify-between">
            <div>
                <p data-theme-muted class="text-[8px] font-bold uppercase tracking-widest opacity-40 leading-none">Take Home Pay</p>
                <p class="text-lg font-bold text-primary leading-tight mt-0.5"><?= format_rupiah($net) ?></p>
            </div>
            <a href="?page=payroll&action=slip&id=<?= h($user['id']) ?>&month=<?= date('n') ?>&year=<?= date('Y') ?>" target="_blank"
               class="flex items-center gap-1.5 px-3 py-2 bg-primary text-white rounded-lg text-[10px] font-bold hover:bg-primary/90 active:scale-95 transition-all">
                <span class="material-symbols-outlined text-base leading-none">print</span>
                <span>Cetak</span>
            </a>
        </div>
    </div>

    <!-- Riwayat Gaji 6 Bulan -->
    <div class="pb-24">
        <p data-theme-muted class="text-[10px] font-bold opacity-40 mb-3">Riwayat Penggajian</p>
        <div class="space-y-2">
            <?php foreach ($history as $idx => $h_row): ?>
            <div data-theme-card class="bg-surface px-4 py-3 rounded-lg border border-border <?= $idx === 0 ? 'ring-1 ring-primary/20' : '' ?> hover:shadow-md transition-all duration-200">
                <!-- Baris atas: bulan + take home + badge -->
                <div class="flex items-center gap-3">
                    <!-- Bulan badge -->
                    <div class="shrink-0 text-center w-10">
                        <div class="text-[8px] font-bold <?= $idx === 0 ? 'text-primary' : 'opacity-40' ?> leading-none"><?= date('M', $h_row['month_ts']) ?></div>
                        <div class="text-[10px] font-bold <?= $idx === 0 ? 'text-primary' : '' ?> opacity-60 leading-none"><?= date('y', $h_row['month_ts']) ?></div>
                    </div>

                    <!-- Divider -->
                    <div class="w-px h-8 bg-border shrink-0"></div>

                    <!-- Take Home (prioritas utama) -->
                    <div class="grow">
                        <div data-theme-muted class="text-[8px] font-bold opacity-30 leading-none mb-0.5">Take Home</div>
                        <div class="text-sm font-bold text-primary leading-none"><?= format_rupiah($h_row['net']) ?></div>
                    </div>

                    <!-- Status badge -->
                    <span class="shrink-0 px-2 py-0.5 bg-emerald-500/10 text-emerald-500 rounded text-[7px] font-bold">Dibayar</span>
                </div>

                <!-- Baris bawah: detail sekunder -->
                <div class="flex items-center gap-4 mt-2 pl-13" style="padding-left:52px;">
                    <span data-theme-muted class="text-[8px] opacity-30">
                        Kotor <span class="font-bold"><?= format_rupiah($h_row['gross']) ?></span>
                    </span>
                    <span class="text-[8px] text-rose-400 opacity-70">
                        Potong <span class="font-bold">-<?= format_rupiah($h_row['deductions']) ?></span>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>
<?php endif; ?>

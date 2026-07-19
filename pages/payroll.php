<?php
// pages/payroll.php – Financial Operations Center
$is_hrd   = auth_is_hrd();
$sel_month = (int)($_GET['month'] ?? date('n'));
$sel_year  = (int)($_GET['year']  ?? date('Y'));

// Load settings for rates
$deduction_type = get_setting('payroll_deduction_type', 'flat');
$deduction_rate = (int)get_setting('payroll_deduction_rate', '150000');
$overtime_rate  = (int)get_setting('payroll_overtime_rate', '50000');

if ($is_hrd):
    // ── HRD: semua karyawan
    $employees = get_employees();
    $total_payout = 0;
    $total_deductions = 0;
    $rows = [];
    foreach ($employees as $emp) {
        $row = calculate_emp_payroll_details($emp, $sel_month, $sel_year);
        $total_payout += $row['net'];
        $total_deductions += $row['deductions'];
        $rows[] = $row;
    }
    $avg_net = count($rows) > 0 ? $total_payout / count($rows) : 0;
else:
    // ── Karyawan: data diri sendiri
    $pdo        = db();
    $emp_row    = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $emp_row->execute([$user['id']]);
    $my_emp     = $emp_row->fetch();
    
    $my_payroll = calculate_emp_payroll_details($my_emp, $sel_month, $sel_year);
    
    // Riwayat gaji: simulasi 6 bulan terakhir
    $history = [];
    for ($i = 0; $i < 6; $i++) {
        $ts = mktime(0,0,0, date('n') - $i, 1, date('Y'));
        $h_month = (int)date('n', $ts);
        $h_year  = (int)date('Y', $ts);
        $h_payroll = calculate_emp_payroll_details($my_emp, $h_month, $h_year);
        $history[] = [
            'month'      => date('F Y', $ts),
            'month_ts'   => $ts,
            'gross'      => $h_payroll['gross'],
            'deductions' => $h_payroll['deductions'],
            'net'        => $h_payroll['net'],
        ];
    }
endif;
?>

<?php if ($is_hrd): ?>
<div class="space-y-8 performance-page-container">
    
    <!-- ══ Header Section ══ -->
    <header class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-3xl font-bold">account_balance_wallet</span>
                </div>
                <h1 data-theme-text class="text-3xl font-bold leading-none">Payroll Ledger</h1>
            </div>
            <p data-theme-muted class="text-[10px] font-bold ml-1 opacity-50">Financial Distribution & Oversight</p>
        </div>
        
        <div class="flex flex-col sm:flex-row gap-2 items-center self-stretch md:self-auto">
            <form method="GET" class="flex flex-col sm:flex-row gap-2 items-center w-full sm:w-auto">
                <input type="hidden" name="page" value="payroll">
                <div class="bg-surface p-1 rounded-lg shadow-sm flex items-center gap-1 w-full sm:w-auto border border-border" style="height:38px;">
                    <select name="month" class="bg-transparent border-none text-[12px] font-semibold focus:ring-0 text-on-surface px-3">
                        <?php for ($m=1;$m<=12;$m++): ?>
                        <option value="<?=$m?>" <?=$sel_month===$m?'selected':''?>><?= date('F', mktime(0,0,0,$m,1,2024)) ?></option>
                        <?php endfor; ?>
                    </select>
                    <div class="w-px h-4 bg-border"></div>
                    <select name="year" class="bg-transparent border-none text-[12px] font-semibold focus:ring-0 text-on-surface px-3">
                        <?php 
                        $cur_y = (int)date('Y');
                        for ($y = $cur_y - 3; $y <= $cur_y + 1; $y++): 
                        ?>
                        <option value="<?=$y?>" <?=$sel_year===$y?'selected':''?>><?=$y?></option>
                        <?php endfor; ?>
                    </select>
                </div>
                <button type="submit" class="w-full sm:w-auto px-6 py-3.5 bg-primary text-white rounded-lg font-bold text-xs flex items-center justify-center gap-2 shadow-xl shadow-primary/20 active:scale-95 transition-all h-[38px]">
                    <span class="material-symbols-outlined text-lg">refresh</span>
                    <span>Update Ledger</span>
                </button>
            </form>
            <a href="?page=payroll&action=summary&month=<?=$sel_month?>&year=<?=$sel_year?>" target="_blank" class="w-full sm:w-auto px-5 py-3.5 bg-emerald-600 text-white rounded-lg font-bold text-xs flex items-center justify-center gap-2 shadow-sm active:scale-95 transition-all h-[38px] hover:bg-emerald-700">
                <span class="material-symbols-outlined text-lg">print</span>
                <span>Cetak Rekap Gaji</span>
            </a>
            <button onclick="document.getElementById('payrollSettingsModal').style.display='flex'" class="w-full sm:w-auto px-5 py-3.5 bg-surface text-on-surface border border-border rounded-lg font-bold text-xs flex items-center justify-center gap-2 shadow-sm active:scale-95 transition-all h-[38px]">
                <span class="material-symbols-outlined text-lg">settings</span>
                <span>Pengaturan</span>
            </button>
        </div>
    </header>

    <!-- ══ Macro Payout Stats ══ -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <!-- 1. Monthly Disbursement (Blue) -->
        <div class="card" style="background: linear-gradient(135deg, #2563EB, #1D4ED8); border: none; color: white; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; min-h: 110px;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
                <span style="font-size: 11px; font-weight: 600; color: rgba(255, 255, 255, 0.8); text-transform: uppercase; letter-spacing: 0.02em;">Monthly Disbursement</span>
                <div style="width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.15); color: #ffffff; flex-shrink: 0;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">payments</span>
                </div>
            </div>
            <div style="display: flex; align-items: baseline; gap: 4px; margin-top: auto;">
                <span style="font-size: 20px; font-weight: 700; color: #ffffff; line-height: 1;"><?= format_rupiah($total_payout) ?></span>
            </div>
            <p style="font-size: 11px; color: rgba(255, 255, 255, 0.7); margin: 4px 0 0; font-style: italic;">Total company payout</p>
        </div>
        
        <!-- 2. Average Net Salary (Green) -->
        <div class="card" style="background: linear-gradient(135deg, #10B981, #059669); border: none; color: white; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; min-h: 110px;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
                <span style="font-size: 11px; font-weight: 600; color: rgba(255, 255, 255, 0.8); text-transform: uppercase; letter-spacing: 0.02em;">Average Net Salary</span>
                <div style="width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.15); color: #ffffff; flex-shrink: 0;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">analytics</span>
                </div>
            </div>
            <div style="display: flex; align-items: baseline; gap: 4px; margin-top: auto;">
                <span style="font-size: 20px; font-weight: 700; color: #ffffff; line-height: 1;"><?= format_rupiah($avg_net) ?></span>
            </div>
            <p style="font-size: 11px; color: rgba(255, 255, 255, 0.7); margin: 4px 0 0; font-style: italic;">Net average per employee</p>
        </div>
        
        <!-- 3. Deductions (Red) -->
        <div class="card" style="background: linear-gradient(135deg, #EF4444, #DC2626); border: none; color: white; padding: 16px; display: flex; flex-direction: column; justify-content: space-between; min-h: 110px;">
            <div style="display: flex; align-items: center; justify-content: space-between; gap: 8px; margin-bottom: 8px;">
                <span style="font-size: 11px; font-weight: 600; color: rgba(255, 255, 255, 0.8); text-transform: uppercase; letter-spacing: 0.02em;">Deductions</span>
                <div style="width: 32px; height: 32px; border-radius: 6px; display: flex; align-items: center; justify-content: center; background: rgba(255, 255, 255, 0.15); color: #ffffff; flex-shrink: 0;">
                    <span class="material-symbols-outlined" style="font-size: 18px;">trending_down</span>
                </div>
            </div>
            <div style="display: flex; align-items: baseline; gap: 4px; margin-top: auto;">
                <span style="font-size: 20px; font-weight: 700; color: #ffffff; line-height: 1;"><?= format_rupiah($total_deductions) ?></span>
            </div>
            <p style="font-size: 11px; color: rgba(255, 255, 255, 0.7); margin: 4px 0 0; font-style: italic;">Total absence cuts</p>
        </div>
    </div>

    <!-- ══ Payroll Table Reimagined as Ledger Feed ══ -->
    <div class="space-y-4 pb-20">
        <p data-theme-muted class="text-[10px] font-bold opacity-50 ml-2 mb-4">Staff Payout Breakdown</p>
        
        <?php foreach ($rows as $row): ?>
        <div data-theme-card class="bg-surface p-6 rounded-lg border border-border group hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
            <div class="flex flex-col lg:flex-row items-center gap-6">
                <!-- Profile -->
                <div class="flex items-center gap-4 min-w-[240px] w-full lg:w-auto pb-4 lg:pb-0 border-b lg:border-b-0 border-border">
                    <div class="w-14 h-14 rounded-lg bg-surface2 border border-border flex items-center justify-center text-primary font-bold text-xl group-hover:scale-110 transition-transform shrink-0">
                        <?= avatar_initials($row['emp']['name']) ?>
                    </div>
                    <div>
                        <h4 data-theme-text class="text-sm font-bold leading-tight"><?= h($row['emp']['name']) ?></h4>
                        <p data-theme-muted class="text-[10px] font-bold opacity-40 mt-1"><?= h($row['emp']['position']) ?></p>
                    </div>
                </div>

                <!-- Breakdown -->
                <div class="grow grid grid-cols-2 md:grid-cols-5 gap-6 w-full lg:w-auto py-2">
                    <div>
                        <div data-theme-muted class="text-[10px] font-bold opacity-45 mb-1">Base Salary</div>
                        <div data-theme-text class="text-xs font-bold"><?= format_rupiah($row['salary']) ?></div>
                    </div>
                    <div>
                        <div data-theme-muted class="text-[10px] font-bold opacity-45 mb-1">Allowances</div>
                        <div class="text-xs font-bold text-emerald-500">+<?= format_rupiah($row['allowance']) ?></div>
                    </div>
                    <div>
                        <div data-theme-muted class="text-[10px] font-bold opacity-45 mb-1">Overtime (<?= $row['overtime_hours'] ?>h)</div>
                        <div class="text-xs font-bold text-amber-500">+<?= format_rupiah($row['overtime_pay']) ?></div>
                    </div>
                    <div>
                        <div data-theme-muted class="text-[10px] font-bold opacity-45 mb-1">Deductions (<?= $row['absent_days'] ?>d absent)</div>
                        <div class="text-xs font-bold text-rose-500">-<?= format_rupiah($row['deductions']) ?></div>
                    </div>
                    <div>
                        <div data-theme-muted class="text-[10px] font-bold opacity-45 mb-1">Net Payable</div>
                        <div class="text-xs font-bold text-primary"><?= format_rupiah($row['net']) ?></div>
                    </div>
                </div>

                <!-- Action -->
                <div class="shrink-0 w-full lg:w-auto flex justify-end pt-4 lg:pt-0 border-t lg:border-t-0 border-border">
                    <a href="?page=payroll&action=slip&id=<?= h($row['emp']['id']) ?>&month=<?=$sel_month?>&year=<?=$sel_year?>" target="_blank" class="w-10 h-10 flex items-center justify-center bg-surface2 border border-border rounded-lg text-on-surface-variant hover:bg-primary hover:text-white transition-all active:scale-95 group/btn shadow-sm" style="background:var(--surface2); border:1px solid var(--border);">
                        <span class="material-symbols-outlined text-lg transition-transform group-hover/btn:rotate-12">print</span>
                    </a>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<!-- Modal Pengaturan Payroll -->
<div id="payrollSettingsModal" style="display:none;" class="fixed inset-0 z-[100] flex items-center justify-center bg-black/50 p-4" onclick="if(event.target===this)closeModal('payrollSettingsModal')">
    <div class="w-full max-w-md bg-[var(--surface)] rounded-lg flex flex-col shadow-2xl" style="border:1px solid var(--border);">
        <!-- Header -->
        <div class="px-6 py-4 border-b bg-[var(--surface2)] flex justify-between items-center rounded-t-lg" style="border-color:var(--border);">
            <div>
                <h3 class="font-bold text-[16px] text-[var(--text-primary)]">Pengaturan Payroll</h3>
                <p class="text-[11px] text-[var(--text-muted)]">Deduction & Overtime Rates</p>
            </div>
            <button onclick="closeModal('payrollSettingsModal')" class="text-[var(--text-muted)] hover:text-[var(--primary)] transition-colors">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <form method="POST" action="?page=payroll&action=save-settings" class="p-6 space-y-4">
            <input type="hidden" name="month" value="<?= $sel_month ?>">
            <input type="hidden" name="year" value="<?= $sel_year ?>">
            
            <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-[var(--text-muted)] block">Tipe Potongan Absensi</label>
                <select name="deduction_type" onchange="toggleDeductionRateInput(this.value)" class="form-input" required>
                    <option value="flat" <?= $deduction_type === 'flat' ? 'selected' : '' ?>>Nominal Tetap (Flat Rate)</option>
                    <option value="salary_proportional_calendar" <?= $deduction_type === 'salary_proportional_calendar' ? 'selected' : '' ?>>Proporsional Gaji (Gaji / Hari Kalender, e.g. Gaji / 30)</option>
                    <option value="salary_proportional_workdays" <?= $deduction_type === 'salary_proportional_workdays' ? 'selected' : '' ?>>Proporsional Hari Kerja (Gaji / Hari Kerja Wajib)</option>
                </select>
            </div>

            <div class="space-y-1.5" id="deduction_rate_container" style="<?= $deduction_type === 'flat' ? '' : 'display: none;' ?>">
                <label class="text-[11px] font-bold text-[var(--text-muted)] block">Potongan per Hari Absen (Rp)</label>
                <input type="number" name="deduction_rate" value="<?= h($deduction_rate) ?>" class="form-input" required min="0">
            </div>

            <div class="space-y-1.5">
                <label class="text-[11px] font-bold text-[var(--text-muted)] block">Uang Lembur per Jam (Rp)</label>
                <input type="number" name="overtime_rate" value="<?= h($overtime_rate) ?>" class="form-input" required min="0">
            </div>

            <div class="pt-2 flex flex-col gap-3">
                <button type="submit" class="w-full py-2.5 bg-[var(--primary)] text-white rounded-lg text-xs font-bold hover:opacity-90 active:scale-95 transition-all shadow-lg shadow-blue-500/20">
                    Simpan Pengaturan
                </button>
                <button type="button" onclick="closeModal('payrollSettingsModal')" class="w-full py-2 text-[11px] font-bold text-[var(--text-muted)] hover:bg-[var(--surface2)] rounded-lg transition-colors text-center">Batal</button>
            </div>
        </form>
    </div>
</div>

<script>
function toggleDeductionRateInput(val) {
    const container = document.getElementById('deduction_rate_container');
    if (container) {
        if (val === 'flat') {
            container.style.display = 'block';
        } else {
            container.style.display = 'none';
        }
    }
}
</script>
<?php else: ?>

<!-- ══════════════════════════════════════
     EMPLOYEE VIEW – Halaman Gaji Saya
     ══════════════════════════════════════ -->
<div class="space-y-8 performance-page-container">

    <!-- ══ Header Section ══ -->
    <header class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-3xl font-bold">account_balance_wallet</span>
                </div>
                <h1 data-theme-text class="text-3xl font-bold leading-none">Gaji Saya</h1>
            </div>
            <p data-theme-muted class="text-[10px] font-bold ml-1 opacity-50">Rincian & Riwayat Penggajian Anda</p>
        </div>
        
        <div class="flex items-center gap-3">
             <div class="bg-surface px-4 py-2.5 rounded-lg flex items-center gap-2 border border-border">
                  <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                  <span class="text-[11px] font-bold text-on-surface"><?= date('F Y') ?></span>
             </div>
        </div>
    </header>

    <!-- Kartu Slip Gaji Bulan Ini -->
    <div data-theme-card class="bg-surface rounded-lg border border-border overflow-hidden shadow-xl">
        <!-- Header Slip: modern gradient -->
        <div class="px-6 py-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4" style="background: linear-gradient(135deg, var(--primary), var(--primary-dark));">
            <div>
                <p class="text-white/60 text-[9px] font-bold uppercase tracking-widest leading-none">Rincian Slip Gaji</p>
                <p class="text-white text-xl font-bold leading-tight mt-1"><?= date('F Y') ?></p>
            </div>
            <div class="text-left sm:text-right">
                <p class="text-white/60 text-[9px] font-bold uppercase tracking-widest leading-none">PT. Perkasa Abadi Logistik</p>
                <p class="text-white/40 text-[9px] font-semibold mt-1">OPERATIONAL DISTRIBUTION DIVISION</p>
            </div>
        </div>

        <!-- Employee Info Section -->
        <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-4 border-b border-border bg-[var(--surface2)]">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center font-bold text-lg shrink-0">
                    <?= avatar_initials($user['name']) ?>
                </div>
                <div>
                    <h4 data-theme-text class="text-sm font-bold leading-tight"><?= h($user['name']) ?></h4>
                    <p data-theme-muted class="text-[10px] font-bold opacity-45 mt-1"><?= h($user['position'] ?? '-') ?> · <?= h($user['department'] ?? '-') ?></p>
                </div>
            </div>
            <div class="text-left sm:text-right">
                <p data-theme-muted class="text-[9px] font-bold opacity-30 leading-none">EMPLOYEE ID</p>
                <p data-theme-text class="text-xs font-mono font-bold mt-1">EMP-<?= str_pad($user['id'], 3, '0', STR_PAD_LEFT) ?></p>
            </div>
        </div>

        <!-- Bento Grid for Earnings & Deductions -->
        <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-border">
            
            <!-- LEFT: Earnings (Pendapatan) -->
            <div class="p-6 space-y-4">
                <h3 data-theme-text class="text-[12px] font-bold uppercase tracking-wider text-emerald-600 dark:text-emerald-400 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">add_circle</span> Pendapatan
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center text-xs">
                        <span data-theme-muted class="opacity-50">Gaji Pokok</span>
                        <span data-theme-text class="font-bold"><?= format_rupiah($my_payroll['salary']) ?></span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span data-theme-muted class="opacity-50">Tunjangan Jabatan</span>
                        <span data-theme-text class="font-bold"><?= format_rupiah($my_payroll['allowance']) ?></span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span data-theme-muted class="opacity-50">Lembur (<?= $my_payroll['overtime_hours'] ?> jam)</span>
                        <span data-theme-text class="font-bold text-emerald-600 dark:text-emerald-400">+<?= format_rupiah($my_payroll['overtime_pay']) ?></span>
                    </div>
                    <div class="pt-2 border-t border-dashed border-border flex justify-between items-center text-xs font-bold">
                        <span data-theme-text>Total Pendapatan</span>
                        <span class="text-emerald-600 dark:text-emerald-400"><?= format_rupiah($my_payroll['salary'] + $my_payroll['allowance'] + $my_payroll['overtime_pay']) ?></span>
                    </div>
                </div>
            </div>

            <!-- RIGHT: Deductions (Potongan) -->
            <div class="p-6 space-y-4">
                <h3 data-theme-text class="text-[12px] font-bold uppercase tracking-wider text-rose-500 flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px]">remove_circle</span> Potongan
                </h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center text-xs">
                        <span data-theme-muted class="opacity-50">Absensi (<?= $my_payroll['absent_days'] ?> hari absen)</span>
                        <span class="font-bold text-rose-500">-<?= format_rupiah($my_payroll['deductions']) ?></span>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span data-theme-muted class="opacity-50">Administrasi & Pajak</span>
                        <span data-theme-text class="font-bold">Rp 0</span>
                    </div>
                    <div class="pt-2 border-t border-dashed border-border flex justify-between items-center text-xs font-bold mt-auto">
                        <span data-theme-text>Total Potongan</span>
                        <span class="text-rose-500">-<?= format_rupiah($my_payroll['deductions']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Take Home Pay Strip -->
        <div class="px-6 py-5 bg-[var(--surface2)] border-t border-border flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <p data-theme-muted class="text-[9px] font-bold uppercase tracking-widest opacity-45 leading-none">Net Take Home Pay</p>
                <p class="text-2xl font-bold text-primary leading-tight mt-1.5"><?= format_rupiah($my_payroll['net']) ?></p>
            </div>
            
            <a href="?page=payroll&action=slip&id=<?= h($user['id']) ?>&month=<?= $sel_month ?>&year=<?= $sel_year ?>" target="_blank"
               class="flex items-center justify-center gap-2 px-5 py-3 bg-primary text-white rounded-lg text-xs font-bold hover:bg-primary-dark active:scale-95 transition-all shadow-md shadow-blue-500/20">
                <span class="material-symbols-outlined text-base">print</span>
                <span>Cetak Slip Gaji</span>
            </a>
        </div>
    </div>

    <!-- Riwayat Gaji 6 Bulan -->
    <div class="pb-24">
        <p data-theme-muted class="text-[10px] font-bold opacity-50 mb-3">Riwayat Penggajian (6 Bulan Terakhir)</p>
        <div class="space-y-3">
            <?php foreach ($history as $idx => $h_row): ?>
            <div data-theme-card class="bg-surface p-4 rounded-lg border border-border <?= $idx === 0 ? 'ring-1 ring-primary/20 shadow-md' : 'hover:shadow-md' ?> transition-all duration-200">
                <!-- Upper Row: Month + Net + Status -->
                <div class="flex items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-surface2 border border-border rounded-lg flex flex-col items-center justify-center shrink-0" style="background:var(--surface2); border:1px solid var(--border);">
                            <span class="text-[9px] font-bold uppercase <?= $idx === 0 ? 'text-primary' : 'opacity-40' ?> leading-none"><?= date('M', $h_row['month_ts']) ?></span>
                            <span data-theme-text class="text-[11px] font-bold mt-0.5"><?= date('Y', $h_row['month_ts']) ?></span>
                        </div>
                        <div>
                            <div data-theme-muted class="text-[10px] font-bold opacity-45 leading-none mb-1">Take Home Pay</div>
                            <div class="text-sm font-bold text-primary leading-none"><?= format_rupiah($h_row['net']) ?></div>
                        </div>
                    </div>
                    
                    <span class="badge badge-green">DIBAYAR</span>
                </div>

                <!-- Lower Row: Secondary Details -->
                <div class="flex flex-wrap items-center gap-4 mt-3 pt-3 border-t border-border border-dashed text-[11px]">
                    <span data-theme-muted class="opacity-60">
                        Gaji Kotor: <span data-theme-text class="font-bold"><?= format_rupiah($h_row['gross']) ?></span>
                    </span>
                    <span class="text-rose-500 opacity-80">
                        Total Potongan: <span class="font-bold">-<?= format_rupiah($h_row['deductions']) ?></span>
                    </span>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

</div>
<?php endif; ?>

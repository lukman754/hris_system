<?php
// pages/payroll_slip.php - Printable Pay Slip View
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Slip Gaji - <?= h($slip['emp']['name']) ?> - <?= date('F Y', mktime(0,0,0,$sel_month,1,$sel_year)) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #ffffff;
            color: #1f2937;
        }
        @media print {
            body {
                background-color: #ffffff;
                color: #000000;
            }
            .no-print {
                display: none;
            }
            .print-border {
                border: 1px solid #e5e7eb !important;
            }
        }
    </style>
</head>
<body class="p-8 max-w-4xl mx-auto">

    <!-- Header / Nav for browser only -->
    <div class="no-print mb-6 flex justify-between items-center bg-gray-50 p-4 rounded-lg border border-gray-200">
        <span class="text-xs font-semibold text-gray-500">Mode Pratinjau Slip Gaji</span>
        <div class="flex gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-blue-600 text-white rounded text-xs font-bold hover:bg-blue-700 transition-all">
                Cetak Slip
            </button>
            <button onclick="window.close()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded text-xs font-bold hover:bg-gray-300 transition-all">
                Tutup
            </button>
        </div>
    </div>

    <!-- Slip Container -->
    <div class="border border-gray-300 rounded-xl p-8 print-border shadow-sm">
        
        <!-- Company & Slip Header -->
        <div class="flex justify-between items-start border-b border-gray-200 pb-6 mb-6">
            <div class="flex items-center gap-3">
                <img src="/hris_system/public/img/logo.jpg" alt="Logo" class="w-12 h-12 object-contain rounded border border-gray-200">
                <div>
                    <h2 class="text-lg font-bold text-gray-950 uppercase tracking-tight">PT. Perkasa Abadi Logistik</h2>
                    <p class="text-[10px] text-gray-500 font-medium">Jl. Raya Logistik No. 88, Jakarta Selatan</p>
                </div>
            </div>
            <div class="text-right">
                <h1 class="text-2xl font-black text-blue-600 uppercase tracking-wide">Slip Gaji</h1>
                <p class="text-xs font-bold text-gray-500 mt-1"><?= date('F Y', mktime(0,0,0,$sel_month,1,$sel_year)) ?></p>
            </div>
        </div>

        <!-- Employee Info -->
        <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 bg-gray-50 p-4 rounded-lg border border-gray-200 mb-6 text-xs">
            <div>
                <span class="block text-[10px] text-gray-400 font-bold uppercase">ID Pegawai</span>
                <span class="font-bold text-gray-800"><?= h($slip['emp']['id']) ?></span>
            </div>
            <div>
                <span class="block text-[10px] text-gray-400 font-bold uppercase">Nama Karyawan</span>
                <span class="font-bold text-gray-800"><?= h($slip['emp']['name']) ?></span>
            </div>
            <div>
                <span class="block text-[10px] text-gray-400 font-bold uppercase">Jabatan</span>
                <span class="font-bold text-gray-800"><?= h($slip['emp']['position']) ?></span>
            </div>
            <div>
                <span class="block text-[10px] text-gray-400 font-bold uppercase">Departemen</span>
                <span class="font-bold text-gray-800"><?= h($slip['emp']['department']) ?></span>
            </div>
        </div>

        <!-- Attendance Stats Summary -->
        <div class="mb-6">
            <h3 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-2">Ikhtisar Kehadiran</h3>
            <div class="grid grid-cols-4 gap-2 text-center text-xs">
                <div class="p-2 bg-gray-50 rounded border border-gray-100">
                    <span class="block text-[9px] text-gray-400 font-semibold uppercase">Hari Kerja Wajib</span>
                    <span class="font-bold text-gray-800 text-sm"><?= $slip['expected_workdays'] ?> Hari</span>
                </div>
                <div class="p-2 bg-emerald-50 rounded border border-emerald-100">
                    <span class="block text-[9px] text-emerald-600 font-semibold uppercase">Hadir</span>
                    <span class="font-bold text-emerald-800 text-sm"><?= $slip['attended_days'] ?> Hari</span>
                </div>
                <div class="p-2 bg-blue-50 rounded border border-blue-100">
                    <span class="block text-[9px] text-blue-600 font-semibold uppercase">Cuti Disetujui</span>
                    <span class="font-bold text-blue-800 text-sm"><?= $slip['leave_days'] ?> Hari</span>
                </div>
                <div class="p-2 bg-red-50 rounded border border-red-100">
                    <span class="block text-[9px] text-red-600 font-semibold uppercase">Alasan Absen</span>
                    <span class="font-bold text-red-800 text-sm"><?= $slip['absent_days'] ?> Hari</span>
                </div>
            </div>
        </div>

        <!-- Details Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Earnings -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wide">Penerimaan (Earnings)</h3>
                </div>
                <div class="p-4 divide-y divide-gray-100 text-xs">
                    <div class="flex justify-between py-2">
                        <span class="text-gray-500">Gaji Pokok (Base Salary)</span>
                        <span class="font-bold text-gray-800"><?= format_rupiah($slip['salary']) ?></span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-gray-500">Tunjangan Tetap (Allowances)</span>
                        <span class="font-bold text-gray-800"><?= format_rupiah($slip['allowance']) ?></span>
                    </div>
                    <div class="flex justify-between py-2">
                        <div>
                            <span class="text-gray-500 block">Uang Harian (Daily Allowance)</span>
                            <span class="text-[9px] text-gray-400 block"><?= $slip['attended_days'] ?> hari masuk @ <?= format_rupiah($slip['daily_allowance_rate']) ?>/hari</span>
                        </div>
                        <span class="font-bold text-emerald-600">+<?= format_rupiah($slip['daily_allowance_pay']) ?></span>
                    </div>
                    <div class="flex justify-between py-2">
                        <div>
                            <span class="text-gray-500 block">Uang Lembur (Overtime)</span>
                            <span class="text-[9px] text-gray-400 block"><?= $slip['overtime_hours'] ?> jam @ <?= format_rupiah($slip['overtime_rate']) ?>/jam</span>
                        </div>
                        <span class="font-bold text-emerald-600">+<?= format_rupiah($slip['overtime_pay']) ?></span>
                    </div>
                    <div class="flex justify-between py-2.5 font-bold text-gray-900 border-t border-gray-200">
                        <span>Total Penerimaan Kotor</span>
                        <span><?= format_rupiah($slip['gross']) ?></span>
                    </div>
                </div>
            </div>

            <!-- Deductions -->
            <div class="border border-gray-200 rounded-lg overflow-hidden">
                <div class="bg-gray-50 px-4 py-2 border-b border-gray-200">
                    <h3 class="text-xs font-bold text-gray-800 uppercase tracking-wide">Potongan (Deductions)</h3>
                </div>
                <div class="p-4 divide-y divide-gray-100 text-xs">
                    <div class="flex justify-between py-2">
                        <div>
                            <span class="text-gray-500 block">Potongan Absensi</span>
                            <span class="text-[9px] text-gray-400 block"><?= $slip['absent_days'] ?> hari tidak masuk (gaji pokok tidak dipotong)</span>
                        </div>
                        <span class="font-bold text-red-600"><?= format_rupiah($slip['deductions']) ?></span>
                    </div>
                    <!-- Empty fill for layout balance -->
                    <div class="flex justify-between py-2 opacity-0">
                        <span>Balance</span>
                        <span>0</span>
                    </div>
                    <div class="flex justify-between py-2 opacity-0">
                        <span>Balance</span>
                        <span>0</span>
                    </div>
                    <div class="flex justify-between py-2.5 font-bold text-gray-900 border-t border-gray-200">
                        <span>Total Potongan</span>
                        <span><?= format_rupiah($slip['deductions']) ?></span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Take Home Pay Card -->
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-5 flex justify-between items-center mb-8">
            <div>
                <p class="text-[10px] text-blue-700 font-bold uppercase tracking-wider leading-none">Penerimaan Bersih (Take Home Pay)</p>
                <p class="text-2xl font-black text-blue-900 mt-1.5 leading-none"><?= format_rupiah($slip['net']) ?></p>
            </div>
            <div class="text-right text-[10px] text-gray-400 italic">
                * Ditransfer langsung ke rekening terdaftar.
            </div>
        </div>

        <!-- Signatures -->
        <div class="grid grid-cols-2 gap-8 text-center text-xs mt-12 pt-8 border-t border-dashed border-gray-200">
            <div>
                <p class="text-gray-400 mb-16">Penerima Karyawan,</p>
                <div class="w-40 border-b border-gray-400 mx-auto mb-1"></div>
                <p class="font-bold text-gray-800"><?= h($slip['emp']['name']) ?></p>
            </div>
            <div>
                <p class="text-gray-400 mb-16">Mengetahui, HRD Manager,</p>
                <div class="w-40 border-b border-gray-400 mx-auto mb-1"></div>
                <p class="font-bold text-gray-800">Admin HRD</p>
            </div>
        </div>

    </div>

    <!-- Print trigger -->
    <script>
        window.addEventListener('load', () => {
            setTimeout(() => {
                window.print();
            }, 600);
        });
    </script>
</body>
</html>

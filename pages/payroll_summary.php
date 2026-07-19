<?php
// pages/payroll_summary.php - Printable Payroll Summary View (Rekapan Gaji)
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekapitulasi Gaji Karyawan - <?= date('F Y', mktime(0,0,0,$sel_month,1,$sel_year)) ?></title>
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
                padding: 0;
            }
            .no-print {
                display: none;
            }
            .print-border {
                border: 1px solid #e5e7eb !important;
            }
            @page {
                size: A4 portrait;
                margin: 1.5cm;
            }
        }
    </style>
</head>
<body class="p-6 max-w-4xl mx-auto">

    <!-- Header / Nav for browser only -->
    <div class="no-print mb-6 flex justify-between items-center bg-gray-50 p-4 rounded-lg border border-gray-200">
        <span class="text-xs font-semibold text-gray-500">Mode Pratinjau Rekap Gaji (Portrait)</span>
        <div class="flex gap-2">
            <button onclick="window.print()" class="px-4 py-2 bg-emerald-600 text-white rounded text-xs font-bold hover:bg-emerald-700 transition-all flex items-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
                Cetak Rekap
            </button>
            <button onclick="window.close()" class="px-4 py-2 bg-gray-200 text-gray-700 rounded text-xs font-bold hover:bg-gray-300 transition-all">
                Tutup
            </button>
        </div>
    </div>

    <!-- Summary Container -->
    <div class="border border-gray-200 rounded-xl p-8 print-border shadow-sm bg-white">
        
        <!-- Header -->
        <div class="flex justify-between items-start border-b border-gray-200 pb-6 mb-6">
            <div class="flex items-center gap-3">
                <img src="/hris_system/public/img/logo.jpg" alt="Logo" class="w-12 h-12 object-contain rounded border border-gray-200">
                <div>
                    <h2 class="text-lg font-bold text-gray-950 uppercase tracking-tight">PT. Perkasa Abadi Logistik</h2>
                    <p class="text-[10px] text-gray-500 font-medium">Jl. Raya Logistik No. 88, Jakarta Selatan</p>
                </div>
            </div>
            <div class="text-right">
                <h1 class="text-xl font-black text-gray-900 uppercase tracking-wide">Rekapitulasi Gaji Karyawan</h1>
                <p class="text-xs font-bold text-gray-500 mt-1">Periode: <?= date('F Y', mktime(0,0,0,$sel_month,1,$sel_year)) ?></p>
            </div>
        </div>

        <!-- Table -->
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left border-collapse border border-gray-200">
                <thead>
                    <tr class="bg-gray-50 text-gray-700 uppercase tracking-wider text-[10px] font-bold border-b border-gray-200">
                        <th class="border border-gray-200 p-2.5 text-center w-12">No</th>
                        <th class="border border-gray-200 p-2.5">Nama Karyawan</th>
                        <th class="border border-gray-200 p-2.5">Jabatan</th>
                        <th class="border border-gray-200 p-2.5 text-right w-44">Total Gaji Bersih</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php 
                    $no = 1;
                    foreach ($rows as $r): 
                    ?>
                    <tr class="hover:bg-gray-50/50">
                        <td class="border border-gray-200 p-2.5 text-center text-gray-500"><?= $no++ ?></td>
                        <td class="border border-gray-200 p-2.5 font-semibold text-gray-900"><?= h($r['emp']['name']) ?></td>
                        <td class="border border-gray-200 p-2.5 text-gray-600"><?= h($r['emp']['position']) ?></td>
                        <td class="border border-gray-200 p-2.5 text-right font-bold text-blue-600"><?= format_rupiah($r['net']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr class="bg-gray-50 font-bold text-gray-900 border-t-2 border-gray-300">
                        <td colspan="3" class="border border-gray-200 p-3 text-center uppercase tracking-wider text-[10px]">Total Payout Keseluruhan</td>
                        <td class="border border-gray-200 p-3 text-right text-blue-700"><?= format_rupiah($total_payout) ?></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <!-- Signatures -->
        <div class="grid grid-cols-2 gap-8 text-center text-xs mt-16 pt-8 border-t border-dashed border-gray-200">
            <div>
                <p class="text-gray-400 mb-20">Dibuat Oleh,</p>
                <div class="w-48 border-b border-gray-400 mx-auto mb-1"></div>
                <p class="font-bold text-gray-800">Admin HRD</p>
            </div>
            <div>
                <p class="text-gray-400 mb-20">Mengetahui,</p>
                <div class="w-48 border-b border-gray-400 mx-auto mb-1"></div>
                <p class="font-bold text-gray-800">Finance</p>
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

<?php
// pages/attendance-reports.php – Laporan Absensi (HRD)
$attendance = get_attendance();
usort($attendance, fn($a,$b) => strtotime($b['attendance_date']) - strtotime($a['attendance_date']));
?>
<div class="card overflow-hidden">
    <div class="px-6 py-4 border-b">
        <h3 class="font-bold text-gray-900 flex items-center gap-2">
            <i class="fas fa-chart-bar text-indigo-500"></i> Laporan Absensi Karyawan
        </h3>
    </div>
    <div class="overflow-x-auto">
        <table class="data-table">
            <thead>
                <tr><th>Karyawan</th><th>Tanggal</th><th>Jam</th><th>Tipe</th><th>Lokasi</th><th>Status</th></tr>
            </thead>
            <tbody>
                <?php foreach ($attendance as $rec): ?>
                <tr>
                    <td class="font-medium"><?= h($rec['employee_name']) ?></td>
                    <td><?= format_date($rec['attendance_date']) ?></td>
                    <td><?= h($rec['attendance_time']) ?></td>
                    <td><span class="badge badge-info"><?= $rec['attendance_type']==='qr' ? 'QR Code' : 'Foto' ?></span></td>
                    <td class="text-gray-600"><?= h($rec['location']) ?></td>
                    <td><?= badge($rec['approval_status']) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

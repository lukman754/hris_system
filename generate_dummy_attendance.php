<?php
/**
 * Script untuk membuat data dummy absensi yang realistis
 * Jalankan file ini melalui CLI (php generate_dummy_attendance.php) atau panggil via browser.
 */

require_once __DIR__ . '/config/database.php';

$pdo = db();
if (!$pdo) {
    die("Error: Gagal terhubung ke database. Harap periksa file config/database.php Anda.\n");
}

// Opsi konfigurasi
$start_date_str = '2026-06-01'; // Silakan sesuaikan tanggal mulai
$end_date_str   = '2026-07-23'; // Silakan sesuaikan tanggal akhir
$absent_chance  = 15;          // Peluang absen (%)
$late_chance    = 15;          // Peluang terlambat (%) jika hadir

echo "=== GENERATOR DATA DUMMY ABSENSI ===\n";
echo "Rentang Tanggal: $start_date_str s/d $end_date_str\n";

// 1. Ambil data semua user
$users = $pdo->query("SELECT id, name FROM users")->fetchAll();
if (empty($users)) {
    die("Error: Tidak ada user yang ditemukan di tabel 'users'. Buat user terlebih dahulu.\n");
}
echo "Menemukan " . count($users) . " user.\n";

// 2. Ambil hari libur dari calendar_events agar tidak digenerate absensinya (atau opsional)
$holidays = [];
try {
    $holiday_rows = $pdo->query("SELECT event_date FROM calendar_events WHERE category = 'holiday'")->fetchAll(PDO::FETCH_COLUMN);
    $holidays = $holiday_rows ?: [];
} catch (PDOException $e) {
    // Abaikan jika tabel calendar_events belum ada
}

// 3. Loop tanggal
$start_date = new DateTime($start_date_str);
$end_date   = new DateTime($end_date_str);
$interval   = new DateInterval('P1D');
$date_period = new DatePeriod($start_date, $interval, $end_date->modify('+1 day'));

$inserted_count = 0;

// Bersihkan data lama agar perubahan random/absen terlihat jelas
$pdo->exec("DELETE FROM attendance");

foreach ($users as $user) {
    $userId = $user['id'];
    $userName = $user['name'];
    echo "Memproses absensi untuk: $userName ($userId)...\n";
    
    foreach ($date_period as $date) {
        $dateStr = $date->format('Y-m-d');
        $dayOfWeek = (int)$date->format('N'); // 1 (Mon) - 7 (Sun)
        
        // Skip akhir pekan (Sabtu/Minggu)
        if ($dayOfWeek > 5) {
            continue;
        }
        
        // Skip hari libur nasional
        if (in_array($dateStr, $holidays)) {
            continue;
        }
        
        // Cek apakah hari ini disimulasikan sebagai absen (tidak masuk)
        if (rand(1, 100) <= $absent_chance) {
            // Simulasi absen (karyawan bolos / tidak melakukan log sama sekali)
            continue;
        }
        
        // Cek jika data absensi pada tanggal tersebut sudah ada untuk menghindari duplikat
        $stmt_check = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE user_id = ? AND attendance_date = ?");
        $stmt_check->execute([$userId, $dateStr]);
        if ($stmt_check->fetchColumn() > 0) {
            continue; // Lewati jika sudah ada data
        }

        // Tentukan jam Check-In
        $roll = rand(1, 100);
        if ($roll <= 60) {
            // Tepat waktu: Check-in antara jam 07:30 s/d 08:00
            $hour = 7;
            $minute = rand(30, 59);
            $status_in = 'valid';
        } elseif ($roll <= 80) {
            // Terlambat dalam toleransi: Check-in antara jam 08:01 s/d 08:15
            $hour = 8;
            $minute = rand(1, 15);
            $status_in = 'late';
        } else {
            // Terlambat di luar toleransi: Check-in antara jam 08:16 s/d 09:15
            $hour = rand(8, 9);
            $minute = ($hour == 8) ? rand(16, 59) : rand(0, 15);
            $status_in = 'late';
        }
        $second = rand(0, 59);
        $check_in_time = sprintf('%02d:%02d:%02d', $hour, $minute, $second);
        
        // Tentukan jam Check-Out (Jam pulang standard adalah 17:00, lembur jika di atas itu)
        // Check-out antara jam 17:00 s/d 19:30
        $out_hour = rand(17, 19);
        $out_minute = rand(0, 59);
        $out_second = rand(0, 59);
        $check_out_time = sprintf('%02d:%02d:%02d', $out_hour, $out_minute, $out_second);
        
        // Koordinat Jakarta Pusat / Kantor
        $lat = -6.2088 + (rand(-100, 100) / 100000.0);
        $lng = 106.8456 + (rand(-100, 100) / 100000.0);
        
        // Insert Check-In
        $stmt = $pdo->prepare("INSERT INTO attendance 
            (user_id, attendance_date, attendance_time, attendance_type, location, location_id, latitude, longitude, status, attendance_flow, approval_status) 
            VALUES (?, ?, ?, 'qr', 'Kantor Pusat Jakarta', 1, ?, ?, ?, 'in', 'approved')");
        $stmt->execute([$userId, $dateStr, $check_in_time, $lat, $lng, $status_in]);
        $inserted_count++;
        
        // Insert Check-Out (Check-out status selalu valid/late sesuai aturan shift)
        $status_out = 'valid'; 
        $stmt = $pdo->prepare("INSERT INTO attendance 
            (user_id, attendance_date, attendance_time, attendance_type, location, location_id, latitude, longitude, status, attendance_flow, approval_status) 
            VALUES (?, ?, ?, 'qr', 'Kantor Pusat Jakarta', 1, ?, ?, ?, 'out', 'approved')");
        $stmt->execute([$userId, $dateStr, $check_out_time, $lat, $lng, $status_out]);
        $inserted_count++;
    }
}

echo "=== SELESAI ===\n";
echo "Berhasil memasukkan $inserted_count rekaman absensi baru.\n";

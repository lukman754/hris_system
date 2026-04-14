<?php
// includes/attendance_v2.php

/**
 * Calculate distance between two GPS points using Haversine formula
 */
function haversine_distance($lat1, $lon1, $lat2, $lon2): float {
    $earth_radius = 6371000; // in meters
    $dLat = deg2rad($lat2 - $lat1);
    $dLon = deg2rad($lon1 - $lon2);
    $a = sin($dLat/2) * sin($dLat/2) + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon/2) * sin($dLon/2);
    $c = 2 * atan2(sqrt($a), sqrt(1-$a));
    return $earth_radius * $c;
}

/**
 * Handle new QR V2 attendance scan
 */
function handle_scan_attendance($user_id, $token, $lat, $lng) {
    $pdo = db();
    $now = new DateTime('now', new DateTimeZone('Asia/Jakarta'));
    $today = $now->format('Y-m-d');
    $time = $now->format('H:i:s');

    // 1. Validate Token
    $stmt = $pdo->prepare("SELECT t.*, l.* FROM qr_tokens t JOIN locations l ON t.location_id = l.id WHERE t.token = ? AND t.is_active = 1 LIMIT 1");
    $stmt->execute([$token]);
    $loc = $stmt->fetch();

    if (!$loc) return ['status' => 'error', 'message' => 'QR Token Invalid atau sudah kedaluwarsa.'];

    // 2. Validate Distance (Geofencing)
    $distance = haversine_distance((float)$lat, (float)$lng, (float)$loc['latitude'], (float)$loc['longitude']);
    if ($distance > (int)$loc['radius_meters']) {
        return ['status' => 'outside_radius', 'message' => "Anda berada di luar radius kantor ($distance meter). Jarak maksimal adalah {$loc['radius_meters']} meter."];
    }

    // 3. Determine Flow & Duplication (Check-In vs Check-Out)
    $stmt = $pdo->prepare("SELECT * FROM attendance WHERE user_id = ? AND attendance_date = ? ORDER BY attendance_time ASC");
    $stmt->execute([$user_id, $today]);
    $history = $stmt->fetchAll();

    $flow = 'in'; // Default to check-in
    
    if (count($history) > 0) {
        $has_in = false;
        $has_out = false;
        foreach ($history as $h) {
            if ($h['attendance_flow'] === 'in') $has_in = true;
            if ($h['attendance_flow'] === 'out') $has_out = true;
        }

        if ($has_in && !$has_out) {
            $flow = 'out'; // Switch to check-out logic
        } else if ($has_in && $has_out) {
            return ['status' => 'duplicate', 'message' => 'Anda sudah melakukan check-in dan check-out hari ini.'];
        }
    }

    // 4. Time Validation
    $att_status = 'valid';
    if ($flow === 'in') {
        if ($time < $loc['check_in_start']) $att_status = 'early';
        if ($time > $loc['check_in_end']) $att_status = 'late';
    } else {
        if ($time < $loc['check_out_start']) $att_status = 'early';
        if ($time > $loc['check_out_end']) $att_status = 'late';
    }

    // 5. Store Data
    $stmt = $pdo->prepare("INSERT INTO attendance 
        (user_id, attendance_date, attendance_time, attendance_type, location, location_id, latitude, longitude, status, attendance_flow, approval_status) 
        VALUES (?, ?, ?, 'qr', ?, ?, ?, ?, ?, ?, 'approved')");
    $stmt->execute([
        $user_id, $today, $time, $loc['name'], $loc['id'], $lat, $lng, $att_status, $flow
    ]);

    return [
        'status' => 'success',
        'flow'   => $flow,
        'message' => "Absensi " . ($flow === 'in' ? 'Masuk' : 'Pulang') . " Terverifikasi. Status: " . strtoupper($att_status) . ".",
        'data' => [
            'location' => $loc['name'],
            'time' => $time,
            'status' => $att_status,
            'distance_to_center' => round($distance, 2)
        ]
    ];
}

/**
 * Handle Admin Token Generation
 */
function admin_generate_token($location_id) {
    $pdo = db();
    // Deactivate old tokens
    $pdo->prepare("UPDATE qr_tokens SET is_active = 0 WHERE location_id = ?")->execute([$location_id]);
    
    // Create new token (CUID-like simplicity)
    $newToken = bin2hex(random_bytes(16));
    $stmt = $pdo->prepare("INSERT INTO qr_tokens (location_id, token) VALUES (?, ?)");
    $stmt->execute([$location_id, $newToken]);
    
    return $newToken;
}

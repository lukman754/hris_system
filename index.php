<?php
/**
 * index.php – Router utama HRIS System
 */

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';
require_once __DIR__ . '/includes/attendance_v2.php';

// ─── Handle Actions ──────────────────────────────────────────

// Login
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email']) && !isset($_GET['action'])) {
    $user = auth_login(trim($_POST['email']), $_POST['password'] ?? '');
    if ($user) {
        $_SESSION['user'] = $user;
        header('Location: /hris_system/');
        exit;
    }
    $login_error = 'Email atau password salah.';
}

// Logout
if (($_GET['action'] ?? '') === 'logout') {
    session_destroy();
    header('Location: /hris_system/');
    exit;
}

// Generic Action Handlers (Real Database CRUD)
if (isset($_SESSION['user']) && (isset($_GET['action']) || isset($_POST['action']))) {
    $pdo    = db();
    $action = $_GET['action'] ?? $_POST['action'] ?? '';
    $page   = $_GET['page'] ?? $_POST['page'] ?? 'dashboard';
    $uid    = $_SESSION['user']['id'];
    
    // Employee Actions (HRD Only)
    if ($page === 'employees' && auth_is_hrd()) {
        if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $pdo->prepare("INSERT INTO users (id, name, email, phone_number, password, role, position, department, salary, join_date, can_attendance) VALUES (?, ?, ?, ?, ?, 'employee', ?, ?, ?, ?, ?)");
            $new_id = 'emp' . str_pad(rand(100, 999), 3, '0', STR_PAD_LEFT);
            $stmt->execute([
                $new_id, $_POST['name'], $_POST['email'], $_POST['phone_number'] ?? null, $_POST['password'], 
                $_POST['position'], $_POST['department'], $_POST['salary'], $_POST['join_date'],
                isset($_POST['can_attendance']) ? 1 : 0
            ]);
            header("Location: /hris_system/?page=employees&success=Karyawan berhasil ditambahkan");
            exit;
        }
        if ($action === 'delete' && isset($_GET['id'])) {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role = 'employee'");
            $stmt->execute([$_GET['id']]);
            header("Location: /hris_system/?page=employees&success=Karyawan berhasil dihapus");
            exit;
        }
        if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $pdo->prepare("UPDATE users SET name=?, email=?, phone_number=?, position=?, department=?, salary=?, can_attendance=? WHERE id = ?");
            $stmt->execute([
                $_POST['name'], $_POST['email'], $_POST['phone_number'] ?? null, $_POST['position'], 
                $_POST['department'], $_POST['salary'], 
                isset($_POST['can_attendance']) ? 1 : 0,
                $_POST['id']
            ]);
            header("Location: /hris_system/?page=employees&success=Karyawan berhasil diperbarui");
            exit;
        }
        if ($action === 'reset-password' && isset($_GET['id'])) {
            $emp_id = $_GET['id'];
            $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
            $new_password = '';
            for ($i = 0; $i < 8; $i++) {
                $new_password .= $chars[rand(0, strlen($chars) - 1)];
            }
            
            $stmt = $pdo->prepare("SELECT name, phone_number FROM users WHERE id = ? AND role = 'employee'");
            $stmt->execute([$emp_id]);
            $emp = $stmt->fetch();
            if ($emp) {
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                $stmt->execute([$new_password, $emp_id]);
                $_SESSION['reset_password_success'] = [
                    'name' => $emp['name'],
                    'password' => $new_password
                ];
                
                $phone = $emp['phone_number'] ?? '';
                $clean_phone = preg_replace('/[^0-9]/', '', $phone);
                if (str_starts_with($clean_phone, '0')) {
                    $clean_phone = '62' . substr($clean_phone, 1);
                } else if (!empty($clean_phone) && !str_starts_with($clean_phone, '62')) {
                    $clean_phone = '62' . $clean_phone;
                }

                if (!empty($clean_phone)) {
                    $msg = "Yth. *" . $emp['name'] . "*,\n\nPassword akun HRIS Anda telah direset oleh Administrator dengan password sementara berikut:\n\n🔑 *Password Baru:* `" . $new_password . "`\n\nSilakan segera login ke sistem portal HRIS dan ganti password ini melalui menu *Profil Saya* demi menjaga keamanan akun Anda.\n\nTerima kasih,\n*HRD - Perkasa Abadi Logistik*";
                    $wa_link = "https://wa.me/" . $clean_phone . "?text=" . urlencode($msg);
                    header("Location: " . $wa_link);
                } else {
                    header("Location: /hris_system/?page=employees&success=Password berhasil direset (karyawan belum mengisi No. HP)");
                }
                exit;
            }
        }
    }

    // Profile Actions (Any logged-in user)
    if ($page === 'profile') {
        if ($action === 'change-password' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $old_pass = $_POST['old_password'] ?? '';
            $new_pass = $_POST['new_password'] ?? '';
            $confirm_pass = $_POST['confirm_password'] ?? '';
            
            if ($old_pass === $_SESSION['user']['password']) {
                if ($new_pass === $confirm_pass) {
                    if (strlen($new_pass) >= 8) {
                        $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
                        $stmt->execute([$new_pass, $uid]);
                        $_SESSION['user']['password'] = $new_pass;
                        header("Location: /hris_system/?page=profile&success=Password berhasil diperbarui");
                    } else {
                        header("Location: /hris_system/?page=profile&error=Password baru minimal 8 karakter");
                    }
                } else {
                    header("Location: /hris_system/?page=profile&error=Konfirmasi password baru tidak cocok");
                }
            } else {
                header("Location: /hris_system/?page=profile&error=Password lama salah");
            }
            exit;
        }
    }

    // Leave Actions
    if ($page === 'leaves') {
        if ($action === 'submit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $pdo->prepare("INSERT INTO leaves (user_id, leave_type, leave_start, leave_end, leave_reason, approval_status) VALUES (?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$uid, $_POST['leave_type'], $_POST['leave_start'], $_POST['leave_end'], $_POST['leave_reason']]);
            header("Location: /hris_system/?page=leaves&success=Pengajuan izin terkirim");
            exit;
        }
        if (auth_is_hrd() && ($action === 'approve' || $action === 'reject')) {
            $status = ($action === 'approve') ? 'approved' : 'rejected';
            $stmt = $pdo->prepare("UPDATE leaves SET approval_status = ? WHERE id = ?");
            $stmt->execute([$status, $_GET['id']]);
            header("Location: /hris_system/?page=leaves&success=Pengajuan izin " . ($status === 'approved' ? 'disetujui' : 'ditolak'));
            exit;
        }
    }

    // Attendance Actions
    if ($page === 'attendance') {
        if ($action === 'qr') {
            $token = $_GET['code'] ?? '';
            $lat   = $_GET['lat']  ?? 0;
            $lng   = $_GET['lng']  ?? 0;
            
            $result = handle_scan_attendance($uid, $token, $lat, $lng);
            
            if ($result['status'] === 'success') {
                header("Location: /hris_system/?page=attendance&success=" . urlencode($result['message']));
            } else {
                header("Location: /hris_system/?page=attendance&error=" . urlencode($result['message']));
            }
            exit;
        }
        if ($action === 'submit-photo' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $photo_data = $_POST['photo_data'] ?? '';
            $photo_path = null;
            $lat = $_POST['lat'] ?? 0;
            $lng = $_POST['lng'] ?? 0;
            $addr = $_POST['address'] ?? 'Remote/WFH';

            if ($photo_data) {
                $dir = __DIR__ . '/public/uploads/attendance';
                if (!is_dir($dir)) mkdir($dir, 0777, true);
                $img = str_replace(['data:image/jpeg;base64,', ' '], ['', '+'], $photo_data);
                $data = base64_decode($img);
                $filename = 'att_' . $uid . '_' . time() . '.jpg';
                file_put_contents($dir . '/' . $filename, $data);
                $photo_path = '/hris_system/public/uploads/attendance/' . $filename;
            }

            // Determine In vs Out
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM attendance WHERE user_id = ? AND attendance_date = CURRENT_DATE AND attendance_flow = 'in'");
            $stmt->execute([$uid]);
            $has_in = $stmt->fetchColumn() > 0;
            $flow = $has_in ? 'out' : 'in';

            $stmt = $pdo->prepare("INSERT INTO attendance (user_id, attendance_date, attendance_time, attendance_type, location, latitude, longitude, photo_path, attendance_flow, approval_status) VALUES (?, CURRENT_DATE, CURRENT_TIME, 'photo', ?, ?, ?, ?, ?, 'pending')");
            $stmt->execute([$uid, $addr, $lat, $lng, $photo_path, $flow]);
            
            header("Location: /hris_system/?page=attendance&success=Absensi " . ($flow === 'in' ? 'Masuk' : 'Pulang') . " terverifikasi (Pending)");
            exit;
        }
    }

    // Photo Approvals (HRD)
    if ($page === 'photo-approvals' && auth_is_hrd()) {
        if ($action === 'approve' || $action === 'reject') {
            $status = ($action === 'approve') ? 'approved' : 'rejected';
            $stmt = $pdo->prepare("UPDATE attendance SET approval_status = ? WHERE id = ?");
            $stmt->execute([$status, $_GET['id']]);
            header("Location: /hris_system/?page=photo-approvals&success=Absensi foto " . ($status === 'approved' ? 'disetujui' : 'ditolak'));
            exit;
        }
    }

    // Announcement Actions (HRD)
    if ($page === 'announcements' && auth_is_hrd()) {
        if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $pdo->prepare("INSERT INTO announcements (title, content, priority, author_id, expires_at) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$_POST['title'], $_POST['content'], $_POST['priority'], $uid, $_POST['expires_at'] ?: null]);
            header("Location: /hris_system/?page=announcements&success=Pengumuman dipublikasikan");
            exit;
        }
        if ($action === 'edit' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $pdo->prepare("UPDATE announcements SET title = ?, content = ?, priority = ?, expires_at = ? WHERE id = ?");
            $stmt->execute([$_POST['title'], $_POST['content'], $_POST['priority'], $_POST['expires_at'] ?: null, $_POST['id']]);
            header("Location: /hris_system/?page=announcements&success=Pengumuman diperbarui");
            exit;
        }
        if ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM announcements WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            header("Location: /hris_system/?page=announcements&success=Pengumuman dihapus");
            exit;
        }
    }

    // Performance Reviews (HRD)
    if ($page === 'performance' && auth_is_hrd() && $action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
        $overall = ($_POST['work_quality'] + $_POST['productivity'] + $_POST['communication'] + $_POST['teamwork'] + $_POST['initiative']) / 5;
        $stmt = $pdo->prepare("INSERT INTO performance_reviews (user_id, reviewer_id, work_quality, productivity, communication, teamwork, initiative, overall, feedback, review_date) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, CURRENT_DATE)");
        $stmt->execute([
            $_POST['employee_id'], $uid, $_POST['work_quality'], $_POST['productivity'], 
            $_POST['communication'], $_POST['teamwork'], $_POST['initiative'], $overall, $_POST['feedback']
        ]);
        header("Location: /hris_system/?page=performance&success=Review kinerja disimpan");
        exit;
    }
    if ($page === 'performance' && auth_is_hrd() && $action === 'delete') {
        $stmt = $pdo->prepare("DELETE FROM performance_reviews WHERE id = ?");
        $stmt->execute([$_GET['id']]);
        header("Location: /hris_system/?page=performance&success=Review kinerja dihapus");
        exit;
    }

    // Calendar Actions (HRD)
    if ($page === 'calendar' && auth_is_hrd()) {
        if ($action === 'add' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $stmt = $pdo->prepare("INSERT INTO calendar_events (title, event_date, category, description) VALUES (?, ?, ?, ?)");
            $stmt->execute([$_POST['title'], $_POST['date'], $_POST['category'], $_POST['desc']]);
            header("Location: /hris_system/?page=calendar&success=Event kalender ditambahkan");
            exit;
        }
    }

    // QR & Location Management (Admin)
    if ($page === 'locations' && auth_is_hrd()) {
        if ($action === 'generate-qr' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $password = $_POST['admin_password'] ?? '';
            if (auth_verify_password($uid, $password)) {
                admin_generate_token($_POST['location_id']);
                header("Location: /hris_system/?page=locations&success=Token QR Baru Berhasil Dipublikasikan");
            } else {
                header("Location: /hris_system/?page=locations&error=Password Admin Salah!");
            }
            exit;
        }

        if ($action === 'save-location' && $_SERVER['REQUEST_METHOD'] === 'POST') {
            $id = $_POST['id'] ?? '';
            $params = [
                $_POST['name'], $_POST['latitude'], $_POST['longitude'], $_POST['radius_meters'],
                $_POST['check_in_start'], $_POST['check_in_end'], $_POST['check_out_start'], $_POST['check_out_end']
            ];

            if ($id) {
                $stmt = $pdo->prepare("UPDATE locations SET name=?, latitude=?, longitude=?, radius_meters=?, check_in_start=?, check_in_end=?, check_out_start=?, check_out_end=? WHERE id = ?");
                $params[] = $id;
                $stmt->execute($params);
                header("Location: /hris_system/?page=locations&success=Konfigurasi Kantor Diperbarui");
            } else {
                $stmt = $pdo->prepare("INSERT INTO locations (name, latitude, longitude, radius_meters, check_in_start, check_in_end, check_out_start, check_out_end) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute($params);
                header("Location: /hris_system/?page=locations&success=Kantor Baru Berhasil Didaftarkan");
            }
            exit;
        }
        if ($action === 'delete') {
            $stmt = $pdo->prepare("DELETE FROM locations WHERE id = ?");
            $stmt->execute([$_GET['id']]);
            header("Location: /hris_system/?page=locations&success=Kantor berhasil dihapus");
            exit;
        }
    }
}

// ─── Halaman Login ──────────────────────────────────────────
if (empty($_SESSION['user'])):
?>
<!DOCTYPE html>
<html class="light" lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Perkasa Abadi Logistik</title>
    <!-- Google Fonts: Manrope & Inter -->
    <link href="https://fonts.googleapis.com" rel="preconnect"/>
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Manrope:wght@700;800;900&display=swap" rel="stylesheet"/>
    <!-- Material Symbols -->
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&display=swap" rel="stylesheet"/>
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "on-background": "#191c1e",
                        "primary": "#003d9b",
                        "surface-container": "#edeef0",
                        "secondary": "#4c616c",
                        "on-primary-container": "#c4d2ff",
                        "inverse-primary": "#b2c5ff",
                        "tertiary": "#653900",
                        "tertiary-fixed-dim": "#ffb870",
                        "surface": "#f8f9fb",
                        "error": "#ba1a1a",
                        "outline-variant": "#c3c6d6",
                        "tertiary-fixed": "#ffdcbe",
                        "on-secondary-container": "#526772",
                        "on-secondary-fixed": "#071e27",
                        "on-primary": "#ffffff",
                        "surface-container-highest": "#e1e2e4",
                        "on-error-container": "#93000a",
                        "surface-container-high": "#e7e8ea",
                        "primary-container": "#0052cc",
                        "outline": "#737685",
                        "secondary-fixed-dim": "#b4cad6",
                        "on-primary-fixed-variant": "#0040a2",
                        "on-secondary": "#ffffff",
                        "on-tertiary-container": "#ffc995",
                        "surface-container-low": "#f3f4f6",
                        "secondary-fixed": "#cfe6f2",
                        "surface-tint": "#0c56d0",
                        "on-surface-variant": "#434654",
                        "on-error": "#ffffff",
                        "on-tertiary": "#ffffff",
                        "inverse-surface": "#2e3132",
                        "on-primary-fixed": "#001848",
                        "surface-variant": "#e1e2e4",
                        "on-surface": "#191c1e",
                        "on-tertiary-fixed": "#2c1600",
                        "tertiary-container": "#864d00",
                        "surface-bright": "#f8f9fb",
                        "primary-fixed-dim": "#b2c5ff",
                        "background": "#f8f9fb",
                        "secondary-container": "#cfe6f2",
                        "surface-dim": "#d9dadc",
                        "surface-container-lowest": "#ffffff",
                        "on-tertiary-fixed-variant": "#693c00",
                        "on-secondary-fixed-variant": "#354a53",
                        "inverse-on-surface": "#f0f1f3",
                        "error-container": "#ffdad6",
                        "primary-fixed": "#dae2ff"
                    },
                    borderRadius: {
                        "DEFAULT": "0.125rem",
                        "lg": "0.25rem",
                        "xl": "0.5rem",
                        "full": "0.75rem"
                    },
                    fontFamily: {
                        "headline": ["Manrope", "sans-serif"],
                        "body": ["Inter", "sans-serif"],
                        "label": ["Inter", "sans-serif"]
                    },
                    keyframes: {
                        fadeUp: {
                            "0%": { opacity: "0", transform: "translateY(20px)" },
                            "100%": { opacity: "1", transform: "translateY(0)" }
                        },
                        float: {
                            "0%, 100%": { transform: "translateY(0)" },
                            "50%": { transform: "translateY(-12px)" }
                        }
                    },
                    animation: {
                        "fade-up": "fadeUp 0.7s cubic-bezier(0.16, 1, 0.3, 1) both",
                        "float": "float 6s ease-in-out infinite"
                    }
                },
            },
        }
    </script>
</head>
<body class="bg-slate-50 min-h-screen font-body antialiased">
<main class="flex min-h-screen flex-col md:flex-row">
    <!-- Left Column: Branding & Illustration (Adaptive Navbar on Mobile) -->
    <div class="relative w-full md:w-1/2 bg-gradient-to-br from-[#002f7a] via-[#003d9b] to-[#001848] flex flex-col md:items-center md:justify-center p-6 md:p-12 text-white overflow-hidden shrink-0">
        <!-- Floating glow decorations for a premium look (Desktop only) -->
        <div class="absolute -top-32 -left-32 w-96 h-96 bg-blue-500 rounded-full blur-[120px] opacity-25 pointer-events-none hidden md:block animate-float"></div>
        <div class="absolute -bottom-32 -right-32 w-[450px] h-[450px] bg-indigo-500 rounded-full blur-[140px] opacity-20 pointer-events-none hidden md:block" style="animation: float 8s ease-in-out infinite;"></div>
        
        <!-- Content Container -->
        <div class="relative z-10 flex flex-row md:flex-col items-center md:justify-center text-left md:text-center w-full max-w-md gap-4 md:gap-0">
            <!-- Logo container (Sleek glassmorphism style) -->
            <div class="bg-white/10 backdrop-blur-md border border-white/15 p-2.5 md:p-6 rounded-2xl md:rounded-3xl shadow-xl transition-all duration-300 hover:scale-[1.03]">
                <img alt="Perkasa Abadi Logo" class="w-10 h-10 md:w-44 md:h-44 object-contain brightness-110" src="/hris_system/public/img/logo.jpg"/>
            </div>
            
            <div class="flex flex-col md:items-center">
                <h2 class="text-lg md:text-3xl font-black font-headline tracking-wider uppercase leading-none mt-0 md:mt-10 mb-0 md:mb-3 text-white">Perkasa Abadi Logistik</h2>
                <div class="hidden md:block h-0.5 w-16 bg-gradient-to-r from-blue-300 to-indigo-300 mb-6"></div>
                <p class="hidden md:block text-sm font-medium text-blue-100/80 leading-relaxed max-w-xs">
                    Logistics Excellence through People. <br/>
                    <span class="text-[10px] text-blue-200/60 uppercase tracking-widest font-bold mt-2.5 block">Empowering movement, delivering trust.</span>
                </p>
            </div>
        </div>
        
        <!-- Floating Decorative Icon in Background (Desktop Only) -->
        <div class="absolute bottom-[-8%] right-[-8%] opacity-[0.04] hidden md:block pointer-events-none select-none">
            <span class="material-symbols-outlined" style="font-family: 'Material Symbols Outlined'; font-variation-settings: 'FILL' 0, 'wght' 100, 'GRAD' 0, 'opsz' 24; font-size: 320px; line-height: 1;">precision_manufacturing</span>
        </div>
    </div>
    
    <!-- Right Column: Login Form -->
    <div class="w-full md:w-1/2 bg-slate-50/50 flex flex-col justify-center items-center p-6 md:p-16 lg:p-24">
        <div class="w-full max-w-md bg-white p-8 md:p-12 rounded-[2rem] border border-slate-100/80 shadow-[0_8px_30px_rgb(0,0,0,0.015)] transition-all duration-300 hover:shadow-[0_8px_30px_rgb(0,0,0,0.03)] animate-fade-up">
            <!-- Header -->
            <div class="mb-10 text-center md:text-left">
                <h1 class="text-3xl font-black text-slate-900 font-headline tracking-tight mb-2">Employee Access</h1>
                <p class="text-slate-400 text-sm font-medium">Enter your credentials to secure your shift.</p>
            </div>

            <!-- Functional Error Message -->
            <?php if (!empty($login_error)): ?>
            <div class="p-4 bg-red-50 border border-red-100/60 rounded-2xl text-red-600 text-xs font-bold mb-6 flex items-center gap-3 animate-pulse">
                <span class="material-symbols-outlined shrink-0" style="font-family: 'Material Symbols Outlined'; font-variation-settings: 'wght' 500;">error</span>
                <span><?= htmlspecialchars($login_error) ?></span>
            </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" class="space-y-6">
                <!-- Email / ID Field -->
                <div class="space-y-2">
                    <label class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest block" for="employee-id">Employee ID or Email</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xl pointer-events-none" style="font-family: 'Material Symbols Outlined';">mail</span>
                        <input class="w-full bg-slate-50/60 border border-slate-200/80 rounded-2xl pl-12 pr-5 py-4 text-slate-800 text-sm placeholder-slate-400 font-semibold focus:bg-white focus:border-[#003d9b] focus:ring-4 focus:ring-blue-100/50 focus:outline-none transition-all duration-200" 
                               id="employee-id" name="email" 
                               placeholder="name@perkasaabadi.com" 
                               type="email" required autofocus
                               value="<?= htmlspecialchars($_POST['email'] ?? '') ?>"/>
                    </div>
                </div>
                <!-- Password Field -->
                <div class="space-y-2">
                    <label class="text-[10px] font-extrabold text-slate-400 uppercase tracking-widest block" for="password">Password</label>
                    <div class="relative">
                        <span class="material-symbols-outlined absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 text-xl pointer-events-none" style="font-family: 'Material Symbols Outlined';">lock</span>
                        <input class="w-full bg-slate-50/60 border border-slate-200/80 rounded-2xl pl-12 pr-12 py-4 text-slate-800 text-sm placeholder-slate-400 font-semibold focus:bg-white focus:border-[#003d9b] focus:ring-4 focus:ring-blue-100/50 focus:outline-none transition-all duration-200" 
                               id="password" name="password" 
                               placeholder="••••••••" type="password" required/>
                        <button class="absolute right-4 top-1/2 -translate-y-1/2 text-slate-400 hover:text-[#003d9b] transition-colors focus:outline-none" 
                                type="button" onclick="const p = document.getElementById('password'); p.type = p.type === 'password' ? 'text' : 'password';">
                            <span class="material-symbols-outlined" style="font-family: 'Material Symbols Outlined';">visibility</span>
                        </button>
                    </div>
                </div>
                <!-- Actions Row -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2.5 cursor-pointer group">
                        <input class="w-4.5 h-4.5 rounded border-slate-300 text-[#003d9b] focus:ring-0 focus:ring-offset-0 cursor-pointer" type="checkbox"/>
                        <span class="text-xs font-bold text-slate-500 group-hover:text-slate-700 transition-colors">Remember me</span>
                    </label>
                    <a class="text-xs font-extrabold text-[#003d9b] hover:text-[#002f7a] hover:underline transition-all" href="#">Forgot Password?</a>
                </div>
                <!-- Primary Action Button -->
                <button class="w-full bg-gradient-to-r from-[#003d9b] to-[#0052cc] text-white font-bold py-4 rounded-2xl shadow-[0_8px_20px_-6px_rgba(0,61,155,0.3)] hover:shadow-[0_12px_24px_-6px_rgba(0,61,155,0.4)] hover:scale-[1.01] active:scale-[0.99] transition-all duration-200 flex items-center justify-center gap-2 text-xs uppercase tracking-widest" type="submit">
                    <span>Login to Dashboard</span>
                    <span class="material-symbols-outlined text-sm" style="font-family: 'Material Symbols Outlined';">arrow_forward</span>
                </button>
            </form>

            <!-- Demo credentials -->
            <div class="mt-8 p-5 bg-slate-50/80 rounded-2xl border border-slate-100">
                <div class="flex items-center gap-2 mb-3">
                    <span class="material-symbols-outlined text-[#003d9b] text-base" style="font-family: 'Material Symbols Outlined';">info</span>
                    <span class="text-[10px] font-black uppercase tracking-wider text-slate-700">Demo Credentials</span>
                </div>
                <div class="space-y-2.5">
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-bold text-slate-400">HRD:</span>
                        <code class="px-2 py-1 bg-white border border-slate-100 rounded-lg font-mono text-[11px] font-semibold text-slate-700">hrd@company.com / admin123</code>
                    </div>
                    <div class="flex justify-between items-center text-xs">
                        <span class="font-bold text-slate-400">Employee:</span>
                        <code class="px-2 py-1 bg-white border border-slate-100 rounded-lg font-mono text-[11px] font-semibold text-slate-700">ayu@company.com / emp123</code>
                    </div>
                </div>
            </div>

            <!-- Bottom Status & Security Notice -->
            <div class="mt-8 flex flex-col items-center gap-4">
                <div class="flex items-center gap-2 bg-emerald-50 text-emerald-700 border border-emerald-100/50 px-4 py-1.5 rounded-full">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 animate-pulse"></span>
                    <span class="text-[10px] font-black uppercase tracking-widest">System Status: Operational</span>
                </div>
                <div class="text-center">
                    <p class="text-[9px] text-slate-400 font-bold uppercase tracking-[0.18em] leading-relaxed max-w-[280px]">
                        Unauthorized access is strictly prohibited under security protocol 14-B.
                    </p>
                </div>
            </div>
        </div>
    </div>
</main>
<!-- Minimal Footer -->
<footer class="bg-white border-t border-slate-100 py-6 px-6">
    <div class="max-w-7xl mx-auto flex flex-col md:flex-row justify-between items-center gap-4 text-slate-500">
        <div class="flex items-center gap-2">
            <span class="material-symbols-outlined text-[#003d9b] text-lg" style="font-family: 'Material Symbols Outlined';">precision_manufacturing</span>
            <span class="font-bold text-slate-700 text-xs tracking-wider uppercase">Perkasa Abadi Logistik</span>
        </div>
        <div class="flex gap-6">
            <a class="hover:text-[#003d9b] transition-colors text-[10px] font-bold uppercase tracking-wider" href="#">Support</a>
            <a class="hover:text-[#003d9b] transition-colors text-[10px] font-bold uppercase tracking-wider" href="#">Privacy</a>
            <a class="hover:text-[#003d9b] transition-colors text-[10px] font-bold uppercase tracking-wider" href="#">Terms</a>
        </div>
        <p class="text-[10px] font-semibold">© 2026 Perkasa Abadi Logistik. All rights reserved.</p>
    </div>
</footer>
</body>
</html>
<?php
exit;
endif;

// ─── Main App ────────────────────────────────────────────────

auth_check();
$user = auth_user();

// Mapping halaman ke file
$page = preg_replace('/[^a-z0-9\-]/', '', $_GET['page'] ?? 'dashboard');
$page_files = [
    'dashboard'          => 'pages/dashboard.php',
    'employees'          => 'pages/employees.php',
    'attendance'         => 'pages/attendance.php',
    'attendance-reports' => 'pages/attendance-reports.php',
    'leaves'             => 'pages/leaves.php',
    'photo-approvals'    => 'pages/photo-approvals.php',
    'payroll'            => 'pages/payroll.php',
    'performance'        => 'pages/performance.php',
    'announcements'      => 'pages/announcements.php',
    'calendar'           => 'pages/calendar.php',
    'people'             => 'pages/people.php',
    'locations'          => 'pages/locations.php',
    'profile'            => 'pages/profile.php',
];

$page_titles = [
    'dashboard'          => 'Dashboard',
    'employees'          => 'Manajemen Karyawan',
    'attendance'         => 'Absensi',
    'attendance-reports' => 'Laporan Absensi',
    'leaves'             => auth_is_hrd() ? 'Approval Izin' : 'Pengajuan Izin',
    'photo-approvals'    => 'Approval Foto',
    'payroll'            => 'Penggajian',
    'performance'        => 'Performance',
    'announcements'      => 'Pengumuman',
    'calendar'           => 'Company Calendar',
    'people'             => 'Pegawai',
    'locations'          => 'QR Office',
    'profile'            => 'Profil Saya',
];

// Validasi akses HRD-only
$hrd_only = ['employees','attendance-reports','photo-approvals','locations'];
if (in_array($page, $hrd_only) && !auth_is_hrd()) {
    $page = 'dashboard';
}

// Validasi akses Absensi
if ($page === 'attendance' && ($user['can_attendance'] ?? 0) == 0) {
    $page = 'dashboard';
}

$page_file  = __DIR__ . '/' . ($page_files[$page] ?? 'pages/dashboard.php');
$page_title = $page_titles[$page] ?? 'HRIS';

// Pastikan file ada
if (!file_exists($page_file)) {
    $page_file = __DIR__ . '/pages/dashboard.php';
    $page_title = 'Dashboard';
}

require_once __DIR__ . '/includes/header.php';
require $page_file;
require_once __DIR__ . '/includes/footer.php';

<!DOCTYPE html>
<html class="<?= $_COOKIE['theme'] ?? 'dark' ?>" lang="id">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Perkasa Abadi Logistik HRIS – <?= h($page_title ?? 'Sistem SDM') ?></title>
    
    <!-- Material Symbols -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200&display=block" rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com?plugins=forms,container-queries"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
    
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    colors: {
                        "primary": "#2563EB",
                        "primary-dark": "#1D4ED8",
                        "accent": "#F59E0B",
                        "secondary": "#6B7280",
                        "surface": "var(--surface)",
                        "surface-variant": "var(--surface2)",
                        "on-surface": "var(--text-primary)",
                        "on-surface-variant": "var(--text-muted)",
                        "brand-primary": "#2563EB",
                    },
                    fontFamily: { "headline": ["Inter"], "body": ["Inter"] },
                    borderRadius: { "lg": "8px", "md": "6px", "sm": "4px" },
                },
            },
        }
    </script>
    <style>
        /* Sidebar active styling based on template */
        .sidebar-active {
            background-color: rgba(255, 255, 255, 0.1);
            border-left: 3px solid var(--primary);
            color: white !important;
        }
        
        /* Zebra table styling based on template */
        .table-zebra tr:nth-child(even) { background-color: rgba(0,0,0,0.02); }
        html.dark .table-zebra tr:nth-child(even) { background-color: rgba(255,255,255,0.02); }
        :root {
            --primary:      #2563EB;
            --primary-dark: #1D4ED8;
            --orange:       #2563EB;
            --orange-dark:  #1D4ED8;
            --accent:       #F59E0B;

            /* Light theme */
            --bg:           #F8FAFC;
            --surface:      #FFFFFF;
            --surface2:     #F1F5F9;
            --border:       #E5E7EB;
            --text-primary: #111827;
            --text-muted:   #6B7280;
            --shadow:       none;
            --sidebar-bg:   #0D0D0D;
            --header-bg:    rgba(255,255,255,.95);
            --header-border:rgba(0,0,0,.06);
        }

        /* Dark theme overrides */
        html.dark {
            --bg:           #0D0D0D;
            --surface:      #1A1A1A;
            --surface2:     #262626;
            --border:       rgba(255,255,255,.005);
            --text-primary: #F3F4F6;
            --text-muted:   #9CA3AF;
            --shadow:       0 1px 2px rgba(0,0,0,.15);
            --sidebar-bg:   #050505;
            --header-bg:    rgba(15,15,15,.95);
            --header-border:rgba(255,255,255,.06);
        }

        /* Global Input Theme Fixes */
        html.dark input, 
        html.dark textarea, 
        html.dark select {
            background-color: var(--surface2) !important;
            color: var(--text-primary) !important;
            border-color: var(--border) !important;
        }
        
        html.dark input::placeholder,
        html.dark textarea::placeholder {
            color: rgba(255,255,255,0.3) !important;
        }

        /* Date Picker Icon in Dark Mode */
        html.dark input::-webkit-calendar-picker-indicator {
            filter: invert(1);
            opacity: 0.5;
            cursor: pointer;
        }

        /* Radio Card Selection Fix */
        .peer:checked + .radio-card {
            background-color: rgba(0, 26, 75, 0.05) !important;
            border-color: var(--primary) !important;
            box-shadow: 0 0 15px rgba(0, 26, 75, 0.1);
        }
        .peer:checked + .radio-card .label-text {
            color: var(--primary) !important;
        }
        .peer:checked + .radio-card .icon-box {
            background-color: var(--primary) !important;
            color: white !important;
        }

        * { box-sizing: border-box; }
        body {
            font-family: 'Inter', sans-serif;
            font-size: 14px;
            font-weight: 400;
            -webkit-font-smoothing: antialiased;
            background-color: var(--bg);
            color: var(--text-primary);
            transition: background-color .3s ease, color .3s ease;
            overflow-x: hidden;
        }
        a { text-decoration: none; }

        /* Sidebar */
        #sidebar { background: var(--sidebar-bg) !important; transition: background .3s; }

        /* Header */
        #main-header {
            background: var(--header-bg);
            border-bottom: 1px solid var(--header-border);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            transition: background .3s, border-color .3s;
        }

        .card {
            background: var(--surface);
            border-radius: 8px;
            border: 1px solid var(--border);
            box-shadow: var(--shadow);
            padding: 16px;
            transition: background .3s, border-color .3s;
            color: var(--text-primary);
        }
        .card-hover:hover { transform: translateY(-2px); }

        /* Badges */
        .badge { display:inline-flex; align-items:center; padding:3px 8px; border-radius:4px; font-size:.7rem; font-weight:600; }
        .badge-success { background:#ECFDF5; color:#059669; }
        .badge-danger  { background:#FEF2F2; color:#DC2626; }
        .badge-warning { background:#FFFBEB; color:#D97706; }
        .badge-info    { background:#EFF6FF; color:#2563EB; }
        .badge-gray    { background:#F3F4F6; color:#6B7280;  }
        html.dark .badge-success { background:#064E3B; color:#34D399; }
        html.dark .badge-danger  { background:#450A0A; color:#F87171; }
        html.dark .badge-warning { background:#451A03; color:#FBBF24; }
        html.dark .badge-info    { background:#1E3A5F; color:#60A5FA; }
        html.dark .badge-gray    { background:#1F2937; color:#9CA3AF;  }

        /* Buttons */
        .btn {
            display:inline-flex; align-items:center; justify-content:center;
            gap:6px; padding:8px 16px; border-radius:6px;
            font-size:14px; font-weight:500; cursor:pointer;
            border:none; text-decoration:none; transition:all .2s ease; white-space:nowrap;
        }
        .btn:active { transform:scale(.97); }
        .btn-primary       { background:var(--primary); color:#fff; }
        .btn-primary:hover { background:var(--primary-dark); }
        .btn-success       { background:#16A34A; color:#fff; }
        .btn-success:hover { background:#15803D; }
        .btn-danger        { background:#DC2626; color:#fff; }
        .btn-danger:hover  { background:#B91C1C; }
        .btn-outline       { background:var(--surface); color:var(--text-primary); border:1.5px solid var(--border); }
        .btn-outline:hover { filter:brightness(.95); }

        /* Forms */
        .form-label { display:block; font-size:.8rem; font-weight:500; color:var(--text-primary); margin-bottom:6px; }
        .form-input {
            display:block; width:100%; padding:8px 12px;
            border:1px solid var(--border); border-radius:6px;
            font-size:14px; color:var(--text-primary);
            background:var(--surface); outline:none; font-family:inherit;
            transition:border-color .15s, box-shadow .15s;
        }
        .form-input:focus { border-color:var(--primary); box-shadow:0 0 0 3px rgba(0,26,75,.12); }
        select.form-input { appearance:auto; }

        /* Tables */
        .data-table { width:100%; border-collapse:collapse; font-size:.875rem; }
        .data-table th {
            padding:8px 12px; background:var(--surface2); color:var(--text-muted);
            font-size:13px; font-weight:600; letter-spacing:normal; text-align:left; border-bottom:1px solid var(--border);
        }
        .data-table td { padding:8px 12px; border-bottom:1px solid var(--border); vertical-align:middle; color:var(--text-primary); font-size:13px; }
        .data-table tbody tr:hover { background:var(--surface2); }
        .data-table tbody tr:last-child td { border-bottom:none; }

        /* Modals */
        .modal-backdrop {
            position:fixed; inset:0; background:rgba(0,0,0,.55);
            display:flex; align-items:center; justify-content:center;
            z-index:1000; padding:16px; backdrop-filter:blur(4px);
        }
        .modal-box {
            background:var(--surface); border-radius:8px; width:100%;
            max-width:540px; max-height:90vh; overflow-y:auto;
            box-shadow:0 4px 12px rgba(0,0,0,.15);
        }
        .modal-header { display:flex; align-items:center; justify-content:space-between; padding:20px 24px 16px; border-bottom:1px solid var(--border); }
        .modal-body   { padding:20px 24px; }
        .modal-footer { display:flex; justify-content:flex-end; gap:10px; padding:16px 24px 20px; border-top:1px solid var(--border); }

        /* Progress */
        .progress-bar  { background:var(--surface2); border-radius:999px; height:7px; overflow:hidden; }
        .progress-fill { height:100%; border-radius:999px; transition:width .4s ease; }

        /* Calendar days */
        .calendar-day {
            aspect-ratio:1; display:flex; align-items:center; justify-content:center;
            border-radius:6px; font-size:.875rem; cursor:pointer; font-weight:500;
            color:var(--text-primary); transition:background .15s;
        }
        .calendar-day:hover  { background:rgba(0,26,75,.08); color:var(--primary-dark); }
        .calendar-day.today  { background:var(--primary); color:#fff; font-weight:600; }

        /* Animations */
        @keyframes fadeUp {
            from { opacity:0; transform:translateY(10px); }
            to   { opacity:1; transform:translateY(0); }
        }
        .fade-up { animation:fadeUp .35s cubic-bezier(.16,1,.3,1) both; }
        .delay-1 { animation-delay:.06s; }
        .delay-2 { animation-delay:.12s; }
        .delay-3 { animation-delay:.18s; }

        /* Leave cards */
        .leave-card { position:relative; overflow:hidden; }
        .leave-card::before { content:''; position:absolute; top:0; left:0; width:3px; height:100%; }
        .leave-card.pending::before  { background:#F59E0B; }
        .leave-card.approved::before { background:#10B981; }
        .leave-card.rejected::before { background:#EF4444; }

        /* Login */
        #loginScreen { background:#fff; min-height:100vh; }

        /* Sidebar links */
        .sidebar-link { color:rgba(255,255,255,.45); transition:all .2s; }
        .sidebar-link:hover { color:rgba(255,255,255,.85); background:rgba(255,255,255,.06) !important; }

        /* Sidebar transition */
        .sidebar-transition { transition:all 0.3s cubic-bezier(0.4,0,0.2,1); }
        .no-scrollbar::-webkit-scrollbar { display:none; }

        /* Material symbols */
        .material-symbols-outlined { font-variation-settings:'FILL' 0,'wght' 400,'GRAD' 0,'opsz' 24; }

        /* Loading overlay */
        #loading-overlay {
            position:fixed; inset:0; z-index:9999; background:#fff;
            display:flex; flex-direction:column; align-items:center; justify-content:center;
            transition:opacity .5s ease, visibility .5s ease;
        }
        html.dark #loading-overlay { background:#0D0D0D; }
        .loading-spinner { border: 3px solid rgba(0,0,0,.1); border-top: 3px solid var(--primary); border-radius: 50%; width: 30px; height: 30px; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

        /* View transition */
        ::view-transition-old(root),
        ::view-transition-new(root) { animation:none; mix-blend-mode:normal; }

        /* ─── Icon Badge Colors (auto dark mode) ─── */
        .ib { display:flex; align-items:center; justify-content:center; border-radius:8px; flex-shrink:0; transition:background .3s; }
        .ib-orange { background:#FFF3E8; }
        .ib-green  { background:#ECFDF5; }
        .ib-blue   { background:#EFF6FF; }
        .ib-yellow { background:#FFFBEB; }
        .ib-red    { background:#FEF2F2; }
        .ib-purple { background:#F5F3FF; }
        .ib-indigo { background:#EEF2FF; }
        .ib-teal   { background:#F0FDFA; }
        .ib-slate  { background:#F8FAFC; }

        html.dark .ib-orange { background:rgba(255,125,0,.12); }
        html.dark .ib-green  { background:rgba(16,185,129,.10); }
        html.dark .ib-blue   { background:rgba(59,130,246,.10); }
        html.dark .ib-yellow { background:rgba(217,119,6,.10); }
        html.dark .ib-red    { background:rgba(239,68,68,.10); }
        html.dark .ib-purple { background:rgba(124,58,237,.10); }
        html.dark .ib-indigo { background:rgba(79,70,229,.10); }
        html.dark .ib-teal   { background:rgba(13,148,136,.10); }
        html.dark .ib-slate  { background:rgba(100,116,139,.10); }

        /* ─── Badge Labels (auto dark mode) ─── */
        .badge { display:inline-block; font-size:9px; font-weight:700; padding:2px 8px; border-radius:4px; transition:all .3s ease; }
        .badge-green  { background:#ECFDF5; color:#059669; }
        .badge-orange { background:#FFF3E8; color:#FF7D00; }
        .badge-yellow { background:#FFFBEB; color:#D97706; }
        .badge-red    { background:#FEF2F2; color:#DC2626; }
        .badge-blue   { background:#EFF6FF; color:#3B82F6; }

        html.dark .badge-green  { background:rgba(5,150,105,.12); color:#10B981; }
        html.dark .badge-orange { background:rgba(0,26,75,.2); color:#E6EBFC; }
        html.dark .badge-yellow { background:rgba(217,119,6, .12); color:#FBBF24; }
        html.dark .badge-red    { background:rgba(220,38,38, .12); color:#EF4444; }
        html.dark .badge-blue   { background:rgba(59,130,246,.12); color:#60A5FA; }

        /* ─── Header & Dropdown (Dark Mode) ─── */
        header#main-header { background: #fff; border-bottom: 1px solid rgba(0,0,0,.03); transition: background .3s, border .3s; }
        html.dark header#main-header { background: #111; border-bottom: 1px solid transparent; }
        
        .header-btn { color: #6B7280; transition: all .2s; }
        .header-btn:hover { background: rgba(0,0,0,.04); }
        html.dark .header-btn { color: #9CA3AF; }
        html.dark .header-btn:hover { background: rgba(255,255,255,.06); }

        .dropdown-item { transition: background .2s; }
        .dropdown-item:hover { background: #F9FAFB; }
        html.dark .dropdown-item:hover { background: rgba(255,255,255,.05); }
        
        .dropdown-danger:hover { background: #FEF2F2 !important; }
        html.dark .dropdown-danger:hover { background: rgba(239,68,68,.1) !important; }

        /* Glass theme awareness */
        .glass { background: rgba(255,255,255,.7); border: 1px solid rgba(255,255,255,.2); }
        html.dark .glass { background: rgba(20,20,20,.7); border: 1px solid rgba(255,255,255,.05); }

        /* Border utility for theme variables */
        .border-border { border-color: var(--border) !important; }

        /* Dark Tables */
        html.dark .data-table thead th { background: var(--surface2) !important; color: var(--text-muted) !important; border-bottom: 1px solid var(--border); }
        html.dark .data-table tr { border-bottom: 1px solid var(--border); }
        
        /* Dark Forms */
        html.dark .form-label { color: var(--text-muted) !important; }
    </style>
</head>
<body class="flex flex-col min-h-screen">

<div id="loading-overlay">
    <div class="loading-spinner"></div>
    <span class="mt-4 text-xs font-medium text-gray-500">Loading System...</span>
</div>

<!-- Sidebar -->
<aside id="sidebar" class="fixed left-0 top-0 h-full z-[60] hidden md:flex flex-col sidebar-transition w-64 shadow-2xl">
    <div class="px-5 py-6 flex items-center gap-3 overflow-hidden">
        <div class="w-8 h-8 rounded-md flex items-center justify-center shrink-0 bg-white p-1 border border-slate-200">
            <img src="/hris_system/public/img/logo.jpg" class="w-full h-full object-contain">
        </div>
        <span class="font-bold text-white text-sm  sidebar-text line-clamp-1">Perkasa Abadi Logistik</span>
    </div>
    <nav class="flex-1 px-4 py-4 space-y-2 overflow-y-auto no-scrollbar">
        <?php
        if (auth_is_hrd()) {
            $nav_groups = [
                'Management' => [
                    ['page'=>'dashboard',          'icon'=>'dashboard',      'label'=>'Dashboard'],
                    ['page'=>'employees',          'icon'=>'group',          'label'=>'Pegawai'],
                    ['page'=>'locations',          'icon'=>'location_on',    'label'=>'QR Office'],
                ],
                'Operations' => [
                    ['page'=>'attendance-reports', 'icon'=>'insights',       'label'=>'Riwayat Absensi'],
                    ['page'=>'photo-approvals',    'icon'=>'photo_camera',   'label'=>'Foto Rec'],
                    ['page'=>'leaves',             'icon'=>'event_busy',     'label'=>'Izin'],
                    ['page'=>'payroll',            'icon'=>'payments',       'label'=>'Gaji'],
                    ['page'=>'performance',        'icon'=>'monitoring',     'label'=>'Kinerja'],
                ],
                'System' => [
                    ['page'=>'calendar',           'icon'=>'calendar_today', 'label'=>'Kalender'],
                    ['page'=>'announcements',      'icon'=>'campaign',       'label'=>'Info'],
                ]
            ];
        } else {
            $nav_groups = [
                'Operations' => [
                    ['page'=>'dashboard',          'icon'=>'dashboard',          'label'=>'Dashboard'],
                    ['page'=>'attendance',         'icon'=>'fingerprint',        'label'=>'Absen'],
                    ['page'=>'attendance-history', 'icon'=>'calendar_month',     'label'=>'Riwayat Absensi'],
                    ['page'=>'leaves',             'icon'=>'calendar_add_on',    'label'=>'Izin'],
                    ['page'=>'performance',        'icon'=>'monitoring',         'label'=>'Kinerja'],
                ],
                'Information' => [
                    ['page'=>'people',       'icon'=>'diversity_3',        'label'=>'People'],
                    ['page'=>'calendar',     'icon'=>'calendar_today',     'label'=>'Kalender'],
                ],
                'System' => [
                    ['page'=>'payroll',      'icon'=>'account_balance_wallet','label'=>'Gaji'],
                    ['page'=>'announcements','icon'=>'campaign',           'label'=>'Info'],
                ]
            ];
        }

        $all_active_announcements = get_announcements();
        $latest_ann_id = !empty($all_active_announcements) ? $all_active_announcements[0]['id'] : 0;
        
        if (($_GET['page'] ?? '') === 'announcements') {
            $_SESSION['last_seen_ann_id'] = $latest_ann_id;
        }

        $last_seen_id = $_SESSION['last_seen_ann_id'] ?? 0;
        $has_unread = ($latest_ann_id > 0 && $latest_ann_id > $last_seen_id);

        $current = $_GET['page'] ?? 'dashboard';
        
        foreach ($nav_groups as $group_name => $items):
        ?>
            <div class="px-3 py-2 mt-2 text-[10px] font-bold text-white/40 uppercase tracking-wider sidebar-text">
                <?= $group_name ?>
            </div>
        <?php
            foreach ($items as $nav):
                $is_active = ($current === $nav['page']);
                $has_badge = ($nav['page'] === 'announcements' && $has_unread);
        ?>
        <a href="?page=<?= $nav['page'] ?>" class="flex items-center gap-3 px-3 py-2 cursor-pointer transition-all rounded-lg text-[13px] font-medium relative 
            <?= $is_active ? 'sidebar-active' : 'text-white/70 hover:bg-white/5 hover:text-white' ?>">
            <span class="material-symbols-outlined" style="font-size:18px;"><?= $nav['icon'] ?></span>
            <span class="sidebar-text"><?= h($nav['label']) ?></span>
            
            <?php if ($has_badge): ?>
                <span class="absolute right-3 top-1/2 -translate-y-1/2 w-3 h-3 bg-rose-500 rounded-full border-2 border-black animate-pulse shadow-lg shadow-rose-500/50"></span>
            <?php endif; ?>
        </a>
        <?php endforeach; endforeach; ?>
    </nav>
</aside>

<div id="main-content" class="flex-1 md:pl-64 sidebar-transition">
    <!-- Top Header Bar -->
    <header id="main-header" class="sticky top-0 z-50 flex items-center justify-between px-4 md:px-6 h-16">
        <!-- Left: toggle + logo mobile -->
        <div class="flex items-center gap-3">
            <button onclick="toggleSidebar()" class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 transition-colors hidden md:flex">
                <span class="material-symbols-outlined" style="font-size:22px;">menu</span>
            </button>
            <!-- Mobile logo -->
            <div class="flex items-center gap-2 md:hidden">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-white p-1 shadow-sm">
                    <img src="/hris_system/public/img/logo.jpg" class="w-full h-full object-contain">
                </div>
                <span data-theme-text class="text-xs font-bold" style="color: #111;">Perkasa Abadi Logistik</span>
            </div>
        </div>

        <!-- Center: Search -->
        <form action="/hris_system/" method="GET" class="hidden md:flex flex-1 max-w-lg mx-6">
            <input type="hidden" name="page" value="search">
            <div class="relative w-full">
                <span class="material-symbols-outlined absolute left-3 top-1/2 -translate-y-1/2 text-gray-400" style="font-size:18px; color:var(--text-muted);">search</span>
                <input type="text" name="q" value="<?= h($_GET['q'] ?? '') ?>" placeholder="Search employees, reports, or tasks..." 
                       style="background:var(--surface2); color:var(--text-primary); border-radius:999px; font-size:13px;"
                       class="w-full pl-10 pr-4 py-1.5 border-none font-medium focus:ring-0 placeholder:text-gray-400 transition-colors">
            </div>
        </form>

        <!-- Right: theme + profile -->
        <div class="flex items-center gap-2">
            <!-- Notifications Dropdown -->
            <div class="relative">
                <button onclick="toggleNotifDropdown(event)" id="notif-btn" class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 transition-colors relative">
                    <span class="material-symbols-outlined" style="font-size:20px;">notifications</span>
                    <span id="notif-badge" class="absolute top-2 right-2 w-2 h-2 bg-rose-500 rounded-full hidden animate-pulse"></span>
                </button>
                
                <!-- Dropdown Menu -->
                <div id="notif-dropdown" class="absolute right-0 mt-2 w-80 rounded-md shadow-lg overflow-hidden hidden transition-all duration-200" style="background: white; border: 1px solid var(--border); z-index: 100;">
                    <div class="px-4 py-2.5 border-b flex justify-between items-center bg-gray-50 dark:bg-neutral-900/30" style="border-color: var(--border);">
                        <span data-theme-text class="text-xs font-bold" style="color: var(--text-primary);">Notifikasi</span>
                        <button onclick="markAllNotifAsRead(event)" class="text-[10px] text-primary font-bold hover:underline">Tandai semua dibaca</button>
                    </div>
                    <div id="notif-list" class="max-h-72 overflow-y-auto divide-y divide-gray-100 dark:divide-neutral-800">
                        <div class="p-4 text-center text-xs text-gray-400">Loading...</div>
                    </div>
                    <div class="px-4 py-2 border-t text-center bg-gray-50 dark:bg-neutral-900/30" style="border-color: var(--border);">
                        <span class="text-[10px] text-gray-400 font-medium">HRIS Notifications Panel</span>
                    </div>
                </div>
            </div>
            
            <!-- Help (from reference) -->
            <button class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 transition-colors">
                <span class="material-symbols-outlined" style="font-size:20px;">help</span>
            </button>

            <!-- Theme Toggle -->
            <button id="theme-toggle-btn" onclick="toggleTheme(event)"
                class="w-9 h-9 flex items-center justify-center rounded-lg text-gray-500 hover:bg-gray-100 transition-colors">
                <span id="theme-icon" class="material-symbols-outlined" style="font-size:20px;">dark_mode</span>
            </button>

            <!-- Profile Dropdown -->
            <div class="relative">
                <button onclick="toggleUserDropdown()"
                    class="header-btn flex items-center gap-2.5 pl-1 pr-3 py-1 rounded-lg">
                    <!-- Avatar -->
                    <div class="w-8 h-8 rounded-full overflow-hidden shrink-0 border-none">
                        <?php if (!empty($user['photo_profile'])): ?>
                            <img src="<?= h($user['photo_profile']) ?>" alt="Profile" class="w-full h-full object-cover">
                        <?php else: ?>
                            <div class="w-full h-full flex items-center justify-center text-slate-800 text-[11px] font-bold bg-[#FDE047]">
                                <?= avatar_initials($user['name']) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                    <!-- Name -->
                    <div class="hidden sm:block text-left">
                        <p data-theme-text class="text-xs font-bold leading-none"><?= h($user['name']) ?></p>
                        <p data-theme-muted class="text-[9px] font-medium mt-0.5"><?= h($user['position'] ?? $user['role']) ?></p>
                    </div>
                    <span class="material-symbols-outlined hidden sm:block" style="font-size:16px;">expand_more</span>
                </button>

                <!-- Dropdown Menu -->
                <div id="user-dropdown" class="absolute right-0 mt-2 w-52 rounded-md shadow-sm overflow-hidden hidden" style="background: white; border: 1px solid var(--border); z-index: 100;">
                    <div class="px-4 py-3 border-b" style="border-color: rgba(0,0,0,.06);">
                        <p data-theme-text class="text-[13px] font-semibold" style="color: #111;"><?= h($user['name']) ?></p>
                        <p data-theme-muted class="text-[10px] mt-0.5" style="color: #9CA3AF;"><?= h($user['email']) ?></p>
                    </div>
                    <a href="?page=profile" class="dropdown-item flex items-center gap-3 px-4 py-3 text-sm" style="color: inherit;">
                        <span class="material-symbols-outlined" style="font-size:18px; color:var(--primary);">account_circle</span>
                        <span data-theme-text style="color: #111;">Profil Saya</span>
                    </a>
                    <a href="?action=logout" class="dropdown-item dropdown-danger flex items-center gap-3 px-4 py-3 text-sm text-red-500 border-t" style="border-color: rgba(0,0,0,.04);">
                        <span class="material-symbols-outlined" style="font-size:18px;">logout</span>
                        Keluar
                    </a>
                </div>
            </div>
        </div>
    </header>

    <main class="px-4 md:px-6 pt-4 pb-32 mx-auto w-full max-w-[1920px]">
    <?php if (isset($_GET['success'])): ?>
        <div class="p-4 mb-6 rounded-xl bg-emerald-50 border border-emerald-200/60 dark:bg-emerald-500/10 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 text-xs font-bold flex items-center justify-between gap-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-600 dark:text-emerald-400 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined" style="font-family: 'Material Symbols Outlined' !important; font-size: 18px;">check_circle</span>
                </div>
                <span><?= h($_GET['success']) ?></span>
            </div>
            <button onclick="this.parentElement.style.display='none'" class="opacity-50 hover:opacity-100 transition-opacity">
                <span class="material-symbols-outlined" style="font-family: 'Material Symbols Outlined' !important; font-size: 16px;">close</span>
            </button>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div class="p-4 mb-6 rounded-xl bg-rose-50 border border-rose-200/60 dark:bg-rose-500/10 dark:border-rose-500/20 text-rose-700 dark:text-rose-400 text-xs font-bold flex items-center justify-between gap-4 shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 rounded-lg bg-rose-500/20 text-rose-600 dark:text-rose-400 flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined" style="font-family: 'Material Symbols Outlined' !important; font-size: 18px;">error</span>
                </div>
                <span><?= h($_GET['error']) ?></span>
            </div>
            <button onclick="this.parentElement.style.display='none'" class="opacity-50 hover:opacity-100 transition-opacity">
                <span class="material-symbols-outlined" style="font-family: 'Material Symbols Outlined' !important; font-size: 16px;">close</span>
            </button>
        </div>
    <?php endif; ?>

<script>
/* ────────── Loading overlay ────────── */
window.addEventListener('load', () => {
    const overlay = document.getElementById('loading-overlay');
    overlay.style.opacity = '0';
    setTimeout(() => { overlay.style.visibility = 'hidden'; }, 500);
});

/* ────────── Modal System ────────── */
window.openModal = function(id) {
    const el = document.getElementById(id);
    if (el) {
        el.style.display = 'flex';
        // If there's an input inside, focus it
        const firstInput = el.querySelector('input, textarea, select');
        if (firstInput) setTimeout(() => firstInput.focus(), 300);
    }
}
window.closeModal = function(id) {
    const el = document.getElementById(id);
    if (el) el.style.display = 'none';
}

/* ────────── Sidebar toggle ────────── */
function toggleSidebar() {
    const sidebar = document.getElementById('sidebar');
    const content = document.getElementById('main-content');
    const texts   = document.querySelectorAll('.sidebar-text');
    if (sidebar.classList.contains('w-64')) {
        sidebar.classList.replace('w-64', 'w-20');
        content.classList.replace('md:pl-64', 'md:pl-20');
        texts.forEach(t => t.classList.add('hidden'));
    } else {
        sidebar.classList.replace('w-20', 'w-64');
        content.classList.replace('md:pl-20', 'md:pl-64');
        texts.forEach(t => t.classList.remove('hidden'));
    }
}

/* ────────── Profile dropdown ────────── */
function toggleUserDropdown() {
    document.getElementById('user-dropdown').classList.toggle('hidden');
}
window.addEventListener('click', e => {
    const dd = document.getElementById('user-dropdown');
    if (!e.target.closest('#user-dropdown') && !e.target.closest('button[onclick="toggleUserDropdown()"]')) {
        dd.classList.add('hidden');
    }
});

/* ────────── Theme System ────────── */

// Inline-style element selectors that need color updates on dark mode
// Enhanced Theme Palette (Clean, Industrial, Non-Flat)
const DARK_SURFACE  = '#1A1A1A';
const LIGHT_SURFACE = '#FFFFFF';
const DARK_SURFACE2 = '#222222';
const LIGHT_SURFACE2 = '#F3F4F6';
const DARK_TEXT     = '#F3F4F6';
const LIGHT_TEXT    = '#111111';
const DARK_MUTED    = '#98A2B3';
const LIGHT_MUTED   = '#667085';
const LIGHT_BORDER  = 'rgba(0,0,0,.06)';
const DARK_SHADOW   = '0 1px 2px rgba(0,0,0,0.15)';
const LIGHT_SHADOW  = 'none';

function applyTheme(theme) {
    const dark = theme === 'dark';
    const html = document.documentElement;

    // 1. Toggle class on <html> (activates CSS variable overrides)
    html.classList.remove('dark', 'light');
    html.classList.add(theme);

    // 2. Save preference
    document.cookie = 'theme=' + theme + ';path=/;max-age=31536000';

    // 3. Update theme toggle icon
    const icon = document.getElementById('theme-icon');
    if (icon) icon.innerText = dark ? 'light_mode' : 'dark_mode';

    // 4. Update header toggle button color
    const btn = document.getElementById('theme-toggle-btn');
    if (btn) btn.style.color = dark ? 'rgba(255,255,255,.6)' : '';

    // 5. Update all inline-styled white/light cards on the page
    //    (dashboard cards use inline styles so we update them here)
    document.querySelectorAll('[data-theme-card]').forEach(el => {
        el.style.backgroundColor = dark ? DARK_SURFACE  : LIGHT_SURFACE;
        el.style.border          = dark ? '1px solid rgba(255,255,255,0.03)' : '1px solid ' + LIGHT_BORDER;
        el.style.boxShadow       = dark ? DARK_SHADOW : LIGHT_SHADOW;
    });

    // Text-primary elements
    document.querySelectorAll('[data-theme-text]').forEach(el => {
        el.style.color = dark ? DARK_TEXT : LIGHT_TEXT;
    });

    // Muted text elements
    document.querySelectorAll('[data-theme-muted]').forEach(el => {
        el.style.color = dark ? DARK_MUTED : LIGHT_MUTED;
    });

    // Surface2 backgrounds (menu cards, table rows)
    document.querySelectorAll('[data-theme-surface2]').forEach(el => {
        el.style.background = dark ? DARK_SURFACE2 : LIGHT_SURFACE2;
    });

    // Menu cards re-init hover state (reset any stuck orange)
    document.querySelectorAll('[data-menu-card]').forEach(el => {
        el.style.background = dark ? DARK_SURFACE : LIGHT_SURFACE;
        el.style.borderColor = dark ? 'rgba(255,255,255,.07)' : 'rgba(0,0,0,.05)';
        const label = el.querySelector('.mc-label');
        const ic    = el.querySelector('.mc-icon');
        if (label) label.style.color = dark ? '#E5E7EB' : '#374151';
        // icon keeps its accent color — intentional
    });

    // Dropdown menu
    const dd = document.getElementById('user-dropdown');
    if (dd) {
        dd.style.background = dark ? DARK_SURFACE : LIGHT_SURFACE;
        dd.style.borderColor = dark ? 'rgba(255,255,255,.07)' : 'rgba(0,0,0,.05)';
    }
    const nd = document.getElementById('notif-dropdown');
    if (nd) {
        nd.style.background = dark ? DARK_SURFACE : LIGHT_SURFACE;
        nd.style.borderColor = dark ? 'rgba(255,255,255,.07)' : 'rgba(0,0,0,.05)';
    }
}

function toggleTheme(event) {
    const isDark   = document.documentElement.classList.contains('dark');
    const newTheme = isDark ? 'light' : 'dark';

    if (!document.startViewTransition) {
        applyTheme(newTheme);
        return;
    }

    const x = event.clientX, y = event.clientY;
    const endRadius = Math.hypot(Math.max(x, innerWidth - x), Math.max(y, innerHeight - y));

    const transition = document.startViewTransition(() => { applyTheme(newTheme); });

    transition.ready.then(() => {
        document.documentElement.animate(
            { clipPath: [`circle(0 at ${x}px ${y}px)`, `circle(${endRadius}px at ${x}px ${y}px)`] },
            { duration: 450, easing: 'ease-in-out', pseudoElement: '::view-transition-new' }
        );
    });
}

/* ────────── Notifications System ────────── */
function toggleNotifDropdown(e) {
    if (e) e.stopPropagation();
    const dropdown = document.getElementById('notif-dropdown');
    dropdown.classList.toggle('hidden');
    
    // Hide user dropdown if open
    const userDD = document.getElementById('user-dropdown');
    if (userDD) userDD.classList.add('hidden');
    
    if (!dropdown.classList.contains('hidden')) {
        loadNotifications();
    }
}

function loadNotifications() {
    fetch('/hris_system/api/?action=get-notifications')
        .then(response => response.json())
        .then(data => {
            updateNotifBadge(data.unread_count);
            
            const list = document.getElementById('notif-list');
            list.innerHTML = '';
            
            if (data.notifications.length === 0) {
                list.innerHTML = `<div class="p-8 text-center text-xs text-gray-400 dark:text-neutral-500">Tidak ada notifikasi</div>`;
                return;
            }
            
            data.notifications.forEach(notif => {
                const isRead = parseInt(notif.is_read) === 1;
                const bgClass = isRead ? '' : 'bg-blue-50/40 dark:bg-blue-950/10';
                const indicatorClass = isRead ? 'hidden' : 'w-2 h-2 rounded-full bg-blue-500 shrink-0';
                
                // Determine icon based on title or content keywords
                let icon = 'notifications';
                let iconColor = 'text-primary';
                
                const titleLower = notif.title.toLowerCase();
                if (titleLower.includes('izin') || titleLower.includes('leave') || titleLower.includes('absence')) {
                    icon = 'event_note';
                    iconColor = 'text-amber-500';
                } else if (titleLower.includes('absen') || titleLower.includes('hadir') || titleLower.includes('attendance')) {
                    icon = 'photo_camera';
                    iconColor = 'text-emerald-500';
                } else if (titleLower.includes('pengumuman') || titleLower.includes('announce')) {
                    icon = 'campaign';
                    iconColor = 'text-indigo-500';
                } else if (titleLower.includes('kinerja') || titleLower.includes('performance')) {
                    icon = 'assignment';
                    iconColor = 'text-pink-500';
                }
                
                const item = document.createElement('div');
                item.className = `p-3 flex items-start gap-3 hover:bg-gray-50/80 dark:hover:bg-neutral-800/40 transition-colors cursor-pointer border-b border-gray-100 dark:border-neutral-800/60 last:border-none ${bgClass}`;
                item.onclick = () => handleNotifClick(notif.id, notif.link);
                
                item.innerHTML = `
                    <div class="w-8 h-8 rounded-lg flex items-center justify-center bg-gray-100 dark:bg-neutral-800 ${iconColor} shrink-0">
                        <span class="material-symbols-outlined" style="font-size: 18px;">${icon}</span>
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex justify-between items-baseline mb-0.5">
                            <p class="text-[11px] font-bold truncate dark:text-neutral-200" style="color: var(--text-primary);">${notif.title}</p>
                            <span class="text-[8px] text-gray-400 dark:text-neutral-500 shrink-0 ml-2">${notif.time_ago}</span>
                        </div>
                        <p class="text-[10px] text-gray-500 dark:text-neutral-400 line-clamp-2 leading-relaxed">${notif.message}</p>
                    </div>
                    <div class="${indicatorClass}"></div>
                `;
                list.appendChild(item);
            });
        })
        .catch(err => {
            console.error('Error fetching notifications:', err);
            const list = document.getElementById('notif-list');
            list.innerHTML = `<div class="p-4 text-center text-xs text-red-500">Gagal memuat notifikasi</div>`;
        });
}

function updateNotifBadge(count) {
    const badge = document.getElementById('notif-badge');
    if (count > 0) {
        badge.classList.remove('hidden');
    } else {
        badge.classList.add('hidden');
    }
}

function handleNotifClick(id, link) {
    fetch(`/hris_system/api/?action=mark-notification-read&id=${id}`)
        .then(() => {
            if (link) {
                window.location.href = `/hris_system/${link}`;
            } else {
                loadNotifications();
            }
        })
        .catch(() => {
            if (link) window.location.href = `/hris_system/${link}`;
        });
}

function markAllNotifAsRead(event) {
    if (event) event.stopPropagation();
    fetch('/hris_system/api/?action=mark-all-notifications-read')
        .then(() => {
            loadNotifications();
        })
        .catch(err => console.error(err));
}

// Auto update badge on page load and poll every 30 seconds
function checkNotifBadgeCount() {
    fetch('/hris_system/api/?action=get-notifications')
        .then(response => response.json())
        .then(data => {
            updateNotifBadge(data.unread_count);
        })
        .catch(err => console.error(err));
}

// Add event listener to check count and handle outside click
document.addEventListener('DOMContentLoaded', () => {
    checkNotifBadgeCount();
    setInterval(checkNotifBadgeCount, 30000);
    
    // Close notification dropdown when clicking outside
    window.addEventListener('click', e => {
        const nd = document.getElementById('notif-dropdown');
        if (nd && !e.target.closest('#notif-dropdown') && !e.target.closest('#notif-btn')) {
            nd.classList.add('hidden');
        }
    });
});

/* ────────── Init on load ────────── */
document.addEventListener('DOMContentLoaded', () => {
    const savedTheme = document.cookie.split(';').find(c => c.trim().startsWith('theme='));
    const theme = savedTheme ? savedTheme.split('=')[1].trim() : 'light';
    applyTheme(theme);
});
</script>

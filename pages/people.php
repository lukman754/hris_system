<?php
// pages/people.php – Modern Directory Redesign
$pdo = db();
$today = date('Y-m-d');
$all_people = $pdo->query("
    SELECT u.*, 
    (SELECT COUNT(*) FROM attendance a WHERE a.user_id = u.id AND a.attendance_date = '$today' AND a.attendance_flow = 'in' LIMIT 1) as is_present,
    (SELECT attendance_time FROM attendance a WHERE a.user_id = u.id AND a.attendance_date = '$today' AND a.attendance_flow = 'in' ORDER BY attendance_time ASC LIMIT 1) as check_in_time
    FROM users u
    ORDER BY name ASC
")->fetchAll();

$total_staff = count($all_people);
$total_present = count(array_filter($all_people, fn($p) => $p['is_present'] > 0));
$departments = array_unique(array_column($all_people, 'department'));

function format_wa_link($phone) {
    if (empty($phone)) return null;
    $clean = preg_replace('/[^0-9]/', '', $phone);
    if (str_starts_with($clean, '0')) {
        $clean = '62' . substr($clean, 1);
    } else if (!str_starts_with($clean, '62')) {
        $clean = '62' . $clean;
    }
    return "https://wa.me/" . $clean;
}
?>

<!-- Header Section -->
<section class="mb-8">
    <div class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <h1 class="text-4xl font-black tracking-tighter leading-none mb-2 text-on-surface">People Directory</h1>
            <div class="flex items-center gap-2">
                <span class="w-1.5 h-1.5 rounded-full bg-primary animate-pulse"></span>
                <p class="text-[9px] font-black uppercase tracking-[0.2em] opacity-80 text-on-surface-variant">Managing <?= $total_staff ?> Team Members</p>
            </div>
        </div>
        
        <!-- Search Bar (Simulated) -->
        <div data-theme-card class="bg-surface p-1.5 rounded-2xl shadow-sm flex items-center gap-2 w-full md:max-w-xs border border-border">
            <div class="pl-3 text-on-surface-variant opacity-30">
                <span class="material-symbols-outlined text-[20px]">search</span>
            </div>
            <input type="text" placeholder="Search name or position..." class="bg-transparent border-none text-xs font-bold w-full focus:ring-0 placeholder:text-on-surface-variant/30 placeholder:uppercase placeholder:tracking-widest text-on-surface">
        </div>
    </div>
</section>

<!-- Stats Grid -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
    <div class="p-4 rounded-2xl shadow-sm relative overflow-hidden group" style="background: var(--surface);">
        <div data-theme-muted class="text-[8px] font-black uppercase tracking-widest opacity-40 mb-1">Total Workforce</div>
        <div data-theme-text class="text-2xl font-black tracking-tighter leading-none"><?= $total_staff ?></div>
        <div class="absolute -right-2 -bottom-2 opacity-5 scale-150 rotate-12 group-hover:rotate-0 transition-all duration-500">
            <span class="material-symbols-outlined text-6xl">group</span>
        </div>
    </div>
    <div class="p-4 rounded-2xl shadow-sm relative overflow-hidden group" style="background: var(--surface);">
        <div data-theme-muted class="text-[8px] font-black uppercase tracking-widest opacity-40 mb-1">Active Today</div>
        <div class="text-2xl font-black tracking-tighter leading-none text-emerald-500"><?= $total_present ?></div>
        <div class="absolute -right-2 -bottom-2 opacity-5 scale-150 rotate-12 group-hover:rotate-0 transition-all duration-500">
            <span class="material-symbols-outlined text-6xl text-emerald-500">bolt</span>
        </div>
    </div>
    <div class="p-4 rounded-2xl shadow-sm relative overflow-hidden group" style="background: var(--surface);">
        <div data-theme-muted class="text-[8px] font-black uppercase tracking-widest opacity-40 mb-1">Departments</div>
        <div data-theme-text class="text-2xl font-black tracking-tighter leading-none"><?= count($departments) ?></div>
        <div class="absolute -right-2 -bottom-2 opacity-5 scale-150 rotate-12 group-hover:rotate-0 transition-all duration-500">
            <span class="material-symbols-outlined text-6xl">account_tree</span>
        </div>
    </div>
    <div class="p-4 rounded-2xl shadow-sm relative overflow-hidden group" style="background: var(--surface);">
        <div data-theme-muted class="text-[8px] font-black uppercase tracking-widest opacity-40 mb-1">Attendance Rate</div>
        <div data-theme-text class="text-2xl font-black tracking-tighter leading-none"><?= $total_staff > 0 ? round(($total_present / $total_staff) * 100) : 0 ?>%</div>
        <div class="absolute -right-2 -bottom-2 opacity-5 scale-150 rotate-12 group-hover:rotate-0 transition-all duration-500">
            <span class="material-symbols-outlined text-6xl">monitoring</span>
        </div>
    </div>
</div>

<!-- People Grid -->
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 pb-20">
    <?php foreach ($all_people as $p): 
        $wa = format_wa_link($p['phone_number']);
        $is_online = $p['is_present'] > 0;
    ?>
    <div data-theme-card class="bg-surface p-5 rounded-2xl shadow-sm relative group hover:shadow-xl hover:-translate-y-1 transition-all duration-300 border border-border">
        <!-- Present Badge -->
        <div class="absolute top-4 right-4 flex items-center gap-1.5 px-2 py-1 rounded-full <?= $is_online ? 'bg-emerald-500/10 text-emerald-500' : 'bg-surface-variant text-on-surface-variant/40' ?>">
            <span class="w-1.5 h-1.5 rounded-full <?= $is_online ? 'bg-emerald-500 animate-pulse' : 'bg-on-surface-variant/40' ?>"></span>
            <span class="text-[8px] font-black uppercase tracking-widest"><?= $is_online ? 'Present' : 'Away' ?></span>
        </div>

        <div class="flex items-center gap-4 mb-6">
            <!-- Profile Pic -->
            <div class="relative">
                <div class="w-16 h-16 rounded-2xl overflow-hidden shadow-md group-hover:rotate-3 transition-transform duration-500 border border-border">
                    <?php if (!empty($p['photo_profile'])): ?>
                        <img src="<?= h($p['photo_profile']) ?>" alt="Profile" class="w-full h-full object-cover">
                    <?php else: ?>
                        <div class="w-full h-full bg-surface-variant flex items-center justify-center text-primary font-black text-lg">
                            <?= avatar_initials($p['name']) ?>
                        </div>
                    <?php endif; ?>
                </div>
                <?php if ($is_online): ?>
                    <div class="absolute -bottom-1 -right-1 w-4 h-4 bg-emerald-500 border-2 rounded-full border-surface"></div>
                <?php endif; ?>
            </div>

            <!-- Info -->
            <div class="min-w-0">
                <h3 class="text-[17px] font-black tracking-tight leading-tight truncate text-on-surface"><?= h($p['name']) ?></h3>
                <p class="text-[9px] font-black uppercase tracking-widest opacity-40 mt-1 truncate text-on-surface-variant"><?= h($p['position']) ?></p>
            </div>
        </div>

        <div class="flex items-center justify-between pt-4">
            <div class="flex items-center gap-1">
                <div class="px-2 py-1 bg-surface-variant rounded text-[8px] font-black uppercase tracking-wider text-on-surface-variant/70 italic"><?= h($p['department']) ?></div>
            </div>
            
            <div class="flex items-center gap-2">
                <a href="mailto:<?= h($p['email']) ?>" class="w-9 h-9 flex items-center justify-center bg-surface-variant rounded-xl text-on-surface-variant/40 hover:text-blue-500 transition-all active:scale-90">
                    <span class="material-symbols-outlined text-[18px]">alternate_email</span>
                </a>
                <?php if ($wa): ?>
                <a href="<?= $wa ?>" target="_blank" class="w-9 h-9 flex items-center justify-center bg-emerald-500/10 text-emerald-500 rounded-xl hover:bg-emerald-500 hover:text-white transition-all active:scale-90 flex items-center justify-center border border-emerald-500/10">
                    <i class="fab fa-whatsapp text-[16px]"></i>
                </a>
                <?php endif; ?>
            </div>
        </div>

        <?php if ($is_online && $p['check_in_time']): ?>
        <div class="mt-4 flex items-center gap-2 px-2 py-1.5 bg-emerald-500/[0.03] rounded-lg border border-emerald-500/5">
            <span class="material-symbols-outlined text-[12px] text-emerald-500/50">login</span>
            <span class="text-[8px] font-bold text-emerald-600 uppercase tracking-tighter">Clocked in at <?= date('H:i', strtotime($p['check_in_time'])) ?></span>
        </div>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>

<style>
    /* Smooth hover effect for cards */
    [data-theme-card] {
        transition: transform 0.3s cubic-bezier(0.34, 1.56, 0.64, 1), box-shadow 0.3s ease, background 0.3s ease, border 0.3s ease;
    }
</style>

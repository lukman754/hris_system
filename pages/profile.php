<?php
// pages/profile.php – User Profile & Security Settings
$user_id = $user['id'];
$initials = avatar_initials($user['name']);
?>

<div class="space-y-8 performance-page-container fade-up">
    
    <!-- ══ Header Section ══ -->
    <header class="flex flex-col md:flex-row md:items-end justify-between gap-6 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-12 h-12 rounded-lg bg-primary/10 text-primary flex items-center justify-center shrink-0">
                    <span class="material-symbols-outlined text-3xl font-bold">account_circle</span>
                </div>
                <h1 data-theme-text class="text-3xl font-bold leading-none">Profil Saya</h1>
            </div>
            <p data-theme-muted class="text-[10px] font-bold ml-1 opacity-50">Kelola Akun & Pengaturan Keamanan</p>
        </div>
    </header>

    <!-- ══ Main Layout ══ -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
        
        <!-- Left Side: Profile Summary Card (4 Columns) -->
        <div class="lg:col-span-5 space-y-6">
            <div data-theme-card class="p-8 rounded-2xl relative overflow-hidden flex flex-col items-center text-center shadow-lg border border-border">
                <!-- Top Accent Glow -->
                <div class="absolute top-0 inset-x-0 h-2 bg-gradient-to-r from-blue-500 via-indigo-500 to-primary"></div>
                
                <!-- Profile Avatar -->
                <div class="w-24 h-24 rounded-full overflow-hidden mb-5 border-4 border-primary/20 p-1 bg-surface relative group">
                    <?php if (!empty($user['photo_profile'])): ?>
                        <img src="<?= h($user['photo_profile']) ?>" class="w-full h-full object-cover rounded-full">
                    <?php else: ?>
                        <div class="w-full h-full bg-primary/10 text-primary flex items-center justify-center font-bold text-3xl rounded-full">
                            <?= h($initials) ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Basic Info -->
                <h3 data-theme-text class="text-xl font-bold leading-tight"><?= h($user['name']) ?></h3>
                <p data-theme-muted class="text-xs font-semibold opacity-60 mt-1 capitalize"><?= h($user['position'] ?? $user['role']) ?></p>
                
                <!-- Status Badge -->
                <div class="mt-4">
                    <span class="px-3 py-1 bg-emerald-500/10 text-emerald-500 dark:text-emerald-400 rounded-full text-[10px] font-bold uppercase tracking-wider">
                        Akun Aktif
                    </span>
                </div>

                <!-- Detailed Information List -->
                <div class="w-full mt-8 border-t border-border/60 pt-6 space-y-4 text-left">
                    <div class="flex items-center justify-between text-xs py-1">
                        <span data-theme-muted class="opacity-50 font-semibold">ID Karyawan</span>
                        <span data-theme-text class="font-bold font-mono text-slate-500"><?= h($user['id']) ?></span>
                    </div>
                    <div class="flex items-center justify-between text-xs py-1">
                        <span data-theme-muted class="opacity-50 font-semibold">Email Resmi</span>
                        <span data-theme-text class="font-bold"><?= h($user['email']) ?></span>
                    </div>
                    <div class="flex items-center justify-between text-xs py-1">
                        <span data-theme-muted class="opacity-50 font-semibold">No. HP / Telepon</span>
                        <span data-theme-text class="font-bold"><?= h($user['phone_number'] ?: '-') ?></span>
                    </div>
                    <div class="flex items-center justify-between text-xs py-1">
                        <span data-theme-muted class="opacity-50 font-semibold">Departemen</span>
                        <span class="px-2.5 py-0.5 bg-surface-variant rounded-md text-[10px] font-bold text-on-surface-variant border border-border/40"><?= h($user['department'] ?? 'Umum') ?></span>
                    </div>
                    <div class="flex items-center justify-between text-xs py-1">
                        <span data-theme-muted class="opacity-50 font-semibold">Tanggal Masuk</span>
                        <span data-theme-text class="font-bold"><?= format_date($user['join_date'] ?? null) ?></span>
                    </div>
                    <?php if (isset($user['salary']) && $user['salary'] > 0): ?>
                    <div class="flex items-center justify-between text-xs py-1">
                        <span data-theme-muted class="opacity-50 font-semibold">Gaji Pokok</span>
                        <span data-theme-text class="font-bold"><?= format_rupiah($user['salary']) ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right Side: Change Password Form (7 Columns) -->
        <div class="lg:col-span-7">
            <div data-theme-card class="p-8 rounded-2xl relative overflow-hidden shadow-lg border border-border h-full">
                <div class="flex items-center gap-3 mb-6">
                    <div class="w-8 h-8 rounded-lg bg-amber-500/10 text-amber-500 flex items-center justify-center">
                        <span class="material-symbols-outlined text-lg">shield</span>
                    </div>
                    <div>
                        <h4 data-theme-text class="text-base font-bold">Ganti Password Keamanan</h4>
                        <p data-theme-muted class="text-[9px] font-semibold opacity-40 uppercase tracking-wide">Perbarui password untuk menjaga keamanan akun Anda</p>
                    </div>
                </div>

                <!-- Form -->
                <form method="POST" action="?page=profile&action=change-password" class="space-y-6">
                    
                    <!-- Old Password -->
                    <div class="space-y-2 text-left relative">
                        <label data-theme-muted class="text-[10px] font-bold opacity-50 ml-1 uppercase tracking-wider">Password Lama</label>
                        <div class="relative">
                            <span class="material-symbols-outlined text-slate-400 absolute left-4 top-1/2 -translate-y-1/2 text-lg">lock</span>
                            <input name="old_password" type="password" required
                                class="w-full pl-11 pr-4 py-3 bg-surface-variant border border-border/80 rounded-xl text-xs font-bold focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all"
                                placeholder="Masukkan password saat ini">
                        </div>
                    </div>

                    <!-- New Password -->
                    <div class="space-y-2 text-left relative">
                        <label data-theme-muted class="text-[10px] font-bold opacity-50 ml-1 uppercase tracking-wider">Password Baru</label>
                        <div class="relative">
                            <span class="material-symbols-outlined text-slate-400 absolute left-4 top-1/2 -translate-y-1/2 text-lg">vpn_key</span>
                            <input name="new_password" type="password" required minlength="8"
                                class="w-full pl-11 pr-4 py-3 bg-surface-variant border border-border/80 rounded-xl text-xs font-bold focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all"
                                placeholder="Min. 8 karakter">
                        </div>
                    </div>

                    <!-- Confirm Password -->
                    <div class="space-y-2 text-left relative">
                        <label data-theme-muted class="text-[10px] font-bold opacity-50 ml-1 uppercase tracking-wider">Konfirmasi Password Baru</label>
                        <div class="relative">
                            <span class="material-symbols-outlined text-slate-400 absolute left-4 top-1/2 -translate-y-1/2 text-lg">check_circle</span>
                            <input name="confirm_password" type="password" required minlength="8"
                                class="w-full pl-11 pr-4 py-3 bg-surface-variant border border-border/80 rounded-xl text-xs font-bold focus:ring-4 focus:ring-primary/10 focus:border-primary outline-none transition-all"
                                placeholder="Ketik ulang password baru">
                        </div>
                    </div>

                    <!-- Guidelines -->
                    <div class="p-4 rounded-xl bg-slate-50 dark:bg-neutral-800/40 border border-slate-100 dark:border-neutral-800 text-[10px] font-semibold text-slate-400 dark:text-neutral-500 space-y-1">
                        <p class="font-bold text-slate-500 dark:text-neutral-400 mb-1 uppercase tracking-wider">Ketentuan Keamanan:</p>
                        <ul class="list-disc pl-4 space-y-1">
                            <li>Password lama harus sesuai dengan kredensial yang aktif saat ini.</li>
                            <li>Password baru tidak boleh sama dengan password lama demi keamanan.</li>
                            <li>Password minimal terdiri dari <strong>8 karakter</strong>.</li>
                        </ul>
                    </div>

                    <!-- Action Button -->
                    <div class="pt-2">
                        <button type="submit" class="w-full py-4 bg-primary text-white hover:bg-primary-dark rounded-xl text-xs font-bold shadow-xl shadow-primary/20 active:scale-95 transition-all flex items-center justify-center gap-2">
                            <span class="material-symbols-outlined text-base">save</span>
                            <span>Simpan Perubahan Password</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

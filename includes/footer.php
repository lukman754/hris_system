    </main>

    <!-- Bottom Navigation (Integrated Executive Style) -->
    <nav class="fixed bottom-0 left-0 w-full z-50 px-4 pb-6 pt-3 bg-surface/80 backdrop-blur-xl rounded-t-xl border-t border-border shadow-[0_-10px_30px_rgba(0,0,0,0.1)] md:hidden transition-all duration-300">
        <div class="flex justify-between items-center max-w-lg mx-auto">
            
            <!-- Home -->
            <a class="flex-1 flex flex-col items-center justify-center py-2 transition-all active:scale-95 group" href="/hris_system/?page=dashboard">
                <div class="flex flex-col items-center gap-1.5 <?= ($_GET['page'] ?? 'dashboard') === 'dashboard' ? 'text-primary' : 'text-on-surface/30' ?>">
                    <span class="material-symbols-outlined text-[24px]" style="<?= ($_GET['page'] ?? 'dashboard') === 'dashboard' ? "font-variation-settings: 'FILL' 1;" : "" ?>">home</span>
                    <span class="text-[8px] font-bold   transition-opacity <?= ($_GET['page'] ?? 'dashboard') === 'dashboard' ? 'opacity-100' : 'opacity-40' ?>">Home</span>
                </div>
            </a>

            <!-- Calendar -->
            <a class="flex-1 flex flex-col items-center justify-center py-2 transition-all active:scale-95 group" href="/hris_system/?page=calendar">
                <div class="flex flex-col items-center gap-1.5 <?= ($_GET['page'] ?? '') === 'calendar' ? 'text-primary' : 'text-on-surface/30' ?>">
                    <span class="material-symbols-outlined text-[24px]" style="<?= ($_GET['page'] ?? '') === 'calendar' ? "font-variation-settings: 'FILL' 1;" : "" ?>">calendar_month</span>
                    <span class="text-[8px] font-bold   transition-opacity <?= ($_GET['page'] ?? '') === 'calendar' ? 'opacity-100' : 'opacity-40' ?>">Events</span>
                </div>
            </a>

            <!-- CENTER BUTTON: SCAN QR (Employee) or STAFF (HRD) -->
            <?php if (auth_is_hrd()): ?>
            <a class="flex-1 flex justify-center transition-all active:scale-90" href="/hris_system/?page=employees">
                <div class="w-16 h-16 rounded-lg bg-primary flex items-center justify-center text-white shadow-2xl shadow-primary/20 overflow-hidden relative group">
                    <span class="material-symbols-outlined text-[28px] font-bold z-10">groups</span>
                    <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </div>
            </a>
            <?php else: ?>
            <a class="flex-1 flex justify-center transition-all active:scale-90" href="/hris_system/?page=attendance">
                <div class="w-16 h-16 rounded-lg bg-primary flex items-center justify-center text-white shadow-2xl shadow-primary/20 overflow-hidden relative group">
                    <span class="material-symbols-outlined text-[28px] font-bold z-10">qr_code_scanner</span>
                    <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </div>
            </a>
            <?php endif; ?>

            <!-- Gaji -->
            <a class="flex-1 flex flex-col items-center justify-center py-2 transition-all active:scale-95 group" href="/hris_system/?page=payroll">
                <div class="flex flex-col items-center gap-1.5 <?= ($_GET['page'] ?? '') === 'payroll' ? 'text-primary' : 'text-on-surface/30' ?>">
                    <span class="material-symbols-outlined text-[24px]" style="<?= ($_GET['page'] ?? '') === 'payroll' ? "font-variation-settings: 'FILL' 1;" : "" ?>">account_balance_wallet</span>
                    <span class="text-[8px] font-bold   transition-opacity <?= ($_GET['page'] ?? '') === 'payroll' ? 'opacity-100' : 'opacity-40' ?>">Gaji</span>
                </div>
            </a>

            <!-- People (Replaced Logout) -->
            <a class="flex-1 flex flex-col items-center justify-center py-2 transition-all active:scale-95 group" href="/hris_system/?page=people">
                <div class="flex flex-col items-center gap-1.5 <?= ($_GET['page'] ?? '') === 'people' ? 'text-primary' : 'text-on-surface/30' ?>">
                    <span class="material-symbols-outlined text-[24px]" style="<?= ($_GET['page'] ?? '') === 'people' ? "font-variation-settings: 'FILL' 1;" : "" ?>">diversity_3</span>
                    <span class="text-[8px] font-bold   transition-opacity <?= ($_GET['page'] ?? '') === 'people' ? 'opacity-100' : 'opacity-40' ?>">People</span>
                </div>
            </a>
            
        </div>
    </nav>

</body>
</html>

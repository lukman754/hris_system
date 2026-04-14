    </main>

    <!-- Bottom Navigation (Integrated Executive Style) -->
    <nav class="fixed bottom-0 left-0 w-full z-50 px-6 pb-8 pt-4 bg-surface-container-low/95 backdrop-blur-3xl rounded-t-[2.5rem] border-t border-outline-variant/10 shadow-[0_-15px_60px_rgba(0,0,0,0.3)] md:hidden transition-colors duration-300">
        <div class="flex justify-between items-center max-w-lg mx-auto">
            
            <!-- Home -->
            <a class="flex-1 flex flex-col items-center justify-center py-2 transition-all active:scale-95 group" href="/hris_system/?page=dashboard">
                <div class="flex flex-col items-center gap-1.5 <?= ($_GET['page'] ?? 'dashboard') === 'dashboard' ? 'text-primary' : 'text-on-surface/30' ?>">
                    <span class="material-symbols-outlined text-[24px]" style="<?= ($_GET['page'] ?? 'dashboard') === 'dashboard' ? "font-variation-settings: 'FILL' 1;" : "" ?>">home</span>
                    <span class="text-[8px] font-black uppercase tracking-[0.2em] transition-opacity <?= ($_GET['page'] ?? 'dashboard') === 'dashboard' ? 'opacity-100' : 'opacity-40' ?>">Home</span>
                </div>
            </a>

            <!-- Calendar -->
            <a class="flex-1 flex flex-col items-center justify-center py-2 transition-all active:scale-95 group" href="/hris_system/?page=calendar">
                <div class="flex flex-col items-center gap-1.5 <?= ($_GET['page'] ?? '') === 'calendar' ? 'text-primary' : 'text-on-surface/30' ?>">
                    <span class="material-symbols-outlined text-[24px]" style="<?= ($_GET['page'] ?? '') === 'calendar' ? "font-variation-settings: 'FILL' 1;" : "" ?>">calendar_month</span>
                    <span class="text-[8px] font-black uppercase tracking-[0.2em] transition-opacity <?= ($_GET['page'] ?? '') === 'calendar' ? 'opacity-100' : 'opacity-40' ?>">Events</span>
                </div>
            </a>

            <!-- SCAN QR -->
            <a class="flex-1 flex justify-center transition-all active:scale-90" href="/hris_system/?page=attendance">
                <div class="w-16 h-16 rounded-[1.25rem] bg-primary flex items-center justify-center text-white shadow-2xl shadow-primary/20 overflow-hidden relative group">
                    <span class="material-symbols-outlined text-[28px] font-black z-10">qr_code_scanner</span>
                    <div class="absolute inset-0 bg-white/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                </div>
            </a>

            <!-- News/Info -->
            <a class="flex-1 flex flex-col items-center justify-center py-2 transition-all active:scale-95 group" href="/hris_system/?page=announcements">
                <div class="flex flex-col items-center gap-1.5 <?= ($_GET['page'] ?? '') === 'announcements' ? 'text-primary' : 'text-on-surface/30' ?>">
                    <span class="material-symbols-outlined text-[24px]" style="<?= ($_GET['page'] ?? '') === 'announcements' ? "font-variation-settings: 'FILL' 1;" : "" ?>">campaign</span>
                    <span class="text-[8px] font-black uppercase tracking-[0.2em] transition-opacity <?= ($_GET['page'] ?? '') === 'announcements' ? 'opacity-100' : 'opacity-40' ?>">News</span>
                </div>
            </a>

            <!-- People (Replaced Logout) -->
            <a class="flex-1 flex flex-col items-center justify-center py-2 transition-all active:scale-95 group" href="/hris_system/?page=people">
                <div class="flex flex-col items-center gap-1.5 <?= ($_GET['page'] ?? '') === 'people' ? 'text-primary' : 'text-on-surface/30' ?>">
                    <span class="material-symbols-outlined text-[24px]" style="<?= ($_GET['page'] ?? '') === 'people' ? "font-variation-settings: 'FILL' 1;" : "" ?>">diversity_3</span>
                    <span class="text-[8px] font-black uppercase tracking-[0.2em] transition-opacity <?= ($_GET['page'] ?? '') === 'people' ? 'opacity-100' : 'opacity-40' ?>">People</span>
                </div>
            </a>
            
        </div>
    </nav>

</body>
</html>

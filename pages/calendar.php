<?php
// pages/calendar.php – Kalender Perusahaan
$db_events = get_calendar_events();
$employees = get_employees();
$today     = date('Y-m-d');
$year      = (int)($_GET['cal_year']  ?? date('Y'));
$month     = (int)($_GET['cal_month'] ?? date('n'));

// Format Events
$events = array_map(function($ev) {
    return [
        'id'       => $ev['id'],
        'title'    => $ev['title'],
        'date'     => $ev['event_date'],
        'category' => $ev['category'],
        'desc'     => $ev['description'],
        'type'     => 'event'
    ];
}, $db_events);

// Add Employee Birthdays
foreach ($employees as $emp) {
    if (!$emp['birth_date']) continue;
    $bday = new DateTime($emp['birth_date']);
    $event_date = sprintf('%04d-%02d-%02d', $year, $bday->format('m'), $bday->format('d'));
    
    $events[] = [
        'id'       => "bday-" . $emp['id'],
        'title'    => "Birthday: " . $emp['name'],
        'date'     => $event_date,
        'category' => 'birthday',
        'desc'     => "Celebrating " . $emp['name'],
        'type'     => 'birthday'
    ];
}

$cat_styles = [
    'meeting'  => ['bg'=>'bg-blue-500/10',   'text'=>'text-blue-500',   'dot'=>'bg-blue-500'],
    'holiday'  => ['bg'=>'bg-rose-500/10',   'text'=>'text-rose-500',   'dot'=>'bg-rose-500'],
    'activity' => ['bg'=>'bg-emerald-500/10','text'=>'text-emerald-500','dot'=>'bg-emerald-500'],
    'birthday' => ['bg'=>'bg-amber-500/10',  'text'=>'text-amber-500',  'dot'=>'bg-amber-500']
];

$cat_labels = [
    'meeting'  => 'Meeting',
    'holiday'  => 'Holiday',
    'activity' => 'Activity',
    'birthday' => 'Birthday'
];

$first_dow = (int)date('N', mktime(0,0,0,$month,1,$year));
$days_in   = (int)date('t', mktime(0,0,0,$month,1,$year));

$event_map = [];
foreach ($events as $ev) { $event_map[$ev['date']][] = $ev; }
?>

<div class="space-y-8 animate-in fade-in slide-in-from-bottom-4 duration-700">
    <!-- Header Section -->
    <section class="flex flex-col md:flex-row md:items-end justify-between gap-6">
        <div>
            <div class="flex items-center gap-3 mb-2">
                <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                    <span class="material-symbols-outlined font-bold">event_available</span>
                </div>
                <h1 data-theme-text class="text-3xl font-bold  leading-none">Event Ledger</h1>
            </div>
            <p data-theme-muted class="text-[10px] font-bold   ml-1">Corporate Schedule & Highlights</p>
        </div>
        
        <?php if (auth_is_hrd()): ?>
        <button onclick="openModal('addEventModal')" class="group relative px-6 py-3.5 bg-primary text-white rounded-lg font-bold text-xs   flex items-center gap-3 overflow-hidden transition-all hover:scale-105 active:scale-95 shadow-xl shadow-primary/20">
            <span class="material-symbols-outlined text-lg">add_circle</span>
            <span>Tambah Event</span>
            <div class="absolute inset-0 bg-white/10 translate-y-full group-hover:translate-y-0 transition-transform duration-300"></div>
        </button>
        <?php endif; ?>
    </section>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
        
        <!-- Left: Calendar (Column 8) -->
        <div class="lg:col-span-8 flex flex-col gap-5">
            <div data-theme-card class="p-5 rounded-lg border border-border shadow-2xl relative overflow-hidden transition-all duration-500" id="calendar-container">
                <!-- Background Decoration -->
                <div class="absolute top-0 right-0 -translate-y-1/2 translate-x-1/2 w-64 h-64 bg-primary/5 rounded-full blur-3xl -z-10"></div>
                
                <!-- Month Navigation -->
                <div class="flex items-center justify-between mb-10">
                    <div class="relative min-w-[200px]">
                        <h2 id="calendar-title" data-theme-text class="text-2xl font-bold  capitalize transition-all duration-300">
                            <?= date('F Y', mktime(0,0,0,$month,1,$year)) ?>
                        </h2>
                        <div id="nav-loader" class="absolute -bottom-2 left-0 w-0 h-1 bg-primary rounded-full transition-all duration-500 opacity-50"></div>
                    </div>
                    
                    <div class="flex items-center gap-3">
                        <button onclick="navigateDebounced(-1)" data-theme-surface2 class="w-12 h-12 rounded-lg flex items-center justify-center hover:bg-primary hover:text-white transition-all active:scale-90 border border-border group">
                            <span class="material-symbols-outlined text-lg group-hover:scale-110">chevron_left</span>
                        </button>
                        <button onclick="navigateDebounced(1)" data-theme-surface2 class="w-12 h-12 rounded-lg flex items-center justify-center hover:bg-primary hover:text-white transition-all active:scale-90 border border-border group">
                            <span class="material-symbols-outlined text-lg group-hover:scale-110">chevron_right</span>
                        </button>
                    </div>
                </div>

                <!-- Calendar Content -->
                <div id="calendar-grid-wrapper" class="transition-opacity duration-300">
                    <!-- Weekdays -->
                    <div class="grid grid-cols-7 gap-3 mb-6">
                        <?php foreach (['Sen','Sel','Rab','Kam','Jum','Sab','Min'] as $d): ?>
                        <div data-theme-muted class="text-center text-[10px] font-bold   opacity-40"><?= $d ?></div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Days Grid -->
                    <div class="grid grid-cols-7 gap-2 md:gap-4">
                        <?php
                        for ($i = 1; $i < $first_dow; $i++):
                        ?>
                        <div class="aspect-square opacity-0"></div>
                        <?php endfor; ?>

                        <?php for ($d = 1; $d <= $days_in; $d++):
                            $dateStr  = sprintf('%04d-%02d-%02d', $year, $month, $d);
                            $isToday  = $dateStr === $today;
                            $dayEvents = $event_map[$dateStr] ?? [];
                            $mainCat = !empty($dayEvents) ? $dayEvents[0]['category'] : null;
                            $dotClass = $mainCat && isset($cat_styles[$mainCat]) ? $cat_styles[$mainCat]['dot'] : 'bg-border';
                        ?>
                        <div class="aspect-square flex flex-col items-center justify-center relative cursor-pointer group active:scale-90 transition-all">
                            <!-- Background Circle on Hover -->
                            <div class="absolute inset-1 rounded-lg bg-primary/5 scale-0 group-hover:scale-100 transition-transform duration-300"></div>
                            
                            <!-- Day Number -->
                            <div class="w-12 h-12 rounded-lg flex items-center justify-center relative z-10 transition-all
                                <?= $isToday ? 'bg-primary text-white font-bold shadow-lg shadow-primary/30 ring-4 ring-primary/10' : 'data-theme-text font-bold' ?>">
                                <span class="text-sm"><?= $d ?></span>
                            </div>

                            <!-- Event Indicators -->
                            <?php if (!empty($dayEvents)): ?>
                            <div class="absolute bottom-2 flex gap-1 justify-center">
                                <?php foreach(array_slice($dayEvents, 0, 3) as $idx => $ev): 
                                    $evSt = $cat_styles[$ev['category']] ?? ['dot'=>'bg-slate-400'];
                                ?>
                                <div class="w-1.5 h-1.5 rounded-full <?= $evSt['dot'] ?> shadow-sm"></div>
                                <?php endforeach; ?>
                            </div>
                            <?php endif; ?>
                        </div>
                        <?php endfor; ?>
                    </div>
                </div>

                <!-- Legend -->
                <div class="mt-6 pt-5 border-t border-border flex flex-wrap gap-2 justify-center md:justify-start">
                    <?php foreach ($cat_labels as $key => $label): 
                        $st = $cat_styles[$key];
                    ?>
                    <div class="flex items-center gap-1.5 px-2.5 py-1 rounded-lg transition-all hover:bg-surface2" style="background:var(--surface2);">
                        <div class="w-1.5 h-1.5 rounded-full <?= $st['dot'] ?> ring-2 ring-<?= $key === 'birthday' ? 'amber' : ($key === 'holiday' ? 'rose' : ($key === 'meeting' ? 'blue' : 'emerald')) ?>-500/10"></div>
                        <span data-theme-text class="text-[8.5px] font-bold  "><?= $label ?></span>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Right: Upcoming List (Column 4) -->
        <div class="lg:col-span-4 space-y-5">
            <div data-theme-card class="p-5 rounded-lg border border-border shadow-xl min-h-full">
                <div class="flex items-center justify-between mb-5">
                    <div class="flex items-center gap-3">
                        <span class="material-symbols-outlined text-primary font-bold">bolt</span>
                        <h3 data-theme-text class="text-sm font-bold  ">Upcoming</h3>
                    </div>
                    <span data-theme-muted class="text-[9px] font-bold  bg-surface2 px-3 py-1 rounded-full border border-border">Agenda</span>
                </div>
                
                <div class="space-y-4">
                    <?php
                    $upcoming = array_filter($events, fn($e) => $e['date'] >= $today);
                    usort($upcoming, fn($a,$b) => strcmp($a['date'],$b['date']));
                    if (empty($upcoming)): ?>
                        <div class="flex flex-col items-center justify-center py-20 text-center space-y-4">
                            <div class="w-16 h-16 rounded-full bg-surface2 flex items-center justify-center">
                                <span class="material-symbols-outlined text-3xl opacity-20">event_busy</span>
                            </div>
                            <p data-theme-muted class="text-xs font-bold italic">No upcoming events scheduled</p>
                        </div>
                    <?php else:
                        foreach (array_slice($upcoming, 0, 8) as $ev):
                            $st = $cat_styles[$ev['category']] ?? ['bg'=>'bg-surface-container-high', 'text'=>'text-on-surface', 'dot'=>'bg-secondary'];
                    ?>
                    <div class="p-3.5 rounded-lg transition-all hover:bg-surface2 border border-transparent hover:border-border group flex items-start gap-4" style="background:var(--surface2);">
                        <div class="w-10 h-10 rounded-lg <?= $st['bg'] ?> flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                            <span class="material-symbols-outlined <?= $st['text'] ?> text-lg font-bold">
                                <?= $ev['category'] === 'birthday' ? 'cake' : ($ev['category'] === 'holiday' ? 'beach_access' : ($ev['category'] === 'meeting' ? 'forum' : 'celebration')) ?>
                            </span>
                        </div>
                        <div class="min-w-0 flex-1 pt-1">
                            <div data-theme-text class="font-bold text-sm leading-tight truncate group-hover:text-primary transition-colors"><?= h($ev['title']) ?></div>
                            <div class="flex items-center gap-2 mt-1">
                                <span data-theme-muted class="text-[10px] font-bold  "><?= date('D, d M Y', strtotime($ev['date'])) ?></span>
                                <span class="w-1 h-1 rounded-full bg-border"></span>
                                <span class="<?= $st['text'] ?> text-[9px] font-bold  italic"><?= $ev['category'] ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Add Event Modal -->
<?php if (auth_is_hrd()): ?>
<div id="addEventModal" class="hidden fixed inset-0 z-[100] p-4 md:p-6" style="display: none;">
    <div class="absolute inset-0 bg-black/60 backdrop-blur-md transition-opacity"></div>
    <div class="relative w-full max-w-lg mx-auto mt-20">
        <div data-theme-card class="bg-surface rounded-lg border border-border shadow-2xl overflow-hidden animate-in fade-in zoom-in duration-300">
            <!-- Modal Header -->
            <div class="p-5 pb-1 flex justify-between items-center">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                        <span class="material-symbols-outlined font-bold">add_circle</span>
                    </div>
                    <div>
                        <h3 data-theme-text class="text-lg font-bold ">Tambah Event Baru</h3>
                        <p data-theme-muted class="text-[9px] font-bold   opacity-50">Corporate Schedule</p>
                    </div>
                </div>
                <button onclick="closeModal('addEventModal')" class="w-10 h-10 rounded-full flex items-center justify-center text-on-surface/40 hover:bg-surface2 transition-colors">
                    <span class="material-symbols-outlined font-bold">close</span>
                </button>
            </div>

            <!-- Modal Body -->
            <form method="POST" action="?page=calendar&action=add" class="p-5 pt-1 space-y-3">
                <!-- Title -->
                <div class="space-y-1.5">
                    <label data-theme-muted class="text-[9px] font-bold   opacity-50 block">Nama Event / Judul</label>
                    <input data-theme-text name="title" type="text" class="w-full border border-border rounded-lg px-4 py-2.5 text-xs font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" style="background:var(--surface2)!important; color:var(--text-primary)!important;" placeholder="e.g. Rapat Tahunan Q1" required>
                </div>

                <!-- Date & Category Grid -->
                <div class="grid grid-cols-2 gap-3">
                    <div class="space-y-1.5">
                        <label data-theme-muted class="text-[9px] font-bold   opacity-50 block">Tanggal</label>
                        <input data-theme-text name="date" type="date" class="w-full border border-border rounded-lg px-4 py-2.5 text-xs font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all" style="background:var(--surface2)!important; color:var(--text-primary)!important;" required>
                    </div>
                    <div class="space-y-1.5">
                        <label data-theme-muted class="text-[9px] font-bold   opacity-50 block">Kategori</label>
                        <select name="category" class="w-full border border-border rounded-lg px-4 py-2.5 text-xs font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all appearance-none" style="background:var(--surface2)!important; color:var(--text-primary)!important;">
                            <?php foreach ($cat_labels as $val=>$lbl): ?>
                            <option value="<?=$val?>"><?=$lbl?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Description -->
                <div class="space-y-1.5">
                    <label data-theme-muted class="text-[9px] font-bold   opacity-50 block">Catatan Tambahan (Opsional)</label>
                    <textarea data-theme-text name="desc" rows="2" class="w-full border border-border rounded-lg px-4 py-2.5 text-xs font-bold focus:ring-2 focus:ring-primary/20 focus:border-primary outline-none transition-all resize-none" style="background:var(--surface2)!important; color:var(--text-primary)!important;" placeholder="Tuliskan keterangan detail event ini..."></textarea>
                </div>



                <!-- Actions -->
                <div class="pt-4 flex flex-col gap-3">
                    <button type="submit" class="w-full py-5 bg-primary text-white rounded-full text-sm font-bold  shadow-xl shadow-primary/20 active:scale-95 transition-all flex items-center justify-center gap-3">
                        <span class="material-symbols-outlined font-bold">publish</span>
                        Publikasikan Event
                    </button>
                    <button type="button" onclick="closeModal('addEventModal')" data-theme-muted class="w-full py-3 text-xs font-bold   hover:bg-surface2 rounded-full transition-all text-center">
                        Batal
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
let currentYear = <?= $year ?>;
let currentMonth = <?= $month ?>;
let navTimeout = null;

function navigateDebounced(direction) {
    currentMonth += direction;
    if (currentMonth > 12) { currentMonth = 1; currentYear++; }
    if (currentMonth < 1) { currentMonth = 12; currentYear--; }

    const monthNames = ["Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember"];
    const title = document.getElementById('calendar-title');
    title.innerText = monthNames[currentMonth-1] + " " + currentYear;
    title.classList.add('opacity-40', 'scale-95');
    
    document.getElementById('calendar-grid-wrapper').classList.add('opacity-30', 'blur-[2px]');

    const loader = document.getElementById('nav-loader');
    loader.style.width = '100%';

    if (navTimeout) clearTimeout(navTimeout);

    navTimeout = setTimeout(() => {
        window.location.href = `?page=calendar&cal_year=${currentYear}&cal_month=${currentMonth}`;
    }, 600);
}
</script>

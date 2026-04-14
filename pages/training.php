<?php
// pages/training.php – Program Training
$programs = get_training();
$category_labels = ['skill'=>'Skill','leadership'=>'Leadership','technical'=>'Technical','softskill'=>'Soft Skill'];
$category_badge  = ['skill'=>'badge-info','leadership'=>'badge-warning','technical'=>'badge-success','softskill'=>'badge-gray'];
?>
<div class="space-y-6">
    <?php if (auth_is_hrd()): ?>
    <div class="flex justify-end">
        <button onclick="openModal('trainingModal')" class="btn btn-primary">
            <i class="fas fa-plus"></i> Tambah Program
        </button>
    </div>
    <?php endif; ?>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
        <?php foreach ($programs as $prog): ?>
        <div class="card p-6 flex flex-col gap-4">
            <div class="flex items-start justify-between gap-2">
                <div class="w-12 h-12 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center flex-shrink-0">
                    <i class="fas fa-graduation-cap text-white text-lg"></i>
                </div>
                <span class="badge <?= $category_badge[$prog['category']] ?? 'badge-gray' ?>">
                    <?= $category_labels[$prog['category']] ?? $prog['category'] ?>
                </span>
            </div>
            <div>
                <h4 class="font-bold text-gray-900 mb-1"><?= h($prog['title']) ?></h4>
                <p class="text-sm text-gray-500 line-clamp-2"><?= h($prog['description']) ?></p>
            </div>
            <div class="space-y-1.5 text-sm text-gray-600">
                <div class="flex items-center gap-2">
                    <i class="fas fa-calendar w-4 text-gray-400"></i>
                    <span><?= format_date($prog['start_date']) ?> – <?= format_date($prog['end_date']) ?></span>
                </div>
                <div class="flex items-center gap-2">
                    <i class="fas fa-users w-4 text-gray-400"></i>
                    <span><?= $prog['participants'] ?>/<?= $prog['capacity'] ?> peserta</span>
                </div>
            </div>
            <div class="progress-bar">
                <?php $pct = min(100, round($prog['participants']/$prog['capacity']*100)); ?>
                <div class="progress-fill <?= $pct>=100?'bg-red-500':'bg-indigo-500' ?>" style="width:<?= $pct ?>%"></div>
            </div>
            <?php if (!auth_is_hrd()): ?>
            <a href="?page=training&action=join&id=<?= $prog['id'] ?>" class="btn btn-primary text-center text-sm py-2">
                <i class="fas fa-sign-in-alt"></i> Daftar Program
            </a>
            <?php endif; ?>
        </div>
        <?php endforeach; ?>
    </div>
</div>

<?php if (auth_is_hrd()): ?>
<div id="trainingModal" class="hidden modal-backdrop" onclick="if(event.target===this)closeModal('trainingModal')">
    <div class="modal-box">
        <div class="modal-header border-b pb-4">
            <h3 class="text-lg font-bold">Tambah Program Training</h3>
            <button onclick="closeModal('trainingModal')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
        </div>
        <form method="POST" action="?page=training&action=add" class="modal-body space-y-4">
            <div>
                <label class="form-label">Judul Program *</label>
                <input name="title" type="text" class="form-input" required>
            </div>
            <div>
                <label class="form-label">Deskripsi</label>
                <textarea name="description" rows="2" class="form-input resize-none"></textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="form-label">Tanggal Mulai *</label>
                    <input name="start_date" type="date" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Tanggal Selesai *</label>
                    <input name="end_date" type="date" class="form-input" required>
                </div>
                <div>
                    <label class="form-label">Instruktur</label>
                    <input name="instructor" type="text" class="form-input">
                </div>
                <div>
                    <label class="form-label">Kapasitas</label>
                    <input name="capacity" type="number" class="form-input" value="20" min="1">
                </div>
            </div>
            <div>
                <label class="form-label">Kategori</label>
                <select name="category" class="form-input">
                    <?php foreach ($category_labels as $val=>$lbl): ?>
                    <option value="<?=$val?>"><?=$lbl?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="modal-footer border-t pt-4">
                <button type="button" onclick="closeModal('trainingModal')" class="btn btn-outline">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-graduation-cap"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

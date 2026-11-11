<?php
/**
 * Tambah Layanan & Penitipan - PawHouse Admin
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    $name        = trim($_POST['name'] ?? '');
    $price       = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $duration    = trim($_POST['duration'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        $error = 'Nama layanan wajib diisi.';
    } elseif ($price === false || $price < 0) {
        $error = 'Tarif layanan harus berupa angka positif.';
    } elseif (empty($duration)) {
        $error = 'Estimasi durasi layanan wajib diisi.';
    } else {
        $pdo = db();
        $stmt = $pdo->prepare("
            INSERT INTO services (name, description, price, duration) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $name, 
            !empty($description) ? $description : null, 
            $price, 
            $duration
        ]);

        redirect(base_url('admin/services/index.php'), 'Layanan "' . $name . '" berhasil ditambahkan.', 'success');
    }
}

$adminTitle = 'Tambah Layanan - PawHouse Admin';
$activeMenu = 'services';
require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Tambah Layanan Baru</h1>
        <p class="page-subtitle">Buat paket penitipan atau jenis perawatan baru</p>
    </div>
    <div>
        <a href="<?= base_url('admin/services/index.php') ?>" class="btn btn-outline">← Kembali ke Daftar</a>
    </div>
</div>

<div class="form-card">
    <?php if ($error): ?>
        <div class="alert alert-danger"><span><?= e($error) ?></span></div>
    <?php endif; ?>

    <form action="<?= base_url('admin/services/create.php') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="name" class="form-label">Nama Layanan <span class="req">*</span></label>
            <input type="text" name="name" id="name" class="form-control" placeholder="Contoh: Penitipan VIP Kamar AC, Grooming Kutu..." required autofocus value="<?= e($_POST['name'] ?? '') ?>">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="price" class="form-label">Tarif Biaya (Rp) <span class="req">*</span></label>
                <input type="number" name="price" id="price" class="form-control" placeholder="Contoh: 75000" min="0" step="1000" required value="<?= e($_POST['price'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="duration" class="form-label">Estimasi Durasi <span class="req">*</span></label>
                <input type="text" name="duration" id="duration" class="form-control" placeholder="Contoh: 1 Hari / 90 Menit" required value="<?= e($_POST['duration'] ?? '1 Hari') ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Deskripsi Fasilitas & Rincian Perawatan</label>
            <textarea name="description" id="description" class="form-control" placeholder="Fasilitas yang didapatkan, pakan, pembersihan..."><?= e($_POST['description'] ?? '') ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan Layanan</button>
            <a href="<?= base_url('admin/services/index.php') ?>" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>


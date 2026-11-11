<?php
/**
 * Tambah Hewan Peliharaan - PawHouse Admin
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$pdo = db();
$customers = $pdo->query("SELECT id, name, phone FROM customers ORDER BY name ASC")->fetchAll();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    $customerId = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
    $name       = trim($_POST['name'] ?? '');
    $type       = trim($_POST['type'] ?? '');
    $breed      = trim($_POST['breed'] ?? '');
    $age        = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);
    $notes      = trim($_POST['notes'] ?? '');

    if (!$customerId) {
        $error = 'Pilih pemilik hewan dari daftar pelanggan.';
    } elseif (empty($name)) {
        $error = 'Nama hewan wajib diisi.';
    } elseif (empty($type)) {
        $error = 'Jenis hewan wajib dipilih.';
    } elseif ($age === false || $age < 0) {
        $error = 'Umur hewan tidak boleh bernilai negatif.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO pets (customer_id, name, type, breed, age, notes) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $customerId,
            $name,
            $type,
            !empty($breed) ? $breed : null,
            $age,
            !empty($notes) ? $notes : null
        ]);

        redirect(base_url('admin/pets/index.php'), 'Hewan "' . $name . '" berhasil ditambahkan.', 'success');
    }
}

$adminTitle = 'Tambah Hewan - PawHouse Admin';
$activeMenu = 'pets';
require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Tambah Hewan Peliharaan</h1>
        <p class="page-subtitle">Daftarkan data hewan peliharaan milik pelanggan</p>
    </div>
    <div>
        <a href="<?= base_url('admin/pets/index.php') ?>" class="btn btn-outline">← Kembali ke Daftar</a>
    </div>
</div>

<div class="form-card">
    <?php if ($error): ?>
        <div class="alert alert-danger"><span><?= e($error) ?></span></div>
    <?php endif; ?>

    <form action="<?= base_url('admin/pets/create.php') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="customer_id" class="form-label">Pemilik Hewan (Relasi Pelanggan) <span class="req">*</span></label>
            <select name="customer_id" id="customer_id" class="form-control" required autofocus>
                <option value="">-- Pilih Pemilik Terdaftar --</option>
                <?php foreach ($customers as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (($_POST['customer_id'] ?? '') == $c['id']) ? 'selected' : '' ?>>
                        <?= e($c['name']) ?> (<?= e($c['phone']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="name" class="form-label">Nama Hewan <span class="req">*</span></label>
                <input type="text" name="name" id="name" class="form-control" placeholder="Contoh: Milo / Luna" required value="<?= e($_POST['name'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="type" class="form-label">Jenis Hewan <span class="req">*</span></label>
                <select name="type" id="type" class="form-control" required>
                    <option value="">-- Pilih Jenis --</option>
                    <option value="Kucing" <?= (($_POST['type'] ?? '') === 'Kucing') ? 'selected' : '' ?>>Kucing</option>
                    <option value="Anjing" <?= (($_POST['type'] ?? '') === 'Anjing') ? 'selected' : '' ?>>Anjing</option>
                    <option value="Kelinci" <?= (($_POST['type'] ?? '') === 'Kelinci') ? 'selected' : '' ?>>Kelinci</option>
                    <option value="Hamster" <?= (($_POST['type'] ?? '') === 'Hamster') ? 'selected' : '' ?>>Hamster</option>
                    <option value="Lainnya" <?= (($_POST['type'] ?? '') === 'Lainnya') ? 'selected' : '' ?>>Lainnya</option>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="breed" class="form-label">Ras / Breed (Opsional)</label>
                <input type="text" name="breed" id="breed" class="form-control" placeholder="Contoh: Persia, Pomeranian..." value="<?= e($_POST['breed'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="age" class="form-label">Perkiraan Umur (Tahun) <span class="req">*</span></label>
                <input type="number" name="age" id="age" class="form-control" min="0" required value="<?= e($_POST['age'] ?? '1') ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="notes" class="form-label">Catatan Kesehatan / Kebiasaan</label>
            <textarea name="notes" id="notes" class="form-control" placeholder="Riwayat alergi pakan, vaksinasi, sifat hewan..."><?= e($_POST['notes'] ?? '') ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan Hewan</button>
            <a href="<?= base_url('admin/pets/index.php') ?>" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>


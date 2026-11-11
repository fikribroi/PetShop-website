<?php
/**
 * Tambah Kategori Produk - PawHouse Admin
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        $error = 'Nama kategori wajib diisi.';
    } else {
        $pdo = db();
        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        $stmt->execute([$name, !empty($description) ? $description : null]);

        redirect(base_url('admin/categories/index.php'), 'Kategori "' . $name . '" berhasil ditambahkan.', 'success');
    }
}

$adminTitle = 'Tambah Kategori - PawHouse Admin';
$activeMenu = 'categories';
require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Tambah Kategori Produk</h1>
        <p class="page-subtitle">Buat grup klasifikasi baru untuk produk toko</p>
    </div>
    <div>
        <a href="<?= base_url('admin/categories/index.php') ?>" class="btn btn-outline">← Kembali ke Daftar</a>
    </div>
</div>

<div class="form-card">
    <?php if ($error): ?>
        <div class="alert alert-danger"><span><?= e($error) ?></span></div>
    <?php endif; ?>

    <form action="<?= base_url('admin/categories/create.php') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="name" class="form-label">Nama Kategori <span class="req">*</span></label>
            <input type="text" name="name" id="name" class="form-control" placeholder="Contoh: Makanan Kucing, Mainan Hewan..." required autofocus value="<?= e($_POST['name'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Deskripsi Kategori</label>
            <textarea name="description" id="description" class="form-control" placeholder="Keterangan singkat tentang kelompok produk ini..."><?= e($_POST['description'] ?? '') ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan Kategori</button>
            <a href="<?= base_url('admin/categories/index.php') ?>" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>


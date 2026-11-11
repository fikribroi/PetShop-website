<?php
/**
 * Edit Kategori Produk - PawHouse Admin
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    redirect(base_url('admin/categories/index.php'), 'ID Kategori tidak valid.', 'danger');
}

$pdo = db();
$stmt = $pdo->prepare("SELECT * FROM categories WHERE id = ?");
$stmt->execute([$id]);
$category = $stmt->fetch();

if (!$category) {
    redirect(base_url('admin/categories/index.php'), 'Kategori tidak ditemukan.', 'danger');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        $error = 'Nama kategori wajib diisi.';
    } else {
        $stmtUpdate = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
        $stmtUpdate->execute([$name, !empty($description) ? $description : null, $id]);

        redirect(base_url('admin/categories/index.php'), 'Kategori "' . $name . '" berhasil diperbarui.', 'success');
    }
}

$adminTitle = 'Edit Kategori - PawHouse Admin';
$activeMenu = 'categories';
require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Edit Kategori Produk</h1>
        <p class="page-subtitle">Ubah informasi nama atau deskripsi kategori #<?= e($category['id']) ?></p>
    </div>
    <div>
        <a href="<?= base_url('admin/categories/index.php') ?>" class="btn btn-outline">← Kembali ke Daftar</a>
    </div>
</div>

<div class="form-card">
    <?php if ($error): ?>
        <div class="alert alert-danger"><span><?= e($error) ?></span></div>
    <?php endif; ?>

    <form action="<?= base_url('admin/categories/edit.php?id=' . $id) ?>" method="POST">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="name" class="form-label">Nama Kategori <span class="req">*</span></label>
            <input type="text" name="name" id="name" class="form-control" required value="<?= e($_POST['name'] ?? $category['name']) ?>">
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Deskripsi Kategori</label>
            <textarea name="description" id="description" class="form-control"><?= e($_POST['description'] ?? $category['description']) ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="<?= base_url('admin/categories/index.php') ?>" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>


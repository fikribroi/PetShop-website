<?php
/**
 * Tambah Produk Pet Shop - PawHouse Admin
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$pdo = db();
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    $name        = trim($_POST['name'] ?? '');
    $categoryId  = filter_input(INPUT_POST, 'category_id', FILTER_VALIDATE_INT);
    $price       = filter_input(INPUT_POST, 'price', FILTER_VALIDATE_FLOAT);
    $stock       = filter_input(INPUT_POST, 'stock', FILTER_VALIDATE_INT);
    $description = trim($_POST['description'] ?? '');

    if (empty($name)) {
        $error = 'Nama produk wajib diisi.';
    } elseif (!$categoryId) {
        $error = 'Kategori produk wajib dipilih.';
    } elseif ($price === false || $price < 0) {
        $error = 'Harga produk harus berupa angka positif.';
    } elseif ($stock === false || $stock < 0) {
        $error = 'Stok produk tidak boleh bernilai negatif.';
    } else {
        $stmt = $pdo->prepare("
            INSERT INTO products (category_id, name, description, price, stock, image) 
            VALUES (?, ?, ?, ?, ?, NULL)
        ");
        $stmt->execute([$categoryId, $name, !empty($description) ? $description : null, $price, $stock]);

        redirect(base_url('admin/products/index.php'), 'Produk "' . $name . '" berhasil ditambahkan.', 'success');
    }
}

$adminTitle = 'Tambah Produk - PawHouse Admin';
$activeMenu = 'products';
require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Tambah Produk Baru</h1>
        <p class="page-subtitle">Daftarkan item makanan, vitamin, atau aksesoris ke inventaris</p>
    </div>
    <div>
        <a href="<?= base_url('admin/products/index.php') ?>" class="btn btn-outline">← Kembali ke Daftar</a>
    </div>
</div>

<div class="form-card">
    <?php if ($error): ?>
        <div class="alert alert-danger"><span><?= e($error) ?></span></div>
    <?php endif; ?>

    <form action="<?= base_url('admin/products/create.php') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="name" class="form-label">Nama Produk <span class="req">*</span></label>
            <input type="text" name="name" id="name" class="form-control" placeholder="Contoh: Royal Canin Persian 2kg" required autofocus value="<?= e($_POST['name'] ?? '') ?>">
        </div>

        <div class="form-group">
            <label for="category_id" class="form-label">Kategori Produk <span class="req">*</span></label>
            <select name="category_id" id="category_id" class="form-control" required>
                <option value="">-- Pilih Kategori --</option>
                <?php foreach ($categories as $cat): ?>
                    <option value="<?= $cat['id'] ?>" <?= (($_POST['category_id'] ?? '') == $cat['id']) ? 'selected' : '' ?>>
                        <?= e($cat['name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="price" class="form-label">Harga Jual (Rp) <span class="req">*</span></label>
                <input type="number" name="price" id="price" class="form-control" placeholder="Contoh: 150000" min="0" step="500" required value="<?= e($_POST['price'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="stock" class="form-label">Jumlah Stok <span class="req">*</span></label>
                <input type="number" name="stock" id="stock" class="form-control" placeholder="Contoh: 25" min="0" required value="<?= e($_POST['stock'] ?? '10') ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Deskripsi & Spesifikasi Produk</label>
            <textarea name="description" id="description" class="form-control" placeholder="Kandungan nutrisi, berat bersih, instruksi pemakaian..."><?= e($_POST['description'] ?? '') ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan Produk</button>
            <a href="<?= base_url('admin/products/index.php') ?>" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>


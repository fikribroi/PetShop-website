<?php
/**
 * Data Kategori Produk - PawHouse Admin
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$pdo = db();
$search = trim($_GET['q'] ?? '');

$sql = "
    SELECT c.*, COUNT(p.id) AS total_products
    FROM categories c
    LEFT JOIN products p ON c.id = p.category_id
";
$params = [];

if (!empty($search)) {
    $sql .= " WHERE c.name LIKE ? OR c.description LIKE ?";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " GROUP BY c.id ORDER BY c.name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$categories = $stmt->fetchAll();

$adminTitle = 'Kategori Produk - PawHouse Admin';
$activeMenu = 'categories';
require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Kelola Kategori Produk</h1>
        <p class="page-subtitle">Klasifikasi produk makanan, aksesoris, vitamin, dan perlengkapan pet shop</p>
    </div>
    <div>
        <a href="<?= base_url('admin/categories/create.php') ?>" class="btn btn-primary">+ Tambah Kategori</a>
    </div>
</div>

<div class="data-card">
    <div class="data-card-header">
        <h2 class="data-card-title">Daftar Kategori (<?= count($categories) ?> Data)</h2>
        <div class="card-toolbar">
            <form action="<?= base_url('admin/categories/index.php') ?>" method="GET" style="display:flex; gap:0.5rem;">
                <input type="text" name="q" class="search-input" placeholder="Cari kategori..." value="<?= e($search) ?>">
                <button type="submit" class="btn btn-outline btn-sm">Cari</button>
                <?php if (!empty($search)): ?>
                    <a href="<?= base_url('admin/categories/index.php') ?>" class="btn btn-outline btn-sm">Reset</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 60px;">No</th>
                    <th>Nama Kategori</th>
                    <th>Deskripsi</th>
                    <th>Jumlah Produk</th>
                    <th style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                    <tr>
                        <td colspan="5" class="empty-state">Tidak ada kategori ditemukan.</td>
                    </tr>
                <?php else: ?>
                    <?php $no = 1; foreach ($categories as $cat): ?>
                        <tr>
                            <td><?= $no++ ?></td>
                            <td><strong><?= e($cat['name']) ?></strong></td>
                            <td><?= e($cat['description'] ?: '-') ?></td>
                            <td><span class="badge badge-primary"><?= e($cat['total_products']) ?> Produk</span></td>
                            <td>
                                <div class="actions-cell">
                                    <a href="<?= base_url('admin/categories/edit.php?id=' . $cat['id']) ?>" class="btn btn-outline btn-sm">Edit</a>
                                    
                                    <form action="<?= base_url('admin/categories/delete.php') ?>" method="POST" class="form-delete" data-name="kategori <?= e($cat['name']) ?>" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $cat['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>


<?php
/**
 * Data Produk Pet Shop - PawHouse Admin
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$pdo = db();

$search     = trim($_GET['q'] ?? '');
$categoryId = filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT);

// Ambil list kategori untuk filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Query produk dengan JOIN Kategori
$sql = "
    SELECT p.*, c.name AS category_name 
    FROM products p
    JOIN categories c ON p.category_id = c.id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (p.name LIKE ? OR p.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if ($categoryId) {
    $sql .= " AND p.category_id = ?";
    $params[] = $categoryId;
}

$sql .= " ORDER BY p.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$adminTitle = 'Data Produk - PawHouse Admin';
$activeMenu = 'products';
require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Kelola Produk Pet Shop</h1>
        <p class="page-subtitle">Manajemen inventaris stok, harga jual, dan kategori perlengkapan hewan</p>
    </div>
    <div>
        <a href="<?= base_url('admin/products/create.php') ?>" class="btn btn-primary">+ Tambah Produk</a>
    </div>
</div>

<div class="data-card">
    <div class="data-card-header">
        <h2 class="data-card-title">Daftar Produk (<?= count($products) ?> Data)</h2>
        <div class="card-toolbar">
            <form action="<?= base_url('admin/products/index.php') ?>" method="GET" style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                <input type="text" name="q" class="search-input" placeholder="Cari nama atau deskripsi..." value="<?= e($search) ?>">
                
                <select name="category_id" class="search-input" style="min-width: 170px;">
                    <option value="">-- Semua Kategori --</option>
                    <?php foreach ($categories as $cat): ?>
                        <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
                            <?= e($cat['name']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>

                <button type="submit" class="btn btn-outline btn-sm">Filter</button>
                <?php if (!empty($search) || $categoryId): ?>
                    <a href="<?= base_url('admin/products/index.php') ?>" class="btn btn-outline btn-sm">Reset</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>Nama Produk</th>
                    <th>Kategori</th> 
                    <th>Harga Jual</th>
                    <th>Stok</th>
                    <th>Ditambahkan</th>
                    <th style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($products)): ?>
                    <tr>
                        <td colspan="7" class="empty-state">Tidak ada produk ditemukan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($products as $p): ?>
                        <tr>
                            <td>#<?= e($p['id']) ?></td>
                            <td>
                                <strong><?= e($p['name']) ?></strong>
                                <div style="font-size: 0.8rem; color: var(--admin-text-muted); max-width: 280px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= e($p['description'] ?: '-') ?>
                                </div>
                            </td>
                            <td><span class="badge badge-secondary"><?= e($p['category_name']) ?></span></td>
                            <td><strong><?= format_rupiah($p['price']) ?></strong></td>
                            <td>
                                <?php if ($p['stock'] > 10): ?>
                                    <span class="badge badge-success"><?= e($p['stock']) ?> pcs</span>
                                <?php elseif ($p['stock'] > 0): ?>
                                    <span class="badge badge-warning"><?= e($p['stock']) ?> pcs (Menipis)</span>
                                <?php else: ?>
                                    <span class="badge badge-danger">Habis (0)</span>
                                <?php endif; ?>
                            </td>
                            <td><?= format_date($p['created_at']) ?></td>
                            <td>
                                <div class="actions-cell">
                                    <a href="<?= base_url('admin/products/edit.php?id=' . $p['id']) ?>" class="btn btn-outline btn-sm">Edit</a>
                                    
                                    <form action="<?= base_url('admin/products/delete.php') ?>" method="POST" class="form-delete" data-name="produk <?= e($p['name']) ?>" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $p['id'] ?>">
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


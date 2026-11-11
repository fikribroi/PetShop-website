<?php
/**
 * Katalog Produk - PawHouse
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = db();

$search = trim($_GET['q'] ?? '');
$categoryId = filter_input(INPUT_GET, 'category_id', FILTER_VALIDATE_INT);

// Ambil semua kategori untuk filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY name ASC")->fetchAll();

// Query produk dengan JOIN ke kategori
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

$sql .= " ORDER BY p.name ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

$pageTitle = 'Katalog Produk Pet Shop - PawHouse';
$activeNav = 'products';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="section" style="padding-top: 3rem;">
    <div class="container">
        <div class="section-header" style="text-align: left; margin-bottom: 2rem;">
            <span class="section-subtitle">Katalog Perlengkapan</span>
            <h1 class="section-title">Produk & Nutrisi Hewan</h1>
            <p class="section-desc">Pilihan makanan bergizi, vitamin, pasir litter, aksesoris, dan obat-obatan hewan peliharaan.</p>
        </div>

        <!-- Filter Bar -->
        <div class="form-card" style="padding: 1.25rem 1.5rem; margin-bottom: 2.5rem;">
            <form action="<?= base_url('public/products.php') ?>" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
                <div style="flex: 1; min-width: 240px;">
                    <input type="text" name="q" class="form-control" placeholder="Cari nama produk atau deskripsi..." value="<?= e($search) ?>">
                </div>

                <div style="min-width: 200px;">
                    <select name="category_id" class="form-control">
                        <option value="">-- Semua Kategori --</option>
                        <?php foreach ($categories as $cat): ?>
                            <option value="<?= $cat['id'] ?>" <?= $categoryId == $cat['id'] ? 'selected' : '' ?>>
                                <?= e($cat['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary">Filter</button>

                <?php if (!empty($search) || $categoryId): ?>
                    <a href="<?= base_url('public/products.php') ?>" class="btn btn-outline">Reset Filter</a>
                <?php endif; ?>
            </form>
        </div>

        <!-- Product Grid -->
        <?php if (empty($products)): ?>
            <div class="card" style="padding: 4rem 2rem; text-align: center; color: var(--color-text-muted);">
                <div style="font-size: 3rem; margin-bottom: 1rem;">🔍</div>
                <h3 style="color: var(--color-primary); margin-bottom: 0.5rem;">Produk Tidak Ditemukan</h3>
                <p>Tidak ada produk yang cocok dengan kata kunci pencarian atau kategori yang Anda pilih.</p>
                <div style="margin-top: 1.5rem;">
                    <a href="<?= base_url('public/products.php') ?>" class="btn btn-primary btn-sm">Lihat Semua Produk</a>
                </div>
            </div>
        <?php else: ?>
            <div class="grid-4">
                <?php foreach ($products as $prod): ?>
                    <div class="card product-card">
                        <div class="card-thumb">
                            <span class="category-pill"><?= e($prod['category_name']) ?></span>
                            <div class="product-thumb-placeholder">🛍️</div>
                        </div>
                        <div class="card-body">
                            <h3 class="product-title"><?= e($prod['name']) ?></h3>
                            <p class="product-desc"><?= e($prod['description']) ?></p>
                            <div class="product-meta">
                                <div>
                                    <div class="product-price"><?= format_rupiah($prod['price']) ?></div>
                                    <div class="product-stock">Tersedia: <?= e($prod['stock']) ?> unit</div>
                                </div>
                                <a href="<?= base_url('public/product-detail.php?id=' . $prod['id']) ?>" class="btn btn-outline btn-sm">Detail</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>


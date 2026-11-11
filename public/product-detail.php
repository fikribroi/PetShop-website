<?php
/**
 * Detail Produk - PawHouse
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    redirect(base_url('public/products.php'), 'Produk tidak valid.', 'danger');
}

$pdo = db();
$stmt = $pdo->prepare("
    SELECT p.*, c.name AS category_name 
    FROM products p 
    JOIN categories c ON p.category_id = c.id 
    WHERE p.id = ? 
    LIMIT 1
");
$stmt->execute([$id]);
$product = $stmt->fetch();

if (!$product) {
    redirect(base_url('public/products.php'), 'Produk tidak ditemukan.', 'danger');
}

$pageTitle = e($product['name']) . ' - Detail Produk PawHouse';
$activeNav = 'products';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="section" style="padding-top: 3rem;">
    <div class="container" style="max-width: 900px;">
        <div style="margin-bottom: 1.5rem;">
            <a href="<?= base_url('public/products.php') ?>" style="display: inline-flex; align-items: center; gap: 6px; font-weight: 600;">
                ← Kembali ke Katalog Produk
            </a>
        </div>

        <div class="card" style="display: grid; grid-template-columns: 1fr 1.2fr; border-radius: var(--radius-lg); overflow: hidden;">
            <div style="background: #EFE8DF; display: flex; align-items: center; justify-content: center; min-height: 350px;">
                <div style="font-size: 5rem;">🛍️</div>
            </div>

            <div class="card-body" style="padding: 2.5rem;">
                <span class="badge badge-primary" style="align-self: flex-start; margin-bottom: 0.75rem;">
                    <?= e($product['category_name']) ?>
                </span>

                <h1 style="color: var(--color-primary); font-size: 1.85rem; font-weight: 800; margin-bottom: 0.75rem;">
                    <?= e($product['name']) ?>
                </h1>

                <div style="font-size: 1.6rem; font-weight: 800; color: var(--color-accent); margin-bottom: 1.5rem;">
                    <?= format_rupiah($product['price']) ?>
                </div>

                <div style="padding: 1rem 1.25rem; background: var(--color-surface-muted); border-radius: var(--radius-md); margin-bottom: 1.5rem; display: flex; gap: 2rem;">
                    <div>
                        <div style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase;">Status Stok</div>
                        <div style="font-weight: 700; color: <?= $product['stock'] > 0 ? 'var(--color-success)' : 'var(--color-danger)' ?>;">
                            <?= $product['stock'] > 0 ? 'Tersedia (' . e($product['stock']) . ' unit)' : 'Habis' ?>
                        </div>
                    </div>
                    <div>
                        <div style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase;">Kategori</div>
                        <div style="font-weight: 700; color: var(--color-text);">
                            <?= e($product['category_name']) ?>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 2rem;">
                    <h3 style="font-size: 1rem; color: var(--color-primary); margin-bottom: 0.5rem;">Deskripsi Produk</h3>
                    <p style="color: var(--color-text-muted); font-size: 0.95rem; line-height: 1.7;">
                        <?= nl2br(e($product['description'])) ?>
                    </p>
                </div>

                <div style="border-top: 1px solid var(--color-border); padding-top: 1.5rem; margin-top: auto;">
                    <p style="font-size: 0.85rem; color: var(--color-text-muted); margin-bottom: 1rem;">
                        💡 Produk ini tersedia langsung di Pet Shop PawHouse. Anda dapat membeli langsung di toko atau memesan melalui kontak kasir kami.
                    </p>
                    <a href="https://wa.me/6283153032700?text=Halo%20PawHouse,%20saya%20tertarik%20dengan%20produk%20<?= urlencode($product['name']) ?>" target="_blank" class="btn btn-primary btn-block">
                        Hubungi Kasir via WhatsApp 💬
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>


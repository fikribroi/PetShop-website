<?php
/**
 * Beranda Utama - PawHouse Pet Shop & Penitipan Hewan
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = db();

// Ambil 4 Layanan Unggulan
$stmtServices = $pdo->query("SELECT * FROM services ORDER BY price ASC LIMIT 4");
$services = $stmtServices->fetchAll();

// Ambil 4 Produk Unggulan dengan JOIN ke Kategori
$stmtProducts = $pdo->query("
    SELECT p.*, c.name AS category_name 
    FROM products p
    JOIN categories c ON p.category_id = c.id
    ORDER BY p.id ASC 
    LIMIT 4
");
$products = $stmtProducts->fetchAll();

// Ambil Statistik Ringkas
$totalCust = $pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$totalPets = $pdo->query("SELECT COUNT(*) FROM pets")->fetchColumn();
$totalServices = $pdo->query("SELECT COUNT(*) FROM services")->fetchColumn();

$pageTitle = 'PawHouse - Pet Shop & Penitipan Hewan Peliharaan Terpercaya';
$activeNav = 'home';
require_once __DIR__ . '/../includes/header.php';
?>

<!-- Hero Section -->
<section class="hero">
    <div class="container hero-grid">
        <div class="hero-content">
            <span class="hero-tag">🐾 Sahabat Terbaik Hewan Peliharaan Anda</span>
            <h1>Rumah Kedua yang <span>Penuh Kasih</span> untuk Anabul Anda</h1>
            <p class="hero-desc">
                PawHouse menghadirkan layanan penitipan hewan berstandar hotel, grooming profesional medikasi, serta perlengkapan nutrisi premium terlengkap untuk kucing dan anjing kesayangan Anda.
            </p>
            <div class="hero-buttons">
                <a href="<?= base_url('public/booking.php') ?>" class="btn btn-accent btn-lg">Booking Penitipan Sekarang</a>
                <a href="<?= base_url('public/products.php') ?>" class="btn btn-outline btn-lg">Lihat Katalog Produk</a>
            </div>

            <div class="hero-stats">
                <div class="stat-item">
                    <h3><?= e($totalCust) ?>+</h3>
                    <p>Pemilik Terdaftar</p>
                </div>
                <div class="stat-item">
                    <h3><?= e($totalPets) ?>+</h3>
                    <p>Hewan Ditangani</p>
                </div>
                <div class="stat-item">
                    <h3>100%</h3>
                    <p>Higienis & Ber-AC</p>
                </div>
            </div>
        </div>

        <div class="hero-visual">
            <div class="hero-card">
                <h3 style="color: var(--color-primary); font-size: 1.3rem; margin-bottom: 0.5rem;">Fasilitas Unggulan PawHouse</h3>
                <p style="color: var(--color-text-muted); font-size: 0.88rem;">Setiap anabul mendapatkan perhatian khusus dari caregiver tersertifikasi.</p>
                
                <ul class="hero-feature-list">
                    <li class="hero-feature-item">
                        <div class="feature-icon-badge">🏠</div>
                        <div>
                            <strong style="color: var(--color-primary); font-size: 0.95rem;">Kandang Higienis & Kamar AC</strong>
                            <p style="font-size: 0.85rem; color: var(--color-text-muted);">Sirkulasi udara segar, disinfeksi harian, bebas stres suara bising.</p>
                        </div>
                    </li>
                    <li class="hero-feature-item">
                        <div class="feature-icon-badge">📸</div>
                        <div>
                            <strong style="color: var(--color-primary); font-size: 0.95rem;">Update Foto & Video Harian</strong>
                            <p style="font-size: 0.85rem; color: var(--color-text-muted);">Pantau keceriaan dan porsi makan anabul langsung ke nomor Anda.</p>
                        </div>
                    </li>
                    <li class="hero-feature-item">
                        <div class="feature-icon-badge">🌿</div>
                        <div>
                            <strong style="color: var(--color-primary); font-size: 0.95rem;">Area Bermain Rumput Khusus</strong>
                            <p style="font-size: 0.85rem; color: var(--color-text-muted);">Sesi olahraga dan interaksi terpisah sesuai karakter hewan.</p>
                        </div>
                    </li>
                </ul>

                <div style="margin-top: 1.75rem; text-align: center;">
                    <a href="<?= base_url('public/booking-status.php') ?>" style="font-size: 0.85rem; font-weight: 600; color: var(--color-accent);">
                        Sudah booking? Cek status tiket Anda di sini →
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>

<!-- Layanan Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Layanan PawHouse</span>
            <h2 class="section-title">Perawatan & Penitipan Berkualitas</h2>
            <p class="section-desc">Pilihan paket penitipan harian dan spa grooming menyeluruh untuk memastikan hewan peliharaan Anda tetap bugar dan ceria.</p>
        </div>

        <div class="grid-4">
            <?php foreach ($services as $svc): ?>
                <div class="card service-card">
                    <div class="card-body">
                        <h3 class="service-title"><?= e($svc['name']) ?></h3>
                        <span class="service-duration">⏱️ Durasi: <?= e($svc['duration']) ?></span>
                        <p class="service-desc"><?= e($svc['description']) ?></p>
                        <div class="service-footer">
                            <span class="service-price"><?= format_rupiah($svc['price']) ?></span>
                            <a href="<?= base_url('public/booking.php?service_id=' . $svc['id']) ?>" class="btn btn-primary btn-sm">Pilih</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 2.5rem;">
            <a href="<?= base_url('public/services.php') ?>" class="btn btn-outline">Lihat Semua Layanan (<?= e($totalServices) ?> Pilihan) →</a>
        </div>
    </div>
</section>

<!-- Alur Booking Section -->
<section class="section section-muted">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Alur Mudah</span>
            <h2 class="section-title">Cara Booking Penitipan di PawHouse</h2>
            <p class="section-desc">Hanya butuh 4 langkah mudah tanpa repot mendaftar akun atau instalasi aplikasi.</p>
        </div>

        <div class="steps-grid">
            <div class="step-card">
                <div class="step-number">1</div>
                <h4>Isi Data Booking</h4>
                <p>Masukkan identitas Anda, nama anabul, serta tanggal mulai dan tanggal selesai penitipan.</p>
            </div>
            <div class="step-card">
                <div class="step-number">2</div>
                <h4>Dapatkan Kode Tiket</h4>
                <p>Sistem otomatis menerbitkan kode unik (contoh: PAW-2026-XXXX) untuk pengecekan status.</p>
            </div>
            <div class="step-card">
                <div class="step-number">3</div>
                <h4>Antar Hewan ke Pet Shop</h4>
                <p>Bawa anabul kesayangan beserta perlengkapan atau pakan khusus sesuai preferensi Anda.</p>
            </div>
            <div class="step-card">
                <div class="step-number">4</div>
                <h4>Pantau & Ambil Pulang</h4>
                <p>Cek status penitipan secara real-time di website dan jemput anabul dengan keadaan bersih.</p>
            </div>
        </div>

        <div style="text-align: center; margin-top: 2.5rem;">
            <a href="<?= base_url('public/booking.php') ?>" class="btn btn-accent btn-lg">Mulai Formulir Booking</a>
        </div>
    </div>
</section>

<!-- Produk Unggulan Section -->
<section class="section">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Katalog Toko</span>
            <h2 class="section-title">Produk & Nutrisi Pilihan</h2>
            <p class="section-desc">Makanan berkualitas, pasir higienis, dan perlengkapan harian terbaik untuk menunjang tumbuh kembang peliharaan.</p>
        </div>

        <div class="grid-4">
            <?php foreach ($products as $prod): ?>
                <div class="card product-card">
                    <div class="card-thumb">
                        <span class="category-pill"><?= e($prod['category_name']) ?></span>
                        <div class="product-thumb-placeholder">🛍️</div>
                    </div>
                    <div class="card-body">
                        <h4 class="product-title"><?= e($prod['name']) ?></h4>
                        <p class="product-desc"><?= e($prod['description']) ?></p>
                        <div class="product-meta">
                            <div>
                                <div class="product-price"><?= format_rupiah($prod['price']) ?></div>
                                <div class="product-stock">Stok: <?= e($prod['stock']) ?> pcs</div>
                            </div>
                            <a href="<?= base_url('public/product-detail.php?id=' . $prod['id']) ?>" class="btn btn-outline btn-sm">Detail</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div style="text-align: center; margin-top: 2.5rem;">
            <a href="<?= base_url('public/products.php') ?>" class="btn btn-outline">Buka Seluruh Katalog Produk →</a>
        </div>
    </div>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>


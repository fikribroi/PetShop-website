<?php
/**
 * Dashboard Admin - PawHouse
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

$pdo = db();

// Query Statistik Utama
$totalProducts   = (int)$pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();
$totalCategories = (int)$pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();
$totalCustomers  = (int)$pdo->query("SELECT COUNT(*) FROM customers")->fetchColumn();
$totalPets       = (int)$pdo->query("SELECT COUNT(*) FROM pets")->fetchColumn();
$totalBookings   = (int)$pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$pendingBookings = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Menunggu'")->fetchColumn();
$activeBoarding  = (int)$pdo->query("SELECT COUNT(*) FROM bookings WHERE status = 'Sedang Dititipkan'")->fetchColumn();
$totalRevenue    = (float)$pdo->query("SELECT COALESCE(SUM(total), 0) FROM transactions WHERE payment_status = 'Lunas'")->fetchColumn();

// 5 Booking Penitipan Terbaru (JOIN 4 Tabel)
$stmtRecentBookings = $pdo->query("
    SELECT 
        b.id,
        b.booking_code,
        b.start_date,
        b.end_date,
        b.status,
        cust.name AS customer_name,
        pet.name AS pet_name,
        pet.type AS pet_type,
        s.name AS service_name
    FROM bookings b
    JOIN customers cust ON b.customer_id = cust.id
    JOIN pets pet ON b.pet_id = pet.id
    JOIN services s ON b.service_id = s.id
    ORDER BY b.created_at DESC
    LIMIT 5
");
$recentBookings = $stmtRecentBookings->fetchAll();

// 5 Transaksi Keuangan Terbaru (JOIN Transaksi + Pelanggan + Booking)
$stmtRecentTx = $pdo->query("
    SELECT 
        t.id,
        t.transaction_date,
        t.description,
        t.total,
        t.payment_status,
        cust.name AS customer_name,
        b.booking_code
    FROM transactions t
    JOIN customers cust ON t.customer_id = cust.id
    LEFT JOIN bookings b ON t.booking_id = b.id
    ORDER BY t.created_at DESC
    LIMIT 5
");
$recentTransactions = $stmtRecentTx->fetchAll();

$adminTitle = 'Dashboard Utama - PawHouse Admin';
$activeMenu = 'dashboard';
require_once __DIR__ . '/../includes/admin-header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Ringkasan Sistem PawHouse</h1>
        <p class="page-subtitle">Pantau aktivitas penitipan, ketersediaan produk, dan pencatatan transaksi pet shop</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="<?= base_url('admin/bookings/index.php') ?>" class="btn btn-primary btn-sm">Lihat Semua Booking</a>
        <a href="<?= base_url('admin/products/create.php') ?>" class="btn btn-accent btn-sm">+ Tambah Produk</a>
    </div>
</div>

<!-- Grid Kartu Statistik -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-icon green">📅</div>
        <div>
            <div class="stat-label">Total Booking</div>
            <div class="stat-val"><?= e($totalBookings) ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon amber">⏳</div>
        <div>
            <div class="stat-label">Perlu Konfirmasi</div>
            <div class="stat-val"><?= e($pendingBookings) ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon blue">🏠</div>
        <div>
            <div class="stat-label">Sedang Dititipkan</div>
            <div class="stat-val"><?= e($activeBoarding) ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green">💰</div>
        <div>
            <div class="stat-label">Pendapatan Lunas</div>
            <div class="stat-val" style="font-size: 1.35rem;"><?= format_rupiah($totalRevenue) ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon blue">📦</div>
        <div>
            <div class="stat-label">Katalog Produk</div>
            <div class="stat-val"><?= e($totalProducts) ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon green">👥</div>
        <div>
            <div class="stat-label">Data Pelanggan</div>
            <div class="stat-val"><?= e($totalCustomers) ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon orange">🐶</div>
        <div>
            <div class="stat-label">Hewan Terdaftar</div>
            <div class="stat-val"><?= e($totalPets) ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon blue">🏷️</div>
        <div>
            <div class="stat-label">Kategori Produk</div>
            <div class="stat-val"><?= e($totalCategories) ?></div>
        </div>
    </div>
</div>

<!-- Tabel Booking Terbaru -->
<div class="data-card">
    <div class="data-card-header">
        <h2 class="data-card-title">Booking Penitipan & Layanan Terbaru</h2>
        <a href="<?= base_url('admin/bookings/index.php') ?>" class="btn btn-outline btn-sm">Buka Data Booking →</a>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Kode Booking</th>
                    <th>Pelanggan</th>
                    <th>Hewan (Jenis)</th>
                    <th>Layanan</th>
                    <th>Periode Penitipan</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentBookings)): ?>
                    <tr>
                        <td colspan="7" class="empty-state">Belum ada data booking.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentBookings as $b): ?>
                        <tr>
                            <td><strong><?= e($b['booking_code']) ?></strong></td>
                            <td><?= e($b['customer_name']) ?></td>
                            <td><?= e($b['pet_name']) ?> (<?= e($b['pet_type']) ?>)</td>
                            <td><?= e($b['service_name']) ?></td>
                            <td><?= format_date($b['start_date']) ?> s.d. <?= format_date($b['end_date']) ?></td>
                            <td><?= get_booking_badge($b['status']) ?></td>
                            <td>
                                <div class="actions-cell">
                                    <a href="<?= base_url('admin/bookings/edit-status.php?id=' . $b['id']) ?>" class="btn btn-outline btn-sm">Update</a>
                                    <a href="<?= base_url('admin/bookings/detail.php?id=' . $b['id']) ?>" class="btn btn-outline btn-sm">Detail</a>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Tabel Transaksi Terbaru -->
<div class="data-card">
    <div class="data-card-header">
        <h2 class="data-card-title">Transaksi Keuangan Terbaru</h2>
        <a href="<?= base_url('admin/transactions/index.php') ?>" class="btn btn-outline btn-sm">Buka Data Transaksi →</a>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Tanggal</th>
                    <th>Pelanggan</th>
                    <th>Ref Booking</th>
                    <th>Keterangan</th>
                    <th>Total</th>
                    <th>Status Bayar</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($recentTransactions)): ?>
                    <tr>
                        <td colspan="7" class="empty-state">Belum ada transaksi.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($recentTransactions as $tx): ?>
                        <tr>
                            <td><?= format_date($tx['transaction_date']) ?></td>
                            <td><?= e($tx['customer_name']) ?></td>
                            <td><?= e($tx['booking_code'] ?: 'Pembelian Retail') ?></td>
                            <td><?= e($tx['description']) ?></td>
                            <td><strong><?= format_rupiah($tx['total']) ?></strong></td>
                            <td><?= get_payment_badge($tx['payment_status']) ?></td>
                            <td>
                                <a href="<?= base_url('admin/transactions/edit.php?id=' . $tx['id']) ?>" class="btn btn-outline btn-sm">Edit</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/admin-footer.php'; ?>


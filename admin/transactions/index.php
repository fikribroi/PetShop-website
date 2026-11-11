<?php
/**
 * Data Transaksi Keuangan - PawHouse Admin
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$pdo = db();

$search = trim($_GET['q'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

// Query Transaksi + Pelanggan + Booking (LEFT JOIN)
$sql = "
    SELECT 
        t.*,
        cust.name AS customer_name,
        cust.phone AS customer_phone,
        b.booking_code
    FROM transactions t
    JOIN customers cust ON t.customer_id = cust.id
    LEFT JOIN bookings b ON t.booking_id = b.id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (cust.name LIKE ? OR t.description LIKE ? OR b.booking_code LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($statusFilter)) {
    $sql .= " AND t.payment_status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY t.transaction_date DESC, t.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$transactions = $stmt->fetchAll();

// Total Pendapatan Lunas
$totalPaid = (float)$pdo->query("SELECT COALESCE(SUM(total), 0) FROM transactions WHERE payment_status = 'Lunas'")->fetchColumn();
$totalPending = (float)$pdo->query("SELECT COALESCE(SUM(total), 0) FROM transactions WHERE payment_status = 'Belum Lunas'")->fetchColumn();

$adminTitle = 'Data Transaksi - PawHouse Admin';
$activeMenu = 'transactions';
require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Pencatatan Transaksi Keuangan</h1>
        <p class="page-subtitle">Daftar pembayaran jasa penitipan, layanan grooming, dan penjualan produk toko</p>
    </div>
    <div>
        <a href="<?= base_url('admin/transactions/create.php') ?>" class="btn btn-primary">+ Catat Transaksi</a>
    </div>
</div>

<!-- Ringkasan Finansial Singkat -->
<div class="stats-grid" style="grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); margin-bottom: 1.5rem;">
    <div class="stat-card">
        <div class="stat-icon green">💵</div>
        <div>
            <div class="stat-label">Total Pembayaran Lunas</div>
            <div class="stat-val" style="color: var(--color-success); font-size: 1.5rem;"><?= format_rupiah($totalPaid) ?></div>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-icon amber">⏳</div>
        <div>
            <div class="stat-label">Piutang / Belum Lunas</div>
            <div class="stat-val" style="color: var(--color-warning); font-size: 1.5rem;"><?= format_rupiah($totalPending) ?></div>
        </div>
    </div>
</div>

<div class="data-card">
    <div class="data-card-header">
        <h2 class="data-card-title">Daftar Transaksi (<?= count($transactions) ?> Data)</h2>
        <div class="card-toolbar">
            <form action="<?= base_url('admin/transactions/index.php') ?>" method="GET" style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                <input type="text" name="q" class="search-input" placeholder="Cari pelanggan, keterangan..." value="<?= e($search) ?>">
                
                <select name="status" class="search-input" style="min-width: 150px;">
                    <option value="">-- Semua Status --</option>
                    <option value="Lunas" <?= $statusFilter === 'Lunas' ? 'selected' : '' ?>>Lunas</option>
                    <option value="Belum Lunas" <?= $statusFilter === 'Belum Lunas' ? 'selected' : '' ?>>Belum Lunas</option>
                </select>

                <button type="submit" class="btn btn-outline btn-sm">Filter</button>
                <?php if (!empty($search) || !empty($statusFilter)): ?>
                    <a href="<?= base_url('admin/transactions/index.php') ?>" class="btn btn-outline btn-sm">Reset</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>Tanggal</th>
                    <th>Pelanggan (JOIN)</th>
                    <th>Referensi Booking</th>
                    <th>Keterangan Transaksi</th>
                    <th>Total Biaya</th>
                    <th>Status Pembayaran</th>
                    <th style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($transactions)): ?>
                    <tr>
                        <td colspan="8" class="empty-state">Tidak ada transaksi ditemukan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($transactions as $tx): ?>
                        <tr>
                            <td>#<?= e($tx['id']) ?></td>
                            <td><?= format_date($tx['transaction_date']) ?></td>
                            <td>
                                <div><strong><?= e($tx['customer_name']) ?></strong></div>
                                <div style="font-size: 0.8rem; color: var(--admin-text-muted);">📞 <?= e($tx['customer_phone']) ?></div>
                            </td>
                            <td>
                                <?php if (!empty($tx['booking_code'])): ?>
                                    <a href="<?= base_url('admin/bookings/detail.php?id=' . $tx['booking_id']) ?>" style="font-weight: 700;">
                                        <?= e($tx['booking_code']) ?>
                                    </a>
                                <?php else: ?>
                                    <span style="color: var(--admin-text-muted); font-size: 0.85rem;">Pembelian Langsung</span>
                                <?php endif; ?>
                            </td>
                            <td><?= e($tx['description']) ?></td>
                            <td><strong style="color: var(--color-primary);"><?= format_rupiah($tx['total']) ?></strong></td>
                            <td><?= get_payment_badge($tx['payment_status']) ?></td>
                            <td>
                                <div class="actions-cell">
                                    <a href="<?= base_url('admin/transactions/edit.php?id=' . $tx['id']) ?>" class="btn btn-outline btn-sm">Edit</a>
                                    
                                    <form action="<?= base_url('admin/transactions/delete.php') ?>" method="POST" class="form-delete" data-name="transaksi #<?= e($tx['id']) ?>" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $tx['id'] ?>">
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


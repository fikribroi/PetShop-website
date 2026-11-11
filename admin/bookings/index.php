<?php
/**
 * Data Booking Penitipan & Layanan - PawHouse Admin
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$pdo = db();

$search = trim($_GET['q'] ?? '');
$statusFilter = trim($_GET['status'] ?? '');

// Query 4 Tabel: Bookings + Customers + Pets + Services
$sql = "
    SELECT 
        b.*,
        cust.name AS customer_name,
        cust.phone AS customer_phone,
        pet.name AS pet_name,
        pet.type AS pet_type,
        pet.breed AS pet_breed,
        s.name AS service_name,
        s.price AS service_price
    FROM bookings b
    JOIN customers cust ON b.customer_id = cust.id
    JOIN pets pet ON b.pet_id = pet.id
    JOIN services s ON b.service_id = s.id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (b.booking_code LIKE ? OR cust.name LIKE ? OR pet.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($statusFilter)) {
    $sql .= " AND b.status = ?";
    $params[] = $statusFilter;
}

$sql .= " ORDER BY b.created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$adminTitle = 'Booking Penitipan - PawHouse Admin';
$activeMenu = 'bookings';
require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Kelola Booking Penitipan</h1>
        <p class="page-subtitle">Daftar reservasi masuk dari website, verifikasi tanggal, dan pembaharuan status penitipan</p>
    </div>
</div>

<div class="data-card">
    <div class="data-card-header">
        <h2 class="data-card-title">Daftar Booking (<?= count($bookings) ?> Data)</h2>
        <div class="card-toolbar">
            <form action="<?= base_url('admin/bookings/index.php') ?>" method="GET" style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                <input type="text" name="q" class="search-input" placeholder="Cari kode, nama, hewan..." value="<?= e($search) ?>">
                
                <select name="status" class="search-input" style="min-width: 160px;">
                    <option value="">-- Semua Status --</option>
                    <?php
                        $statuses = ['Menunggu', 'Dikonfirmasi', 'Sedang Dititipkan', 'Selesai', 'Dibatalkan'];
                        foreach ($statuses as $st) {
                            $selected = ($statusFilter === $st) ? 'selected' : '';
                            echo "<option value=\"$st\" $selected>$st</option>";
                        }
                    ?>
                </select>

                <button type="submit" class="btn btn-outline btn-sm">Filter</button>
                <?php if (!empty($search) || !empty($statusFilter)): ?>
                    <a href="<?= base_url('admin/bookings/index.php') ?>" class="btn btn-outline btn-sm">Reset</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>Kode Booking</th>
                    <th>Pelanggan (Pemilik)</th>
                    <th>Hewan (JOIN)</th>
                    <th>Paket Layanan</th>
                    <th>Periode Penitipan</th>
                    <th>Status</th>
                    <th style="width: 160px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="7" class="empty-state">Tidak ada data booking yang sesuai.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bookings as $b): ?>
                        <tr>
                            <td>
                                <strong><?= e($b['booking_code']) ?></strong>
                                <div style="font-size: 0.75rem; color: var(--admin-text-muted);"><?= format_date($b['created_at']) ?></div>
                            </td>
                            <td>
                                <div><strong><?= e($b['customer_name']) ?></strong></div>
                                <div style="font-size: 0.8rem; color: var(--admin-text-muted);">📞 <?= e($b['customer_phone']) ?></div>
                            </td>
                            <td>
                                <?= e($b['pet_name']) ?>
                                <span class="badge badge-secondary" style="font-size: 0.7rem;"><?= e($b['pet_type']) ?></span>
                            </td>
                            <td>
                                <div><?= e($b['service_name']) ?></div>
                                <div style="font-size: 0.8rem; color: var(--admin-text-muted);"><?= format_rupiah($b['service_price']) ?></div>
                            </td>
                            <td>
                                <div style="font-size: 0.85rem;">
                                    <?= format_date($b['start_date']) ?> s.d. <?= format_date($b['end_date']) ?>
                                </div>
                            </td>
                            <td><?= get_booking_badge($b['status']) ?></td>
                            <td>
                                <div class="actions-cell">
                                    <a href="<?= base_url('admin/bookings/detail.php?id=' . $b['id']) ?>" class="btn btn-outline btn-sm" title="Lihat Rincian">Detail</a>
                                    <a href="<?= base_url('admin/bookings/edit-status.php?id=' . $b['id']) ?>" class="btn btn-primary btn-sm" title="Ubah Status">Status</a>
                                    
                                    <form action="<?= base_url('admin/bookings/delete.php') ?>" method="POST" class="form-delete" data-name="booking <?= e($b['booking_code']) ?>" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $b['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm" title="Hapus Booking">&times;</button>
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


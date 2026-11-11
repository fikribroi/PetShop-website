<?php
/**
 * Data Layanan & Penitipan - PawHouse Admin
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$pdo = db();
$search = trim($_GET['q'] ?? '');

$sql = "
    SELECT 
        s.*,
        (SELECT COUNT(*) FROM bookings WHERE service_id = s.id) AS total_bookings
    FROM services s
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (s.name LIKE ? OR s.description LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY s.price ASC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$services = $stmt->fetchAll();

$adminTitle = 'Layanan & Penitipan - PawHouse Admin';
$activeMenu = 'services';
require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Kelola Layanan & Penitipan</h1>
        <p class="page-subtitle">Daftar paket kamar penitipan hotel hewan, grooming spa, dan tarif harian</p>
    </div>
    <div>
        <a href="<?= base_url('admin/services/create.php') ?>" class="btn btn-primary">+ Tambah Layanan</a>
    </div>
</div>

<div class="data-card">
    <div class="data-card-header">
        <h2 class="data-card-title">Daftar Layanan (<?= count($services) ?> Layanan)</h2>
        <div class="card-toolbar">
            <form action="<?= base_url('admin/services/index.php') ?>" method="GET" style="display:flex; gap:0.5rem;">
                <input type="text" name="q" class="search-input" placeholder="Cari layanan..." value="<?= e($search) ?>">
                <button type="submit" class="btn btn-outline btn-sm">Cari</button>
                <?php if (!empty($search)): ?>
                    <a href="<?= base_url('admin/services/index.php') ?>" class="btn btn-outline btn-sm">Reset</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>Nama Layanan</th>
                    <th>Deskripsi & Fasilitas</th>
                    <th>Tarif Biaya</th>
                    <th>Estimasi Durasi</th>
                    <th>Total Dipesan</th>
                    <th style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($services)): ?>
                    <tr>
                        <td colspan="7" class="empty-state">Tidak ada layanan ditemukan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($services as $s): ?>
                        <tr>
                            <td>#<?= e($s['id']) ?></td>
                            <td><strong><?= e($s['name']) ?></strong></td>
                            <td>
                                <div style="font-size: 0.85rem; max-width: 300px; color: var(--admin-text-muted); white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= e($s['description'] ?: '-') ?>
                                </div>
                            </td>
                            <td><strong style="color: var(--color-accent);"><?= format_rupiah($s['price']) ?></strong></td>
                            <td><span class="badge badge-secondary"><?= e($s['duration']) ?></span></td>
                            <td><span class="badge badge-primary"><?= e($s['total_bookings']) ?> Kali</span></td>
                            <td>
                                <div class="actions-cell">
                                    <a href="<?= base_url('admin/services/edit.php?id=' . $s['id']) ?>" class="btn btn-outline btn-sm">Edit</a>
                                    
                                    <form action="<?= base_url('admin/services/delete.php') ?>" method="POST" class="form-delete" data-name="layanan <?= e($s['name']) ?>" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $s['id'] ?>">
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


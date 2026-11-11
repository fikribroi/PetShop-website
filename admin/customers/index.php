<?php
/**
 * Data Pelanggan - PawHouse Admin
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$pdo = db();
$search = trim($_GET['q'] ?? '');

$sql = "
    SELECT 
        c.*,
        (SELECT COUNT(*) FROM pets WHERE customer_id = c.id) AS total_pets,
        (SELECT COUNT(*) FROM bookings WHERE customer_id = c.id) AS total_bookings
    FROM customers c
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (c.name LIKE ? OR c.phone LIKE ? OR c.email LIKE ? OR c.address LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$sql .= " ORDER BY c.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$customers = $stmt->fetchAll();

$adminTitle = 'Data Pelanggan - PawHouse Admin';
$activeMenu = 'customers';
require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Kelola Data Pelanggan</h1>
        <p class="page-subtitle">Daftar kontak pemilik hewan, nomor telepon, dan riwayat kepemilikan peliharaan</p>
    </div>
    <div>
        <a href="<?= base_url('admin/customers/create.php') ?>" class="btn btn-primary">+ Tambah Pelanggan</a>
    </div>
</div>

<div class="data-card">
    <div class="data-card-header">
        <h2 class="data-card-title">Daftar Pelanggan (<?= count($customers) ?> Orang)</h2>
        <div class="card-toolbar">
            <form action="<?= base_url('admin/customers/index.php') ?>" method="GET" style="display:flex; gap:0.5rem;">
                <input type="text" name="q" class="search-input" placeholder="Cari nama, telp, email..." value="<?= e($search) ?>">
                <button type="submit" class="btn btn-outline btn-sm">Cari</button>
                <?php if (!empty($search)): ?>
                    <a href="<?= base_url('admin/customers/index.php') ?>" class="btn btn-outline btn-sm">Reset</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>Nama Pemilik</th>
                    <th>Kontak (Telepon & Email)</th>
                    <th>Alamat Domisili</th>
                    <th>Hewan Dimiliki</th>
                    <th>Total Reservasi</th>
                    <th style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($customers)): ?>
                    <tr>
                        <td colspan="7" class="empty-state">Tidak ada data pelanggan ditemukan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($customers as $c): ?>
                        <tr>
                            <td>#<?= e($c['id']) ?></td>
                            <td><strong><?= e($c['name']) ?></strong></td>
                            <td>
                                <div>📞 <?= e($c['phone']) ?></div>
                                <div style="font-size: 0.8rem; color: var(--admin-text-muted);"><?= e($c['email'] ?: 'Tanpa Email') ?></div>
                            </td>
                            <td><?= e($c['address'] ?: '-') ?></td>
                            <td><span class="badge badge-primary"><?= e($c['total_pets']) ?> Ekor</span></td>
                            <td><span class="badge badge-secondary"><?= e($c['total_bookings']) ?> Kali</span></td>
                            <td>
                                <div class="actions-cell">
                                    <a href="<?= base_url('admin/customers/edit.php?id=' . $c['id']) ?>" class="btn btn-outline btn-sm">Edit</a>
                                    
                                    <form action="<?= base_url('admin/customers/delete.php') ?>" method="POST" class="form-delete" data-name="pelanggan <?= e($c['name']) ?>" style="display:inline;">
                                        <?= csrf_field() ?>
                                        <input type="hidden" name="id" value="<?= $c['id'] ?>">
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


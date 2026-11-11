<?php
/**
 * Data Hewan Peliharaan - PawHouse Admin
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$pdo = db();
$search = trim($_GET['q'] ?? '');
$typeFilter = trim($_GET['type'] ?? '');

$sql = "
    SELECT 
        pet.*,
        cust.name AS owner_name,
        cust.phone AS owner_phone
    FROM pets pet
    JOIN customers cust ON pet.customer_id = cust.id
    WHERE 1=1
";
$params = [];

if (!empty($search)) {
    $sql .= " AND (pet.name LIKE ? OR pet.breed LIKE ? OR cust.name LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

if (!empty($typeFilter)) {
    $sql .= " AND pet.type = ?";
    $params[] = $typeFilter;
}

$sql .= " ORDER BY pet.id DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$pets = $stmt->fetchAll();

$adminTitle = 'Data Hewan Peliharaan - PawHouse Admin';
$activeMenu = 'pets';
require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Kelola Data Hewan Peliharaan</h1>
        <p class="page-subtitle">Profil anabul terdaftar, ras, usia, dan relasi pemilik (JOIN Customers)</p>
    </div>
    <div>
        <a href="<?= base_url('admin/pets/create.php') ?>" class="btn btn-primary">+ Tambah Hewan</a>
    </div>
</div>

<div class="data-card">
    <div class="data-card-header">
        <h2 class="data-card-title">Daftar Hewan (<?= count($pets) ?> Ekor)</h2>
        <div class="card-toolbar">
            <form action="<?= base_url('admin/pets/index.php') ?>" method="GET" style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                <input type="text" name="q" class="search-input" placeholder="Cari nama hewan, ras, pemilik..." value="<?= e($search) ?>">
                
                <select name="type" class="search-input" style="min-width: 140px;">
                    <option value="">-- Semua Jenis --</option>
                    <option value="Kucing" <?= $typeFilter === 'Kucing' ? 'selected' : '' ?>>Kucing</option>
                    <option value="Anjing" <?= $typeFilter === 'Anjing' ? 'selected' : '' ?>>Anjing</option>
                    <option value="Kelinci" <?= $typeFilter === 'Kelinci' ? 'selected' : '' ?>>Kelinci</option>
                    <option value="Hamster" <?= $typeFilter === 'Hamster' ? 'selected' : '' ?>>Hamster</option>
                </select>

                <button type="submit" class="btn btn-outline btn-sm">Filter</button>
                <?php if (!empty($search) || !empty($typeFilter)): ?>
                    <a href="<?= base_url('admin/pets/index.php') ?>" class="btn btn-outline btn-sm">Reset</a>
                <?php endif; ?>
            </form>
        </div>
    </div>

    <div class="table-responsive">
        <table class="admin-table">
            <thead>
                <tr>
                    <th style="width: 50px;">ID</th>
                    <th>Nama Hewan</th>
                    <th>Jenis & Ras</th>
                    <th>Umur</th>
                    <th>Pemilik (JOIN)</th>
                    <th>Catatan Khusus</th>
                    <th style="width: 140px;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($pets)): ?>
                    <tr>
                        <td colspan="7" class="empty-state">Tidak ada data hewan ditemukan.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($pets as $p): ?>
                        <tr>
                            <td>#<?= e($p['id']) ?></td>
                            <td><strong><?= e($p['name']) ?></strong></td>
                            <td>
                                <span class="badge badge-primary"><?= e($p['type']) ?></span>
                                <span style="font-size: 0.85rem; color: var(--admin-text-muted); margin-left: 4px;"><?= e($p['breed'] ?: 'Campuran') ?></span>
                            </td>
                            <td><?= e($p['age']) ?> Tahun</td>
                            <td>
                                <div><strong><?= e($p['owner_name']) ?></strong></div>
                                <div style="font-size: 0.8rem; color: var(--admin-text-muted);">📞 <?= e($p['owner_phone']) ?></div>
                            </td>
                            <td>
                                <div style="font-size: 0.85rem; max-width: 240px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                    <?= e($p['notes'] ?: '-') ?>
                                </div>
                            </td>
                            <td>
                                <div class="actions-cell">
                                    <a href="<?= base_url('admin/pets/edit.php?id=' . $p['id']) ?>" class="btn btn-outline btn-sm">Edit</a>
                                    
                                    <form action="<?= base_url('admin/pets/delete.php') ?>" method="POST" class="form-delete" data-name="hewan <?= e($p['name']) ?>" style="display:inline;">
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


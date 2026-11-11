<?php
/**
 * Edit Hewan Peliharaan - PawHouse Admin
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    redirect(base_url('admin/pets/index.php'), 'ID Hewan tidak valid.', 'danger');
}

$pdo = db();
$stmt = $pdo->prepare("SELECT * FROM pets WHERE id = ?");
$stmt->execute([$id]);
$pet = $stmt->fetch();

if (!$pet) {
    redirect(base_url('admin/pets/index.php'), 'Hewan tidak ditemukan.', 'danger');
}

$customers = $pdo->query("SELECT id, name, phone FROM customers ORDER BY name ASC")->fetchAll();
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    $customerId = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
    $name       = trim($_POST['name'] ?? '');
    $type       = trim($_POST['type'] ?? '');
    $breed      = trim($_POST['breed'] ?? '');
    $age        = filter_input(INPUT_POST, 'age', FILTER_VALIDATE_INT);
    $notes      = trim($_POST['notes'] ?? '');

    if (!$customerId) {
        $error = 'Pilih pemilik hewan dari daftar pelanggan.';
    } elseif (empty($name)) {
        $error = 'Nama hewan wajib diisi.';
    } elseif (empty($type)) {
        $error = 'Jenis hewan wajib dipilih.';
    } elseif ($age === false || $age < 0) {
        $error = 'Umur hewan tidak boleh bernilai negatif.';
    } else {
        $stmtUpdate = $pdo->prepare("
            UPDATE pets 
            SET customer_id = ?, name = ?, type = ?, breed = ?, age = ?, notes = ? 
            WHERE id = ?
        ");
        $stmtUpdate->execute([
            $customerId,
            $name,
            $type,
            !empty($breed) ? $breed : null,
            $age,
            !empty($notes) ? $notes : null,
            $id
        ]);

        redirect(base_url('admin/pets/index.php'), 'Data hewan "' . $name . '" berhasil diperbarui.', 'success');
    }
}

$adminTitle = 'Edit Hewan - PawHouse Admin';
$activeMenu = 'pets';
require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Edit Data Hewan</h1>
        <p class="page-subtitle">Perbarui data ras, usia, atau kepemilikan hewan #<?= e($pet['id']) ?></p>
    </div>
    <div>
        <a href="<?= base_url('admin/pets/index.php') ?>" class="btn btn-outline">← Kembali ke Daftar</a>
    </div>
</div>

<div class="form-card">
    <?php if ($error): ?>
        <div class="alert alert-danger"><span><?= e($error) ?></span></div>
    <?php endif; ?>

    <form action="<?= base_url('admin/pets/edit.php?id=' . $id) ?>" method="POST">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="customer_id" class="form-label">Pemilik Hewan (Relasi Pelanggan) <span class="req">*</span></label>
            <select name="customer_id" id="customer_id" class="form-control" required>
                <?php foreach ($customers as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (($pet['customer_id'] == $c['id']) || (($_POST['customer_id'] ?? '') == $c['id'])) ? 'selected' : '' ?>>
                        <?= e($c['name']) ?> (<?= e($c['phone']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="name" class="form-label">Nama Hewan <span class="req">*</span></label>
                <input type="text" name="name" id="name" class="form-control" required value="<?= e($_POST['name'] ?? $pet['name']) ?>">
            </div>

            <div class="form-group">
                <label for="type" class="form-label">Jenis Hewan <span class="req">*</span></label>
                <select name="type" id="type" class="form-control" required>
                    <?php
                        $types = ['Kucing', 'Anjing', 'Kelinci', 'Hamster', 'Lainnya'];
                        $currentType = $_POST['type'] ?? $pet['type'];
                    ?>
                    <?php foreach ($types as $t): ?>
                        <option value="<?= $t ?>" <?= $currentType === $t ? 'selected' : '' ?>><?= $t ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="breed" class="form-label">Ras / Breed (Opsional)</label>
                <input type="text" name="breed" id="breed" class="form-control" value="<?= e($_POST['breed'] ?? $pet['breed']) ?>">
            </div>

            <div class="form-group">
                <label for="age" class="form-label">Perkiraan Umur (Tahun) <span class="req">*</span></label>
                <input type="number" name="age" id="age" class="form-control" min="0" required value="<?= e($_POST['age'] ?? $pet['age']) ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="notes" class="form-label">Catatan Kesehatan / Kebiasaan</label>
            <textarea name="notes" id="notes" class="form-control"><?= e($_POST['notes'] ?? $pet['notes']) ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="<?= base_url('admin/pets/index.php') ?>" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>


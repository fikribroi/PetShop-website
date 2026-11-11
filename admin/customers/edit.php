<?php
/**
 * Edit Pelanggan - PawHouse Admin
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    redirect(base_url('admin/customers/index.php'), 'ID Pelanggan tidak valid.', 'danger');
}

$pdo = db();
$stmt = $pdo->prepare("SELECT * FROM customers WHERE id = ?");
$stmt->execute([$id]);
$customer = $stmt->fetch();

if (!$customer) {
    redirect(base_url('admin/customers/index.php'), 'Pelanggan tidak ditemukan.', 'danger');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    $name    = trim($_POST['name'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $address = trim($_POST['address'] ?? '');

    if (empty($name)) {
        $error = 'Nama pelanggan wajib diisi.';
    } elseif (empty($phone)) {
        $error = 'Nomor telepon wajib diisi.';
    } else {
        $stmtUpdate = $pdo->prepare("
            UPDATE customers 
            SET name = ?, phone = ?, email = ?, address = ? 
            WHERE id = ?
        ");
        $stmtUpdate->execute([
            $name, 
            $phone, 
            !empty($email) ? $email : null, 
            !empty($address) ? $address : null, 
            $id
        ]);

        redirect(base_url('admin/customers/index.php'), 'Data pelanggan "' . $name . '" berhasil diperbarui.', 'success');
    }
}

$adminTitle = 'Edit Pelanggan - PawHouse Admin';
$activeMenu = 'customers';
require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Edit Data Pelanggan</h1>
        <p class="page-subtitle">Perbarui data kontak atau alamat pelanggan #<?= e($customer['id']) ?></p>
    </div>
    <div>
        <a href="<?= base_url('admin/customers/index.php') ?>" class="btn btn-outline">← Kembali ke Daftar</a>
    </div>
</div>

<div class="form-card">
    <?php if ($error): ?>
        <div class="alert alert-danger"><span><?= e($error) ?></span></div>
    <?php endif; ?>

    <form action="<?= base_url('admin/customers/edit.php?id=' . $id) ?>" method="POST">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="name" class="form-label">Nama Lengkap <span class="req">*</span></label>
            <input type="text" name="name" id="name" class="form-control" required value="<?= e($_POST['name'] ?? $customer['name']) ?>">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="phone" class="form-label">No. Telepon / WhatsApp <span class="req">*</span></label>
                <input type="tel" name="phone" id="phone" class="form-control" required value="<?= e($_POST['phone'] ?? $customer['phone']) ?>">
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Alamat Email</label>
                <input type="email" name="email" id="email" class="form-control" value="<?= e($_POST['email'] ?? $customer['email']) ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="address" class="form-label">Alamat Domisili</label>
            <textarea name="address" id="address" class="form-control"><?= e($_POST['address'] ?? $customer['address']) ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="<?= base_url('admin/customers/index.php') ?>" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>


<?php
/**
 * Tambah Pelanggan - PawHouse Admin
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

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
        $pdo = db();
        $stmt = $pdo->prepare("
            INSERT INTO customers (name, phone, email, address) 
            VALUES (?, ?, ?, ?)
        ");
        $stmt->execute([
            $name, 
            $phone, 
            !empty($email) ? $email : null, 
            !empty($address) ? $address : null
        ]);

        redirect(base_url('admin/customers/index.php'), 'Pelanggan "' . $name . '" berhasil ditambahkan.', 'success');
    }
}

$adminTitle = 'Tambah Pelanggan - PawHouse Admin';
$activeMenu = 'customers';
require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Tambah Pelanggan Baru</h1>
        <p class="page-subtitle">Daftarkan data kontak dan alamat pemilik hewan</p>
    </div>
    <div>
        <a href="<?= base_url('admin/customers/index.php') ?>" class="btn btn-outline">← Kembali ke Daftar</a>
    </div>
</div>

<div class="form-card">
    <?php if ($error): ?>
        <div class="alert alert-danger"><span><?= e($error) ?></span></div>
    <?php endif; ?>

    <form action="<?= base_url('admin/customers/create.php') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="name" class="form-label">Nama Lengkap <span class="req">*</span></label>
            <input type="text" name="name" id="name" class="form-control" placeholder="Contoh: Raditya Dika" required autofocus value="<?= e($_POST['name'] ?? '') ?>">
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="phone" class="form-label">No. Telepon / WhatsApp <span class="req">*</span></label>
                <input type="tel" name="phone" id="phone" class="form-control" placeholder="Contoh: 081234567890" required value="<?= e($_POST['phone'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label for="email" class="form-label">Alamat Email</label>
                <input type="email" name="email" id="email" class="form-control" placeholder="Contoh: nama@domain.com" value="<?= e($_POST['email'] ?? '') ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="address" class="form-label">Alamat Domisili</label>
            <textarea name="address" id="address" class="form-control" placeholder="Jl. Nama Jalan No. XX, Kota..."><?= e($_POST['address'] ?? '') ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan Pelanggan</button>
            <a href="<?= base_url('admin/customers/index.php') ?>" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>


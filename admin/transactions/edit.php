<?php
/**
 * Edit Transaksi Keuangan - PawHouse Admin
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    redirect(base_url('admin/transactions/index.php'), 'ID Transaksi tidak valid.', 'danger');
}

$pdo = db();
$stmt = $pdo->prepare("SELECT * FROM transactions WHERE id = ?");
$stmt->execute([$id]);
$transaction = $stmt->fetch();

if (!$transaction) {
    redirect(base_url('admin/transactions/index.php'), 'Transaksi tidak ditemukan.', 'danger');
}

$customers = $pdo->query("SELECT id, name, phone FROM customers ORDER BY name ASC")->fetchAll();
$bookings = $pdo->query("
    SELECT b.id, b.booking_code, cust.name AS customer_name 
    FROM bookings b 
    JOIN customers cust ON b.customer_id = cust.id 
    ORDER BY b.id DESC 
    LIMIT 30
")->fetchAll();

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    $customerId      = filter_input(INPUT_POST, 'customer_id', FILTER_VALIDATE_INT);
    $bookingId       = filter_input(INPUT_POST, 'booking_id', FILTER_VALIDATE_INT);
    $transactionDate = trim($_POST['transaction_date'] ?? '');
    $description     = trim($_POST['description'] ?? '');
    $total           = filter_input(INPUT_POST, 'total', FILTER_VALIDATE_FLOAT);
    $paymentStatus   = trim($_POST['payment_status'] ?? 'Belum Lunas');

    if (!$customerId) {
        $error = 'Pilih pelanggan yang melakukan transaksi.';
    } elseif (empty($transactionDate)) {
        $error = 'Tanggal transaksi wajib diisi.';
    } elseif (empty($description)) {
        $error = 'Keterangan transaksi wajib diisi.';
    } elseif ($total === false || $total < 0) {
        $error = 'Total biaya harus berupa angka bernilai positif.';
    } elseif (!in_array($paymentStatus, ['Lunas', 'Belum Lunas'], true)) {
        $error = 'Status pembayaran tidak valid.';
    } else {
        $stmtUpdate = $pdo->prepare("
            UPDATE transactions 
            SET customer_id = ?, booking_id = ?, transaction_date = ?, description = ?, total = ?, payment_status = ? 
            WHERE id = ?
        ");
        $stmtUpdate->execute([
            $customerId,
            $bookingId ?: null,
            $transactionDate,
            $description,
            $total,
            $paymentStatus,
            $id
        ]);

        redirect(base_url('admin/transactions/index.php'), 'Data transaksi #' . $id . ' berhasil diperbarui.', 'success');
    }
}

$adminTitle = 'Edit Transaksi #' . $transaction['id'] . ' - PawHouse Admin';
$activeMenu = 'transactions';
require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Edit Transaksi #<?= e($transaction['id']) ?></h1>
        <p class="page-subtitle">Ubah nominal, keterangan, atau status pembayaran (Lunas / Belum Lunas)</p>
    </div>
    <div>
        <a href="<?= base_url('admin/transactions/index.php') ?>" class="btn btn-outline">← Kembali ke Daftar</a>
    </div>
</div>

<div class="form-card">
    <?php if ($error): ?>
        <div class="alert alert-danger"><span><?= e($error) ?></span></div>
    <?php endif; ?>

    <form action="<?= base_url('admin/transactions/edit.php?id=' . $id) ?>" method="POST">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="customer_id" class="form-label">Pelanggan Pembeli <span class="req">*</span></label>
            <select name="customer_id" id="customer_id" class="form-control" required>
                <?php foreach ($customers as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (($transaction['customer_id'] == $c['id']) || (($_POST['customer_id'] ?? '') == $c['id'])) ? 'selected' : '' ?>>
                        <?= e($c['name']) ?> (<?= e($c['phone']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="booking_id" class="form-label">Hubungkan dengan Booking (Opsional)</label>
            <select name="booking_id" id="booking_id" class="form-control">
                <option value="">-- Tanpa Hubungan Booking (Pembelian Toko Langsung) --</option>
                <?php foreach ($bookings as $b): ?>
                    <option value="<?= $b['id'] ?>" <?= (($transaction['booking_id'] == $b['id']) || (($_POST['booking_id'] ?? '') == $b['id'])) ? 'selected' : '' ?>>
                        Tiket <?= e($b['booking_code']) ?> — <?= e($b['customer_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="transaction_date" class="form-label">Tanggal Transaksi <span class="req">*</span></label>
                <input type="date" name="transaction_date" id="transaction_date" class="form-control" required value="<?= e($_POST['transaction_date'] ?? $transaction['transaction_date']) ?>">
            </div>

            <div class="form-group">
                <label for="total" class="form-label">Total Pembayaran (Rp) <span class="req">*</span></label>
                <input type="number" name="total" id="total" class="form-control" min="0" step="1000" required value="<?= e($_POST['total'] ?? $transaction['total']) ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Keterangan Transaksi <span class="req">*</span></label>
            <input type="text" name="description" id="description" class="form-control" required value="<?= e($_POST['description'] ?? $transaction['description']) ?>">
        </div>

        <div class="form-group">
            <label for="payment_status" class="form-label">Status Pembayaran <span class="req">*</span></label>
            <select name="payment_status" id="payment_status" class="form-control" required>
                <?php $currentPay = $_POST['payment_status'] ?? $transaction['payment_status']; ?>
                <option value="Belum Lunas" <?= $currentPay === 'Belum Lunas' ? 'selected' : '' ?>>Belum Lunas (Pending)</option>
                <option value="Lunas" <?= $currentPay === 'Lunas' ? 'selected' : '' ?>>Lunas (Diterima)</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            <a href="<?= base_url('admin/transactions/index.php') ?>" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>


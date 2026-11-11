<?php
/**
 * Tambah Transaksi Keuangan - PawHouse Admin
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$pdo = db();
$customers = $pdo->query("SELECT id, name, phone FROM customers ORDER BY name ASC")->fetchAll();
$bookings = $pdo->query("
    SELECT b.id, b.booking_code, cust.name AS customer_name 
    FROM bookings b 
    JOIN customers cust ON b.customer_id = cust.id 
    ORDER BY b.id DESC 
    LIMIT 20
")->fetchAll();

$defaultCustomerId = filter_input(INPUT_GET, 'customer_id', FILTER_VALIDATE_INT);
$defaultBookingId  = filter_input(INPUT_GET, 'booking_id', FILTER_VALIDATE_INT);
$defaultTotal      = filter_input(INPUT_GET, 'total', FILTER_VALIDATE_FLOAT) ?: '';
$defaultDesc       = trim($_GET['desc'] ?? '');

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
        $stmt = $pdo->prepare("
            INSERT INTO transactions (customer_id, booking_id, transaction_date, description, total, payment_status) 
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $customerId,
            $bookingId ?: null,
            $transactionDate,
            $description,
            $total,
            $paymentStatus
        ]);

        redirect(base_url('admin/transactions/index.php'), 'Transaksi berhasil dicatat ke sistem.', 'success');
    }
}

$adminTitle = 'Tambah Transaksi - PawHouse Admin';
$activeMenu = 'transactions';
require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Catat Transaksi Baru</h1>
        <p class="page-subtitle">Pencatatan pembayaran layanan penitipan, grooming, atau pembelian item produk</p>
    </div>
    <div>
        <a href="<?= base_url('admin/transactions/index.php') ?>" class="btn btn-outline">← Kembali ke Daftar</a>
    </div>
</div>

<div class="form-card">
    <?php if ($error): ?>
        <div class="alert alert-danger"><span><?= e($error) ?></span></div>
    <?php endif; ?>

    <form action="<?= base_url('admin/transactions/create.php') ?>" method="POST">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="customer_id" class="form-label">Pelanggan Pembeli <span class="req">*</span></label>
            <select name="customer_id" id="customer_id" class="form-control" required autofocus>
                <option value="">-- Pilih Pelanggan --</option>
                <?php foreach ($customers as $c): ?>
                    <option value="<?= $c['id'] ?>" <?= (($defaultCustomerId == $c['id']) || (($_POST['customer_id'] ?? '') == $c['id'])) ? 'selected' : '' ?>>
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
                    <option value="<?= $b['id'] ?>" <?= (($defaultBookingId == $b['id']) || (($_POST['booking_id'] ?? '') == $b['id'])) ? 'selected' : '' ?>>
                        Tiket <?= e($b['booking_code']) ?> — <?= e($b['customer_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
            <div class="form-help">Pilih jika transaksi ini merupakan pelunasan tiket booking penitipan hewan.</div>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="transaction_date" class="form-label">Tanggal Transaksi <span class="req">*</span></label>
                <input type="date" name="transaction_date" id="transaction_date" class="form-control" required value="<?= e($_POST['transaction_date'] ?? date('Y-m-d')) ?>">
            </div>

            <div class="form-group">
                <label for="total" class="form-label">Total Pembayaran (Rp) <span class="req">*</span></label>
                <input type="number" name="total" id="total" class="form-control" placeholder="Contoh: 150000" min="0" step="1000" required value="<?= e($_POST['total'] ?? $defaultTotal) ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="description" class="form-label">Keterangan Transaksi <span class="req">*</span></label>
            <input type="text" name="description" id="description" class="form-control" placeholder="Contoh: Penitipan VIP Kucing Mochi (4 hari) + Wet Food" required value="<?= e($_POST['description'] ?? $defaultDesc) ?>">
        </div>

        <div class="form-group">
            <label for="payment_status" class="form-label">Status Pembayaran <span class="req">*</span></label>
            <select name="payment_status" id="payment_status" class="form-control" required>
                <option value="Belum Lunas" <?= (($_POST['payment_status'] ?? '') === 'Belum Lunas') ? 'selected' : '' ?>>Belum Lunas (Pending)</option>
                <option value="Lunas" <?= (($_POST['payment_status'] ?? 'Lunas') === 'Lunas') ? 'selected' : '' ?>>Lunas (Diterima)</option>
            </select>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan Transaksi</button>
            <a href="<?= base_url('admin/transactions/index.php') ?>" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>


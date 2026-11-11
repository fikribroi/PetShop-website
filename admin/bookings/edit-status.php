<?php
/**
 * Update Status Booking - PawHouse Admin
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    redirect(base_url('admin/bookings/index.php'), 'ID Booking tidak valid.', 'danger');
}

$pdo = db();
$stmt = $pdo->prepare("
    SELECT 
        b.*,
        cust.name AS customer_name,
        pet.name AS pet_name,
        s.name AS service_name
    FROM bookings b
    JOIN customers cust ON b.customer_id = cust.id
    JOIN pets pet ON b.pet_id = pet.id
    JOIN services s ON b.service_id = s.id
    WHERE b.id = ?
");
$stmt->execute([$id]);
$booking = $stmt->fetch();

if (!$booking) {
    redirect(base_url('admin/bookings/index.php'), 'Data booking tidak ditemukan.', 'danger');
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    $status    = trim($_POST['status'] ?? '');
    $startDate = trim($_POST['start_date'] ?? '');
    $endDate   = trim($_POST['end_date'] ?? '');
    $notes     = trim($_POST['notes'] ?? '');

    $allowedStatuses = ['Menunggu', 'Dikonfirmasi', 'Sedang Dititipkan', 'Selesai', 'Dibatalkan'];

    if (!in_array($status, $allowedStatuses, true)) {
        $error = 'Pilihan status tidak valid.';
    } elseif (empty($startDate) || empty($endDate)) {
        $error = 'Tanggal mulai dan selesai penitipan wajib diisi.';
    } elseif ($endDate < $startDate) {
        $error = 'Tanggal selesai tidak boleh lebih awal dari tanggal mulai.';
    } else {
        $stmtUpdate = $pdo->prepare("
            UPDATE bookings 
            SET status = ?, start_date = ?, end_date = ?, notes = ? 
            WHERE id = ?
        ");
        $stmtUpdate->execute([$status, $startDate, $endDate, !empty($notes) ? $notes : null, $id]);

        redirect(base_url('admin/bookings/detail.php?id=' . $id), 'Status booking berhasil diubah menjadi "' . $status . '".', 'success');
    }
}

$adminTitle = 'Ubah Status Booking ' . $booking['booking_code'] . ' - PawHouse Admin';
$activeMenu = 'bookings';
require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Perbarui Status Booking</h1>
        <p class="page-subtitle">Tiket <?= e($booking['booking_code']) ?> — <?= e($booking['customer_name']) ?> (<?= e($booking['pet_name']) ?>)</p>
    </div>
    <div>
        <a href="<?= base_url('admin/bookings/detail.php?id=' . $id) ?>" class="btn btn-outline">← Kembali ke Rincian</a>
    </div>
</div>

<div class="form-card">
    <?php if ($error): ?>
        <div class="alert alert-danger"><span><?= e($error) ?></span></div>
    <?php endif; ?>

    <form action="<?= base_url('admin/bookings/edit-status.php?id=' . $id) ?>" method="POST">
        <?= csrf_field() ?>

        <div class="form-group">
            <label for="status" class="form-label">Status Reservasi <span class="req">*</span></label>
            <select name="status" id="status" class="form-control" required style="font-weight: 700; font-size: 1rem;">
                <?php
                    $statuses = [
                        'Menunggu'          => 'Menunggu Konfirmasi',
                        'Dikonfirmasi'      => 'Dikonfirmasi (Siap Masuk)',
                        'Sedang Dititipkan' => 'Sedang Dititipkan di Kamar Hotel',
                        'Selesai'           => 'Selesai (Sudah Dijemput)',
                        'Dibatalkan'        => 'Dibatalkan'
                    ];
                    $currentStatus = $_POST['status'] ?? $booking['status'];
                    foreach ($statuses as $val => $lbl) {
                        $selected = ($currentStatus === $val) ? 'selected' : '';
                        echo "<option value=\"$val\" $selected>$lbl</option>";
                    }
                ?>
            </select>
        </div>

        <div class="form-row">
            <div class="form-group">
                <label for="start_date" class="form-label">Tanggal Mulai Penitipan <span class="req">*</span></label>
                <input type="date" name="start_date" id="start_date" class="form-control" required value="<?= e($_POST['start_date'] ?? $booking['start_date']) ?>">
            </div>

            <div class="form-group">
                <label for="end_date" class="form-label">Tanggal Selesai Penitipan <span class="req">*</span></label>
                <input type="date" name="end_date" id="end_date" class="form-control" required value="<?= e($_POST['end_date'] ?? $booking['end_date']) ?>">
            </div>
        </div>

        <div class="form-group">
            <label for="notes" class="form-label">Catatan Tambahan Petugas</label>
            <textarea name="notes" id="notes" class="form-control"><?= e($_POST['notes'] ?? $booking['notes']) ?></textarea>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">Simpan Status Booking</button>
            <a href="<?= base_url('admin/bookings/detail.php?id=' . $id) ?>" class="btn btn-outline">Batal</a>
        </div>
    </form>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>


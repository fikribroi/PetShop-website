<?php
/**
 * Rincian Detail Booking - PawHouse Admin
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
        cust.phone AS customer_phone,
        cust.email AS customer_email,
        cust.address AS customer_address,
        pet.name AS pet_name,
        pet.type AS pet_type,
        pet.breed AS pet_breed,
        pet.age AS pet_age,
        pet.notes AS pet_notes,
        s.name AS service_name,
        s.price AS service_price,
        s.duration AS service_duration
    FROM bookings b
    JOIN customers cust ON b.customer_id = cust.id
    JOIN pets pet ON b.pet_id = pet.id
    JOIN services s ON b.service_id = s.id
    WHERE b.id = ?
    LIMIT 1
");
$stmt->execute([$id]);
$booking = $stmt->fetch();

if (!$booking) {
    redirect(base_url('admin/bookings/index.php'), 'Data booking tidak ditemukan.', 'danger');
}

// Cek apakah sudah ada transaksi yang terhubung
$stmtTx = $pdo->prepare("SELECT * FROM transactions WHERE booking_id = ? LIMIT 1");
$stmtTx->execute([$id]);
$transaction = $stmtTx->fetch();

$startDate = new DateTime($booking['start_date']);
$endDate = new DateTime($booking['end_date']);
$days = $startDate->diff($endDate)->days + 1;
$totalEstimated = $days * (float)$booking['service_price'];

$adminTitle = 'Detail Booking ' . $booking['booking_code'] . ' - PawHouse Admin';
$activeMenu = 'bookings';
require_once __DIR__ . '/../../includes/admin-header.php';
?>

<div class="page-header">
    <div>
        <h1 class="page-title">Rincian Booking #<?= e($booking['booking_code']) ?></h1>
        <p class="page-subtitle">Informasi lengkap reservasi penitipan, hewan, dan tagihan</p>
    </div>
    <div style="display: flex; gap: 0.75rem;">
        <a href="<?= base_url('admin/bookings/edit-status.php?id=' . $booking['id']) ?>" class="btn btn-primary">Ubah Status Booking</a>
        <a href="<?= base_url('admin/bookings/index.php') ?>" class="btn btn-outline">← Kembali ke Daftar</a>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem; align-items: start;">
    <!-- Kolom Kiri: Rincian Lengkap -->
    <div>
        <div class="data-card" style="margin-bottom: 1.5rem;">
            <div class="data-card-header">
                <h2 class="data-card-title">Informasi Reservasi & Jadwal</h2>
                <?= get_booking_badge($booking['status']) ?>
            </div>
            <div style="padding: 1.5rem;">
                <div class="status-details-grid">
                    <div class="status-detail-item">
                        <div class="label">Kode Tiket</div>
                        <div class="value" style="color: var(--color-primary);"><?= e($booking['booking_code']) ?></div>
                    </div>
                    <div class="status-detail-item">
                        <div class="label">Tanggal Pengajuan</div>
                        <div class="value"><?= format_date($booking['created_at'], true) ?></div>
                    </div>
                    <div class="status-detail-item">
                        <div class="label">Tanggal Mulai Titip</div>
                        <div class="value"><?= format_date($booking['start_date']) ?></div>
                    </div>
                    <div class="status-detail-item">
                        <div class="label">Tanggal Selesai Titip</div>
                        <div class="value"><?= format_date($booking['end_date']) ?></div>
                    </div>
                    <div class="status-detail-item">
                        <div class="label">Total Durasi</div>
                        <div class="value"><?= $days ?> Hari</div>
                    </div>
                    <div class="status-detail-item">
                        <div class="label">Estimasi Biaya Dasar</div>
                        <div class="value" style="color: var(--color-accent);"><?= format_rupiah($totalEstimated) ?></div>
                    </div>
                </div>

                <?php if (!empty($booking['notes'])): ?>
                    <div style="margin-top: 1.5rem; padding: 1rem; background: var(--admin-bg); border-radius: var(--radius-md);">
                        <strong style="font-size: 0.85rem; color: var(--admin-text-muted); text-transform: uppercase;">Catatan Tambahan dari Pemilik:</strong>
                        <div style="margin-top: 0.35rem; font-size: 0.95rem;"><?= nl2br(e($booking['notes'])) ?></div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="data-card">
            <div class="data-card-header">
                <h2 class="data-card-title">Hewan & Pemilik (Relasi Database)</h2>
            </div>
            <div style="padding: 1.5rem;">
                <div class="status-details-grid">
                    <div class="status-detail-item">
                        <div class="label">Nama Hewan</div>
                        <div class="value">🐾 <?= e($booking['pet_name']) ?></div>
                    </div>
                    <div class="status-detail-item">
                        <div class="label">Jenis & Ras</div>
                        <div class="value"><?= e($booking['pet_type']) ?> (<?= e($booking['pet_breed'] ?: 'Campuran') ?>)</div>
                    </div>
                    <div class="status-detail-item">
                        <div class="label">Usia Hewan</div>
                        <div class="value"><?= e($booking['pet_age']) ?> Tahun</div>
                    </div>
                    <div class="status-detail-item">
                        <div class="label">Pemilik Terdaftar</div>
                        <div class="value">👤 <?= e($booking['customer_name']) ?></div>
                    </div>
                    <div class="status-detail-item">
                        <div class="label">No. Telepon</div>
                        <div class="value">📞 <?= e($booking['customer_phone']) ?></div>
                    </div>
                    <div class="status-detail-item">
                        <div class="label">Email & Alamat</div>
                        <div class="value" style="font-size: 0.9rem; font-weight: normal;">
                            <?= e($booking['customer_email'] ?: '-') ?><br>
                            <?= e($booking['customer_address'] ?: '-') ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Kolom Kanan: Status Transaksi & Pembayaran -->
    <div>
        <div class="data-card">
            <div class="data-card-header">
                <h2 class="data-card-title">Status Transaksi</h2>
            </div>
            <div style="padding: 1.5rem;">
                <?php if ($transaction): ?>
                    <div style="margin-bottom: 1rem;">
                        <span style="font-size: 0.8rem; color: var(--admin-text-muted);">Status Pembayaran:</span>
                        <div style="margin-top: 4px;"><?= get_payment_badge($transaction['payment_status']) ?></div>
                    </div>
                    <div style="margin-bottom: 1rem;">
                        <span style="font-size: 0.8rem; color: var(--admin-text-muted);">Nominal Tagihan:</span>
                        <div style="font-size: 1.35rem; font-weight: 800; color: var(--color-primary);"><?= format_rupiah($transaction['total']) ?></div>
                    </div>
                    <div style="margin-bottom: 1.5rem;">
                        <span style="font-size: 0.8rem; color: var(--admin-text-muted);">Keterangan Tagihan:</span>
                        <div style="font-size: 0.9rem; margin-top: 2px;"><?= e($transaction['description']) ?></div>
                    </div>
                    <a href="<?= base_url('admin/transactions/edit.php?id=' . $transaction['id']) ?>" class="btn btn-outline btn-block btn-sm">
                        Ubah Tagihan / Status Bayar
                    </a>
                <?php else: ?>
                    <p style="font-size: 0.85rem; color: var(--admin-text-muted); margin-bottom: 1.25rem;">
                        Booking ini belum memiliki catatan transaksi keuangan resmi di tabel transactions.
                    </p>
                    <a href="<?= base_url('admin/transactions/create.php?customer_id=' . $booking['customer_id'] . '&booking_id=' . $booking['id'] . '&total=' . $totalEstimated . '&desc=' . urlencode('Penitipan ' . $booking['pet_name'] . ' (' . $days . ' hari) - ' . $booking['service_name'])) ?>" class="btn btn-accent btn-block btn-sm">
                        + Buatkan Transaksi Tagihan
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../../includes/admin-footer.php'; ?>


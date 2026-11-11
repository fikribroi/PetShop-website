<?php
/**
 * Cek Status Booking Online - PawHouse
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$bookingCode = trim($_GET['booking_code'] ?? '');
$phone       = trim($_GET['phone'] ?? '');

$booking = null;
$searched = false;
$errorMessage = null;

if (!empty($bookingCode) && !empty($phone)) {
    $searched = true;
    $pdo = db();

    $stmt = $pdo->prepare("
        SELECT 
            b.*,
            cust.name AS customer_name,
            cust.phone AS customer_phone,
            pet.name AS pet_name,
            pet.type AS pet_type,
            pet.breed AS pet_breed,
            s.name AS service_name,
            s.price AS service_price,
            s.duration AS service_duration
        FROM bookings b
        JOIN customers cust ON b.customer_id = cust.id
        JOIN pets pet ON b.pet_id = pet.id
        JOIN services s ON b.service_id = s.id
        WHERE UPPER(b.booking_code) = UPPER(?) AND cust.phone = ?
        LIMIT 1
    ");
    $stmt->execute([$bookingCode, $phone]);
    $booking = $stmt->fetch();

    if (!$booking) {
        $errorMessage = 'Data booking tidak ditemukan. Pastikan kombinasi Kode Booking dan Nomor Telepon sudah benar.';
    }
} elseif ($searched && (empty($bookingCode) || empty($phone))) {
    $errorMessage = 'Mohon masukkan Kode Booking dan Nomor Telepon.';
}

$pageTitle = 'Cek Status Booking Penitipan - PawHouse';
$activeNav = 'status';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="section" style="padding-top: 3rem;">
    <div class="container" style="max-width: 780px;">
        <div class="section-header" style="text-align: left; margin-bottom: 2rem;">
            <span class="section-subtitle">Lacak Reservasi</span>
            <h1 class="section-title">Cek Status Booking Penitipan</h1>
            <p class="section-desc">Masukkan kode tiket reservasi dan nomor telepon yang Anda daftarkan saat melakukan pemesanan.</p>
        </div>

        <!-- Form Pencarian Status -->
        <div class="form-card" style="margin-bottom: 2rem;">
            <form action="<?= base_url('public/booking-status.php') ?>" method="GET">
                <div class="form-row">
                    <div class="form-group">
                        <label for="booking_code" class="form-label">Kode Booking <span class="req">*</span></label>
                        <input type="text" name="booking_code" id="booking_code" class="form-control" placeholder="Contoh: PAW-20261101-001" required value="<?= e($bookingCode) ?>">
                    </div>

                    <div class="form-group">
                        <label for="phone" class="form-label">No. Telepon / WhatsApp <span class="req">*</span></label>
                        <input type="tel" name="phone" id="phone" class="form-control" placeholder="Contoh: 081234567890" required value="<?= e($phone) ?>">
                    </div>
                </div>

                <div style="margin-top: 0.5rem;">
                    <button type="submit" class="btn btn-primary btn-block">
                        🔍 Cari & Cek Status
                    </button>
                </div>
            </form>
        </div>

        <!-- Hasil Pencarian -->
        <?php if ($errorMessage): ?>
            <div class="alert alert-danger">
                <span><?= e($errorMessage) ?></span>
            </div>
        <?php elseif ($booking): ?>
            <?php
                $startDate = new DateTime($booking['start_date']);
                $endDate = new DateTime($booking['end_date']);
                $days = $startDate->diff($endDate)->days + 1;
            ?>
            <div class="card status-card" style="margin-top: 0;">
                <div class="status-header">
                    <div>
                        <span style="font-size: 0.8rem; color: var(--color-text-muted); text-transform: uppercase;">Kode Reservasi</span>
                        <div style="font-size: 1.5rem; font-weight: 800; color: var(--color-primary);"><?= e($booking['booking_code']) ?></div>
                    </div>
                    <div>
                        <span style="font-size: 0.8rem; color: var(--color-text-muted); display: block; margin-bottom: 4px; text-align: right;">Status Tiket:</span>
                        <?= get_booking_badge($booking['status']) ?>
                    </div>
                </div>

                <div class="status-details-grid">
                    <div class="status-detail-item">
                        <div class="label">Pemilik Hewan</div>
                        <div class="value"><?= e($booking['customer_name']) ?></div>
                    </div>
                    <div class="status-detail-item">
                        <div class="label">Peliharaan</div>
                        <div class="value"><?= e($booking['pet_name']) ?> (<?= e($booking['pet_type']) ?> - <?= e($booking['pet_breed'] ?: 'Campuran') ?>)</div>
                    </div>
                    <div class="status-detail-item">
                        <div class="label">Layanan Penitipan</div>
                        <div class="value"><?= e($booking['service_name']) ?></div>
                    </div>
                    <div class="status-detail-item">
                        <div class="label">Tarif Layanan</div>
                        <div class="value"><?= format_rupiah($booking['service_price']) ?> / hari</div>
                    </div>
                    <div class="status-detail-item">
                        <div class="label">Jadwal Penitipan</div>
                        <div class="value"><?= format_date($booking['start_date']) ?> s.d. <?= format_date($booking['end_date']) ?> (<?= $days ?> Hari)</div>
                    </div>
                    <div class="status-detail-item">
                        <div class="label">Waktu Pendaftaran</div>
                        <div class="value" style="font-size: 0.95rem; font-weight: 500; color: var(--color-text-muted);"><?= format_date($booking['created_at'], true) ?></div>
                    </div>
                </div>

                <?php if (!empty($booking['notes'])): ?>
                    <div style="margin-top: 1.5rem; padding: 1rem; background: var(--color-surface-muted); border-radius: var(--radius-md); font-size: 0.9rem;">
                        <strong>Catatan Penitipan:</strong><br>
                        <?= nl2br(e($booking['notes'])) ?>
                    </div>
                <?php endif; ?>

                <div style="margin-top: 2rem; border-top: 1px solid var(--color-border); padding-top: 1.25rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 1rem;">
                    <div style="font-size: 0.85rem; color: var(--color-text-muted);">
                        Butuh bantuan konfirmasi darurat? Hubungi customer service PawHouse di WhatsApp.
                    </div>
                    <a href="https://wa.me/6281234567890?text=Halo%20PawHouse,%20konfirmasi%20booking%20<?= urlencode($booking['booking_code']) ?>" target="_blank" class="btn btn-outline btn-sm">
                        Chat Admin WhatsApp 💬
                    </a>
                </div>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>


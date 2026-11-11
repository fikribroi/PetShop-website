<?php
/**
 * Formulir Booking Penitipan & Layanan Online - PawHouse
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = db();
$services = $pdo->query("SELECT * FROM services ORDER BY price ASC")->fetchAll();

$errors = [];
$bookingSuccess = null;

$selectedServiceId = filter_input(INPUT_GET, 'service_id', FILTER_VALIDATE_INT) ?? 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    $customerName = trim($_POST['customer_name'] ?? '');
    $phone        = trim($_POST['phone'] ?? '');
    $email        = trim($_POST['email'] ?? '');
    $petName      = trim($_POST['pet_name'] ?? '');
    $petType      = trim($_POST['pet_type'] ?? '');
    $petBreed     = trim($_POST['pet_breed'] ?? '');
    $serviceId    = filter_input(INPUT_POST, 'service_id', FILTER_VALIDATE_INT);
    $startDate    = trim($_POST['start_date'] ?? '');
    $endDate      = trim($_POST['end_date'] ?? '');
    $notes        = trim($_POST['notes'] ?? '');

    // 1. Validasi input wajib
    if (empty($customerName)) {
        $errors[] = 'Nama pemilik wajib diisi.';
    }
    if (empty($phone)) {
        $errors[] = 'Nomor telepon/WhatsApp wajib diisi.';
    } elseif (!preg_match('/^[0-9+\-\s]{8,20}$/', $phone)) {
        $errors[] = 'Format nomor telepon tidak valid.';
    }
    if (empty($petName)) {
        $errors[] = 'Nama hewan peliharaan wajib diisi.';
    }
    if (empty($petType)) {
        $errors[] = 'Jenis hewan wajib dipilih.';
    }
    if (!$serviceId) {
        $errors[] = 'Layanan penitipan wajib dipilih.';
    }

    // 2. Validasi Tanggal
    $today = date('Y-m-d');
    if (empty($startDate) || empty($endDate)) {
        $errors[] = 'Tanggal mulai dan tanggal selesai penitipan wajib diisi.';
    } else {
        if ($startDate < $today) {
            $errors[] = 'Tanggal mulai penitipan tidak boleh tanggal di masa lalu.';
        }
        if ($endDate < $startDate) {
            $errors[] = 'Tanggal selesai penitipan tidak boleh lebih awal dari tanggal mulai.';
        }
    }

    // 3. Simpan ke Database jika tidak ada error
    if (empty($errors)) {
        try {
            $pdo->beginTransaction();

            // A. Cek atau Buat Data Pelanggan berdasarkan No Telepon
            $stmtCust = $pdo->prepare("SELECT id FROM customers WHERE phone = ? LIMIT 1");
            $stmtCust->execute([$phone]);
            $customerId = $stmtCust->fetchColumn();

            if (!$customerId) {
                $stmtInsertCust = $pdo->prepare("
                    INSERT INTO customers (name, phone, email, address) 
                    VALUES (?, ?, ?, NULL)
                ");
                $stmtInsertCust->execute([$customerName, $phone, !empty($email) ? $email : null]);
                $customerId = (int)$pdo->lastInsertId();
            } else {
                // Update nama / email jika ada
                $stmtUpdateCust = $pdo->prepare("
                    UPDATE customers 
                    SET name = ?, email = COALESCE(?, email) 
                    WHERE id = ?
                ");
                $stmtUpdateCust->execute([$customerName, !empty($email) ? $email : null, $customerId]);
            }

            // B. Cek atau Buat Data Hewan Pelanggan
            $stmtPet = $pdo->prepare("
                SELECT id FROM pets 
                WHERE customer_id = ? AND LOWER(name) = LOWER(?) 
                LIMIT 1
            ");
            $stmtPet->execute([$customerId, $petName]);
            $petId = $stmtPet->fetchColumn();

            if (!$petId) {
                $stmtInsertPet = $pdo->prepare("
                    INSERT INTO pets (customer_id, name, type, breed, age, notes) 
                    VALUES (?, ?, ?, ?, 1, ?)
                ");
                $stmtInsertPet->execute([
                    $customerId,
                    $petName,
                    $petType,
                    !empty($petBreed) ? $petBreed : null,
                    $notes
                ]);
                $petId = (int)$pdo->lastInsertId();
            }

            // C. Buat Data Booking
            $bookingCode = generate_booking_code($pdo);
            $stmtInsertBooking = $pdo->prepare("
                INSERT INTO bookings (
                    booking_code, customer_id, pet_id, service_id, 
                    start_date, end_date, notes, status
                ) VALUES (?, ?, ?, ?, ?, ?, ?, 'Menunggu')
            ");
            $stmtInsertBooking->execute([
                $bookingCode,
                $customerId,
                $petId,
                $serviceId,
                $startDate,
                $endDate,
                $notes
            ]);

            $pdo->commit();

            // Ambil nama layanan untuk ditampilkan di tiket konfirmasi
            $stmtSvc = $pdo->prepare("SELECT name, price FROM services WHERE id = ?");
            $stmtSvc->execute([$serviceId]);
            $svcInfo = $stmtSvc->fetch();

            $bookingSuccess = [
                'code'          => $bookingCode,
                'customer_name' => $customerName,
                'phone'         => $phone,
                'pet_name'      => $petName,
                'pet_type'      => $petType,
                'service_name'  => $svcInfo['name'] ?? '',
                'service_price' => $svcInfo['price'] ?? 0,
                'start_date'    => $startDate,
                'end_date'      => $endDate,
                'notes'         => $notes,
            ];

        } catch (Exception $e) {
            $pdo->rollBack();
            error_log("Booking Error: " . $e->getMessage());
            $errors[] = 'Terjadi kesalahan sistem saat memproses booking Anda. Silakan coba kembali.';
        }
    }
}

$pageTitle = 'Formulir Booking Penitipan Online - PawHouse';
$activeNav = 'booking';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="section" style="padding-top: 3rem;">
    <div class="container" style="max-width: 780px;">

        <?php if ($bookingSuccess): ?>
            <!-- Halaman Sukses & Bukti Booking -->
            <div class="card status-card" style="border-top: 5px solid var(--color-success); margin-top: 0;">
                <div style="text-align: center; margin-bottom: 2rem;">
                    <div style="width: 68px; height: 68px; background: var(--color-success-bg); color: var(--color-success); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 2.2rem; margin-bottom: 1rem;">
                        ✓
                    </div>
                    <h2 style="color: var(--color-primary); font-size: 1.8rem; font-weight: 800;">Booking Berhasil Diajukan!</h2>
                    <p style="color: var(--color-text-muted); font-size: 0.95rem; margin-top: 0.35rem;">
                        Simpan kode tiket berikut untuk mengecek status konfirmasi penitipan Anda.
                    </p>

                    <div style="background: var(--color-surface-muted); border: 2px dashed var(--color-primary); border-radius: var(--radius-md); padding: 1.25rem; margin: 1.5rem auto 0; max-width: 380px;">
                        <span style="font-size: 0.8rem; color: var(--color-text-muted); text-transform: uppercase; letter-spacing: 0.05em;">Kode Tiket Booking</span>
                        <div style="font-size: 1.85rem; font-weight: 800; color: var(--color-accent); letter-spacing: 0.05em; margin-top: 0.25rem;">
                            <?= e($bookingSuccess['code']) ?>
                        </div>
                    </div>
                </div>

                <div class="status-details-grid" style="border-top: 1px solid var(--color-border); padding-top: 1.5rem;">
                    <div class="status-detail-item">
                        <div class="label">Nama Pemilik</div>
                        <div class="value"><?= e($bookingSuccess['customer_name']) ?></div>
                    </div>
                    <div class="status-detail-item">
                        <div class="label">Nomor Telepon</div>
                        <div class="value"><?= e($bookingSuccess['phone']) ?></div>
                    </div>
                    <div class="status-detail-item">
                        <div class="label">Nama & Jenis Hewan</div>
                        <div class="value"><?= e($bookingSuccess['pet_name']) ?> (<?= e($bookingSuccess['pet_type']) ?>)</div>
                    </div>
                    <div class="status-detail-item">
                        <div class="label">Pilihan Layanan</div>
                        <div class="value"><?= e($bookingSuccess['service_name']) ?></div>
                    </div>
                    <div class="status-detail-item">
                        <div class="label">Tanggal Penitipan</div>
                        <div class="value">
                            <?= format_date($bookingSuccess['start_date']) ?> s.d. <?= format_date($bookingSuccess['end_date']) ?>
                        </div>
                    </div>
                    <div class="status-detail-item">
                        <div class="label">Status Saat Ini</div>
                        <div class="value"><span class="badge badge-warning">Menunggu Konfirmasi</span></div>
                    </div>
                </div>

                <?php if (!empty($bookingSuccess['notes'])): ?>
                    <div style="margin-top: 1.25rem; padding: 0.85rem; background: var(--color-surface-muted); border-radius: var(--radius-sm); font-size: 0.88rem;">
                        <strong>Catatan Khusus:</strong> <?= e($bookingSuccess['notes']) ?>
                    </div>
                <?php endif; ?>

                <div style="display: flex; gap: 1rem; margin-top: 2rem; justify-content: center; flex-wrap: wrap;">
                    <a href="<?= base_url('public/booking-status.php?booking_code=' . urlencode($bookingSuccess['code']) . '&phone=' . urlencode($bookingSuccess['phone'])) ?>" class="btn btn-primary">
                        Cek Status Booking Sekarang →
                    </a>
                    <a href="<?= base_url('public/booking.php') ?>" class="btn btn-outline">
                        Buat Booking Baru
                    </a>
                </div>
            </div>

        <?php else: ?>
            <!-- Formulir Booking Baru -->
            <div class="section-header" style="text-align: left; margin-bottom: 2rem;">
                <span class="section-subtitle">Reservasi Mudah</span>
                <h1 class="section-title">Formulir Booking Penitipan</h1>
                <p class="section-desc">Mohon isi data diri dan informasi hewan peliharaan Anda dengan lengkap untuk menjamin kenyamanan maksimal.</p>
            </div>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul style="padding-left: 1.25rem;">
                        <?php foreach ($errors as $err): ?>
                            <li><?= e($err) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <div class="form-card">
                <form action="<?= base_url('public/booking.php') ?>" method="POST">
                    <?= csrf_field() ?>

                    <h3 style="color: var(--color-primary); font-size: 1.15rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem;">
                        1. Informasi Pemilik
                    </h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="customer_name" class="form-label">Nama Lengkap <span class="req">*</span></label>
                            <input type="text" name="customer_name" id="customer_name" class="form-control" placeholder="Contoh: Budi Santoso" required value="<?= e($_POST['customer_name'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="phone" class="form-label">No. Telepon / WhatsApp <span class="req">*</span></label>
                            <input type="tel" name="phone" id="phone" class="form-control" placeholder="Contoh: 081234567890" required value="<?= e($_POST['phone'] ?? '') ?>">
                            <div class="form-help">Digunakan saat verifikasi dan pengecekan tiket.</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email" class="form-label">Email (Opsional)</label>
                        <input type="email" name="email" id="email" class="form-control" placeholder="contoh: nama@email.com" value="<?= e($_POST['email'] ?? '') ?>">
                    </div>

                    <h3 style="color: var(--color-primary); font-size: 1.15rem; margin-top: 2rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem;">
                        2. Informasi Hewan Peliharaan
                    </h3>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="pet_name" class="form-label">Nama Hewan <span class="req">*</span></label>
                            <input type="text" name="pet_name" id="pet_name" class="form-control" placeholder="Contoh: Mochi / Brownie" required value="<?= e($_POST['pet_name'] ?? '') ?>">
                        </div>

                        <div class="form-group">
                            <label for="pet_type" class="form-label">Jenis Hewan <span class="req">*</span></label>
                            <select name="pet_type" id="pet_type" class="form-control" required>
                                <option value="">-- Pilih Jenis --</option>
                                <option value="Kucing" <?= (($_POST['pet_type'] ?? '') === 'Kucing') ? 'selected' : '' ?>>Kucing</option>
                                <option value="Anjing" <?= (($_POST['pet_type'] ?? '') === 'Anjing') ? 'selected' : '' ?>>Anjing</option>
                                <option value="Kelinci" <?= (($_POST['pet_type'] ?? '') === 'Kelinci') ? 'selected' : '' ?>>Kelinci</option>
                                <option value="Hamster" <?= (($_POST['pet_type'] ?? '') === 'Hamster') ? 'selected' : '' ?>>Hamster</option>
                                <option value="Lainnya" <?= (($_POST['pet_type'] ?? '') === 'Lainnya') ? 'selected' : '' ?>>Lainnya</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="pet_breed" class="form-label">Ras / Breed (Opsional)</label>
                        <input type="text" name="pet_breed" id="pet_breed" class="form-control" placeholder="Contoh: Persia Peaknose, Golden Retriever, dll" value="<?= e($_POST['pet_breed'] ?? '') ?>">
                    </div>

                    <h3 style="color: var(--color-primary); font-size: 1.15rem; margin-top: 2rem; margin-bottom: 1.25rem; border-bottom: 1px solid var(--color-border); padding-bottom: 0.5rem;">
                        3. Rencana Penitipan & Catatan
                    </h3>

                    <div class="form-group">
                        <label for="service_id" class="form-label">Pilihan Layanan Penitipan <span class="req">*</span></label>
                        <select name="service_id" id="service_id" class="form-control" required>
                            <?php foreach ($services as $svc): ?>
                                <option value="<?= $svc['id'] ?>" <?= (($selectedServiceId == $svc['id']) || (($_POST['service_id'] ?? '') == $svc['id'])) ? 'selected' : '' ?>>
                                    <?= e($svc['name']) ?> — <?= format_rupiah($svc['price']) ?> (<?= e($svc['duration']) ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="start_date" class="form-label">Tanggal Mulai <span class="req">*</span></label>
                            <input type="date" name="start_date" id="start_date" class="form-control" min="<?= date('Y-m-d') ?>" required value="<?= e($_POST['start_date'] ?? date('Y-m-d')) ?>">
                        </div>

                        <div class="form-group">
                            <label for="end_date" class="form-label">Tanggal Selesai <span class="req">*</span></label>
                            <input type="date" name="end_date" id="end_date" class="form-control" min="<?= date('Y-m-d') ?>" required value="<?= e($_POST['end_date'] ?? date('Y-m-d', strtotime('+3 days'))) ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="notes" class="form-label">Catatan Khusus / Permintaan Pakan</label>
                        <textarea name="notes" id="notes" class="form-control" placeholder="Contoh: Alergi ayam, bawa pakan sendiri, minta update WA 2x sehari..."><?= e($_POST['notes'] ?? '') ?></textarea>
                    </div>

                    <div style="margin-top: 2rem;">
                        <button type="submit" class="btn btn-accent btn-lg btn-block">
                            Ajukan Booking Sekarang 🐾
                        </button>
                    </div>
                </form>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>


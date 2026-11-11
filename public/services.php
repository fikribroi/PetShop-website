<?php
/**
 * Layanan & Penitipan Hewan - PawHouse
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pdo = db();
$services = $pdo->query("SELECT * FROM services ORDER BY price ASC")->fetchAll();

$pageTitle = 'Layanan & Penitipan Hewan - PawHouse';
$activeNav = 'services';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="section" style="padding-top: 3rem;">
    <div class="container">
        <div class="section-header">
            <span class="section-subtitle">Pilihan Pelayanan</span>
            <h1 class="section-title">Layanan & Fasilitas PawHouse</h1>
            <p class="section-desc">Mulai dari hotel penitipan harian yang nyaman, perawatan spa grooming medikasi, hingga transportasi antar-jemput hewan kesayangan Anda.</p>
        </div>

        <div class="grid-3">
            <?php foreach ($services as $svc): ?>
                <div class="card service-card">
                    <div class="card-body">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.5rem;">
                            <h3 class="service-title" style="margin-bottom: 0;"><?= e($svc['name']) ?></h3>
                            <span class="badge badge-primary">⏱️ <?= e($svc['duration']) ?></span>
                        </div>
                        <p class="service-desc" style="margin-top: 0.75rem;"><?= nl2br(e($svc['description'])) ?></p>
                        
                        <div class="service-footer">
                            <div>
                                <div style="font-size: 0.75rem; color: var(--color-text-muted); text-transform: uppercase;">Tarif Layanan</div>
                                <div class="service-price"><?= format_rupiah($svc['price']) ?></div>
                            </div>
                            <a href="<?= base_url('public/booking.php?service_id=' . $svc['id']) ?>" class="btn btn-accent btn-sm">
                                Booking Layanan Ini
                            </a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>


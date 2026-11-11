<?php
/**
 * Header Partials - Public Website (PawHouse)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? 'PawHouse - Pet Shop & Penitipan Hewan Peliharaan';
$activeNav = $activeNav ?? 'home';
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <!-- Google Fonts: Inter -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Pure Vanilla CSS (No Tailwind) -->
    <link rel="stylesheet" href="<?= asset_url('css/style.css') ?>">
</head>
<body>

<header class="navbar">
    <div class="container nav-container">
        <a href="<?= base_url('public/index.php') ?>" class="brand-logo">
            <span class="paw-icon">🐾</span>
            <span>PawHouse</span>
        </a>

        <button class="mobile-toggle" aria-label="Toggle navigation">
            ☰
        </button>

        <ul class="nav-links">
            <li><a href="<?= base_url('public/index.php') ?>" class="<?= $activeNav === 'home' ? 'active' : '' ?>">Beranda</a></li>
            <li><a href="<?= base_url('public/products.php') ?>" class="<?= $activeNav === 'products' ? 'active' : '' ?>">Katalog Produk</a></li>
            <li><a href="<?= base_url('public/services.php') ?>" class="<?= $activeNav === 'services' ? 'active' : '' ?>">Layanan & Penitipan</a></li>
            <li><a href="<?= base_url('public/booking.php') ?>" class="<?= $activeNav === 'booking' ? 'active' : '' ?>">Booking Online</a></li>
            <li><a href="<?= base_url('public/booking-status.php') ?>" class="<?= $activeNav === 'status' ? 'active' : '' ?>">Cek Status</a></li>
        </ul>

        <div class="nav-actions">
            <a href="<?= base_url('public/booking.php') ?>" class="btn btn-accent btn-sm">Titipkan Hewan</a>
            <a href="<?= base_url('public/login.php') ?>" class="btn btn-outline btn-sm">Login Admin</a>
        </div>
    </div>
</header>

<main>
<?php if ($flash): ?>
    <div class="container" style="margin-top: 1.5rem;">
        <div class="alert alert-<?= e($flash['type']) ?>">
            <span><?= e($flash['message']) ?></span>
        </div>
    </div>
<?php endif; ?>


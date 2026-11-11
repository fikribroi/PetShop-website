<?php
/**
 * Admin Top Header & Layout Starter (PawHouse)
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/functions.php';
require_once __DIR__ . '/auth.php';

// Proteksi akses admin
require_login(base_url('public/login.php'));

$adminTitle = $adminTitle ?? 'Admin Panel - PawHouse';
$flash = get_flash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($adminTitle) ?></title>
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Pure Vanilla Admin CSS (No Tailwind) -->
    <link rel="stylesheet" href="<?= asset_url('css/admin.css') ?>">
</head>
<body>

<?php require_once __DIR__ . '/admin-sidebar.php'; ?>

<div class="admin-main">
    <header class="admin-header">
        <div class="header-left">
            <button class="sidebar-toggle" aria-label="Toggle sidebar">☰</button>
            <span style="font-weight: 600; color: var(--admin-text-muted); font-size: 0.9rem;">
                PawHouse Management System
            </span>
        </div>
        <div>
            <span class="badge badge-primary">Sistem Aktif (MySQL 9.1 / PHP 8.3)</span>
        </div>
    </header>

    <div class="admin-body">
        <?php if ($flash): ?>
            <div class="alert alert-<?= e($flash['type']) ?>">
                <span><?= e($flash['message']) ?></span>
                <button type="button" onclick="this.parentElement.remove()" style="background:none; border:none; cursor:pointer; font-weight:bold;">&times;</button>
            </div>
        <?php endif; ?>


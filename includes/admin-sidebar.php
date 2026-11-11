<?php
/**
 * Admin Sidebar Component (PawHouse)
 */
require_once __DIR__ . '/auth.php';

$currentAdmin = auth_user();
$activeMenu = $activeMenu ?? 'dashboard';
?>
<aside class="admin-sidebar">
    <a href="<?= base_url('admin/index.php') ?>" class="sidebar-brand">
        <span>🐾 PawHouse</span>
        <span class="brand-badge"><?= e($currentAdmin['role'] ?? 'Admin') ?></span>
    </a>

    <nav class="sidebar-nav">
        <span class="nav-section-title">Menu Utama</span>
        <a href="<?= base_url('admin/index.php') ?>" class="sidebar-link <?= $activeMenu === 'dashboard' ? 'active' : '' ?>">
            <span>📊</span>
            <span>Dashboard</span>
        </a>

        <span class="nav-section-title">Katalog & Layanan</span>
        <a href="<?= base_url('admin/categories/index.php') ?>" class="sidebar-link <?= $activeMenu === 'categories' ? 'active' : '' ?>">
            <span>🏷️</span>
            <span>Kategori Produk</span>
        </a>
        <a href="<?= base_url('admin/products/index.php') ?>" class="sidebar-link <?= $activeMenu === 'products' ? 'active' : '' ?>">
            <span>📦</span>
            <span>Data Produk</span>
        </a>
        <a href="<?= base_url('admin/services/index.php') ?>" class="sidebar-link <?= $activeMenu === 'services' ? 'active' : '' ?>">
            <span>🛁</span>
            <span>Layanan Penitipan</span>
        </a>

        <span class="nav-section-title">Pelanggan & Peliharaan</span>
        <a href="<?= base_url('admin/customers/index.php') ?>" class="sidebar-link <?= $activeMenu === 'customers' ? 'active' : '' ?>">
            <span>👥</span>
            <span>Data Pelanggan</span>
        </a>
        <a href="<?= base_url('admin/pets/index.php') ?>" class="sidebar-link <?= $activeMenu === 'pets' ? 'active' : '' ?>">
            <span>🐶</span>
            <span>Data Hewan</span>
        </a>

        <span class="nav-section-title">Operasional & Keuangan</span>
        <a href="<?= base_url('admin/bookings/index.php') ?>" class="sidebar-link <?= $activeMenu === 'bookings' ? 'active' : '' ?>">
            <span>📅</span>
            <span>Booking Penitipan</span>
        </a>
        <a href="<?= base_url('admin/transactions/index.php') ?>" class="sidebar-link <?= $activeMenu === 'transactions' ? 'active' : '' ?>">
            <span>💳</span>
            <span>Data Transaksi</span>
        </a>

        <span class="nav-section-title">Akses Cepat</span>
        <a href="<?= base_url('public/index.php') ?>" target="_blank" class="sidebar-link">
            <span>🌐</span>
            <span>Lihat Website ↗</span>
        </a>
    </nav>

    <div class="sidebar-footer">
        <div class="user-snippet">
            <div class="user-avatar">
                <?= strtoupper(substr($currentAdmin['name'] ?? 'A', 0, 1)) ?>
            </div>
            <div class="user-info">
                <div class="user-name"><?= e($currentAdmin['name'] ?? 'Admin') ?></div>
                <div class="user-role">@<?= e($currentAdmin['username'] ?? 'admin') ?></div>
            </div>
        </div>
        <a href="<?= base_url('admin/logout.php') ?>" class="btn btn-outline btn-sm btn-block" style="color: #ef0909ff; border-color: rgba(255,255,255,0.2);">
            Keluar (Logout)
        </a>
    </div>
</aside>


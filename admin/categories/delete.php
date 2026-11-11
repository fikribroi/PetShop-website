<?php
/**
 * Hapus Kategori Produk - PawHouse Admin
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(base_url('admin/categories/index.php'), 'Metode request tidak diizinkan.', 'danger');
}

validate_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    redirect(base_url('admin/categories/index.php'), 'ID Kategori tidak valid.', 'danger');
}

$pdo = db();

// Periksa apakah masih ada produk yang menggunakan kategori ini
$stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
$stmtCheck->execute([$id]);
$productCount = $stmtCheck->fetchColumn();

if ($productCount > 0) {
    redirect(
        base_url('admin/categories/index.php'), 
        "Kategori tidak dapat dihapus karena masih digunakan oleh $productCount produk. Ubah atau hapus produk terkait terlebih dahulu.", 
        'danger'
    );
}

$stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
$stmt->execute([$id]);

redirect(base_url('admin/categories/index.php'), 'Kategori berhasil dihapus.', 'success');


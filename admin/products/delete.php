<?php
/**
 * Hapus Produk Pet Shop - PawHouse Admin
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(base_url('admin/products/index.php'), 'Metode request tidak diizinkan.', 'danger');
}

validate_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    redirect(base_url('admin/products/index.php'), 'ID Produk tidak valid.', 'danger');
}

$pdo = db();
$stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
$stmt->execute([$id]);

redirect(base_url('admin/products/index.php'), 'Produk berhasil dihapus.', 'success');


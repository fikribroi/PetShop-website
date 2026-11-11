<?php
/**
 * Hapus Pelanggan - PawHouse Admin
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(base_url('admin/customers/index.php'), 'Metode request tidak diizinkan.', 'danger');
}

validate_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    redirect(base_url('admin/customers/index.php'), 'ID Pelanggan tidak valid.', 'danger');
}

$pdo = db();

// Periksa apakah ada booking terkait
$stmtBookings = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE customer_id = ?");
$stmtBookings->execute([$id]);
if ($stmtBookings->fetchColumn() > 0) {
    redirect(base_url('admin/customers/index.php'), 'Pelanggan tidak dapat dihapus karena memiliki riwayat booking.', 'danger');
}

// Periksa apakah ada transaksi terkait
$stmtTx = $pdo->prepare("SELECT COUNT(*) FROM transactions WHERE customer_id = ?");
$stmtTx->execute([$id]);
if ($stmtTx->fetchColumn() > 0) {
    redirect(base_url('admin/customers/index.php'), 'Pelanggan tidak dapat dihapus karena memiliki catatan transaksi.', 'danger');
}

// Hapus pelanggan (hewan milik pelanggan akan otomatis terhapus via CASCADE)
$stmt = $pdo->prepare("DELETE FROM customers WHERE id = ?");
$stmt->execute([$id]);

redirect(base_url('admin/customers/index.php'), 'Pelanggan berhasil dihapus.', 'success');


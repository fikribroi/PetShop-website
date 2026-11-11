<?php
/**
 * Hapus Layanan & Penitipan - PawHouse Admin
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(base_url('admin/services/index.php'), 'Metode request tidak diizinkan.', 'danger');
}

validate_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    redirect(base_url('admin/services/index.php'), 'ID Layanan tidak valid.', 'danger');
}

$pdo = db();

// Periksa apakah layanan sedang digunakan di data booking
$stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE service_id = ?");
$stmtCheck->execute([$id]);
if ($stmtCheck->fetchColumn() > 0) {
    redirect(base_url('admin/services/index.php'), 'Layanan tidak dapat dihapus karena tercatat dalam data riwayat booking.', 'danger');
}

$stmt = $pdo->prepare("DELETE FROM services WHERE id = ?");
$stmt->execute([$id]);

redirect(base_url('admin/services/index.php'), 'Layanan berhasil dihapus.', 'success');


<?php
/**
 * Hapus Data Booking - PawHouse Admin
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(base_url('admin/bookings/index.php'), 'Metode request tidak diizinkan.', 'danger');
}

validate_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    redirect(base_url('admin/bookings/index.php'), 'ID Booking tidak valid.', 'danger');
}

$pdo = db();
$stmt = $pdo->prepare("DELETE FROM bookings WHERE id = ?");
$stmt->execute([$id]);

redirect(base_url('admin/bookings/index.php'), 'Data booking berhasil dihapus.', 'success');


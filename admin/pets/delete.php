<?php
/**
 * Hapus Hewan Peliharaan - PawHouse Admin
 */
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect(base_url('admin/pets/index.php'), 'Metode request tidak diizinkan.', 'danger');
}

validate_csrf();

$id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    redirect(base_url('admin/pets/index.php'), 'ID Hewan tidak valid.', 'danger');
}

$pdo = db();

// Periksa apakah ada booking dengan hewan ini
$stmtCheck = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE pet_id = ?");
$stmtCheck->execute([$id]);
if ($stmtCheck->fetchColumn() > 0) {
    redirect(base_url('admin/pets/index.php'), 'Hewan tidak dapat dihapus karena tercatat dalam data riwayat booking.', 'danger');
}

$stmt = $pdo->prepare("DELETE FROM pets WHERE id = ?");
$stmt->execute([$id]);

redirect(base_url('admin/pets/index.php'), 'Data hewan berhasil dihapus.', 'success');


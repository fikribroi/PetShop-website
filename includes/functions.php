<?php
/**
 * Helper Functions - Sistem Pet Shop & Penitipan Hewan (PawHouse)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Escape output HTML untuk mencegah serangan XSS
 */
function e(?string $value): string {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
}

/**
 * Format angka ke mata uang Rupiah (IDR)
 */
function format_rupiah($amount): string {
    return 'Rp ' . number_format((float)$amount, 0, ',', '.');
}

/**
 * Format tanggal standar MySQL ke format Indonesia yang mudah dibaca
 */
function format_date(?string $date, bool $withTime = false): string {
    if (!$date) return '-';
    $timestamp = strtotime($date);
    if (!$timestamp) return '-';

    $months = [
        1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
        5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
        9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
    ];

    $day = date('d', $timestamp);
    $month = $months[(int)date('m', $timestamp)];
    $year = date('Y', $timestamp);

    $formatted = "$day $month $year";
    if ($withTime) {
        $formatted .= ' ' . date('H:i', $timestamp) . ' WIB';
    }
    return $formatted;
}

/**
 * Menghasilkan CSRF token untuk perlindungan form
 */
function csrf_token(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

/**
 * Menghasilkan elemen input hidden untuk CSRF
 */
function csrf_field(): string {
    return '<input type="hidden" name="csrf_token" value="' . e(csrf_token()) . '">';
}

/**
 * Memvalidasi token CSRF dari request POST
 */
function validate_csrf(): void {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $token = $_POST['csrf_token'] ?? '';
        if (empty($token) || !hash_equals($_SESSION['csrf_token'] ?? '', $token)) {
            http_response_code(403);
            die("Akses ditolak: Validasi CSRF Token gagal atau sesi telah kedaluwarsa.");
        }
    }
}

/**
 * Helper redirect dengan pesan flash notifikasi
 */
function redirect(string $url, ?string $message = null, string $type = 'success'): void {
    if ($message !== null) {
        $_SESSION['flash'] = [
            'message' => $message,
            'type'    => $type // 'success', 'danger', 'warning', 'info'
        ];
    }
    header("Location: $url");
    exit;
}

/**
 * Mengambil dan menghapus pesan notifikasi flash
 */
function get_flash(): ?array {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

/**
 * Menghasilkan kode booking unik
 * Contoh format: PAW-20261110-8421
 */
function generate_booking_code(PDO $pdo): string {
    $datePart = date('Ymd');
    do {
        $randomPart = strtoupper(substr(bin2hex(random_bytes(2)), 0, 4));
        $code = "PAW-{$datePart}-{$randomPart}";
        
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE booking_code = ?");
        $stmt->execute([$code]);
        $exists = $stmt->fetchColumn() > 0;
    } while ($exists);

    return $code;
}

/**
 * Menghasilkan badge HTML untuk status booking
 */
function get_booking_badge(string $status): string {
    $classes = [
        'Menunggu'          => 'badge-warning',
        'Dikonfirmasi'      => 'badge-info',
        'Sedang Dititipkan' => 'badge-primary',
        'Selesai'           => 'badge-success',
        'Dibatalkan'        => 'badge-danger',
    ];

    $badgeClass = $classes[$status] ?? 'badge-secondary';
    return '<span class="badge ' . $badgeClass . '">' . e($status) . '</span>';
}

/**
 * Menghasilkan badge HTML untuk status pembayaran
 */
function get_payment_badge(string $status): string {
    $class = ($status === 'Lunas') ? 'badge-success' : 'badge-danger';
    return '<span class="badge ' . $class . '">' . e($status) . '</span>';
}


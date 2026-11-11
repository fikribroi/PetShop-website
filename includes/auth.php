<?php
/**
 * Autentikasi & Otorisasi Pengguna Admin (PawHouse)
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/functions.php';

/**
 * Cek apakah user sedang login
 */
function is_logged_in(): bool {
    return isset($_SESSION['user']) && !empty($_SESSION['user']['id']);
}

/**
 * Ambil data user yang sedang login
 */
function auth_user(): ?array {
    return $_SESSION['user'] ?? null;
}

/**
 * Proteksi halaman: pastikan user telah login sebagai admin/staff
 */
function require_login(string $redirectPath = '../../public/login.php'): void {
    if (!is_logged_in()) {
        redirect($redirectPath, 'Silakan login terlebih dahulu untuk mengakses panel admin.', 'warning');
    }
}

/**
 * Proses login: simpan session dan regenerate session ID untuk keamanan
 */
function login_user(array $user): void {
    session_regenerate_id(true);
    $_SESSION['user'] = [
        'id'       => $user['id'],
        'name'     => $user['name'],
        'username' => $user['username'],
        'role'     => $user['role'],
    ];
}

/**
 * Logout: bersihkan session secara menyeluruh
 */
function logout_user(string $redirectUrl = '../public/login.php'): void {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(
            session_name(),
            '',
            time() - 42000,
            $params["path"],
            $params["domain"],
            $params["secure"],
            $params["httponly"]
        );
    }
    session_destroy();
    header("Location: $redirectUrl");
    exit;
}


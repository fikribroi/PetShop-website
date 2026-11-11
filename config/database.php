<?php
/**
 * Konfigurasi Database - Sistem Pet Shop & Penitipan Hewan (PawHouse)
 * Menggunakan PDO (PHP Data Objects) dengan Prepared Statements
 */

// Pengaturan Konfigurasi
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'db_petshop');
define('DB_USER', getenv('DB_USER') ?: 'root');
define('DB_PASS', getenv('DB_PASS') !== false ? getenv('DB_PASS') : '');
define('DB_CHARSET', 'utf8mb4');

// URL Dasar Aplikasi (Otomatis mendeteksi atau fallback relatif)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443)) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'] ?? 'localhost:8000';

$rawDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = str_replace('\\', '/', $rawDir);
if ($scriptDir === '/' || $scriptDir === '.' || $scriptDir === '\\' || empty($scriptDir)) {
    $basePath = '';
} else {
    $parts = explode('/', trim($scriptDir, '/'));
    $filtered = [];
    foreach ($parts as $p) {
        if ($p === 'admin' || $p === 'public') {
            break;
        }
        $filtered[] = $p;
    }
    $basePath = !empty($filtered) ? '/' . implode('/', $filtered) : '';
}

define('BASE_URL', rtrim($protocol . $host . $basePath, '/'));

/**
 * Helper URL Aplikasi
 */
function base_url(string $path = ''): string {
    return BASE_URL . ($path ? '/' . ltrim($path, '/') : '');
}

/**
 * Helper URL Aset (CSS, JS, Gambar)
 */
function asset_url(string $path = ''): string {
    return base_url('assets/' . ltrim($path, '/'));
}

/**
 * Mendapatkan koneksi PDO tunggal (Singleton pattern)
 * 
 * @return PDO
 */
function db(): PDO {
    static $pdo = null;

    if ($pdo === null) {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            DB_HOST,
            DB_PORT,
            DB_NAME,
            DB_CHARSET
        );

        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];

        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            die("Terjadi gangguan koneksi ke database. Pastikan MySQL di Laragon/XAMPP sudah aktif dan database 'db_petshop' sudah diimport.");
        }
    }

    return $pdo;
}

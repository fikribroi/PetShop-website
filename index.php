<?php
/**
 * Root Entrypoint Paw & Stay
 * Meneruskan permintaan ke front-controller di folder public/
 */

declare(strict_types=1);

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$publicPath = __DIR__ . '/public/index.php';

if (file_exists($publicPath)) {
    require_once $publicPath;
} else {
    die('Aplikasi tidak ditemukan.');
}


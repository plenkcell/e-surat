<?php
// Mulai session di awal
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Muat autoloader Composer
require_once __DIR__ . '/../vendor/autoload.php';

// Muat variabel environment dari file .env
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Definisikan konstanta dari .env untuk akses yang lebih mudah dan aman
define('DB_HOST', $_ENV['DB_HOST']);
define('DB_NAME', $_ENV['DB_NAME']);
define('DB_USER', $_ENV['DB_USER']);
define('DB_PASS', $_ENV['DB_PASS']);
define('JWT_SECRET', $_ENV['JWT_SECRET']);

// Set zona waktu default
date_default_timezone_set('Asia/Jakarta');
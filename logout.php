<?php
// Selalu mulai dengan config untuk memastikan session aktif
require_once 'backend/config.php';

// Hapus semua variabel session
$_SESSION = [];

// Hancurkan session
session_destroy();

// Redirect ke halaman login dengan pesan sukses (opsional)
header('Location: login.php');
exit();
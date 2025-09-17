<?php
require_once 'config.php';

// Keamanan Tingkat 1: Pastikan pengguna sudah login.
if (!isset($_SESSION['user'])) {
    http_response_code(403); // Forbidden
    die('Akses ditolak. Anda harus login untuk melihat file ini.');
}

// Keamanan Tingkat 2: Pastikan parameter 'token' ada.
if (!isset($_GET['token'])) {
    http_response_code(400); // Bad Request
    die('Permintaan tidak valid.');
}

try {
    // Ambil kunci rahasia dari environment variables untuk enkripsi/dekripsi
    $encryption_key = JWT_SECRET; 
    $ciphering = "AES-128-CTR";
    $iv_length = openssl_cipher_iv_length($ciphering);
    
    // Dekripsi token untuk mendapatkan path file asli
    $token = urldecode($_GET['token']);
    $decoded_token = base64_decode($token);
    
    $iv = substr($decoded_token, 0, $iv_length);
    $encrypted_path = substr($decoded_token, $iv_length);
    
    $decrypted_path = openssl_decrypt($encrypted_path, $ciphering, $encryption_key, 0, $iv);

    if ($decrypted_path === false) {
        throw new Exception('Gagal mendekripsi path file.');
    }

    // Bangun path file yang aman di server
    // __DIR__ . '/../' akan mengarah ke direktori root 'e-surat/'
    $file_path = __DIR__ . '/../' . $decrypted_path;

    // Keamanan Tingkat 3: Validasi path dan pastikan file ada
    if (!file_exists($file_path) || !is_readable($file_path)) {
        http_response_code(404); // Not Found
        die('File tidak ditemukan atau tidak dapat diakses.');
    }

    // Kirim file ke browser
    header('Content-Type: application/pdf');
    header('Content-Disposition: inline; filename="' . basename($file_path) . '"');
    header('Content-Length: ' . filesize($file_path));
    header('Accept-Ranges: bytes');
    
    // Hapus buffer output sebelum mengirim file
    ob_clean();
    flush();
    
    // Baca dan kirimkan file
    readfile($file_path);
    exit;

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    die('Terjadi kesalahan saat memproses file: ' . $e->getMessage());
}
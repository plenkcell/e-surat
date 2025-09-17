<?php
require_once '../init.php';
require_once '../database.php';
require_once '../../vendor/autoload.php';

use Firebase\JWT\JWT;

// Kunci rahasia dan detail lain untuk JWT
$jwt_secret = 'e-surat-rsifc';
$issuer_claim = "E_SURAT_SERVER";
$audience_claim = "E_SURAT_CLIENT";
$issuedat_claim = time(); // waktu token diterbitkan
$notbefore_claim = $issuedat_claim; // token berlaku sejak
$expire_claim = $issuedat_claim + 3600; // token kedaluwarsa dalam 1 jam

// Mendapatkan data POST
$data = json_decode(file_get_contents("php://input"));

if (empty($data->user_login) || empty($data->pass_login)) {
    http_response_code(400);
    echo json_encode(["message" => "Login gagal. Username dan password harus diisi."]);
    exit();
}

// Koneksi ke database
$database = new Database();
$db = $database->getConnection();

// Cari user berdasarkan user_login (Query diperbarui dengan id_jabatan)
$query = "SELECT nip, nm_pegawai, user_login, pass_login, level, pelihat, kd_unit, id_jabatan FROM rsi_user WHERE user_login = :user_login AND is_aktif = '1' LIMIT 0,1";

$stmt = $db->prepare($query);
$stmt->bindParam(':user_login', $data->user_login);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Jika user ditemukan dan password cocok
if ($user && password_verify($data->pass_login, $user['pass_login'])) {
    
    // Buat payload untuk token (Payload diperbarui dengan id_jabatan)
    $payload = [
        "iss" => $issuer_claim,
        "aud" => $audience_claim,
        "iat" => $issuedat_claim,
        "nbf" => $notbefore_claim,
        "exp" => $expire_claim,
        "data" => [
            "user_login" => $user['user_login'],
            "nm_pegawai" => $user['nm_pegawai'],
            "level"      => $user['level'],
            "pelihat"    => $user['pelihat'],
            "kd_unit"    => $user['kd_unit'],
            "id_jabatan" => $user['id_jabatan']
        ]
    ];

    // Buat JWT
    $jwt = JWT::encode($payload, $jwt_secret, 'HS256');

    http_response_code(200);
    // Respons JSON diperbarui dengan id_jabatan
    echo json_encode([
        "message" => "Login berhasil.",
        "token" => $jwt,
        "user" => [
            "nama"       => $user['nm_pegawai'],
            "level"      => $user['level'],
            "pelihat"    => $user['pelihat'],
            "unit"       => $user['kd_unit'],
            "id_jabatan" => $user['id_jabatan']
        ]
    ]);

} else {
    // Jika login gagal
    http_response_code(401);
    echo json_encode(["message" => "Login gagal. Username atau password salah."]);
}
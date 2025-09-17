<?php
require_once '../config.php';
require_once '../database.php';

// Pastikan hanya pengguna yang sudah login yang bisa mengakses
if (!isset($_SESSION['user']) || !isset($_SESSION['user']['kd_unit'])) {
    http_response_code(401); // Unauthorized
    echo json_encode(["message" => "Akses ditolak. Silakan login terlebih dahulu."]);
    exit();
}

header('Content-Type: application/json');

try {
    $database = new Database();
    $db = $database->getConnection();

    $kd_unit = $_SESSION['user']['kd_unit'];

    $query = "
        SELECT 
            rs.*,
            rdu.id as id_disposisi_unit,
            rdu.is_balas,
            (SELECT GROUP_CONCAT(DISTINCT ru_inner.nm_unit, ' = ', IF(rdu_inner.is_balas='1','Sudah','Belum') SEPARATOR ' | ') 
             FROM rsi_disposisi_unit rdu_inner 
             INNER JOIN rsi_unit ru_inner ON rdu_inner.kd_unit = ru_inner.kd_unit 
             WHERE rdu_inner.id_surat = rs.id_surat) AS status_disposisi_all
        FROM rsi_disposisi_unit rdu
        INNER JOIN rsi_suratmasuk rs ON rdu.id_surat = rs.id_surat
        WHERE rdu.kd_unit = :kd_unit AND rs.stts_surat = 'Belum'
        ORDER BY rs.no_agenda DESC
    ";

    $stmt = $db->prepare($query);
    $stmt->bindParam(':kd_unit', $kd_unit);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Proses hasil untuk menambahkan token file terenkripsi
    $encryption_key = JWT_SECRET;
    $ciphering = "AES-128-CTR";
    $iv_length = openssl_cipher_iv_length($ciphering);
    
    foreach ($results as $key => $row) {
        if (!empty($row['file_surat']) && !empty($row['file_dir'])) {
            $path_to_encrypt = 'file/' . $row['file_dir'] . '/' . $row['file_surat'];
            
            $iv = openssl_random_pseudo_bytes($iv_length);
            $encrypted_path = openssl_encrypt($path_to_encrypt, $ciphering, $encryption_key, 0, $iv);
            
            // Gabungkan IV dengan data terenkripsi, lalu encode agar aman di URL
            $results[$key]['file_token'] = urlencode(base64_encode($iv . $encrypted_path));
        } else {
            $results[$key]['file_token'] = null;
        }
    }


    http_response_code(200);
    echo json_encode($results);

} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["message" => "Gagal mengambil data: " . $e->getMessage()]);
}
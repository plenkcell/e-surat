<?php
require_once 'backend/config.php';

// Jika sudah login, redirect ke index.php
if (isset($_SESSION['user'])) {
    header('Location: index.php');
    exit();
}

// Generate CSRF token jika belum ada
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$error_message = '';

// Proses form jika metode adalah POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Validasi CSRF Token
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $error_message = 'Sesi tidak valid. Silakan muat ulang halaman dan coba lagi.';
    } else {
        // 2. Validasi input
        if (empty($_POST['username']) || empty($_POST['password'])) {
            $error_message = 'Username dan password wajib diisi.';
        } else {
            $user_login = $_POST['username'];
            $pass_login = $_POST['password'];

            require_once 'backend/database.php';
            $database = new Database();
            $db = $database->getConnection();

            // 3. Gunakan Prepared Statement
            $query = "SELECT nip, nm_pegawai, user_login, pass_login, level FROM rsi_user WHERE user_login = :user_login AND is_aktif = '1' LIMIT 1";
            $stmt = $db->prepare($query);
            $stmt->bindParam(':user_login', $user_login, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() == 1) {
                $user = $stmt->fetch(PDO::FETCH_ASSOC);

                if (password_verify($pass_login, $user['pass_login'])) {
                    $_SESSION['user'] = [
                        'user_login' => $user['user_login'],
                        'nm_pegawai' => $user['nm_pegawai'],
                        'level' => $user['level']
                    ];
                    session_regenerate_id(true);
                    header('Location: index.php');
                    exit();
                }
            }
            $error_message = 'Username atau password salah.';
        }
    }
}

// Ambil pesan error dari redirect
if (isset($_SESSION['error'])) {
    $error_message = $_SESSION['error'];
    unset($_SESSION['error']);
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - E-Surat</title>
    
    <link rel="stylesheet" href="assets/css/login.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body class="login-body">
    <div class="shape shape-1"></div>
    <div class="shape shape-2"></div>
    <div class="login-split-container">
        <div class="login-branding">
            <div class="branding-content">
                <i class="fa-solid fa-envelope-open-text"></i>
                <h1>Selamat Datang di E-SURAT</h1>
                <p>Manajemen persuratan digital terpadu untuk efisiensi dan keamanan data.</p>
            </div>
        </div>
        <div class="login-form-wrapper">
            <div class="login-card">
                <div class="login-logo">
                    <i class="fa-solid fa-hospital-user"></i>
                </div>
                <h2>Login Akun</h2>
                <p class="login-subtitle">Silakan masukkan kredensial Anda.</p>
                
                <?php if (!empty($error_message)): ?>
                    <p class="login-error-message"><?php echo htmlspecialchars($error_message, ENT_QUOTES, 'UTF-8'); ?></p>
                <?php endif; ?>

                <form action="login.php" method="post" class="login-form">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8'); ?>">

                    <div class="form-group">
                        <i class="fa-solid fa-user form-icon"></i>
                        <input type="text" id="username" name="username" placeholder="Username" required>
                    </div>
                    <div class="form-group">
                        <i class="fa-solid fa-lock form-icon"></i>
                        <input type="password" id="password" name="password" placeholder="Password" required>
                        <i class="fa-regular fa-eye password-toggle-btn" id="togglePassword"></i>
                    </div>
                    <div class="form-options">
                        <label class="remember-me">
                            <input type="checkbox" name="remember"> Ingat Saya
                        </label>
                    </div>
                    <button type="submit" class="login-btn">Login</button>
                </form>
            </div>
        </div>
    </div>

    <script>
        const togglePassword = document.getElementById('togglePassword');
        const passwordInput = document.getElementById('password');

        if(togglePassword && passwordInput) {
            togglePassword.addEventListener('click', function () {
                const type = passwordInput.getAttribute('type') === 'password' ? 'text' : 'password';
                passwordInput.setAttribute('type', type);
                this.classList.toggle('fa-eye');
                this.classList.toggle('fa-eye-slash');
            });
        }
    </script>
</body>
</html>
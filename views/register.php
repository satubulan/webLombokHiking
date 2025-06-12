<?php
session_start();
require_once '../config.php';

$error = '';
$success = '';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    header('Location: ../index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $phone = trim($_POST['phone']);
    $role = 'user';
    $avatar = 'default.jpg';
    $id = uniqid('u');

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password) || empty($phone)) {
        $error = "Semua field harus diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif ($password !== $confirm_password) {
        $error = "Password tidak cocok.";
    } elseif (strlen($password) < 8) {
        $error = "Password harus minimal 8 karakter.";
    } else {
        $check_query = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $check_query->bind_param("s", $email);
        $check_query->execute();
        $check_query->store_result();

        if ($check_query->num_rows > 0) {
            $error = "Email sudah terdaftar.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $insert_query = $conn->prepare("INSERT INTO users (id, name, email, password, phone, avatar, role, active) VALUES (?, ?, ?, ?, ?, ?, ?, 1)");
            $insert_query->bind_param("sssssss", $id, $name, $email, $hashed_password, $phone, $avatar, $role);

            if ($insert_query->execute()) {
                $success = "Pendaftaran berhasil! Silakan login.";
            } else {
                $error = "Terjadi kesalahan saat menyimpan data.";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - LombokHiking</title>
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background: #f5f5f5;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            padding: 20px;
            overflow-y: auto;
        }
        .auth-container {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
            border: 1px solid #ddd;
            margin: auto;
        }
        .auth-header h2 {
            margin: 0;
            color: #2e8b57;
            text-align: center;
            margin-bottom: 30px;
        }
        .auth-header p {
            text-align: center;
            color: #666;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
            position: relative;
        }
        label {
            display: block;
            margin-bottom: 5px;
        }
        .form-group input {
            width: 100%;
            padding: 12px;
            padding-right: 40px;
            border-radius: 5px;
            border: 1px solid #ccc;
            font-size: 14px;
            box-sizing: border-box;
        }
        .toggle-password {
            position: absolute;
            top: 36px;
            right: 12px;
            font-size: 18px;
            cursor: pointer;
            color: #777;
        }
        .btn {
            width: 100%;
            padding: 12px;
            background-color: #4CAF50;
            color: white;
            font-size: 16px;
            font-weight: bold;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        .error-message {
            background-color: #ffdddd;
            padding: 10px;
            color: #a94442;
            border: 1px solid #a94442;
            border-radius: 5px;
            margin-bottom: 15px;
            text-align: center;
        }
        .success-message {
            color: #4caf50;
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #e8f5e9;
            border-radius: 4px;
        }
        a {
            color: #2e8b57;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="auth-container">
        <div class="auth-header">
            <h2>Registrasi Akun</h2>
        </div>

        <?php if ($error): ?>
            <div class="error-message" style="text-align: center; margin-bottom: 20px;">
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if ($success): ?>
            <div class="success-message">
                <?= htmlspecialchars($success) ?>
                <p style="margin-top: 10px;"><a href="login.php">Login sekarang</a></p>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
        <form class="auth-form" method="POST" action="">
            <div class="form-group">
                <label for="name">Nama Lengkap</label>
                <input type="text" id="name" name="name" required placeholder="Masukkan nama lengkap">
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required placeholder="Masukkan email">
            </div>
            <div class="form-group">
                <label for="phone">Nomor Telepon</label>
                <input type="tel" id="phone" name="phone" required placeholder="Masukkan nomor telepon">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required placeholder="Masukkan password">
                <span class="toggle-password" onclick="togglePassword(this, 'password')">
                    <svg xmlns="http://www.w3.org/2000/svg" id="eyeIconPassword" width="20" height="20" viewBox="0 0 16 16">
                        <path fill="#777" d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path fill="#777" d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                    </svg>
                </span>
            </div>
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password</label>
                <input type="password" name="confirm_password" id="confirm_password" required placeholder="Konfirmasi password">
                <span class="toggle-password" onclick="togglePassword(this, 'confirm_password')">
                    <svg xmlns="http://www.w3.org/2000/svg" id="eyeIconConfirmPassword" width="20" height="20" viewBox="0 0 16 16">
                        <path fill="#777" d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path fill="#777" d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>
                    </svg>
                </span>
            </div>
            <button type="submit" class="btn">Daftar</button>
        </form>
        <?php endif; ?>

        <div class="form-footer">
            Sudah punya akun? <a href="login.php">Login sekarang</a>
        </div>
    </div>
</div>
    <script>
        function togglePassword(el, fieldId) {
            const passwordField = document.getElementById(fieldId);
            const icon = el.querySelector('svg');
            const isPasswordHidden = passwordField.type === 'password';

            passwordField.type = isPasswordHidden ? 'text' : 'password';

            if (passwordField.type === 'password') {
                // Eye open icon
                icon.innerHTML = `<path fill="#777" d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><path fill="#777" d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5zM4.5 8a3.5 3.5 0 1 1 7 0 3.5 3.5 0 0 1-7 0z"/>`;
            } else {
                // Eye slash icon (cleaner version)
                icon.innerHTML = `<path fill="#777" d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8zM1.173 8a13.133 13.133 0 0 1 1.66-2.043C4.12 4.668 5.88 3.5 8 3.5c2.12 0 3.879 1.168 5.168 2.457A13.133 13.133 0 0 1 14.828 8c-.058.087-.122.183-.195.288-.335.48-.83 1.12-1.465 1.755C11.879 11.332 10.119 12.5 8 12.5c-2.12 0-3.879-1.168-5.168-2.457A13.134 13.134 0 0 1 1.172 8z"/><line x1="2" y1="14" x2="14" y2="2" stroke="#777" stroke-width="1.5" stroke-linecap="round"/>`;
            }
        }
    </script>
</body>
</html>

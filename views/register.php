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
        .auth-container {
            max-width: 500px;
            margin: 50px auto;
            padding: 30px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .auth-header {
            text-align: center;
            margin-bottom: 30px;
        }
        .auth-form .form-group {
            margin-bottom: 20px;
        }
        .auth-form label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
        }
        .auth-form input {
            width: 100%;
            padding: 12px;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            font-size: 16px;
        }
        .auth-form input.error {
            border-color: #f44336;
        }
        .error-message {
            color: #f44336;
            margin-top: 5px;
            font-size: 14px;
        }
        .success-message {
            color: #4caf50;
            text-align: center;
            margin-bottom: 20px;
            padding: 10px;
            background-color: #e8f5e9;
            border-radius: 4px;
        }
        .auth-form button {
            width: 100%;
            padding: 12px;
            font-size: 16px;
            background-color: #134E5E;
            color: white;
            border: none;
            border-radius: 4px;
        }
        .form-footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }
        .form-footer a {
            color: #134E5E;
            text-decoration: none;
        }
    </style>
</head>
<body>
<div class="container">
    <div class="auth-container">
        <div class="auth-header">
            <h2>Daftar di LombokHiking</h2>
            <p>Buat akun untuk dapat memesan open trip pendakian</p>
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
                <input type="text" id="name" name="name" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required>
            </div>
            <div class="form-group">
                <label for="phone">Nomor Telepon</label>
                <input type="tel" id="phone" name="phone" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required>
            </div>
            <div class="form-group">
                <label for="confirm_password">Konfirmasi Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required>
            </div>
            <button type="submit">Daftar</button>
        </form>
        <?php endif; ?>

        <div class="form-footer">
            Sudah punya akun? <a href="login.php">Login sekarang</a>
        </div>
    </div>
</div>
</body>
</html>

<?php
session_start();
require_once '../config.php';

$error = '';

// Redirect jika sudah login
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: ../admin/index.php');
    } elseif ($_SESSION['user_role'] === 'guide') {
        header('Location: ../guides/dashboard.php');
    } else {
        header('Location: ../userbiasa/dashboard.php');
    }
    exit();
}

// Proses login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Email dan password wajib diisi.";
    } else {
        $stmt = $conn->prepare("SELECT * FROM users WHERE email = ? AND active = 1");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result && $result->num_rows > 0) {
            $user = $result->fetch_assoc();

            if (!empty($user['id']) && !empty($user['role']) && password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header('Location: ../admin/index.php');
                } elseif ($user['role'] === 'guide') {
                    header('Location: ../guides/dashboard.php');
                } else {
                    header('Location: ../userbiasa/dashboard.php');
                }
                exit();
            } else {
                $error = "Password salah atau data akun tidak lengkap.";
            }
        } else {
            $error = "Akun tidak ditemukan atau belum aktif.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            background: linear-gradient(to right, #134E5E, #71B280);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .auth-container {
            background: #fff;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
        }
        .auth-header h2 {
            margin: 0;
            color: #134E5E;
            text-align: center;
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
            font-weight: bold;
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
            background-color: #134E5E;
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
        a {
            color: #134E5E;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h2>Login Akun</h2>
            <p>Masuk untuk melanjutkan ke dashboard</p>
        </div>

        <?php if (!empty($error)) : ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form class="auth-form" method="POST" action="">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" name="email" id="email" required placeholder="Masukkan email">
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" name="password" id="password" required placeholder="Masukkan password">
                <span class="toggle-password" onclick="togglePassword(this)">
                    <!-- Eye open (default) -->
                    <svg xmlns="http://www.w3.org/2000/svg" id="eyeIcon" width="20" height="20" fill="currentColor" viewBox="0 0 16 16">
                        <path d="M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8z"/>
                        <path d="M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5z"/>
                    </svg>
                </span>
            </div>

            <button type="submit" class="btn">Login</button>
        </form>

        <p style="text-align: center; margin-top: 15px;">
            Belum punya akun? <a href="../views/register.php">Daftar di sini</a>
        </p>
    </div>

    <script>
        function togglePassword(el) {
            const passwordField = document.getElementById('password');
            const icon = el.querySelector('svg');
            const isPassword = passwordField.type === 'password';
            passwordField.type = isPassword ? 'text' : 'password';
            icon.innerHTML = isPassword
                ? `<path d=\"M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8z\"/>
                   <path d=\"M3.707 3.707a1 1 0 0 1 1.414 0L15 13.586l-1.414 1.414L3.707 5.121a1 1 0 0 1 0-1.414z\"/>`
                : `<path d=\"M16 8s-3-5.5-8-5.5S0 8 0 8s3 5.5 8 5.5S16 8 16 8z\"/>
                   <path d=\"M8 5.5a2.5 2.5 0 1 0 0 5 2.5 2.5 0 0 0 0-5z\"/>`;
        }
    </script>
</body>
</html>

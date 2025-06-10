<?php
session_start();
require_once '../config.php';

$error = '';

// Redirect if already logged in
if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_role'] === 'admin') {
        header('Location: ../admin/index.php');
    } elseif ($_SESSION['user_role'] === 'guide') {
        header('Location: ../guides/dashboard.php');
    } else {
        header('Location: dashboard-user.php');
    }
    exit();
}

// Proses form login
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

        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                if ($user['role'] === 'admin') {
                    header('Location: ../admin/index.php');
                } elseif ($user['role'] === 'guide') {
                    header('Location: ../guides/dashboard.php');
                } else {
                    header('Location: dashboard-user.php');
                }
                exit();
            } else {
                $error = "Password salah.";
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
                <label for="password">Kata Sandi</label>
                <input type="password" name="password" id="password" required placeholder="Masukkan password">
            </div>

            <button type="submit" class="btn btn-primary">Login</button>
        </form>

        <p style="text-align: center; margin-top: 15px;">
            Belum punya akun? <a href=".../views/register.php">Daftar di sini</a>
        </p>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>

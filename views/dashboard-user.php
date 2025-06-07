<?php
session_start();
require_once '../config.php';

// Redirect jika belum login
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit();
}

$userName = $_SESSION['user_name'];
$userRole = $_SESSION['user_role'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Pengguna - Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container" style="margin-top: 50px;">
        <h2>Halo, <?php echo htmlspecialchars($userName); ?>!</h2>
        <p>Selamat datang di dashboard Anda sebagai <strong><?php echo htmlspecialchars($userRole); ?></strong>.</p>

        <div style="margin-top: 30px;">
            <a href="guides.php" class="btn btn-primary">Lihat Guide Pendakian</a>
            <a href="contact.php" class="btn btn-secondary" style="margin-left: 10px;">Hubungi Admin</a>
            <a href="../logout.php" class="btn" style="margin-left: 10px;">Logout</a>
        </div>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>
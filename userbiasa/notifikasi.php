<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: ../views/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil notifikasi user
$query = "SELECT * FROM notifikasi WHERE user_id = '$user_id' ORDER BY waktu DESC";
$result = mysqli_query($conn, $query);

// Tandai semua notifikasi sebagai sudah dibaca
mysqli_query($conn, "UPDATE notifikasi SET dibaca = 1 WHERE user_id = '$user_id' AND dibaca = 0");

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Notifikasi</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9f9f9;
            padding: 40px;
        }
        .container {
            max-width: 960px;
            margin: auto;
        }
        h2 {
            text-align: center;
            color: #2e8b57;
            margin-bottom: 30px;
        }
        .notif-card {
            background-color: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 15px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            position: relative;
        }
        .notif-card p {
            margin: 0;
            color: #444;
        }
        .notif-time {
            font-size: 13px;
            color: #888;
            margin-top: 8px;
        }
        .new-dot {
            width: 10px;
            height: 10px;
            background-color: #f39c12;
            border-radius: 50%;
            position: absolute;
            top: 20px;
            right: 20px;
        }
        .back-button {
            text-decoration: none;
            color: #2e8b57;
            font-size: 18px;
            display: inline-block;
            margin-bottom: 25px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-button"><i class="fas fa-arrow-left"></i> Kembali</a>
        <h2>Notifikasi</h2>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="notif-card">
                    <p><?php echo htmlspecialchars($row['pesan']); ?></p>
                    <div class="notif-time"><?php echo date('d M Y, H:i', strtotime($row['waktu'])); ?></div>
                    <?php if (!$row['sudah_dibaca']) : ?>
                        <div class="new-dot" title="Baru"></div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align: center;">Belum ada notifikasi.</p>
        <?php endif; ?>
    </div>
</body>
</html>

<?php
session_start();
require_once '../config.php';

// Cek login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

// Ambil feedback dengan join user
$sql = "SELECT feedback.*, users.name AS user_name 
        FROM feedback
        LEFT JOIN users ON feedback.user_id = users.id
        ORDER BY feedback.created_at DESC";
$result = $conn->query($sql);
$feedbacks = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Feedback Pengguna - Admin Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="nav-section-title">Admin Panel</div>
            <ul class="nav-links">
                <li><a href="index.php" class="nav-link">Dashboard</a></li>
                <li><a href="users.php" class="nav-link">Pengguna</a></li>
                <li><a href="guides.php" class="nav-link">Guide</a></li>
                <li><a href="mountains.php" class="nav-link">Gunung</a></li>
                <li><a href="trips.php" class="nav-link">Trip</a></li>
                <li><a href="bookings.php" class="nav-link">Booking</a></li>
                <li><a href="feedback.php" class="nav-link active">Feedback</a></li>
                <li><a href="profile.php" class="nav-link">Profil</a></li>
                <li><a href="../logout.php" class="nav-link">Logout</a></li>
            </ul>
        </aside>

        <!-- Header -->
        <header class="admin-header">
            <h1>Feedback Pengguna</h1>
        </header>

        <!-- Main Content -->
        <main class="admin-main">
            <table style="width: 100%; border-collapse: collapse; background-color: #fff;">
                <thead>
                    <tr>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd;">ID</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd;">Nama Pengguna</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd;">Pesan</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd;">Dikirim</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($feedbacks) > 0): ?>
                        <?php foreach ($feedbacks as $feedback): ?>
                            <tr>
                                <td style="padding: 10px;"><?php echo $feedback['id']; ?></td>
                                <td style="padding: 10px;"><?php echo htmlspecialchars($feedback['user_name']); ?></td>
                                <td style="padding: 10px;"><?php echo nl2br(htmlspecialchars($feedback['message'])); ?></td>
                                <td style="padding: 10px;"><?php echo $feedback['created_at']; ?></td>
                                <td style="padding: 10px;">
                                    <a href="#" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin hapus pesan ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="padding: 20px; text-align: center;">Belum ada feedback masuk.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>

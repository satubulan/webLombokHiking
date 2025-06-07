<?php
session_start();
require_once '../config.php';

// Cek login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

// Ambil data booking dengan join user dan trip
$sql = "SELECT bookings.*, 
               users.name AS user_name, 
               trips.date AS trip_date, 
               mountains.name AS mountain_name
        FROM bookings
        LEFT JOIN users ON bookings.user_id = users.id
        LEFT JOIN trips ON bookings.trip_id = trips.id
        LEFT JOIN mountains ON trips.mountain_id = mountains.id
        ORDER BY bookings.created_at DESC";
$result = $conn->query($sql);
$bookings = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Booking - Admin Lombok Hiking</title>
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
                <li><a href="bookings.php" class="nav-link active">Booking</a></li>
                <li><a href="feedback.php" class="nav-link">Feedback</a></li>
                <li><a href="profile.php" class="nav-link">Profil</a></li>
                <li><a href="../logout.php" class="nav-link">Logout</a></li>
            </ul>
        </aside>

        <!-- Header -->
        <header class="admin-header">
            <h1>Manajemen Booking</h1>
        </header>

        <!-- Main Content -->
        <main class="admin-main">
            <table style="width: 100%; border-collapse: collapse; background-color: #fff;">
                <thead>
                    <tr>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd;">ID</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd;">Pengguna</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd;">Gunung</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd;">Tanggal Trip</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd;">Status</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd;">Dibuat</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($bookings) > 0): ?>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td style="padding: 10px;"><?php echo $booking['id']; ?></td>
                                <td style="padding: 10px;"><?php echo htmlspecialchars($booking['user_name']); ?></td>
                                <td style="padding: 10px;"><?php echo htmlspecialchars($booking['mountain_name']); ?></td>
                                <td style="padding: 10px;"><?php echo $booking['trip_date']; ?></td>
                                <td style="padding: 10px;">
                                    <?php
                                    $status = $booking['status'];
                                    $badgeColor = $status === 'confirmed' ? 'green' : ($status === 'cancelled' ? 'red' : 'orange');
                                    echo "<span style='color: $badgeColor;'>$status</span>";
                                    ?>
                                </td>
                                <td style="padding: 10px;"><?php echo $booking['created_at']; ?></td>
                                <td style="padding: 10px;">
                                    <a href="#" class="btn btn-secondary btn-sm">Lihat</a>
                                    <a href="#" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin hapus booking ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="padding: 20px; text-align: center;">Tidak ada data booking.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>

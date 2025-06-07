<?php
session_start();
require_once '../config.php';

// Cek login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

// Ambil semua trip dengan join gunung dan guide
$sql = "SELECT trips.*, 
               mountains.name AS mountain_name, 
               guides.name AS guide_name 
        FROM trips
        LEFT JOIN mountains ON trips.mountain_id = mountains.id
        LEFT JOIN guides ON trips.guide_id = guides.id
        ORDER BY trips.date DESC";
$result = $conn->query($sql);
$trips = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Trip - Admin Lombok Hiking</title>
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
                <li><a href="trips.php" class="nav-link active">Trip</a></li>
                <li><a href="bookings.php" class="nav-link">Booking</a></li>
                <li><a href="feedback.php" class="nav-link">Feedback</a></li>
                <li><a href="profile.php" class="nav-link">Profil</a></li>
                <li><a href="../logout.php" class="nav-link">Logout</a></li>
            </ul>
        </aside>

        <!-- Header -->
        <header class="admin-header">
            <h1>Manajemen Trip</h1>
        </header>

        <!-- Main Content -->
        <main class="admin-main">
            <div style="margin-bottom: 20px;">
                <a href="#" class="btn btn-primary">+ Tambah Trip</a>
            </div>

            <table style="width: 100%; border-collapse: collapse; background-color: #fff;">
                <thead>
                    <tr>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd;">ID</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd;">Gunung</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd;">Guide</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd;">Tanggal</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd;">Harga</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd;">Kuota</th>
                        <th style="padding: 10px; border-bottom: 1px solid #ddd;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($trips) > 0): ?>
                        <?php foreach ($trips as $trip): ?>
                            <tr>
                                <td style="padding: 10px;"><?php echo $trip['id']; ?></td>
                                <td style="padding: 10px;"><?php echo htmlspecialchars($trip['mountain_name']); ?></td>
                                <td style="padding: 10px;"><?php echo htmlspecialchars($trip['guide_name']); ?></td>
                                <td style="padding: 10px;"><?php echo $trip['date']; ?></td>
                                <td style="padding: 10px;">Rp<?php echo number_format($trip['price'], 0, ',', '.'); ?></td>
                                <td style="padding: 10px;"><?php echo $trip['quota']; ?></td>
                                <td style="padding: 10px;">
                                    <a href="#" class="btn btn-secondary btn-sm">Edit</a>
                                    <a href="#" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin hapus trip ini?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="padding: 20px; text-align: center;">Belum ada trip terdaftar.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>

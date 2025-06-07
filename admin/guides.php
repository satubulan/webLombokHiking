<?php
session_start();
require_once '../config.php';

// Cek login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

// Ambil semua guide
$result = $conn->query("SELECT * FROM guides ORDER BY id DESC");
$guides = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Guide - Admin Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/guide.css">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="nav-section-title">Admin Panel</div>
        <ul class="nav-links">
            <li><a href="index.php" class="nav-link"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li><a href="users.php" class="nav-link"><i class="fas fa-users"></i> Pengguna</a></li>
            <li><a href="guides.php" class="nav-link active"><i class="fas fa-map-signs"></i> Guide</a></li>
            <li><a href="mountains.php" class="nav-link"><i class="fas fa-mountain"></i> Gunung</a></li>
            <li><a href="trips.php" class="nav-link"><i class="fas fa-route"></i> Trip</a></li>
            <li><a href="bookings.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Booking</a></li>
            <li><a href="feedback.php" class="nav-link"><i class="fas fa-comment-dots"></i> Feedback</a></li>
            <li><a href="profile.php" class="nav-link"><i class="fas fa-user-cog"></i> Profil</a></li>
            <li><a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>


        <!-- Main Content -->
        <main class="admin-main">
            <div style="margin-bottom: 20px;">
                <a href="guide_create.php" class="btn btn-primary">+ Tambah Guide</a>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Rating</th>
                        <th>Bahasa</th>
                        <th>Foto</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($guides) > 0): ?>
                        <?php foreach ($guides as $guide): ?>
                            <tr>
                                <td><?php echo $guide['id']; ?></td>
                                <td><?php echo htmlspecialchars($guide['name']); ?></td>
                                <td><?php echo number_format($guide['rating'], 1); ?></td>
                                <td><?php echo htmlspecialchars($guide['languages']); ?></td>
                                <td>
                                    <img src="../assets/images/<?php echo htmlspecialchars($guide['image_url']); ?>" alt="guide">
                                </td>
                                <td>
                                    <?php echo $guide['active'] ? '<span style="color:green;">Aktif</span>' : '<span style="color:red;">Nonaktif</span>'; ?>
                                </td>
                                <td>
                                    <a href="guide_edit.php?id=<?php echo $guide['id']; ?>" class="btn btn-secondary btn-sm">Edit</a>
                                    <a href="guide_delete.php?id=<?php echo $guide['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Yakin ingin hapus?')">Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="padding: 20px; text-align: center;">Belum ada guide.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>

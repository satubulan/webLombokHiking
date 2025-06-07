<?php
session_start();
require_once '../config.php';

// Cek login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

$adminId = $_SESSION['user_id'];
$query = $conn->prepare("SELECT name, email, phone, role FROM users WHERE id = ?");
$query->bind_param("i", $adminId);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Admin - Lombok Hiking</title>
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
                <li><a href="feedback.php" class="nav-link">Feedback</a></li>
                <li><a href="profile.php" class="nav-link active">Profil</a></li>
                <li><a href="../logout.php" class="nav-link">Logout</a></li>
            </ul>
        </aside>

        <!-- Header -->
        <header class="admin-header">
            <h1>Profil Admin</h1>
        </header>

        <!-- Main Content -->
        <main class="admin-main">
            <div style="background-color: white; padding: 20px; border-radius: 8px; max-width: 600px;">
                <h2 style="margin-bottom: 20px;">Informasi Profil</h2>

                <p><strong>Nama:</strong> <?php echo htmlspecialchars($user['name']); ?></p>
                <p><strong>Email:</strong> <?php echo htmlspecialchars($user['email']); ?></p>
                <p><strong>No. Telepon:</strong> <?php echo htmlspecialchars($user['phone']); ?></p>
                <p><strong>Role:</strong> <?php echo htmlspecialchars($user['role']); ?></p>

                <a href="#" class="btn btn-secondary" style="margin-top: 20px;">Ubah Password</a>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>

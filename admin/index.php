<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

$userName = $_SESSION['user_name'];

// Ambil statistik
$users = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'user'")->fetch_assoc()['total'];
$guides = $conn->query("SELECT COUNT(*) AS total FROM guides")->fetch_assoc()['total'];
$mountains = $conn->query("SELECT COUNT(*) AS total FROM mountains")->fetch_assoc()['total'];
$trips = $conn->query("SELECT COUNT(*) AS total FROM trips")->fetch_assoc()['total'];
$bookings = $conn->query("SELECT COUNT(*) AS total FROM bookings")->fetch_assoc()['total'];
$feedback = $conn->query("SELECT COUNT(*) AS total FROM feedbacks")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">Admin Panel</div>
            <nav>
                <a href="index.php" class="active"><i class="fas fa-chart-line"></i> Dashboard</a>
                <a href="users.php"class= "nav-a"><i class="fas fa-users"></i> Pengguna</a>
                <a href="guides.php"><i class="fas fa-map-signs"></i> Guide</a>
                <a href="mountains.php"><i class="fas fa-mountain"></i> Gunung</a>
                <a href="trips.php"><i class="fas fa-route"></i> Trip</a>
                <a href="bookings.php"><i class="fas fa-calendar-alt"></i> Booking</a>
                <a href="feedback.php"><i class="fas fa-comment-dots"></i> Feedback</a>
                <a href="profile.php"><i class="fas fa-user-cog"></i> Profil</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <!-- Main -->
        <main class="main">
            <header class="admin-header">
                <h1>Selamat Datang, <?php echo htmlspecialchars($userName); ?> 👋</h1>
                <p>Ini adalah ringkasan aktivitas di Lombok Hiking.</p>
            </header>

            <section class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <div>
                        <h3>Pengguna</h3>
                        <p><?= $users ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <i class="fas fa-map-signs"></i>
                    <div>
                        <h3>Guide</h3>
                        <p><?= $guides ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <i class="fas fa-mountain"></i>
                    <div>
                        <h3>Gunung</h3>
                        <p><?= $mountains ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <i class="fas fa-route"></i>
                    <div>
                        <h3>Trip</h3>
                        <p><?= $trips ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <i class="fas fa-calendar-alt"></i>
                    <div>
                        <h3>Booking</h3>
                        <p><?= $bookings ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <i class="fas fa-comment-dots"></i>
                    <div>
                        <h3>Feedback</h3>
                        <p><?= $feedback ?></p>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>


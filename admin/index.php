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
$feedbacks = $conn->query("SELECT COUNT(*) AS total FROM feedbacks")->fetch_assoc()['total'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Lombok Hiking</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* ===== Reset dan Dasar ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
            color: #333;
        }

        /* ===== Layout Utama ===== */
        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        .admin-sidebar {
            width: 220px;
            background-color: #2e8b57;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .admin-sidebar .nav-section-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 30px;
            user-select: none;
        }

        .admin-sidebar .nav-links {
            list-style: none;
            flex-grow: 1;
        }

        .admin-sidebar .nav-links li {
            margin: 10px 0;
        }

        .admin-sidebar .nav-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 6px;
            transition: background 0.3s;
            font-weight: 600;
        }

        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background-color: #246b46;
        }

        /* ===== Main Area ===== */
        .main {
            flex: 1;
            padding: 30px;
            overflow-x: auto;
        }

        .admin-header {
            margin-bottom: 30px;
        }

        .admin-header h1 {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .admin-header p {
            font-size: 14px;
            color: #777;
        }

        /* ===== Stats Grid ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card i {
            font-size: 40px;
            color: #2e8b57;
            margin-bottom: 10px;
        }

        .stat-card h3 {
            font-size: 18px;
            margin-bottom: 5px;
            color: #333;
        }

        .stat-card p {
            font-size: 24px;
            font-weight: bold;
            color: #2e8b57;
        }

        /* ===== Responsive ===== */
        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                gap: 15px;
            }

            .stats-grid {
                grid-template-columns: 1fr; /* Single column on small screens */
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="nav-section-title">Admin Panel</div>
            <ul class="nav-links">
                <li><a href="index.php" class="nav-link active"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                <li><a href="users.php" class="nav-link"><i class="fas fa-users"></i> Pengguna</a></li>
                <li><a href="guides.php" class="nav-link"><i class="fas fa-map-signs"></i> Guide</a></li>
                <li><a href="mountains.php" class="nav-link"><i class="fas fa-mountain"></i> Gunung</a></li>
                <li><a href="trips.php" class="nav-link"><i class="fas fa-route"></i> Trip</a></li>
                <li><a href="profile.php" class="nav-link"><i class="fas fa-user-cog"></i> Profil</a></li>
                <li><a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
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
                        <h3>lihat pembayaran</h3>
                        <p><?= $bookings ?></p>
                    </div>
                </div>
            </section>
        </main>
    </div>
</body>
</html>

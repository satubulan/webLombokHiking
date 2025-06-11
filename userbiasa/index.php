<?php
session_start();
require_once '../config.php';

$unread_result = mysqli_query($conn, "SELECT COUNT(*) AS total FROM notifikasi WHERE user_id = '{$_SESSION['user_id']}' AND dibaca = 0");
$unread = mysqli_fetch_assoc($unread_result)['total'];

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: ../views/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

$notif_result = mysqli_query($conn, "SELECT COUNT(*) AS jumlah FROM notifikasi WHERE user_id = '$user_id' AND dibaca = 0");
$notif_unread = mysqli_fetch_assoc($notif_result)['jumlah'];

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda User - LombokHiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', sans-serif;
            overflow-x: hidden;
        }

        .hero-banner {
            position: relative;
            width: 100%;
            height: 100vh;
            background-image: url('../assets/images/gomendaki.jpg');
            background-size: cover;
            background-position: center;
            display: flex;
            align-items: flex-start;
            justify-content: center;
            padding-top: 180px;
            color: white;
            text-align: center;
        }

        .hero-content {
            background-color: rgba(0, 0, 0, 0.5);
            padding: 40px 60px;
            border-radius: 12px;
        }

        .topbar {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px 20px;
            background: rgba(0, 0, 0, 0.4);
            z-index: 1001;
            transition: transform 0.3s ease;
        }

        .topbar.shifted { transform: translateX(250px); }

        .logo-text { font-size: 24px; color: white; font-weight: bold; }

        .hamburger {
            cursor: pointer;
            width: 30px;
            height: 22px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .hamburger span {
            display: block;
            height: 4px;
            background: white;
            border-radius: 2px;
        }

        .sidebar {
            position: fixed;
            top: 0;
            left: -250px;
            width: 250px;
            height: 100vh;
            background-color: #2e8b57;
            padding: 20px;
            box-shadow: 2px 0 5px rgba(0,0,0,0.2);
            transition: left 0.3s ease;
            z-index: 1000;
        }

        .sidebar.open { left: 0; }

        .sidebar nav a {
            display: block;
            color: white;
            text-decoration: none;
            padding: 10px 0;
            font-weight: bold;
        }

        .overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.4);
            display: none;
            z-index: 998;
        }

        .overlay.active { display: block; }

        .recommendation-section {
            padding: 60px 20px;
            background-color: #f5f5f5;
        }

        .recommendation-section h2 {
            text-align: center;
            margin-bottom: 30px;
            color: #2e8b57;
        }

        .gunung-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            max-width: 1200px;
            margin: 0 auto;
        }

        .gunung-card {
            background-color: white;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            overflow: hidden;
            text-align: left;
        }

        .gunung-card img {
            width: 100%;
            height: 180px;
            object-fit: cover;
        }

        .gunung-card .content {
            padding: 15px;
        }

        .gunung-card h4 {
            margin: 0 0 5px;
            color: #2e8b57;
        }

        .gunung-card p {
            margin: 0 0 8px;
            color: #555;
            font-size: 14px;
        }

        .gunung-card .harga {
            font-weight: bold;
            color: #e67e22;
        }
    </style>
</head>
<body>
    <div class="topbar" id="topbar">
        <div class="logo-text">LombokHiking</div>
        <div class="hamburger" onclick="toggleSidebar()" aria-label="Menu">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </div>

    <aside class="sidebar" id="sidebar">
        <nav>
            <a href="index.php">Beranda</a>
            <a href="profile.php">Profil</a>
            <a href="booking.php">Booking</a>
            <a href="keranjang.php">Keranjang</a>
            <a href="status-pembayaran.php">Status Pembayaran</a>
            <a href="paket-saya.php">Paket Saya</a>
            <a href="notifikasi.php" style="position: relative;">
                Notifikasi
                <?php if ($notif_unread > 0): ?>
                    <span class="badge-notif"><?php echo $notif_unread; ?></span>
                <?php endif; ?>
            </a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>

    <div class="overlay" id="overlay" onclick="closeSidebar()"></div>

    <main>
        <section class="hero-banner">
            <div class="hero-content">
                <h1>Selamat Datang, <?php echo htmlspecialchars($userName); ?>!</h1>
                <h3>Siap Mendaki?</h3>
            </div>
        </section>

        <section class="recommendation-section">
            <h2>Rekomendasi Pendakian</h2>
            <div class="gunung-grid">
                <?php
                $result = mysqli_query($conn, "SELECT * FROM gunung LIMIT 6");
                while ($row = mysqli_fetch_assoc($result)) {
                    echo '<div class="gunung-card">';
                    echo '<img src="../assets/images/' . htmlspecialchars($row['gambar']) . '" alt="' . htmlspecialchars($row['nama']) . '">';
                    echo '<div class="content">';
                    echo '<h4>' . htmlspecialchars($row['nama']) . '</h4>';
                    echo '<p>' . htmlspecialchars($row['tentang']) . '</p>';
                    echo '<p class="harga">Rp ' . number_format($row['harga'], 0, ',', '.') . '</p>';
                    echo '</div>';
                    echo '</div>';
                }
                ?>
            </div>
        </section>
    </main>

    <script>
        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('overlay');
            const topbar = document.getElementById('topbar');
            sidebar.classList.toggle('open');
            overlay.classList.toggle('active');
            topbar.classList.toggle('shifted');
        }

        function closeSidebar() {
            document.getElementById('sidebar').classList.remove('open');
            document.getElementById('overlay').classList.remove('active');
            document.getElementById('topbar').classList.remove('shifted');
        }
    </script>
</body>
</html>

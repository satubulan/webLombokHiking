<?php
session_start();
require_once 'config.php';

$trips = $conn->query("SELECT * FROM trips ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Trip - LombokHiking</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: #ffffff;
            color: #333;
            line-height: 1.6;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .trips {
            margin: 50px 0;
        }

        .trips h2 {
            text-align: center;
            font-size: 32px;
            color: #2e8b57;
            margin-bottom: 40px;
            position: relative;
        }

        .trips h2::after {
            content: '';
            width: 60px;
            height: 3px;
            background-color: #4CAF50;
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .trip-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            display: flex;
            flex-direction: column;
        }

        .trip-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .trip-content {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .trip-content h3 {
            font-size: 24px;
            color: #2e8b57;
            margin-top: 0;
            margin-bottom: 10px;
        }

        .trip-content p {
            font-size: 15px;
            color: #666;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

<header class="site-header">
    <div class="container header-wrapper">
        <a href="index.php" class="logo">
            <h1>LombokHiking</h1>
        </a>
        <nav class="main-nav">
            <a href="index.php">Beranda</a>
            <a href="views/mountains.php">Gunung</a>
            <a href="views/trips.php">Trip</a>
            <a href="views/guides.php">Guide</a>
            <a href="views/contact.php">Kontak</a>
        </nav>
        <div class="auth-buttons">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="views/login.php" class="btn btn-secondary">Login</a>
                <a href="views/register.php" class="btn btn-primary">Daftar</a>
            <?php else: ?>
                <a href="userbiasa/index.php" class="btn btn-secondary">Dashboard</a>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<main class="container">
    <section class="trips">
        <h2>Daftar Trip</h2>
        <div class="grid-3">
            <?php foreach ($trips as $trip): ?>
                <div class="trip-card">
                    <div class="trip-content">
                        <h3><?php echo htmlspecialchars($trip['title']); ?></h3>
                        <p><?php echo substr(strip_tags($trip['description']), 0, 100); ?>...</p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</main>

<footer class="site-footer">
    <div class="container footer-content">
        <div class="footer-section">
            <h3 class="footer-heading">Tentang Kami</h3>
            <p>LombokHiking adalah platform digital untuk booking pendakian gunung di Lombok secara online.</p>
        </div>
        <div class="footer-section">
            <h3 class="footer-heading">Kontak</h3>
            <p>Jl. Pariwisata, Lombok, NTB</p>
            <p>+62 812 3456 7890</p>
            <p>info@lombokhiking.com</p>
        </div>
    </div>
</footer>

<script src="assets/js/main.js"></script>
</body>
</html> 
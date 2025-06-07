<?php
session_start();
require_once 'config.php';

$mountains = $conn->query("SELECT * FROM mountains ORDER BY id DESC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beranda - LombokHiking</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        body {
            background: url('assets/images/background.jpg') no-repeat center center fixed;
            background-size: cover;
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
                <a href="views/dashboard-user.php" class="btn btn-secondary">Dashboard</a>
                <a href="logout.php" class="btn btn-danger">Logout</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<section class="hero">
    <div class="container">
        <h2>Selamat Datang di LombokHiking</h2>
        <p>Booking trip pendakian gunung mudah dan terpercaya di Pulau Lombok</p>
        <a href="views/trips.php" class="btn btn-primary">Lihat Trip</a>
    </div>
</section>

<main class="container">
    <section class="features">
        <div class="grid-3">
            <div class="feature-box">
                <h3>Gunung Populer</h3>
                <p>Rinjani, Tambora, dan destinasi lainnya.</p>
            </div>
            <div class="feature-box">
                <h3>Guide Profesional</h3>
                <p>Bersertifikat dan berpengalaman.</p>
            </div>
            <div class="feature-box">
                <h3>Booking Mudah</h3>
                <p>Reservasi cepat dan aman.</p>
            </div>
        </div>
    </section>

    <section class="mountain-cards">
        <h2>Destinasi Gunung</h2>
        <div class="grid-3">
            <?php foreach ($mountains as $mountain): ?>
                <div class="mountain-card">
                    <div class="mountain-image">
                        <img src="assets/images/<?php echo htmlspecialchars($mountain['image_url']); ?>" alt="<?php echo htmlspecialchars($mountain['name']); ?>">
                    </div>
                    <div class="mountain-content">
                        <h3><?php echo htmlspecialchars($mountain['name']); ?></h3>
                        <p><?php echo substr(strip_tags($mountain['description']), 0, 100); ?>...</p>
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

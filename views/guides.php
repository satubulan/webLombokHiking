<?php
session_start();
require_once '../config.php';

// Ambil data guide dari database
$result = $conn->query("SELECT * FROM guides ORDER BY id DESC");
$guides = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Guide - Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>

<header class="site-header">
    <div class="container header-wrapper">
        <a href="../index.php" class="logo">
            <h1>LombokHiking</h1>
        </a>
        <nav class="main-nav">
            <a href="../index.php">Beranda</a>
            <a href="mountains.php">Gunung</a>
            <a href="trips.php">Trip</a>
            <a href="guides.php" class="active">Guide</a>
            <a href="contact.php">Kontak</a>
        </nav>
    </div>
</header>

<main class="container">
    <h2>Daftar Guide</h2>
    <div class="guide-list">
        <?php foreach ($guides as $guide): ?>
            <div class="guide-item">
                <h3><?php echo htmlspecialchars($guide['name']); ?></h3>
                <p><strong>Rating:</strong> ⭐ <?php echo number_format($guide['rating'], 1); ?></p>
                <p><strong>Bahasa:</strong> <?php echo htmlspecialchars($guide['languages']); ?></p>
                <p><strong>Spesialisasi:</strong>
                    <?php foreach (explode(',', $guide['specialization']) as $spec): ?>
                        <span class="spec-tag"><?php echo htmlspecialchars(trim($spec)); ?></span>
                    <?php endforeach; ?>
                </p>
            </div>
        <?php endforeach; ?>
    </div>
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

<script src="../assets/js/main.js"></script>
</body>
</html>

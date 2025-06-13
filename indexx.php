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
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background: url('assets/images/background.jpg') no-repeat center center fixed;
            background-size: cover;
            color: #333;
            line-height: 1.6;
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .site-header {
            background: rgba(255, 255, 255, 0.95);
            padding: 15px 0;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo h1 {
            font-size: 28px;
            color: #2e8b57;
            margin: 0;
            font-weight: 700;
        }

        .main-nav {
            display: flex;
            gap: 25px;
        }

        .main-nav a {
            text-decoration: none;
            color: #4CAF50;
            font-weight: 600;
            transition: color 0.3s ease-in-out;
            padding: 5px 0;
            position: relative;
        }

        .main-nav a:hover {
            color: #2e8b57;
        }

        .main-nav a::after {
            content: '';
            position: absolute;
            width: 0;
            height: 2px;
            bottom: -5px;
            left: 0;
            background-color: #2e8b57;
            transition: width 0.3s ease-in-out;
        }

        .main-nav a:hover::after {
            width: 100%;
        }

        .auth-buttons a {
            margin-left: 15px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            color: white;
            transition: background-color 0.3s ease-in-out, transform 0.2s ease-in-out;
            font-weight: 600;
            border: none;
            cursor: pointer;
        }

        .btn-primary {
            background-color: #4CAF50;
            box-shadow: 0 5px 15px rgba(76, 175, 80, 0.3);
        }

        .btn-primary:hover {
            background-color: #388E3C;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: #607D8B;
            box-shadow: 0 5px 15px rgba(96, 125, 139, 0.3);
        }

        .btn-secondary:hover {
            background-color: #455A64;
            transform: translateY(-2px);
        }

        .btn-danger {
            background-color: #f44336;
            box-shadow: 0 5px 15px rgba(244, 67, 54, 0.3);
        }

        .btn-danger:hover {
            background-color: #d32f2f;
            transform: translateY(-2px);
        }

        .hero {
            text-align: center;
            padding: 80px 20px;
            background: linear-gradient(rgba(255, 255, 255, 0.9), rgba(255, 255, 255, 0.7));
            border-radius: 15px;
            margin: 30px 0;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }

        .hero h2 {
            font-size: 36px;
            margin-bottom: 15px;
            color: #2e8b57;
            font-weight: 700;
        }

        .hero p {
            font-size: 18px;
            margin-bottom: 30px;
            color: #555;
        }

        .features {
            margin: 50px 0;
        }

        .grid-3 {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
        }

        .feature-box {
            background: white;
            border-radius: 12px;
            padding: 25px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
        }

        .feature-box:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .mountain-cards {
            margin: 50px 0;
        }

        .mountain-cards h2 {
            text-align: center;
            font-size: 32px;
            color: #2e8b57;
            margin-bottom: 40px;
            position: relative;
        }

        .mountain-cards h2::after {
            content: '';
            width: 60px;
            height: 3px;
            background-color: #4CAF50;
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
        }

        .mountain-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease-in-out, box-shadow 0.3s ease-in-out;
            display: flex;
            flex-direction: column;
        }

        .mountain-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .mountain-image img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            border-top-left-radius: 12px;
            border-top-right-radius: 12px;
        }

        .mountain-content {
            padding: 20px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
        }

        .mountain-content h3 {
            font-size: 24px;
            color: #2e8b57;
            margin-top: 0;
            margin-bottom: 10px;
        }

        .mountain-content p {
            font-size: 15px;
            color: #666;
            margin-bottom: 15px;
        }

        .site-footer {
            background: #222;
            color: #eee;
            padding: 40px 0;
            margin-top: 50px;
            text-align: center;
            box-shadow: 0 -4px 15px rgba(0, 0, 0, 0.1);
        }

        .footer-content {
            display: flex;
            flex-wrap: wrap;
            justify-content: space-around;
            gap: 30px;
        }

        .footer-section {
            flex: 1 1 250px;
            margin-bottom: 20px;
        }

        .footer-heading {
            font-size: 20px;
            margin-bottom: 15px;
            color: #4CAF50;
            font-weight: 600;
        }

        .site-footer p {
            font-size: 15px;
            color: #bbb;
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .header-wrapper {
                flex-direction: column;
                gap: 15px;
            }

            .main-nav {
                flex-wrap: wrap;
                justify-content: center;
            }

            .hero h2 {
                font-size: 30px;
            }

            .hero p {
                font-size: 16px;
            }

            .grid-3 {
                grid-template-columns: 1fr;
            }

            .mountain-cards h2 {
                font-size: 28px;
            }
        }

        @media (max-width: 480px) {
            .auth-buttons {
                display: flex;
                flex-direction: column;
                gap: 10px;
            }

            .auth-buttons a {
                margin-left: 0;
            }
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
        <h2>Destinasi Gunung</h2>
        <div class="grid-3">
            <?php foreach ($mountains as $mountain): ?>
                <div class="mountain-card">
                    <div class="mountain-image">
                        <img src="assets/images/<?php echo htmlspecialchars($mountain['image_url']
); ?>" alt="<?php echo htmlspecialchars($mountain['name']); ?>">
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
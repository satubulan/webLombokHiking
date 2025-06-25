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
    <title>Lombok Hiking - Jelajahi Keindahan Alam Lombok</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            line-height: 1.6;
            overflow-x: hidden;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }

        /* Header */
        .site-header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            padding: 15px 0;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1000;
            box-shadow: 0 2px 20px rgba(0, 0, 0, 0.1);
        }

        .header-wrapper {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .logo h1 {
            font-size: 24px;
            color: #667eea;
            font-weight: 800;
            margin: 0;
        }

        .main-nav {
            display: flex;
            gap: 30px;
        }

        .main-nav a {
            text-decoration: none;
            color: #555;
            font-weight: 500;
            transition: all 0.3s ease;
            padding: 8px 16px;
            border-radius: 25px;
            position: relative;
        }

        .main-nav a:hover {
            color: #667eea;
            background: rgba(102, 126, 234, 0.1);
        }

        .auth-buttons {
            display: flex;
            gap: 15px;
        }

        .btn {
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.9);
            color: #667eea;
            border: 2px solid #667eea;
        }

        .btn-secondary:hover {
            background: #667eea;
            color: white;
            transform: translateY(-2px);
        }

        .btn-danger {
            background: linear-gradient(135deg, #ff6b6b, #ee5a52);
            color: white;
            box-shadow: 0 4px 15px rgba(255, 107, 107, 0.4);
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 107, 107, 0.6);
        }

        /* Hero Section */
        .hero-section {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.9), rgba(118, 75, 162, 0.9)), 
                        url('assets/images/background.jpg') no-repeat center center;
            background-size: cover;
            position: relative;
            overflow: hidden;
        }

        .hero-content {
            max-width: 800px;
            padding: 0 20px;
            z-index: 2;
        }

        .hero-title {
            font-size: clamp(3rem, 8vw, 6rem);
            font-weight: 900;
            color: white;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 1s ease-out;
        }

        .hero-subtitle {
            font-size: clamp(1.2rem, 3vw, 1.5rem);
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 40px;
            font-weight: 400;
            animation: fadeInUp 1s ease-out 0.2s both;
        }

        .hero-buttons {
            display: flex;
            gap: 20px;
            justify-content: center;
            flex-wrap: wrap;
            animation: fadeInUp 1s ease-out 0.4s both;
        }

        .hero-btn {
            padding: 15px 30px;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            font-size: 1.1rem;
        }

        .hero-btn-primary {
            background: white;
            color: #667eea;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.2);
        }

        .hero-btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.3);
        }

        .hero-btn-secondary {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 2px solid white;
        }

        .hero-btn-secondary:hover {
            background: white;
            color: #667eea;
            transform: translateY(-3px);
        }

        /* Mountains Section */
        .mountains-section {
            padding: 100px 0;
            background: white;
        }

        .section-title {
            text-align: center;
            font-size: clamp(2rem, 5vw, 3rem);
            color: #333;
            margin-bottom: 60px;
            font-weight: 800;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -15px;
            left: 50%;
            transform: translateX(-50%);
            width: 80px;
            height: 4px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 2px;
        }

        .mountains-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 60px;
        }

        .mountain-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.4s ease;
            cursor: pointer;
            position: relative;
        }

        .mountain-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .mountain-image {
            position: relative;
            height: 250px;
            overflow: hidden;
        }

        .mountain-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .mountain-card:hover .mountain-image img {
            transform: scale(1.1);
        }

        .mountain-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.8), rgba(118, 75, 162, 0.8));
            opacity: 0;
            transition: opacity 0.4s ease;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .mountain-card:hover .mountain-overlay {
            opacity: 1;
        }

        .mountain-overlay-text {
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .mountain-content {
            padding: 25px;
        }

        .mountain-content h3 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .mountain-content p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
        }

        .mountain-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #667eea;
            font-weight: 600;
        }

        /* Footer */
        .site-footer {
            background: #1a1a1a;
            color: white;
            padding: 60px 0 30px;
        }

        .footer-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 40px;
        }

        .footer-section h3 {
            color: #667eea;
            margin-bottom: 20px;
            font-weight: 600;
        }

        .footer-section p {
            color: #ccc;
            line-height: 1.6;
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .fade-in {
            opacity: 0;
            transform: translateY(30px);
            transition: all 0.6s ease;
        }

        .fade-in.visible {
            opacity: 1;
            transform: translateY(0);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .header-wrapper {
                flex-direction: column;
                gap: 15px;
            }

            .main-nav {
                gap: 15px;
            }

            .hero-buttons {
                flex-direction: column;
                align-items: center;
            }

            .mountains-grid {
                grid-template-columns: 1fr;
            }

            .site-header {
                position: relative;
            }
        }

        @media (max-width: 480px) {
            .auth-buttons {
                flex-direction: column;
                gap: 10px;
            }

            .hero-title {
                font-size: 2.5rem;
            }

            .hero-subtitle {
                font-size: 1rem;
            }
        }
    </style>
</head>
<body>

<header class="site-header">
    <div class="container header-wrapper">
        <a href="index.php" class="logo">
            <h1><i class="fas fa-mountain"></i> LombokHiking</h1>
        </a>
        <nav class="main-nav">
            <a href="index.php"><i class="fas fa-home"></i> Beranda</a>
            <a href="views/mountains.php"><i class="fas fa-mountain"></i> Gunung</a>
            <a href="views/trips.php"><i class="fas fa-route"></i> Trip</a>
            <a href="views/guides.php"><i class="fas fa-user-friends"></i> Guide</a>
            <a href="views/contact.php"><i class="fas fa-envelope"></i> Kontak</a>
        </nav>
        <div class="auth-buttons">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="views/login.php" class="btn btn-secondary"><i class="fas fa-sign-in-alt"></i> Login</a>
                <a href="views/register.php" class="btn btn-primary"><i class="fas fa-user-plus"></i> Daftar</a>
            <?php else: ?>
                <a href="userbiasa/dashboard.php" class="btn btn-secondary"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<section class="hero-section">
    <div class="hero-content">
        <h1 class="hero-title">Lombok Hiking</h1>
        <p class="hero-subtitle">Jelajahi Keindahan Alam Lombok Bersama Kami</p>
        <div class="hero-buttons">
            <a href="views/mountains.php" class="hero-btn hero-btn-primary">
                <i class="fas fa-mountain"></i> Jelajahi Gunung
            </a>
            <a href="views/trips.php" class="hero-btn hero-btn-secondary">
                <i class="fas fa-route"></i> Lihat Trip
            </a>
        </div>
    </div>
</section>

<section class="mountains-section">
    <div class="container">
        <h2 class="section-title">Destinasi Gunung Favorit</h2>
        <div class="mountains-grid">
            <?php foreach ($mountains as $mountain): ?>
                <div class="mountain-card fade-in" onclick="window.location.href='views/mountains.php?id=<?php echo $mountain['id']; ?>'">
                    <div class="mountain-image">
                        <img src="assets/images/mountains/<?php echo htmlspecialchars($mountain['image']); ?>" 
                             alt="<?php echo htmlspecialchars($mountain['name']); ?>">
                        <div class="mountain-overlay">
                            <div class="mountain-overlay-text">
                                <i class="fas fa-eye"></i> Lihat Detail
                            </div>
                        </div>
                    </div>
                    <div class="mountain-content">
                        <h3><?php echo htmlspecialchars($mountain['name']); ?></h3>
                        <p><?php echo substr(strip_tags($mountain['description']), 0, 120); ?>...</p>
                        <div class="mountain-meta">
                            <span><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($mountain['location'] ?? 'Lombok'); ?></span>
                            <span><i class="fas fa-arrow-up"></i> <?php echo number_format($mountain['height']); ?>m</span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
        </div>
    </section>

<footer class="site-footer">
    <div class="container footer-content">
        <div class="footer-section">
            <h3><i class="fas fa-mountain"></i> Tentang Kami</h3>
            <p>LombokHiking adalah platform digital terpercaya untuk booking pendakian gunung di Lombok secara online dengan pengalaman yang aman dan menyenangkan.</p>
        </div>
        <div class="footer-section">
            <h3><i class="fas fa-map-marker-alt"></i> Lokasi</h3>
            <p>Jl. Pariwisata, Lombok, Nusa Tenggara Barat</p>
            <p>Indonesia</p>
        </div>
        <div class="footer-section">
            <h3><i class="fas fa-phone"></i> Kontak</h3>
            <p><i class="fas fa-phone"></i> +62 812 3456 7890</p>
            <p><i class="fas fa-envelope"></i> info@lombokhiking.com</p>
            <p><i class="fas fa-clock"></i> Senin - Minggu: 08:00 - 20:00</p>
        </div>
    </div>
</footer>

<script>
// Smooth scroll for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            target.scrollIntoView({
                behavior: 'smooth',
                block: 'start'
            });
        }
    });
});

// Fade-in animation for mountain cards
const fadeElements = document.querySelectorAll('.fade-in');
const fadeObserver = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.classList.add('visible');
            fadeObserver.unobserve(entry.target);
        }
    });
}, { threshold: 0.1 });

fadeElements.forEach(element => fadeObserver.observe(element));

// Parallax effect for hero section
window.addEventListener('scroll', () => {
    const scrolled = window.pageYOffset;
    const hero = document.querySelector('.hero-section');
    if (hero) {
        hero.style.transform = `translateY(${scrolled * 0.5}px)`;
    }
});

// Add loading animation
window.addEventListener('load', () => {
    document.body.style.opacity = '1';
});
</script>

</body>
</html>
<?php
session_start();
require_once '../config.php';

// Ambil data guide dari database dengan join ke tabel users dan guide
$sql = "SELECT u.*, g.rating, g.specialization, g.experience, g.languages, g.bio 
        FROM users u 
        JOIN guide g ON u.id = g.user_id 
        WHERE u.role = 'guide' AND g.status = 'approved'
        ORDER BY g.rating DESC";
$result = $conn->query($sql);
$guides = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Guide - Lombok Hiking</title>
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

        .main-nav a:hover, .main-nav a.active {
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
            min-height: 60vh;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            background: linear-gradient(135deg, rgba(102, 126, 234, 0.9), rgba(118, 75, 162, 0.9)), 
                        url('../assets/images/background.jpg') no-repeat center center;
            background-size: cover;
            position: relative;
            overflow: hidden;
            margin-top: 80px;
        }

        .hero-content {
            max-width: 800px;
            padding: 0 20px;
            z-index: 2;
        }

        .hero-title {
            font-size: clamp(2.5rem, 6vw, 4rem);
            font-weight: 900;
            color: white;
            margin-bottom: 20px;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3);
            animation: fadeInUp 1s ease-out;
        }

        .hero-subtitle {
            font-size: clamp(1rem, 2.5vw, 1.3rem);
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 40px;
            font-weight: 400;
            animation: fadeInUp 1s ease-out 0.2s both;
        }

        /* Guides Section */
        .guides-section {
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

        .guides-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 30px;
            margin-top: 60px;
        }

        .guide-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.4s ease;
            cursor: pointer;
            position: relative;
        }

        .guide-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .guide-image {
            position: relative;
            height: 250px;
            overflow: hidden;
            background: linear-gradient(135deg, #667eea, #764ba2);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .guide-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .guide-image .guide-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 3rem;
            font-weight: 700;
            border: 4px solid rgba(255, 255, 255, 0.3);
        }

        .guide-card:hover .guide-image img {
            transform: scale(1.1);
        }

        .guide-overlay {
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

        .guide-card:hover .guide-overlay {
            opacity: 1;
        }

        .guide-overlay-text {
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .guide-content {
            padding: 25px;
        }

        .guide-content h3 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .guide-rating {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 15px;
            color: #fbbf24;
            font-size: 1.1rem;
        }

        .guide-rating .rating-text {
            color: #667eea;
            font-weight: 600;
        }

        .guide-specialization {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 15px;
        }

        .spec-tag {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .guide-experience, .guide-languages {
            color: #666;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }

        .guide-experience strong, .guide-languages strong {
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

            .guides-grid {
                grid-template-columns: 1fr;
            }

            .site-header {
                position: relative;
            }

            .hero-section {
                margin-top: 0;
            }
        }

        @media (max-width: 480px) {
            .auth-buttons {
                flex-direction: column;
                gap: 10px;
            }

            .hero-title {
                font-size: 2rem;
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
        <a href="../indexx.php" class="logo">
            <h1><i class="fas fa-mountain"></i> LombokHiking</h1>
        </a>
        <nav class="main-nav">
            <a href="../indexx.php"><i class="fas fa-home"></i> Beranda</a>
            <a href="mountains.php"><i class="fas fa-mountain"></i> Gunung</a>
            <a href="trips.php"><i class="fas fa-route"></i> Trip</a>
            <a href="guides.php" class="active"><i class="fas fa-user-friends"></i> Guide</a>
            <a href="contact.php"><i class="fas fa-envelope"></i> Kontak</a>
        </nav>
        <div class="auth-buttons">
            <?php if (!isset($_SESSION['user_id'])): ?>
                <a href="login.php" class="btn btn-secondary"><i class="fas fa-sign-in-alt"></i> Login</a>
                <a href="register.php" class="btn btn-primary"><i class="fas fa-user-plus"></i> Daftar</a>
            <?php else: ?>
                <a href="../userbiasa/dashboard.php" class="btn btn-secondary"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="../logout.php" class="btn btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php endif; ?>
        </div>
    </div>
</header>

<section class="hero-section">
    <div class="hero-content">
        <h1 class="hero-title">Guide Profesional</h1>
        <p class="hero-subtitle">Temukan guide berpengalaman untuk mendampingi petualangan Anda</p>
    </div>
</section>

<section class="guides-section">
    <div class="container">
        <h2 class="section-title">Guide Terbaik Kami</h2>
        <div class="guides-grid">
            <?php if (count($guides) > 0): ?>
        <?php foreach ($guides as $guide): ?>
                    <div class="guide-card fade-in">
                        <div class="guide-image">
                            <?php if (!empty($guide['profile_picture'])): ?>
                                <img src="../assets/images/profiles/<?php echo htmlspecialchars($guide['profile_picture']); ?>" 
                                     alt="<?php echo htmlspecialchars($guide['name']); ?>">
                            <?php else: ?>
                                <div class="guide-avatar">
                                    <?php echo strtoupper(substr($guide['name'], 0, 2)); ?>
                                </div>
                            <?php endif; ?>
                            <div class="guide-overlay">
                                <div class="guide-overlay-text">
                                    <i class="fas fa-user"></i> Lihat Profil
                                </div>
                            </div>
                        </div>
                        <div class="guide-content">
                <h3><?php echo htmlspecialchars($guide['name']); ?></h3>
                            <div class="guide-rating">
                                <?php 
                                $rating = $guide['rating'] ?? 0;
                                for ($i = 1; $i <= 5; $i++) {
                                    echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                }
                                ?>
                                <span class="rating-text"><?php echo number_format($rating, 1); ?>/5.0</span>
                            </div>
                            <?php if (!empty($guide['specialization'])): ?>
                                <div class="guide-specialization">
                                    <?php 
                                    $specs = explode(',', $guide['specialization']);
                                    foreach (array_slice($specs, 0, 3) as $spec): 
                                    ?>
                        <span class="spec-tag"><?php echo htmlspecialchars(trim($spec)); ?></span>
                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($guide['experience'])): ?>
                                <div class="guide-experience">
                                    <strong><i class="fas fa-clock"></i> Pengalaman:</strong> <?php echo htmlspecialchars($guide['experience']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($guide['languages'])): ?>
                                <div class="guide-languages">
                                    <strong><i class="fas fa-language"></i> Bahasa:</strong> <?php echo htmlspecialchars($guide['languages']); ?>
                                </div>
                            <?php endif; ?>
                        </div>
            </div>
        <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results" style="grid-column: 1 / -1; text-align: center; padding: 60px; background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);">
                    <i class="fas fa-user-friends" style="font-size: 4rem; color: #667eea; margin-bottom: 20px;"></i>
                    <h3 style="color: #333; margin-bottom: 10px;">Belum ada guide tersedia</h3>
                    <p style="color: #666;">Silakan cek kembali nanti atau hubungi admin.</p>
                </div>
            <?php endif; ?>
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
// Fade-in animation for guide cards
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
</script>

</body>
</html>
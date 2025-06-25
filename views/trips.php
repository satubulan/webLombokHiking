<?php
session_start();
require_once '../config.php';

// Ambil data trip dengan join ke tabel mountains dan users (guide)
$sql = "SELECT t.id, t.title, t.description, t.start_date, t.end_date, t.package_price, t.capacity, t.status, t.image as trip_image,
               m.name as mountain_name, m.image as mountain_image, 
               u.name as guide_name
        FROM trips t 
        LEFT JOIN mountains m ON t.mountain_id = m.id 
        LEFT JOIN users u ON t.guide_id = u.id 
        WHERE t.status = 'active' 
        ORDER BY t.id DESC";

// Debug: cek apakah query berhasil
$result = $conn->query($sql);
if (!$result) {
    die("Query Error: " . $conn->error);
}

$trips = $result->fetch_all(MYSQLI_ASSOC);

// Debug: tampilkan data trip
// echo "<pre>";
// foreach ($trips as $trip) {
//     echo "ID: " . $trip['id'] . ", Title: " . $trip['title'] . ", Image: " . $trip['trip_image'] . "\n";
// }
// echo "</pre>";

// Debug: cek jumlah trip yang ditemukan
// echo "Jumlah trip: " . count($trips);

// Tambahkan durasi dan max_participants ke setiap trip
foreach ($trips as &$trip) {
    if ($trip['start_date'] && $trip['end_date']) {
        $start = new DateTime($trip['start_date']);
        $end = new DateTime($trip['end_date']);
        $trip['duration'] = $end->diff($start)->days + 1;
    } else {
        $trip['duration'] = 1;
    }
    $trip['max_participants'] = $trip['capacity'] ?? 0;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Trip - Lombok Hiking</title>
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

        /* Trips Section */
        .trips-section {
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

        .trips-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(380px, 1fr));
            gap: 30px;
            margin-top: 60px;
        }

        .trip-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.4s ease;
            cursor: pointer;
            position: relative;
        }

        .trip-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
        }

        .trip-image {
            position: relative;
            height: 250px;
            overflow: hidden;
        }

        .trip-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.4s ease;
        }

        .trip-card:hover .trip-image img {
            transform: scale(1.1);
        }

        .trip-overlay {
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

        .trip-card:hover .trip-overlay {
            opacity: 1;
        }

        .trip-overlay-text {
            color: white;
            font-size: 1.2rem;
            font-weight: 600;
        }

        .trip-content {
            padding: 25px;
        }

        .trip-content h3 {
            font-size: 1.5rem;
            color: #333;
            margin-bottom: 15px;
            font-weight: 700;
        }

        .trip-description {
            color: #666;
            line-height: 1.6;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        .trip-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }

        .trip-meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #667eea;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .trip-price {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 12px 20px;
            border-radius: 25px;
            text-align: center;
            font-weight: 700;
            font-size: 1.2rem;
            margin-top: 15px;
        }

        .trip-guide {
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            padding: 8px 15px;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
            text-align: center;
            margin-top: 10px;
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

            .trips-grid {
                grid-template-columns: 1fr;
            }

            .site-header {
                position: relative;
            }

            .hero-section {
                margin-top: 0;
            }

            .trip-meta {
                grid-template-columns: 1fr;
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
            <a href="trips.php" class="active"><i class="fas fa-route"></i> Trip</a>
            <a href="guides.php"><i class="fas fa-user-friends"></i> Guide</a>
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

<<<<<<< HEAD
<section class="hero-section">
    <div class="hero-content">
        <h1 class="hero-title">Paket Trip Menarik</h1>
        <p class="hero-subtitle">Pilih paket pendakian yang sesuai dengan impian Anda</p>
    </div>
</section>

<section class="trips-section">
    <div class="container">
        <h2 class="section-title">Paket Trip Tersedia</h2>
        <div class="trips-grid">
            <?php if (count($trips) > 0): ?>
                <?php foreach ($trips as $trip): ?>
                    <div class="trip-card fade-in">
                        <div class="trip-image">
                            <?php 
                            $image_path = '';
                            if (!empty($trip['trip_image']) && $trip['trip_image'] != '0') {
                                // Jika sudah ada path lengkap
                                if (strpos($trip['trip_image'], 'uploads/') === 0) {
                                    $image_path = "../" . $trip['trip_image'];
                                } else {
                                    // Coba beberapa kemungkinan path
                                    $possible_paths = [
                                        "../uploads/trips/" . $trip['trip_image'],
                                        "../assets/images/trips/" . $trip['trip_image'],
                                        "../assets/images/" . $trip['trip_image']
                                    ];
                                    
                                    foreach ($possible_paths as $path) {
                                        if (file_exists(str_replace('../', '', $path))) {
                                            $image_path = $path;
                                            break;
                                        }
                                    }
                                }
                            }
                            ?>
                            
                            <?php if (!empty($image_path)): ?>
                                <img src="<?php echo $image_path; ?>" 
                                     alt="<?php echo htmlspecialchars($trip['title']); ?>">
                            <?php else: ?>
                                <img src="../assets/images/background.jpg" 
                                     alt="<?php echo htmlspecialchars($trip['title']); ?>">
                            <?php endif; ?>
                            <div class="trip-overlay">
                                <div class="trip-overlay-text">
                                    <i class="fas fa-eye"></i> Lihat Detail
                                </div>
                            </div>
                        </div>
                        <div class="trip-content">
                            <h3><?php echo htmlspecialchars($trip['title']); ?></h3>
                            <p class="trip-description">
                                <?php echo mb_substr(strip_tags($trip['description']), 0, 120) . '...'; ?>
                            </p>
                            <div class="trip-meta">
                                <div class="trip-meta-item">
                                    <i class="fas fa-mountain"></i>
                                    <span><?php echo htmlspecialchars($trip['mountain_name'] ?? 'Gunung'); ?></span>
                                </div>
                                <div class="trip-meta-item">
                                    <i class="fas fa-calendar"></i>
                                    <span><?php echo date('d M Y', strtotime($trip['start_date'])); ?></span>
                                </div>
                                <div class="trip-meta-item">
                                    <i class="fas fa-users"></i>
                                    <span><?php echo $trip['max_participants']; ?> Peserta</span>
                                </div>
                                <div class="trip-meta-item">
                                    <i class="fas fa-clock"></i>
                                    <span><?php echo $trip['duration']; ?> Hari</span>
                                </div>
                            </div>
                            <?php if (!empty($trip['guide_name'])): ?>
                                <div class="trip-guide">
                                    <i class="fas fa-user"></i> Guide: <?php echo htmlspecialchars($trip['guide_name']); ?>
                                </div>
                            <?php endif; ?>
                            <div class="trip-price">
                                Rp <?php echo number_format($trip['package_price'], 0, ',', '.'); ?>
                            </div>
                        </div>
=======
<main class="container">
    <section class="trips">
        <h2>Daftar Trip</h2>
        <div class="grid-3">
                <?php foreach ($trips as $trip): ?>
                <div class="trip-card">
                    <div class="trip-content">
                        <h3><?php echo htmlspecialchars($trip['title']); ?></h3>
                        <p><?php echo substr(strip_tags($trip['description']), 0, 100); ?>...</p>
>>>>>>> 9702fe102c9302797aa9977642210d1886f1fd00
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results" style="grid-column: 1 / -1; text-align: center; padding: 60px; background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);">
                    <i class="fas fa-route" style="font-size: 4rem; color: #667eea; margin-bottom: 20px;"></i>
                    <h3 style="color: #333; margin-bottom: 10px;">Belum ada trip tersedia</h3>
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
// Fade-in animation for trip cards
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
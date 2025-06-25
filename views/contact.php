<?php
session_start();
require_once '../config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $message = trim($_POST['message']);

    if (empty($message)) {
        $error = "Pesan tidak boleh kosong.";
    } elseif (!$user_id) {
        $error = "Anda harus login untuk mengirim pesan.";
    } else {
        $stmt = $conn->prepare("INSERT INTO feedback (user_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $message);

        if ($stmt->execute()) {
            $success = "Pesan Anda berhasil dikirim.";
        } else {
            $error = "Gagal mengirim pesan. Silakan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak - Lombok Hiking</title>
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

        /* Contact Section */
        .contact-section {
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

        .contact-container {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 60px;
            max-width: 1000px;
            margin: 0 auto;
        }

        .contact-info {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .contact-info h3 {
            font-size: 1.8rem;
            margin-bottom: 30px;
            font-weight: 700;
        }

        .contact-item {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            padding: 15px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 15px;
            transition: all 0.3s ease;
        }

        .contact-item:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateX(10px);
        }

        .contact-item i {
            font-size: 1.5rem;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
        }

        .contact-item-content h4 {
            font-size: 1.1rem;
            margin-bottom: 5px;
            font-weight: 600;
        }

        .contact-item-content p {
            font-size: 0.95rem;
            opacity: 0.9;
        }

        /* Contact Form */
        .contact-form {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }

        .form-title {
            font-size: 1.8rem;
            color: #333;
            margin-bottom: 30px;
            font-weight: 700;
            text-align: center;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #333;
            font-weight: 600;
            font-size: 0.95rem;
        }

        .form-group textarea {
            width: 100%;
            padding: 15px;
            border: 2px solid #e1e5e9;
            border-radius: 15px;
            font-family: 'Inter', sans-serif;
            font-size: 1rem;
            transition: all 0.3s ease;
            resize: vertical;
            min-height: 120px;
        }

        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group textarea::placeholder {
            color: #999;
        }

        .submit-btn {
            width: 100%;
            padding: 15px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 15px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 10px;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        /* Messages */
        .message {
            padding: 15px 20px;
            border-radius: 15px;
            margin-bottom: 20px;
            font-weight: 500;
        }

        .success-message {
            background: linear-gradient(135deg, #10b981, #059669);
            color: white;
        }

        .error-message {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
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

            .contact-container {
                grid-template-columns: 1fr;
                gap: 40px;
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

            .contact-info, .contact-form {
                padding: 25px;
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
            <a href="guides.php"><i class="fas fa-user-friends"></i> Guide</a>
            <a href="contact.php" class="active"><i class="fas fa-envelope"></i> Kontak</a>
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
        <h1 class="hero-title">Hubungi Kami</h1>
        <p class="hero-subtitle">Kami siap membantu Anda dengan pertanyaan dan saran</p>
    </div>
</section>

<section class="contact-section">
    <div class="container">
        <h2 class="section-title">Mari Berbincang</h2>
        <div class="contact-container">
            <div class="contact-info fade-in">
                <h3><i class="fas fa-comments"></i> Informasi Kontak</h3>
                <div class="contact-item">
                    <i class="fas fa-map-marker-alt"></i>
                    <div class="contact-item-content">
                        <h4>Alamat</h4>
                        <p>Jl. Pariwisata, Lombok, Nusa Tenggara Barat, Indonesia</p>
                    </div>
                </div>
                <div class="contact-item">
                    <i class="fas fa-phone"></i>
                    <div class="contact-item-content">
                        <h4>Telepon</h4>
                        <p>+62 812 3456 7890</p>
                    </div>
                </div>
                <div class="contact-item">
                    <i class="fas fa-envelope"></i>
                    <div class="contact-item-content">
                        <h4>Email</h4>
                        <p>info@lombokhiking.com</p>
                    </div>
                </div>
                <div class="contact-item">
                    <i class="fas fa-clock"></i>
                    <div class="contact-item-content">
                        <h4>Jam Operasional</h4>
                        <p>Senin - Minggu: 08:00 - 20:00 WITA</p>
                    </div>
                </div>
                <div class="contact-item">
                    <i class="fas fa-globe"></i>
                    <div class="contact-item-content">
                        <h4>Website</h4>
                        <p>www.lombokhiking.com</p>
                    </div>
                </div>
            </div>

            <div class="contact-form fade-in">
                <h3 class="form-title"><i class="fas fa-paper-plane"></i> Kirim Pesan</h3>

        <?php if (!empty($error)) : ?>
                    <div class="error-message">
                        <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
                    </div>
        <?php endif; ?>

        <?php if (!empty($success)) : ?>
                    <div class="success-message">
                        <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($success); ?>
                    </div>
        <?php endif; ?>

                <?php if (!isset($_SESSION['user_id'])) : ?>
                    <div class="error-message">
                        <i class="fas fa-info-circle"></i> Silakan <a href="login.php" style="color: white; text-decoration: underline;">login</a> terlebih dahulu untuk mengirim pesan.
                    </div>
                <?php else : ?>
                    <form method="POST" action="">
            <div class="form-group">
                            <label for="message">
                                <i class="fas fa-comment"></i> Pesan Anda
                            </label>
                            <textarea 
                                name="message" 
                                id="message" 
                                required 
                                rows="6" 
                                placeholder="Tulis pesan, pertanyaan, atau saran Anda di sini..."
                            ><?php echo isset($_POST['message']) ? htmlspecialchars($_POST['message']) : ''; ?></textarea>
                        </div>

                        <button type="submit" class="submit-btn">
                            <i class="fas fa-paper-plane"></i> Kirim Pesan
                        </button>
                    </form>
                <?php endif; ?>
            </div>
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
// Fade-in animation for contact elements
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

// Auto-resize textarea
const textarea = document.querySelector('textarea');
if (textarea) {
    textarea.addEventListener('input', function() {
        this.style.height = 'auto';
        this.style.height = this.scrollHeight + 'px';
    });
}
</script>

</body>
</html>

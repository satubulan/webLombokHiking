
<?php
// Start the session
session_start();

// Include database connection
require_once 'config/database.php';

$success = '';
$error = '';

// Process form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    
    // Basic validation
    if (empty($name) || empty($email) || empty($message)) {
        $error = "Semua field harus diisi.";
    } else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email tidak valid.";
    } else {
        // Generate UUID
        $id = uniqid('f');
        
        // Insert into database
        $query = "INSERT INTO feedbacks (id, name, email, message) VALUES ('$id', '$name', '$email', '$message')";
        
        if ($conn->query($query) === TRUE) {
            $success = "Pesan Anda telah berhasil dikirim. Kami akan segera menghubungi Anda.";
        } else {
            $error = "Error: " . $conn->error;
        }
    }
}

// Get subject from URL if provided
$subject = '';
if (isset($_GET['subject'])) {
    $subject = htmlspecialchars($_GET['subject']);
}

// Include header
require_once 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1 class="page-title">Hubungi Kami</h1>
        <nav class="breadcrumb">
            <a href="index.php">Beranda</a> / <span>Kontak</span>
        </nav>
    </div>
</section>

<section class="contact-section">
    <div class="container">
        <div class="contact-container">
            <div class="contact-info">
                <h2>Informasi Kontak</h2>
                <p class="contact-intro">Kami siap membantu Anda dengan informasi pendakian, pemesanan trip, atau pertanyaan lainnya.</p>
                
                <div class="info-item">
                    <div class="icon"><i class="fas fa-map-marker-alt"></i></div>
                    <div class="content">
                        <h3>Alamat</h3>
                        <p>Jl. Pariwisata No.123<br>Mataram, Lombok<br>Nusa Tenggara Barat 83126</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="icon"><i class="fas fa-phone"></i></div>
                    <div class="content">
                        <h3>Telepon</h3>
                        <p>+62 812 3456 7890</p>
                        <p>+62 878 6543 2109</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="icon"><i class="fas fa-envelope"></i></div>
                    <div class="content">
                        <h3>Email</h3>
                        <p>info@lombokhiking.com</p>
                        <p>booking@lombokhiking.com</p>
                    </div>
                </div>
                
                <div class="info-item">
                    <div class="icon"><i class="fas fa-clock"></i></div>
                    <div class="content">
                        <h3>Jam Operasional</h3>
                        <p>Senin - Jumat: 08.00 - 17.00 WITA</p>
                        <p>Sabtu: 09.00 - 15.00 WITA</p>
                        <p>Minggu: Tutup</p>
                    </div>
                </div>
                
                <div class="social-links">
                    <a href="#" class="social-link"><i class="fab fa-facebook-f"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-instagram"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-twitter"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-youtube"></i></a>
                    <a href="#" class="social-link"><i class="fab fa-whatsapp"></i></a>
                </div>
            </div>
            
            <div class="contact-form-container">
                <h2>Kirim Pesan</h2>
                
                <?php if ($success): ?>
                    <div class="success-message">
                        <?php echo $success; ?>
                    </div>
                <?php endif; ?>
                
                <?php if ($error): ?>
                    <div class="error-message">
                        <?php echo $error; ?>
                    </div>
                <?php endif; ?>
                
                <form class="contact-form" method="POST" action="contact.php" id="contactForm" onsubmit="return validateForm('contactForm')">
                    <div class="form-group">
                        <label for="name">Nama Lengkap</label>
                        <input type="text" id="name" name="name" required>
                        <div id="name-error" class="error-message" style="display: none;">Nama harus diisi.</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email</label>
                        <input type="email" id="email" name="email" required>
                        <div id="email-error" class="error-message" style="display: none;">Email tidak valid.</div>
                    </div>
                    
                    <div class="form-group">
                        <label for="subject">Subjek</label>
                        <input type="text" id="subject" name="subject" value="<?php echo $subject; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="message">Pesan</label>
                        <textarea id="message" name="message" rows="6" required></textarea>
                        <div id="message-error" class="error-message" style="display: none;">Pesan harus diisi.</div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Kirim Pesan</button>
                </form>
            </div>
        </div>
    </div>
</section>

<section class="map-section">
    <div class="container">
        <h2 class="section-title">Lokasi Kami</h2>
        <div class="map-container">
            <iframe 
                src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d252994.96892169066!2d116.07397534099116!3d-8.58311963218931!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2dcdb7d23e8cc745%3A0x170592ff996ecdad!2sMataram%2C%20Kota%20Mataram%2C%20Nusa%20Tenggara%20Bar.!5e0!3m2!1sid!2sid!4v1623123456789!5m2!1sid!2sid" 
                width="100%" 
                height="450" 
                style="border:0;" 
                allowfullscreen="" 
                loading="lazy">
            </iframe>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<style>
    .contact-section {
        padding: 60px 0;
    }
    
    .contact-container {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 40px;
    }
    
    .contact-info, .contact-form-container {
        background-color: white;
        border-radius: 8px;
        padding: 30px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }
    
    .contact-info h2, .contact-form-container h2 {
        margin-bottom: 20px;
        position: relative;
        padding-bottom: 10px;
    }
    
    .contact-info h2:after, .contact-form-container h2:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 3px;
        background-color: var(--primary-color);
    }
    
    .contact-intro {
        margin-bottom: 25px;
        color: var(--light-text);
    }
    
    .info-item {
        display: flex;
        margin-bottom: 25px;
    }
    
    .info-item .icon {
        width: 50px;
        height: 50px;
        background-color: var(--primary-color);
        color: white;
        border-radius: 50%;
        display: flex;
        justify-content: center;
        align-items: center;
        font-size: 1.2rem;
        margin-right: 15px;
    }
    
    .info-item .content {
        flex: 1;
    }
    
    .info-item h3 {
        font-size: 1.1rem;
        margin-bottom: 5px;
    }
    
    .info-item p {
        margin: 0;
        color: var(--light-text);
        line-height: 1.5;
    }
    
    .social-links {
        display: flex;
        gap: 10px;
        margin-top: 30px;
    }
    
    .social-link {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background-color: var(--light-bg);
        color: var(--text-color);
        display: flex;
        justify-content: center;
        align-items: center;
        transition: all 0.3s ease;
    }
    
    .social-link:hover {
        background-color: var(--primary-color);
        color: white;
        transform: translateY(-3px);
    }
    
    .form-group {
        margin-bottom: 20px;
    }
    
    .form-group label {
        display: block;
        margin-bottom: 8px;
        font-weight: 500;
    }
    
    .form-group input, .form-group textarea {
        width: 100%;
        padding: 12px 15px;
        border: 1px solid var(--border-color);
        border-radius: 4px;
        font-size: 16px;
    }
    
    .form-group textarea {
        resize: vertical;
    }
    
    .form-group input:focus, .form-group textarea:focus {
        border-color: var(--primary-color);
        outline: none;
    }
    
    .form-group input.error, .form-group textarea.error {
        border-color: #f44336;
    }
    
    .error-message {
        color: #f44336;
        font-size: 14px;
        margin-top: 5px;
    }
    
    .success-message {
        background-color: #e8f5e9;
        color: #4caf50;
        padding: 15px;
        border-radius: 4px;
        margin-bottom: 20px;
    }
    
    .map-section {
        padding: 40px 0 80px;
    }
    
    .map-container {
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 4px 15px rgba(0,0,0,0.08);
    }
    
    @media screen and (max-width: 991px) {
        .contact-container {
            grid-template-columns: 1fr;
        }
    }
</style>

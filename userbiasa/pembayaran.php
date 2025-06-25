<?php
session_start();
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: ../views/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// Get parameters
$type = $_GET['type'] ?? '';
$trip_id = $_GET['trip_id'] ?? '';

if ($type !== 'package' || empty($trip_id)) {
    header('Location: dashboard.php');
    exit();
}

// Get trip details
$trip_query = $conn->prepare("
    SELECT t.*, m.name as mountain_name, m.height, m.location, 
           u.name as guide_name, COALESCE(g.rating, 0) as guide_rating, g.experience,
           (t.capacity - COALESCE((SELECT COUNT(*) FROM bookings b WHERE b.trip_id = t.id AND b.status IN ('confirmed', 'pending')), 0)) as available_spots
    FROM trips t 
    LEFT JOIN mountains m ON t.mountain_id = m.id 
    LEFT JOIN users u ON t.guide_id = u.id 
    LEFT JOIN guide g ON u.id = g.user_id
    WHERE t.id = ? AND t.status = 'active'
");

$trip_query->bind_param("i", $trip_id);
$trip_query->execute();
$trip_result = $trip_query->get_result();

if ($trip_result->num_rows === 0) {
    $_SESSION['error'] = "Trip tidak ditemukan atau tidak tersedia.";
    header('Location: dashboard.php');
    exit();
}

$trip = $trip_result->fetch_assoc();

// Check if trip is still available
if ($trip['available_spots'] <= 0) {
    $_SESSION['error'] = "Maaf, trip ini sudah penuh.";
    header('Location: dashboard.php');
    exit();
}

// Process booking if form submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $participants = $_POST['participants'] ?? 1;
    $special_requests = $_POST['special_requests'] ?? '';
    $emergency_contact = $_POST['emergency_contact'] ?? '';
    
    // Validate participants
    if ($participants > $trip['available_spots']) {
        $error = "Jumlah peserta melebihi slot yang tersedia.";
    } else {
        // Calculate total price
        $total_price = $trip['package_price'] * $participants;
        
        // Generate payment code
        $payment_code = 'PAY-' . date('Ymd') . '-' . strtoupper(substr(md5(uniqid()), 0, 6));
        
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert booking
            $booking_query = $conn->prepare("
                INSERT INTO bookings (user_id, trip_id, booking_date, status, total_price, mountain_ticket_id, selected_guide_id, booking_type) 
                VALUES (?, ?, NOW(), 'pending', ?, ?, ?, 'package')
            ");
            
            $booking_query->bind_param("iidii", $user_id, $trip_id, $total_price, $trip['mountain_ticket_id'], $trip['guide_id']);
            $booking_query->execute();
            
            $booking_id = $conn->insert_id;
            
            // Ambil harga tiket gunung
            $ticket_query = $conn->prepare("SELECT price FROM mountain_tickets WHERE id = ?");
            $ticket_query->bind_param("i", $trip['mountain_ticket_id']);
            $ticket_query->execute();
            $ticket_result = $ticket_query->get_result();
            $ticket = $ticket_result->fetch_assoc();
            $mountain_ticket_price = $ticket ? (float)$ticket['price'] : 0;
            
            // Hitung pendapatan admin & guide
            $admin_income = $mountain_ticket_price * $participants;
            $guide_income = ($trip['package_price'] - $mountain_ticket_price) * $participants;
            
            // Insert pendapatan guide
            $pendapatan_guide_query = $conn->prepare("
                INSERT INTO pendapatan_guide (guide_id, booking_id, amount, system_fee, source)
                VALUES (?, ?, ?, ?, 'package')
            ");
            $pendapatan_guide_query->bind_param("iidd", $trip['guide_id'], $booking_id, $guide_income, $admin_income);
            $pendapatan_guide_query->execute();
            
            // Insert payment record
            $payment_query = $conn->prepare("
                INSERT INTO pembayaran (booking_id, payment_code, amount, status) 
                VALUES (?, ?, ?, 'unpaid')
            ");
            
            $payment_query->bind_param("isd", $booking_id, $payment_code, $total_price);
            $payment_query->execute();
            
            // Insert notification
            $notif_query = $conn->prepare("
                INSERT INTO notifikasi (user_id, title, message) 
                VALUES (?, 'Booking Berhasil', ?)
            ");
            
            $notif_message = "Booking trip '{$trip['title']}' berhasil dibuat. Kode pembayaran: {$payment_code}";
            $notif_query->bind_param("is", $user_id, $notif_message);
            $notif_query->execute();
            
            $conn->commit();
            
            // Redirect to payment status
            $_SESSION['success'] = "Booking berhasil! Silakan lakukan pembayaran.";
            header("Location: status_pembayaran.php?payment_code=" . $payment_code);
            exit();
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Terjadi kesalahan saat memproses booking: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran Paket Trip - Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary-green: #2e8b57;
            --secondary-green: #3cb371;
            --light-green: #f0f9f4;
            --accent-green: #10b981;
            --dark-text: #1f2937;
            --light-text: #6b7280;
            --danger-red: #ef4444;
            --warning-yellow: #f59e0b;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header h1 {
            margin: 0;
            font-size: 1.8rem;
        }

        .back-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .payment-container {
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }

        .trip-details {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .trip-image {
            width: 100%;
            height: 250px;
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
            font-weight: bold;
            margin-bottom: 20px;
            position: relative;
            overflow: hidden;
        }

        .trip-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .price-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--accent-green);
            color: white;
            padding: 10px 15px;
            border-radius: 20px;
            font-weight: 700;
            font-size: 1.1rem;
        }

        .trip-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 15px;
        }

        .trip-info {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .info-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px;
            background: var(--light-green);
            border-radius: 8px;
        }

        .info-item i {
            color: var(--accent-green);
            width: 20px;
        }

        .guide-info {
            background: var(--light-green);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .guide-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 10px;
        }

        .guide-avatar {
            width: 50px;
            height: 50px;
            background: var(--accent-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 1.2rem;
        }

        .guide-rating {
            display: flex;
            align-items: center;
            gap: 8px;
            color: var(--light-text);
        }

        .stars {
            color: #fbbf24;
        }

        .facilities {
            margin-top: 20px;
        }

        .facilities h4 {
            color: var(--dark-text);
            margin-bottom: 10px;
        }

        .facilities-list {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .facility-tag {
            background: var(--accent-green);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.85rem;
        }

        .booking-form {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .form-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 8px;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--accent-green);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .price-summary {
            background: var(--light-green);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .price-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }

        .price-row.total {
            font-weight: 700;
            font-size: 1.2rem;
            color: var(--dark-text);
            border-top: 2px solid var(--accent-green);
            padding-top: 10px;
            margin-top: 10px;
        }

        .submit-btn {
            width: 100%;
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            color: white;
            border: none;
            padding: 15px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .submit-btn:hover {
            background: linear-gradient(135deg, var(--secondary-green), var(--primary-green));
            transform: translateY(-2px);
        }

        .submit-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert.error {
            background: #fef2f2;
            color: var(--danger-red);
            border: 1px solid #fecaca;
        }

        .alert.success {
            background: #f0fdf4;
            color: var(--accent-green);
            border: 1px solid #bbf7d0;
        }

        .availability-warning {
            background: #fffbeb;
            color: var(--warning-yellow);
            border: 1px solid #fed7aa;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        @media (max-width: 768px) {
            .payment-container {
                grid-template-columns: 1fr;
            }
            
            .booking-form {
                position: static;
            }
            
            .trip-info {
                grid-template-columns: 1fr;
            }
            
            .container {
                padding: 15px;
            }
        }
    </style>
</head>
<body>
<div class="admin-container">
    <aside class="sidebar">
        <div class="logo">
            <i class="fas fa-mountain"></i>
            Lombok Hiking
        </div>
        <nav>
            <a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="profile.php" class="nav-link"><i class="fas fa-user"></i> Profil Saya</a>
            <a href="booking.php" class="nav-link"><i class="fas fa-calendar-plus"></i> Booking Trip</a>
            <a href="status_pembayaran.php" class="nav-link"><i class="fas fa-credit-card"></i> Status Pembayaran</a>
            <a href="paket_saya.php" class="nav-link"><i class="fas fa-hiking"></i> Paket Saya</a>
            <a href="ajukan_guide.php" class="nav-link"><i class="fas fa-user-plus"></i> Ajukan Diri Jadi Guide</a>
            <a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>
    <main class="main">
    <div class="container">
        <!-- Header -->
        <div class="header">
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Kembali
            </a>
            <div>
                <h1>Pembayaran Paket Trip</h1>
                <p style="margin: 5px 0 0 0; opacity: 0.9;">Selesaikan pembayaran untuk mengkonfirmasi booking Anda</p>
            </div>
        </div>

        <!-- Error/Success Messages -->
                <!-- Error/Success Messages -->
        <?php if (isset($error)): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

        <!-- Availability Warning -->
        <?php if ($trip['available_spots'] <= 3): ?>
            <div class="availability-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Perhatian!</strong> Hanya tersisa <?= $trip['available_spots'] ?> slot untuk trip ini.
            </div>
        <?php endif; ?>

        <div class="payment-container">
            <!-- Trip Details -->
            <div class="trip-details">
                <div class="trip-image">
                    <?php if ($trip['image']): ?>
                        <img src="../uploads/trips/<?= htmlspecialchars($trip['image']) ?>" 
                             alt="<?= htmlspecialchars($trip['title']) ?>"
                             onerror="this.style.display='none'; this.parentElement.innerHTML='<?= htmlspecialchars($trip['mountain_name']) ?>';">
                    <?php else: ?>
                        <?= htmlspecialchars($trip['mountain_name']) ?>
                    <?php endif; ?>
                    <div class="price-badge">Rp <?= number_format($trip['package_price'], 0, ',', '.') ?>/orang</div>
                </div>

                <h2 class="trip-title"><?= htmlspecialchars($trip['title']) ?></h2>

                <div class="trip-info">
                    <div class="info-item">
                        <i class="fas fa-mountain"></i>
                        <div>
                            <strong><?= htmlspecialchars($trip['mountain_name']) ?></strong><br>
                            <small><?= htmlspecialchars($trip['location']) ?></small>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-calendar-alt"></i>
                        <div>
                            <strong><?= date_diff(date_create($trip['start_date']), date_create($trip['end_date']))->days + 1 ?> Hari</strong><br>
                            <small><?= date('d M', strtotime($trip['start_date'])) ?> - <?= date('d M Y', strtotime($trip['end_date'])) ?></small>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-users"></i>
                        <div>
                            <strong>Kapasitas</strong><br>
                            <small><?= $trip['capacity'] ?> orang (<?= $trip['available_spots'] ?> tersisa)</small>
                        </div>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-ruler-vertical"></i>
                        <div>
                            <strong><?= number_format($trip['height']) ?> MDPL</strong><br>
                            <small>Ketinggian</small>
                        </div>
                    </div>
                </div>

                <!-- Guide Information -->
                <div class="guide-info">
                    <div class="guide-header">
                        <div class="guide-avatar">
                            <?= strtoupper(substr($trip['guide_name'], 0, 2)) ?>
                        </div>
                        <div>
                            <h4 style="margin: 0; color: var(--dark-text);">Guide: <?= htmlspecialchars($trip['guide_name']) ?></h4>
                            <div class="guide-rating">
                                <span class="stars">
                                    <?php 
                                    $rating = $trip['guide_rating'];
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= $rating ? '★' : '☆';
                                    }
                                    ?>
                                </span>
                                <span><?= number_format($trip['guide_rating'], 1) ?>/5.0</span>
                                <span>• <?= $trip['experience'] ?? '0' ?> tahun pengalaman</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Facilities -->
                <?php if ($trip['facilities']): ?>
                <div class="facilities">
                    <h4><i class="fas fa-list-check"></i> Fasilitas Termasuk:</h4>
                    <div class="facilities-list">
                        <?php 
                        $facilities = explode(',', $trip['facilities']);
                        foreach ($facilities as $facility): 
                        ?>
                            <span class="facility-tag"><?= htmlspecialchars(trim($facility)) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Description -->
                <?php if ($trip['description']): ?>
                <div style="margin-top: 20px;">
                    <h4><i class="fas fa-info-circle"></i> Deskripsi Trip:</h4>
                    <p style="color: var(--light-text); line-height: 1.6;">
                        <?= nl2br(htmlspecialchars($trip['description'])) ?>
                    </p>
                </div>
                <?php endif; ?>
            </div>

            <!-- Booking Form -->
            <div class="booking-form">
                <h3 class="form-title">
                    <i class="fas fa-credit-card"></i>
                    Detail Booking
                </h3>

                <form method="POST" id="bookingForm">
                    <div class="form-group">
                        <label for="participants">
                            <i class="fas fa-users"></i> Jumlah Peserta
                        </label>
                        <select name="participants" id="participants" required onchange="updatePrice()">
                            <?php for ($i = 1; $i <= min(10, $trip['available_spots']); $i++): ?>
                                <option value="<?= $i ?>"><?= $i ?> orang</option>
                            <?php endfor; ?>
                        </select>
                        <small style="color: var(--light-text);">Maksimal <?= $trip['available_spots'] ?> orang</small>
                    </div>

                    <div class="form-group">
                        <label for="emergency_contact">
                            <i class="fas fa-phone"></i> Kontak Darurat
                        </label>
                        <input type="tel" name="emergency_contact" id="emergency_contact" 
                               placeholder="Nomor telepon kontak darurat" required>
                        <small style="color: var(--light-text);">Untuk keperluan darurat selama trip</small>
                    </div>

                    <div class="form-group">
                        <label for="special_requests">
                            <i class="fas fa-comment"></i> Permintaan Khusus (Opsional)
                        </label>
                        <textarea name="special_requests" id="special_requests" rows="3" 
                                  placeholder="Dietary restrictions, kondisi medis, atau permintaan khusus lainnya..."></textarea>
                    </div>

                    <!-- Price Summary -->
                    <div class="price-summary">
                        <h4 style="margin: 0 0 15px 0; color: var(--dark-text);">
                            <i class="fas fa-calculator"></i> Ringkasan Harga
                        </h4>
                        <div class="price-row">
                            <span>Harga per orang:</span>
                            <span>Rp <?= number_format($trip['package_price'], 0, ',', '.') ?></span>
                        </div>
                        <div class="price-row">
                            <span>Jumlah peserta:</span>
                            <span id="participantCount">1 orang</span>
                        </div>
                        <div class="price-row">
                            <span>Subtotal:</span>
                            <span id="subtotal">Rp <?= number_format($trip['package_price'], 0, ',', '.') ?></span>
                        </div>
                        <div class="price-row total">
                            <span>Total Pembayaran:</span>
                            <span id="totalPrice">Rp <?= number_format($trip['package_price'], 0, ',', '.') ?></span>
                        </div>
                    </div>

                    <!-- Terms and Conditions -->
                    <div class="form-group">
                        <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer;">
                            <input type="checkbox" required style="margin-top: 4px;">
                            <span style="font-size: 0.9rem; line-height: 1.5;">
                                Saya menyetujui <a href="#" style="color: var(--accent-green);">syarat dan ketentuan</a> 
                                serta memahami bahwa pembayaran yang sudah dilakukan tidak dapat dikembalikan 
                                kecuali dalam kondisi tertentu sesuai kebijakan perusahaan.
                            </span>
                        </label>
                    </div>

                    <button type="submit" class="submit-btn" id="submitBtn">
                        <i class="fas fa-credit-card"></i>
                        Lanjutkan ke Pembayaran
                    </button>
                </form>

                <!-- Payment Info -->
                <div style="margin-top: 20px; padding: 15px; background: #f8f9fa; border-radius: 10px; font-size: 0.9rem; color: var(--light-text);">
                    <h5 style="margin: 0 0 10px 0; color: var(--dark-text);">
                        <i class="fas fa-info-circle"></i> Informasi Pembayaran
                    </h5>
                    <ul style="margin: 0; padding-left: 20px;">
                        <li>Setelah klik "Lanjutkan ke Pembayaran", Anda akan mendapat kode pembayaran</li>
                        <li>Lakukan transfer sesuai nominal yang tertera</li>
                        <li>Upload bukti pembayaran untuk konfirmasi</li>
                        <li>Booking akan dikonfirmasi setelah pembayaran terverifikasi</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
    </main>
</div>

    <script>
        const basePrice = <?= $trip['package_price'] ?>;
        
        function updatePrice() {
            const participants = document.getElementById('participants').value;
            const subtotal = basePrice * participants;
            const total = subtotal; // Bisa ditambah biaya admin dll
            
            document.getElementById('participantCount').textContent = participants + ' orang';
            document.getElementById('subtotal').textContent = 'Rp ' + subtotal.toLocaleString('id-ID');
            document.getElementById('totalPrice').textContent = 'Rp ' + total.toLocaleString('id-ID');
        }
        
        // Form validation
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            const submitBtn = document.getElementById('submitBtn');
            const participants = document.getElementById('participants').value;
            const emergencyContact = document.getElementById('emergency_contact').value;
            
            // Validate emergency contact
            if (emergencyContact.length < 10) {
                e.preventDefault();
                alert('Nomor kontak darurat harus minimal 10 digit');
                return;
            }
            
            // Validate participants
            if (participants > <?= $trip['available_spots'] ?>) {
                e.preventDefault();
                alert('Jumlah peserta melebihi slot yang tersedia');
                return;
            }
            
            // Add loading state
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
            submitBtn.disabled = true;
        });
        
        // Auto-format phone number
        document.getElementById('emergency_contact').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.startsWith('0')) {
                value = '62' + value.substring(1);
            }
            e.target.value = value;
        });
        
        // Countdown timer for limited slots
        <?php if ($trip['available_spots'] <= 3): ?>
        let timeLeft = 900; // 15 minutes
        const timer = setInterval(function() {
            const minutes = Math.floor(timeLeft / 60);
            const seconds = timeLeft % 60;
            
            if (timeLeft <= 0) {
                clearInterval(timer);
                alert('Waktu booking telah habis. Halaman akan di-refresh.');
                location.reload();
            }
            
            timeLeft--;
        }, 1000);
        <?php endif; ?>
        
        // Prevent back button after form submission
        if (window.history.replaceState) {
            window.history.replaceState(null, null, window.location.href);
        }
        
        window.onload = function() {
            if (typeof history.pushState === "function") {
                history.pushState("jibberish", null, null);
                window.onpopstate = function() {
                    history.pushState('newjibberish', null, null);
                };
            }
        }
        
        console.log('Pembayaran page loaded successfully');
    </script>
</body>
</html>

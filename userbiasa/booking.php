
<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is a regular user
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: ../views/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];
$trip_id = isset($_GET['trip_id']) ? $_GET['trip_id'] : '';
$selected_trip = null;
$error_message = '';
$success_message = '';

// Get trip details if trip_id is provided
if (!empty($trip_id)) {
    $trip_query = $conn->prepare("
        SELECT t.*, m.name as mountain_name, m.difficulty, m.height, m.location, 
               g.name as guide_name, g.rating as guide_rating, g.experience,
               (t.max_participants - t.current_participants) as available_spots
        FROM trips t 
        JOIN mountains m ON t.mountain_id = m.id 
        JOIN guides g ON t.guide_id = g.id 
        WHERE t.id = ? AND t.start_date > CURDATE()
    ");
    $trip_query->bind_param("s", $trip_id);
    $trip_query->execute();
    $trip_result = $trip_query->get_result();
    $selected_trip = $trip_result->fetch_assoc();
}

// Handle booking submission - BAGIAN INI YANG DIPERBAIKI
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trip_id = $_POST['trip_id'];
    $participants = (int)$_POST['participants'];
    $booking_date = $_POST['booking_date'];
    $payment_method = $_POST['payment_method'];
    $special_requests = trim($_POST['special_requests']);
    
    // Validate inputs
    if (empty($trip_id) || $participants <= 0 || empty($booking_date) || empty($payment_method)) {
        $error_message = "Semua field wajib diisi dengan benar.";
    } else {
        // Get trip details for validation
        $trip_check = $conn->prepare("
            SELECT t.*, (t.max_participants - t.current_participants) as available_spots
            FROM trips t WHERE t.id = ?
        ");
        $trip_check->bind_param("s", $trip_id);
        $trip_check->execute();
        $trip_data = $trip_check->get_result()->fetch_assoc();
        
        if (!$trip_data) {
            $error_message = "Trip tidak ditemukan.";
        } elseif ($participants > $trip_data['available_spots']) {
            $error_message = "Jumlah peserta melebihi slot yang tersedia ({$trip_data['available_spots']} slot).";
        } elseif (strtotime($booking_date) < strtotime(date('Y-m-d'))) {
            // PERBAIKAN: Cek jika tanggal booking di masa lalu
            $error_message = "Tanggal booking tidak boleh di masa lalu.";
        } elseif (strtotime($booking_date) >= strtotime($trip_data['start_date'])) {
            // PERBAIKAN: Logika yang benar - booking harus SEBELUM tanggal mulai trip
            $error_message = "Tanggal booking harus sebelum tanggal mulai trip (" . date('d M Y', strtotime($trip_data['start_date'])) . ").";
        } else {
            // Calculate total price
            $total_price = $trip_data['price'] * $participants;
            
            // Generate booking ID
            $booking_id = uniqid('book_');
            
            // Insert booking
            $insert_booking = $conn->prepare("
                INSERT INTO bookings (id, user_id, trip_id, booking_date, participants, total_price, status, payment_method)
                VALUES (?, ?, ?, ?, ?, ?, 'pending', ?)
            ");
            $insert_booking->bind_param("sssiids", $booking_id, $user_id, $trip_id, $booking_date, $participants, $total_price, $payment_method);
            
            if ($insert_booking->execute()) {
                // Update trip current participants
                $update_trip = $conn->prepare("UPDATE trips SET current_participants = current_participants + ? WHERE id = ?");
                $update_trip->bind_param("is", $participants, $trip_id);
                $update_trip->execute();
                
                // Redirect to payment status
                header("Location: status_pembayaran.php?booking_id=" . $booking_id);
                exit();
            } else {
                $error_message = "Terjadi kesalahan saat membuat booking.";
            }
        }
    }
}

// Get all available trips for selection
$available_trips = $conn->query("
    SELECT t.*, m.name as mountain_name, m.difficulty, m.location,
           g.name as guide_name, (t.max_participants - t.current_participants) as available_spots
    FROM trips t 
    JOIN mountains m ON t.mountain_id = m.id 
    JOIN guides g ON t.guide_id = g.id 
    WHERE t.start_date > CURDATE() AND (t.max_participants - t.current_participants) > 0
    ORDER BY t.start_date ASC
");

// Get user's recent bookings
$recent_bookings = $conn->prepare("
    SELECT b.*, t.title as trip_title, m.name as mountain_name
    FROM bookings b 
    JOIN trips t ON b.trip_id = t.id 
    JOIN mountains m ON t.mountain_id = m.id
    WHERE b.user_id = ? 
    ORDER BY b.booking_date DESC 
    LIMIT 3
");
$recent_bookings->bind_param("s", $user_id);
$recent_bookings->execute();
$recent_result = $recent_bookings->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Trip - Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Booking Page Styles */
        :root {
            --primary-green: #2e8b57;
            --secondary-green: #3cb371;
            --light-green: #f0f9f4;
            --accent-green: #10b981;
            --dark-text: #1f2937;
            --light-text: #6b7280;
            --warning-yellow: #f59e0b;
            --danger-red: #dc2626;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
            background: #f8fafc;
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            color: white;
            padding: 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .logo {
            padding: 30px 25px;
            font-size: 1.5rem;
            font-weight: bold;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05);
        }

        .sidebar nav {
            padding: 20px 0;
        }

        .sidebar nav a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 25px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .sidebar nav a:hover,
        .sidebar nav a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: white;
        }

        .sidebar nav a i {
            width: 20px;
            text-align: center;
        }

        .main {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .booking-container {
            max-width: 1400px;
            margin: 0 auto;
        }

        /* Page Header */
        .page-header {
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            color: white;
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(46, 139, 87, 0.3);
            position: relative;
            overflow: hidden;
        }

        .page-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 120px;
            height: 120px;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="rgba(255,255,255,0.1)"><path d="M19 3h-1V1h-2v2H8V1H6v2H5c-1.11 0-1.99.9-1.99 2L3 19c0 1.1.89 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-5 6h4v4h-4V9z"/></svg>') no-repeat center;
            background-size: contain;
        }

        .header-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .header-icon {
            width: 80px;
            height: 80px;
            background: rgba(255,255,255,0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }

        .header-details h2 {
            margin: 0 0 10px 0;
            font-size: 2rem;
            font-weight: 700;
        }

        .header-details p {
            margin: 0;
            opacity: 0.9;
            font-size: 1.1rem;
        }

        /* Content Layout */
        .booking-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 30px;
        }

        .booking-form-section,
        .booking-sidebar {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .section-header {
            padding: 25px 30px;
            border-bottom: 1px solid #e5e7eb;
            background: var(--light-green);
        }

        .section-header h3 {
            margin: 0;
            color: var(--dark-text);
            font-size: 1.4rem;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .section-header i {
            color: var(--accent-green);
        }

        /* Trip Selection */
        .trip-selection {
            padding: 30px;
        }

        .trip-selector {
            position: relative;
        }

        .selected-trip {
            display: none;
            border: 2px solid var(--accent-green);
            border-radius: 15px;
            padding: 20px;
            background: var(--light-green);
            margin-bottom: 25px;
        }

        .selected-trip.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        .trip-info {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .trip-image {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.5rem;
        }

        .trip-details h4 {
            margin: 0 0 8px 0;
            color: var(--dark-text);
            font-size: 1.2rem;
            font-weight: 700;
        }

        .trip-meta {
            display: flex;
            gap: 20px;
            font-size: 0.9rem;
            color: var(--light-text);
            margin-top: 10px;
        }

        .trip-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .trip-dropdown {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .trip-dropdown:focus {
            outline: none;
            border-color: var(--accent-green);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        /* Booking Form */
        .booking-form {
            padding: 30px;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 25px;
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 8px;
            font-size: 1rem;
        }

        .required {
            color: var(--danger-red);
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--accent-green);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .participant-counter {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .counter-btn {
            width: 40px;
            height: 40px;
            border: 2px solid var(--accent-green);
            background: white;
            color: var(--accent-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-weight: bold;
        }

        .counter-btn:hover {
            background: var(--accent-green);
            color: white;
        }

        .counter-btn:disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .counter-value {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--dark-text);
            min-width: 40px;
            text-align: center;
        }

        .payment-options {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .payment-option {
            position: relative;
        }

        .payment-option input {
            position: absolute;
            opacity: 0;
            cursor: pointer;
        }

        .payment-card {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 20px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .payment-option input:checked + .payment-card {
            border-color: var(--accent-green);
            background: var(--light-green);
        }

        .payment-icon {
            width: 40px;
            height: 40px;
            background: var(--light-green);
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--accent-green);
            font-size: 1.2rem;
        }

        .payment-info h5 {
            margin: 0 0 5px 0;
            color: var(--dark-text);
            font-weight: 600;
        }

        .payment-info p {
            margin: 0;
            font-size: 0.85rem;
            color: var(--light-text);
        }

        /* Submit Button */
        .submit-section {
            padding: 30px;
            border-top: 1px solid #e5e7eb;
            background: #f9fafb;
        }

        .btn-book-now {
            width: 100%;
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            color: white;
            border: none;
            padding: 18px 30px;
            border-radius: 15px;
            font-weight: 700;
            font-size: 1.1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .btn-book-now:hover {
            background: linear-gradient(135deg, var(--secondary-green), var(--primary-green));
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(46, 139, 87, 0.3);
        }

        /* Sidebar Content */
        .booking-sidebar {
            height: fit-content;
        }

        .price-summary {
            padding: 30px;
        }

        .summary-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 12px 0;
            border-bottom: 1px solid #e5e7eb;
        }

        .summary-item:last-child {
            border-bottom: none;
            margin-top: 10px;
            padding-top: 20px;
            border-top: 2px solid var(--accent-green);
            font-weight: 700;
            font-size: 1.1rem;
        }

        .summary-label {
            color: var(--light-text);
        }

        .summary-value {
            color: var(--dark-text);
            font-weight: 600;
        }

        .total-value {
            color: var(--accent-green);
            font-size: 1.3rem;
        }

        .recent-bookings {
            border-top: 1px solid #e5e7eb;
        }

        .recent-item {
            padding: 20px 30px;
            border-bottom: 1px solid #f3f4f6;
        }

        .recent-item:last-child {
            border-bottom: none;
        }

        .recent-title {
            font-weight: 600;
            color: var(--dark-text);
            margin: 0 0 5px 0;
            font-size: 0.95rem;
        }

        .recent-info {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            color: var(--light-text);
        }

        .recent-status {
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.75rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-confirmed {
            background: #d1fae5;
            color: #065f46;
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .alert-info {
            background: #dbeafe;
            color: #1e40af;
            border: 1px solid #93c5fd;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .booking-content {
                grid-template-columns: 1fr;
            }
            
            .booking-sidebar {
                order: -1;
            }
        }

        @media (max-width: 767px) {
            .admin-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                order: 2;
            }

            .main {
                order: 1;
                padding: 20px;
            }

            .header-info {
                flex-direction: column;
                text-align: center;
            }

            .form-grid {
                grid-template-columns: 1fr;
            }

            .payment-options {
                grid-template-columns: 1fr;
            }

            .trip-info {
                flex-direction: column;
                text-align: center;
            }

            .trip-meta {
                justify-content: center;
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                max-height: 0;
            }
            to {
                opacity: 1;
                max-height: 300px;
            }
        }

        .availability-warning {
            background: #fef3c7;
            border: 1px solid #f59e0b;
            color: #92400e;
            padding: 12px 16px;
            border-radius: 10px;
            margin-top: 10px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Responsive Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <i class="fas fa-mountain"></i>
                Lombok Hiking
            </div>
            <nav>
                <a href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> 
                    Dashboard
                </a>
                <a href="profile.php">
                    <i class="fas fa-user"></i> 
                    Profil Saya
                </a>
                <a href="booking.php" class="active">
                    <i class="fas fa-calendar-plus"></i> 
                    Booking Trip
                </a>
                <a href="keranjang.php">
                    <i class="fas fa-shopping-cart"></i> 
                    Keranjang
                </a>
                <a href="status_pembayaran.php">
                    <i class="fas fa-credit-card"></i> 
                    Status Pembayaran
                </a>
                <a href="paket_saya.php">
                    <i class="fas fa-hiking"></i> 
                    Paket Saya
                </a>
                <a href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i> 
                    Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main">
            <div class="booking-container">
                <!-- Page Header -->
                <div class="page-header">
                    <div class="header-info">
                        <div class="header-icon">
                            <i class="fas fa-calendar-plus"></i>
                        </div>
                        <div class="header-details">
                            <h2>Booking Trip Pendakian</h2>
                            <p>Pilih trip impian Anda dan mulai petualangan hiking yang tak terlupakan!</p>
                        </div>
                    </div>
                </div>

                <!-- Booking Content -->
                <div class="booking-content">
                    <!-- Main Booking Form -->
                    <div class="booking-form-section">
                        <!-- Trip Selection -->
                        <div class="section-header">
                            <h3>
                                <i class="fas fa-mountain"></i>
                                Pilih Trip
                            </h3>
                        </div>
                        
                        <div class="trip-selection">
                            <?php if (!empty($error_message)): ?>
                                <div class="alert alert-error">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?= htmlspecialchars($error_message) ?>
                                </div>
                            <?php endif; ?>

                            <!-- Selected Trip Display -->
                            <?php if ($selected_trip): ?>
                                <div class="selected-trip show" id="selectedTripDisplay">
                                    <div class="trip-info">
                                        <div class="trip-image">
                                            <i class="fas fa-mountain"></i>
                                        </div>
                                        <div class="trip-details">
                                            <h4><?= htmlspecialchars($selected_trip['title']) ?></h4>
                                            <p><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($selected_trip['mountain_name']) ?>, <?= htmlspecialchars($selected_trip['location']) ?></p>
                                            <div class="trip-meta">
                                                <span><i class="fas fa-calendar-alt"></i> <?= $selected_trip['duration'] ?> hari</span>
                                                <span><i class="fas fa-users"></i> <?= $selected_trip['available_spots'] ?> slot tersisa</span>
                                                <span><i class="fas fa-star"></i> <?= htmlspecialchars($selected_trip['difficulty']) ?></span>
                                                <span><i class="fas fa-money-bill-wave"></i> Rp <?= number_format($selected_trip['price'], 0, ',', '.') ?>/orang</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endif; ?>

                            <!-- Trip Dropdown -->
                            <div class="form-group">
                                <label for="trip_selector">Pilih Trip yang Tersedia</label>
                                <select class="trip-dropdown" id="trip_selector" onchange="selectTrip(this.value)">
                                    <option value="">-- Pilih Trip --</option>
                                    <?php while ($trip = $available_trips->fetch_assoc()): ?>
                                        <option value="<?= $trip['id'] ?>" 
                                                data-title="<?= htmlspecialchars($trip['title']) ?>"
                                                data-mountain="<?= htmlspecialchars($trip['mountain_name']) ?>"
                                                data-location="<?= htmlspecialchars($trip['location']) ?>"
                                                data-duration="<?= $trip['duration'] ?>"
                                                data-price="<?= $trip['price'] ?>"
                                                data-spots="<?= $trip['available_spots'] ?>"
                                                data-difficulty="<?= htmlspecialchars($trip['difficulty']) ?>"
                                                data-start="<?= $trip['start_date'] ?>"
                                                data-end="<?= $trip['end_date'] ?>"
                                                <?= ($selected_trip && $trip['id'] == $selected_trip['id']) ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($trip['title']) ?> - <?= htmlspecialchars($trip['mountain_name']) ?> 
                                            (<?= date('d M', strtotime($trip['start_date'])) ?>) - Rp <?= number_format($trip['price'], 0, ',', '.') ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>

                        <!-- Booking Form -->
                        <div class="section-header">
                            <h3>
                                <i class="fas fa-edit"></i>
                                Detail Booking
                            </h3>
                        </div>

                        <form method="POST" action="booking.php" class="booking-form" id="bookingForm">
                            <input type="hidden" name="trip_id" id="selected_trip_id" value="<?= htmlspecialchars($trip_id) ?>">
                            
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="participants">Jumlah Peserta <span class="required">*</span></label>
                                    <div class="participant-counter">
                                        <button type="button" class="counter-btn" onclick="changeParticipants(-1)" id="decreaseBtn">-</button>
                                        <input type="number" name="participants" id="participants" value="1" min="1" max="10" readonly class="counter-value">
                                        <button type="button" class="counter-btn" onclick="changeParticipants(1)" id="increaseBtn">+</button>
                                    </div>
                                    <div class="availability-warning" id="availabilityWarning" style="display: none;">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <span id="warningText"></span>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label for="booking_date">Tanggal Booking <span class="required">*</span></label>
                                    <input type="date" name="booking_date" id="booking_date" 
                                           value="<?= date('Y-m-d') ?>" 
                                           min="<?= date('Y-m-d') ?>" 
                                           max="<?= $selected_trip ? date('Y-m-d', strtotime($selected_trip['start_date'] . ' -1 day')) : '' ?>"
                                           required>
                                </div>
                            </div>

                            <div class="form-group">
                                <label>Metode Pembayaran <span class="required">*</span></label>
                                <div class="payment-options">
                                    <div class="payment-option">
                                        <input type="radio" name="payment_method" value="Transfer Bank" id="bank_transfer" required>
                                        <label for="bank_transfer" class="payment-card">
                                            <div class="payment-icon">
                                                <i class="fas fa-university"></i>
                                            </div>
                                            <div class="payment-info">
                                                <h5>Transfer Bank</h5>
                                                <p>BCA, Mandiri, BRI, BNI</p>
                                            </div>
                                        </label>
                                    </div>

                                    <div class="payment-option">
                                        <input type="radio" name="payment_method" value="E-Wallet" id="e_wallet">
                                        <label for="e_wallet" class="payment-card">
                                            <div class="payment-icon">
                                                <i class="fas fa-mobile-alt"></i>
                                            </div>
                                            <div class="payment-info">
                                                <h5>E-Wallet</h5>
                                                <p>GoPay, OVO, Dana, ShopeePay</p>
                                            </div>
                                        </label>
                                    </div>

                                    <div class="payment-option">
                                        <input type="radio" name="payment_method" value="Kartu Kredit" id="credit_card">
                                        <label for="credit_card" class="payment-card">
                                            <div class="payment-icon">
                                                <i class="fas fa-credit-card"></i>
                                            </div>
                                            <div class="payment-info">
                                                <h5>Kartu Kredit</h5>
                                                <p>Visa, Mastercard, JCB</p>
                                            </div>
                                        </label>
                                    </div>
                                </div>
                            </div>

                            <div class="form-group">
                                <label for="special_requests">Permintaan Khusus (Opsional)</label>
                                <textarea name="special_requests" id="special_requests" 
                                          placeholder="Masukkan permintaan khusus seperti diet makanan, alergi, atau kebutuhan khusus lainnya..."></textarea>
                            </div>

                            <div class="submit-section">
                                <button type="submit" class="btn-book-now" id="submitBtn" disabled>
                                    <i class="fas fa-hiking"></i>
                                    Book Trip Sekarang
                                </button>
                                <div class="alert alert-info" style="margin-top: 15px;">
                                    <i class="fas fa-info-circle"></i>
                                    Dengan melakukan booking, Anda menyetujui syarat dan ketentuan perjalanan.
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Booking Sidebar -->
                    <div class="booking-sidebar">
                        <!-- Price Summary -->
                        <div class="section-header">
                            <h3>
                                <i class="fas fa-calculator"></i>
                                Ringkasan Harga
                            </h3>
                        </div>
                        
                        <div class="price-summary">
                            <div class="summary-item">
                                <span class="summary-label">Harga per orang:</span>
                                <span class="summary-value" id="price-per-person">Rp 0</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Jumlah peserta:</span>
                                <span class="summary-value" id="participant-count">0 orang</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label">Subtotal:</span>
                                <span class="summary-value" id="subtotal">Rp 0</span>
                            </div>
                            <div class="summary-item">
                                <span class="summary-label total-label">Total Pembayaran:</span>
                                <span class="summary-value total-value" id="total-payment">Rp 0</span>
                            </div>
                        </div>

                        <!-- Recent Bookings -->
                        <div class="section-header">
                            <h3>
                                <i class="fas fa-history"></i>
                                Booking Terbaru
                            </h3>
                        </div>

                        <div class="recent-bookings">
                            <?php if ($recent_result->num_rows > 0): ?>
                                <?php while ($booking = $recent_result->fetch_assoc()): ?>
                                    <div class="recent-item">
                                        <h5 class="recent-title"><?= htmlspecialchars($booking['trip_title']) ?></h5>
                                        <div class="recent-info">
                                            <span><?= htmlspecialchars($booking['mountain_name']) ?></span>
                                            <span class="recent-status status-<?= $booking['status'] ?>">
                                                <?= ucfirst($booking['status']) ?>
                                            </span>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="recent-item">
                                    <p style="text-align: center; color: var(--light-text); font-style: italic;">
                                        <i class="fas fa-info-circle"></i> Belum ada booking sebelumnya
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        let selectedTripData = null;
        let currentParticipants = 1;
        let currentPrice = 0;
        let maxParticipants = 0;

        // Initialize with selected trip if exists
        <?php if ($selected_trip): ?>
        selectedTripData = {
            id: '<?= $selected_trip['id'] ?>',
            title: '<?= htmlspecialchars($selected_trip['title']) ?>',
            mountain: '<?= htmlspecialchars($selected_trip['mountain_name']) ?>',
            location: '<?= htmlspecialchars($selected_trip['location']) ?>',
            duration: <?= $selected_trip['duration'] ?>,
            price: <?= $selected_trip['price'] ?>,
            spots: <?= $selected_trip['available_spots'] ?>,
            difficulty: '<?= htmlspecialchars($selected_trip['difficulty']) ?>',
            start_date: '<?= $selected_trip['start_date'] ?>',
            end_date: '<?= $selected_trip['end_date'] ?>'
        };
        currentPrice = selectedTripData.price;
        maxParticipants = selectedTripData.spots;
        document.getElementById('booking_date').max = new Date(new Date(selectedTripData.start_date).getTime() - 86400000).toISOString().split('T')[0];
        updatePriceSummary();
        updateSubmitButton();
        <?php endif; ?>

        // Trip selection
        function selectTrip(tripId) {
            const selector = document.getElementById('trip_selector');
            const selectedOption = selector.options[selector.selectedIndex];
            
            if (!tripId || !selectedOption.dataset.title) {
                selectedTripData = null;
                hideSelectedTrip();
                updatePriceSummary();
                updateSubmitButton();
                return;
            }

            selectedTripData = {
                id: tripId,
                title: selectedOption.dataset.title,
                mountain: selectedOption.dataset.mountain,
                location: selectedOption.dataset.location,
                duration: parseInt(selectedOption.dataset.duration),
                price: parseInt(selectedOption.dataset.price),
                spots: parseInt(selectedOption.dataset.spots),
                difficulty: selectedOption.dataset.difficulty,
                start_date: selectedOption.dataset.start,
                end_date: selectedOption.dataset.end
            };

            currentPrice = selectedTripData.price;
            maxParticipants = selectedTripData.spots;
            
            showSelectedTrip();
            updateBookingDateLimits();
            updateParticipantLimits();
            updatePriceSummary();
            updateSubmitButton();
            
            document.getElementById('selected_trip_id').value = tripId;
        }

        function showSelectedTrip() {
            const display = document.getElementById('selectedTripDisplay');
            if (!display) {
                // Create the display element if it doesn't exist
                const tripSelection = document.querySelector('.trip-selection');
                const displayHTML = `
                    <div class="selected-trip" id="selectedTripDisplay">
                        <div class="trip-info">
                            <div class="trip-image">
                                <i class="fas fa-mountain"></i>
                            </div>
                            <div class="trip-details">
                                <h4 id="displayTitle">${selectedTripData.title}</h4>
                                <p><i class="fas fa-map-marker-alt"></i> <span id="displayLocation">${selectedTripData.mountain}, ${selectedTripData.location}</span></p>
                                <div class="trip-meta">
                                    <span><i class="fas fa-calendar-alt"></i> <span id="displayDuration">${selectedTripData.duration}</span> hari</span>
                                    <span><i class="fas fa-users"></i> <span id="displaySpots">${selectedTripData.spots}</span> slot tersisa</span>
                                    <span><i class="fas fa-star"></i> <span id="displayDifficulty">${selectedTripData.difficulty}</span></span>
                                    <span><i class="fas fa-money-bill-wave"></i> Rp <span id="displayPrice">${new Intl.NumberFormat('id-ID').format(selectedTripData.price)}</span>/orang</span>
                                </div>
                            </div>
                        </div>
                    </div>
                `;
                tripSelection.insertAdjacentHTML('afterbegin', displayHTML);
                document.getElementById('selectedTripDisplay').classList.add('show');
            } else {
                // Update existing display
                document.getElementById('displayTitle').textContent = selectedTripData.title;
                document.getElementById('displayLocation').textContent = `${selectedTripData.mountain}, ${selectedTripData.location}`;
                document.getElementById('displayDuration').textContent = selectedTripData.duration;
                document.getElementById('displaySpots').textContent = selectedTripData.spots;
                document.getElementById('displayDifficulty').textContent = selectedTripData.difficulty;
                document.getElementById('displayPrice').textContent = new Intl.NumberFormat('id-ID').format(selectedTripData.price);
                display.classList.add('show');
            }
        }

        function hideSelectedTrip() {
            const display = document.getElementById('selectedTripDisplay');
            if (display) {
                display.classList.remove('show');
            }
        }

        function updateBookingDateLimits() {
            const bookingDate = document.getElementById('booking_date');
            if (selectedTripData) {
                const maxDate = new Date(new Date(selectedTripData.start_date).getTime() - 86400000);
                bookingDate.max = maxDate.toISOString().split('T')[0];
            }
        }

        // Participant counter
        function changeParticipants(change) {
            const newValue = currentParticipants + change;
            
            if (newValue >= 1 && newValue <= maxParticipants && newValue <= 10) {
                currentParticipants = newValue;
                document.getElementById('participants').value = currentParticipants;
                updateParticipantLimits();
                updatePriceSummary();
                updateSubmitButton();
            }
        }

        function updateParticipantLimits() {
            const decreaseBtn = document.getElementById('decreaseBtn');
            const increaseBtn = document.getElementById('increaseBtn');
            const warningDiv = document.getElementById('availabilityWarning');
            const warningText = document.getElementById('warningText');
            
            decreaseBtn.disabled = currentParticipants <= 1;
            increaseBtn.disabled = currentParticipants >= maxParticipants || currentParticipants >= 10;
            
            if (selectedTripData) {
                if (currentParticipants >= selectedTripData.spots) {
                    warningDiv.style.display = 'flex';
                    warningText.textContent = 'Jumlah peserta mencapai batas maksimal slot yang tersedia.';
                } else if (currentParticipants >= selectedTripData.spots * 0.8) {
                    warningDiv.style.display = 'flex';
                    warningText.textContent = 'Slot hampir penuh! Segera konfirmasi booking Anda.';
                } else {
                    warningDiv.style.display = 'none';
                }
            }
        }

        // Price calculation
        function updatePriceSummary() {
            const pricePerPerson = document.getElementById('price-per-person');
            const participantCount = document.getElementById('participant-count');
            const subtotal = document.getElementById('subtotal');
            const totalPayment = document.getElementById('total-payment');
            
            if (selectedTripData && currentPrice > 0) {
                const total = currentPrice * currentParticipants;
                
                pricePerPerson.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(currentPrice)}`;
                participantCount.textContent = `${currentParticipants} orang`;
                subtotal.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(total)}`;
                totalPayment.textContent = `Rp ${new Intl.NumberFormat('id-ID').format(total)}`;
            } else {
                pricePerPerson.textContent = 'Rp 0';
                participantCount.textContent = '0 orang';
                subtotal.textContent = 'Rp 0';
                totalPayment.textContent = 'Rp 0';
            }
        }

        // Form validation
        function updateSubmitButton() {
            const submitBtn = document.getElementById('submitBtn');
            const tripSelected = selectedTripData !== null;
            const participantsValid = currentParticipants > 0 && currentParticipants <= maxParticipants;
            const dateValid = document.getElementById('booking_date').value !== '';
            const paymentSelected = document.querySelector('input[name="payment_method"]:checked') !== null;
            
            const formValid = tripSelected && participantsValid && dateValid && paymentSelected;
            
            submitBtn.disabled = !formValid;
            
            if (formValid) {
                submitBtn.style.opacity = '1';
                submitBtn.style.cursor = 'pointer';
            } else {
                submitBtn.style.opacity = '0.6';
                submitBtn.style.cursor = 'not-allowed';
            }
        }

        // Event listeners
        document.getElementById('booking_date').addEventListener('change', updateSubmitButton);
        document.querySelectorAll('input[name="payment_method"]').forEach(radio => {
            radio.addEventListener('change', updateSubmitButton);
        });

        // PERBAIKAN VALIDASI JAVASCRIPT
        document.getElementById('bookingForm').addEventListener('submit', function(e) {
            if (!selectedTripData) {
                e.preventDefault();
                alert('Silakan pilih trip terlebih dahulu!');
                return;
            }
            
            if (currentParticipants > selectedTripData.spots) {
                e.preventDefault();
                alert(`Jumlah peserta melebihi slot yang tersedia (${selectedTripData.spots} slot)!`);
                return;
            }
            
            const bookingDate = new Date(document.getElementById('booking_date').value);
            const startDate = new Date(selectedTripData.start_date);
            const today = new Date();
            today.setHours(0, 0, 0, 0);
            
            // PERBAIKAN: Cek tanggal masa lalu
            if (bookingDate < today) {
                e.preventDefault();
                alert('Tanggal booking tidak boleh di masa lalu!');
                return;
            }
            
            // PERBAIKAN: Cek tanggal booking vs tanggal mulai trip
            if (bookingDate >= startDate) {
                e.preventDefault();
                alert('Tanggal booking harus sebelum tanggal mulai trip!');
                return;
            }
            
            // Confirmation dialog
            const total = currentPrice * currentParticipants;
            const confirmMessage = `Konfirmasi Booking:\n\nTrip: ${selectedTripData.title}\nPeserta: ${currentParticipants} orang\nTotal: Rp ${new Intl.NumberFormat('id-ID').format(total)}\n\nLanjutkan booking?`;
            
            if (!confirm(confirmMessage)) {
                e.preventDefault();
            }
        });

        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateSubmitButton();
            
            // Auto-select trip from URL parameter
            const urlParams = new URLSearchParams(window.location.search);
            const tripIdFromUrl = urlParams.get('trip_id');
            if (tripIdFromUrl && !selectedTripData) {
                document.getElementById('trip_selector').value = tripIdFromUrl;
                selectTrip(tripIdFromUrl);
            }
        });
    </script>
</body>
</html>


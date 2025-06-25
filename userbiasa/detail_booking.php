<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is a regular user
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: ../views/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get the booking ID from the URL parameter
if (isset($_GET['booking_id'])) {
    $booking_id = $_GET['booking_id'];

    // Fetch the detailed booking information
    $booking_query = $conn->prepare("
        SELECT b.*, t.title, t.package_price, t.start_date, t.end_date, t.description, m.name as mountain_name, 
               b.bukti_pembayaran, b.booking_date
        FROM bookings b 
        JOIN trips t ON b.trip_id = t.id
        JOIN mountains m ON t.mountain_id = m.id
        WHERE b.user_id = ? AND b.id = ? AND b.status = 'confirmed'
    ");
    $booking_query->bind_param("si", $user_id, $booking_id);
    $booking_query->execute();
    $booking_result = $booking_query->get_result();

    // Check if the booking exists
    if ($booking_result->num_rows === 0) {
        // If no booking found, redirect to the user's paket saya page
        header('Location: paket_saya.php');
        exit();
    }

    $booking = $booking_result->fetch_assoc();
} else {
    // If no booking ID is passed, redirect to the user's paket saya page
    header('Location: paket_saya.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Booking - Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Detail Booking Styles */
        :root {
            --primary-green: #2e8b57;
            --secondary-green: #3cb371;
            --light-green: #f0f9f4;
            --accent-green: #10b981;
            --dark-text: #1f2937;
            --light-text: #6b7280;
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

        .page-header h2 {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
        }

        .booking-details {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }

        .booking-item {
            display: flex;
            gap: 20px;
            align-items: center;
            margin-bottom: 20px;
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
        }

        .booking-item img {
            width: 100px;
            height: 100px;
            border-radius: 12px;
        }

        .booking-item .item-info {
            font-size: 1.1rem;
            color: var(--dark-text);
        }

        .booking-item .item-title {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .booking-item .item-info div {
            margin-bottom: 5px;
        }

        .payment-proof {
            font-size: 1rem;
            color: var(--accent-green);
            margin-top: 10px;
        }

        .payment-proof img {
            max-width: 200px;
            margin-top: 10px;
        }

        .booking-date {
            font-size: 0.9rem;
            color: var(--light-text);
            margin-top: 5px;
        }

        .action-btns {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
        }

        .action-btns a {
            padding: 10px 20px;
            background: var(--accent-green);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .action-btns a:hover {
            background: var(--primary-green);
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
            <div class="page-header">
                <h2>Detail Booking Trip</h2>
            </div>

            <div class="booking-details">
                <div class="booking-item">
                    <img src="../assets/images/trips/<?= htmlspecialchars($booking['title']) ?>.jpg" alt="Trip Image">
                    <div class="item-info">
                        <div class="item-title"><?= htmlspecialchars($booking['title']) ?></div>
                        <div>Gunung: <?= htmlspecialchars($booking['mountain_name']) ?></div>
                        <div>Tanggal Mulai: <?= date('d M Y', strtotime($booking['start_date'])) ?></div>
                        <div>Tanggal Selesai: <?= date('d M Y', strtotime($booking['end_date'])) ?></div>
                        <div>Status: <span style="color: var(--accent-green); font-weight: bold;">Konfirmasi</span></div>
                        <div>Total Harga: Rp <?= number_format($booking['total_price'], 0, ',', '.') ?></div>
                        <div class="booking-date">Tanggal Pemesanan: <?= date('d M Y', strtotime($booking['booking_date'])) ?></div>
                        <?php if ($booking['payment_proof']): ?>
                            <div class="payment-proof">
                                Bukti Pembayaran:
                                <img src="../uploads/payment/<?= htmlspecialchars($booking['payment_proof']) ?>" alt="Bukti Pembayaran">
                            </div>
                        <?php else: ?>
                            <div class="payment-proof">Bukti Pembayaran Belum Diupload</div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="action-btns">
                    <a href="paket_saya.php">Kembali ke Paket Saya</a>
                    <a href="status_pembayaran.php">Cek Status Pembayaran</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

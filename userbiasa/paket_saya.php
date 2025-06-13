<?php
session_start();
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: ../views/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Fetch all bookings for this user with status 'confirmed'
$query = $conn->prepare("
    SELECT b.*, t.title, t.price, t.start_date, t.end_date, m.name as mountain_name, b.booking_date, b.status
    FROM bookings b
    JOIN trips t ON b.trip_id = t.id
    JOIN mountains m ON t.mountain_id = m.id
    WHERE b.user_id = ? AND b.status = 'confirmed'
");
$query->bind_param("s", $user_id);
$query->execute();
$result = $query->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Paket Saya - Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Paket Saya Styles */
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

        .trip-list {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            padding: 30px;
        }

        .trip-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
        }

        .trip-item .item-info {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .trip-item .item-info img {
            width: 60px;
            height: 60px;
            border-radius: 12px;
        }

        .trip-item .item-info div {
            font-size: 1.1rem;
            color: var(--dark-text);
        }

        .trip-item .item-info .item-title {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .trip-item .item-actions {
            text-align: center;
            font-size: 1rem;
            color: var(--light-text);
        }

        .trip-item .item-actions a {
            padding: 10px 20px;
            background: var(--accent-green);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            transition: background 0.3s ease;
        }

        .trip-item .item-actions a:hover {
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
                <a href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> 
                    Dashboard
                </a>
                <a href="profile.php">
                    <i class="fas fa-user"></i> 
                    Profil Saya
                </a>
                <a href="booking.php">
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
                <a href="paket_saya.php" class="active">
                    <i class="fas fa-hiking"></i> 
                    Paket Saya
                </a>
                <a href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i> 
                    Logout
                </a>
            </nav>
        </aside>

        <main class="main">
            <div class="page-header">
                <h2>Paket Saya</h2>
            </div>

            <div class="trip-list">
                <?php if ($result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <div class="trip-item">
                            <div class="item-info">
                                <img src="../assets/images/trips/<?= htmlspecialchars($row['title']) ?>.jpg" alt="Trip Image">
                                <div>
                                    <div class="item-title"><?= htmlspecialchars($row['title']) ?></div>
                                    <div>Gunung: <?= htmlspecialchars($row['mountain_name']) ?></div>
                                    <div>Tanggal Mulai: <?= date('d M Y', strtotime($row['start_date'])) ?></div>
                                    <div>Tanggal Selesai: <?= date('d M Y', strtotime($row['end_date'])) ?></div>
                                </div>
                            </div>
                            <div class="item-actions">
                                <!-- Link to the detail booking page -->
                                <a href="detail_booking.php?booking_id=<?= $row['id'] ?>">Lihat Detail Booking</a>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <p>Anda belum melakukan booking trip apapun.</p>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>

<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is a guide
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'guide') {
    header('Location: ../views/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// Get guide info
$guide_query = $conn->prepare("SELECT * FROM guide WHERE user_id = ?");
$guide_query->bind_param("i", $user_id); // user_id integer
$guide_query->execute();
$guide_result = $guide_query->get_result();
$guide_info = $guide_result->fetch_assoc();
$guide_id = $guide_info['id'] ?? null;

$message = '';
$error = '';

// Handle booking actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'update_status':
            $booking_id = intval($_POST['booking_id']);
            $new_status = $_POST['status'];

            // Validate status value
            $allowed_statuses = ['pending', 'confirmed', 'cancelled'];
            if (!in_array($new_status, $allowed_statuses)) {
                $error = "Status tidak valid.";
                break;
            }
            
            // Verify that this booking belongs to guide's trip
            $verify_query = $conn->prepare("
                SELECT b.id FROM bookings b 
                JOIN trips t ON b.trip_id = t.id 
                WHERE b.id = ? AND t.guide_id = ?
            ");
            $verify_query->bind_param("ii", $booking_id, $guide_id);
            $verify_query->execute();
            
            if ($verify_query->get_result()->num_rows > 0) {
                $update_query = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
                $update_query->bind_param("si", $new_status, $booking_id);
                
                if ($update_query->execute()) {
                    $message = "Status pesanan berhasil diperbarui!";
                } else {
                    $error = "Gagal memperbarui status pesanan.";
                }
            } else {
                $error = "Pesanan tidak ditemukan atau bukan milik Anda.";
            }
            break;
        case 'verifikasi_pembayaran':
            $booking_id = intval($_POST['booking_id']);
            // Cek booking milik guide
            $verify_query = $conn->prepare("
                SELECT b.id FROM bookings b 
                JOIN trips t ON b.trip_id = t.id 
                WHERE b.id = ? AND t.guide_id = ?
            ");
            $verify_query->bind_param("ii", $booking_id, $guide_id);
            $verify_query->execute();
            if ($verify_query->get_result()->num_rows > 0) {
                // Update status pembayaran dan booking
                $conn->query("UPDATE pembayaran SET status='paid', payment_date=NOW() WHERE booking_id=$booking_id");
                $conn->query("UPDATE bookings SET status='confirmed' WHERE id=$booking_id");
                $message = "Pembayaran berhasil diverifikasi.";
            } else {
                $error = "Booking tidak ditemukan atau bukan milik Anda.";
            }
            break;
        case 'tolak_pembayaran':
            $booking_id = intval($_POST['booking_id']);
            $verify_query = $conn->prepare("
                SELECT b.id FROM bookings b 
                JOIN trips t ON b.trip_id = t.id 
                WHERE b.id = ? AND t.guide_id = ?
            ");
            $verify_query->bind_param("ii", $booking_id, $guide_id);
            $verify_query->execute();
            if ($verify_query->get_result()->num_rows > 0) {
                $conn->query("UPDATE pembayaran SET status='rejected' WHERE booking_id=$booking_id");
                $conn->query("UPDATE bookings SET status='cancelled' WHERE id=$booking_id");
                $message = "Pembayaran ditolak.";
            } else {
                $error = "Booking tidak ditemukan atau bukan milik Anda.";
            }
            break;
    }
}

// Get filter parameters
$status_filter = $_GET['status'] ?? 'all';
$date_filter = $_GET['date'] ?? 'all';

// Build query for bookings
$where_conditions = ["t.guide_id = ?"];
$params = [$guide_id];
$param_types = "i";

if ($status_filter !== 'all') {
    $where_conditions[] = "b.status = ?";
    $params[] = $status_filter;
    $param_types .= "s";
}

if ($date_filter !== 'all') {
    switch ($date_filter) {
        case 'today':
            $where_conditions[] = "DATE(b.booking_date) = CURDATE()";
            break;
        case 'week':
            $where_conditions[] = "b.booking_date >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)";
            break;
        case 'month':
            $where_conditions[] = "b.booking_date >= DATE_SUB(CURDATE(), INTERVAL 30 DAY)";
            break;
    }
}

$where_clause = implode(" AND ", $where_conditions);

// Get bookings with trip and user details
$bookings_query = $conn->prepare("
    SELECT 
        b.*,
        t.title as trip_title,
        t.start_date,
        t.end_date,
        t.package_price as trip_price,
        m.name as mountain_name,
        u.name as user_name,
        u.email as user_email,
        u.phone as user_phone,
        p.payment_proof,
        p.status as payment_status
    FROM bookings b
    JOIN trips t ON b.trip_id = t.id
    JOIN mountains m ON t.mountain_id = m.id
    JOIN users u ON b.user_id = u.id
    LEFT JOIN pembayaran p ON b.id = p.booking_id
    WHERE {$where_clause}
    ORDER BY b.booking_date DESC
");

if (!empty($params)) {
    // bind_param requires references
    $refs = [];
    foreach ($params as $key => $value) {
        $refs[$key] = &$params[$key];
    }
    call_user_func_array([$bookings_query, 'bind_param'], array_merge([$param_types], $refs));
}

$bookings_query->execute();
$bookings = $bookings_query->get_result()->fetch_all(MYSQLI_ASSOC);

// Get statistics
$stats_query = $conn->prepare("
    SELECT 
        COUNT(*) as total_bookings,
        SUM(CASE WHEN b.status = 'pending' THEN 1 ELSE 0 END) as pending_count,
        SUM(CASE WHEN b.status = 'confirmed' THEN 1 ELSE 0 END) as confirmed_count,
        SUM(CASE WHEN b.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_count,
        SUM(CASE WHEN b.status = 'confirmed' THEN b.total_price ELSE 0 END) as total_revenue
    FROM bookings b
    JOIN trips t ON b.trip_id = t.id
    WHERE t.guide_id = ?
");
$stats_query->bind_param("i", $guide_id);
$stats_query->execute();
$stats = $stats_query->get_result()->fetch_assoc();
?>
<!-- HTML bagian tetap sama seperti sebelumnya -->

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesanan Saya - Guide Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Main Layout - consistent with other pages */
        .bookings-header {
            margin-bottom: 30px;
        }
        
        .bookings-header h1 {
            color: #2c3e50;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .bookings-header p {
            color: #6c757d;
            margin: 0;
        }
        
        /* Stats Grid - same style as dashboard */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid #e9ecef;
            border-radius: 15px;
            padding: 25px;
            display: flex;
            align-items: center;
            gap: 20px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(135deg, #2e8b57, #3cb371);
        }
        
        .stat-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        
        .stat-card i {
            font-size: 2.5rem;
            color: #2e8b57;
            opacity: 0.8;
        }
        
        .stat-card div h3 {
            margin: 0 0 5px 0;
            color: #6c757d;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-card div p {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
            color: #2c3e50;
        }
        
        /* Filters Section */
        .filters-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .filters-section h3 {
            margin: 0 0 20px 0;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            align-items: end;
        }
        
        .filter-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .filter-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .filter-group select:focus {
            outline: none;
            border-color: #2e8b57;
            box-shadow: 0 0 0 3px rgba(46, 139, 87, 0.1);
        }
        
        .filter-actions {
            display: flex;
            gap: 10px;
        }
        
        .filter-btn {
            padding: 12px 20px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-filter {
            background: linear-gradient(135deg, #2e8b57, #3cb371);
            color: white;
            box-shadow: 0 4px 15px rgba(46, 139, 87, 0.3);
        }
        
        .btn-reset {
            background: #6c757d;
            color: white;
        }
        
        .filter-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 139, 87, 0.4);
        }
        
        /* Bookings Grid */
        .bookings-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 25px;
        }
        
        .booking-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }
        
        .booking-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        
        .booking-header {
            padding: 20px;
            background: linear-gradient(135deg, rgba(46, 139, 87, 0.1), rgba(60, 179, 113, 0.1));
            border-bottom: 1px solid #e9ecef;
        }
        
        .booking-status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }
        
        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }
        
        .status-confirmed {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .status-cancelled {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .trip-info h4 {
            margin: 0 0 5px 0;
            color: #2c3e50;
            font-size: 1.2rem;
        }
        
        .mountain-name {
            color: #2e8b57;
            font-weight: 600;
            margin-bottom: 10px;
        }
        
        .trip-dates {
            color: #6c757d;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .booking-content {
            padding: 20px;
        }
        
        .customer-section {
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .customer-section h5 {
            margin: 0 0 10px 0;
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .customer-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
            font-size: 0.9rem;
        }
        
        .customer-details div {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #6c757d;
        }
        
        .booking-details {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .detail-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .detail-label {
            font-size: 0.8rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .detail-value {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .participants-badge {
            background: #e3f2fd;
            color: #1976d2;
            padding: 4px 8px;
            border-radius: 4px;
            font-weight: 600;
            display: inline-block;
        }
        
        .price-highlight {
            font-size: 1.3rem;
            font-weight: 700;
            color: #e67e22;
        }
        
        .booking-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 10px 16px;
            border: none;
            border-radius: 8px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            flex: 1;
            justify-content: center;
            min-width: 100px;
        }
        
        .btn-confirm {
            background: #28a745;
            color: white;
        }
        
        .btn-cancel {
            background: #dc3545;
            color: white;
        }
        
        .btn-contact {
            background: #17a2b8;
            color: white;
        }
        
        .action-btn:hover {
            transform: scale(1.05);
            opacity: 0.9;
        }
        
        .action-btn:disabled {
            background: #6c757d;
            cursor: not-allowed;
            transform: none;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #e9ecef;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: #6c757d;
            margin-bottom: 15px;
        }
        
        .empty-state p {
            color: #adb5bd;
            margin-bottom: 25px;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        /* Responsive Design */
        @media (max-width: 767px) {
            .admin-container {
                flex-direction: column;
            }
            
            .sidebar {
                width: 100%;
                height: auto;
                order: 2;
            }
            
            .main {
                order: 1;
                padding: 15px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 15px;
            }
            
            .bookings-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .filters-grid {
                grid-template-columns: 1fr;
            }
            
            .customer-details {
                grid-template-columns: 1fr;
            }
            
            .booking-details {
                grid-template-columns: 1fr;
            }
            
            .booking-actions {
                flex-direction: column;
            }
            
            .action-btn {
                flex: auto;
                min-width: auto;
            }
        }
        
        /* Large Desktop */
        @media (min-width: 1440px) {
            .main {
                max-width: 1400px;
                margin: 0 auto;
                padding: 30px 40px;
            }
            
            .bookings-grid {
                grid-template-columns: repeat(auto-fill, minmax(450px, 1fr));
                gap: 30px;
            }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Responsive Sidebar -->
        <aside class="sidebar">
            <div class="logo">Guide Panel</div>
            <nav>
                <a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
                <a href="profile.php"><i class="fas fa-user-edit"></i> Profile</a>
                <a href="trips.php"><i class="fas fa-route"></i> Trip Saya</a>
                <a href="bookings.php" class="active"><i class="fas fa-calendar-check"></i> Pesanan</a>
                <a href="schedule.php"><i class="fas fa-calendar-alt"></i> Jadwal</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main">
            <!-- Header -->
            <div class="bookings-header">
                <h1><i class="fas fa-calendar-check"></i> Pesanan Saya</h1>
                <p>Kelola pesanan dan booking untuk trip pendakian Anda</p>
            </div>

            <!-- Alerts -->
            <?php if ($message): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <!-- Statistics Grid -->
            <section class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-clipboard-list"></i>
                    <div>
                        <h3>Total Pesanan</h3>
                        <p><?php echo $stats['total_bookings'] ?? 0; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <div>
                        <h3>Menunggu Konfirmasi</h3>
                        <p><?php echo $stats['pending_count'] ?? 0; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <h3>Dikonfirmasi</h3>
                        <p><?php echo $stats['confirmed_count'] ?? 0; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <i class="fas fa-times-circle"></i>
                    <div>
                        <h3>Dibatalkan</h3>
                        <p><?php echo $stats['cancelled_count'] ?? 0; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <i class="fas fa-money-bill-wave"></i>
                    <div>
                        <h3>Total Pendapatan</h3>
                        <p>Rp <?php echo number_format($stats['total_revenue'] ?? 0, 0, ',', '.'); ?></p>
                    </div>
                </div>
            </section>

            <!-- Filters Section -->
            <section class="filters-section">
                <h3><i class="fas fa-filter"></i> Filter Pesanan</h3>
                <form method="GET" class="filters-grid">
                    <div class="filter-group">
                        <label for="status">Status</label>
                        <select name="status" id="status">
                            <option value="all" <?php echo ($status_filter === 'all') ? 'selected' : ''; ?>>Semua Status</option>
                            <option value="pending" <?php echo ($status_filter === 'pending') ? 'selected' : ''; ?>>Menunggu Konfirmasi</option>
                            <option value="confirmed" <?php echo ($status_filter === 'confirmed') ? 'selected' : ''; ?>>Dikonfirmasi</option>
                            <option value="cancelled" <?php echo ($status_filter === 'cancelled') ? 'selected' : ''; ?>>Dibatalkan</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="date">Tanggal</label>
                        <select name="date" id="date">
                            <option value="all" <?php echo ($date_filter === 'all') ? 'selected' : ''; ?>>Semua Tanggal</option>
                            <option value="today" <?php echo ($date_filter === 'today') ? 'selected' : ''; ?>>Hari Ini</option>
                            <option value="week" <?php echo ($date_filter === 'week') ? 'selected' : ''; ?>>7 Hari Terakhir</option>
                            <option value="month" <?php echo ($date_filter === 'month') ? 'selected' : ''; ?>>30 Hari Terakhir</option>
                        </select>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="filter-btn btn-filter">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="bookings.php" class="filter-btn btn-reset">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </form>
            </section>

            <!-- Bookings Grid -->
            <?php if (count($bookings) > 0): ?>
                <div class="bookings-grid">
                    <?php foreach ($bookings as $booking): ?>
                        <div class="booking-card">
                            <div class="booking-header">
                                <div class="booking-status status-<?php echo $booking['status']; ?>">
                                    <?php 
                                    switch($booking['status']) {
                                        case 'pending': echo 'Menunggu Konfirmasi'; break;
                                        case 'confirmed': echo 'Dikonfirmasi'; break;
                                        case 'cancelled': echo 'Dibatalkan'; break;
                                    }
                                    ?>
                                </div>
                                <div class="trip-info">
                                    <h4><?php echo htmlspecialchars($booking['trip_title']); ?></h4>
                                    <div class="mountain-name">
                                        <i class="fas fa-mountain"></i> <?php echo htmlspecialchars($booking['mountain_name']); ?>
                                    </div>
                                    <div class="trip-dates">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span><?php echo date('d M', strtotime($booking['start_date'])); ?> - <?php echo date('d M Y', strtotime($booking['end_date'])); ?></span>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="booking-content">
                                <div class="customer-section">
                                    <h5><i class="fas fa-user"></i> Informasi Pelanggan</h5>
                                    <div class="customer-details">
                                        <div>
                                            <i class="fas fa-user"></i>
                                            <span><?php echo htmlspecialchars($booking['user_name']); ?></span>
                                        </div>
                                        <div>
                                            <i class="fas fa-envelope"></i>
                                            <span><?php echo htmlspecialchars($booking['user_email']); ?></span>
                                        </div>
                                        <div>
                                            <i class="fas fa-phone"></i>
                                            <span><?php echo htmlspecialchars($booking['user_phone'] ?? 'Tidak tersedia'); ?></span>
                                        </div>
                                        <div>
                                            <i class="fas fa-calendar"></i>
                                            <span><?php echo date('d M Y', strtotime($booking['booking_date'])); ?></span>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="booking-details">
                                    
                                    <div class="detail-item">
                                        <span class="detail-label">Total Pembayaran</span>
                                        <span class="detail-value price-highlight">Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Metode Pembayaran</span>
                                        <span class="detail-value"><?php echo htmlspecialchars($booking['payment_method'] ?? 'Belum dipilih'); ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <span class="detail-label">Tanggal Booking</span>
                                        <span class="detail-value"><?php echo date('d M Y H:i', strtotime($booking['booking_date'])); ?></span>
                                    </div>
                                    <?php if ($booking['payment_proof']): ?>
                                        <div class="detail-item">
                                            <span class="detail-label">Bukti Pembayaran</span>
                                            <a href="../uploads/payments/<?= htmlspecialchars($booking['payment_proof']) ?>" target="_blank">
                                                <img src="../uploads/payments/<?= htmlspecialchars($booking['payment_proof']) ?>" alt="Bukti Pembayaran" style="max-width:120px; border-radius:8px; margin-top:8px;">
                                            </a>
                                        </div>
                                    <?php endif; ?>
                                </div>
                                
                                <div class="booking-actions">
                                    <?php if ($booking['status'] === 'pending' && $booking['payment_proof']): ?>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="verifikasi_pembayaran">
                                            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                            <button type="submit" class="action-btn btn-confirm"><i class="fas fa-check"></i> Verifikasi Pembayaran</button>
                                        </form>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="action" value="tolak_pembayaran">
                                            <input type="hidden" name="booking_id" value="<?= $booking['id'] ?>">
                                            <button type="submit" class="action-btn btn-cancel"><i class="fas fa-times"></i> Tolak Pembayaran</button>
                                        </form>
                                    <?php endif; ?>
                                    <a href="tel:<?php echo htmlspecialchars($booking['user_phone'] ?? ''); ?>" class="action-btn btn-contact">
                                        <i class="fas fa-phone"></i> Hubungi
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-clipboard-list"></i>
                    <h3>Belum Ada Pesanan</h3>
                    <p>Anda belum memiliki pesanan untuk trip pendakian. Pastikan trip Anda aktif dan menarik untuk mendapatkan pesanan.</p>
                    <a href="trips.php" class="filter-btn btn-filter">
                        <i class="fas fa-route"></i> Kelola Trip
                    </a>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Hidden form for status update -->
    <form id="statusForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="booking_id" id="statusBookingId">
        <input type="hidden" name="status" id="statusValue">
    </form>

    <script src="../assets/js/main.js"></script>
    <script>
        function updateBookingStatus(bookingId, newStatus) {
            const actionText = newStatus === 'confirmed' ? 'mengkonfirmasi' : 'menolak';
            
            if (confirm(`Apakah Anda yakin ingin ${actionText} pesanan ini?`)) {
                document.getElementById('statusBookingId').value = bookingId;
                document.getElementById('statusValue').value = newStatus;
                document.getElementById('statusForm').submit();
            }
        }

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);

        // Mobile responsive adjustments
        document.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth <= 767) {
                console.log('Mobile view detected for bookings page');
            }
        });
    </script>
</body>
</html>


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

// Get user statistics
$total_bookings = $conn->prepare("SELECT COUNT(*) AS total FROM bookings WHERE user_id = ?");
$total_bookings->bind_param("i", $user_id);
$total_bookings->execute();
$bookings_count = $total_bookings->get_result()->fetch_assoc()['total'];

// Total spent
$total_spent = $conn->prepare("SELECT COALESCE(SUM(total_price), 0) AS total FROM bookings WHERE user_id = ? AND status = 'confirmed'");
$total_spent->bind_param("i", $user_id);
$total_spent->execute();
$spent = $total_spent->get_result()->fetch_assoc()['total'];

// Completed trips
$completed_trips = $conn->prepare("
    SELECT COUNT(*) AS total 
    FROM bookings b 
    WHERE b.user_id = ? AND b.status = 'confirmed'
");
$completed_trips->bind_param("i", $user_id);
$completed_trips->execute();
$completed_count = $completed_trips->get_result()->fetch_assoc()['total'];

// Upcoming trips
$upcoming_trips = $conn->prepare("
    SELECT COUNT(*) AS total 
    FROM bookings b 
    WHERE b.user_id = ? AND b.status = 'pending'
");
$upcoming_trips->bind_param("i", $user_id);
$upcoming_trips->execute();
$upcoming_count = $upcoming_trips->get_result()->fetch_assoc()['total'];

// Get filters
$search = isset($_GET['search']) ? $_GET['search'] : '';
$mountain_filter = isset($_GET['mountain']) ? $_GET['mountain'] : '';
$price_filter = isset($_GET['price']) ? $_GET['price'] : '';

// Get featured trips (yang dibuat guide) - TRIPS REKOMENDASI
$featured_trips = $conn->query("
    SELECT t.*, m.name as mountain_name, m.height, m.location, 
           u.name as guide_name, COALESCE(g.rating, 0) as guide_rating, g.experience,
           (t.capacity - COALESCE((SELECT COUNT(*) FROM bookings b WHERE b.trip_id = t.id AND b.status IN ('confirmed', 'pending')), 0)) as available_spots
    FROM trips t 
    LEFT JOIN mountains m ON t.mountain_id = m.id 
    LEFT JOIN users u ON t.guide_id = u.id 
    LEFT JOIN guide g ON u.id = g.user_id
    
");

// Build query for mountain tickets (regular tickets)
$where_conditions = [];
$params = [];
$types = '';

$query = "
    SELECT mt.*, m.name as mountain_name, m.height, m.location, m.description
    FROM mountain_tickets mt 
    LEFT JOIN mountains m ON mt.mountain_id = m.id 
    WHERE mt.status = 'active' AND mt.type = 'regular'
";

if (!empty($search)) {
    $where_conditions[] = "(mt.title LIKE ? OR m.name LIKE ? OR m.location LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
    $types .= 'sss';
}

if (!empty($mountain_filter)) {
    $where_conditions[] = "m.id = ?";
    $params[] = $mountain_filter;
    $types .= 'i';
}

if (!empty($price_filter)) {
    switch ($price_filter) {
        case 'under_1m':
            $where_conditions[] = "mt.price < 1000000";
            break;
        case '1m_2m':
            $where_conditions[] = "mt.price BETWEEN 1000000 AND 2000000";
            break;
        case 'over_2m':
            $where_conditions[] = "mt.price > 2000000";
            break;
    }
}

if (!empty($where_conditions)) {
    $query .= " AND " . implode(" AND ", $where_conditions);
}

$query .= " ORDER BY mt.created_at DESC";

$stmt = $conn->prepare($query);
if (!$stmt) {
    die("Gagal prepare statement: " . $conn->error);
}
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$tickets_result = $stmt->get_result();

// Get all mountains for filter
$mountains = $conn->query("SELECT id, name FROM mountains ORDER BY name");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* [CSS yang sama seperti sebelumnya] */
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

        .user-welcome {
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            color: white;
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(46, 139, 87, 0.3);
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 15px;
        }

        .user-avatar {
            width: 70px;
            height: 70px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            border: 3px solid rgba(255,255,255,0.3);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 20px;
            border-left: 5px solid var(--accent-green);
        }

        .stat-card i {
            font-size: 2.5rem;
            color: var(--accent-green);
            width: 60px;
            height: 60px;
            background: var(--light-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .filters-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
        }

        .filters-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .search-box {
            position: relative;
            flex: 1;
            max-width: 400px;
        }

        .search-box input {
            width: 100%;
            padding: 12px 20px 12px 50px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
        }

        .search-box i {
            position: absolute;
            left: 18px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--light-text);
        }

        .filters-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .filter-group select {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 1rem;
            background: white;
        }

        .filter-btn {
            background: var(--accent-green);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 10px;
            font-weight: 600;
            cursor: pointer;
            align-self: end;
        }

        .trips-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
            gap: 25px;
        }

        .trip-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.1);
            transition: all 0.4s ease;
            position: relative;
        }

        .trip-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15);
        }

        .trip-image {
            height: 220px;
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            position: relative;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
            font-weight: bold;
        }

        .trip-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(255,255,255,0.95);
            color: var(--primary-green);
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            z-index: 2;
        }

        .trip-price {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--accent-green);
            color: white;
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 700;
            z-index: 2;
        }

        .trip-content {
            padding: 25px;
        }

        .trip-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark-text);
            margin: 0 0 10px 0;
        }

        .trip-mountain {
            color: var(--accent-green);
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .trip-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
            margin-bottom: 20px;
        }

        .detail-item {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.9rem;
            color: var(--light-text);
        }

        .trip-guide {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 15px;
            background: var(--light-green);
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .trip-guide-avatar {
            width: 40px;
            height: 40px;
            background: var(--accent-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
        }

        .trip-guide-info h5 {
            margin: 0 0 5px 0;
            color: var(--dark-text);
            font-weight: 600;
        }

        .guide-rating {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 0.8rem;
            color: var(--light-text);
        }

        .stars {
            color: #fbbf24;
        }

        .trip-actions {
            display: flex;
            gap: 12px;
        }

        .btn-book {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            color: white;
            border: none;
            padding: 15px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-book:hover {
            background: linear-gradient(135deg, var(--secondary-green), var(--primary-green));
            transform: translateY(-2px);
        }

        .btn-cart {
            background: transparent;
            color: var(--accent-green);
            border: 2px solid var(--accent-green);
            padding: 15px 20px;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-cart:hover {
            background: var(--accent-green);
            color: white;
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
        }

        .section-header h3 {
            font-size: 1.5rem;
            color: var(--dark-text);
            margin: 0;
        }

        .featured-badge {
            background: linear-gradient(135deg, #fbbf24, #f59e0b);
            color: white;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .availability-indicator {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-top: 10px;
        }

        .availability-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
        }

        .availability-dot.available {
            background: var(--accent-green);
        }

        .availability-dot.limited {
            background: #f59e0b;
        }

        .availability-dot.full {
            background: #ef4444;
        }

        /* Package badge styling */
        .package-badge {
            background: linear-gradient(135deg, #8b5cf6, #a855f7);
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .regular-badge {
            background: linear-gradient(135deg, #06b6d4, #0891b2);
            color: white;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">
                <i class="fas fa-mountain"></i>
                Lombok Hiking
            </div>
            <nav>
                <a href="dashboard.php" class="active">
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
                
                <a href="status_pembayaran.php">
                    <i class="fas fa-credit-card"></i> 
                    Status Pembayaran
                </a>
                <a href="paket_saya.php">
                    <i class="fas fa-hiking"></i> 
                    Paket Saya
                </a>
                <a href="ajukan_guide.php">
                    <i class="fas fa-user-plus"></i> 
                    Ajukan Diri Jadi Guide
                </a>
                <a href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i> 
                    Logout
                </a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main">
            <!-- Welcome Section -->
            <div class="user-welcome">
                <div class="user-info">
                    <div class="user-avatar">
                        <i class="fas fa-user-circle"></i>
                    </div>
                    <div class="user-details">
                        <h2>Halo, <?php echo htmlspecialchars($userName); ?>!</h2>
                        <p>Selamat datang di Lombok Hiking - Jelajahi keindahan alam Lombok</p>
                    </div>
                </div>
                <p>Temukan petualangan hiking terbaik dengan guide berpengalaman dan nikmati keindahan gunung-gunung eksotis di Lombok!</p>
            </div>

            <!-- Statistics Grid -->
            <section class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-calendar-check"></i>
                    <div>
                        <h3>Total Booking</h3>
                        <p><?= $bookings_count ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <i class="fas fa-money-bill-wave"></i>
                    <div>
                        <h3>Total Pengeluaran</h3>
                        <p>Rp <?= number_format($spent, 0, ',', '.') ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <i class="fas fa-mountain"></i>
                    <div>
                        <h3>Trip Selesai</h3>
                        <p><?= $completed_count ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <div>
                        <h3>Trip Mendatang</h3>
                        <p><?= $upcoming_count ?></p>
                    </div>
                </div>
            </section>

            <!-- Featured Trips Section (PAKET DARI GUIDE) -->
            <?php if ($featured_trips && $featured_trips->num_rows > 0): ?>
            <section class="featured-section">
                <div class="section-header">
                    <h3>
                        <i class="fas fa-star"></i>
                        Paket Trip Unggulan
                    </h3>
                    <span class="featured-badge">Rekomendasi Guide</span>
                </div>
                
                <div class="trips-grid">
                    <?php while ($trip = $featured_trips->fetch_assoc()): ?>
                        <div class="trip-card">
                            <div class="trip-image">
                                <img src="<?= $trip['image'] ? '../uploads/trips/' . $trip['image'] : 'https://placehold.co/400x220/2e8b57/ffffff?text=' . urlencode($trip['mountain_name'] ?? 'Gunung') ?>" 
                                     alt="<?= htmlspecialchars($trip['mountain_name'] ?? 'Gunung Lombok') ?>" 
                                     style="width: 100%; height: 100%; object-fit: cover;"
                                     onerror="this.style.display='none'; this.parentElement.innerHTML='<?= htmlspecialchars($trip['mountain_name'] ?? 'Gunung Lombok') ?>';">
                                <div class="trip-badge package-badge">Paket Guide</div>
                                <div class="trip-price">Rp <?= number_format($trip['package_price'], 0, ',', '.') ?></div>
                            </div>
                            
                            <div class="trip-content">
                                <h4 class="trip-title"><?= htmlspecialchars($trip['title']) ?></h4>
                                <div class="trip-mountain">
                                    <i class="fas fa-mountain"></i>
                                    <?= htmlspecialchars($trip['mountain_name'] ?? 'Lombok') ?>
                                </div>
                                
                                <div class="trip-details">
                                    <div class="detail-item">
                                        <i class="fas fa-calendar-alt"></i>
                                        <span><?= date_diff(date_create($trip['start_date']), date_create($trip['end_date']))->days + 1 ?> hari</span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-users"></i>
                                        <span><?= $trip['available_spots'] ?> slot tersisa</span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-calendar-day"></i>
                                        <span><?= date('d M Y', strtotime($trip['start_date'])) ?></span>
                                    </div>
                                    <div class="detail-item">
                                        <i class="fas fa-ruler-vertical"></i>
                                        <span><?= number_format($trip['height'] ?? 0) ?> mdpl</span>
                                    </div>
                                </div>

                                <div class="availability-indicator">
                                    <?php 
                                    $available_spots = $trip['available_spots'];
                                    if ($available_spots > 5) {
                                        echo '<div class="availability-dot available"></div><span>Tersedia</span>';
                                    } elseif ($available_spots > 0) {
                                        echo '<div class="availability-dot limited"></div><span>Terbatas</span>';
                                    } else {
                                        echo '<div class="availability-dot full"></div><span>Penuh</span>';
                                    }
                                    ?>
                                </div>

                                <div class="trip-guide">
                                    <div class="trip-guide-avatar">
                                        <?= strtoupper(substr($trip['guide_name'] ?? 'G', 0, 2)) ?>
                                    </div>
                                    <div class="trip-guide-info">
                                        <h5>Guide: <?= htmlspecialchars($trip['guide_name'] ?? 'Guide') ?></h5>
                                        <div class="guide-rating">
                                            <span class="stars">
                                                <?php 
                                                $rating = $trip['guide_rating'] ?? 0;
                                                for ($i = 1; $i <= 5; $i++) {
                                                    echo $i <= $rating ? '★' : '☆';
                                                }
                                                ?>
                                            </span>
                                            <span><?= number_format($trip['guide_rating'] ?? 0, 1) ?>/5.0</span>
                                            <span>• <?= $trip['experience'] ?? '0' ?> tahun</span>
                                        </div>
                                    </div>
                                </div>

                                <div class="trip-actions">
                                    <?php if ($available_spots > 0): ?>
                                        <button class="btn-book" onclick="bookPackageTrip(<?= $trip['id'] ?>)">
                                            <i class="fas fa-credit-card"></i>
                                            Book & Bayar
                                        </button>
                                        <button class="btn-cart" onclick="addTripToCart(<?= $trip['id'] ?>)">
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-book" disabled style="opacity: 0.5; cursor: not-allowed;">
                                            <i class="fas fa-ban"></i>
                                            Trip Penuh
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </div>
            </section>
            <?php endif; ?>

            <!-- Filters Section -->
            <div class="filters-section">
                <div class="filters-header">
                    <h3>
                        <i class="fas fa-filter"></i>
                        Cari Tiket Reguler
                    </h3>
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Cari berdasarkan nama tiket, gunung, atau lokasi..." 
                               value="<?= htmlspecialchars($search) ?>" id="searchInput">
                    </div>
                </div>

                <form method="GET" action="dashboard.php" id="filterForm">
                    <div class="filters-grid">
                        <div class="filter-group">
                            <label for="mountain">Pilih Gunung</label>
                            <select name="mountain" id="mountain">
                                <option value="">Semua Gunung</option>
                                <?php 
                                if ($mountains) {
                                    while ($mountain = $mountains->fetch_assoc()): 
                                ?>
                                    <option value="<?= $mountain['id'] ?>" 
                                            <?= $mountain_filter == $mountain['id'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($mountain['name']) ?>
                                    </option>
                                <?php 
                                    endwhile; 
                                }
                                ?>
                            </select>
                        </div>

                        <div class="filter-group">
                            <label for="price">Rentang Harga</label>
                            <select name="price" id="price">
                                <option value="">Semua Harga</option>
                                <option value="under_1m" <?= $price_filter == 'under_1m' ? 'selected' : '' ?>>Di bawah 1 Juta</option>
                                <option value="1m_2m" <?= $price_filter == '1m_2m' ? 'selected' : '' ?>>1 - 2 Juta</option>
                                <option value="over_2m" <?= $price_filter == 'over_2m' ? 'selected' : '' ?>>Di atas 2 Juta</option>
                            </select>
                        </div>

                        <button type="submit" class="filter-btn">
                            <i class="fas fa-search"></i>
                            Cari Tiket
                        </button>
                    </div>
                </form>
            </div>

            <!-- Regular Tickets Section -->
            <section>
                <div class="section-header">
                    <h3>
                        <i class="fas fa-ticket-alt"></i>
                        Tiket Reguler Tersedia
                    </h3>
                </div>
                
                <?php if ($tickets_result && $tickets_result->num_rows > 0): ?>
                    <div class="trips-grid">
                        <?php while ($ticket = $tickets_result->fetch_assoc()): ?>
                            <div class="trip-card">
                                <div class="trip-image">
                                    <img src="https://placehold.co/400x220/0891b2/ffffff?text=<?= urlencode($ticket['mountain_name'] ?? 'Gunung') ?>" 
                                         alt="<?= htmlspecialchars($ticket['mountain_name'] ?? 'Gunung Lombok') ?>" 
                                         style="width: 100%; height: 100%; object-fit: cover;"
                                         onerror="this.style.display='none'; this.parentElement.innerHTML='<?= htmlspecialchars($ticket['mountain_name'] ?? 'Gunung Lombok') ?>';">
                                    <div class="trip-badge regular-badge">Tiket Reguler</div>
                                                                        <div class="trip-price">Rp <?= number_format($ticket['price'], 0, ',', '.') ?></div>
                                </div>
                                
                                <div class="trip-content">
                                    <h4 class="trip-title"><?= htmlspecialchars($ticket['title']) ?></h4>
                                    <div class="trip-mountain">
                                        <i class="fas fa-mountain"></i>
                                        <?= htmlspecialchars($ticket['mountain_name'] ?? 'Lombok') ?> - <?= htmlspecialchars($ticket['location'] ?? 'Lombok') ?>
                                    </div>
                                    
                                    <div class="trip-details">
                                        <div class="detail-item">
                                            <i class="fas fa-tag"></i>
                                            <span><?= ucfirst($ticket['type']) ?></span>
                                        </div>
                                        <div class="detail-item">
                                            <i class="fas fa-map-marker-alt"></i>
                                            <span><?= htmlspecialchars($ticket['location'] ?? 'Lombok') ?></span>
                                        </div>
                                        <?php if ($ticket['height']): ?>
                                        <div class="detail-item">
                                            <i class="fas fa-ruler-vertical"></i>
                                            <span><?= number_format($ticket['height']) ?> mdpl</span>
                                        </div>
                                        <?php endif; ?>
                                        <div class="detail-item">
                                            <i class="fas fa-calendar-plus"></i>
                                            <span><?= date('d M Y', strtotime($ticket['created_at'])) ?></span>
                                        </div>
                                    </div>

                                    <?php if ($ticket['description']): ?>
                                    <div style="margin-bottom: 15px; padding: 10px; background: #f8f9fa; border-radius: 8px; font-size: 0.9rem; color: var(--light-text);">
                                        <?= htmlspecialchars(substr($ticket['description'], 0, 100)) ?><?= strlen($ticket['description']) > 100 ? '...' : '' ?>
                                    </div>
                                    <?php endif; ?>

                                    <div class="trip-actions">
                                        <button class="btn-book" onclick="bookRegularTicket(<?= $ticket['id'] ?>)">
                                            <i class="fas fa-hiking"></i>
                                            Book Sekarang
                                        </button>
                                        <button class="btn-cart" onclick="addTicketToCart(<?= $ticket['id'] ?>)">
                                            <i class="fas fa-cart-plus"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div style="text-align: center; padding: 60px 20px; background: white; border-radius: 15px;">
                        <i class="fas fa-search" style="font-size: 3rem; color: var(--light-text); margin-bottom: 20px;"></i>
                        <h3 style="color: var(--dark-text); margin-bottom: 10px;">Tiket tidak ditemukan</h3>
                        <p style="color: var(--light-text);">Coba ubah filter pencarian atau kata kunci Anda</p>
                        <button onclick="resetFilters()" style="margin-top: 20px; background: var(--accent-green); color: white; border: none; padding: 12px 24px; border-radius: 10px; cursor: pointer;">
                            <i class="fas fa-redo"></i> Reset Filter
                        </button>
                    </div>
                <?php endif; ?>
            </section>
        </main>
    </div>

    <script>
        // Search functionality
        document.getElementById('searchInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const form = document.getElementById('filterForm');
                const searchInput = document.createElement('input');
                searchInput.type = 'hidden';
                searchInput.name = 'search';
                searchInput.value = this.value;
                form.appendChild(searchInput);
                form.submit();
            }
        });

        // Function untuk booking paket trip (langsung ke pembayaran)
        function bookPackageTrip(tripId) {
            // Konfirmasi dulu
            if (confirm('Anda akan melakukan booking paket trip ini. Lanjutkan ke pembayaran?')) {
                // Redirect langsung ke halaman pembayaran untuk paket
                window.location.href = 'pembayaran.php?type=package&trip_id=' + tripId;
            }
        }

        // Function untuk booking tiket reguler (ke halaman booking biasa)
        function bookRegularTicket(ticketId) {
            // Redirect ke halaman booking untuk tiket reguler
            window.location.href = 'booking.php?ticket_id=' + ticketId;
        }

        // Function untuk add trip paket ke cart
        function addTripToCart(tripId) {
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    trip_id: tripId,
                    type: 'package',
                    action: 'add_to_cart'
                })
            })
            .then(response => response.json())
            
        }

        // Function untuk add tiket reguler ke cart
        function addTicketToCart(ticketId) {
            fetch('add_to_cart.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ 
                    ticket_id: ticketId,
                    type: 'regular',
                    action: 'add_to_cart'
                })
            })
            .then(response => response.json())
            
        }

        // Function untuk update cart counter (optional)
        function updateCartCounter() {
            fetch('get_cart_count.php')
            .then(response => response.json())
            .then(data => {
                // Update cart counter di UI jika ada
                const cartCounter = document.querySelector('.cart-counter');
                if (cartCounter && data.count) {
                    cartCounter.textContent = data.count;
                    cartCounter.style.display = data.count > 0 ? 'block' : 'none';
                }
            })
            .catch(error => {
                console.error('Error updating cart counter:', error);
            });
        }

        // Function untuk reset filters
        function resetFilters() {
            window.location.href = 'dashboard.php';
        }

        // Mobile responsive handling
        document.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth <= 767) {
                console.log('Mobile view active');
                // Add mobile-specific interactions if needed
            }
            
            // Load cart counter on page load
            updateCartCounter();
        });

        // Auto-suggest search (optional enhancement)
        document.getElementById('searchInput').addEventListener('input', function() {
            // Could implement auto-complete suggestions here
            const searchTerm = this.value.toLowerCase();
            if (searchTerm.length > 2) {
                // Implement search suggestions
                console.log('Searching for:', searchTerm);
            }
        });

        // Debug function
        console.log('Dashboard JavaScript loaded successfully');
        
        // Add smooth scrolling for better UX
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

        // Add loading states for buttons
        function addLoadingState(button) {
            const originalText = button.innerHTML;
            button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Loading...';
            button.disabled = true;
            
            // Remove loading state after 3 seconds (fallback)
            setTimeout(() => {
                button.innerHTML = originalText;
                button.disabled = false;
            }, 3000);
        }

        // Enhanced error handling
        window.addEventListener('error', function(e) {
            console.error('JavaScript Error:', e.error);
        });

        // Add keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + K untuk focus search
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                document.getElementById('searchInput').focus();
            }
        });
    </script>
</body>
</html>

                                    
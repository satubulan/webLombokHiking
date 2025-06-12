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
$guide_query = $conn->prepare("SELECT * FROM guides WHERE user_id = ?");
$guide_query->bind_param("s", $user_id);
$guide_query->execute();
$guide_result = $guide_query->get_result();
$guide_info = $guide_result->fetch_assoc();

// Get guide statistics
$guide_id = $guide_info['id'] ?? null;

// Total trips that this guide leads
$total_trips = $conn->prepare("SELECT COUNT(*) AS total FROM trips WHERE guide_id = ?");
$total_trips->bind_param("s", $guide_id);
$total_trips->execute();
$trips_count = $total_trips->get_result()->fetch_assoc()['total'];

// Total bookings for this guide's trips
$total_bookings = $conn->prepare("
    SELECT COUNT(*) AS total 
    FROM bookings b 
    JOIN trips t ON b.trip_id = t.id 
    WHERE t.guide_id = ?
");
$total_bookings->bind_param("s", $guide_id);
$total_bookings->execute();
$bookings_count = $total_bookings->get_result()->fetch_assoc()['total'];

// Total earnings (confirmed bookings only)
$total_earnings = $conn->prepare("
    SELECT COALESCE(SUM(b.total_price), 0) AS total 
    FROM bookings b 
    JOIN trips t ON b.trip_id = t.id 
    WHERE t.guide_id = ? AND b.status = 'confirmed'
");
$total_earnings->bind_param("s", $guide_id);
$total_earnings->execute();
$earnings = $total_earnings->get_result()->fetch_assoc()['total'];

// Active trips (future trips)
$active_trips = $conn->prepare("
    SELECT COUNT(*) AS total 
    FROM trips 
    WHERE guide_id = ? AND end_date >= CURDATE()
");
$active_trips->bind_param("s", $guide_id);
$active_trips->execute();
$active_trips_count = $active_trips->get_result()->fetch_assoc()['total'];

// Pending bookings
$pending_bookings = $conn->prepare("
    SELECT COUNT(*) AS total 
    FROM bookings b 
    JOIN trips t ON b.trip_id = t.id 
    WHERE t.guide_id = ? AND b.status = 'pending'
");
$pending_bookings->bind_param("s", $guide_id);
$pending_bookings->execute();
$pending_count = $pending_bookings->get_result()->fetch_assoc()['total'];

$guide_rating = $guide_info['rating'] ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Guide - Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Additional styles for guide dashboard */
        .guide-welcome {
            background: linear-gradient(135deg, #2e8b57 0%, #3cb371 100%);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(46, 139, 87, 0.3);
        }
        
        .guide-info {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .guide-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
        }
        
        .guide-details h2 {
            margin: 0 0 5px 0;
            font-size: 1.8rem;
        }
        
        .guide-rating {
            display: flex;
            align-items: center;
            gap: 10px;
            margin: 10px 0;
        }
        
        .rating-stars {
            color: #ffd700;
        }
        
        .recent-activity {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
            margin-top: 30px;
        }
        
        .activity-item {
            padding: 15px;
            border-left: 4px solid #2e8b57;
            margin-bottom: 15px;
            background: #f8f9fa;
            border-radius: 0 8px 8px 0;
        }
        
        .stat-card.guide-card {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
            border: 1px solid #e9ecef;
            position: relative;
            overflow: hidden;
        }
        
        .stat-card.guide-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(135deg, #2e8b57, #3cb371);
        }
        
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 30px;
        }
        
        .action-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 20px;
            background: linear-gradient(135deg, #2e8b57, #3cb371);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(46, 139, 87, 0.3);
        }
        
        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(46, 139, 87, 0.4);
            color: white;
        }

        /* Mobile Responsive */
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
            
            .guide-info {
                flex-direction: column;
                text-align: center;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 15px;
            }
            
            .quick-actions {
                grid-template-columns: 1fr;
                gap: 15px;
            }
        }
        
        /* Tablet */
        @media (min-width: 768px) and (max-width: 1023px) {
            .sidebar {
                width: 200px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            }
        }
        
        /* Large Desktop */
        @media (min-width: 1440px) {
            .container {
                max-width: 1400px;
                margin: 0 auto;
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
                <a href="dashboard.php" class="active"><i class="fas fa-chart-line"></i> Dashboard</a>
                <a href="profile.php"><i class="fas fa-user-edit"></i> Profile</a>
                <a href="trips.php"><i class="fas fa-route"></i> Trip Saya</a>
                <a href="bookings.php"><i class="fas fa-calendar-check"></i> Pesanan</a>
                <a href="schedule.php"><i class="fas fa-calendar-alt"></i> Jadwal</a>
                <a href="notifications.php"><i class="fas fa-bell"></i> Notifikasi</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main">
            <!-- Welcome Section -->
            <div class="guide-welcome">
                <div class="guide-info">
                    <div class="guide-avatar">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <div class="guide-details">
                        <h2>Selamat Datang, <?php echo htmlspecialchars($userName); ?>!</h2>
                        <p>Guide Pendakian Lombok Hiking</p>
                        <?php if ($guide_info): ?>
                            <div class="guide-rating">
                                <span class="rating-stars">
                                    <?php 
                                    $rating = $guide_rating;
                                    for ($i = 1; $i <= 5; $i++) {
                                        echo $i <= $rating ? '<i class="fas fa-star"></i>' : '<i class="far fa-star"></i>';
                                    }
                                    ?>
                                </span>
                                <span><?php echo number_format($guide_rating, 1); ?>/5.0</span>
                                <span class="experience">• <?php echo $guide_info['experience'] ?? 0; ?> tahun pengalaman</span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <p>Kelola trip pendakian Anda dan berikan pengalaman terbaik untuk para pendaki!</p>
            </div>

            <!-- Statistics Grid -->
            <section class="stats-grid">
                <div class="stat-card guide-card">
                    <i class="fas fa-route"></i>
                    <div>
                        <h3>Total Trip</h3>
                        <p><?= $trips_count ?></p>
                    </div>
                </div>

                <div class="stat-card guide-card">
                    <i class="fas fa-calendar-check"></i>
                    <div>
                        <h3>Total Peserta</h3>
                        <p><?= $bookings_count ?></p>
                    </div>
                </div>

                <div class="stat-card guide-card">
                    <i class="fas fa-money-bill-wave"></i>
                    <div>
                        <h3>Pendapatan</h3>
                        <p>Rp <?= number_format($earnings, 0, ',', '.') ?></p>
                    </div>
                </div>

                <div class="stat-card guide-card">
                    <i class="fas fa-play-circle"></i>
                    <div>
                        <h3>Trip Aktif</h3>
                        <p><?= $active_trips_count ?></p>
                    </div>
                </div>

                <div class="stat-card guide-card">
                    <i class="fas fa-star"></i>
                    <div>
                        <h3>Rating</h3>
                        <p><?= number_format($guide_rating, 1) ?>/5.0</p>
                    </div>
                </div>

                <div class="stat-card guide-card">
                    <i class="fas fa-clock"></i>
                    <div>
                        <h3>Pesanan Pending</h3>
                        <p><?= $pending_count ?></p>
                    </div>
                </div>
            </section>

            <!-- Quick Actions -->
            <section class="quick-actions">
                <a href="profile.php" class="action-btn">
                    <i class="fas fa-user-edit"></i>
                    <span>Update Profile</span>
                </a>
                <a href="trips.php" class="action-btn">
                    <i class="fas fa-route"></i>
                    <span>Kelola Trip</span>
                </a>
                <a href="schedule.php" class="action-btn">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Lihat Jadwal</span>
                </a>
                <a href="notifications.php" class="action-btn">
                    <i class="fas fa-bell"></i>
                    <span>Notifikasi</span>
                </a>
            </section>

            <!-- Recent Activity -->
            <?php
            // Get recent bookings for this guide
            $recent_bookings = $conn->prepare("
                SELECT b.*, t.title as trip_title, u.name as user_name, t.start_date
                FROM bookings b 
                JOIN trips t ON b.trip_id = t.id 
                JOIN users u ON b.user_id = u.id 
                WHERE t.guide_id = ? 
                ORDER BY b.booking_date DESC 
                LIMIT 5
            ");
            $recent_bookings->bind_param("s", $guide_id);
            $recent_bookings->execute();
            $recent_result = $recent_bookings->get_result();
            ?>

            <div class="recent-activity">
                <h3><i class="fas fa-history"></i> Aktivitas Terbaru</h3>
                
                <?php if ($recent_result->num_rows > 0): ?>
                    <?php while ($booking = $recent_result->fetch_assoc()): ?>
                        <div class="activity-item">
                            <h4><?= htmlspecialchars($booking['trip_title']) ?></h4>
                            <p><strong>Peserta:</strong> <?= htmlspecialchars($booking['user_name']) ?></p>
                            <p><strong>Tanggal Trip:</strong> <?= date('d M Y', strtotime($booking['start_date'])) ?></p>
                            <p><strong>Status:</strong> 
                                <span class="status-<?= $booking['status'] ?>">
                                    <?= ucfirst($booking['status']) ?>
                                </span>
                            </p>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="activity-item">
                        <p style="text-align: center; color: #666; font-style: italic;">
                            <i class="fas fa-info-circle"></i> Belum ada aktivitas terbaru
                        </p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Mobile menu toggle functionality
        document.addEventListener('DOMContentLoaded', function() {
            const sidebar = document.querySelector('.sidebar');
            const main = document.querySelector('.main');
            
            // Add mobile menu toggle if needed
            if (window.innerWidth <= 767) {
                // Mobile-specific interactions can be added here
                console.log('Mobile view detected');
            }
            
            // Update stats periodically (optional)
            setInterval(function() {
                // Could add AJAX calls to update stats in real-time
            }, 300000); // Every 5 minutes
        });
    </script>
</body>
</html>


<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is a guide
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'guide') {
    header('Location: ../views/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Get guide info
$guide_query = $conn->prepare("SELECT * FROM guide WHERE user_id = ?");
$guide_query->bind_param("i", $user_id); // Changed to 'i' assuming user_id is int
$guide_query->execute();
$guide_result = $guide_query->get_result();
$guide_info = $guide_result->fetch_assoc();
$guide_id = $guide_info['id'] ?? null;

if (!$guide_id) {
    header('Location: dashboard.php?error=' . urlencode('Profile guide tidak ditemukan'));
    exit();
}

$trip_id = $_GET['id'] ?? null;

if (!$trip_id) {
    header('Location: trips.php');
    exit();
}

// Get trip details with mountain name and mountain ticket info
$trip_query = $conn->prepare("
    SELECT 
        t.*, 
        m.name as mountain_name,
        mt.title as mountain_ticket_title,
        mt.price as mountain_ticket_price
    FROM trips t
    LEFT JOIN mountains m ON t.mountain_id = m.id
    LEFT JOIN mountain_tickets mt ON t.mountain_ticket_id = mt.id
    WHERE t.id = ? AND t.guide_id = ?
");
$trip_query->bind_param("ii", $trip_id, $guide_id); // Changed to 'ii' assuming trip_id and guide_id are int
$trip_query->execute();
$trip = $trip_query->get_result()->fetch_assoc();

// Check if trip exists
if (!$trip) {
    header('Location: trips.php?error=' . urlencode('Trip tidak ditemukan'));
    exit();
}

// Calculate duration
$start_date_obj = new DateTime($trip['start_date']);
$end_date_obj = new DateTime($trip['end_date']);
$duration = $start_date_obj->diff($end_date_obj)->days + 1;

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Trip - Guide Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Trip Detail Styles - Konsisten dengan trips.php */
        .trip-detail-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .detail-header {
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 20px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .detail-title {
            color: #2e8b57;
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: 700;
        }
        
        .detail-subtitle {
            color: #6c757d;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }
        
        .detail-image {
            width: 100%;
            height: 300px;
            border-radius: 15px;
            overflow: hidden;
            margin-bottom: 30px;
            background: linear-gradient(135deg, rgba(46, 139, 87, 0.8), rgba(60, 179, 113, 0.8));
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 1.2rem;
        }
        
        .detail-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .detail-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .detail-item {
            padding: 20px;
            background: #f8f9fa;
            border-radius: 12px;
            border-left: 4px solid #2e8b57;
            transition: all 0.3s ease;
        }
        
        .detail-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }
        
        .detail-item strong {
            color: #2c3e50;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            font-size: 1rem;
        }
        
        .detail-item strong i {
            color: #2e8b57;
            width: 20px;
        }
        
        .detail-value {
            font-size: 1.1rem;
            color: #495057;
            font-weight: 600;
        }
        
        .detail-description {
            grid-column: 1 / -1;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 12px;
            line-height: 1.7;
            margin-bottom: 20px;
        }
        
        .detail-description h4 {
            color: #2e8b57;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 1.2rem;
        }
        
        .detail-description h4 i {
            color: #2e8b57;
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-top: 30px;
            flex-wrap: wrap;
        }
        
        .btn-action {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            cursor: pointer;
            min-width: 150px;
            justify-content: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #2e8b57, #3cb371);
            color: white;
            box-shadow: 0 4px 15px rgba(46, 139, 87, 0.3);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 139, 87, 0.4);
            color: white;
        }
        
        .btn-warning {
            background: linear-gradient(135deg, #ffc107, #ffcd39);
            color: #212529;
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
        }
        
        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 193, 7, 0.4);
            color: #212529;
        }
        
        .price-highlight {
            background: linear-gradient(135deg, #e67e22, #f39c12);
            color: white;
            padding: 15px;
            border-radius: 12px;
            text-align: center;
            font-size: 1.4rem;
            font-weight: 700;
            box-shadow: 0 4px 15px rgba(230, 126, 34, 0.3);
        }
        
        /* Mobile Responsive */
        @media (max-width: 767px) {
            .main {
                padding: 20px;
            }
            
            .trip-detail-container {
                margin: 0;
                padding: 20px;
                border-radius: 10px;
            }
            
            .detail-title {
                font-size: 1.8rem;
            }
            
            .detail-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .action-buttons {
                flex-direction: column;
                align-items: stretch;
            }
            
            .btn-action {
                min-width: auto;
            }
        }
        
        /* Desktop Large */
        @media (min-width: 1440px) {
            .main {
                max-width: 1400px;
                margin: 0 auto;
                padding: 30px 40px;
            }
            
            .detail-grid {
                grid-template-columns: repeat(3, 1fr);
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
                <a href="trips.php" class="active"><i class="fas fa-route"></i> Trip Saya</a>
                <a href="bookings.php"><i class="fas fa-calendar-check"></i> Pesanan</a>
                <a href="schedule.php"><i class="fas fa-calendar-alt"></i> Jadwal</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main">
            <div class="trip-detail-container">
                <div class="detail-header">
                    <h1 class="detail-title"><?php echo htmlspecialchars($trip['title']); ?></h1>
                    <p class="detail-subtitle">
                        <i class="fas fa-mountain"></i>
                        <span><?php echo htmlspecialchars($trip['mountain_name'] ?? 'Gunung tidak diketahui'); ?></span>
                    </p>
                </div>

                <div class="detail-image">
                    <?php if (!empty($trip['image']) && file_exists('../' . $trip['image'])): ?>
                        <img src="../<?php echo htmlspecialchars($trip['image']); ?>" 
                             alt="<?php echo htmlspecialchars($trip['title']); ?>">
                    <?php else: ?>
                        <div style="text-align: center;">
                            <i class="fas fa-image" style="font-size: 4rem;"></i>
                            <p>Gambar tidak tersedia</p>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="detail-grid">
                    <div class="detail-item">
                        <strong><i class="fas fa-calendar-alt"></i> Tanggal Mulai</strong>
                        <div class="detail-value"><?php echo date('d M Y', strtotime($trip['start_date'])); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <strong><i class="fas fa-calendar-check"></i> Tanggal Selesai</strong>
                        <div class="detail-value"><?php echo date('d M Y', strtotime($trip['end_date'])); ?></div>
                    </div>
                    
                    <div class="detail-item">
                        <strong><i class="fas fa-clock"></i> Durasi</strong>
                        <div class="detail-value"><?php echo $duration; ?> hari</div>
                    </div>
                    
                    <div class="detail-item">
                        <strong><i class="fas fa-users"></i> Maksimal Peserta</strong>
                        <div class="detail-value"><?php echo $trip['capacity']; ?> orang</div>
                    </div>

                    <div class="detail-item">
                        <strong><i class="fas fa-tag"></i> Jenis Trip</strong>
                        <div class="detail-value"><?php echo ucfirst($trip['type']); ?></div>
                    </div>
                    
                    <div class="detail-item price-highlight">
                        <strong><i class="fas fa-money-bill-wave"></i> Harga per Orang</strong>
                        <div>Rp <?php echo number_format($trip['package_price'], 0, ',', '.'); ?></div>
                    </div>

                    <?php if ($trip['type'] === 'package' && !empty($trip['mountain_ticket_id'])): ?>
                        <div class="detail-item">
                            <strong><i class="fas fa-ticket-alt"></i> Tiket Gunung Termasuk</strong>
                            <div class="detail-value">
                                <?php echo htmlspecialchars($trip['mountain_ticket_title']); ?> 
                                (Rp <?php echo number_format($trip['mountain_ticket_price'], 0, ',', '.'); ?>)
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <div class="detail-description">
                    <h4><i class="fas fa-info-circle"></i> Deskripsi Trip</h4>
                    <p><?php echo nl2br(htmlspecialchars($trip['description'])); ?></p>
                </div>
                
                <div class="detail-description">
                    <h4><i class="fas fa-check-circle"></i> Fasilitas Termasuk</h4>
                    <p><?php echo nl2br(htmlspecialchars($trip['facilities'])); ?></p>
                </div>
                
                <div class="action-buttons">
                    <a href="trips.php" class="btn-action btn-primary">
                        <i class="fas fa-arrow-left"></i>
                        Kembali ke Trip Saya
                    </a>
                    <a href="edit_trip.php?id=<?php echo $trip['id']; ?>" class="btn-action btn-warning">
                        <i class="fas fa-edit"></i>
                        Edit Trip
                    </a>
                </div>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Mobile responsive adjustments
        document.addEventListener('DOMContentLoaded', function() {
            // Handle responsive behavior
            function handleResize() {
                const sidebar = document.querySelector('.sidebar');
                const main = document.querySelector('.main');
                
                if (window.innerWidth <= 767) {
                    // Mobile behavior
                    console.log('Mobile view detected for trip detail');
                } else {
                    // Desktop behavior
                    console.log('Desktop view detected for trip detail');
                }
            }
            
            // Initial call
            handleResize();
            
            // Listen for resize events
            window.addEventListener('resize', handleResize);
        });
    </script>
</body>
</html>

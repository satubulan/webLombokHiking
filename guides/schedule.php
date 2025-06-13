
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
$guide_id = $guide_info['id'] ?? null;

$message = '';
$error = '';

// Get filter parameters
$month_filter = $_GET['month'] ?? date('Y-m');
$status_filter = $_GET['status'] ?? 'all';

// Build query conditions
$where_conditions = ["t.guide_id = ?"];
$params = [$guide_id];
$param_types = "s";

if ($status_filter !== 'all') {
    if ($status_filter === 'upcoming') {
        $where_conditions[] = "t.start_date >= CURDATE()";
    } elseif ($status_filter === 'ongoing') {
        $where_conditions[] = "t.start_date <= CURDATE() AND t.end_date >= CURDATE()";
    } elseif ($status_filter === 'completed') {
        $where_conditions[] = "t.end_date < CURDATE()";
    }
}

// Filter by month if specified
if ($month_filter) {
    $where_conditions[] = "DATE_FORMAT(t.start_date, '%Y-%m') = ?";
    $params[] = $month_filter;
    $param_types .= "s";
}

$where_clause = implode(" AND ", $where_conditions);

// Get trips/schedules for this guide
$schedules_query = $conn->prepare("
    SELECT 
        t.*,
        m.name as mountain_name,
        m.height as mountain_height,
        (SELECT COUNT(*) FROM bookings b WHERE b.trip_id = t.id) as total_bookings,
        (SELECT COUNT(*) FROM bookings b WHERE b.trip_id = t.id AND b.status = 'confirmed') as confirmed_bookings
    FROM trips t 
    LEFT JOIN mountains m ON t.mountain_id = m.id 
    WHERE {$where_clause}
    ORDER BY t.start_date ASC, t.created_at DESC
");

if (!empty($params)) {
    $schedules_query->bind_param($param_types, ...$params);
}

$schedules_query->execute();
$schedules = $schedules_query->get_result()->fetch_all(MYSQLI_ASSOC);

// Get statistics
$stats_query = $conn->prepare("
    SELECT 
        COUNT(*) as total_trips,
        SUM(CASE WHEN t.start_date >= CURDATE() THEN 1 ELSE 0 END) as upcoming_trips,
        SUM(CASE WHEN t.start_date <= CURDATE() AND t.end_date >= CURDATE() THEN 1 ELSE 0 END) as ongoing_trips,
        SUM(CASE WHEN t.end_date < CURDATE() THEN 1 ELSE 0 END) as completed_trips
    FROM trips t
    WHERE t.guide_id = ?
");
$stats_query->bind_param("s", $guide_id);
$stats_query->execute();
$stats = $stats_query->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Trip - Guide Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Schedule Styles - consistent with other pages */
        .schedule-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .schedule-header div h1 {
            color: #2c3e50;
            margin: 0;
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .schedule-header div p {
            color: #6c757d;
            margin: 5px 0 0 0;
        }
        
        .add-trip-btn {
            background: linear-gradient(135deg, #2e8b57, #3cb371);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(46, 139, 87, 0.3);
        }
        
        .add-trip-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 139, 87, 0.4);
            color: white;
        }
        
        /* Stats Grid - same as other pages */
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
        
        .filter-group select,
        .filter-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .filter-group select:focus,
        .filter-group input:focus {
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
        }
        
        /* Schedule Grid */
        .schedules-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .schedule-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }
        
        .schedule-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        
        .schedule-header-card {
            padding: 20px;
            background: linear-gradient(135deg, rgba(46, 139, 87, 0.1), rgba(60, 179, 113, 0.1));
            border-bottom: 1px solid #e9ecef;
        }
        
        .trip-status {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 10px;
        }
        
        .status-upcoming {
            background: #e3f2fd;
            color: #1976d2;
            border: 1px solid #bbdefb;
        }
        
        .status-ongoing {
            background: #e8f5e8;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }
        
        .status-completed {
            background: #f3e5f5;
            color: #7b1fa2;
            border: 1px solid #e1bee7;
        }
        
        .trip-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin: 0 0 5px 0;
            color: #2c3e50;
            line-height: 1.3;
        }
        
        .mountain-info {
            color: #2e8b57;
            font-weight: 600;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .schedule-dates {
            color: #6c757d;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .schedule-content {
            padding: 20px;
        }
        
        .schedule-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .meta-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        
        .meta-label {
            font-size: 0.8rem;
            color: #6c757d;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .meta-value {
            font-weight: 600;
            color: #2c3e50;
        }
        
        .participants-info {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 15px;
        }
        
        .participants-count {
            font-size: 0.9rem;
            color: #6c757d;
        }
        
        .participants-bar {
            width: 100px;
            height: 6px;
            background: #e9ecef;
            border-radius: 3px;
            overflow: hidden;
            margin-left: 10px;
        }
        
        .participants-fill {
            height: 100%;
            background: linear-gradient(90deg, #2e8b57, #3cb371);
            transition: width 0.3s ease;
        }
        
        .schedule-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            flex: 1;
            justify-content: center;
            min-width: 80px;
        }
        
        .btn-primary-action {
            background: #2e8b57;
            color: white;
        }
        
        .btn-secondary-action {
            background: #6c757d;
            color: white;
        }
        
        .action-btn:hover {
            transform: scale(1.05);
            opacity: 0.9;
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
        
        /* Alerts */
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
            
            .schedule-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .stats-grid {
                grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
                gap: 15px;
            }
            
            .schedules-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .filters-grid {
                grid-template-columns: 1fr;
            }
            
            .schedule-meta {
                grid-template-columns: 1fr;
            }
            
            .schedule-actions {
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
            
            .schedules-grid {
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
                <a href="bookings.php"><i class="fas fa-calendar-check"></i> Pesanan</a>
                <a href="schedule.php" class="active"><i class="fas fa-calendar-alt"></i> Jadwal</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main">
            <!-- Header -->
            <div class="schedule-header">
                <div>
                    <h1><i class="fas fa-calendar-alt"></i> Jadwal Trip Pendakian</h1>
                    <p>Lihat dan kelola jadwal trip pendakian yang Anda pimpin</p>
                </div>
                <a href="trips.php" class="add-trip-btn">
                    <i class="fas fa-plus"></i>
                    <span>Tambah Trip Baru</span>
                </a>
            </div>

            <!-- Statistics Grid -->
            <section class="stats-grid">
                <div class="stat-card">
                    <i class="fas fa-calendar-check"></i>
                    <div>
                        <h3>Total Trip</h3>
                        <p><?php echo $stats['total_trips'] ?? 0; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <i class="fas fa-clock"></i>
                    <div>
                        <h3>Trip Mendatang</h3>
                        <p><?php echo $stats['upcoming_trips'] ?? 0; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <i class="fas fa-play-circle"></i>
                    <div>
                        <h3>Trip Berlangsung</h3>
                        <p><?php echo $stats['ongoing_trips'] ?? 0; ?></p>
                    </div>
                </div>

                <div class="stat-card">
                    <i class="fas fa-check-circle"></i>
                    <div>
                        <h3>Trip Selesai</h3>
                        <p><?php echo $stats['completed_trips'] ?? 0; ?></p>
                    </div>
                </div>
            </section>

            <!-- Filters Section -->
            <section class="filters-section">
                <h3><i class="fas fa-filter"></i> Filter Jadwal</h3>
                <form method="GET" class="filters-grid">
                    <div class="filter-group">
                        <label for="status">Status Trip</label>
                        <select name="status" id="status">
                            <option value="all" <?php echo ($status_filter === 'all') ? 'selected' : ''; ?>>Semua Status</option>
                            <option value="upcoming" <?php echo ($status_filter === 'upcoming') ? 'selected' : ''; ?>>Mendatang</option>
                            <option value="ongoing" <?php echo ($status_filter === 'ongoing') ? 'selected' : ''; ?>>Berlangsung</option>
                            <option value="completed" <?php echo ($status_filter === 'completed') ? 'selected' : ''; ?>>Selesai</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="month">Bulan</label>
                        <input type="month" name="month" id="month" value="<?php echo htmlspecialchars($month_filter); ?>">
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="filter-btn btn-filter">
                            <i class="fas fa-search"></i> Filter
                        </button>
                        <a href="schedule.php" class="filter-btn btn-reset">
                            <i class="fas fa-undo"></i> Reset
                        </a>
                    </div>
                </form>
            </section>

            <!-- Schedule Grid -->
            <?php if (count($schedules) > 0): ?>
                <div class="schedules-grid">
                    <?php foreach ($schedules as $schedule): 
                        $participants_percentage = $schedule['max_participants'] > 0 ? ($schedule['total_bookings'] / $schedule['max_participants']) * 100 : 0;
                        
                        // Determine status
                        $current_date = date('Y-m-d');
                        if ($schedule['start_date'] > $current_date) {
                            $status = 'upcoming';
                            $status_text = 'Mendatang';
                        } elseif ($schedule['start_date'] <= $current_date && $schedule['end_date'] >= $current_date) {
                            $status = 'ongoing';
                            $status_text = 'Berlangsung';
                        } else {
                            $status = 'completed';
                            $status_text = 'Selesai';
                        }
                    ?>
                        <div class="schedule-card">
                            <div class="schedule-header-card">
                                <div class="trip-status status-<?php echo $status; ?>">
                                    <?php echo $status_text; ?>
                                </div>
                                
                                <h3 class="trip-title"><?php echo htmlspecialchars($schedule['title']); ?></h3>
                                
                                <div class="mountain-info">
                                    <i class="fas fa-mountain"></i>
                                    <span><?php echo htmlspecialchars($schedule['mountain_name']); ?> (<?php echo $schedule['mountain_height']; ?>m)</span>
                                </div>
                                
                                <div class="schedule-dates">
                                    <i class="fas fa-calendar-alt"></i>
                                    <span><?php echo date('d M', strtotime($schedule['start_date'])); ?> - <?php echo date('d M Y', strtotime($schedule['end_date'])); ?></span>
                                </div>
                            </div>
                            
                            <div class="schedule-content">
                                <div class="schedule-meta">
                                    <div class="meta-item">
                                        <span class="meta-label">Durasi</span>
                                        <span class="meta-value"><?php echo $schedule['duration']; ?> hari</span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Harga</span>
                                        <span class="meta-value">Rp <?php echo number_format($schedule['price'], 0, ',', '.'); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Max Peserta</span>
                                        <span class="meta-value"><?php echo $schedule['max_participants']; ?> orang</span>
                                    </div>
                                    <div class="meta-item">
                                        <span class="meta-label">Titik Kumpul</span>
                                        <span class="meta-value"><?php echo htmlspecialchars(substr($schedule['meeting_point'], 0, 20)); ?>...</span>
                                    </div>
                                </div>
                                
                                <div class="participants-info">
                                    <span class="participants-count">
                                        <strong><?php echo $schedule['total_bookings']; ?></strong> dari <strong><?php echo $schedule['max_participants']; ?></strong> peserta
                                    </span>
                                    <div class="participants-bar">
                                        <div class="participants-fill" style="width: <?php echo min($participants_percentage, 100); ?>%"></div>
                                    </div>
                                </div>
                                
                                <div class="schedule-actions">
                                    <a href="trips.php" class="action-btn btn-primary-action">
                                        <i class="fas fa-eye"></i>
                                        <span>Lihat Trip</span>
                                    </a>
                                    <a href="bookings.php" class="action-btn btn-secondary-action">
                                        <i class="fas fa-users"></i>
                                        <span>Peserta</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-calendar-times"></i>
                    <h3>Tidak Ada Jadwal</h3>
                    <p>Belum ada trip yang dijadwalkan untuk periode ini. Buat trip baru untuk menambah jadwal!</p>
                    <a href="trips.php" class="add-trip-btn">
                        <i class="fas fa-plus"></i>
                        <span>Buat Trip Baru</span>
                    </a>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
        // Mobile responsive adjustments
        document.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth <= 767) {
                console.log('Mobile view detected for schedule page');
            }
        });

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease';
                alert.style.opacity = '0';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>


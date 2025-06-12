<?php 
session_start();
require_once '../config.php';

// Cek login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

// Cek dan buat tabel trips jika belum ada
$check_table = "SHOW TABLES LIKE 'trips'";
$table_exists = $conn->query($check_table);

if ($table_exists->num_rows == 0) {
    // Buat tabel trips
    $create_table = "CREATE TABLE trips (
        id INT AUTO_INCREMENT PRIMARY KEY,
        mountain_id INT NOT NULL,
        guide_id INT NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        max_participants INT NOT NULL,
        active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (mountain_id) REFERENCES mountains(id),
        FOREIGN KEY (guide_id) REFERENCES guides(id)
    )";
    
    if (!$conn->query($create_table)) {
        die("Error creating table: " . $conn->error);
    }
}

// Ambil data gunung dan guide untuk dropdown
$mountains_query = "SELECT id, name FROM mountains ORDER BY name ASC";
$mountains_result = $conn->query($mountains_query);
if (!$mountains_result) {
    die("Error fetching mountains: " . $conn->error);
}
$mountains = $mountains_result->fetch_all(MYSQLI_ASSOC);

$guides_query = "SELECT id, name FROM guides ORDER BY name ASC";
$guides_result = $conn->query($guides_query);
if (!$guides_result) {
    die("Error fetching guides: " . $conn->error);
}
$guides = $guides_result->fetch_all(MYSQLI_ASSOC);

// Jika form disubmit (POST), simpan data trip
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mountain_id = intval($_POST['mountain_id']);
    $guide_id = intval($_POST['guide_id']);
    $start_date = $conn->real_escape_string($_POST['start_date']);
    $end_date = $conn->real_escape_string($_POST['end_date']);
    $price = floatval($_POST['price']);
    $max_participants = intval($_POST['max_participants']);
    $active = isset($_POST['active']) ? 1 : 0;

    $sql = "INSERT INTO trips (mountain_id, guide_id, start_date, end_date, price, max_participants, active) 
            VALUES ($mountain_id, $guide_id, '$start_date', '$end_date', $price, $max_participants, $active)";
    
    if (!$conn->query($sql)) {
        $error = "Error: " . $conn->error;
    } else {
        header("Location: trips.php");
        exit();
    }
}

// Ambil semua trip dengan urutan ascending
$sql = "SELECT t.*, 
               m.name AS mountain_name, 
               g.name AS guide_name 
        FROM trips t
        LEFT JOIN mountains m ON t.mountain_id = m.id
        LEFT JOIN guides g ON t.guide_id = g.id
        ORDER BY t.id ASC";
$result = $conn->query($sql);
if (!$result) {
    die("Error fetching trips: " . $conn->error);
}
$trips = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Trip - Admin Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/guide.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        .trips-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
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
        
        .trips-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .trip-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }
        
        .trip-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        
        .trip-image {
            height: 200px;
            background: linear-gradient(135deg, rgba(46, 139, 87, 0.8), rgba(60, 179, 113, 0.8));
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .trip-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .trip-status {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: white;
            text-transform: uppercase;
        }
        
        .status-active {
            background: #28a745;
        }
        
        .status-inactive {
            background: #6c757d;
        }
        
        .trip-content {
            padding: 20px;
        }
        
        .trip-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #2c3e50;
            line-height: 1.3;
        }
        
        .trip-destination {
            color: #2e8b57;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .trip-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #6c757d;
            font-size: 14px;
        }
        
        .meta-item i {
            color: #2e8b57;
            width: 16px;
        }
        
        .trip-price {
            font-size: 1.4rem;
            font-weight: 700;
            color: #e67e22;
            margin-bottom: 15px;
            text-align: center;
            padding: 10px;
            background: #fff3e0;
            border-radius: 8px;
        }
        
        .trip-actions {
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
        
        .btn-danger-action {
            background: #dc3545;
            color: white;
        }
        
        .action-btn:hover {
            transform: scale(1.05);
            opacity: 0.9;
        }
        
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
    </style>
</head>
<body>
<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="nav-section-title">Admin Panel</div>
        <ul class="nav-links">
            <li><a href="index.php" class="nav-link"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li><a href="users.php" class="nav-link"><i class="fas fa-users"></i> Pengguna</a></li>
            <li><a href="guides.php" class="nav-link"><i class="fas fa-map-signs"></i> Guide</a></li>
            <li><a href="mountains.php" class="nav-link"><i class="fas fa-mountain"></i> Gunung</a></li>
            <li><a href="trips.php" class="nav-link active"><i class="fas fa-route"></i> Trip</a></li>
            <li><a href="profile.php" class="nav-link"><i class="fas fa-user-cog"></i> Profil</a></li>
            <li><a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="admin-main">
        <!-- Header -->
        <div class="trips-header">
            <div>
                <h1><i class="fas fa-route"></i> Kelola Trip Pendakian</h1>
                <p>Atur dan kelola trip pendakian yang tersedia</p>
            </div>
            <a href="trip_create.php" class="add-trip-btn">
                <i class="fas fa-plus"></i>
                <span>Tambah Trip Baru</span>
            </a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Trips Grid -->
        <?php if (count($trips) > 0): ?>
            <div class="trips-grid">
                <?php foreach ($trips as $trip): ?>
                    <div class="trip-card">
                        <div class="trip-image">
                            <img src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/9c578af0-d9da-4311-8700-2accfb95d5cf.png" 
                                 alt="Beautiful mountain landscape of <?php echo htmlspecialchars($trip['mountain_name']); ?>">
                            <div class="trip-status status-<?php echo $trip['active'] ? 'active' : 'inactive'; ?>">
                                <?php echo $trip['active'] ? 'Aktif' : 'Nonaktif'; ?>
                            </div>
                        </div>
                        
                        <div class="trip-content">
                            <h3 class="trip-title"><?php echo htmlspecialchars($trip['mountain_name']); ?></h3>
                            
                            <div class="trip-destination">
                                <i class="fas fa-user"></i>
                                <span>Guide: <?php echo htmlspecialchars($trip['guide_name']); ?></span>
                            </div>
                            
                            <div class="trip-meta">
                                <div class="meta-item">
                                    <i class="fas fa-calendar"></i>
                                    <span><?php echo date('d M Y', strtotime($trip['start_date'])); ?></span>
                                </div>
                                <div class="meta-item">
                                    <i class="fas fa-users"></i>
                                    <span>Max <?php echo $trip['max_participants']; ?> orang</span>
                                </div>
                            </div>
                            
                            <div class="trip-price">
                                Rp <?php echo number_format($trip['price'], 0, ',', '.'); ?>
                                <small>/orang</small>
                            </div>
                            
                            <div class="trip-actions">
                                <a href="edit_trip.php?id=<?php echo $trip['id']; ?>" class="action-btn btn-primary-action">
                                    <i class="fas fa-edit"></i>
                                    <span>Edit</span>
                                </a>
                                <a href="delete_trip.php?id=<?php echo $trip['id']; ?>" class="action-btn btn-danger-action" onclick="return confirm('Yakin ingin hapus trip ini?')">
                                    <i class="fas fa-trash-alt"></i>
                                    <span>Hapus</span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-route"></i>
                <h3>Belum Ada Trip</h3>
                <p>Belum ada trip pendakian yang terdaftar. Mulai tambahkan trip pertama!</p>
                <a href="trip_create.php" class="add-trip-btn">
                    <i class="fas fa-plus"></i>
                    <span>Tambah Trip Pertama</span>
                </a>
            </div>
        <?php endif; ?>
    </main>
</div>

<script>
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

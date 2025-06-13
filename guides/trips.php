
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

// Handle trip actions
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'add_trip':
                $mountain_id = $_POST['mountain_id'];
                $title = $conn->real_escape_string($_POST['title']);
                $description = $conn->real_escape_string($_POST['description']);
                $start_date = $_POST['start_date'];
                $end_date = $_POST['end_date'];
                $duration = intval($_POST['duration']);
                $price = floatval($_POST['price']);
                $max_participants = intval($_POST['max_participants']);
                $included = $conn->real_escape_string($_POST['included']);
                $not_included = $conn->real_escape_string($_POST['not_included']);
                $meeting_point = $conn->real_escape_string($_POST['meeting_point']);
                
                $trip_id = 't' . uniqid();
                $image_url = 'assets/images/trips/trip_default.jpg';
                
                $insert_trip = $conn->prepare("
                    INSERT INTO trips (id, mountain_id, title, description, start_date, end_date, duration, price, max_participants, guide_id, included, not_included, meeting_point, image_url) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
                ");
                $insert_trip->bind_param("sssssiidisssss", $trip_id, $mountain_id, $title, $description, $start_date, $end_date, $duration, $price, $max_participants, $guide_id, $included, $not_included, $meeting_point, $image_url);
                
                if ($insert_trip->execute()) {
                    $message = "Trip berhasil ditambahkan!";
                } else {
                    $error = "Gagal menambahkan trip.";
                }
                break;
                
            case 'update_status':
                $trip_id = $_POST['trip_id'];
                $status = $_POST['status'];
                
                $update_status = $conn->prepare("UPDATE trips SET featured = ? WHERE id = ? AND guide_id = ?");
                $featured = ($status === 'active') ? 1 : 0;
                $update_status->bind_param("iss", $featured, $trip_id, $guide_id);
                
                if ($update_status->execute()) {
                    $message = "Status trip berhasil diperbarui!";
                } else {
                    $error = "Gagal memperbarui status trip.";
                }
                break;

            case 'delete_trip':
                $trip_id = $_POST['trip_id'];
                
                // Check if there are any bookings for this trip
                $check_bookings = $conn->prepare("SELECT COUNT(*) as booking_count FROM bookings WHERE trip_id = ?");
                $check_bookings->bind_param("s", $trip_id);
                $check_bookings->execute();
                $booking_result = $check_bookings->get_result()->fetch_assoc();
                
                if ($booking_result['booking_count'] > 0) {
                    $error = "Tidak dapat menghapus trip yang sudah memiliki pesanan!";
                } else {
                    $delete_trip = $conn->prepare("DELETE FROM trips WHERE id = ? AND guide_id = ?");
                    $delete_trip->bind_param("ss", $trip_id, $guide_id);
                    
                    if ($delete_trip->execute()) {
                        $message = "Trip berhasil dihapus!";
                    } else {
                        $error = "Gagal menghapus trip.";
                    }
                }
                break;
        }
    }
}

// Get mountains for dropdown
$mountains = $conn->query("SELECT * FROM mountains ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

// Get guide's trips
$trips_query = $conn->prepare("
    SELECT t.*, m.name as mountain_name, m.image_url as mountain_image,
           (SELECT COUNT(*) FROM bookings b WHERE b.trip_id = t.id) as total_bookings
    FROM trips t 
    LEFT JOIN mountains m ON t.mountain_id = m.id 
    WHERE t.guide_id = ? 
    ORDER BY t.start_date DESC
");
$trips_query->bind_param("s", $guide_id);
$trips_query->execute();
$trips = $trips_query->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Trip - Guide Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Trip Management Styles */
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
        
        .status-full {
            background: #dc3545;
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
        
        .trip-participants {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-top: 1px solid #e9ecef;
            margin-bottom: 15px;
        }
        
        .participants-count {
            font-size: 14px;
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
            min-width: 70px;
        }
        
        .btn-primary-action {
            background: #2e8b57;
            color: white;
        }
        
        .btn-secondary-action {
            background: #6c757d;
            color: white;
        }
        
        .btn-warning-action {
            background: #ffc107;
            color: #212529;
        }
        
        .btn-danger-action {
            background: #dc3545;
            color: white;
        }
        
        .action-btn:hover {
            transform: scale(1.05);
            opacity: 0.9;
        }
        
        /* Modal Styles */
        .modal {
            display: none;
            position: fixed;
            z-index: 1000;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            backdrop-filter: blur(5px);
        }
        
        .modal-content {
            background-color: white;
            margin: 5% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 600px;
            max-height: 80vh;
            overflow-y: auto;
            position: relative;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e9ecef;
        }
        
        .modal-header h2 {
            color: #2e8b57;
            margin: 0;
        }
        
        .close {
            color: #aaa;
            font-size: 28px;
            font-weight: bold;
            cursor: pointer;
            transition: color 0.3s ease;
        }
        
        .close:hover {
            color: #2e8b57;
        }
        
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s ease;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2e8b57;
            box-shadow: 0 0 0 3px rgba(46, 139, 87, 0.1);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }
        
        .form-actions {
            display: flex;
            justify-content: flex-end;
            gap: 15px;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-weight: 500;
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
        
        /* Delete Confirmation Modal */
        .delete-modal {
            display: none;
            position: fixed;
            z-index: 1001;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.6);
            backdrop-filter: blur(5px);
        }
        
        .delete-modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 450px;
            text-align: center;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }
        
        .delete-icon {
            font-size: 4rem;
            color: #dc3545;
            margin-bottom: 20px;
        }
        
        .delete-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #2c3e50;
            margin-bottom: 15px;
        }
        
        .delete-text {
            color: #6c757d;
            margin-bottom: 25px;
            line-height: 1.5;
        }
        
        .delete-actions {
            display: flex;
            gap: 15px;
            justify-content: center;
        }
        
        /* Responsive Design */
        @media (max-width: 767px) {
            .trips-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .trips-grid {
                grid-template-columns: 1fr;
                gap: 20px;
            }
            
            .trip-meta {
                grid-template-columns: 1fr;
                gap: 10px;
            }
            
            .trip-actions {
                flex-direction: column;
            }
            
            .action-btn {
                flex: auto;
                min-width: auto;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .modal-content {
                width: 95%;
                margin: 2% auto;
                padding: 20px;
            }
        }
        
        @media (min-width: 1440px) {
            .main {
                max-width: 1400px;
                margin: 0 auto;
                padding: 30px 40px;
            }
            
            .trips-grid {
                grid-template-columns: repeat(auto-fill, minmax(400px, 1fr));
                gap: 30px;
            }
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
            <!-- Header -->
            <div class="trips-header">
                <div>
                    <h1><i class="fas fa-route"></i> Kelola Trip Pendakian</h1>
                    <p>Atur dan kelola trip pendakian yang Anda tawarkan</p>
                </div>
                <button class="add-trip-btn" onclick="openAddTripModal()">
                    <i class="fas fa-plus"></i>
                    <span>Tambah Trip Baru</span>
                </button>
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

            <!-- Trips Grid -->
            <?php if (count($trips) > 0): ?>
                <div class="trips-grid">
                    <?php foreach ($trips as $trip): 
                        $participants_percentage = $trip['max_participants'] > 0 ? ($trip['total_bookings'] / $trip['max_participants']) * 100 : 0;
                        $status = $trip['featured'] ? 'active' : 'inactive';
                        if ($trip['total_bookings'] >= $trip['max_participants']) {
                            $status = 'full';
                        }
                    ?>
                        <div class="trip-card">
                            <div class="trip-image">
                                <img src="https://storage.googleapis.com/workspace-0f70711f-8b4e-4d94-86f1-2a93ccde5887/image/515966f9-d134-4063-9c40-78312c6951c2.png?php echo urlencode($trip['mountain_name']); ?>" 
                                     alt="Beautiful mountain landscape of <?php echo htmlspecialchars($trip['mountain_name']); ?> with hiking trails and scenic views perfect for guided trekking adventures">
                                <div class="trip-status status-<?php echo $status; ?>">
                                    <?php 
                                    switch($status) {
                                        case 'active': echo 'Aktif'; break;
                                        case 'inactive': echo 'Nonaktif'; break;
                                        case 'full': echo 'Penuh'; break;
                                    }
                                    ?>
                                </div>
                            </div>
                            
                            <div class="trip-content">
                                <h3 class="trip-title"><?php echo htmlspecialchars($trip['title']); ?></h3>
                                
                                <div class="trip-destination">
                                    <i class="fas fa-mountain"></i>
                                    <span><?php echo htmlspecialchars($trip['mountain_name']); ?></span>
                                </div>
                                
                                <div class="trip-meta">
                                    <div class="meta-item">
                                        <i class="fas fa-calendar"></i>
                                        <span><?php echo date('d M', strtotime($trip['start_date'])); ?> - <?php echo date('d M Y', strtotime($trip['end_date'])); ?></span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-clock"></i>
                                        <span><?php echo $trip['duration']; ?> hari</span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-users"></i>
                                        <span>Max <?php echo $trip['max_participants']; ?> orang</span>
                                    </div>
                                    <div class="meta-item">
                                        <i class="fas fa-map-marker-alt"></i>
                                        <span><?php echo htmlspecialchars(substr($trip['meeting_point'], 0, 20)); ?>...</span>
                                    </div>
                                </div>
                                
                                <div class="trip-price">
                                    Rp <?php echo number_format($trip['price'], 0, ',', '.'); ?>
                                    <small>/orang</small>
                                </div>
                                
                                <div class="trip-participants">
                                    <span class="participants-count">
                                        <strong><?php echo $trip['total_bookings']; ?></strong> dari <strong><?php echo $trip['max_participants']; ?></strong> peserta
                                    </span>
                                    <div class="participants-bar">
                                        <div class="participants-fill" style="width: <?php echo min($participants_percentage, 100); ?>%"></div>
                                    </div>
                                </div>
                                
                                <div class="trip-actions">
                                    <a href="trip_detail.php?id=<?php echo $trip['id']; ?>" class="action-btn btn-primary-action">
                                        <i class="fas fa-eye"></i>
                                        <span>Detail</span>
                                    </a>
                                    <a href="edit_trip.php?id=<?php echo $trip['id']; ?>" class="action-btn btn-secondary-action">
                                        <i class="fas fa-edit"></i>
                                        <span>Edit</span>
                                    </a>
                                    <button class="action-btn btn-warning-action" onclick="toggleTripStatus('<?php echo $trip['id']; ?>', '<?php echo $status === 'active' ? 'inactive' : 'active'; ?>')">
                                        <i class="fas fa-<?php echo $status === 'active' ? 'pause' : 'play'; ?>"></i>
                                        <span><?php echo $status === 'active' ? 'Nonaktif' : 'Aktif'; ?>kan</span>
                                    </button>
                                    <button class="action-btn btn-danger-action" onclick="openDeleteModal('<?php echo $trip['id']; ?>', '<?php echo htmlspecialchars($trip['title']); ?>', <?php echo $trip['total_bookings']; ?>)">
                                        <i class="fas fa-trash"></i>
                                        <span>Hapus</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-route"></i>
                    <h3>Belum Ada Trip</h3>
                    <p>Anda belum membuat trip pendakian. Mulai tambahkan trip pertama Anda!</p>
                    <button class="add-trip-btn" onclick="openAddTripModal()">
                        <i class="fas fa-plus"></i>
                        <span>Tambah Trip Pertama</span>
                    </button>
                </div>
            <?php endif; ?>
        </main>
    </div>

    <!-- Add Trip Modal -->
    <div id="addTripModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2><i class="fas fa-plus-circle"></i> Tambah Trip Baru</h2>
                <span class="close" onclick="closeModal('addTripModal')">&times;</span>
            </div>
            
            <form method="POST" action="">
                <input type="hidden" name="action" value="add_trip">
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="mountain_id">Pilih Gunung</label>
                        <select name="mountain_id" id="mountain_id" required>
                            <option value="">-- Pilih Gunung --</option>
                            <?php foreach ($mountains as $mountain): ?>
                                <option value="<?php echo $mountain['id']; ?>">
                                    <?php echo htmlspecialchars($mountain['name']); ?> (<?php echo $mountain['height']; ?>m)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="title">Judul Trip</label>
                        <input type="text" name="title" id="title" placeholder="Contoh: Pendakian Rinjani 3D2N" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Deskripsi Trip</label>
                    <textarea name="description" id="description" placeholder="Jelaskan detail trip, rute, aktivitas, dan hal menarik lainnya..." required></textarea>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="start_date">Tanggal Mulai</label>
                        <input type="date" name="start_date" id="start_date" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="end_date">Tanggal Selesai</label>
                        <input type="date" name="end_date" id="end_date" required>
                    </div>
                </div>
                
                <div class="form-grid">
                    <div class="form-group">
                        <label for="duration">Durasi (hari)</label>
                        <input type="number" name="duration" id="duration" min="1" max="30" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="max_participants">Maksimal Peserta</label>
                        <input type="number" name="max_participants" id="max_participants" min="1" max="50" required>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="price">Harga per Orang (Rp)</label>
                    <input type="number" name="price" id="price" min="0" step="1000" placeholder="2500000" required>
                </div>
                
                <div class="form-group">
                    <label for="meeting_point">Titik Kumpul</label>
                    <input type="text" name="meeting_point" id="meeting_point" placeholder="Contoh: Basecamp Sembalun" required>
                </div>
                
                <div class="form-group">
                    <label for="included">Yang Termasuk</label>
                    <textarea name="included" id="included" placeholder="Contoh: Guide berpengalaman, Peralatan camping, Makan 3x sehari, Transport lokal..." required></textarea>
                </div>
                
                <div class="form-group">
                    <label for="not_included">Yang Tidak Termasuk</label>
                    <textarea name="not_included" id="not_included" placeholder="Contoh: Tiket pesawat, Asuransi perjalanan, Pengeluaran pribadi..." required></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="action-btn btn-secondary-action" onclick="closeModal('addTripModal')">
                        <i class="fas fa-times"></i> Batal
                    </button>
                    <button type="submit" class="action-btn btn-primary-action">
                        <i class="fas fa-save"></i> Simpan Trip
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div id="deleteModal" class="delete-modal">
        <div class="delete-modal-content">
            <div class="delete-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <h3 class="delete-title">Konfirmasi Hapus Trip</h3>
            <p class="delete-text">
                Apakah Anda yakin ingin menghapus trip "<span id="deleteTripTitle"></span>"?
                <br><br>
                <strong id="deleteWarning" style="color: #dc3545;"></strong>
            </p>
            <div class="delete-actions">
                <button type="button" class="action-btn btn-secondary-action" onclick="closeDeleteModal()">
                    <i class="fas fa-times"></i> Batal
                </button>
                <button type="button" class="action-btn btn-danger-action" onclick="confirmDeleteTrip()" id="confirmDeleteBtn">
                    <i class="fas fa-trash"></i> <span id="deleteButtonText">Hapus Trip</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Hidden forms -->
    <form id="statusForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="update_status">
        <input type="hidden" name="trip_id" id="statusTripId">
        <input type="hidden" name="status" id="statusValue">
    </form>

    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="action" value="delete_trip">
        <input type="hidden" name="trip_id" id="deleteTripId">
    </form>

    <script src="../assets/js/main.js"></script>
    <script>
        let currentDeleteTripId = null;
        let currentDeleteBookingCount = 0;

        // Modal functions
        function openAddTripModal() {
            document.getElementById('addTripModal').style.display = 'block';
            // Set tomorrow as minimum date
            const tomorrow = new Date();
            tomorrow.setDate(tomorrow.getDate() + 1);
            document.getElementById('start_date').min = tomorrow.toISOString().split('T')[0];
        }

        function closeModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        // Delete modal functions
        function openDeleteModal(tripId, tripTitle, bookingCount) {
            currentDeleteTripId = tripId;
            currentDeleteBookingCount = bookingCount;
            
            document.getElementById('deleteTripTitle').textContent = tripTitle;
            
            const warningElement = document.getElementById('deleteWarning');
            const deleteButton = document.getElementById('confirmDeleteBtn');
            const deleteButtonText = document.getElementById('deleteButtonText');
            
            if (bookingCount > 0) {
                warningElement.textContent = `Trip ini memiliki ${bookingCount} pesanan dan tidak dapat dihapus!`;
                deleteButton.disabled = true;
                deleteButton.style.opacity = '0.5';
                deleteButton.style.cursor = 'not-allowed';
                deleteButtonText.textContent = 'Tidak Dapat Dihapus';
            } else {
                warningElement.textContent = 'Tindakan ini tidak dapat dibatalkan!';
                deleteButton.disabled = false;
                deleteButton.style.opacity = '1';
                deleteButton.style.cursor = 'pointer';
                deleteButtonText.textContent = 'Hapus Trip';
            }
            
            document.getElementById('deleteModal').style.display = 'block';
        }

        function closeDeleteModal() {
            document.getElementById('deleteModal').style.display = 'none';
            currentDeleteTripId = null;
            currentDeleteBookingCount = 0;
        }

        function confirmDeleteTrip() {
            if (currentDeleteTripId && currentDeleteBookingCount === 0) {
                document.getElementById('deleteTripId').value = currentDeleteTripId;
                document.getElementById('deleteForm').submit();
            }
        }

        // Auto-calculate duration when dates change
        document.getElementById('start_date').addEventListener('change', calculateDuration);
        document.getElementById('end_date').addEventListener('change', calculateDuration);

        function calculateDuration() {
            const startDate = new Date(document.getElementById('start_date').value);
            const endDate = new Date(document.getElementById('end_date').value);
            
            if (startDate && endDate && endDate >= startDate) {
                const diffTime = Math.abs(endDate - startDate);
                const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
                document.getElementById('duration').value = diffDays;
                
                // Set minimum end date
                document.getElementById('end_date').min = document.getElementById('start_date').value;
            }
        }

        function toggleTripStatus(tripId, newStatus) {
            if (confirm(`Apakah Anda yakin ingin mengubah status trip ini menjadi ${newStatus === 'active' ? 'aktif' : 'nonaktif'}?`)) {
                document.getElementById('statusTripId').value = tripId;
                document.getElementById('statusValue').value = newStatus;
                document.getElementById('statusForm').submit();
            }
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const addModal = document.getElementById('addTripModal');
            const deleteModal = document.getElementById('deleteModal');
            
            if (event.target == addModal) {
                addModal.style.display = 'none';
            }
            if (event.target == deleteModal) {
                closeDeleteModal();
            }
        }

        // Mobile responsive adjustments
        document.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth <= 767) {
                // Mobile-specific functionality
                console.log('Mobile view detected for trips page');
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


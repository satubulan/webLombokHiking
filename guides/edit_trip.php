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
$guide_query = $conn->prepare("SELECT * FROM guides WHERE user_id = ?");
$guide_query->bind_param("s", $user_id);
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

// Get trip details
$trip_query = $conn->prepare("
    SELECT t.*, m.name as mountain_name 
    FROM trips t 
    LEFT JOIN mountains m ON t.mountain_id = m.id 
    WHERE t.id = ? AND t.guide_id = ?
");
$trip_query->bind_param("ss", $trip_id, $guide_id);
$trip_query->execute();
$trip = $trip_query->get_result()->fetch_assoc();

// Check if trip exists
if (!$trip) {
    header('Location: trips.php?error=' . urlencode('Trip tidak ditemukan'));
    exit();
}

// Handle update trip action
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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

    // Validation
    if (empty($title) || empty($description) || empty($start_date) || empty($end_date) || $price <= 0 || $max_participants <= 0) {
        $error = "Semua field wajib diisi dengan benar.";
    } else {
        $update_trip = $conn->prepare("
            UPDATE trips SET 
            mountain_id = ?, title = ?, description = ?, start_date = ?, end_date = ?, 
            duration = ?, price = ?, max_participants = ?, included = ?, not_included = ?, meeting_point = ? 
            WHERE id = ? AND guide_id = ?
        ");
        $update_trip->bind_param("ssssisissssss", $mountain_id, $title, $description, $start_date, $end_date, $duration, $price, $max_participants, $included, $not_included, $meeting_point, $trip_id, $guide_id);
        
        if ($update_trip->execute()) {
            $message = "Trip berhasil diperbarui!";
            // Refresh trip data
            $trip_query->execute();
            $trip = $trip_query->get_result()->fetch_assoc();
        } else {
            $error = "Gagal memperbarui trip.";
        }
    }
}

// Get mountains for dropdown
$mountains = $conn->query("SELECT * FROM mountains ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Trip - Guide Dashboard</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Edit Trip Styles - Konsisten dengan trips.php */
        .edit-trip-container {
            max-width: 900px;
            margin: 0 auto;
            padding: 30px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .edit-header {
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 20px;
            margin-bottom: 30px;
            text-align: center;
        }
        
        .edit-title {
            color: #2e8b57;
            font-size: 2.2rem;
            margin-bottom: 10px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        
        .edit-subtitle {
            color: #6c757d;
            font-size: 1.1rem;
        }
        
        .trip-preview {
            background: linear-gradient(135deg, rgba(46, 139, 87, 0.1), rgba(60, 179, 113, 0.1));
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 30px;
            border: 1px solid rgba(46, 139, 87, 0.2);
        }
        
        .trip-preview h4 {
            color: #2e8b57;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
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
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .form-group label i {
            color: #2e8b57;
            width: 16px;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
            box-sizing: border-box;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #2e8b57;
            box-shadow: 0 0 0 3px rgba(46, 139, 87, 0.1);
            transform: translateY(-2px);
        }
        
        .form-group textarea {
            resize: vertical;
            min-height: 120px;
            font-family: inherit;
        }
        
        .form-group-full {
            grid-column: 1 / -1;
        }
        
        .price-input {
            position: relative;
        }
        
        .price-input::before {
            content: 'Rp';
            position: absolute;
            left: 12px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-weight: 600;
            pointer-events: none;
            z-index: 1;
        }
        
        .price-input input {
            padding-left: 35px;
        }
        
        .form-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            gap: 15px;
            margin-top: 30px;
            padding-top: 25px;
            border-top: 1px solid #e9ecef;
            flex-wrap: wrap;
        }
        
        .btn-action {
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            font-size: 1rem;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            cursor: pointer;
            transition: all 0.3s ease;
            min-width: 140px;
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
        
        .btn-secondary {
            background: #6c757d;
            color: white;
            box-shadow: 0 4px 15px rgba(108, 117, 125, 0.3);
        }
        
        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(108, 117, 125, 0.4);
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
        
        .alert {
            padding: 15px 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert i {
            font-size: 1.2rem;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d4edda, #c3e6cb);
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        
        .alert-error {
            background: linear-gradient(135deg, #f8d7da, #f5c6cb);
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        
        .input-help {
            font-size: 12px;
            color: #6c757d;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .char-counter {
            font-size: 12px;
            color: #6c757d;
            text-align: right;
            margin-top: 5px;
        }
        
        /* Mobile Responsive */
        @media (max-width: 767px) {
            .main {
                padding: 20px;
            }
            
            .edit-trip-container {
                margin: 0;
                padding: 20px;
                border-radius: 10px;
            }
            
            .edit-title {
                font-size: 1.8rem;
                flex-direction: column;
                gap: 10px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .form-actions {
                flex-direction: column;
                align-items: stretch;
            }
            
            .btn-action {
                min-width: auto;
                width: 100%;
            }
            
            .form-actions > div {
                display: flex;
                gap: 10px;
            }
            
            .form-actions > div .btn-action {
                flex: 1;
            }
        }
        
        /* Tablet Responsive */
        @media (min-width: 768px) and (max-width: 1023px) {
            .form-grid {
                gap: 18px;
            }
            
            .edit-trip-container {
                padding: 25px;
            }
        }
        
        /* Desktop Large */
        @media (min-width: 1440px) {
            .main {
                max-width: 1400px;
                margin: 0 auto;
                padding: 30px 40px;
            }
            
            .form-grid {
                gap: 25px;
            }
            
            .edit-trip-container {
                padding: 40px;
            }
        }
        
        /* Animation for form improvements */
        .form-group {
            position: relative;
            overflow: hidden;
        }
        
        .form-group::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: linear-gradient(135deg, #2e8b57, #3cb371);
            transition: width 0.3s ease;
        }
        
        .form-group input:focus + ::after,
        .form-group select:focus + ::after,
        .form-group textarea:focus + ::after {
            width: 100%;
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
            <div class="edit-trip-container">
                <div class="edit-header">
                    <h1 class="edit-title">
                        <i class="fas fa-edit"></i>
                        Edit Trip
                    </h1>
                    <p class="edit-subtitle">Perbarui informasi trip pendakian Anda</p>
                </div>

                <div class="trip-preview">
                    <h4><i class="fas fa-info-circle"></i> Trip yang sedang diedit</h4>
                    <p><strong><?php echo htmlspecialchars($trip['title']); ?></strong> - <?php echo htmlspecialchars($trip['mountain_name'] ?? 'Gunung tidak diketahui'); ?></p>
                </div>

                <!-- Alerts -->
                <?php if ($message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?php echo htmlspecialchars($message); ?>
                    </div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php echo htmlspecialchars($error); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="mountain_id">
                                <i class="fas fa-mountain"></i>
                                Pilih Gunung
                            </label>
                            <select name="mountain_id" id="mountain_id" required>
                                <option value="">-- Pilih Gunung --</option>
                                <?php foreach ($mountains as $mountain): ?>
                                    <option value="<?php echo $mountain['id']; ?>" 
                                            <?php echo ($trip['mountain_id'] == $mountain['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($mountain['name']); ?> (<?php echo $mountain['height']; ?>m)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="input-help">
                                <i class="fas fa-info-circle"></i>
                                Pilih lokasi gunung untuk trip ini
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="title">
                                <i class="fas fa-heading"></i>
                                Judul Trip
                            </label>
                            <input type="text" 
                                   name="title" 
                                   id="title" 
                                   placeholder="Contoh: Pendakian Rinjani 3D2N" 
                                   value="<?php echo htmlspecialchars($trip['title']); ?>"
                                   maxlength="255"
                                   required>
                            <div class="char-counter">
                                <span id="titleCounter"><?php echo strlen($trip['title']); ?></span>/255 karakter
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group form-group-full">
                        <label for="description">
                            <i class="fas fa-align-left"></i>
                            Deskripsi Trip
                        </label>
                        <textarea name="description" 
                                  id="description" 
                                  placeholder="Jelaskan detail trip, rute, aktivitas, dan hal menarik lainnya..." 
                                  required><?php echo htmlspecialchars($trip['description']); ?></textarea>
                        <div class="input-help">
                            <i class="fas fa-lightbulb"></i>
                            Deskripsikan dengan detail agar calon peserta tertarik
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="start_date">
                                <i class="fas fa-calendar-alt"></i>
                                Tanggal Mulai
                            </label>
                            <input type="date" 
                                   name="start_date" 
                                   id="start_date" 
                                   value="<?php echo $trip['start_date']; ?>"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label for="end_date">
                                <i class="fas fa-calendar-check"></i>
                                Tanggal Selesai
                            </label>
                            <input type="date" 
                                   name="end_date" 
                                   id="end_date" 
                                   value="<?php echo $trip['end_date']; ?>"
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="duration">
                                <i class="fas fa-clock"></i>
                                Durasi (hari)
                            </label>
                            <input type="number" 
                                   name="duration" 
                                   id="duration" 
                                   min="1" 
                                   max="30" 
                                   value="<?php echo $trip['duration']; ?>"
                                   required>
                            <div class="input-help">
                                <i class="fas fa-info-circle"></i>
                                Akan dihitung otomatis dari tanggal
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="max_participants">
                                <i class="fas fa-users"></i>
                                Maksimal Peserta
                            </label>
                            <input type="number" 
                                   name="max_participants" 
                                   id="max_participants" 
                                   min="1" 
                                   max="50" 
                                   value="<?php echo $trip['max_participants']; ?>"
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-grid">
                        <div class="form-group price-input">
                            <label for="price">
                                <i class="fas fa-money-bill-wave"></i>
                                Harga per Orang
                            </label>
                            <input type="number" 
                                   name="price" 
                                   id="price" 
                                   min="0" 
                                   step="1000" 
                                   placeholder="2500000"
                                   value="<?php echo intval($trip['price']); ?>"
                                   required>
                            <div class="input-help">
                                <i class="fas fa-calculator"></i>
                                Masukkan harga dalam Rupiah
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="meeting_point">
                                <i class="fas fa-map-marker-alt"></i>
                                Titik Kumpul
                            </label>
                            <input type="text" 
                                   name="meeting_point" 
                                   id="meeting_point" 
                                   placeholder="Contoh: Basecamp Sembalun"
                                   value="<?php echo htmlspecialchars($trip['meeting_point']); ?>"
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-group form-group-full">
                        <label for="included">
                            <i class="fas fa-check-circle"></i>
                            Yang Termasuk
                        </label>
                        <textarea name="included" 
                                  id="included" 
                                  placeholder="Contoh: Guide berpengalaman, Peralatan camping, Makan 3x sehari, Transport lokal..." 
                                  required><?php echo htmlspecialchars($trip['included']); ?></textarea>
                    </div>
                    
                    <div class="form-group form-group-full">
                        <label for="not_included">
                            <i class="fas fa-times-circle"></i>
                            Yang Tidak Termasuk
                        </label>
                        <textarea name="not_included" 
                                  id="not_included" 
                                  placeholder="Contoh: Tiket pesawat, Asuransi perjalanan, Pengeluaran pribadi..." 
                                  required><?php echo htmlspecialchars($trip['not_included']); ?></textarea>
                    </div>
                    
                    <div class="form-actions">
                        <div>
                            <a href="trips.php" class="btn-action btn-secondary">
                                <i class="fas fa-arrow-left"></i>
                                Kembali
                            </a>
                            <a href="trip_detail.php?id=<?php echo $trip['id']; ?>" class="btn-action btn-warning">
                                <i class="fas fa-eye"></i>
                                Lihat Detail
                            </a>
                        </div>
                        <button type="submit" class="btn-action btn-primary">
                            <i class="fas fa-save"></i>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
    <script>
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

        // Character counter for title
        document.getElementById('title').addEventListener('input', function() {
            const counter = document.getElementById('titleCounter');
            counter.textContent = this.value.length;
            
            if (this.value.length > 200) {
                counter.style.color = '#dc3545';
            } else if (this.value.length > 150) {
                counter.style.color = '#ffc107';
            } else {
                counter.style.color = '#6c757d';
            }
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const startDate = new Date(document.getElementById('start_date').value);
            const endDate = new Date(document.getElementById('end_date').value);
            const today = new Date();
            
            if (startDate < today) {
                e.preventDefault();
                alert('Tanggal mulai tidak boleh kurang dari hari ini!');
                return;
            }
            
            if (endDate < startDate) {
                e.preventDefault();
                alert('Tanggal selesai harus setelah tanggal mulai!');
                return;
            }
            
            const price = parseFloat(document.getElementById('price').value);
            if (price < 50000) {
                e.preventDefault();
                alert('Harga minimum adalah Rp 50.000!');
                return;
            }
        });

        // Set minimum date to today for start date
        document.addEventListener('DOMContentLoaded', function() {
            const today = new Date().toISOString().split('T')[0];
            document.getElementById('start_date').min = today;
            
            // Auto-focus first input
            document.getElementById('mountain_id').focus();
            
            // Initial duration calculation
            calculateDuration();
        });

        // Price input formatter
        document.getElementById('price').addEventListener('input', function() {
            // Remove non-numeric characters except for decimal point
            this.value = this.value.replace(/[^\d]/g, '');
            
            // Format with thousands separator
            if (this.value) {
                const formatted = parseInt(this.value).toLocaleString('id-ID');
                // Update placeholder to show formatted version
                this.setAttribute('data-formatted', 'Rp ' + formatted);
            }
        });

        // Mobile responsive adjustments
        function handleResize() {
            if (window.innerWidth <= 767) {
                // Mobile behavior
                console.log('Mobile view detected for edit trip');
            } else {
                // Desktop behavior
                console.log('Desktop view detected for edit trip');
            }
        }

        window.addEventListener('resize', handleResize);
        window.addEventListener('DOMContentLoaded', handleResize);

        // Auto-hide alerts after 5 seconds
        setTimeout(function() {
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                alert.style.transition = 'opacity 0.5s ease, transform 0.5s ease';
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                setTimeout(() => alert.remove(), 500);
            });
        }, 5000);
    </script>
</body>
</html>


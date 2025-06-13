<?php
session_start();
require_once '../config.php';

// Check if user is logged in and has user role
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: ../views/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$booking_id = isset($_GET['booking_id']) ? $_GET['booking_id'] : '';

// Fetch booking details for the current user and booking id
$booking_query = $conn->prepare("SELECT b.*, t.title, t.price, t.start_date, t.end_date
                                FROM bookings b
                                JOIN trips t ON b.trip_id = t.id
                                WHERE b.user_id = ? AND b.id = ?");
$booking_query->bind_param("si", $user_id, $booking_id);
$booking_query->execute();
$booking_result = $booking_query->get_result();

if ($booking_result->num_rows === 0) {
    header('Location: dashboard.php'); // Redirect if no booking found
    exit();
}

$booking = $booking_result->fetch_assoc();
$error_message = '';
$success_message = '';

// Handle file upload for payment proof
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] == 0) {
    $file_tmp = $_FILES['payment_proof']['tmp_name'];
    $file_name = $_FILES['payment_proof']['name'];
    $file_size = $_FILES['payment_proof']['size'];
    $file_type = $_FILES['payment_proof']['type'];

    // Validating file type and size
    $allowed_types = ['image/jpeg', 'image/png', 'application/pdf'];
    if (!in_array($file_type, $allowed_types)) {
        $error_message = "Hanya file gambar (JPEG, PNG) atau PDF yang diizinkan!";
    } elseif ($file_size > 5000000) { // Max file size 5MB
        $error_message = "Ukuran file terlalu besar, maksimal 5MB!";
    } else {
        // Save the file
        $upload_dir = 'uploads/payment_proofs/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true); // Create directory if not exists
        }

        $file_path = $upload_dir . basename($file_name);
        if (move_uploaded_file($file_tmp, $file_path)) {
            // Update the booking record with the payment proof
            $update_query = $conn->prepare("UPDATE bookings SET payment_proof = ?, status = 'Menunggu Verifikasi' WHERE id = ?");
            $update_query->bind_param("si", $file_path, $booking_id);

            if ($update_query->execute()) {
                $success_message = "Bukti pembayaran berhasil di-upload. Menunggu verifikasi!";
            } else {
                $error_message = "Terjadi kesalahan saat memperbarui bukti pembayaran.";
            }
        } else {
            $error_message = "Gagal meng-upload file!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pembayaran - Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Status Pembayaran Styles */
        :root {
            --primary-green: #2e8b57;
            --secondary-green: #3cb371;
            --light-green: #f0f9f4;
            --accent-green: #10b981;
            --dark-text: #1f2937;
            --light-text: #6b7280;
            --danger-red: #dc2626;
            --warning-yellow: #f59e0b;
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

        .status-container {
            max-width: 1200px;
            margin: 0 auto;
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

        .status-content {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            padding: 30px;
        }

        .status-content h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 20px;
        }

        .status-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }

        .status-item span {
            font-size: 1rem;
            color: var(--light-text);
        }

        .status-item strong {
            color: var(--dark-text);
            font-size: 1.1rem;
        }

        .status-item .status-label {
            font-weight: 700;
            font-size: 1.1rem;
        }

        .upload-section {
            margin-top: 30px;
            background: var(--light-green);
            padding: 20px;
            border-radius: 12px;
        }

        .upload-section input {
            margin-bottom: 20px;
        }

        .upload-section button {
            padding: 12px 30px;
            background: var(--primary-green);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s;
        }

        .upload-section button:hover {
            background: var(--secondary-green);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

        .btn {
            padding: 12px 30px;
            background: var(--primary-green);
            color: white;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            margin-top: 20px;
        }

        .btn:hover {
            background: var(--secondary-green);
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
                <a href="status_pembayaran.php" class="active">
                    <i class="fas fa-credit-card"></i> 
                    Status Pembayaran
                </a>
                <a href="paket_saya.php">
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
            <div class="status-container">
                <div class="page-header">
                    <h2>Status Pembayaran Trip</h2>
                </div>
                
                <!-- Display success/error messages -->
                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($success_message) ?>
                    </div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>

                <div class="status-content">
                    <h3>Detail Booking</h3>

                    <div class="status-item">
                        <span class="status-label">Trip:</span>
                        <strong><?= htmlspecialchars($booking['title']) ?></strong>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Tanggal Trip:</span>
                        <strong><?= date('d M Y', strtotime($booking['start_date'])) ?> - <?= date('d M Y', strtotime($booking['end_date'])) ?></strong>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Total Pembayaran:</span>
                        <strong>Rp <?= number_format($booking['total_price'], 0, ',', '.') ?></strong>
                    </div>
                    <div class="status-item">
                        <span class="status-label">Status Pembayaran:</span>
                        <strong><?= htmlspecialchars($booking['status']) ?></strong>
                    </div>
                    
                    <?php if ($booking['status'] === 'Menunggu Pembayaran'): ?>
                        <div class="upload-section">
                            <h3>Upload Bukti Pembayaran</h3>
                            <form action="status_pembayaran.php?booking_id=<?= $booking_id ?>" method="POST" enctype="multipart/form-data">
                                <label for="payment_proof">Pilih Bukti Pembayaran (JPEG, PNG, PDF)</label>
                                <input type="file" name="payment_proof" id="payment_proof" required>
                                <button type="submit">Upload Bukti Pembayaran</button>
                            </form>
                        </div>
                    <?php elseif ($booking['payment_proof']): ?>
                        <h3>Bukti Pembayaran</h3>
                        <p><a href="../<?= $booking['payment_proof'] ?>" target="_blank">Lihat Bukti Pembayaran</a></p>
                    <?php endif; ?>

                    <a href="dashboard.php" class="btn">Kembali ke Dashboard</a>
                </div>
            </div>
        </main>
    </div>
</body>
</html>

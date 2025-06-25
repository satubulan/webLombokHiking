<?php
session_start();
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: ../views/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$userName = $_SESSION['user_name'];

// Get payment code from URL or show all payments
$payment_code = $_GET['payment_code'] ?? '';

// Build query based on whether we have a specific payment code
if (!empty($payment_code)) {
    // Show specific payment
    $query = "
        SELECT b.*, p.payment_code, p.amount, p.payment_date, p.status as payment_status, p.payment_proof,
               COALESCE(t.title, mt.title) as trip_title,
               COALESCE(t.package_price, mt.price) as trip_price,
               COALESCE(t.start_date, 'TBD') as start_date,
               COALESCE(t.end_date, 'TBD') as end_date,
               m.name as mountain_name,
               u.name as guide_name
        FROM bookings b
        JOIN pembayaran p ON b.id = p.booking_id
        LEFT JOIN trips t ON b.trip_id = t.id
        LEFT JOIN mountain_tickets mt ON b.mountain_ticket_id = mt.id
        LEFT JOIN mountains m ON COALESCE(t.mountain_id, mt.mountain_id) = m.id
        LEFT JOIN users u ON b.selected_guide_id = u.id
        WHERE b.user_id = ? AND p.payment_code = ?
        ORDER BY b.booking_date DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $user_id, $payment_code);
} else {
    // Show all payments for user
    $query = "
        SELECT b.*, p.payment_code, p.amount, p.payment_date, p.status as payment_status, p.payment_proof,
               COALESCE(t.title, mt.title) as trip_title,
               COALESCE(t.package_price, mt.price) as trip_price,
               COALESCE(t.start_date, 'TBD') as start_date,
               COALESCE(t.end_date, 'TBD') as end_date,
               m.name as mountain_name,
               u.name as guide_name
        FROM bookings b
        JOIN pembayaran p ON b.id = p.booking_id
        LEFT JOIN trips t ON b.trip_id = t.id
        LEFT JOIN mountain_tickets mt ON b.mountain_ticket_id = mt.id
        LEFT JOIN mountains m ON COALESCE(t.mountain_id, mt.mountain_id) = m.id
        LEFT JOIN users u ON b.selected_guide_id = u.id
        WHERE b.user_id = ?
        ORDER BY b.booking_date DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $user_id);
}

$stmt->execute();
$payments_result = $stmt->get_result();

// Handle payment proof upload
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['upload_proof'])) {
    $booking_id = $_POST['booking_id'];
    
    // Verify booking belongs to user
    $verify_query = $conn->prepare("SELECT id FROM bookings WHERE id = ? AND user_id = ?");
    $verify_query->bind_param("ii", $booking_id, $user_id);
    $verify_query->execute();
    
    if ($verify_query->get_result()->num_rows > 0) {
        if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === 0) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
            $file_type = $_FILES['payment_proof']['type'];
            
            if (in_array($file_type, $allowed_types)) {
                $upload_dir = '../uploads/payments/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $file_extension = pathinfo($_FILES['payment_proof']['name'], PATHINFO_EXTENSION);
                $file_name = 'payment_' . $booking_id . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $file_name;
                
                if (move_uploaded_file($_FILES['payment_proof']['tmp_name'], $upload_path)) {
                    // Update payment record
                    $update_query = $conn->prepare("UPDATE pembayaran SET payment_proof = ?, payment_date = NOW() WHERE booking_id = ?");
                    $update_query->bind_param("si", $file_name, $booking_id);
                    
                    if ($update_query->execute()) {
                        $_SESSION['success'] = "Bukti pembayaran berhasil diupload. Menunggu verifikasi admin.";
                    } else {
                        $_SESSION['error'] = "Gagal menyimpan bukti pembayaran.";
                    }
                } else {
                    $_SESSION['error'] = "Gagal mengupload file.";
                }
            } else {
                $_SESSION['error'] = "Format file tidak didukung. Gunakan JPG, JPEG, atau PNG.";
            }
        } else {
            $_SESSION['error'] = "Pilih file bukti pembayaran terlebih dahulu.";
        }
    } else {
        $_SESSION['error'] = "Booking tidak ditemukan.";
    }
    
    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Status Pembayaran - Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        :root {
            --primary-green: #2e8b57;
            --secondary-green: #3cb371;
            --light-green: #f0f9f4;
            --accent-green: #10b981;
            --dark-text: #1f2937;
            --light-text: #6b7280;
            --danger-red: #ef4444;
            --warning-yellow: #f59e0b;
            --success-green: #22c55e;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8fafc;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .header {
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            color: white;
            padding: 20px;
            border-radius: 15px;
            margin-bottom: 30px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header h1 {
            margin: 0;
            font-size: 1.8rem;
        }

        .back-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .back-btn:hover {
            background: rgba(255,255,255,0.3);
        }

        .alert {
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert.error {
            background: #fef2f2;
            color: var(--danger-red);
            border: 1px solid #fecaca;
        }

        .alert.success {
            background: #f0fdf4;
            color: var(--success-green);
            border: 1px solid #bbf7d0;
        }

        .payment-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 20px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            border-left: 5px solid var(--accent-green);
        }

        .payment-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 20px;
            flex-wrap: wrap;
            gap: 15px;
        }

        .payment-title {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--dark-text);
            margin: 0;
        }

        .payment-code {
            background: var(--light-green);
            color: var(--accent-green);
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-family: monospace;
        }

        .status-badge {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
        }

        .status-badge.unpaid {
            background: #fef3c7;
            color: var(--warning-yellow);
        }

        .status-badge.paid {
            background: #d1fae5;
            color: var(--success-green);
        }

        .status-badge.rejected {
            background: #fee2e2;
            color: var(--danger-red);
        }

        .payment-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .detail-group {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 10px;
        }

        .detail-label {
            font-size: 0.9rem;
            color: var(--light-text);
            margin-bottom: 5px;
        }

        .detail-value {
            font-weight: 600;
            color: var(--dark-text);
            font-size: 1.1rem;
        }

        .payment-amount {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--accent-green);
        }

        .upload-section {
            background: var(--light-green);
            padding: 20px;
            border-radius: 12px;
            margin-top: 20px;
        }

        .upload-form {
            display: flex;
            gap: 15px;
            align-items: end;
            flex-wrap: wrap;
        }

        .file-input-group {
            flex: 1;
            min-width: 250px;
        }

        .file-input-group label {
            display: block;
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 8px;
        }

        .file-input {
            width: 100%;
            padding: 10px;
            border: 2px dashed var(--accent-green);
            border-radius: 8px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .file-input:hover {
            border-color: var(--primary-green);
            background: #f9fafb;
        }

        .upload-btn {
            background: var(--accent-green);
            color: white;
            border: none;
            padding: 12px 20px;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .upload-btn:hover {
            background: var(--primary-green);
        }

        .bank-info {
            background: #fff7ed;
            border: 1px solid #fed7aa;
            padding: 20px;
            border-radius: 12px;
            margin-top: 20px;
        }

        .bank-info h4 {
            color: var(--warning-yellow);
            margin: 0 0 15px 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .bank-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }

        .bank-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
        }

        .bank-name {
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 5px;
        }

        .bank-number {
            font-family: monospace;
            font-size: 1.1rem;
            color: var(--accent-green);
            margin-bottom: 5px;
        }

        .bank-holder {
            font-size: 0.9rem;
            color: var(--light-text);
        }

        .copy-btn {
            background: none;
            border: 1px solid var(--accent-green);
            color: var(--accent-green);
            padding: 5px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 0.8rem;
            margin-top: 5px;
        }

        .copy-btn:hover {
            background: var(--accent-green);
            color: white;
        }

        .proof-image {
            max-width: 200px;
            border-radius: 8px;
            margin-top: 10px;
        }

        .no-payments {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            color: var(--light-text);
        }

        .no-payments i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: var(--light-text);
        }

        @media (max-width: 768px) {
            .payment-header {
                flex-direction: column;
                align-items: stretch;
            }
            
            .payment-details {
                grid-template-columns: 1fr;
            }
            
            .upload-form {
                flex-direction: column;
            }
            
            .bank-details {
                grid-template-columns: 1fr;
            }
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
            <a href="dashboard.php" class="nav-link"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="profile.php" class="nav-link"><i class="fas fa-user"></i> Profil Saya</a>
            <a href="booking.php" class="nav-link"><i class="fas fa-calendar-plus"></i> Booking Trip</a>
            <a href="status_pembayaran.php" class="nav-link active"><i class="fas fa-credit-card"></i> Status Pembayaran</a>
            <a href="paket_saya.php" class="nav-link"><i class="fas fa-hiking"></i> Paket Saya</a>
            <a href="ajukan_guide.php" class="nav-link"><i class="fas fa-user-plus"></i> Ajukan Diri Jadi Guide</a>
            <a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>
    <main class="main">
        <div class="container">
        <!-- Header -->
        <div class="header">
            <a href="dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i>
                Kembali
            </a>
            <h1>Status Pembayaran</h1>
        </div>

        <!-- Error/Success Messages -->
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($_SESSION['error']) ?>
            </div>
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($_SESSION['success']) ?>
            </div>
            <?php unset($_SESSION['success']); ?>
        <?php endif; ?>

                <!-- Payment Details -->
        <?php if ($payments_result && $payments_result->num_rows > 0): ?>
            <?php while ($payment = $payments_result->fetch_assoc()): ?>
            <div class="payment-card">
                <div class="payment-header">
                    <h2 class="payment-title"><?= htmlspecialchars($payment['trip_title']) ?></h2>
                    <div class="payment-code"><b>Kode Pembayaran:</b> <?= htmlspecialchars($payment['payment_code']) ?></div>
                    <div class="status-badge <?= $payment['payment_status'] === 'unpaid' ? 'unpaid' : 'paid' ?>">
                        <?= ucfirst($payment['payment_status']) ?>
                    </div>
                </div>

                <div class="payment-details">
                    
                    <div class="detail-group">
                        <span class="detail-label">Total Pembayaran:</span>
                        <span class="payment-amount">Rp <?= number_format($payment['amount'], 0, ',', '.') ?></span>
                    </div>
                    <div class="detail-group">
                        <span class="detail-label">Tanggal Pembayaran:</span>
                        <span class="detail-value"><?= date('d M Y', strtotime($payment['payment_date'])) ?></span>
                    </div>
                    <div class="detail-group">
                        <span class="detail-label">Status Pembayaran:</span>
                        <span class="detail-value"><?= ucfirst($payment['payment_status']) ?></span>
                    </div>
                </div>

                <!-- Upload Payment Proof -->
                <?php if ($payment['payment_status'] === 'unpaid'): ?>
                <div class="upload-section">
                    <h4><i class="fas fa-upload"></i> Upload Bukti Pembayaran</h4>
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="booking_id" value="<?= $payment['id'] ?>">
                        <div class="upload-form">
                            <div class="file-input-group">
                                <label for="payment_proof">Pilih file (JPG, PNG):</label>
                                <input type="file" name="payment_proof" id="payment_proof" class="file-input" required>
                            </div>
                            <button type="submit" name="upload_proof" class="upload-btn">
                                <i class="fas fa-upload"></i> Upload
                            </button>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <!-- Payment Proof Display -->
                <?php if ($payment['payment_proof']): ?>
                <div class="proof-section">
                    <h4><i class="fas fa-file-image"></i> Bukti Pembayaran:</h4>
                    <img src="../uploads/payments/<?= htmlspecialchars($payment['payment_proof']) ?>" alt="Bukti Pembayaran" class="proof-image">
                </div>
                <?php endif; ?>
            </div>
            <?php endwhile; ?>
        <?php else: ?>
            <div class="no-payments">
                <i class="fas fa-exclamation-circle"></i>
                <h3>Tidak ada pembayaran yang ditemukan</h3>
                <p>Silakan lakukan booking terlebih dahulu untuk melihat status pembayaran.</p>
            </div>
        <?php endif; ?>

    </div>
    </main>
</div>
</body>
</html>

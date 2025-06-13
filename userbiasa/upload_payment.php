<?php
session_start();
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: ../views/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$booking_id = isset($_GET['booking_id']) ? $_GET['booking_id'] : '';

// Fetch booking details to check if it's eligible for uploading payment proof
$query = $conn->prepare("
    SELECT b.id, b.status, b.payment_proof, t.title
    FROM bookings b
    JOIN trips t ON b.trip_id = t.id
    WHERE b.id = ? AND b.user_id = ?
");
$query->bind_param("si", $booking_id, $user_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows === 0) {
    header('Location: status_pembayaran.php');
    exit();
}

$booking = $result->fetch_assoc();

$error_message = '';
$success_message = '';

// Process file upload if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Check if file is uploaded
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === 0) {
        $file_name = $_FILES['payment_proof']['name'];
        $file_tmp = $_FILES['payment_proof']['tmp_name'];
        $file_size = $_FILES['payment_proof']['size'];
        $file_type = $_FILES['payment_proof']['type'];

        // Validate file type (only image files allowed)
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        if (!in_array($file_type, $allowed_types)) {
            $error_message = "Hanya file gambar yang diperbolehkan (JPEG, PNG).";
        } elseif ($file_size > 5000000) {
            // Max file size: 5MB
            $error_message = "Ukuran file terlalu besar. Maksimal 5MB.";
        } else {
            // Generate unique filename and move file
            $upload_dir = '../uploads/payment_proofs/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true); // Create directory if not exists
            }

            $unique_file_name = uniqid('payment_', true) . '.' . pathinfo($file_name, PATHINFO_EXTENSION);
            $upload_path = $upload_dir . $unique_file_name;

            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Only update the booking if its status is not already 'confirmed'
                if ($booking['status'] !== 'confirmed') {
                    // Update the booking with the uploaded payment proof
                    $update_query = $conn->prepare("UPDATE bookings SET payment_proof = ?, status = 'waiting for confirmation' WHERE id = ?");
                    $update_query->bind_param("si", $unique_file_name, $booking_id);
                    if ($update_query->execute()) {
                        $success_message = "Bukti pembayaran berhasil di-upload dan menunggu konfirmasi.";
                    } else {
                        $error_message = "Terjadi kesalahan saat memperbarui bukti pembayaran.";
                    }
                } else {
                    // If already confirmed, do nothing
                    $success_message = "Bukti pembayaran sudah dikonfirmasi sebelumnya.";
                }
            } else {
                $error_message = "Gagal meng-upload file. Coba lagi.";
            }
        }
    } else {
        $error_message = "Harap pilih file bukti pembayaran untuk di-upload.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Upload Bukti Pembayaran - Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Styles for upload payment page */
        :root {
            --primary-green: #2e8b57;
            --secondary-green: #3cb371;
            --light-green: #f0f9f4;
            --accent-green: #10b981;
            --dark-text: #1f2937;
            --light-text: #6b7280;
            --danger-red: #dc2626;
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

        .upload-form {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            padding: 30px;
        }

        .upload-form h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 20px;
        }

        .upload-form input[type="file"] {
            padding: 10px;
            font-size: 1rem;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            width: 100%;
        }

        .upload-form button {
            padding: 12px 25px;
            background: var(--accent-green);
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .upload-form button:hover {
            background: var(--primary-green);
        }

        /* Alerts */
        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
            padding: 15px;
            border-radius: 12px;
            margin-bottom: 20px;
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
                <a href="status_pembayaran.php">
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
            <div class="page-header">
                <h2>Upload Bukti Pembayaran</h2>
            </div>

            <!-- Display error/success messages -->
            <?php if ($error_message): ?>
                <div class="alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    <?= htmlspecialchars($error_message) ?>
                </div>
            <?php endif; ?>
            <?php if ($success_message): ?>
                <div class="alert-success">
                    <i class="fas fa-check-circle"></i>
                    <?= htmlspecialchars($success_message) ?>
                </div>
            <?php endif; ?>

            <div class="upload-form">
                <h3>Silakan upload bukti pembayaran untuk booking: <?= htmlspecialchars($booking['title']) ?></h3>
                <form method="POST" enctype="multipart/form-data">
                    <input type="file" name="payment_proof" accept="image/*">
                    <button type="submit">Upload Bukti Pembayaran</button>
                </form>
            </div>
        </main>
    </div>
</body>
</html>

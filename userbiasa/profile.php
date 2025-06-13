
<?php
session_start();
require_once '../config.php';

// Check if user is logged in and is a regular user
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: ../views/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success_message = '';
$error_message = '';

// Get user data
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("s", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user_data = $user_result->fetch_assoc();

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validate required fields
    if (empty($name) || empty($email)) {
        $error_message = "Nama dan email wajib diisi.";
    } else {
        // Check if email is already used by another user
        $email_check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $email_check->bind_param("ss", $email, $user_id);
        $email_check->execute();
        $email_result = $email_check->get_result();
        
        if ($email_result->num_rows > 0) {
            $error_message = "Email sudah digunakan oleh user lain.";
        } else {
            // Update basic info
            if (!empty($new_password)) {
                // Validate current password
                if (empty($current_password)) {
                    $error_message = "Password saat ini wajib diisi untuk mengganti password.";
                } elseif (!password_verify($current_password, $user_data['password'])) {
                    $error_message = "Password saat ini salah.";
                } elseif ($new_password !== $confirm_password) {
                    $error_message = "Konfirmasi password tidak sesuai.";
                } elseif (strlen($new_password) < 6) {
                    $error_message = "Password baru minimal 6 karakter.";
                } else {
                    // Update with new password
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    $update_query = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, password = ? WHERE id = ?");
                    $update_query->bind_param("sssss", $name, $email, $phone, $hashed_password, $user_id);
                    
                    if ($update_query->execute()) {
                        $success_message = "Profil dan password berhasil diperbarui!";
                        $_SESSION['user_name'] = $name;
                        $_SESSION['user_email'] = $email;
                        
                        // Refresh user data
                        $user_data['name'] = $name;
                        $user_data['email'] = $email;
                        $user_data['phone'] = $phone;
                    } else {
                        $error_message = "Terjadi kesalahan saat memperbarui profil.";
                    }
                }
            } else {
                // Update without password change
                $update_query = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ? WHERE id = ?");
                $update_query->bind_param("ssss", $name, $email, $phone, $user_id);
                
                if ($update_query->execute()) {
                    $success_message = "Profil berhasil diperbarui!";
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    
                    // Refresh user data
                    $user_data['name'] = $name;
                    $user_data['email'] = $email;
                    $user_data['phone'] = $phone;
                } else {
                    $error_message = "Terjadi kesalahan saat memperbarui profil.";
                }
            }
        }
    }
}

// Get user statistics for profile summary
$stats_query = $conn->prepare("
    SELECT 
        COUNT(CASE WHEN b.status = 'confirmed' THEN 1 END) as completed_trips,
        COUNT(CASE WHEN b.status = 'pending' THEN 1 END) as pending_bookings,
        COUNT(*) as total_bookings,
        COALESCE(SUM(CASE WHEN b.status = 'confirmed' THEN b.total_price END), 0) as total_spent
    FROM bookings b 
    WHERE b.user_id = ?
");
$stats_query->bind_param("s", $user_id);
$stats_query->execute();
$stats = $stats_query->get_result()->fetch_assoc();

// Get recent bookings
$recent_bookings = $conn->prepare("
    SELECT b.*, t.title as trip_title, t.start_date, t.end_date, m.name as mountain_name
    FROM bookings b 
    JOIN trips t ON b.trip_id = t.id 
    JOIN mountains m ON t.mountain_id = m.id
    WHERE b.user_id = ? 
    ORDER BY b.booking_date DESC 
    LIMIT 5
");
$recent_bookings->bind_param("s", $user_id);
$recent_bookings->execute();
$recent_result = $recent_bookings->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Profile Page Styles */
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

        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        /* Profile Header */
        .profile-header {
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            color: white;
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(46, 139, 87, 0.3);
            position: relative;
            overflow: hidden;
        }

        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 150px;
            height: 150px;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="rgba(255,255,255,0.1)"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>') no-repeat center;
            background-size: contain;
        }

        .profile-info {
            display: flex;
            align-items: center;
            gap: 30px;
            margin-bottom: 20px;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            border: 4px solid rgba(255,255,255,0.3);
            position: relative;
        }

        .avatar-upload {
            position: absolute;
            bottom: 0;
            right: 0;
            width: 30px;
            height: 30px;
            background: var(--accent-green);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 0.8rem;
        }

        .profile-details h2 {
            margin: 0 0 10px 0;
            font-size: 2rem;
            font-weight: 700;
        }

        .profile-details p {
            margin: 5px 0;
            opacity: 0.9;
        }

        .profile-status {
            display: flex;
            gap: 30px;
            margin-top: 20px;
        }

        .status-item {
            text-align: center;
        }

        .status-number {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .status-label {
            font-size: 0.9rem;
            opacity: 0.8;
        }

        /* Content Grid */
        .content-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }

        .form-section,
        .activity-section {
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
        }

        .section-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid var(--light-green);
        }

        .section-header h3 {
            font-size: 1.4rem;
            color: var(--dark-text);
            margin: 0;
            font-weight: 700;
        }

        .section-header i {
            font-size: 1.5rem;
            color: var(--accent-green);
        }

        /* Form Styles */
        .form-group {
            margin-bottom: 25px;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--dark-text);
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 15px 20px;
            border: 2px solid #e5e7eb;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
            background: white;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--accent-green);
            box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.1);
        }

        .required {
            color: var(--danger-red);
        }

        /* Password Toggle */
        .password-field {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            cursor: pointer;
            color: var(--light-text);
            transition: color 0.3s ease;
        }

        .password-toggle:hover {
            color: var(--accent-green);
        }

        /* Button Styles */
        .btn-group {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn-update {
            flex: 1;
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            color: white;
            border: none;
            padding: 15px 30px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-update:hover {
            background: linear-gradient(135deg, var(--secondary-green), var(--primary-green));
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(46, 139, 87, 0.3);
        }

        .btn-cancel {
            background: transparent;
            color: var(--light-text);
            border: 2px solid #e5e7eb;
            padding: 15px 30px;
            border-radius: 12px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-cancel:hover {
            border-color: var(--danger-red);
            color: var(--danger-red);
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 25px;
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

        /* Activity Section */
        .activity-list {
            max-height: 500px;
            overflow-y: auto;
        }

        .activity-item {
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .activity-item:hover {
            border-color: var(--accent-green);
            box-shadow: 0 4px 15px rgba(16, 185, 129, 0.1);
        }

        .activity-header {
            display: flex;
            justify-content: between;
            align-items: start;
            margin-bottom: 10px;
        }

        .activity-title {
            font-weight: 600;
            color: var(--dark-text);
            margin: 0 0 5px 0;
            font-size: 1rem;
        }

        .activity-mountain {
            color: var(--accent-green);
            font-size: 0.9rem;
            font-weight: 500;
        }

        .activity-details {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin: 15px 0;
        }

        .activity-detail {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.85rem;
            color: var(--light-text);
        }

        .activity-detail i {
            color: var(--accent-green);
            width: 14px;
        }

        .activity-status {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.8rem;
            font-weight: 600;
            text-transform: capitalize;
        }

        .status-confirmed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-cancelled {
            background: #fee2e2;
            color: #991b1b;
        }

        /* Responsive Design */
        @media (max-width: 1024px) {
            .content-grid {
                grid-template-columns: 1fr;
            }
            
            .activity-section {
                order: 2;
            }
        }

        @media (max-width: 767px) {
            .admin-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                order: 2;
            }

            .main {
                order: 1;
                padding: 20px;
            }

            .profile-info {
                flex-direction: column;
                text-align: center;
            }

            .profile-status {
                justify-content: center;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .btn-group {
                flex-direction: column;
            }

            .activity-details {
                grid-template-columns: 1fr;
            }
        }

        /* Collapsible Password Section */
        .password-section {
            border-top: 1px solid #e5e7eb;
            margin-top: 30px;
            padding-top: 25px;
        }

        .password-toggle-btn {
            background: transparent;
            border: none;
            color: var(--accent-green);
            font-weight: 600;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
            font-size: 0.95rem;
        }

        .password-toggle-btn:hover {
            color: var(--primary-green);
        }

        .password-fields {
            display: none;
        }

        .password-fields.show {
            display: block;
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                max-height: 0;
            }
            to {
                opacity: 1;
                max-height: 300px;
            }
        }

        .password-strength {
            margin-top: 10px;
            padding: 10px;
            border-radius: 8px;
            font-size: 0.85rem;
        }

        .strength-weak {
            background: #fee2e2;
            color: #991b1b;
        }

        .strength-medium {
            background: #fef3c7;
            color: #92400e;
        }

        .strength-strong {
            background: #d1fae5;
            color: #065f46;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Responsive Sidebar -->
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
                <a href="profile.php" class="active">
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

        <!-- Main Content -->
        <main class="main">
            <div class="profile-container">
                <!-- Profile Header -->
                <div class="profile-header">
                    <div class="profile-info">
                        <div class="profile-avatar">
                            <i class="fas fa-user-circle"></i>
                            <div class="avatar-upload" onclick="uploadAvatar()">
                                <i class="fas fa-camera"></i>
                            </div>
                        </div>
                        <div class="profile-details">
                            <h2><?= htmlspecialchars($user_data['name']) ?></h2>
                            <p><i class="fas fa-envelope"></i> <?= htmlspecialchars($user_data['email']) ?></p>
                            <p><i class="fas fa-phone"></i> <?= htmlspecialchars($user_data['phone'] ?? 'Belum diisi') ?></p>
                            <p><i class="fas fa-calendar-alt"></i> Bergabung sejak <?= date('d M Y', strtotime($user_data['created_at'])) ?></p>
                        </div>
                    </div>
                    
                    <div class="profile-status">
                        <div class="status-item">
                            <div class="status-number"><?= $stats['total_bookings'] ?></div>
                            <div class="status-label">Total Booking</div>
                        </div>
                        <div class="status-item">
                            <div class="status-number"><?= $stats['completed_trips'] ?></div>
                            <div class="status-label">Trip Selesai</div>
                        </div>
                        <div class="status-item">
                            <div class="status-number"><?= $stats['pending_bookings'] ?></div>
                            <div class="status-label">Trip Pending</div>
                        </div>
                        <div class="status-item">
                            <div class="status-number">Rp <?= number_format($stats['total_spent'], 0, ',', '.') ?></div>
                            <div class="status-label">Total Pengeluaran</div>
                        </div>
                    </div>
                </div>

                <!-- Content Grid -->
                <div class="content-grid">
                    <!-- Profile Form -->
                    <div class="form-section">
                        <div class="section-header">
                            <i class="fas fa-user-edit"></i>
                            <h3>Edit Profil</h3>
                        </div>

                        <!-- Alert Messages -->
                        <?php if (!empty($success_message)): ?>
                            <div class="alert alert-success">
                                <i class="fas fa-check-circle"></i>
                                <?= htmlspecialchars($success_message) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (!empty($error_message)): ?>
                            <div class="alert alert-error">
                                <i class="fas fa-exclamation-circle"></i>
                                <?= htmlspecialchars($error_message) ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="profile.php">
                            <div class="form-group">
                                <label for="name">Nama Lengkap <span class="required">*</span></label>
                                <input type="text" name="name" id="name" value="<?= htmlspecialchars($user_data['name']) ?>" required>
                            </div>

                            <div class="form-row">
                                <div class="form-group">
                                    <label for="email">Email <span class="required">*</span></label>
                                    <input type="email" name="email" id="email" value="<?= htmlspecialchars($user_data['email']) ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Nomor Telepon</label>
                                    <input type="tel" name="phone" id="phone" value="<?= htmlspecialchars($user_data['phone'] ?? '') ?>" placeholder="08xxxxxxxxx">
                                </div>
                            </div>

                            <!-- Password Section -->
                            <div class="password-section">
                                <button type="button" class="password-toggle-btn" onclick="togglePasswordFields()">
                                    <i class="fas fa-key"></i>
                                    <span id="passwordToggleText">Ubah Password</span>
                                    <i class="fas fa-chevron-down" id="passwordToggleIcon"></i>
                                </button>

                                <div class="password-fields" id="passwordFields">
                                    <div class="form-group">
                                        <label for="current_password">Password Saat Ini</label>
                                        <div class="password-field">
                                            <input type="password" name="current_password" id="current_password" placeholder="Masukkan password saat ini">
                                            <span class="password-toggle" onclick="togglePassword('current_password')">
                                                <i class="fas fa-eye" id="current_password_icon"></i>
                                            </span>
                                        </div>
                                    </div>

                                    <div class="form-row">
                                        <div class="form-group">
                                            <label for="new_password">Password Baru</label>
                                            <div class="password-field">
                                                <input type="password" name="new_password" id="new_password" placeholder="Minimum 6 karakter" oninput="checkPasswordStrength()">
                                                <span class="password-toggle" onclick="togglePassword('new_password')">
                                                    <i class="fas fa-eye" id="new_password_icon"></i>
                                                </span>
                                            </div>
                                            <div class="password-strength" id="passwordStrength" style="display: none;"></div>
                                        </div>
                                        <div class="form-group">
                                            <label for="confirm_password">Konfirmasi Password</label>
                                            <div class="password-field">
                                                <input type="password" name="confirm_password" id="confirm_password" placeholder="Ulangi password baru" oninput="checkPasswordMatch()">
                                                <span class="password-toggle" onclick="togglePassword('confirm_password')">
                                                    <i class="fas fa-eye" id="confirm_password_icon"></i>
                                                </span>
                                            </div>
                                            <div id="passwordMatch" style="display: none; margin-top: 5px; font-size: 0.85rem;"></div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="btn-group">
                                <button type="submit" class="btn-update">
                                    <i class="fas fa-save"></i>
                                    Perbarui Profil
                                </button>
                                <button type="button" class="btn-cancel" onclick="resetForm()">
                                    <i class="fas fa-times"></i>
                                    Batal
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Recent Activity -->
                    <div class="activity-section">
                        <div class="section-header">
                            <i class="fas fa-history"></i>
                            <h3>Aktivitas Terbaru</h3>
                        </div>

                        <div class="activity-list">
                            <?php if ($recent_result->num_rows > 0): ?>
                                <?php while ($booking = $recent_result->fetch_assoc()): ?>
                                    <div class="activity-item">
                                        <div class="activity-header">
                                            <div>
                                                <h4 class="activity-title"><?= htmlspecialchars($booking['trip_title']) ?></h4>
                                                <p class="activity-mountain">
                                                    <i class="fas fa-mountain"></i>
                                                    <?= htmlspecialchars($booking['mountain_name']) ?>
                                                </p>
                                            </div>
                                            <span class="activity-status status-<?= $booking['status'] ?>">
                                                <?= ucfirst($booking['status']) ?>
                                            </span>
                                        </div>
                                        
                                        <div class="activity-details">
                                            <div class="activity-detail">
                                                <i class="fas fa-calendar-alt"></i>
                                                <span><?= date('d M Y', strtotime($booking['start_date'])) ?> - <?= date('d M Y', strtotime($booking['end_date'])) ?></span>
                                            </div>
                                            <div class="activity-detail">
                                                <i class="fas fa-users"></i>
                                                <span><?= $booking['participants'] ?> peserta</span>
                                            </div>
                                            <div class="activity-detail">
                                                <i class="fas fa-money-bill-wave"></i>
                                                <span>Rp <?= number_format($booking['total_price'], 0, ',', '.') ?></span>
                                            </div>
                                            <div class="activity-detail">
                                                <i class="fas fa-clock"></i>
                                                <span>Booked <?= date('d M Y', strtotime($booking['booking_date'])) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="activity-item" style="text-align: center; padding: 40px 20px;">
                                    <i class="fas fa-hiking" style="font-size: 2rem; color: var(--light-text); margin-bottom: 15px;"></i>
                                    <h4 style="color: var(--dark-text); margin-bottom: 10px;">Belum Ada Aktivitas</h4>
                                    <p style="color: var(--light-text);">Mulai petualangan hiking Anda dengan booking trip pertama!</p>
                                    <a href="dashboard.php" style="display: inline-block; margin-top: 15px; background: var(--accent-green); color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; font-weight: 600;">
                                        <i class="fas fa-search"></i> Cari Trip
                                    </a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Password field toggle
        function togglePasswordFields() {
            const fields = document.getElementById('passwordFields');
            const icon = document.getElementById('passwordToggleIcon');
            const text = document.getElementById('passwordToggleText');
            
            if (fields.classList.contains('show')) {
                fields.classList.remove('show');
                icon.classList.replace('fa-chevron-up', 'fa-chevron-down');
                text.textContent = 'Ubah Password';
            } else {
                fields.classList.add('show');
                icon.classList.replace('fa-chevron-down', 'fa-chevron-up');
                text.textContent = 'Tutup Password';
            }
        }

        // Password visibility toggle
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = document.getElementById(fieldId + '_icon');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.classList.replace('fa-eye', 'fa-eye-slash');
            } else {
                field.type = 'password';
                icon.classList.replace('fa-eye-slash', 'fa-eye');
            }
        }

        // Password strength checker
        function checkPasswordStrength() {
            const password = document.getElementById('new_password').value;
            const strengthDiv = document.getElementById('passwordStrength');
            
            if (password.length === 0) {
                strengthDiv.style.display = 'none';
                return;
            }
            
            strengthDiv.style.display = 'block';
            
            let strength = 0;
            let feedback = [];
            
            if (password.length >= 8) strength++;
            else feedback.push('Minimal 8 karakter');
            
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            else feedback.push('Kombinasi huruf besar dan kecil');
            
            if (/\d/.test(password)) strength++;
            else feedback.push('Minimal 1 angka');
            
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;
            else feedback.push('Karakter khusus');
            
            if (strength <= 1) {
                strengthDiv.className = 'password-strength strength-weak';
                strengthDiv.innerHTML = '<i class="fas fa-exclamation-triangle"></i> Password lemah: ' + feedback.join(', ');
            } else if (strength <= 2) {
                strengthDiv.className = 'password-strength strength-medium';
                strengthDiv.innerHTML = '<i class="fas fa-shield-alt"></i> Password sedang';
            } else {
                strengthDiv.className = 'password-strength strength-strong';
                strengthDiv.innerHTML = '<i class="fas fa-check-circle"></i> Password kuat';
            }
        }

        // Password match checker
        function checkPasswordMatch() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            const matchDiv = document.getElementById('passwordMatch');
            
            if (confirmPassword.length === 0) {
                matchDiv.style.display = 'none';
                return;
            }
            
            matchDiv.style.display = 'block';
            
            if (newPassword === confirmPassword) {
                matchDiv.style.color = 'var(--accent-green)';
                matchDiv.innerHTML = '<i class="fas fa-check"></i> Password cocok';
            } else {
                matchDiv.style.color = 'var(--danger-red)';
                matchDiv.innerHTML = '<i class="fas fa-times"></i> Password tidak cocok';
            }
        }

        // Reset form
        function resetForm() {
            if (confirm('Apakah Anda yakin ingin membatalkan perubahan?')) {
                location.reload();
            }
        }

        // Avatar upload function
        function uploadAvatar() {
            alert('Fitur upload avatar akan segera tersedia!');
            // Could implement file upload functionality here
        }

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (newPassword && newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Password baru dan konfirmasi password tidak cocok!');
                return false;
            }
            
            if (newPassword && newPassword.length < 6) {
                e.preventDefault();
                alert('Password baru minimal 6 karakter!');
                return false;
            }
        });

        // Auto-save functionality (optional)
        let autoSaveTimeout;
        document.querySelectorAll('input[type="text"], input[type="email"], input[type="tel"]').forEach(input => {
            input.addEventListener('input', function() {
                clearTimeout(autoSaveTimeout);
                autoSaveTimeout = setTimeout(() => {
                    // Could implement auto-save draft functionality
                    console.log('Auto-saving draft...');
                }, 2000);
            });
        });

        // Mobile responsive handling
        document.addEventListener('DOMContentLoaded', function() {
            if (window.innerWidth <= 767) {
                console.log('Mobile profile view active');
            }
        });
    </script>
</body>
</html>


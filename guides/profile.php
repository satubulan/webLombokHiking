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

// Get user info
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("s", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user_info = $user_result->fetch_assoc();

// Get guide info
$guide_query = $conn->prepare("SELECT * FROM guides WHERE user_id = ?");
$guide_query->bind_param("s", $user_id);
$guide_query->execute();
$guide_result = $guide_query->get_result();
$guide_info = $guide_result->fetch_assoc();

$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $experience = intval($_POST['experience']);
    $specialization = trim($_POST['specialization']);
    $bio = trim($_POST['bio']);
    $languages = trim($_POST['languages']);
    
    // Validation
    if (empty($name) || empty($phone) || empty($experience) || empty($specialization) || empty($bio) || empty($languages)) {
        $error = "Semua field wajib diisi.";
    } else {
        $conn->begin_transaction();
        
        try {
            // Update users table
            $update_user = $conn->prepare("UPDATE users SET name = ?, phone = ? WHERE id = ?");
            $update_user->bind_param("sss", $name, $phone, $user_id);
            $update_user->execute();
            
            if ($guide_info) {
                // Update existing guide profile
                $update_guide = $conn->prepare("
                    UPDATE guides 
                    SET name = ?, experience = ?, specialization = ?, bio = ?, languages = ? 
                    WHERE user_id = ?
                ");
                $update_guide->bind_param("sissss", $name, $experience, $specialization, $bio, $languages, $user_id);
                $update_guide->execute();
            } else {
                // Create new guide profile
                $guide_id = 'g' . uniqid();
                $default_image = 'assets/images/guides/default.jpg';
                $default_rating = 4.5;
                
                $create_guide = $conn->prepare("
                    INSERT INTO guides (id, user_id, name, image_url, experience, rating, specialization, bio, languages, active) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 1)
                ");
                $create_guide->bind_param("ssssidsss", $guide_id, $user_id, $name, $default_image, $experience, $default_rating, $specialization, $bio, $languages);
                $create_guide->execute();
            }
            
            $conn->commit();
            $message = "Profile berhasil diperbarui!";
            $_SESSION['user_name'] = $name; // Update session name
            
            // Refresh data
            $user_query->execute();
            $user_info = $user_query->get_result()->fetch_assoc();
            $guide_query->execute();
            $guide_info = $guide_query->get_result()->fetch_assoc();
            
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Terjadi kesalahan: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profile Guide - Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="logo">Guide Panel</div>
            <nav>
                <a href="dashboard.php"><i class="fas fa-chart-line"></i> Beranda</a>
                <a href="profile.php" class="active"><i class="fas fa-user-edit"></i> Profile</a>
                <a href="trips.php"><i class="fas fa-route"></i> Trip</a>
                <a href="bookings.php"><i class="fas fa-calendar-check"></i> Pesanan Saya</a>
                <a href="schedule.php"><i class="fas fa-calendar-alt"></i> Jadwal</a>
                <a href="notifications.php"><i class="fas fa-bell"></i> Notifikasi</a>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <!-- Main -->
        <main class="main">
            <header class="admin-header">
                <h1>Profile Guide</h1>
                <p>Kelola informasi profile dan keahlian Anda sebagai guide</p>
            </header>

            <?php if (!empty($message)): ?>
                <div class="success-message">
                    <i class="fas fa-check-circle"></i>
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="error-message">
                    <i class="fas fa-exclamation-circle"></i>
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <div class="profile-container">
                <div class="profile-card">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <div class="profile-info">
                            <h2><?php echo htmlspecialchars($user_info['name']); ?></h2>
                            <p><?php echo htmlspecialchars($user_info['email']); ?></p>
                            <?php if ($guide_info): ?>
                                <div class="profile-stats">
                                    <span><i class="fas fa-star"></i> <?php echo $guide_info['rating']; ?>/5.0</span>
                                    <span><i class="fas fa-calendar"></i> <?php echo $guide_info['experience']; ?> tahun</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <div class="profile-form-card">
                    <h3>Informasi Profile</h3>
                    <form method="POST" class="profile-form">
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="name">Nama Lengkap</label>
                                <input type="text" id="name" name="name" 
                                       value="<?php echo htmlspecialchars($user_info['name']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="phone">Nomor Telepon</label>
                                <input type="tel" id="phone" name="phone" 
                                       value="<?php echo htmlspecialchars($user_info['phone'] ?? ''); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="experience">Pengalaman (tahun)</label>
                                <input type="number" id="experience" name="experience" min="0" max="50"
                                       value="<?php echo $guide_info ? $guide_info['experience'] : ''; ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="specialization">Spesialisasi</label>
                                <input type="text" id="specialization" name="specialization" 
                                       placeholder="High Peak,Volcanic,Lake"
                                       value="<?php echo $guide_info ? htmlspecialchars($guide_info['specialization']) : ''; ?>" required>
                                <small>Pisahkan dengan koma (,)</small>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="languages">Bahasa yang Dikuasai</label>
                            <input type="text" id="languages" name="languages" 
                                   placeholder="Bahasa Indonesia,English,Japanese"
                                   value="<?php echo $guide_info ? htmlspecialchars($guide_info['languages']) : ''; ?>" required>
                            <small>Pisahkan dengan koma (,)</small>
                        </div>

                        <div class="form-group">
                            <label for="bio">Bio / Deskripsi</label>
                            <textarea id="bio" name="bio" rows="5" 
                                      placeholder="Ceritakan tentang pengalaman dan keahlian Anda sebagai guide..."
                                      required><?php echo $guide_info ? htmlspecialchars($guide_info['bio']) : ''; ?></textarea>
                        </div>

                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Simpan Profile
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>

    <style>
        .success-message, .error-message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .success-message {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .error-message {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .profile-container {
            display: grid;
            gap: 20px;
        }

        .profile-card, .profile-form-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .profile-avatar i {
            font-size: 60px;
            color: #2e8b57;
        }

        .profile-info h2 {
            margin: 0 0 5px 0;
            color: #333;
        }

        .profile-info p {
            margin: 0 0 10px 0;
            color: #666;
            font-size: 14px;
        }

        .profile-stats {
            display: flex;
            gap: 15px;
            font-size: 12px;
        }

        .profile-stats span {
            background: #f0f9f3;
            padding: 4px 8px;
            border-radius: 4px;
            color: #2e8b57;
        }

        .profile-form-card h3 {
            margin: 0 0 20px 0;
            color: #333;
            border-bottom: 2px solid #2e8b57;
            padding-bottom: 10px;
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
            margin-bottom: 5px;
            font-weight: 600;
            color: #333;
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 6px;
            font-size: 14px;
            transition: border-color 0.3s;
        }

        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #2e8b57;
        }

        .form-group small {
            display: block;
            margin-top: 5px;
            color: #666;
            font-size: 12px;
        }

        .form-actions {
            text-align: right;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 6px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background-color: #2e8b57;
            color: white;
        }

        .btn-primary:hover {
            background-color: #246b46;
            transform: translateY(-2px);
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            .profile-header {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</body>
</html>
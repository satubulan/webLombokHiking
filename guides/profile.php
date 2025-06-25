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
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_result = $user_query->get_result();
$user_info = $user_result->fetch_assoc();

// Get guide info
$guide_query = $conn->prepare("SELECT * FROM guide WHERE user_id = ?");
$guide_query->bind_param("i", $user_id);
$guide_query->execute();
$guide_result = $guide_query->get_result();
$guide_info = $guide_result->fetch_assoc();
$guide_id = $guide_info['id'] ?? null;

$message = '';
$error = '';

// Ambil data gunung untuk dropdown
$mountains = $conn->query("SELECT * FROM mountains ORDER BY name ASC")->fetch_all(MYSQLI_ASSOC);

// Ambil data service fee guide
$guide_services = [];
if ($guide_id) {
    $service_stmt = $conn->prepare("SELECT gs.*, m.name as mountain_name FROM guide_services gs JOIN mountains m ON gs.mountain_id = m.id WHERE gs.guide_id = ?");
    $service_stmt->bind_param("i", $guide_id);
    $service_stmt->execute();
    $guide_services = $service_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Handle tambah/ubah service fee
if (isset($_POST['service_action'])) {
    $mountain_id = intval($_POST['mountain_id']);
    $service_fee = floatval($_POST['service_fee']);
    $service_desc = trim($_POST['service_desc']);
    if ($mountain_id && $service_fee > 0) {
        // Cek apakah sudah ada service fee untuk gunung ini
        $cek = $conn->prepare("SELECT id FROM guide_services WHERE guide_id = ? AND mountain_id = ?");
        $cek->bind_param("ii", $guide_id, $mountain_id);
        $cek->execute();
        $cek_result = $cek->get_result();
        if ($cek_result->num_rows > 0) {
            // Update
            $row = $cek_result->fetch_assoc();
            $update = $conn->prepare("UPDATE guide_services SET service_fee = ?, description = ?, active = 1 WHERE id = ?");
            $update->bind_param("dsi", $service_fee, $service_desc, $row['id']);
            $update->execute();
            $message = "Service fee berhasil diupdate.";
        } else {
            // Insert
            $insert = $conn->prepare("INSERT INTO guide_services (guide_id, mountain_id, service_fee, description, active) VALUES (?, ?, ?, ?, 1)");
            $insert->bind_param("iids", $guide_id, $mountain_id, $service_fee, $service_desc);
            $insert->execute();
            $message = "Service fee berhasil ditambahkan.";
        }
        // Refresh data
        $service_stmt = $conn->prepare("SELECT gs.*, m.name as mountain_name FROM guide_services gs JOIN mountains m ON gs.mountain_id = m.id WHERE gs.guide_id = ?");
        $service_stmt->bind_param("i", $guide_id);
        $service_stmt->execute();
        $guide_services = $service_stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $error = "Pilih gunung dan masukkan fee yang valid.";
    }
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = isset($_POST['name']) ? trim($_POST['name']) : '';
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $experience = isset($_POST['experience']) ? trim($_POST['experience']) : '';
    $specialization = isset($_POST['specialization']) ? trim($_POST['specialization']) : '';
    $bio = isset($_POST['bio']) ? trim($_POST['bio']) : '';
    $languages = isset($_POST['languages']) ? trim($_POST['languages']) : '';
    
    // Handle profile picture upload
    $profile_picture = $user_info['profile_picture']; // Keep existing if no new upload
    
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = '../assets/images/profiles/';
        
        // Create directory if it doesn't exist
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
        }
        
        $file_tmp = $_FILES['profile_picture']['tmp_name'];
        $file_name = $_FILES['profile_picture']['name'];
        $file_size = $_FILES['profile_picture']['size'];
        $file_type = $_FILES['profile_picture']['type'];
        
        // Validate file type
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        if (!in_array($file_type, $allowed_types)) {
            $error = "Format file tidak didukung. Gunakan JPG, PNG, atau GIF.";
        }
        // Validate file size (max 5MB)
        elseif ($file_size > 5 * 1024 * 1024) {
            $error = "Ukuran file terlalu besar. Maksimal 5MB.";
        }
        else {
            // Generate unique filename
            $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
            $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($file_tmp, $upload_path)) {
                // Delete old profile picture if exists and not default
                if ($user_info['profile_picture'] && 
                    $user_info['profile_picture'] !== 'default.jpg' && 
                    file_exists($upload_dir . $user_info['profile_picture'])) {
                    unlink($upload_dir . $user_info['profile_picture']);
                }
                $profile_picture = $new_filename;
            } else {
                $error = "Gagal mengupload foto profil.";
            }
        }
    }
    
    // Validation
    if (empty($error) && (empty($name) || empty($phone) || empty($experience) || empty($specialization) || empty($bio) || empty($languages))) {
        $error = "Semua field wajib diisi.";
    }
    
    if (empty($error)) {
        $conn->begin_transaction();
        
        try {
            // Update users table
            $update_user = $conn->prepare("UPDATE users SET name = ?, phone = ?, profile_picture = ? WHERE id = ?");
            $update_user->bind_param("sssi", $name, $phone, $profile_picture, $user_id);
            $update_user->execute();
            
            if ($guide_info) {
                // Update existing guide profile
                $update_guide = $conn->prepare("
                    UPDATE guide 
                    SET specialization = ?, experience = ?, languages = ?, bio = ? 
                    WHERE user_id = ?
                ");
                $update_guide->bind_param("ssssi", $specialization, $experience, $languages, $bio, $user_id);
                $update_guide->execute();
            } else {
                // Create new guide profile
                $create_guide = $conn->prepare("
                    INSERT INTO guide (user_id, specialization, experience, languages, bio, status) 
                    VALUES (?, ?, ?, ?, ?, 'approved')
                ");
                $create_guide->bind_param("issss", $user_id, $specialization, $experience, $languages, $bio);
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

// Calculate average rating for guide
$rating = 0;
$total_feedback = 0;
if ($guide_info && $guide_id) { // Pastikan $guide_id ada
        $rating_query = $conn->prepare("
            SELECT AVG(rating) as avg_rating, COUNT(*) as total_feedback 
            FROM feedback 
            WHERE guide_id = ?
        ");
        $rating_query->bind_param("i", $guide_id); // <-- UBAH INI: Gunakan $guide_id
        $rating_query->execute();
        $rating_result = $rating_query->get_result()->fetch_assoc();
        $rating = round($rating_result['avg_rating'] ?? 0, 1);
        $total_feedback = $rating_result['total_feedback'] ?? 0;
        // <-- TAMBAHKAN BLOK KODE INI UNTUK MENGUPDATE RATING DI TABEL GUIDE
        // Update rating di tabel guide agar selalu sinkron
        $update_guide_rating = $conn->prepare("UPDATE guide SET rating = ? WHERE id = ?");
        $update_guide_rating->bind_param("di", $rating, $guide_id); // 'd' untuk double/decimal, 'i' untuk integer
        $update_guide_rating->execute();
        // AKHIR BLOK TAMBAHAN
    }
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
                <a href="trips.php"><i class="fas fa-route"></i> Trip Saya</a>
                <a href="bookings.php"><i class="fas fa-calendar-check"></i> Pesanan</a>
                <a href="schedule.php"><i class="fas fa-calendar-alt"></i> Jadwal</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>

        <!-- Main -->
        <main class="main">
            <div class="container">
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
                    <!-- Profile Info Card -->
                    <div class="profile-card">
                        <div class="profile-header">
                            <div class="profile-avatar">
                                <?php if (!empty($user_info['profile_picture']) && $user_info['profile_picture'] !== 'default.jpg'): ?>
                                    <img src="../assets/images/profiles/<?php echo htmlspecialchars($user_info['profile_picture']); ?>" alt="Profile">
                                <?php else: ?>
                                    <i class="fas fa-user-circle"></i>
                                <?php endif; ?>
                            </div>
                            <div class="profile-info">
                                <h2><?php echo htmlspecialchars($user_info['name']); ?></h2>
                                <p class="email"><?php echo htmlspecialchars($user_info['email']); ?></p>
                                <?php if ($guide_info): ?>
                                    <div class="profile-stats">
                                        <span class="stat-item">
                                            <i class="fas fa-star"></i> 
                                            <?php echo $rating; ?>/5.0 (<?php echo $total_feedback; ?> ulasan)
                                        </span>
                                        <span class="stat-item">
                                            <i class="fas fa-calendar"></i> 
                                            <?php echo htmlspecialchars($guide_info['experience']); ?>
                                        </span>
                                        <span class="status-badge status-<?php echo $guide_info['status']; ?>">
                                            <?php 
                                            $status_text = [
                                                'pending' => 'Menunggu Persetujuan',
                                                'approved' => 'Disetujui',
                                                'rejected' => 'Ditolak'
                                            ];
                                            echo $status_text[$guide_info['status']] ?? $guide_info['status'];
                                            ?>
                                        </span>
                                    </div>
                                <?php else: ?>
                                    <div class="profile-stats">
                                        <span class="status-badge status-pending">Belum Mendaftar sebagai Guide</span>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Profile Form Card -->
                    <div class="profile-form-card">
                        <h3><i class="fas fa-edit"></i> Edit Informasi Profile</h3>
                        <form method="POST" class="profile-form" enctype="multipart/form-data">
                            
                            <!-- Profile Picture Upload Section -->
                            <div class="form-section">
                                <h4>Foto Profil</h4>
                                <div class="profile-picture-upload">
                                    <div class="upload-preview">
                                        <?php if (!empty($user_info['profile_picture']) && $user_info['profile_picture'] !== 'default.jpg'): ?>
                                            <img src="../assets/images/profiles/<?php echo htmlspecialchars($user_info['profile_picture']); ?>" alt="Preview" id="preview-image">
                                        <?php else: ?>
                                            <div class="upload-placeholder" id="upload-placeholder">
                                                <i class="fas fa-camera"></i>
                                                <span>Pilih Foto</span>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                    <div class="upload-controls">
                                        <input type="file" id="profile_picture" name="profile_picture" accept="image/*" style="display: none;">
                                        <button type="button" class="btn btn-secondary" onclick="document.getElementById('profile_picture').click();">
                                            <i class="fas fa-upload"></i> Pilih Foto
                                        </button>
                                        <small>Format: JPG, PNG, GIF. Maksimal 5MB</small>
                                    </div>
                                </div>
                            </div>

                            <!-- Personal Information -->
                            <div class="form-section">
                                <h4>Informasi Personal</h4>
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
                                </div>
                            </div>

                            <!-- Guide Information -->
                            <div class="form-section">
                                <h4>Informasi Guide</h4>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="experience">Pengalaman</label>
                                        <input type="text" id="experience" name="experience" 
                                               placeholder="Contoh: 5 tahun sebagai guide hiking"
                                               value="<?php echo $guide_info ? htmlspecialchars($guide_info['experience']) : ''; ?>" required>
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
                            </div>

                                                        <div class="form-actions">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Simpan Profile
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <!-- Tambahkan form dan tabel service fee di bawah sini -->
            <div class="profile-form-card" style="margin-top:30px;">
                <h3><i class="fas fa-money-bill-wave"></i> Kelola Service Fee Guide per Gunung</h3>
                <form method="POST" class="service-fee-form" style="margin-bottom:20px;">
                    <input type="hidden" name="service_action" value="1">
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="mountain_id">Pilih Gunung</label>
                            <select name="mountain_id" id="mountain_id" required>
                                <option value="">-- Pilih Gunung --</option>
                                <?php foreach ($mountains as $m): ?>
                                    <option value="<?php echo $m['id']; ?>"><?php echo htmlspecialchars($m['name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="service_fee">Service Fee (Rp)</label>
                            <input type="number" name="service_fee" id="service_fee" min="10000" step="1000" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="service_desc">Deskripsi Layanan</label>
                        <input type="text" name="service_desc" id="service_desc" maxlength="255" placeholder="Contoh: Guide berpengalaman, include dokumentasi, dll" required>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Service Fee</button>
                    </div>
                </form>
                <h4>Daftar Service Fee Anda</h4>
                <div style="overflow-x:auto;">
                <table border="1" cellpadding="8" cellspacing="0" style="width:100%;border-collapse:collapse;">
                    <thead>
                        <tr style="background:#f8f9fa;">
                            <th>Gunung</th>
                            <th>Fee (Rp)</th>
                            <th>Deskripsi</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($guide_services) > 0): ?>
                            <?php foreach ($guide_services as $gs): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($gs['mountain_name']); ?></td>
                                    <td><?php echo number_format($gs['service_fee'],0,',','.'); ?></td>
                                    <td><?php echo htmlspecialchars($gs['description']); ?></td>
                                    <td><?php echo $gs['active'] ? 'Aktif' : 'Nonaktif'; ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr><td colspan="4" style="text-align:center;">Belum ada service fee yang diinput.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
                </div>
                <div style="margin-top:15px;">
                    <strong>Total Service Fee Terdaftar: </strong>Rp <?php echo number_format(array_sum(array_column($guide_services,'service_fee')),0,',','.'); ?>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Preview image when file is selected
        document.getElementById('profile_picture').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const placeholder = document.getElementById('upload-placeholder');
                    const previewImage = document.getElementById('preview-image');
                    
                    if (placeholder) {
                        placeholder.style.display = 'none';
                    }
                    
                    if (previewImage) {
                        previewImage.src = e.target.result;
                    } else {
                        const uploadPreview = document.querySelector('.upload-preview');
                        uploadPreview.innerHTML = '<img src="' + e.target.result + '" alt="Preview" id="preview-image">';
                    }
                };
                reader.readAsDataURL(file);
            }
        });
    </script>

    <style>
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        .success-message, .error-message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            max-width: 100%;
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
            gap: 25px;
            max-width: 100%;
        }

        .profile-card, .profile-form-card {
            background: white;
            border-radius: 12px;
            padding: 30px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.07);
            border: 1px solid #e9ecef;
        }

        .profile-header {
            display: flex;
            align-items: center;
            gap: 25px;
        }

        .profile-avatar {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, #f0f9f3, #e8f5e8);
            border: 3px solid #2e8b57;
            flex-shrink: 0;
        }

        .profile-avatar img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .profile-avatar i {
            font-size: 50px;
            color: #2e8b57;
        }

        .profile-info h2 {
            margin: 0 0 8px 0;
            color: #333;
            font-size: 24px;
        }

        .profile-info .email {
            margin: 0 0 15px 0;
            color: #666;
            font-size: 14px;
        }

        .profile-stats {
            display: flex;
            gap: 15px;
            font-size: 13px;
            flex-wrap: wrap;
        }

        .stat-item {
            background: #f8f9fa;
            padding: 6px 12px;
            border-radius: 6px;
            color: #2e8b57;
            border: 1px solid #e9ecef;
        }

        .status-badge {
            padding: 6px 12px;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-approved {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .status-rejected {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .profile-form-card h3 {
            margin: 0 0 25px 0;
            color: #333;
            border-bottom: 2px solid #2e8b57;
            padding-bottom: 12px;
            font-size: 20px;
        }

        .form-section {
            margin-bottom: 30px;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #2e8b57;
        }

        .form-section h4 {
            margin: 0 0 20px 0;
            color: #2e8b57;
            font-size: 16px;
            font-weight: 600;
        }

        .profile-picture-upload {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .upload-preview {
            width: 120px;
            height: 120px;
            border-radius: 12px;
            overflow: hidden;
            border: 2px dashed #ddd;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            flex-shrink: 0;
        }

        .upload-preview img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .upload-placeholder {
            text-align: center;
            color: #666;
        }

        .upload-placeholder i {
            font-size: 30px;
            margin-bottom: 8px;
            display: block;
        }

        .upload-placeholder span {
            font-size: 14px;
        }

        .upload-controls {
            flex: 1;
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
            color: #333;
            font-size: 14px;
        }

        .form-group input, .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s;
            box-sizing: border-box;
        }

        .form-group input:focus, .form-group textarea:focus {
            outline: none;
            border-color: #2e8b57;
            box-shadow: 0 0 0 3px rgba(46, 139, 87, 0.1);
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
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
        }

        .btn-primary {
            background-color: #2e8b57;
            color: white;
        }

        .btn-primary:hover {
            background-color: #246b46;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(46, 139, 87, 0.3);
        }

        .btn-secondary {
            background-color: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #545b62;
        }

        @media (max-width: 768px) {
            .container {
                padding: 15px;
            }
            
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .profile-header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .profile-picture-upload {
                flex-direction: column;
                text-align: center;
            }
            
            .profile-stats {
                justify-content: center;
            }
            
            .form-section {
                padding: 15px;
            }
        }

        @media (max-width: 480px) {
            .profile-avatar {
                width: 80px;
                height: 80px;
            }
            
            .profile-avatar i {
                font-size: 40px;
            }
            
            .upload-preview {
                width: 100px;
                height: 100px;
            }
        }
    </style>
</body>
</html>

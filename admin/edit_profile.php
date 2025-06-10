<?php
session_start();
require_once '../config.php';

// Cek login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

$adminId = $_SESSION['user_id'];
$query = $conn->prepare("SELECT name, email, phone, role, profile_image FROM users WHERE id = ?");
$query->bind_param("i", $adminId);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

// Jika form disubmit (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $current_password = $_POST['current_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    
    $error = null;
    $success = null;
    
    // Validasi password jika diisi
    if (!empty($current_password)) {
        $check_password = $conn->prepare("SELECT password FROM users WHERE id = ?");
        $check_password->bind_param("i", $adminId);
        $check_password->execute();
        $result = $check_password->get_result();
        $user_data = $result->fetch_assoc();
        
        if (!password_verify($current_password, $user_data['password'])) {
            $error = "Password saat ini tidak sesuai";
        } elseif (empty($new_password) || empty($confirm_password)) {
            $error = "Password baru dan konfirmasi password harus diisi";
        } elseif ($new_password !== $confirm_password) {
            $error = "Password baru dan konfirmasi password tidak sesuai";
        } elseif (strlen($new_password) < 6) {
            $error = "Password baru minimal 6 karakter";
        }
    }
    
    // Upload foto profil jika ada
    $profile_image = $user['profile_image']; // Default ke foto yang ada
    if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
        $max_size = 2 * 1024 * 1024; // 2MB
        
        if (!in_array($_FILES['profile_image']['type'], $allowed_types)) {
            $error = "Tipe file tidak didukung. Gunakan JPG, JPEG, atau PNG";
        } elseif ($_FILES['profile_image']['size'] > $max_size) {
            $error = "Ukuran file terlalu besar. Maksimal 2MB";
        } else {
            $upload_dir = '../assets/images/profiles/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }
            
            $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
            $new_filename = 'profile_' . $adminId . '_' . time() . '.' . $file_extension;
            $upload_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                // Hapus foto lama jika ada
                if ($user['profile_image'] && file_exists($upload_dir . $user['profile_image'])) {
                    unlink($upload_dir . $user['profile_image']);
                }
                $profile_image = $new_filename;
            } else {
                $error = "Gagal mengupload foto profil";
            }
        }
    }
    
    // Update data jika tidak ada error
    if (!$error) {
        $sql = "UPDATE users SET name = ?, email = ?, phone = ?";
        $params = [$name, $email, $phone];
        $types = "sss";
        
        // Tambahkan password jika diubah
        if (!empty($new_password)) {
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $sql .= ", password = ?";
            $params[] = $hashed_password;
            $types .= "s";
        }
        
        // Tambahkan foto profil jika diupload
        if ($profile_image !== $user['profile_image']) {
            $sql .= ", profile_image = ?";
            $params[] = $profile_image;
            $types .= "s";
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $adminId;
        $types .= "i";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        
        if ($stmt->execute()) {
            $success = "Profil berhasil diperbarui";
            // Refresh data user
            $query = $conn->prepare("SELECT name, email, phone, role, profile_image FROM users WHERE id = ?");
            $query->bind_param("i", $adminId);
            $query->execute();
            $result = $query->get_result();
            $user = $result->fetch_assoc();
        } else {
            $error = "Gagal memperbarui profil: " . $conn->error;
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Profil</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="../assets/css/users.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
<div class="admin-layout" style="display:flex;min-height:100vh;">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="nav-section-title">Admin Panel</div>
        <ul class="nav-links">
            <li><a href="index.php" class="nav-link"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li><a href="users.php" class="nav-link"><i class="fas fa-users"></i> Pengguna</a></li>
            <li><a href="guides.php" class="nav-link"><i class="fas fa-map-signs"></i> Guide</a></li>
            <li><a href="mountains.php" class="nav-link"><i class="fas fa-mountain"></i> Gunung</a></li>
            <li><a href="trips.php" class="nav-link"><i class="fas fa-route"></i> Trip</a></li>
            <li><a href="bookings.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Booking</a></li>
            <li><a href="feedback.php" class="nav-link"><i class="fas fa-comment-dots"></i> Feedback</a></li>
            <li><a href="profile.php" class="nav-link active"><i class="fas fa-user-cog"></i> Profil</a></li>
            <li><a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="admin-main" style="flex:1;padding:30px;overflow-x:auto;">
        <div class="admin-header">
            <h1>Edit Profil</h1>
            <a href="profile.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <!-- Form edit profil -->
        <div class="admin-form-container" style="max-width: 600px;">
            <form method="POST" enctype="multipart/form-data" class="admin-form">
                <div class="profile-image-upload">
                    <div class="current-image">
                        <?php if ($user['profile_image']): ?>
                            <img src="../assets/images/profiles/<?php echo htmlspecialchars($user['profile_image']); ?>" alt="Profile Image">
                        <?php else: ?>
                            <i class="fas fa-user-circle"></i>
                        <?php endif; ?>
                    </div>
                    <div class="upload-controls">
                        <label for="profile_image" class="btn btn-secondary">
                            <i class="fas fa-camera"></i> Pilih Foto
                        </label>
                        <input type="file" id="profile_image" name="profile_image" accept="image/*" style="display: none;">
                        <small>Format: JPG, JPEG, PNG (Maks. 2MB)</small>
                    </div>
                </div>

                <div class="form-group">
                    <label for="name">Nama</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">No. Telepon</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                </div>

                <div class="form-section">
                    <h3>Ubah Password</h3>
                    <div class="form-group">
                        <label for="current_password">Password Saat Ini</label>
                        <input type="password" id="current_password" name="current_password">
                    </div>

                    <div class="form-group">
                        <label for="new_password">Password Baru</label>
                        <input type="password" id="new_password" name="new_password">
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password Baru</label>
                        <input type="password" id="confirm_password" name="confirm_password">
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan Perubahan</button>
                    <a href="profile.php" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
                </div>
            </form>
        </div>
    </main>
</div>

<style>
.profile-image-upload {
    text-align: center;
    margin-bottom: 30px;
}

.current-image {
    width: 150px;
    height: 150px;
    margin: 0 auto 20px;
    border-radius: 50%;
    overflow: hidden;
    background: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
}

.current-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.current-image i {
    font-size: 100px;
    color: #ccc;
}

.upload-controls {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 10px;
}

.upload-controls small {
    color: #666;
}

.form-section {
    margin-top: 30px;
    padding-top: 20px;
    border-top: 1px solid #eee;
}

.form-section h3 {
    margin-bottom: 20px;
    color: #333;
    font-size: 18px;
}

.alert {
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.alert-danger {
    background: #fee;
    color: #c00;
    border: 1px solid #fcc;
}

.alert-success {
    background: #efe;
    color: #0c0;
    border: 1px solid #cfc;
}
</style>

<script>
// Preview image sebelum upload
document.getElementById('profile_image').addEventListener('change', function(e) {
    if (e.target.files && e.target.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const img = document.querySelector('.current-image img') || document.createElement('img');
            img.src = e.target.result;
            if (!document.querySelector('.current-image img')) {
                document.querySelector('.current-image').innerHTML = '';
                document.querySelector('.current-image').appendChild(img);
            }
        }
        reader.readAsDataURL(e.target.files[0]);
    }
});
</script>
</body>
</html> 
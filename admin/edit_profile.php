<?php
session_start();
require_once '../config.php';

// Cek login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

$adminId = $_SESSION['user_id'];
$error = '';
$success = '';

// Ambil data admin
$query = $conn->prepare("SELECT name, email, phone, profile_picture FROM users WHERE id = ?");
$query->bind_param("i", $adminId);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

// Proses form edit profil
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $profile_picture = $user['profile_picture']; // Default ke gambar yang ada

    // Validasi input
    if (empty($name) || empty($email) || empty($phone)) {
        $error = "Semua field harus diisi.";
    } else {
        // Cek apakah email sudah digunakan user lain
        $check_email = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $check_email->bind_param("si", $email, $adminId);
        $check_email->execute();
        if ($check_email->get_result()->num_rows > 0) {
            $error = "Email sudah digunakan oleh user lain.";
        } else {
            // Upload foto profil jika ada
            if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
                $allowed = ['jpg', 'jpeg', 'png'];
                $filename = $_FILES['profile_picture']['name'];
                $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
                
                if (in_array($ext, $allowed)) {
                    $new_filename = uniqid() . '.' . $ext;
                    $upload_path = '../assets/images/profiles/' . $new_filename;
                    
                    // Buat direktori jika belum ada
                    $upload_dir = '../assets/images/profiles';
                    if (!file_exists($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $upload_path)) {
                        // Hapus foto lama jika ada
                        if ($user['profile_picture'] && file_exists('../assets/images/profiles/' . $user['profile_picture'])) {
                            unlink('../assets/images/profiles/' . $user['profile_picture']);
                        }
                        $profile_picture = $new_filename;
                    } else {
                        $error = "Gagal mengupload foto profil.";
                    }
                } else {
                    $error = "Format file tidak didukung. Gunakan JPG, JPEG, atau PNG.";
                }
            }

            if (!$error) {
                // Update data admin
                $sql = "UPDATE users SET name = ?, email = ?, phone = ?, profile_picture = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssi", $name, $email, $phone, $profile_picture, $adminId);
                
                if ($stmt->execute()) {
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    header("Location: profile.php?success=Profil berhasil diperbarui");
                    exit();
                } else {
                    $error = "Gagal memperbarui profil: " . $conn->error;
                }
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Profil - Admin Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="../assets/css/trips.css" />
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
            <li><a href="lihat_pembayaran.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Lihat Pembayaran</a></li>
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

        <?php if ($error): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="profile-container">
            <div class="profile-card">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <?php if ($user['profile_picture']): ?>
                                <img src="../assets/images/profiles/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Foto Profil" id="preview-image">
                            <?php else: ?>
                                <i class="fas fa-user-circle" id="preview-icon"></i>
                            <?php endif; ?>
                        </div>
                        <div class="profile-upload">
                            <label for="profile_picture" class="btn btn-edit">
                                <i class="fas fa-camera"></i>
                                Pilih Foto
                            </label>
                            <input type="file" id="profile_picture" name="profile_picture" accept="image/jpeg,image/png" style="display: none;" onchange="previewImage(this)">
                        </div>
                    </div>

                    <div class="profile-info">
                        <div class="info-section">
                            <h3>Informasi Pribadi</h3>
                            
                            <div class="form-group">
                                <label for="name">Nama:</label>
                                <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="email">Email:</label>
                                <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="phone">Nomor Telepon:</label>
                                <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                            </div>
                        </div>
                    </div>

                    <div class="profile-actions">
                        <button type="submit" class="btn btn-edit">
                            <i class="fas fa-save"></i>
                            Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</div>

<script>
function previewImage(input) {
    const preview = document.getElementById('preview-image');
    const icon = document.getElementById('preview-icon');
    
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        
        reader.onload = function(e) {
            if (!preview) {
                // Buat elemen img jika belum ada
                const img = document.createElement('img');
                img.id = 'preview-image';
                img.alt = 'Foto Profil';
                input.parentElement.querySelector('.profile-avatar').appendChild(img);
                icon.style.display = 'none';
            }
            preview.src = e.target.result;
        }
        
        reader.readAsDataURL(input.files[0]);
    }
}
</script>
</body>
</html> 
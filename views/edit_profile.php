<?php
session_start();
require_once '../config.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$error = '';
$success = '';

// Ambil data user
$query = $conn->prepare("SELECT name, email, phone, profile_picture FROM users WHERE id = ?");
$query->bind_param("i", $userId);
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
        $check_email->bind_param("si", $email, $userId);
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
                // Update data user
                $sql = "UPDATE users SET name = ?, email = ?, phone = ?, profile_picture = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("ssssi", $name, $email, $phone, $profile_picture, $userId);
                
                if ($stmt->execute()) {
                    $_SESSION['user_name'] = $name;
                    $_SESSION['user_email'] = $email;
                    $success = "Profil berhasil diperbarui";
                    
                    // Refresh data user
                    $query = $conn->prepare("SELECT name, email, phone, profile_picture FROM users WHERE id = ?");
                    $query->bind_param("i", $userId);
                    $query->execute();
                    $result = $query->get_result();
                    $user = $result->fetch_assoc();
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
    <title>Edit Profil - Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <div class="form-container">
            <h1>Edit Profil</h1>
            
            <?php if ($error): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success"><?php echo $success; ?></div>
            <?php endif; ?>

            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Nama:</label>
                    <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="email">Email:</label>
                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="phone">Telepon:</label>
                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="profile_picture">Foto Profil:</label>
                    <?php if ($user['profile_picture']): ?>
                        <div class="current-image">
                            <img src="../assets/images/profiles/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Foto Profil" style="width:100px;height:100px;object-fit:cover;border-radius:50%;">
                        </div>
                    <?php endif; ?>
                    <input type="file" id="profile_picture" name="profile_picture" accept="image/jpeg,image/png">
                    <small>Format yang didukung: JPG, JPEG, PNG. Maksimal 2MB</small>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="dashboard-user.php" class="btn btn-secondary">Kembali</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html> 
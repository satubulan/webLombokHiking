<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $rating = floatval($_POST['rating']);
    $languages = $conn->real_escape_string($_POST['languages']);
    $password = password_hash($conn->real_escape_string($_POST['password']), PASSWORD_DEFAULT);
    $email = $conn->real_escape_string($_POST['email']);
    $active = isset($_POST['active']) ? 1 : 0;

    // Handle file upload
    $target_dir = "../assets/images/guides/";
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $image_name = time() . '_' . basename($_FILES["image"]["name"]);
    $target_file = $target_dir . $image_name;
    $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));

    // Check if image file is actual image
    $check = getimagesize($_FILES["image"]["tmp_name"]);
    if($check !== false) {
        // Check file size (max 5MB)
        if ($_FILES["image"]["size"] <= 5000000) {
            // Allow certain file formats
            if($imageFileType == "jpg" || $imageFileType == "png" || $imageFileType == "jpeg") {
                if (move_uploaded_file($_FILES["image"]["tmp_name"], $target_file)) {
                    $image_url = "guides/" . $image_name;
                    
                    // Insert into database
                    $sql = "INSERT INTO guides (name, rating, languages, password, email, image_url, active) 
                            VALUES ('$name', $rating, '$languages', '$password', '$email', '$image_url', $active)";
                    
                    if ($conn->query($sql)) {
                        header("Location: guides.php");
                        exit();
                    } else {
                        $error = "Error: " . $conn->error;
                    }
                } else {
                    $error = "Maaf, terjadi kesalahan saat mengupload file.";
                }
            } else {
                $error = "Maaf, hanya file JPG, JPEG & PNG yang diperbolehkan.";
            }
        } else {
            $error = "Maaf, ukuran file terlalu besar (max 5MB).";
        }
    } else {
        $error = "File bukan gambar.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Guide - Admin Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/guide.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="nav-section-title">Admin Panel</div>
            <ul class="nav-links">
                <li><a href="index.php" class="nav-link"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                <li><a href="users.php" class="nav-link"><i class="fas fa-users"></i> Pengguna</a></li>
                <li><a href="guides.php" class="nav-link active"><i class="fas fa-map-signs"></i> Guide</a></li>
                <li><a href="mountains.php" class="nav-link"><i class="fas fa-mountain"></i> Gunung</a></li>
                <li><a href="trips.php" class="nav-link"><i class="fas fa-route"></i> Trip</a></li>
                <li><a href="lihat_pembayaran.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Lihat Pembayaran</a></li>
                <li><a href="feedback.php" class="nav-link"><i class="fas fa-comment-dots"></i> Feedback</a></li>
                <li><a href="profile.php" class="nav-link"><i class="fas fa-user-cog"></i> Profil</a></li>
                <li><a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <div class="admin-header">
                <h1>Tambah Guide Baru</h1>
                <a href="guides.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="admin-form-container">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Nama Guide:</label>
                        <input type="text" id="name" name="name" placeholder="Masukkan nama lengkap guide" required>
                    </div>

                    <div class="form-group">
                        <label for="rating">Rating:</label>
                        <input type="number" id="rating" name="rating" step="0.1" min="0" max="5" placeholder="Contoh: 4.5" required>
                    </div>

                    <div class="form-group">
                        <label for="languages">Bahasa:</label>
                        <input type="text" id="languages" name="languages" placeholder="Contoh: Indonesia, Inggris" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" placeholder="Masukkan alamat email" required>
                    </div>

                    <div class="form-group">
                        <label for="image">Foto Guide:</label>
                        <input type="file" id="image" name="image" accept="image/*">
                        <small>Biarkan kosong jika tidak ingin mengubah gambar. Format: JPG, JPEG, PNG (Max 5MB)</small>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="active" checked>
                            Status Aktif
                        </label>
                    </div>

                    <div class="form-group form-group-actions">
                        <button type="submit" class="btn btn-primary">Tambah Guide</button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html> 
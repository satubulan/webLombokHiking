<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $height = intval($_POST['height']);

    // Handle file upload
    $target_dir = "../assets/images/mountains/";
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
                    $image_url = "mountains/" . $image_name;
                    
                    // Insert into database
                    $sql = "INSERT INTO mountains (name, description, height, image_url) 
                            VALUES ('$name', '$description', $height, '$image_url')";
                    
                    if ($conn->query($sql)) {
                        header("Location: mountains.php");
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
    <title>Tambah Gunung - Admin Lombok Hiking</title>
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
                <li><a href="guides.php" class="nav-link"><i class="fas fa-map-signs"></i> Guide</a></li>
                <li><a href="mountains.php" class="nav-link active"><i class="fas fa-mountain"></i> Gunung</a></li>
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
                <h1>Tambah Gunung</h1>
            </div>

            <?php if (isset($error)): ?>
                <div class="alert alert-danger"><?php echo $error; ?></div>
            <?php endif; ?>

            <div class="admin-form-container">
                <form method="POST" action="" enctype="multipart/form-data">
                    <div class="form-group">
                        <label for="name">Nama Gunung:</label>
                        <input type="text" id="name" name="name" placeholder="Masukkan nama gunung" required>
                    </div>

                    <div class="form-group">
                        <label for="description">Deskripsi:</label>
                        <textarea id="description" name="description" rows="4" placeholder="Tulis deskripsi singkat tentang gunung" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="height">Tinggi (meter):</label>
                        <input type="number" id="height" name="height" min="0" placeholder="Contoh: 3726" required>
                    </div>

                    <div class="form-group">
                        <label for="image">Foto Gunung:</label>
                        <input type="file" id="image" name="image" accept="image/*">
                        <small>Format: JPG, JPEG, PNG (Max 5MB)</small>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" name="active" checked>
                            Status Aktif
                        </label>
                    </div>

                    <div class="form-group form-group-actions">
                        <button type="submit" class="btn btn-primary">Tambah Gunung</button>
                        <a href="mountains.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html> 
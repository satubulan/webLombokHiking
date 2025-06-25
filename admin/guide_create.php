<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

// Fungsi untuk buat UUID (unik 36 karakter)
function generateUUIDv4() {
    $data = random_bytes(16);
    $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // versi 4
    $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // varian
    return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
}

$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($conn->real_escape_string($_POST['password']), PASSWORD_DEFAULT);
    $phone = $conn->real_escape_string($_POST['phone']);
    $specialization = isset($_POST['specialization']) ? $conn->real_escape_string($_POST['specialization']) : '';
    $experience = isset($_POST['experience']) ? $conn->real_escape_string($_POST['experience']) : '';
    $languages = isset($_POST['languages']) ? $conn->real_escape_string($_POST['languages']) : '';
    $bio = isset($_POST['bio']) ? $conn->real_escape_string($_POST['bio']) : '';

    // Cek apakah email sudah terdaftar
    $stmtCheck = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmtCheck->bind_param("s", $email);
    $stmtCheck->execute();
    $stmtCheck->store_result();
    if ($stmtCheck->num_rows > 0) {
        $stmtCheck->close();
        header("Location: guide_create.php?error=" . urlencode("Email sudah terdaftar, silakan gunakan email lain."));
        exit();
    } else {
        $stmtCheck->close();
        // Insert data ke tabel users
        $user_sql = "INSERT INTO users (name, email, password, role, phone) VALUES (?, ?, ?, 'guide', ?)";
        $user_stmt = $conn->prepare($user_sql);
        $user_stmt->bind_param("ssss", $name, $email, $password, $phone);
        if ($user_stmt->execute()) {
            $user_id = $conn->insert_id;
            // Insert data ke tabel guide
            $guide_sql = "INSERT INTO guide (user_id, specialization, experience, languages, bio, status) VALUES (?, ?, ?, ?, ?, 'pending')";
            $guide_stmt = $conn->prepare($guide_sql);
            $guide_stmt->bind_param("issss", $user_id, $specialization, $experience, $languages, $bio);
            if ($guide_stmt->execute()) {
                header("Location: guides.php?message=" . urlencode("Guide berhasil ditambahkan!"));
                exit();
            } else {
                $conn->query("DELETE FROM users WHERE id = $user_id");
                header("Location: guide_create.php?error=" . urlencode("Error menambahkan guide: " . $guide_stmt->error));
                exit();
            }
            $guide_stmt->close();
        } else {
            header("Location: guide_create.php?error=" . urlencode("Error menambahkan pengguna baru: " . $user_stmt->error));
            exit();
        }
        $user_stmt->close();
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
                <li><a href="feedback.php" class="nav-link"><i class="fas fa-comments"></i> Feedback</a></li>
                <li><a href="lihat_pembayaran.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Lihat Pembayaran</a></li>
                <li><a href="notifikasi.php" class="nav-link"><i class="fas fa-bell"></i> Notifikasi</a></li>
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

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($_GET['error']); ?></div>
            <?php endif; ?>

            <div class="admin-form-container">
                <form method="POST" action="">
                    <div class="form-group">
                        <label for="name">Nama Lengkap Guide:</label>
                        <input type="text" id="name" name="name" placeholder="Masukkan nama lengkap guide" required>
                    </div>

                    <div class="form-group">
                        <label for="email">Email Guide:</label>
                        <input type="email" id="email" name="email" placeholder="Masukkan alamat email guide" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Password Guide:</label>
                        <input type="password" id="password" name="password" placeholder="Minimal 6 karakter" required minlength="6">
                    </div>

                    <div class="form-group">
                        <label for="phone">Nomor Telepon:</label>
                        <input type="text" id="phone" name="phone" placeholder="Masukkan nomor telepon guide" required>
                    </div>

                    <div class="form-group">
                        <label for="specialization">Spesialisasi:</label>
                        <input type="text" id="specialization" name="specialization" placeholder="Contoh: Gunung Rinjani, Trekking, dll" required>
                    </div>

                    <div class="form-group">
                        <label for="experience">Pengalaman:</label>
                        <textarea id="experience" name="experience" placeholder="Ceritakan pengalaman guide" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="languages">Bahasa:</label>
                        <input type="text" id="languages" name="languages" placeholder="Contoh: Indonesia, Inggris" required>
                    </div>

                    <div class="form-group">
                        <label for="bio">Bio:</label>
                        <textarea id="bio" name="bio" placeholder="Deskripsi singkat tentang guide" required></textarea>
                    </div>

                    <div class="form-group form-group-actions">
                        <button type="submit" class="btn btn-primary">Tambah Guide</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>

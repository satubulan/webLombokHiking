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

    // Generate UUID untuk users.id
    $user_id = generateUUIDv4();

    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $password = password_hash($conn->real_escape_string($_POST['password']), PASSWORD_DEFAULT);
    $phone = $conn->real_escape_string($_POST['phone']);

    // Cek apakah email sudah terdaftar
    $stmtCheck = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $stmtCheck->bind_param("s", $email);
    $stmtCheck->execute();
    $stmtCheck->store_result();
    if ($stmtCheck->num_rows > 0) {
        $error = "Email sudah terdaftar, silakan gunakan email lain.";
    } else {
        $stmtCheck->close();

        // Insert data ke tabel users
        $user_sql = "INSERT INTO users (id, name, email, password, role, phone) VALUES (?, ?, ?, ?, 'guide', ?)";
        $user_stmt = $conn->prepare($user_sql);
        $user_stmt->bind_param("sssss", $user_id, $name, $email, $password, $phone);

        if ($user_stmt->execute()) {

            // Generate UUID untuk guides.id
            $guide_id = generateUUIDv4();

            // Insert data ke tabel guides
            $guide_sql = "INSERT INTO guides (id, user_id, name) VALUES (?, ?, ?)";
            $guide_stmt = $conn->prepare($guide_sql);
            $guide_stmt->bind_param("sss", $guide_id, $user_id, $name);

            if ($guide_stmt->execute()) {
                $message = "Guide berhasil ditambahkan!";
                header("Location: guides.php?message=" . urlencode($message));
                exit();
            } else {
                // Jika insert guide gagal, hapus user yg sudah dibuat supaya data konsisten
                $conn->query("DELETE FROM users WHERE id = '$user_id'");
                $error = "Error menambahkan guide: " . $guide_stmt->error;
            }

            $guide_stmt->close();

        } else {
            $error = "Error menambahkan pengguna baru: " . $user_stmt->error;
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

            <?php if (!empty($message)): ?>
                <div class="alert alert-success"><?php echo htmlspecialchars($message); ?></div>
            <?php endif; ?>
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
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

                    <div class="form-group form-group-actions">
                        <button type="submit" class="btn btn-primary">Tambah Guide</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>

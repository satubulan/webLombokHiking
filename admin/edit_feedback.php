<?php
session_start();
require_once '../config.php';

// Cek login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

// Ambil data feedback berdasarkan ID
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM feedback WHERE id = $id");
    if ($result->num_rows === 0) {
        header("Location: feedback.php");
        exit();
    }
    $feedback = $result->fetch_assoc();
} else {
    header("Location: feedback.php");
    exit();
}

// Jika form disubmit (POST), update data feedback
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $message = $conn->real_escape_string($_POST['message']);

    $sql = "UPDATE feedback SET message = '$message' WHERE id = $id";
    
    if (!$conn->query($sql)) {
        $error = "Error: " . $conn->error;
    } else {
        header("Location: feedback.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Feedback</title>
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
            <li><a href="lihat_pembayaran.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Lihat Pembayaran</a></li>
            <li><a href="feedback.php" class="nav-link active"><i class="fas fa-comment-dots"></i> Feedback</a></li>
            <li><a href="profile.php" class="nav-link"><i class="fas fa-user-cog"></i> Profil</a></li>
            <li><a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="admin-main" style="flex:1;padding:30px;overflow-x:auto;">
        <div class="admin-header">
            <h1>Edit Feedback</h1>
            <a href="feedback.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Form edit feedback -->
        <div class="admin-form-container">
            <form method="POST" class="admin-form">
                <div class="form-group">
                    <label for="message">Pesan</label>
                    <textarea name="message" id="message" rows="5" required><?php echo htmlspecialchars($feedback['message']); ?></textarea>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                    <a href="feedback.php" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html> 
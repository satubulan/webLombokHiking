<?php
include '../config.php';
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../views/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$msg = '';

// Cek apakah user sudah pernah mengajukan
$sql_cek = "SELECT * FROM guide_applications WHERE user_id = ? ORDER BY id DESC LIMIT 1";
$stmt_cek = $conn->prepare($sql_cek);
$stmt_cek->bind_param('i', $user_id);
$stmt_cek->execute();
$res_cek = $stmt_cek->get_result();
$pengajuan = $res_cek->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$pengajuan) {
    $nama = $_POST['nama'];
    $email = $_POST['email'];
    $no_hp = $_POST['no_hp'];
    $cv = $_FILES['cv'];
    $ktp = $_FILES['ktp'];
    $foto = $_FILES['foto'];
    $target_dir = '../uploads/guide_applications/';
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    $cv_name = 'cv_' . time() . '_' . basename($cv['name']);
    $ktp_name = 'ktp_' . time() . '_' . basename($ktp['name']);
    $foto_name = 'foto_' . time() . '_' . basename($foto['name']);
    move_uploaded_file($cv['tmp_name'], $target_dir . $cv_name);
    move_uploaded_file($ktp['tmp_name'], $target_dir . $ktp_name);
    move_uploaded_file($foto['tmp_name'], $target_dir . $foto_name);
    $status = 'pending';
    $tanggal = date('Y-m-d H:i:s');
    $sql = "INSERT INTO guide_applications (user_id, nama, email, no_hp, cv_file, ktp_file, foto_file, status, tanggal_pengajuan) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('issssssss', $user_id, $nama, $email, $no_hp, $cv_name, $ktp_name, $foto_name, $status, $tanggal);
    if ($stmt->execute()) {
        $msg = 'Pengajuan berhasil dikirim!';
        // Refresh pengajuan
        $pengajuan = [
            'status' => 'pending',
            'nama' => $nama
        ];
    } else {
        $msg = 'Gagal mengirim pengajuan.';
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Ajukan Diri Jadi Guide</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
<div class="admin-container">
    <aside class="sidebar">
        <div class="logo">
            <i class="fas fa-mountain"></i>
            Lombok Hiking
        </div>
        <nav>
            <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="profile.php"><i class="fas fa-user"></i> Profil Saya</a>
            <a href="booking.php"><i class="fas fa-calendar-plus"></i> Booking Trip</a>
            <a href="status_pembayaran.php"><i class="fas fa-credit-card"></i> Status Pembayaran</a>
            <a href="paket_saya.php"><i class="fas fa-hiking"></i> Paket Saya</a>
            <a href="ajukan_guide.php" class="active"><i class="fas fa-user-plus"></i> Ajukan Diri Jadi Guide</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </nav>
    </aside>
    <main class="main">
        <div class="page-header">
            <h2>Ajukan Diri Jadi Guide</h2>
        </div>
        <div class="form-container" style="max-width:500px;margin:auto;">
            <?php if ($msg) echo '<div class="alert alert-success">' . $msg . '</div>'; ?>
            <?php if ($pengajuan && in_array($pengajuan['status'], ['pending','accepted'])): ?>
                <div class="alert alert-info" style="margin-top:30px;">
                    <i class="fas fa-info-circle"></i>
                    <?php if ($pengajuan['status'] === 'pending'): ?>
                        Pengajuan anda sedang diproses. Mohon tunggu konfirmasi dari admin.
                    <?php elseif ($pengajuan['status'] === 'accepted'): ?>
                        Selamat, pengajuan anda telah diterima! Anda sudah menjadi guide.
                    <?php endif; ?>
                </div>
            <?php else: ?>
            <form method="post" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Nama:</label>
                    <input type="text" name="nama" required class="form-control">
                </div>
                <div class="form-group">
                    <label>Email:</label>
                    <input type="email" name="email" required class="form-control">
                </div>
                <div class="form-group">
                    <label>No HP:</label>
                    <input type="text" name="no_hp" required class="form-control">
                </div>
                <div class="form-group">
                    <label>Upload CV (PDF):</label>
                    <input type="file" name="cv" accept="application/pdf" required class="form-control">
                </div>
                <div class="form-group">
                    <label>Upload Fotocopy KTP (JPG/PNG):</label>
                    <input type="file" name="ktp" accept="image/*" required class="form-control">
                </div>
                <div class="form-group">
                    <label>Upload Foto Diri (JPG/PNG):</label>
                    <input type="file" name="foto" accept="image/*" required class="form-control">
                </div>
                <button type="submit" class="btn btn-primary" style="width:100%;margin-top:15px;">Kirim Pengajuan</button>
            </form>
            <?php endif; ?>
        </div>
    </main>
</div>
</body>
</html> 
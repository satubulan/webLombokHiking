<?php
session_start();
require_once '../config.php';
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

// Proses aksi verifikasi/tolak
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = intval($_GET['id']);
    if ($_GET['action'] === 'accept') {
        // Ambil data pengajuan
        $q = $conn->query("SELECT * FROM guide_applications WHERE id=$id");
        $app = $q ? $q->fetch_assoc() : null;
        if ($app) {
            $user_id = $app['user_id'];
            $foto = $app['foto_file'];
            // Update status pengajuan
            $conn->query("UPDATE guide_applications SET status='accepted' WHERE id=$id");
            // Update role user
            $conn->query("UPDATE users SET role='guide' WHERE id=$user_id");
            // Cek apakah sudah ada di tabel guide
            $cek_guide = $conn->query("SELECT id FROM guide WHERE user_id=$user_id");
            if ($cek_guide->num_rows == 0) {
                // Insert ke tabel guide
                $conn->query("INSERT INTO guide (user_id, profile_picture, rating, specialization, experience, languages, bio, status) VALUES ($user_id, '$foto', 0, '', '', '', '', 'approved')");
            } else {
                // Jika sudah ada, update status dan foto
                $conn->query("UPDATE guide SET status='approved', profile_picture='$foto' WHERE user_id=$user_id");
            }
        }
    } elseif ($_GET['action'] === 'reject') {
        $conn->query("UPDATE guide_applications SET status='rejected' WHERE id=$id");
    }
    header('Location: verifikasi_guide.php');
    exit();
}

// Ambil data pengajuan guide
$sql = "SELECT ga.*, u.name as user_name, u.email as user_email FROM guide_applications ga JOIN users u ON ga.user_id = u.id ORDER BY ga.tanggal_pengajuan DESC";
$result = $conn->query($sql);
$guides = $result ? $result->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Verifikasi Guide - Admin Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        .admin-table th, .admin-table td { vertical-align: top; }
        .btn-verif { background: #2e8b57; color: #fff; border: none; padding: 6px 14px; border-radius: 6px; font-weight: 600; cursor: pointer; margin-right: 4px; }
        .btn-tolak { background: #dc3545; color: #fff; border: none; padding: 6px 14px; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .btn-detail { background: #f4f6f9; color: #2e8b57; border: 1px solid #2e8b57; padding: 6px 14px; border-radius: 6px; font-weight: 600; cursor: pointer; margin-right: 4px; }
        .status-badge { padding: 4px 10px; border-radius: 12px; font-size: 13px; font-weight: 600; }
        .status-pending { background: #e6f9ed; color: #10b981; }
        .status-accepted { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
    </style>
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
            <li><a href="verifikasi_guide.php" class="nav-link active"><i class="fas fa-user-check"></i> Verifikasi Guide</a></li>
            <li><a href="mountains.php" class="nav-link"><i class="fas fa-mountain"></i> Gunung</a></li>
            <li><a href="trips.php" class="nav-link"><i class="fas fa-route"></i> Trip</a></li>
            <li><a href="feedback.php" class="nav-link"><i class="fas fa-comments"></i> Feedback</a></li>
            <li><a href="lihat_pembayaran.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Lihat Pembayaran</a></li>
            <li><a href="notifikasi.php" class="nav-link"><i class="fas fa-bell"></i> Notifikasi</a></li>
            <li><a href="profile.php" class="nav-link"><i class="fas fa-user-cog"></i> Profil</a></li>
            <li><a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>
    <main class="admin-main">
        <div class="admin-header">
            <h1>Verifikasi Pengajuan Guide</h1>
        </div>
        <div class="admin-table-container">
            <table class="admin-table" border="1" cellpadding="6" cellspacing="0" style="width:100%;background:#fff;">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>No HP</th>
                        <th>Status</th>
                        <th>CV</th>
                        <th>KTP</th>
                        <th>Foto</th>
                        <th>Tanggal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if (count($guides) > 0): foreach ($guides as $g): ?>
                    <tr>
                        <td><?= htmlspecialchars($g['nama']) ?></td>
                        <td><?= htmlspecialchars($g['email']) ?></td>
                        <td><?= htmlspecialchars($g['no_hp']) ?></td>
                        <td>
                            <span class="status-badge status-<?= htmlspecialchars($g['status']) ?>">
                                <?= ucfirst($g['status']) ?>
                            </span>
                        </td>
                        <td><a href="../uploads/guide_applications/<?= htmlspecialchars($g['cv_file']) ?>" target="_blank">Lihat CV</a></td>
                        <td><a href="../uploads/guide_applications/<?= htmlspecialchars($g['ktp_file']) ?>" target="_blank">Lihat KTP</a></td>
                        <td><a href="../uploads/guide_applications/<?= htmlspecialchars($g['foto_file']) ?>" target="_blank">Lihat Foto</a></td>
                        <td><?= date('d M Y H:i', strtotime($g['tanggal_pengajuan'])) ?></td>
                        <td>
                            <?php if ($g['status'] === 'pending'): ?>
                                <a href="?action=accept&id=<?= $g['id'] ?>" class="btn-verif" onclick="return confirm('Terima pengajuan ini?')">Terima</a>
                                <a href="?action=reject&id=<?= $g['id'] ?>" class="btn-tolak" onclick="return confirm('Tolak pengajuan ini?')">Tolak</a>
                            <?php elseif ($g['status'] === 'accepted'): ?>
                                <span style="color:#2e8b57;font-weight:600;">Diterima</span>
                            <?php else: ?>
                                <span style="color:#dc3545;font-weight:600;">Ditolak</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; else: ?>
                    <tr><td colspan="9" style="text-align:center; color:#aaa; font-style:italic;">Tidak ada pengajuan guide</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html> 
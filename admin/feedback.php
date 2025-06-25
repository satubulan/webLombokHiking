<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

// Ambil data feedback gunung (feedback_mountains) urut rating tertinggi
$sql_mountain = "SELECT fm.*, u.name AS user_name, m.name AS mountain_name
    FROM feedback_mountains fm
    LEFT JOIN users u ON fm.user_id = u.id
    LEFT JOIN bookings b ON fm.booking_id = b.id
    LEFT JOIN mountain_tickets mt ON b.mountain_ticket_id = mt.id
    LEFT JOIN mountains m ON mt.mountain_id = m.id
    ORDER BY fm.rating DESC, fm.created_at DESC";

$result_mountain = $conn->query($sql_mountain);
$mountain_feedback = $result_mountain ? $result_mountain->fetch_all(MYSQLI_ASSOC) : [];

// Ambil data feedback guide (tabel feedback) urut terbaru
$sql_guide = "SELECT f.*, u.name AS user_name, guser.name AS guide_name
    FROM feedback f
    LEFT JOIN users u ON f.user_id = u.id
    LEFT JOIN users guser ON f.guide_id = guser.id
    ORDER BY f.id DESC";
$result_guide = $conn->query($sql_guide);
$guide_feedback = $result_guide ? $result_guide->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Daftar Feedback - Admin Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/guide.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        .rating-star { color: #ffc107; font-size: 1.1em; }
        .admin-table th, .admin-table td { vertical-align: top; }
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
            <li><a href="mountains.php" class="nav-link"><i class="fas fa-mountain"></i> Gunung</a></li>
            <li><a href="trips.php" class="nav-link"><i class="fas fa-route"></i> Trip</a></li>
            <li><a href="feedback.php" class="nav-link active"><i class="fas fa-comments"></i> Feedback</a></li>
            <li><a href="lihat_pembayaran.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Lihat Pembayaran</a></li>
            <li><a href="notifikasi.php" class="nav-link"><i class="fas fa-bell"></i> Notifikasi</a></li>
            <li><a href="profile.php" class="nav-link"><i class="fas fa-user-cog"></i> Profil</a></li>
            <li><a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>
    <main class="admin-main">
        <div class="admin-header">
            <h1>Feedback Gunung</h1>
        </div>
        <div class="admin-table-container">
            <table class="admin-table" cellspacing="0" cellpadding="0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Gunung</th>
                        <th>Rating</th>
                        <th>Komentar</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($mountain_feedback) && count($mountain_feedback) > 0): foreach ($mountain_feedback as $fb): ?>
                    <tr>
                        <td><?= htmlspecialchars($fb['user_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($fb['mountain_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($fb['rating']) ?></td>
                        <td><?= htmlspecialchars($fb['comment']) ?></td>
                        <td><?= date('d M Y', strtotime($fb['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="5" style="text-align:center; color:#aaa; font-style:italic;">Tidak ada feedback</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="admin-header" style="margin-top:38px;">
            <h1>Feedback Guide</h1>
        </div>
        <div class="admin-table-container">
            <table class="admin-table" cellspacing="0" cellpadding="0">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Guide</th>
                        <th>Rating</th>
                        <th>Komentar</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (isset($guide_feedback) && count($guide_feedback) > 0): foreach ($guide_feedback as $fb): ?>
                    <tr>
                        <td><?= htmlspecialchars($fb['user_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($fb['guide_name'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($fb['rating_guide'] ?? $fb['rating'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($fb['comment_guide'] ?? $fb['comment'] ?? '-') ?></td>
                        <td><?= date('d M Y', strtotime($fb['created_at'])) ?></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="5" style="text-align:center; color:#aaa; font-style:italic;">Tidak ada feedback</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>
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
    if ($_GET['action'] === 'verifikasi') {
        // Update status pembayaran dan booking
        $conn->query("UPDATE pembayaran SET status='paid', payment_date=NOW() WHERE booking_id=$id");
        $conn->query("UPDATE bookings SET status='confirmed' WHERE id=$id");
    } elseif ($_GET['action'] === 'tolak') {
        $conn->query("UPDATE pembayaran SET status='rejected' WHERE booking_id=$id");
        $conn->query("UPDATE bookings SET status='cancelled' WHERE id=$id");
    }
    header('Location: lihat_pembayaran.php');
    exit();
}

// Ambil data pembayaran (join bookings dan pembayaran)
$sql = "SELECT 
            b.id AS booking_id,
            u.id AS user_id,
            u.name AS user_name,
            u.role,
            b.status AS booking_status,
            b.booking_date,
            b.total_price,
            b.addon_fee,
            p.status AS payment_status,
            p.payment_proof
        FROM bookings b
        LEFT JOIN users u ON b.user_id = u.id
        LEFT JOIN pembayaran p ON b.id = p.booking_id
        ORDER BY b.id DESC";
$result = $conn->query($sql);
if (!$result) { die('Query Error (bookings): ' . $conn->error); }
$bookings = $result->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Lihat Pembayaran - Admin Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/guide.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        .admin-table th, .admin-table td { vertical-align: top; }
        .btn-verif { background: #2e8b57; color: #fff; border: none; padding: 6px 14px; border-radius: 6px; font-weight: 600; cursor: pointer; margin-right: 4px; }
        .btn-tolak { background: #dc3545; color: #fff; border: none; padding: 6px 14px; border-radius: 6px; font-weight: 600; cursor: pointer; }
        .btn-detail { background: #f4f6f9; color: #2e8b57; border: 1px solid #2e8b57; padding: 6px 14px; border-radius: 6px; font-weight: 600; cursor: pointer; margin-right: 4px; }
        .status-badge { padding: 4px 10px; border-radius: 12px; font-size: 13px; font-weight: 600; }
        .status-unpaid { background: #fff3cd; color: #856404; }
        .status-paid { background: #d4edda; color: #155724; }
        .status-rejected { background: #f8d7da; color: #721c24; }
        .bukti-img { max-width: 90px; max-height: 90px; border-radius: 6px; border: 1px solid #eee; }
        .modal-bg { display:none; position:fixed; top:0; left:0; width:100vw; height:100vh; background:rgba(0,0,0,0.4); z-index:1000; align-items:center; justify-content:center; }
        .modal-content { background:#fff; border-radius:10px; padding:30px 24px; min-width:320px; max-width:95vw; box-shadow:0 4px 24px rgba(0,0,0,0.15); }
        .modal-close { float:right; font-size:22px; color:#888; cursor:pointer; }
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
            <li><a href="feedback.php" class="nav-link"><i class="fas fa-comments"></i> Feedback</a></li>
            <li><a href="lihat_pembayaran.php" class="nav-link active"><i class="fas fa-money-bill-wave"></i> Lihat Pembayaran</a></li>
            <li><a href="notifikasi.php" class="nav-link"><i class="fas fa-bell"></i> Notifikasi</a></li>
            <li><a href="profile.php" class="nav-link"><i class="fas fa-user-cog"></i> Profil</a></li>
            <li><a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>
    <main class="admin-main">
        <div class="admin-header">
            <h1>Daftar Pembayaran</h1>
        </div>
        <div class="admin-table-container">
            <table class="admin-table" cellspacing="0" cellpadding="0">
                <thead>
                    <tr>
                        <th>ID Booking</th>
                        <th>User ID</th>
                        <th>Nama User</th>
                        <th>Role</th>
                        <th>Status Booking</th>
                        <th>Status Pembayaran</th>
                        <th>Booking Date</th>
                        <th>Total Price</th>
                        <th>Bukti Pembayaran</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if(count($bookings) > 0): foreach($bookings as $b): ?>
                    <tr>
                        <td><?= htmlspecialchars($b['booking_id']) ?></td>
                        <td><?= htmlspecialchars($b['user_id']) ?></td>
                        <td><?= htmlspecialchars($b['user_name']) ?></td>
                        <td><?= htmlspecialchars($b['role']) ?></td>
                        <td>
                            <?php
                                $status = $b['booking_status'];
                                if ($status === 'confirmed') {
                                    echo '<span class="status-badge status-paid">Terverifikasi</span>';
                                } elseif ($status === 'cancelled') {
                                    echo '<span class="status-badge status-rejected">Ditolak</span>';
                                } elseif ($status === 'pending') {
                                    echo '<span class="status-badge status-unpaid">Pending</span>';
                                } else {
                                    echo htmlspecialchars($status);
                                }
                            ?>
                        </td>
                        <td>
                            <?php
                                $pstatus = $b['payment_status'];
                                if ($pstatus === 'paid') {
                                    echo '<span class="status-badge status-paid">Paid</span>';
                                } elseif ($pstatus === 'rejected') {
                                    echo '<span class="status-badge status-rejected">Rejected</span>';
                                } elseif ($pstatus === 'unpaid') {
                                    echo '<span class="status-badge status-unpaid">Unpaid</span>';
                                } else {
                                    echo htmlspecialchars($pstatus);
                                }
                            ?>
                        </td>
                        <td><?= htmlspecialchars($b['booking_date']) ?></td>
                        <td>Rp <?= number_format($b['total_price'], 0, ',', '.') ?></td>
                        <td>
                            <?php if($b['payment_proof']): ?>
                                <img src="../uploads/payments/<?= htmlspecialchars($b['payment_proof']) ?>" class="bukti-img" alt="Bukti Pembayaran" onclick="showModal('<?= htmlspecialchars($b['payment_proof']) ?>')" style="cursor:pointer;">
                            <?php else: ?>
                                <span style="color:#aaa;">-</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if($b['booking_status'] === 'pending' && $b['payment_proof']): ?>
                                <a href="?action=verifikasi&id=<?= $b['booking_id'] ?>" class="btn-verif" onclick="return confirm('Verifikasi pembayaran ini?')">Verifikasi</a>
                                <a href="?action=tolak&id=<?= $b['booking_id'] ?>" class="btn-tolak" onclick="return confirm('Tolak pembayaran ini?')">Tolak</a>
                            <?php endif; ?>
                            <button class="btn-detail" onclick="showDetail(<?= htmlspecialchars(json_encode($b), ENT_QUOTES, 'UTF-8') ?>)">Edit</button>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="10" style="text-align:center; color:#aaa; font-style:italic;">Tidak ada booking</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <!-- Modal Bukti Pembayaran -->
        <div id="modalBukti" class="modal-bg" onclick="this.style.display='none'">
            <div class="modal-content" onclick="event.stopPropagation()">
                <span class="modal-close" onclick="document.getElementById('modalBukti').style.display='none'">&times;</span>
                <img id="modalBuktiImg" src="" style="max-width:100%; max-height:70vh; border-radius:8px;">
            </div>
        </div>
        <!-- Modal Detail Pembayaran -->
        <div id="modalDetail" class="modal-bg" onclick="this.style.display='none'">
            <div class="modal-content" onclick="event.stopPropagation()">
                <span class="modal-close" onclick="document.getElementById('modalDetail').style.display='none'">&times;</span>
                <div id="modalDetailContent"></div>
            </div>
        </div>
    </main>
</div>
<script>
function showModal(img) {
    document.getElementById('modalBuktiImg').src = '../uploads/payments/' + img;
    document.getElementById('modalBukti').style.display = 'flex';
}
function showDetail(data) {
    let html = `<h3>Detail Booking</h3>`;
    html += `<p><strong>ID Booking:</strong> ${data.booking_id}</p>`;
    html += `<p><strong>Nama User:</strong> ${data.user_name}</p>`;
    html += `<p><strong>Status:</strong> ${data.booking_status}</p>`;
    html += `<p><strong>Status Pembayaran:</strong> ${data.payment_status}</p>`;
    html += `<p><strong>Total Price:</strong> Rp ${parseInt(data.total_price).toLocaleString()}</p>`;
    html += `<p><strong>Booking Date:</strong> ${data.booking_date}</p>`;
    if (data.payment_proof) {
        html += `<img src='../uploads/payments/${data.payment_proof}' style='max-width:300px; border-radius:8px; margin-top:10px;'>`;
    }
    if (data.booking_status === 'pending' && data.payment_proof) {
        html += `<div style='display:flex; gap:16px; margin-top:24px;'>`;
        html += `<a href='?action=verifikasi&id=${data.booking_id}' class='btn-verif' onclick='return confirm("Verifikasi pembayaran ini?")'>Verifikasi</a>`;
        html += `<a href='?action=tolak&id=${data.booking_id}' class='btn-tolak' onclick='return confirm("Tolak pembayaran ini?")'>Tolak</a>`;
        html += `</div>`;
    } else if (data.booking_status === 'confirmed') {
        html += `<p style='margin-top:24px; color:#666;'>Status booking sudah terverifikasi</p>`;
    } else if (data.booking_status === 'cancelled') {
        html += `<p style='margin-top:24px; color:#666;'>Status booking sudah ditolak</p>`;
    }
    document.getElementById('modalDetailContent').innerHTML = html;
    document.getElementById('modalDetail').style.display = 'flex';
}
</script>
</body>
</html> 
<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

$userName = $_SESSION['user_name'];

// Ambil statistik
$users = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'user'");
if (!$users) { die("Query Error (users): " . $conn->error); }
$users = $users->fetch_assoc()['total'];

$guides = $conn->query("SELECT COUNT(*) AS total FROM users WHERE role = 'guide'");
if (!$guides) { die("Query Error (guide): " . $conn->error); }
$guides = $guides->fetch_assoc()['total'];

$mountains = $conn->query("SELECT COUNT(*) AS total FROM mountains");
if (!$mountains) { die("Query Error (mountains): " . $conn->error); }
$mountains = $mountains->fetch_assoc()['total'];

$trips = $conn->query("SELECT COUNT(*) AS total FROM trips");
if (!$trips) { die("Query Error (trips): " . $conn->error); }
$trips = $trips->fetch_assoc()['total'];

$bookings = $conn->query("SELECT COUNT(*) AS total FROM bookings");
if (!$bookings) { die("Query Error (bookings): " . $conn->error); }
$bookings = $bookings->fetch_assoc()['total'];

// Statistik tambahan
$feedbacks = $conn->query("SELECT COUNT(*) AS total FROM feedback");
if (!$feedbacks) { die("Query Error (feedback): " . $conn->error); }
$feedbacks = $feedbacks->fetch_assoc()['total'];

$pending_guides = $conn->query("SELECT COUNT(*) AS total FROM guide WHERE status = 'pending'");
if (!$pending_guides) { die("Query Error (pending guides): " . $conn->error); }
$pending_guides = $pending_guides->fetch_assoc()['total'];

// Pendapatan admin dari system_fee (harga tiket gunung) pada pendapatan_guide
$pendapatan_query = $conn->query("SELECT SUM(system_fee) AS total FROM pendapatan_guide WHERE source = 'package'");
if (!$pendapatan_query) { die("Query Error (pendapatan): " . $conn->error); }
$pendapatan = $pendapatan_query->fetch_assoc()['total'] ?? 0;

// 5 booking terbaru
$recent_bookings = $conn->query("SELECT b.*, u.name AS user_name, t.title AS trip_title FROM bookings b LEFT JOIN users u ON b.user_id = u.id LEFT JOIN trips t ON b.trip_id = t.id ORDER BY b.id DESC LIMIT 5");
if (!$recent_bookings) { die("Query Error (recent bookings): " . $conn->error); }
$recent_bookings = $recent_bookings->fetch_all(MYSQLI_ASSOC);

// 5 pembayaran terbaru
$recent_payments = $conn->query("SELECT p.*, u.name AS user_name FROM pembayaran p LEFT JOIN bookings b ON p.booking_id = b.id LEFT JOIN users u ON b.user_id = u.id ORDER BY p.id DESC LIMIT 5");
if (!$recent_payments) { die("Query Error (recent payments): " . $conn->error); }
$recent_payments = $recent_payments->fetch_all(MYSQLI_ASSOC);

// Ambil daftar gunung untuk galeri
$mountain_list = $conn->query("SELECT * FROM mountains ORDER BY id DESC LIMIT 12");
$mountain_list = $mountain_list ? $mountain_list->fetch_all(MYSQLI_ASSOC) : [];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard Admin - Lombok Hiking</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* ===== Reset dan Dasar ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
            color: #333;
        }

        /* ===== Layout Utama ===== */
        .admin-container {
            display: flex;
            min-height: 100vh;
        }

        .admin-sidebar {
            width: 220px;
            background-color: #2e8b57;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 20px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 100;
        }

        .admin-sidebar .nav-section-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 30px;
            user-select: none;
        }

        .admin-sidebar .nav-links {
            list-style: none;
            flex-grow: 1;
        }

        .admin-sidebar .nav-links li {
            margin: 10px 0;
        }

        .admin-sidebar .nav-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 6px;
            transition: background 0.3s;
            font-weight: 600;
        }

        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background-color: #246b46;
        }

        /* ===== Main Area ===== */
        .main {
            flex: 1;
            padding: 30px;
            overflow-x: auto;
            margin-left: 220px;
        }

        .admin-header {
            margin-bottom: 30px;
        }

        .admin-header h1 {
            font-size: 24px;
            margin-bottom: 8px;
        }

        .admin-header p {
            font-size: 14px;
            color: #777;
        }

        /* ===== Stats Grid ===== */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
            padding: 20px;
            text-align: center;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card i {
            font-size: 40px;
            color: #2e8b57;
            margin-bottom: 10px;
        }

        .stat-card h3 {
            font-size: 18px;
            margin-bottom: 5px;
            color: #333;
        }

        .stat-card p {
            font-size: 24px;
            font-weight: bold;
            color: #2e8b57;
        }

        .stat-card small {
            display: block;
            margin-top: 5px;
            font-size: 12px;
            color: #666;
        }

        /* ===== Responsive ===== */
        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                gap: 15px;
            }

            .stats-grid {
                grid-template-columns: 1fr; /* Single column on small screens */
            }
        }

        .dashboard-table-wrap {
            flex: 1;
            min-width: 320px;
            background: #fff;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0,0,0,0.07);
            margin-bottom: 20px;
            padding: 20px 18px 18px 18px;
            display: flex;
            flex-direction: column;
        }
        .dashboard-table-title {
            font-size: 1.2rem;
            font-weight: bold;
            margin-bottom: 16px;
            color: #2e8b57;
            letter-spacing: 0.5px;
        }
        .dashboard-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: #fff;
            border-radius: 8px;
            overflow: hidden;
            font-size: 15px;
        }
        .dashboard-table thead tr {
            background: #f4f6f9;
        }
        .dashboard-table th, .dashboard-table td {
            padding: 10px 8px;
            text-align: left;
        }
        .dashboard-table tbody tr {
            transition: background 0.2s;
        }
        .dashboard-table tbody tr:hover {
            background: #f0f7f4;
        }
        .dashboard-table th {
            font-weight: 600;
            color: #333;
        }
        .dashboard-table td {
            color: #444;
        }
        .dashboard-table-empty {
            text-align: center;
            color: #aaa;
            font-style: italic;
            padding: 18px 0;
        }
        @media (max-width: 900px) {
            .dashboard-table-wrap { min-width: 220px; padding: 12px 6px 10px 6px; }
            .dashboard-table-title { font-size: 1rem; }
            .dashboard-table th, .dashboard-table td { padding: 7px 4px; font-size: 13px; }
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="admin-sidebar">
            <div class="nav-section-title">Admin Panel</div>
            <ul class="nav-links">
                <li><a href="index.php" class="nav-link active"><i class="fas fa-chart-line"></i> Dashboard</a></li>
                <li><a href="users.php" class="nav-link"><i class="fas fa-users"></i> Pengguna</a></li>
                <li><a href="guides.php" class="nav-link"><i class="fas fa-map-signs"></i> Guide</a></li>
                <li><a href="verifikasi_guide.php" class="nav-link"><i class="fas fa-user-check"></i> Verifikasi Guide</a></li>
                <li><a href="mountains.php" class="nav-link"><i class="fas fa-mountain"></i> Gunung</a></li>
                <li><a href="trips.php" class="nav-link"><i class="fas fa-route"></i> Trip</a></li>
                <li><a href="feedback.php" class="nav-link"><i class="fas fa-comments"></i> Feedback</a></li>
                <li><a href="lihat_pembayaran.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Lihat Pembayaran</a></li>
                <li><a href="notifikasi.php" class="nav-link"><i class="fas fa-bell"></i> Notifikasi</a></li>
                <li><a href="profile.php" class="nav-link active"><i class="fas fa-user-cog"></i> Profil</a></li>
                <li><a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
        </aside>

        <!-- Main -->
        <main class="main">
            <header class="admin-header">
                <h1>Selamat Datang, <?php echo htmlspecialchars($userName); ?> 👋</h1>
                <p>Ini adalah ringkasan aktivitas di Lombok Hiking.</p>
            </header>

            <section class="stats-grid">
                <a href="users.php" style="text-decoration:none;">
                <div class="stat-card">
                    <i class="fas fa-users"></i>
                    <div>
                        <h3>Pengguna</h3>
                        <p><?= $users ?></p>
                    </div>
                </div>
                </a>
                <a href="guides.php" style="text-decoration:none;">
                <div class="stat-card">
                    <i class="fas fa-map-signs"></i>
                    <div>
                        <h3>Guide</h3>
                        <p><?= $guides ?></p>
                    </div>
                </div>
                </a>
                <a href="mountains.php" style="text-decoration:none;">
                <div class="stat-card">
                    <i class="fas fa-mountain"></i>
                    <div>
                        <h3>Gunung</h3>
                        <p><?= $mountains ?></p>
                    </div>
                </div>
                </a>
                <a href="trips.php" style="text-decoration:none;">
                <div class="stat-card">
                    <i class="fas fa-route"></i>
                    <div>
                        <h3>Trip</h3>
                        <p><?= $trips ?></p>
                    </div>
                </div>
                </a>
                <a href="lihat_pembayaran.php" style="text-decoration:none;">
                <div class="stat-card">
                    <i class="fas fa-calendar-alt"></i>
                    <div>
                        <h3>lihat pembayaran</h3>
                        <p><?= $bookings ?></p>
                    </div>
                </div>
                </a>
                <a href="feedback.php" style="text-decoration:none;">
                <div class="stat-card">
                    <i class="fas fa-comments"></i>
                    <div>
                        <h3>Feedback</h3>
                        <p><?= $feedbacks ?></p>
                    </div>
                </div>
                </a>
                <a href="guides.php" style="text-decoration:none;">
                <div class="stat-card">
                    <i class="fas fa-user-clock"></i>
                    <div>
                        <h3>Guide Pending</h3>
                        <p><?= $pending_guides ?></p>
                    </div>
                </div>
                </a>
                <a href="lihat_pembayaran.php" style="text-decoration:none;">
                <div class="stat-card">
                    <i class="fas fa-money-bill-wave"></i>
                    <div>
                        <h3>Total Pendapatan</h3>
                        <p>Rp <?= number_format($pendapatan, 0, ',', '.') ?></p>
                        <small style="color: #666; font-size: 12px;">Dari booking terverifikasi</small>
                    </div>
                </div>
                </a>
            </section>

            <!-- Tabel ringkasan booking & pembayaran terbaru -->
            <div style="display: flex; gap: 30px; margin-top: 40px; flex-wrap: wrap;">
                <div class="dashboard-table-wrap">
                    <div class="dashboard-table-title">5 Booking Terbaru</div>
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Trip</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($recent_bookings) > 0): foreach($recent_bookings as $b): ?>
                            <tr>
                                <td><?= htmlspecialchars($b['user_name']) ?></td>
                                <td><?= htmlspecialchars($b['trip_title']) ?></td>
                                <td><?= htmlspecialchars($b['status']) ?></td>
                                <td><?= date('d-m-Y', strtotime($b['booking_date'])) ?></td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="4" class="dashboard-table-empty">Tidak ada data</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
                <div class="dashboard-table-wrap">
                    <div class="dashboard-table-title">5 Pembayaran Terbaru</div>
                    <table class="dashboard-table">
                        <thead>
                            <tr>
                                <th>User</th>
                                <th>Jumlah</th>
                                <th>Status</th>
                                <th>Tanggal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(count($recent_payments) > 0): foreach($recent_payments as $p): ?>
                            <tr>
                                <td><?= htmlspecialchars($p['user_name']) ?></td>
                                <td>Rp <?= number_format($p['amount'], 0, ',', '.') ?></td>
                                <td><?= htmlspecialchars($p['status']) ?></td>
                                <td><?= $p['payment_date'] ? date('d-m-Y', strtotime($p['payment_date'])) : '-' ?></td>
                            </tr>
                            <?php endforeach; else: ?>
                            <tr><td colspan="4" class="dashboard-table-empty">Tidak ada data</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Galeri Gunung -->
            <div style="margin-top:40px;">
              <h2 style="color:#2e8b57;font-size:1.6rem;margin-bottom:18px;font-weight:700;">Galeri Gunung</h2>
              <div style="display:grid;grid-template-columns:repeat(auto-fit,minmax(220px,1fr));gap:24px;">
                <?php if(count($mountain_list)>0):foreach($mountain_list as $mtn):?>
                  <div style="background:#f8f9fa;border-radius:12px;box-shadow:0 2px 10px rgba(44,62,80,0.08);padding:0 0 18px 0;display:flex;flex-direction:column;align-items:center;overflow:hidden;">
                    <?php if(!empty($mtn['image'])):?>
                      <img src="../assets/images/mountains/<?php echo htmlspecialchars($mtn['image']);?>" alt="<?php echo htmlspecialchars($mtn['name']);?>" style="width:100%;aspect-ratio:4/3;object-fit:cover;border-radius:12px 12px 0 0;box-shadow:0 2px 8px rgba(44,62,80,0.10);">
                    <?php else:?>
                      <div style="width:100%;aspect-ratio:4/3;display:flex;align-items:center;justify-content:center;background:#e9ecef;border-radius:12px 12px 0 0;">
                        <i class="fas fa-mountain" style="font-size:48px;color:#b2bec3;"></i>
                      </div>
                    <?php endif;?>
                    <div style="text-align:center;padding:14px 12px 0 12px;width:100%;">
                      <div style="font-size:1.1rem;font-weight:700;color:#2e8b57;margin-bottom:4px;word-break:break-word;"><?php echo htmlspecialchars($mtn['name']);?></div>
                      <div style="color:#636e72;font-size:14px;margin-bottom:8px;">Tinggi: <?php echo number_format($mtn['height']);?> m</div>
                    </div>
                    <div style="font-size:13px;color:#555;line-height:1.5;padding:0 12px 0 12px;width:100%;margin-top:8px;word-break:break-word;min-height:38px;">
                      <?php echo htmlspecialchars($mtn['description']);?>
                    </div>
                  </div>
                <?php endforeach;else:?>
                  <div style='grid-column:1/-1;text-align:center;color:#888;'>Belum ada gunung terdaftar.</div>
                <?php endif;?>
              </div>
            </div>
        </main>
    </div>
</body>
</html>

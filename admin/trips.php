<?php 
session_start();
require_once '../config.php';

// Cek login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

// Jika form disubmit (POST), simpan data trip
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mountain = trim($_POST['mountain']);
    $guide = trim($_POST['guide']);
    $date = $_POST['date'];
    $price = $_POST['price'];
    $quota = $_POST['quota'];

    // Ambil ID gunung dan guide
    $stmtMountain = $conn->prepare("SELECT id FROM mountains WHERE name = ?");
    $stmtMountain->bind_param("s", $mountain);
    $stmtMountain->execute();
    $resMountain = $stmtMountain->get_result();
    $mountainData = $resMountain->fetch_assoc();

    $stmtGuide = $conn->prepare("SELECT id FROM guides WHERE name = ?");
    $stmtGuide->bind_param("s", $guide);
    $stmtGuide->execute();
    $resGuide = $stmtGuide->get_result();
    $guideData = $resGuide->fetch_assoc();

    if ($mountainData && $guideData) {
        $stmtInsert = $conn->prepare("INSERT INTO trips (mountain_id, guide_id, date, price, quota) VALUES (?, ?, ?, ?, ?)");
        $stmtInsert->bind_param("iisis", $mountainData['id'], $guideData['id'], $date, $price, $quota);
        $stmtInsert->execute();
        header("Location: trips.php");
        exit();
    } else {
        $error = "Gunung atau guide tidak ditemukan di database.";
    }
}

// Ambil semua trip
$sql = "SELECT trips.*, 
               mountains.name AS mountain_name, 
               guides.name AS guide_name 
        FROM trips
        LEFT JOIN mountains ON trips.mountain_id = mountains.id
        LEFT JOIN guides ON trips.guide_id = guides.id
        ORDER BY trips.date DESC";
$result = $conn->query($sql);
$trips = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Trip - Admin Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/trips.css">
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
            <li><a href="trips.php" class="nav-link active"><i class="fas fa-route"></i> Trip</a></li>
            <li><a href="bookings.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Booking</a></li>
            <li><a href="feedback.php" class="nav-link"><i class="fas fa-comment-dots"></i> Feedback</a></li>
            <li><a href="profile.php" class="nav-link"><i class="fas fa-user-cog"></i> Profil</a></li>
            <li><a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="admin-main" style="flex:1;padding:30px;overflow-x:auto;">
        <h1 style="margin-bottom:20px;color:#2e8b57;">Manajemen Trip</h1>

        <?php if (isset($_GET['action']) && $_GET['action'] === 'add'): ?>
            <a href="trips.php" class="btn btn-secondary" style="margin-bottom:20px;">← Kembali ke Daftar Trip</a>
            <h2>Tambah Trip Baru</h2>
            <?php if (isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <form method="POST" action="trips.php">
                <label>Gunung:</label>
                <input type="text" name="mountain" placeholder="Contoh: Rinjani" required>

                <label>Guide:</label>
                <input type="text" name="guide" placeholder="Contoh: Budi" required>

                <label>Tanggal:</label>
                <input type="date" name="date" required>

                <label>Harga:</label>
                <input type="number" name="price" required>

                <label>Kuota:</label>
                <input type="number" name="quota" required>

                <button type="submit">Simpan Trip</button>
            </form>
        <?php else: ?>
            <!-- Tombol ke form tambah trip -->
            <a href="trips.php?action=add" class="btn btn-primary" style="margin-bottom:20px;">Tambah Trip</a>

            <!-- Tabel daftar trip -->
            <div class="admin-table-container">
                <table class="admin-table" cellspacing="0" cellpadding="0">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Gunung</th>
                            <th>Guide</th>
                            <th>Tanggal</th>
                            <th>Harga</th>
                            <th>Kuota</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($trips) > 0): ?>
                            <?php foreach ($trips as $trip): ?>
                                <tr>
                                    <td><?= $trip['id']; ?></td>
                                    <td><?= htmlspecialchars($trip['mountain_name']); ?></td>
                                    <td><?= htmlspecialchars($trip['guide_name']); ?></td>
                                    <td><?= $trip['date']; ?></td>
                                    <td>Rp<?= number_format($trip['price'], 0, ',', '.'); ?></td>
                                    <td><?= $trip['quota']; ?></td>
                                    <td>
                                        <a href="#" class="btn btn-edit"><i class="fas fa-edit"></i> Edit</a>
                                        <a href="#" class="btn btn-delete" onclick="return confirm('Yakin ingin hapus trip ini?')"><i class="fas fa-trash-alt"></i> Hapus</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">Belum ada trip terdaftar.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
</div>
</body>
</html>

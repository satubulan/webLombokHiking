<?php 
session_start();
require_once '../config.php';

// Cek login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

// Cek dan buat tabel trips jika belum ada
$check_table = "SHOW TABLES LIKE 'trips'";
$table_exists = $conn->query($check_table);

if ($table_exists->num_rows == 0) {
    // Buat tabel trips
    $create_table = "CREATE TABLE trips (
        id INT AUTO_INCREMENT PRIMARY KEY,
        mountain_id INT NOT NULL,
        guide_id INT NOT NULL,
        date DATE NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        quota INT NOT NULL,
        active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (mountain_id) REFERENCES mountains(id),
        FOREIGN KEY (guide_id) REFERENCES guides(id)
    )";
    
    if (!$conn->query($create_table)) {
        die("Error creating table: " . $conn->error);
    }
}

// Ambil data gunung dan guide untuk dropdown
$mountains_query = "SELECT id, name FROM mountains ORDER BY name ASC";
$mountains_result = $conn->query($mountains_query);
if (!$mountains_result) {
    die("Error fetching mountains: " . $conn->error);
}
$mountains = $mountains_result->fetch_all(MYSQLI_ASSOC);

$guides_query = "SELECT id, name FROM guides ORDER BY name ASC";
$guides_result = $conn->query($guides_query);
if (!$guides_result) {
    die("Error fetching guides: " . $conn->error);
}
$guides = $guides_result->fetch_all(MYSQLI_ASSOC);

// Jika form disubmit (POST), simpan data trip
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mountain_id = intval($_POST['mountain_id']);
    $guide_id = intval($_POST['guide_id']);
    $date = $conn->real_escape_string($_POST['date']);
    $price = floatval($_POST['price']);
    $quota = intval($_POST['quota']);
    $active = isset($_POST['active']) ? 1 : 0;

    $sql = "INSERT INTO trips (mountain_id, guide_id, date, price, quota, active) 
            VALUES ($mountain_id, $guide_id, '$date', $price, $quota, $active)";
    
    if (!$conn->query($sql)) {
        $error = "Error: " . $conn->error;
    } else {
        header("Location: trips.php");
        exit();
    }
}

// Ambil semua trip dengan urutan ascending
$sql = "SELECT t.*, 
               m.name AS mountain_name, 
               g.name AS guide_name 
        FROM trips t
        LEFT JOIN mountains m ON t.mountain_id = m.id
        LEFT JOIN guides g ON t.guide_id = g.id
        ORDER BY t.id ASC";
$result = $conn->query($sql);
if (!$result) {
    die("Error fetching trips: " . $conn->error);
}
$trips = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Manajemen Trip - Admin Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/guide.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
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
            <li><a href="trips.php" class="nav-link active"><i class="fas fa-route"></i> Trip</a></li>
            <li><a href="lihat_pembayaran.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Lihat Pembayaran</a></li>
            <li><a href="feedback.php" class="nav-link"><i class="fas fa-comment-dots"></i> Feedback</a></li>
            <li><a href="profile.php" class="nav-link"><i class="fas fa-user-cog"></i> Profil</a></li>
            <li><a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="admin-main">
        <div class="admin-header">
            <h1>Daftar Trip</h1>
            <div class="header-actions">
                <a href="trip_create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Trip
                </a>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Cari trip...">
                </div>
            </div>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

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
                        <?php foreach ($trips as $index => $trip): ?>
                            <tr>
                                <td class="user-id">#<?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($trip['mountain_name']); ?></td>
                                <td><?php echo htmlspecialchars($trip['guide_name']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($trip['date'])); ?></td>
                                <td>Rp<?php echo number_format($trip['price'], 0, ',', '.'); ?></td>
                                <td><?php echo $trip['quota']; ?></td>
                                <td class="action-buttons">
                                    <a href="edit_trip.php?id=<?php echo $trip['id']; ?>" class="btn btn-edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_trip.php?id=<?php echo $trip['id']; ?>" class="btn btn-delete" onclick="return confirm('Yakin ingin hapus trip ini?')" title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-route"></i>
                                    <p>Tidak ada trip terdaftar.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    let searchText = this.value.toLowerCase();
    let table = document.querySelector('.admin-table');
    let rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        let row = rows[i];
        let cells = row.getElementsByTagName('td');
        let found = false;

        for (let j = 0; j < cells.length; j++) {
            let cell = cells[j];
            if (cell.textContent.toLowerCase().indexOf(searchText) > -1) {
                found = true;
                break;
            }
        }

        row.style.display = found ? '' : 'none';
    }
});
</script>
</body>
</html>

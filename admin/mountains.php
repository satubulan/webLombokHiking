<?php 
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

// Ambil data gunung dengan urutan ascending
$result = $conn->query("SELECT * FROM mountains ORDER BY id ASC");
$mountains = $result->fetch_all(MYSQLI_ASSOC);

// Jika form tambah gunung disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_mountain'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $height = intval($_POST['height']);
    $image_url = $conn->real_escape_string($_POST['image_url']);

    $conn->query("INSERT INTO mountains (name, description, height, image_url) VALUES ('$name', '$description', $height, '$image_url')");
    header("Location: mountains.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Daftar Gunung</title>
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
            <li><a href="mountains.php" class="nav-link active"><i class="fas fa-mountain"></i> Gunung</a></li>
            <li><a href="trips.php" class="nav-link"><i class="fas fa-route"></i> Trip</a></li>
            <li><a href="bookings.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Booking</a></li>
            <li><a href="feedback.php" class="nav-link"><i class="fas fa-comment-dots"></i> Feedback</a></li>
            <li><a href="profile.php" class="nav-link"><i class="fas fa-user-cog"></i> Profil</a></li>
            <li><a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>
    
    <main class="admin-main" style="flex:1;padding:30px;overflow-x:auto;">
        <div class="admin-header">
            <h1>Daftar Gunung</h1>
            <a href="mountain_create.php" class="btn btn-primary"><i class="fas fa-plus"></i> Tambah Gunung</a>
        </div>

        <!-- Tabel daftar gunung -->
        <div class="admin-table-container">
            <table class="admin-table" cellspacing="0" cellpadding="0">
                <thead>   
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Deskripsi</th>
                        <th>Tinggi (m)</th>
                        <th>Gambar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($mountains) > 0): ?>
                        <?php foreach ($mountains as $index => $mountain): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($mountain['name']); ?></td>
                                <td><?php echo htmlspecialchars($mountain['description']); ?></td>
                                <td><?php echo number_format($mountain['height']); ?></td>
                                <td>
                                    <?php if (!empty($mountain['image_url'])): ?>
                                        <img src="../assets/images/<?php echo htmlspecialchars($mountain['image_url']); ?>" alt="Gambar Gunung" style="width:80px; border-radius:4px;">
                                    <?php else: ?>
                                        <span class="text-muted">Tidak ada gambar</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="edit_mountain.php?id=<?php echo $mountain['id']; ?>" class="btn btn-edit"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="delete_mountain.php?id=<?php echo $mountain['id']; ?>" class="btn btn-delete" onclick="return confirm('Yakin ingin hapus gunung ini?')"><i class="fas fa-trash-alt"></i> Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada gunung terdaftar.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script src="../assets/js/main.js"></script>
</body>
</html>

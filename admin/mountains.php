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
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Manajemen Gunung - Admin Lombok Hiking</title>
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
            <li><a href="mountains.php" class="nav-link active"><i class="fas fa-mountain"></i> Gunung</a></li>
            <li><a href="trips.php" class="nav-link"><i class="fas fa-route"></i> Trip</a></li>
            <li><a href="profile.php" class="nav-link"><i class="fas fa-user-cog"></i> Profil</a></li>
            <li><a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>
    
    <main class="admin-main">
        <div class="admin-header">
            <h1>Daftar Gunung</h1>
            <div class="header-actions">
                <a href="mountain_create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Gunung
                </a>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Cari gunung...">
                </div>
            </div>
        </div>

        <!-- Tabel daftar gunung -->
        <div class="admin-table-container">
            <table class="admin-table" cellspacing="0" cellpadding="0">
                <thead>   
                    <tr>
                        <th>ID</th>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Tinggi (m)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($mountains) > 0): ?>
                        <?php foreach ($mountains as $index => $mountain): ?>
                            <tr>
                                <td class="user-id">#<?php echo $index + 1; ?></td>
                                <td>
                                    <?php if (!empty($mountain['image_url'])): ?>
                                        <img src="../assets/images/<?php echo htmlspecialchars($mountain['image_url']); ?>" 
                                             alt="Foto <?php echo htmlspecialchars($mountain['name']); ?>"
                                             class="profile-thumbnail">
                                    <?php else: ?>
                                        <div class="profile-thumbnail no-image">
                                            <i class="fas fa-mountain"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="user-name">
                                    <div class="user-info">
                                        <span class="name"><?php echo htmlspecialchars($mountain['name']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo number_format($mountain['height']); ?></td>
                                <td class="action-buttons">
                                    <a href="edit_mountain.php?id=<?php echo $mountain['id']; ?>" class="btn btn-edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_mountain.php?id=<?php echo $mountain['id']; ?>" class="btn btn-delete" onclick="return confirm('Yakin ingin hapus gunung ini?')" title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-mountain"></i>
                                    <p>Tidak ada gunung terdaftar.</p>
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

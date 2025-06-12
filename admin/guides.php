<?php
session_start();
require_once '../config.php';

// Cek login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

// Ambil semua guide dengan urutan ascending
$result = $conn->query("SELECT * FROM guides ORDER BY id ASC");
$guides = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Manajemen Guide - Admin Lombok Hiking</title>
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
            <li><a href="guides.php" class="nav-link active"><i class="fas fa-map-signs"></i> Guide</a></li>
            <li><a href="mountains.php" class="nav-link"><i class="fas fa-mountain"></i> Gunung</a></li>
            <li><a href="trips.php" class="nav-link"><i class="fas fa-route"></i> Trip</a></li>
            <li><a href="lihat_pembayaran.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Lihat Pembayaran</a></li>
            <li><a href="feedback.php" class="nav-link"><i class="fas fa-comment-dots"></i> Feedback</a></li>
            <li><a href="profile.php" class="nav-link"><i class="fas fa-user-cog"></i> Profil</a></li>
            <li><a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <div class="admin-header">
            <h1>Daftar Guide</h1>
            <div class="header-actions">
                <a href="guide_create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Guide
                </a>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Cari guide...">
                </div>
            </div>
        </div>

        <div class="admin-table-container">
            <table class="admin-table" cellspacing="0" cellpadding="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Rating</th>
                        <th>Bahasa</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($guides) > 0): ?>
                        <?php foreach ($guides as $index => $guide): ?>
                            <tr>
                                <td class="user-id">#<?php echo $index + 1; ?></td>
                                <td>
                                    <?php if ($guide['image_url']): ?>
                                        <img src="../assets/images/<?php echo htmlspecialchars($guide['image_url']); ?>" 
                                             alt="Foto <?php echo htmlspecialchars($guide['name']); ?>"
                                             class="profile-thumbnail">
                                    <?php else: ?>
                                        <div class="profile-thumbnail no-image">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="user-name">
                                    <div class="user-info">
                                        <span class="name"><?php echo htmlspecialchars($guide['name']); ?></span>
                                    </div>
                                </td>
                                <td>
                                    <div class="rating">
                                        <i class="fas fa-star"></i>
                                        <span class="rating-value"><?php echo number_format($guide['rating'], 1); ?></span>
                                    </div>
                                </td>
                                <td><?php echo htmlspecialchars($guide['languages']); ?></td>
                                <td>
                                    <?php if ($guide['active']): ?>
                                        <span class="status-badge active">
                                            <i class="fas fa-circle"></i>
                                            Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge inactive">
                                            <i class="fas fa-circle"></i>
                                            Nonaktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="action-buttons">
                                    <a href="edit_guide.php?id=<?php echo $guide['id']; ?>" class="btn btn-edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_guide.php?id=<?php echo $guide['id']; ?>" class="btn btn-delete" onclick="return confirm('Yakin ingin hapus guide ini?')" title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-map-signs"></i>
                                    <p>Tidak ada guide terdaftar.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<style>
.rating {
    display: flex;
    align-items: center;
    gap: 8px;
}
.rating i {
    color: #ffc107;
    font-size: 16px;
}
.rating-value {
    font-weight: 500;
    color: #666;
}
</style>
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

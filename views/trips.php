<?php
session_start();
require_once '../config.php';

// Ambil data trip dari database
$sql = "SELECT trips.*, 
               mountains.name AS mountain_name, 
               guides.name AS guide_name 
        FROM trips
        LEFT JOIN mountains ON trips.mountain_id = mountains.id
        LEFT JOIN guides ON trips.guide_id = guides.id
        ORDER BY date ASC";
$result = $conn->query($sql);
$trips = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Jadwal Trip Pendakian - Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/guide.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
    <div class="admin-layout">
        <main class="admin-main">
            <div class="admin-header">
                <h1>Jadwal Trip Pendakian</h1>
                <div class="header-actions">
                    <div class="search-box">
                        <i class="fas fa-search"></i>
                        <input type="text" id="searchInput" placeholder="Cari trip...">
                            </div>
                        </div>
                    </div>

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
                                        <a href="#" class="btn btn-primary">Pesan Sekarang</a>
                                    </td>
                                </tr>
                <?php endforeach; ?>
            <?php else: ?>
                            <tr>
                                <td colspan="7" class="text-center">
                                    <div class="empty-state">
                                        <i class="fas fa-route"></i>
                    <p>Belum ada trip yang tersedia saat ini.</p>
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

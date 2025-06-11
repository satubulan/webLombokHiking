<?php
session_start();
require_once '../config.php';

function hitung_total($harga_per_orang, $jumlah) {
    if ($jumlah <= 1) return $harga_per_orang;
    $tambahan = ($jumlah - 1) * 25000;
    return $harga_per_orang + $tambahan;
}

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: ../views/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];

// Ambil data status pembayaran
$query = "SELECT b.id_booking, b.tanggal, b.jumlah, b.metode_pembayaran, b.status, g.nama, g.gambar, g.harga
          FROM booking b
          JOIN gunung g ON b.id_gunung = g.id_gunung
          WHERE b.user_id = '$user_id'
          ORDER BY b.id_booking DESC";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Status Pembayaran</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9f9f9;
            padding: 40px;
        }
        .container {
            max-width: 960px;
            margin: auto;
        }
        h2 {
            text-align: center;
            color: #2e8b57;
            margin-bottom: 30px;
        }
        .status-card {
            display: flex;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .status-card img {
            width: 200px;
            height: 140px;
            object-fit: cover;
        }
        .status-content {
            padding: 20px;
            flex-grow: 1;
            position: relative;
        }
        .status-content h4 {
            margin: 0 0 10px;
            color: #2e8b57;
        }
        .status-content p {
            margin: 5px 0;
            color: #555;
            font-size: 14px;
        }
        .status-content .harga {
            font-weight: bold;
            color: #e67e22;
        }
        .badge {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: bold;
            display: inline-block;
            margin-top: 10px;
        }
        .badge-menunggu { background: #f39c12; color: white; }
        .badge-berhasil { background: #2ecc71; color: white; }
        .badge-gagal    { background: #e74c3c; color: white; }
        .back-button {
            text-decoration: none;
            color: #2e8b57;
            font-size: 18px;
            display: inline-block;
            margin-bottom: 25px;
        }
    </style>
</head>
<body>
    <div class="container">
        <a href="index.php" class="back-button"><i class="fas fa-arrow-left"></i> Kembali</a>
        <h2>Status Pembayaran</h2>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): 
                $total = $row['harga'] + ($row['jumlah'] - 1) * 25000;
                $status = strtolower($row['status']);
                $badgeClass = ($status == 'berhasil') ? 'badge-berhasil' :
                              (($status == 'gagal') ? 'badge-gagal' : 'badge-menunggu');
            ?>
                <div class="status-card">
                    <img src="../assets/images/<?php echo htmlspecialchars($row['gambar']); ?>" alt="<?php echo htmlspecialchars($row['nama']); ?>">
                    <div class="status-content">
                        <h4><?php echo htmlspecialchars($row['nama']); ?></h4>
                        <p>Tanggal Pendakian: <?php echo htmlspecialchars($row['tanggal']); ?></p>
                        <p>Jumlah Peserta: <?php echo htmlspecialchars($row['jumlah']); ?></p>
                        <p>Metode: <?php echo htmlspecialchars($row['metode_pembayaran']); ?></p>
                        <p>Total: <span class="harga">Rp <?php echo number_format($total, 0, ',', '.'); ?></span></p>
                        <span class="badge <?php echo $badgeClass; ?>"><?php echo ucfirst($status); ?></span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center;">Belum ada data pembayaran.</p>
        <?php endif; ?>
    </div>
</body>
</html>

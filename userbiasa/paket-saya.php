<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: ../views/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$today = date('Y-m-d');

// Ambil data booking dengan info gunung dan guide
$query = "
    SELECT 
        b.*, 
        g.nama AS gunung_nama, 
        g.gambar, 
        g.harga, 
        u.name AS guide_nama
    FROM booking b
    JOIN gunung g ON b.id_gunung = g.id_gunung
    LEFT JOIN users u ON b.guide_id = u.id
    WHERE b.user_id = '$user_id'
    ORDER BY b.tanggal DESC
";
$result = mysqli_query($conn, $query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Paket Saya</title>
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
        .paket-card {
            display: flex;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .paket-card img {
            width: 200px;
            height: 140px;
            object-fit: cover;
        }
        .paket-content {
            padding: 20px;
            flex-grow: 1;
        }
        .paket-content h4 {
            margin: 0 0 10px;
            color: #2e8b57;
        }
        .paket-content p {
            margin: 5px 0;
            color: #555;
            font-size: 14px;
        }
        .paket-content .harga {
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
        .badge-belum { background: #f39c12; color: white; }
        .badge-berlangsung { background: #3498db; color: white; }
        .badge-selesai { background: #2ecc71; color: white; }
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
        <h2>Paket Pendakian Saya</h2>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)):
                $tanggal = $row['tanggal'];
                $hari_ini = strtotime($today);
                $jadwal = strtotime($tanggal);
                $status = '';

                if ($jadwal > $hari_ini) {
                    $status = 'Belum Berangkat';
                    $badge = 'badge-belum';
                } elseif ($jadwal == $hari_ini) {
                    $status = 'Berlangsung';
                    $badge = 'badge-berlangsung';
                } else {
                    $status = 'Selesai';
                    $badge = 'badge-selesai';
                }

                $total = $row['harga'] + ($row['jumlah'] - 1) * 25000;
            ?>
                <div class="paket-card">
                    <img src="../assets/images/<?php echo htmlspecialchars($row['gambar']); ?>" alt="<?php echo htmlspecialchars($row['gunung_nama']); ?>">
                    <div class="paket-content">
                        <h4><?php echo htmlspecialchars($row['gunung_nama']); ?></h4>
                        <p>Tanggal Pendakian: <?php echo htmlspecialchars($tanggal); ?></p>
                        <p>Jumlah Peserta: <?php echo htmlspecialchars($row['jumlah']); ?></p>
                        <p>Metode: <?php echo htmlspecialchars($row['metode_pembayaran']); ?></p>
                        <?php if ($row['guide_nama']) : ?>
                            <p>Guide: <?php echo htmlspecialchars($row['guide_nama']); ?></p>
                        <?php endif; ?>
                        <p>Total: <span class="harga">Rp <?php echo number_format($total, 0, ',', '.'); ?></span></p>
                        <span class="badge <?php echo $badge; ?>"><?php echo $status; ?></span>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center;">Anda belum memiliki paket pendakian.</p>
        <?php endif; ?>
    </div>
</body>
</html>

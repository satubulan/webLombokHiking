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
$success = '';
$error = '';

// Proses pembatalan pesanan
if (isset($_GET['batal']) && is_numeric($_GET['batal'])) {
    $id = (int)$_GET['batal'];
    $delete = mysqli_query($conn, "DELETE FROM booking WHERE id_booking = $id AND user_id = '$user_id'");
    if ($delete) {
        $success = "Pesanan berhasil dibatalkan.";
        $pesan = "Pesanan Anda telah dibatalkan.";
        mysqli_query($conn, "INSERT INTO notifikasi (user_id, pesan) VALUES ('$user_id', '$pesan')");
    } else {
        $error = "Gagal membatalkan pesanan.";
    }
}

// Ambil daftar booking user
$query = "SELECT b.id_booking, b.tanggal, b.jumlah, b.metode_pembayaran, g.nama, g.gambar, g.harga 
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
    <title>Keranjang Saya</title>
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
        .keranjang-card {
            display: flex;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            overflow: hidden;
        }
        .keranjang-card img {
            width: 200px;
            height: 140px;
            object-fit: cover;
        }
        .keranjang-content {
            padding: 20px;
            flex-grow: 1;
            position: relative;
        }
        .keranjang-content h4 {
            margin: 0 0 10px;
            color: #2e8b57;
        }
        .keranjang-content p {
            margin: 5px 0;
            color: #555;
            font-size: 14px;
        }
        .keranjang-content .harga {
            font-weight: bold;
            color: #e67e22;
        }
        .keranjang-content .btn-batal {
            position: absolute;
            top: 20px;
            right: 20px;
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 6px 10px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
        }
        .alert-success {
            color: green;
            margin-bottom: 20px;
            text-align: center;
        }
        .alert-error {
            color: red;
            margin-bottom: 20px;
            text-align: center;
        }
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
        <h2>Keranjang Saya</h2>

        <?php if ($success): ?>
            <div class="alert-success"><?php echo $success; ?></div>
        <?php elseif ($error): ?>
            <div class="alert-error"><?php echo $error; ?></div>
        <?php endif; ?>

        <?php if (mysqli_num_rows($result) > 0): ?>
            <?php while ($row = mysqli_fetch_assoc($result)): ?>
                <div class="keranjang-card">
                    <img src="../assets/images/<?php echo htmlspecialchars($row['gambar']); ?>" alt="<?php echo htmlspecialchars($row['nama']); ?>">
                    <div class="keranjang-content">
                        <h4><?php echo htmlspecialchars($row['nama']); ?></h4>
                        <p>Tanggal: <?php echo htmlspecialchars($row['tanggal']); ?></p>
                        <p>Jumlah Peserta: <?php echo htmlspecialchars($row['jumlah']); ?></p>
                        <p>Metode: <?php echo htmlspecialchars($row['metode_pembayaran']); ?></p>
                        <?php
                        $total = $row['harga'] + ($row['jumlah'] - 1) * 25000;
                        ?>
                        <p class="harga">Rp <?php echo number_format($total, 0, ',', '.'); ?></p>
                        <a href="?batal=<?php echo $row['id_booking']; ?>" class="btn-batal" onclick="return confirm('Yakin ingin membatalkan pesanan ini?')">Batalkan</a>
                    </div>
                </div>
            <?php endwhile; ?>
        <?php else: ?>
            <p style="text-align:center;">Keranjang Anda kosong.</p>
        <?php endif; ?>
    </div>
</body>
</html>

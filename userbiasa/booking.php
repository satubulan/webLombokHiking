<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: ../views/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_booking'])) {
    $id_gunung = $_POST['id_gunung'];
    $tanggal = $_POST['tanggal'];
    $jumlah = $_POST['jumlah'];
    $metode = $_POST['metode_pembayaran'];

    $query = "INSERT INTO booking (user_id, id_gunung, tanggal, jumlah, metode_pembayaran) 
              VALUES ('$user_id', '$id_gunung', '$tanggal', '$jumlah', '$metode')";

    if (mysqli_query($conn, $query)) {
        $success = "Booking berhasil! Silakan tunggu verifikasi dari admin.";
        $pesan = "Booking Anda untuk pendakian telah berhasil dilakukan.";
        mysqli_query($conn, "INSERT INTO notifikasi (user_id, pesan) VALUES ('$user_id', '$pesan')");
    } else {
        $error = "Terjadi kesalahan saat melakukan booking.";
    }
}

$gunungResult = mysqli_query($conn, "SELECT id_gunung, nama FROM gunung");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Booking Pendakian</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f9f9f9;
            padding: 40px;
        }
        .booking-container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h2 {
            text-align: center;
            color: #2e8b57;
            margin-bottom: 30px;
        }
        .form-group {
            margin-bottom: 20px;
        }
        label {
            font-weight: 600;
            display: block;
            margin-bottom: 8px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        .btn-booking {
            background-color: #2e8b57;
            color: white;
            border: none;
            padding: 12px;
            border-radius: 6px;
            cursor: pointer;
            width: 100%;
            font-size: 16px;
        }
        .btn-booking:hover {
            background-color: #246e47;
        }
        .success-msg {
            color: green;
            text-align: center;
            margin-bottom: 15px;
        }
        .error-msg {
            color: red;
            text-align: center;
            margin-bottom: 15px;
        }
        .back-button {
            text-decoration: none;
            color: #2e8b57;
            font-size: 18px;
            margin-bottom: 20px;
            display: inline-block;
        }
        .step {
            display: none;
        }
        .step.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="booking-container">
        <a href="index.php" class="back-button"><i class="fas fa-arrow-left"></i> Kembali</a>
        <h2>Form Booking Pendakian</h2>

        <?php if ($success): ?>
            <div class="success-msg"><?php echo $success; ?></div>
        <?php elseif ($error): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form method="POST" id="bookingForm">
            <div class="step active" id="step1">
                <div class="form-group">
                    <label for="id_gunung">Pilih Gunung</label>
                    <select name="id_gunung" required>
                        <option value="">-- Pilih --</option>
                        <?php while ($g = mysqli_fetch_assoc($gunungResult)) {
                            echo "<option value='{$g['id_gunung']}'>" . htmlspecialchars($g['nama']) . "</option>";
                        } ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="tanggal">Tanggal Pendakian</label>
                    <input type="date" name="tanggal" required>
                </div>

                <div class="form-group">
                    <label for="jumlah">Jumlah Peserta</label>
                    <input type="number" name="jumlah" min="1" required>
                </div>

                <button type="button" class="btn-booking" onclick="nextStep()">Lanjutkan</button>
            </div>

            <div class="step" id="step2">
                <div class="form-group">
                    <label for="metode_pembayaran">Pilih Metode Pembayaran</label>
                    <select name="metode_pembayaran" required>
                        <option value="">-- Pilih --</option>
                        <option value="Transfer Bank">Transfer Bank</option>
                        <option value="E-Wallet">E-Wallet</option>
                        <option value="QRIS">QRIS</option>
                    </select>
                </div>
                <div class="form-group">
                    <p><strong>Instruksi:</strong><br>Silakan lakukan pembayaran sesuai metode yang dipilih. Tim kami akan memverifikasi pesanan Anda setelah pembayaran diterima.</p>
                </div>
                <button type="submit" name="submit_booking" class="btn-booking">Kirim Booking</button>
            </div>
        </form>
    </div>

    <script>
        function nextStep() {
            document.getElementById('step1').classList.remove('active');
            document.getElementById('step2').classList.add('active');
        }
    </script>
</body>
</html>

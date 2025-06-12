<?php
session_start();
require_once '../config.php';

// Check if user is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: ../views/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$success_message = '';

// Fetch the selected trips from the user's cart
$cart_query = $conn->prepare("
    SELECT b.*, t.title, t.price, t.start_date, t.end_date, m.name as mountain_name 
    FROM bookings b 
    JOIN trips t ON b.trip_id = t.id
    JOIN mountains m ON t.mountain_id = m.id
    WHERE b.user_id = ? AND b.status = 'pending'
");
$cart_query->bind_param("s", $user_id);
$cart_query->execute();
$cart_result = $cart_query->get_result();

// Handle updating participant count in the cart
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['update'])) {
        $booking_id = $_POST['booking_id'];
        $participants = (int)$_POST['participants'];

        if ($participants <= 0) {
            $error_message = "Jumlah peserta harus lebih dari 0.";
        } else {
            // Update the number of participants for this booking
            $update_query = $conn->prepare("UPDATE bookings SET participants = ? WHERE id = ? AND user_id = ?");
            $update_query->bind_param("iis", $participants, $booking_id, $user_id);

            if ($update_query->execute()) {
                $success_message = "Jumlah peserta berhasil diperbarui!";
            } else {
                $error_message = "Terjadi kesalahan saat memperbarui jumlah peserta.";
            }
        }
    }

    if (isset($_POST['remove'])) {
        $booking_id = $_POST['booking_id'];

        // Remove the selected booking from the cart
        $remove_query = $conn->prepare("DELETE FROM bookings WHERE id = ? AND user_id = ?");
        $remove_query->bind_param("is", $booking_id, $user_id);

        if ($remove_query->execute()) {
            $success_message = "Booking berhasil dihapus dari keranjang.";
        } else {
            $error_message = "Terjadi kesalahan saat menghapus booking.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang - Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Keranjang Styles */
        :root {
            --primary-green: #2e8b57;
            --secondary-green: #3cb371;
            --light-green: #f0f9f4;
            --accent-green: #10b981;
            --dark-text: #1f2937;
            --light-text: #6b7280;
            --danger-red: #dc2626;
            --warning-yellow: #f59e0b;
        }

        .admin-container {
            display: flex;
            min-height: 100vh;
            background: #f8fafc;
        }

        .sidebar {
            width: 280px;
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            color: white;
            padding: 0;
            box-shadow: 2px 0 10px rgba(0,0,0,0.1);
        }

        .logo {
            padding: 30px 25px;
            font-size: 1.5rem;
            font-weight: bold;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            background: rgba(255,255,255,0.05);
        }

        .sidebar nav {
            padding: 20px 0;
        }

        .sidebar nav a {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 25px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-left: 4px solid transparent;
        }

        .sidebar nav a:hover,
        .sidebar nav a.active {
            background: rgba(255,255,255,0.1);
            color: white;
            border-left-color: white;
        }

        .sidebar nav a i {
            width: 20px;
            text-align: center;
        }

        .main {
            flex: 1;
            padding: 30px;
            overflow-y: auto;
        }

        .cart-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary-green), var(--secondary-green));
            color: white;
            padding: 40px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(46, 139, 87, 0.3);
            position: relative;
            overflow: hidden;
        }

        .page-header h2 {
            margin: 0;
            font-size: 2rem;
            font-weight: 700;
        }

        .cart-content {
            background: white;
            border-radius: 20px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            padding: 30px;
        }

        .cart-content h3 {
            font-size: 1.5rem;
            font-weight: 700;
            color: var(--dark-text);
            margin-bottom: 20px;
        }

        .cart-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding: 20px;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
        }

        .cart-item .item-info {
            display: flex;
            gap: 20px;
            align-items: center;
        }

        .cart-item .item-info img {
            width: 60px;
            height: 60px;
            border-radius: 12px;
        }

        .cart-item .item-info div {
            font-size: 1.1rem;
            color: var(--dark-text);
        }

        .cart-item .item-info .item-title {
            font-weight: 600;
            margin-bottom: 5px;
        }

        .cart-item .item-actions {
            text-align: center;
            font-size: 1rem;
            color: var(--light-text);
        }

        .cart-item .item-actions input {
            width: 50px;
            padding: 5px;
            font-size: 1rem;
            text-align: center;
        }

        .cart-item .item-actions button {
            padding: 10px 20px;
            background: var(--accent-green);
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .cart-item .item-actions button:hover {
            background: var(--primary-green);
        }

        .cart-summary {
            margin-top: 30px;
            background: var(--light-green);
            padding: 20px;
            border-radius: 12px;
        }

        .cart-summary .summary-item {
            display: flex;
            justify-content: space-between;
            font-size: 1.2rem;
            margin-bottom: 15px;
        }

        .cart-summary .summary-item .summary-label {
            font-weight: 700;
            color: var(--dark-text);
        }

        .cart-summary .summary-item .summary-value {
            color: var(--primary-green);
            font-weight: 600;
        }

        .checkout-btn {
            background: var(--primary-green);
            color: white;
            padding: 15px 30px;
            font-size: 1.2rem;
            border: none;
            border-radius: 12px;
            width: 100%;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .checkout-btn:hover {
            background: var(--secondary-green);
        }

        /* Alert Messages */
        .alert {
            padding: 15px 20px;
            border-radius: 12px;
            margin-bottom: 20px;
            font-weight: 500;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d1fae5;
            color: #065f46;
            border: 1px solid #a7f3d0;
        }

        .alert-error {
            background: #fee2e2;
            color: #991b1b;
            border: 1px solid #fca5a5;
        }

    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="logo">
                <i class="fas fa-mountain"></i>
                Lombok Hiking
            </div>
            <nav>
                <a href="dashboard.php">
                    <i class="fas fa-tachometer-alt"></i> 
                    Dashboard
                </a>
                <a href="profile.php">
                    <i class="fas fa-user"></i> 
                    Profil Saya
                </a>
                <a href="booking.php">
                    <i class="fas fa-calendar-plus"></i> 
                    Booking Trip
                </a>
                <a href="keranjang.php" class="active">
                    <i class="fas fa-shopping-cart"></i> 
                    Keranjang
                </a>
                <a href="status_pembayaran.php">
                    <i class="fas fa-credit-card"></i> 
                    Status Pembayaran
                </a>
                <a href="paket_saya.php">
                    <i class="fas fa-hiking"></i> 
                    Paket Saya
                </a>
                <a href="../logout.php">
                    <i class="fas fa-sign-out-alt"></i> 
                    Logout
                </a>
            </nav>
        </aside>

        <main class="main">
            <div class="cart-container">
                <div class="page-header">
                    <h2>Keranjang Trip Pendakian</h2>
                </div>

                <!-- Display success/error messages -->
                <?php if ($success_message): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-check-circle"></i>
                        <?= htmlspecialchars($success_message) ?>
                    </div>
                <?php endif; ?>
                <?php if ($error_message): ?>
                    <div class="alert alert-error">
                        <i class="fas fa-exclamation-circle"></i>
                        <?= htmlspecialchars($error_message) ?>
                    </div>
                <?php endif; ?>

                <div class="cart-content">
                    <h3>Daftar Trip yang Dipilih</h3>

                    <?php if ($cart_result->num_rows > 0): ?>
                        <?php while ($cart_item = $cart_result->fetch_assoc()): ?>
                            <div class="cart-item">
                                <div class="item-info">
                                    <img src="../assets/images/trips/<?= htmlspecialchars($cart_item['title']) ?>.jpg" alt="Trip Image">
                                    <div>
                                        <div class="item-title"><?= htmlspecialchars($cart_item['title']) ?></div>
                                        <div>Gunung: <?= htmlspecialchars($cart_item['mountain_name']) ?></div>
                                        <div>Peserta: <?= $cart_item['participants'] ?> orang</div>
                                        <div>Total: Rp <?= number_format($cart_item['total_price'], 0, ',', '.') ?></div>
                                    </div>
                                </div>

                                <div class="item-actions">
                                    <form method="POST" action="keranjang.php">
                                        <input type="hidden" name="booking_id" value="<?= $cart_item['id'] ?>">
                                        <input type="number" name="participants" value="<?= $cart_item['participants'] ?>" min="1" max="10">
                                        <button type="submit" name="update">Perbarui</button>
                                    </form>
                                    <form method="POST" action="keranjang.php">
                                        <input type="hidden" name="booking_id" value="<?= $cart_item['id'] ?>">
                                        <button type="submit" name="remove" style="background-color: #dc2626;">Hapus</button>
                                    </form>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <p>Keranjang Anda kosong. Silakan pilih trip yang ingin dipesan.</p>
                    <?php endif; ?>
                </div>

                <?php if ($cart_result->num_rows > 0): ?>
                    <div class="cart-summary">
                        <div class="summary-item">
                            <span class="summary-label">Total Harga:</span>
                            <span class="summary-value" id="total-price">
                                Rp <?= number_format(array_sum(array_column($cart_result->fetch_all(MYSQLI_ASSOC), 'total_price')), 0, ',', '.') ?>
                            </span>
                        </div>
                        <a href="status_pembayaran.php" class="checkout-btn">Lanjutkan ke Pembayaran</a>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>
</body>
</html>

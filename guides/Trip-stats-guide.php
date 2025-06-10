<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'guide') {
    header('Location: ../views/login.php');
    exit();
}

$guideId = $_SESSION['user_id'];

// Total trip
$totalTripQuery = $conn->prepare("SELECT COUNT(*) AS total FROM trips WHERE guide_id = ?");
$totalTripQuery->bind_param("i", $guideId);
$totalTripQuery->execute();
$totalTrip = $totalTripQuery->get_result()->fetch_assoc()['total'];

// Total booking
$totalBookingQuery = $conn->prepare("SELECT COUNT(*) AS total FROM bookings b
JOIN trips t ON b.trip_id = t.id
WHERE t.guide_id = ?");
$totalBookingQuery->bind_param("i", $guideId);
$totalBookingQuery->execute();
$totalBooking = $totalBookingQuery->get_result()->fetch_assoc()['total'];

// Pendapatan (anggap semua booking confirmed dibayar full)
$revenueQuery = $conn->prepare("SELECT SUM(t.price) AS revenue FROM bookings b
JOIN trips t ON b.trip_id = t.id
WHERE t.guide_id = ? AND b.status = 'confirmed'");
$revenueQuery->bind_param("i", $guideId);
$revenueQuery->execute();
$revenue = $revenueQuery->get_result()->fetch_assoc()['revenue'] ?? 0;

// Rating (dari tabel guides)
$ratingQuery = $conn->prepare("SELECT rating FROM guides WHERE id = ?");
$ratingQuery->bind_param("i", $guideId);
$ratingQuery->execute();
$rating = $ratingQuery->get_result()->fetch_assoc()['rating'] ?? '-';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Statistik Trip - Guide</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container" style="margin-top: 50px;">
        <h2>📊 Statistik Trip Anda</h2>

        <div class="stats-box">
            <div class="stat-item">
                <h3>Total Trip</h3>
                <p><?php echo $totalTrip; ?></p>
            </div>
            <div class="stat-item">
                <h3>Total Booking</h3>
                <p><?php echo $totalBooking; ?></p>
            </div>
            <div class="stat-item">
                <h3>Total Pendapatan</h3>
                <p>Rp <?php echo number_format($revenue, 0, ',', '.'); ?></p>
            </div>
            <div class="stat-item">
                <h3>Rating Anda</h3>
                <p><?php echo $rating; ?> ⭐</p>
            </div>
        </div>

        <a href="dashboard.php" class="btn">⬅ Kembali ke Dashboard</a>
        <a href="trip-statistics.php" class="btn btn-secondary">📈 Statistik Trip</a>
    </div>
</body>
</html>

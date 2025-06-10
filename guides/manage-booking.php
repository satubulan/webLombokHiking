<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'guide') {
    header('Location: ../views/login.php');
    exit();
}

$guideId = $_SESSION['user_id'];
$guideName = $_SESSION['user_name'];

// Ambil data booking berdasarkan guide
$query = "
    SELECT b.id AS booking_id, u.name AS user_name, t.date, t.description, b.status, t.id AS trip_id
    FROM bookings b
    JOIN users u ON b.user_id = u.id
    JOIN trips t ON b.trip_id = t.id
    WHERE t.guide_id = ?
    ORDER BY b.created_at DESC
";

$stmt = $conn->prepare($query);
$stmt->bind_param("i", $guideId);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kelola Booking - Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container" style="margin-top: 50px;">
        <h2>Booking Masuk</h2>
        <table class="table">
            <thead>
                <tr>
                    <th>Nama Peserta</th>
                    <th>Tanggal Trip</th>
                    <th>Deskripsi</th>
                    <th>Status</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['user_name']); ?></td>
                        <td><?php echo htmlspecialchars($row['date']); ?></td>
                        <td><?php echo htmlspecialchars(substr($row['description'], 0, 50)) . '...'; ?></td>
                        <td><?php echo htmlspecialchars($row['status']); ?></td>
                        <td>
                            <?php if ($row['status'] === 'pending') : ?>
                                <form method="POST" action="update-booking.php" style="display:inline-block;">
                                    <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                                    <input type="hidden" name="action" value="confirm">
                                    <button type="submit" class="btn btn-success">Konfirmasi</button>
                                </form>
                                <form method="POST" action="update-booking.php" style="display:inline-block;">
                                    <input type="hidden" name="booking_id" value="<?php echo $row['booking_id']; ?>">
                                    <input type="hidden" name="action" value="cancel">
                                    <button type="submit" class="btn btn-danger">Tolak</button>
                                </form>
                            <?php else : ?>
                                -
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="dashboard.php" class="btn" style="margin-top: 20px;">⬅️ Kembali ke Dashboard</a>
    </div>
</body>
</html>

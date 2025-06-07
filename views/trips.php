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
</head>
<body>
    <section class="container">
        <div class="section-intro">
            <h2>Jadwal Trip Pendakian</h2>
            <p>Pilih jadwal pendakian yang sesuai dan nikmati pengalaman bersama guide terbaik kami.</p>
        </div>

        <div class="guide-cards">
            <?php if (count($trips) > 0): ?>
                <?php foreach ($trips as $trip): ?>
                    <div class="guide-card">
                        <div class="guide-content">
                            <h3 class="guide-name"><?php echo htmlspecialchars($trip['mountain_name']); ?></h3>
                            <div class="guide-experience">
                                Guide: <?php echo htmlspecialchars($trip['guide_name']); ?>
                            </div>
                            <div class="guide-languages">
                                Tanggal: <?php echo date('d M Y', strtotime($trip['date'])); ?>
                            </div>
                            <div class="guide-experience">
                                Kuota: <?php echo $trip['quota']; ?> orang
                            </div>
                            <div class="guide-languages">
                                Harga: Rp<?php echo number_format($trip['price'], 0, ',', '.'); ?>
                            </div>
                            <div style="margin-top: 15px;">
                                <a href="#" class="btn btn-primary">Pesan Sekarang</a> <!-- Placeholder -->
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">
                    <p>Belum ada trip yang tersedia saat ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <script src="../assets/js/main.js"></script>
</body>
</html>

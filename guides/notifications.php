<?php
// Mulai sesi
session_start();

// Cek apakah ada notifikasi yang disimpan dalam sesi
if (isset($_SESSION['notification'])) {
    $notification = $_SESSION['notification'];
    unset($_SESSION['notification']); // Hapus notifikasi setelah ditampilkan
} else {
    $notification = null;
}

// HTML untuk menampilkan notifikasi
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi</title>
    <link rel="stylesheet" href="styles.css"> <!-- Ganti dengan lokasi file CSS Anda -->
</head>
<body>
    <div class="container">
        <h1>Notifikasi</h1>
        <?php if ($notification): ?>
            <div class="notification">
                <?php echo htmlspecialchars($notification); ?>
            </div>
        <?php else: ?>
            <p>Tidak ada notifikasi baru.</p>
        <?php endif; ?>
    </div>
</body>
</html>

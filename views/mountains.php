<?php
session_start();
require_once '../config.php';

// Ambil semua data gunung
$sql = "SELECT * FROM mountains ORDER BY name ASC";
$result = $conn->query($sql);
$mountains = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Gunung - Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <section class="container">
        <div class="section-intro">
            <h2>Daftar Gunung Pendakian</h2>
            <p>Jelajahi keindahan alam Lombok dengan memilih gunung impian Anda.</p>
        </div>

        <div class="guide-cards">
            <?php if (count($mountains) > 0): ?>
                <?php foreach ($mountains as $mountain): ?>
                    <div class="guide-card">
                        <div class="guide-image">
                            <img src="../assets/images/<?php echo htmlspecialchars($mountain['image_url']); ?>" alt="<?php echo htmlspecialchars($mountain['name']); ?>">
                        </div>
                        <div class="guide-content">
                            <h3 class="guide-name"><?php echo htmlspecialchars($mountain['name']); ?></h3>
                            <div class="guide-experience">
                                Tinggi: <?php echo intval($mountain['height']); ?> mdpl
                            </div>
                            <div class="guide-languages">
                                <?php echo mb_substr(strip_tags($mountain['description']), 0, 100) . '...'; ?>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">
                    <p>Tidak ada data gunung tersedia.</p>
                </div>
            <?php endif; ?>
        </div>
    </section>

    <script src="../assets/js/main.js"></script>
</body>
</html>

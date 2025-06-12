<?php
session_start();
require_once '../config.php';

// Cek login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

// Ambil data trip berdasarkan ID
if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    $result = $conn->query("SELECT * FROM trips WHERE id = $id");
    if ($result->num_rows === 0) {
        header("Location: trips.php");
        exit();
    }
    $trip = $result->fetch_assoc();
} else {
    header("Location: trips.php");
    exit();
}

// Ambil data gunung dan guide untuk dropdown
$mountains_query = "SELECT id, name FROM mountains ORDER BY name ASC";
$mountains_result = $conn->query($mountains_query);
if (!$mountains_result) {
    die("Error fetching mountains: " . $conn->error);
}
$mountains = $mountains_result->fetch_all(MYSQLI_ASSOC);

$guides_query = "SELECT id, name FROM guides ORDER BY name ASC";
$guides_result = $conn->query($guides_query);
if (!$guides_result) {
    die("Error fetching guides: " . $conn->error);
}
$guides = $guides_result->fetch_all(MYSQLI_ASSOC);

// Jika form disubmit (POST), update data trip
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $mountain_id = intval($_POST['mountain_id']);
    $guide_id = intval($_POST['guide_id']);
    $date = $conn->real_escape_string($_POST['date']);
    $price = floatval($_POST['price']);
    $quota = intval($_POST['quota']);
    $active = isset($_POST['active']) ? 1 : 0;

    $sql = "UPDATE trips SET 
            mountain_id = $mountain_id,
            guide_id = $guide_id,
            date = '$date',
            price = $price,
            quota = $quota,
            active = $active
            WHERE id = $id";
    
    if (!$conn->query($sql)) {
        $error = "Error: " . $conn->error;
    } else {
        header("Location: trips.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Trip</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="../assets/css/users.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
<div class="admin-layout" style="display:flex;min-height:100vh;">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="nav-section-title">Admin Panel</div>
        <ul class="nav-links">
            <li><a href="index.php" class="nav-link"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li><a href="users.php" class="nav-link"><i class="fas fa-users"></i> Pengguna</a></li>
            <li><a href="guides.php" class="nav-link"><i class="fas fa-map-signs"></i> Guide</a></li>
            <li><a href="mountains.php" class="nav-link"><i class="fas fa-mountain"></i> Gunung</a></li>
            <li><a href="trips.php" class="nav-link"><i class="fas fa-route"></i> Trip</a></li>
            <li><a href="lihat_pembayaran.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Lihat Pembayaran</a></li>
            <li><a href="feedback.php" class="nav-link"><i class="fas fa-comment-dots"></i> Feedback</a></li>
            <li><a href="profile.php" class="nav-link"><i class="fas fa-user-cog"></i> Profil</a></li>
            <li><a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="admin-main" style="flex:1;padding:30px;overflow-x:auto;">
        <div class="admin-header">
            <h1>Edit Trip</h1>
            <a href="trips.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Kembali</a>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <!-- Form edit trip -->
        <div class="admin-form-container">
            <form method="POST" class="admin-form">
                <div class="form-group">
                    <label for="mountain_id">Gunung</label>
                    <select name="mountain_id" id="mountain_id" required>
                        <option value="">Pilih Gunung</option>
                        <?php foreach ($mountains as $mountain): ?>
                            <option value="<?php echo $mountain['id']; ?>" <?php echo ($mountain['id'] == $trip['mountain_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($mountain['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="guide_id">Guide</label>
                    <select name="guide_id" id="guide_id" required>
                        <option value="">Pilih Guide</option>
                        <?php foreach ($guides as $guide): ?>
                            <option value="<?php echo $guide['id']; ?>" <?php echo ($guide['id'] == $trip['guide_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($guide['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="date">Tanggal</label>
                    <input type="date" name="date" id="date" value="<?php echo $trip['date']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="price">Harga</label>
                    <input type="number" name="price" id="price" value="<?php echo $trip['price']; ?>" required min="0">
                </div>

                <div class="form-group">
                    <label for="quota">Kuota</label>
                    <input type="number" name="quota" id="quota" value="<?php echo $trip['quota']; ?>" required min="1">
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="active" <?php echo $trip['active'] ? 'checked' : ''; ?>>
                        Aktif
                    </label>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Simpan</button>
                    <a href="trips.php" class="btn btn-secondary"><i class="fas fa-times"></i> Batal</a>
                </div>
            </form>
        </div>
    </main>
</div>
</body>
</html> 
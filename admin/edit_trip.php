<?php
session_start();
require_once '../config.php';

// Cek login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

$message = '';
$error = '';

// Ambil data trip berdasarkan ID
if (isset($_GET['id'])) {
    $id = $_GET['id']; // ID is VARCHAR now
    $stmt_select = $conn->prepare("SELECT * FROM trips WHERE id = ?");
    $stmt_select->bind_param("s", $id);
    $stmt_select->execute();
    $result = $stmt_select->get_result();
    
    if ($result->num_rows === 0) {
        header("Location: trips.php");
        exit();
    }
    $trip = $result->fetch_assoc();
    $stmt_select->close();
} else {
    header("Location: trips.php");
    exit();
}

// Ambil data gunung dan guide untuk dropdown
$mountains_query = "SELECT id, name, height FROM mountains ORDER BY name ASC";
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
    $title = $conn->real_escape_string($_POST['title']);
    $description = $conn->real_escape_string($_POST['description']);
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $duration = intval($_POST['duration']);
    $price = floatval($_POST['price']);
    $max_participants = intval($_POST['max_participants']);
    $included = $conn->real_escape_string($_POST['included']);
    $not_included = $conn->real_escape_string($_POST['not_included']);
    $meeting_point = $conn->real_escape_string($_POST['meeting_point']);
    $active = isset($_POST['active']) ? 1 : 0; // Checkbox for active status

    $sql = "UPDATE trips SET 
            mountain_id = ?,
            guide_id = ?,
            title = ?,
            description = ?,
            start_date = ?,
            end_date = ?,
            duration = ?,
            price = ?,
            max_participants = ?,
            included = ?,
            not_included = ?,
            meeting_point = ?,
            active = ?
            WHERE id = ?";
    
    $stmt_update = $conn->prepare($sql);
    $stmt_update->bind_param("iissssiidisssis", 
                            $mountain_id, $guide_id, $title, $description, $start_date, 
                            $end_date, $duration, $price, $max_participants, $included, 
                            $not_included, $meeting_point, $active, $id);
    
    if ($stmt_update->execute()) {
        $message = "Trip berhasil diperbarui!";
        // Refresh trip data after update to display latest changes
        $stmt_select = $conn->prepare("SELECT * FROM trips WHERE id = ?");
        $stmt_select->bind_param("s", $id);
        $stmt_select->execute();
        $result = $stmt_select->get_result();
        $trip = $result->fetch_assoc();
        $stmt_select->close();
    } else {
        $error = "Error: " . $stmt_update->error;
    }
    $stmt_update->close();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Trip - Admin Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/guide.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        /* CSS from trip_create.php */
        .admin-form-container {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-top: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        .form-group input[type="text"],
        .form-group input[type="number"],
        .form-group input[type="date"],
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #e9ecef;
            border-radius: 8px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-group input[type="text"]:focus,
        .form-group input[type="number"]:focus,
        .form-group input[type="date"]:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            border-color: #2e8b57;
            box-shadow: 0 0 0 3px rgba(46, 139, 87, 0.1);
            outline: none;
        }

        .form-group textarea {
            min-height: 120px;
            resize: vertical;
        }

        .form-group-actions {
            display: flex;
            gap: 15px;
            margin-top: 30px;
        }

        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .btn-primary {
            background: linear-gradient(135deg, #2e8b57, #3cb371);
            color: white;
            box-shadow: 0 4px 15px rgba(46, 139, 87, 0.3);
        }

        .btn-secondary {
            background: #e9ecef;
            color: #495057;
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 139, 87, 0.4);
        }

        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .admin-header {
            margin-bottom: 30px;
        }

        .admin-header h1 {
            color: #2c3e50;
            font-size: 24px;
            font-weight: 700;
            margin-bottom: 10px;
        }

        .admin-header p {
            color: #6c757d;
            font-size: 16px;
        }
    </style>
</head>
<body>
<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="nav-section-title">Admin Panel</div>
        <ul class="nav-links">
            <li><a href="index.php" class="nav-link"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li><a href="users.php" class="nav-link"><i class="fas fa-users"></i> Pengguna</a></li>
            <li><a href="guides.php" class="nav-link"><i class="fas fa-map-signs"></i> Guide</a></li>
            <li><a href="mountains.php" class="nav-link"><i class="fas fa-mountain"></i> Gunung</a></li>
            <li><a href="trips.php" class="nav-link active"><i class="fas fa-route"></i> Trip</a></li>
            <li><a href="profile.php" class="nav-link"><i class="fas fa-user-cog"></i> Profil</a></li>
            <li><a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="admin-main"

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $message; ?>
            </div>
        <?php endif; ?>

        <!-- Form edit trip -->
        <div class="admin-form-container">
            <form method="POST" class="admin-form">
                <div class="form-group">
                    <label for="mountain_id">Pilih Gunung:</label>
                    <select name="mountain_id" id="mountain_id" required>
                        <option value="">-- Pilih Gunung --</option>
                        <?php foreach ($mountains as $mountain): ?>
                            <option value="<?php echo $mountain['id']; ?>" <?php echo ($mountain['id'] == $trip['mountain_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($mountain['name']); ?> (<?php echo $mountain['height']; ?>m)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="guide_id">Pilih Guide:</label>
                    <select name="guide_id" id="guide_id" required>
                        <option value="">-- Pilih Guide --</option>
                        <?php foreach ($guides as $guide): ?>
                            <option value="<?php echo $guide['id']; ?>" <?php echo ($guide['id'] == $trip['guide_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($guide['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="title">Judul Trip:</label>
                    <input type="text" id="title" name="title" placeholder="Contoh: Pendakian Rinjani 3D2N" value="<?php echo htmlspecialchars($trip['title']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="description">Deskripsi Trip:</label>
                    <textarea id="description" name="description" placeholder="Jelaskan detail trip, rute, aktivitas, dan hal menarik lainnya..." required><?php echo htmlspecialchars($trip['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="start_date">Tanggal Mulai:</label>
                    <input type="date" id="start_date" name="start_date" value="<?php echo $trip['start_date']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="end_date">Tanggal Selesai:</label>
                    <input type="date" id="end_date" name="end_date" value="<?php echo $trip['end_date']; ?>" required>
                </div>

                <div class="form-group">
                    <label for="duration">Durasi (hari):</label>
                    <input type="number" id="duration" name="duration" min="1" max="30" value="<?php echo htmlspecialchars($trip['duration']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="max_participants">Maksimal Peserta:</label>
                    <input type="number" id="max_participants" name="max_participants" min="1" max="50" value="<?php echo htmlspecialchars($trip['max_participants']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="price">Harga per Orang (Rp):</label>
                    <input type="number" id="price" name="price" min="0" step="1000" placeholder="2500000" value="<?php echo htmlspecialchars($trip['price']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="meeting_point">Titik Kumpul:</label>
                    <input type="text" id="meeting_point" name="meeting_point" placeholder="Contoh: Basecamp Sembalun" value="<?php echo htmlspecialchars($trip['meeting_point']); ?>" required>
                </div>

                <div class="form-group">
                    <label for="included">Yang Termasuk:</label>
                    <textarea id="included" name="included" placeholder="Contoh: Guide berpengalaman, Peralatan camping, Makan 3x sehari, Transport lokal..." required><?php echo htmlspecialchars($trip['included']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="not_included">Yang Tidak Termasuk:</label>
                    <textarea id="not_included" name="not_included" placeholder="Contoh: Tiket pesawat, Asuransi perjalanan, Pengeluaran pribadi..." required><?php echo htmlspecialchars($trip['not_included']); ?></textarea>
                </div>

                <div class="form-group">
                    <label>
                        <input type="checkbox" name="active" <?php echo $trip['active'] ? 'checked' : ''; ?>>
                        Aktif
                    </label>
                </div>

                <div class="form-group form-group-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i>
                        <span>Simpan</span>
                    </button>
                    <a href="trips.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i>
                        <span>Batal</span>
                    </a>
                </div>
            </form>
        </div>
    </main>
</div>
<script>
// Auto-hide alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.transition = 'opacity 0.5s ease';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    });
}, 5000);
</script>
</body>
</html> 
<?php
session_start();
require_once '../config.php';

// Cek login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

$adminId = $_SESSION['user_id'];

// Ambil data admin
$query = $conn->prepare("SELECT name, email, phone, profile_picture FROM users WHERE id = ?");
$query->bind_param("s", $adminId);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Profil - Admin Lombok Hiking</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        /* ===== Reset dan Dasar ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
            color: #333;
        }

        /* ===== Layout Utama ===== */
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .admin-sidebar {
            width: 220px;
            background-color: #2e8b57;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .admin-sidebar .nav-section-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 30px;
            user-select: none;
        }

        .admin-sidebar .nav-links {
            list-style: none;
            flex-grow: 1;
        }

        .admin-sidebar .nav-links li {
            margin: 10px 0;
        }

        .admin-sidebar .nav-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 6px;
            transition: background 0.3s;
            font-weight: 600;
        }

        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background-color: #246b46;
        }

        /* ===== Header ===== */
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .admin-header h1 {
            font-size: 24px;
            margin-bottom: 8px;
        }

        /* ===== Main Area ===== */
        .admin-main {
            flex: 1;
            padding: 30px;
            overflow-x: auto;
        }

        /* ===== Profile Container ===== */
        .profile-container {
            max-width: 600px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin: 0 auto;
            text-align: center; /* Center align text */
        }

        .profile-card {
            text-align: center;
        }

        /* ===== Profile Avatar ===== */
        .profile-avatar {
            margin-bottom: 20px;
            position: relative;
            display: flex; /* Use flexbox for centering */
            justify-content: center; /* Center horizontally */
            align-items: center; /* Center vertically */
        }

        .profile-avatar img,
        .profile-avatar .empty-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #2e8b57;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .profile-avatar .empty-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: #999;
            background: #f0f0f0;
        }

        /* ===== Profile Info ===== */
        .profile-info h3 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #2e8b57;
        }

        .info-item {
            margin-bottom: 15px;
            text-align: left;
            padding: 10px;
            border-radius: 8px;
            background: rgba(46, 139, 87, 0.1);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .info-item label {
            font-weight: bold;
            color: #2e8b57;
        }

        .info-item p {
            margin: 0;
            color: #333;
        }

        /* ===== Responsive ===== */
        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                gap: 15px;
            }

            .profile-container {
                padding: 20px;
            }
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
            <li><a href="trips.php" class="nav-link"><i class="fas fa-route"></i> Trip</a></li>
            <li><a href="profile.php" class="nav-link active"><i class="fas fa-user-cog"></i> Profil</a></li>
            <li><a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="admin-main">
        <div class="profile-container">
            <div class="profile-card">
                <div class="profile-avatar">
                    <?php if (!empty($user['profile_picture']) && file_exists('../assets/images/profiles/' . $user['profile_picture'])): ?>
                        <img src="../assets/images/profiles/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Foto Profil" />
                    <?php else: ?>
                        <div class="empty-avatar" aria-label="Foto profil kosong">
                            <i class="fas fa-user-circle"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="profile-info">
                    <h3>Informasi Profile</h3>
                    <div class="info-item">
                        <label>Nama:</label>
                        <p><?php echo htmlspecialchars($user['name']); ?></p>
                    </div>
                    <div class="info-item">
                        <label>Email:</label>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div class="info-item">
                        <label>Nomor Telepon:</label>
                        <p><?php echo htmlspecialchars($user['phone']); ?></p>
                    </div>
                </div>
            </div>
        </div>
    </main>
</div>
</body>
</html>

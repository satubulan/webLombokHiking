<?php
session_start();
require_once '../config.php';

// Cek login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

$adminId = $_SESSION['user_id'];
$query = $conn->prepare("SELECT name, email, phone, role, profile_picture FROM users WHERE id = ?");

// Cek apakah prepare statement berhasil
if ($query === false) {
    die("Error preparing statement: " . $conn->error);
}

$query->bind_param("i", $adminId);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();

// Jika ada pesan sukses dari halaman lain
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="../assets/css/trips.css" />
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
            <li><a href="bookings.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Booking</a></li>
            <li><a href="feedback.php" class="nav-link"><i class="fas fa-comment-dots"></i> Feedback</a></li>
            <li><a href="profile.php" class="nav-link active"><i class="fas fa-user-cog"></i> Profil</a></li>
            <li><a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="admin-main" style="flex:1;padding:30px;overflow-x:auto;">
        <div class="admin-header">
            <h1>Profil Admin</h1>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <!-- Profil Card -->
        <div class="profile-container">
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php if ($user['profile_picture']): ?>
                            <img src="../assets/images/profiles/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile Image">
                        <?php else: ?>
                            <i class="fas fa-user-circle"></i>
                        <?php endif; ?>
                    </div>
                    <h1 class="profile-name"><?php echo htmlspecialchars($user['name']); ?></h1>
                    <span class="profile-role"><?php echo ucfirst(htmlspecialchars($user['role'])); ?></span>
                </div>

                <div class="profile-info">
                    <div class="info-section">
                        <h3>Informasi Pribadi</h3>
                        <div class="info-group">
                            <div class="info-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Email</div>
                                <div class="info-value"><?php echo htmlspecialchars($user['email']); ?></div>
                            </div>
                        </div>

                        <div class="info-group">
                            <div class="info-icon">
                                <i class="fas fa-phone"></i>
                            </div>
                            <div class="info-content">
                                <div class="info-label">Nomor Telepon</div>
                                <div class="info-value"><?php echo htmlspecialchars($user['phone']); ?></div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="profile-actions">
                    <a href="edit_profile.php" class="btn btn-edit">
                        <i class="fas fa-edit"></i>
                        Edit Profil
                    </a>
                    <a href="change_password.php" class="btn btn-password">
                        <i class="fas fa-key"></i>
                        Ubah Password
                    </a>
                </div>
            </div>
        </div>
    </main>
</div>

<style>
.profile-card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 30px;
}

.profile-header {
    text-align: center;
    margin-bottom: 30px;
}

.profile-avatar {
    width: 150px;
    height: 150px;
    margin: 0 auto 20px;
    border-radius: 50%;
    overflow: hidden;
    background: #f5f5f5;
    display: flex;
    align-items: center;
    justify-content: center;
}

.profile-avatar img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.profile-avatar i {
    font-size: 100px;
    color: #ccc;
}

.profile-header h2 {
    margin: 0;
    color: #333;
    font-size: 24px;
}

.profile-role {
    color: #666;
    font-size: 14px;
    display: block;
    margin-top: 5px;
}

.profile-info {
    margin-bottom: 30px;
}

.info-group {
    margin-bottom: 20px;
}

.info-group label {
    display: block;
    color: #666;
    margin-bottom: 5px;
    font-size: 14px;
}

.info-group label i {
    margin-right: 8px;
    color: #4a90e2;
}

.info-group p {
    margin: 0;
    color: #333;
    font-size: 16px;
    padding: 8px 0;
    border-bottom: 1px solid #eee;
}

.profile-actions {
    display: flex;
    gap: 10px;
    justify-content: center;
}

.profile-actions .btn {
    padding: 10px 20px;
    border-radius: 4px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.profile-actions .btn i {
    margin-right: 8px;
}

.btn-primary {
    background: #4a90e2;
    color: white;
}

.btn-primary:hover {
    background: #357abd;
}

.btn-secondary {
    background: #f5f5f5;
    color: #333;
}

.btn-secondary:hover {
    background: #e0e0e0;
}

.alert {
    padding: 15px;
    border-radius: 4px;
    margin-bottom: 20px;
}

.alert-success {
    background: #efe;
    color: #0c0;
    border: 1px solid #cfc;
}
</style>
</body>
</html>

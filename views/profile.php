<?php
session_start();
require_once '../config.php';

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$userId = $_SESSION['user_id'];
$query = $conn->prepare("SELECT name, email, phone, role, profile_picture FROM users WHERE id = ?");
$query->bind_param("i", $userId);
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
    <title>Profil Saya - Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/trips.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <div class="container">
        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <div class="profile-container">
            <div class="profile-card">
                <div class="profile-header">
                    <div class="profile-avatar">
                        <?php if ($user['profile_picture']): ?>
                            <img src="../assets/images/profiles/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Foto Profil">
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
    </div>
</body>
</html> 
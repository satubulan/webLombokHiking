<?php
session_start();
require_once '../config.php';

// Redirect kalau belum login atau bukan guide
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'guide') {
    header('Location: ../views/login.php');
    exit();
}

$guideId = $_SESSION['user_id'];
$guideName = $_SESSION['user_name'];

$stmt = $conn->prepare("SELECT name, email, phone, bio, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $guideId);
$stmt->execute();
$result = $stmt->get_result();
$userData = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Guide - Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/styles.css"> <!-- jika punya CSS terpisah -->
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 2rem;
            background: #f0f4f8;
        }
        .profile-container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 1rem;
        }
        .profile-info {
            margin-bottom: 1rem;
        }
        .profile-info label {
            font-weight: bold;
        }
        .edit-btn {
            display: inline-block;
            padding: 0.7rem 1.2rem;
            background: #10b981;
            color: white;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
        }
    </style>
</head>
<body>

<div class="profile-container">
    <h2>👤 Profil Guide</h2>
    
    <?php if ($userData['profile_picture']): ?>
        <img src="../uploads/<?php echo htmlspecialchars($userData['profile_picture']); ?>" class="profile-picture" alt="Foto Profil">
    <?php else: ?>
        <img src="../uploads/default.png" class="profile-picture" alt="Default Foto Profil">
    <?php endif; ?>

    <div class="profile-info">
        <p><label>Nama:</label> <?php echo htmlspecialchars($userData['name']); ?></p>
        <p><label>Email:</label> <?php echo htmlspecialchars($userData['email']); ?></p>
        <p><label>No HP:</label> <?php echo htmlspecialchars($userData['phone']); ?></p>
        <p><label>Bio:</label> <?php echo nl2br(htmlspecialchars($userData['bio'])); ?></p>
    </div>

    <a href="edit-profile.php" class="edit-btn">✏️ Edit Profil</a>
</div>

</body>
</html>

<?php
session_start();
require_once '../config.php';

// Cek apakah user login dan role-nya guide
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'guide') {
    header('Location: ../views/login.php');
    exit();
}

$guideId = $_SESSION['user_id'];
$errors = [];
$success = '';

// Ambil data user saat ini
$stmt = $conn->prepare("SELECT name, phone, bio, profile_picture FROM users WHERE id = ?");
$stmt->bind_param("i", $guideId);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();

// Kalau form disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $bio = trim($_POST['bio']);

    // Validasi sederhana
    if (empty($name)) {
        $errors[] = "Nama tidak boleh kosong.";
    }

    // Upload foto jika ada
    if ($_FILES['profile_picture']['name']) {
        $targetDir = "../uploads/";
        $filename = basename($_FILES["profile_picture"]["name"]);
        $targetFile = $targetDir . $filename;

        if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $targetFile)) {
            $profile_picture = $filename;
        } else {
            $errors[] = "Gagal mengunggah foto.";
        }
    } else {
        $profile_picture = $user['profile_picture']; // tetap pakai yang lama
    }

    // Kalau tidak ada error, update data
    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE users SET name = ?, phone = ?, bio = ?, profile_picture = ? WHERE id = ?");
        $stmt->bind_param("ssssi", $name, $phone, $bio, $profile_picture, $guideId);
        if ($stmt->execute()) {
            $success = "Profil berhasil diperbarui.";
            $_SESSION['user_name'] = $name; // update session juga
            header("Location: profile.php"); // kembali ke profile
            exit();
        } else {
            $errors[] = "Gagal menyimpan data.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Profil Guide</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #eef2f7;
            padding: 2rem;
        }
        .form-container {
            max-width: 600px;
            margin: auto;
            background: white;
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        input, textarea {
            width: 100%;
            padding: 0.8rem;
            margin-bottom: 1rem;
            border-radius: 8px;
            border: 1px solid #ccc;
        }
        button {
            background-color: #3b82f6;
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 8px;
            font-weight: bold;
        }
        .error {
            background: #fee2e2;
            padding: 1rem;
            margin-bottom: 1rem;
            color: #991b1b;
            border-radius: 8px;
        }
        .success {
            background: #d1fae5;
            padding: 1rem;
            margin-bottom: 1rem;
            color: #065f46;
            border-radius: 8px;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Edit Profil</h2>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <ul>
                <?php foreach ($errors as $e): ?>
                    <li><?= htmlspecialchars($e) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="" method="post" enctype="multipart/form-data">
        <label>Nama:</label>
        <input type="text" name="name" value="<?= htmlspecialchars($user['name']) ?>">

        <label>No HP:</label>
        <input type="text" name="phone" value="<?= htmlspecialchars($user['phone']) ?>">

        <label>Bio:</label>
        <textarea name="bio" rows="4"><?= htmlspecialchars($user['bio']) ?></textarea>

        <label>Foto Profil (opsional):</label>
        <input type="file" name="profile_picture" accept="image/*">

        <button type="submit">💾 Simpan Perubahan</button>
    </form>
</div>

</body>
</html>

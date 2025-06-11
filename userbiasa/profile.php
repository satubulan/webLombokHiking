<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: ../views/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
if (!$user_id || strlen($user_id) < 5) {
    echo '<pre>'; var_dump($_SESSION); echo '</pre>';
    die('User ID tidak valid.');
}

// Ambil data user
$query = "SELECT * FROM users WHERE id = '$user_id'";
$result = mysqli_query($conn, $query);
$user = mysqli_fetch_assoc($result);

// Ganti profil
if (isset($_POST['update_profile'])) {
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $avatar = $user['avatar'];

    if ($_FILES['foto']['name']) {
        $target = '../assets/images/' . basename($_FILES['foto']['name']);
        if (move_uploaded_file($_FILES['foto']['tmp_name'], $target)) {
            $avatar = $_FILES['foto']['name'];
        }
    }

    $update = "UPDATE users SET name='$name', email='$email', phone='$phone', avatar='$avatar' WHERE id='$user_id'";
    mysqli_query($conn, $update);
    $_SESSION['user_name'] = $name;
    header('Location: profile.php');
    exit();
}

// Reset foto ke default
if (isset($_POST['reset_foto'])) {
    $update = "UPDATE users SET avatar='default.jpg' WHERE id='$user_id'";
    mysqli_query($conn, $update);
    header('Location: profile.php');
    exit();
}

// Ganti password
if (isset($_POST['change_password'])) {
    $pass1 = mysqli_real_escape_string($conn, $_POST['password1']);
    $pass2 = mysqli_real_escape_string($conn, $_POST['password2']);

    if ($pass1 === $pass2 && strlen($pass1) >= 6) {
        $hashed = password_hash($pass1, PASSWORD_DEFAULT);
        mysqli_query($conn, "UPDATE users SET password='$hashed' WHERE id='$user_id'");
        $pass_msg = "Password berhasil diganti.";
    } else {
        $pass_msg = "Password tidak cocok atau terlalu pendek (min. 6 karakter).";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Profil Saya</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        body {
            font-family: 'Segoe UI', sans-serif;
            padding: 40px;
            background: #f5f5f5;
        }
        .profile-container {
            max-width: 600px;
            margin: auto;
            background: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            position: relative;
        }
        .back-button {
            position: absolute;
            top: 20px;
            left: 20px;
            font-size: 22px;
            color: #2e8b57;
            text-decoration: none;
        }
        .profile-container h2 {
            text-align: center;
            color: #2e8b57;
            margin-bottom: 30px;
        }
        .form-group { margin-bottom: 20px; }
        label { font-weight: bold; display: block; margin-bottom: 5px; }
        input[type="text"], input[type="email"], input[type="file"], input[type="password"] {
            width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 5px;
        }
        button {
            background: #2e8b57; color: white; padding: 10px 20px; border: none;
            border-radius: 5px; cursor: pointer; margin-top: 10px;
        }
        .profile-image {
            text-align: center; margin-bottom: 20px; position: relative;
        }
        .profile-image img {
            width: 120px; height: 120px; object-fit: cover; border-radius: 50%;
            border: 2px solid #ccc; cursor: pointer;
        }
        .profile-actions {
            text-align: center;
        }
        .success-msg { color: green; text-align: center; margin-top: 10px; }
        .error-msg { color: red; text-align: center; margin-top: 10px; }
    </style>
</head>
<body>
    <div class="profile-container">
        <a href="index.php" class="back-button" title="Kembali"><i class="fas fa-arrow-left"></i></a>

        <h2>Profil Saya</h2>
        <form method="POST" enctype="multipart/form-data">
            <div class="profile-image">
                <label for="foto">
                    <img src="../assets/images/<?php echo htmlspecialchars($user['avatar'] ?: 'default.jpg'); ?>" alt="Foto Profil">
                </label>
                <input type="file" name="foto" id="foto" style="display: none;">
            </div>
            <div class="form-group">
                <label>Nama Lengkap</label>
                <input type="text" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
            </div>
            <div class="form-group">
                <label>Email</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
            </div>
            <div class="form-group">
                <label>No. Telepon</label>
                <input type="text" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>">
            </div>
            <div class="profile-actions">
                <button type="submit" name="update_profile">Simpan Perubahan</button>
                <button type="submit" name="reset_foto" style="background: #ccc; color: black;">Hapus Foto</button>
            </div>
        </form>

        <hr style="margin: 30px 0;">
        <h3>Ganti Password</h3>
        <form method="POST">
            <!-- Password Baru -->
            <div class="form-group">
                <label>Password Baru</label>
                <div style="position: relative;">
                    <input type="password" name="password1" id="password1" required>
                    <span class="toggle-password" data-target="password1" style="position: absolute; right: 10px; top: 10px; cursor: pointer;">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>

            <!-- Konfirmasi Password -->
            <div class="form-group">
                <label>Konfirmasi Password</label>
                <div style="position: relative;">
                    <input type="password" name="password2" id="password2" required>
                    <span class="toggle-password" data-target="password2" style="position: absolute; right: 10px; top: 10px; cursor: pointer;">
                        <i class="fas fa-eye"></i>
                    </span>
                </div>
            </div>

            <button type="submit" name="change_password">Ganti Password</button>
        </form>
        <?php if (isset($pass_msg)) echo '<p class="' . (str_contains($pass_msg, 'berhasil') ? 'success-msg' : 'error-msg') . '">' . $pass_msg . '</p>'; ?>
    </div>
<script src="../assets/js/main.js?v=<?php echo time(); ?>" defer></script>
</body>
</html>

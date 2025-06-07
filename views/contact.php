<?php
session_start();
require_once '../config.php';

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    $message = trim($_POST['message']);

    if (empty($message)) {
        $error = "Pesan tidak boleh kosong.";
    } elseif (!$user_id) {
        $error = "Anda harus login untuk mengirim pesan.";
    } else {
        $stmt = $conn->prepare("INSERT INTO feedback (user_id, message) VALUES (?, ?)");
        $stmt->bind_param("is", $user_id, $message);

        if ($stmt->execute()) {
            $success = "Pesan Anda berhasil dikirim.";
        } else {
            $error = "Gagal mengirim pesan. Silakan coba lagi.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak - Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-header">
            <h2>Hubungi Kami</h2>
            <p>Silakan kirimkan pertanyaan atau saran Anda kepada kami.</p>
        </div>

        <?php if (!empty($error)) : ?>
            <div class="error-message"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php if (!empty($success)) : ?>
            <div class="success-message" style="color: green;"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>

        <form class="auth-form" method="POST" action="">
            <div class="form-group">
                <label for="message">Pesan</label>
                <textarea name="message" id="message" required rows="5" placeholder="Tulis pesan Anda..."></textarea>
            </div>

            <button type="submit" class="btn btn-primary">Kirim</button>
        </form>
    </div>

    <script src="../assets/js/main.js"></script>
</body>
</html>

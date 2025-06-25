<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit('Forbidden');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_SESSION['user_id'];
    $old_password = $_POST['old_password'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];
    // Ambil password lama dari database
    $result = $conn->query("SELECT password FROM users WHERE id = $id");
    $user = $result->fetch_assoc();
    if (!$user || !password_verify($old_password, $user['password'])) {
        http_response_code(400);
        echo 'Password lama salah!';
        exit();
    }
    if ($new_password !== $confirm_password) {
        http_response_code(400);
        echo 'Konfirmasi password tidak cocok!';
        exit();
    }
    $new_hash = password_hash($new_password, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET password=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $new_hash, $id);
    if ($stmt->execute()) {
        echo 'success';
    } else {
        http_response_code(500);
        echo 'Gagal update password!';
    }
    exit();
} 
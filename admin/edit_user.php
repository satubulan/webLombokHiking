<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit('Forbidden');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_SESSION['user_id'];
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $profile_picture = null;
    // Handle upload foto profile jika ada
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['profile_picture']['name'], PATHINFO_EXTENSION);
        $profile_picture = uniqid('profile_') . '.' . $ext;
        move_uploaded_file($_FILES['profile_picture']['tmp_name'], '../assets/images/profiles/' . $profile_picture);
        $sql = "UPDATE users SET name=?, email=?, phone=?, profile_picture=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssssi", $name, $email, $phone, $profile_picture, $id);
    } else {
        $sql = "UPDATE users SET name=?, email=?, phone=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssi", $name, $email, $phone, $id);
    }
    if ($stmt->execute()) {
        // Ambil data terbaru
        $result = $conn->query("SELECT name, email, phone, profile_picture FROM users WHERE id = $id");
        $user = $result->fetch_assoc();
        header('Content-Type: application/json');
        echo json_encode(['success'=>true,'user'=>$user]);
    } else {
        http_response_code(500);
        echo 'Gagal update profile!';
    }
    exit();
} 
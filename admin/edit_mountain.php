<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit('Forbidden');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $height = intval($_POST['height']);
    $image = null;
    if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
        $image = uniqid('mountain_') . '.' . $ext;
        move_uploaded_file($_FILES['image']['tmp_name'], '../assets/images/mountains/' . $image);
    }
    if ($image) {
        $sql = "UPDATE mountains SET name=?, description=?, height=?, image=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssisi", $name, $description, $height, $image, $id);
    } else {
        $sql = "UPDATE mountains SET name=?, description=?, height=? WHERE id=?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssii", $name, $description, $height, $id);
    }
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
    exit();
} 
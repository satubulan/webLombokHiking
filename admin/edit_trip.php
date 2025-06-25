<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit('Forbidden');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = intval($_POST['id']);
    $title = $conn->real_escape_string($_POST['title']);
    $type = $conn->real_escape_string($_POST['type']);
    $mountain_id = intval($_POST['mountain_id']);
    $price = floatval($_POST['price']);
    $status = $conn->real_escape_string($_POST['status']);
    $sql = "UPDATE mountain_tickets SET title=?, type=?, mountain_id=?, price=?, status=? WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssidsi", $title, $type, $mountain_id, $price, $status, $id);
    if ($stmt->execute()) {
        echo 'success';
    } else {
        echo 'error';
    }
    exit();
} 
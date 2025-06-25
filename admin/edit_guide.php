<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    http_response_code(403);
    exit('Forbidden');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $guide_id = intval($_POST['guide_id']);
    $user_id = intval($_POST['user_id']);
    $name = $conn->real_escape_string($_POST['name']);
    $email = $conn->real_escape_string($_POST['email']);
    $phone = $conn->real_escape_string($_POST['phone']);
    $status = $_POST['status'] == '1' ? 'approved' : 'rejected';
    $specialization = $conn->real_escape_string($_POST['specialization']);
    $experience = $conn->real_escape_string($_POST['experience']);
    $languages = $conn->real_escape_string($_POST['languages']);
    $bio = $conn->real_escape_string($_POST['bio']);
    $sql1 = "UPDATE users SET name=?, email=?, phone=? WHERE id=?";
    $stmt1 = $conn->prepare($sql1);
    $stmt1->bind_param("sssi", $name, $email, $phone, $user_id);
    $sql2 = "UPDATE guide SET status=?, specialization=?, experience=?, languages=?, bio=? WHERE id=?";
    $stmt2 = $conn->prepare($sql2);
    $stmt2->bind_param("sssssi", $status, $specialization, $experience, $languages, $bio, $guide_id);
    $ok1 = $stmt1->execute();
    $ok2 = $stmt2->execute();
    if ($ok1 && $ok2) {
        echo 'success';
    } else {
        echo 'error';
    }
    exit();
} 
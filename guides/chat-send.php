<?php
session_start();
require_once '../config.php'; // atau sesuaikan path kamu

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $trip_id = intval($_POST['trip_id']);
    $message = trim($_POST['message']);
    $sender_id = $_SESSION['user_id'];
    $sender_role = $_SESSION['user_role'];

    if ($trip_id && $message && $sender_id) {
        $stmt = $conn->prepare("INSERT INTO chat_messages (trip_id, sender_id, sender_role, message) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $trip_id, $sender_id, $sender_role, $message);
        $stmt->execute();
    }
}

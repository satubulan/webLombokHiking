<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'guide') {
    header('Location: ../views/login.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $bookingId = intval($_POST['booking_id']);
    $action = $_POST['action'];

    if ($action === 'confirm') {
        $status = 'confirmed';
    } elseif ($action === 'cancel') {
        $status = 'cancelled';
    } else {
        header('Location: manage-booking.php');
        exit();
    }

    $stmt = $conn->prepare("UPDATE bookings SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $bookingId);
    $stmt->execute();

    // Optional: feedback session
    $_SESSION['flash_message'] = "Status booking berhasil diperbarui.";
    header('Location: manage-booking.php');
    exit();
} else {
    header('Location: manage-booking.php');
    exit();
}
?>

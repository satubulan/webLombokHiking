<?php
session_start();
require_once '../config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"));
    $user_id = $_SESSION['user_id'];
    $trip_id = $data->trip_id;

    // Insert the trip into the user's cart
    $insert_query = $conn->prepare("INSERT INTO bookings (user_id, trip_id, status) VALUES (?, ?, 'pending')");
    $insert_query->bind_param("ii", $user_id, $trip_id);

    if ($insert_query->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false]);
    }
}
?>

<?php
require_once '../config.php';

$trip_id = intval($_GET['trip_id']);

if ($trip_id) {
    $stmt = $conn->prepare("SELECT cm.*, u.name FROM chat_messages cm 
                            LEFT JOIN users u ON cm.sender_id = u.id
                            WHERE cm.trip_id = ? ORDER BY cm.sent_at ASC");
    $stmt->bind_param("i", $trip_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $chats = [];
    while ($row = $result->fetch_assoc()) {
        $chats[] = [
            'sender' => $row['sender_role'] === 'guide' ? 'Guide: ' . $row['name'] : $row['name'],
            'message' => $row['message'],
            'sent_at' => $row['sent_at']
        ];
    }

    header('Content-Type: application/json');
    echo json_encode($chats);
}

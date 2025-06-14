<?php
session_start();
require_once '../config.php';

// Cek login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Hapus trip
    $sql = "DELETE FROM trips WHERE id = $id";
    if ($conn->query($sql)) {
        // Ambil semua ID yang tersisa
        $remaining_ids = $conn->query("SELECT id FROM trips ORDER BY id ASC");
        $ids = $remaining_ids->fetch_all(MYSQLI_ASSOC);
        
        // Reset auto increment
        $conn->query("ALTER TABLE trips AUTO_INCREMENT = 1");
        
        // Update ID secara berurutan
        foreach ($ids as $index => $row) {
            $new_id = $index + 1;
            if ($row['id'] != $new_id) {
                $conn->query("UPDATE trips SET id = $new_id WHERE id = " . $row['id']);
            }
        }
        
        header("Location: trips.php");
        exit();
    } else {
        die("Error deleting trip: " . $conn->error);
    }
} else {
    header("Location: trips.php");
    exit();
}
?> 
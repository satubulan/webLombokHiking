<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Hapus data dari database
    $sql = "DELETE FROM users WHERE id = $id";
    if ($conn->query($sql)) {
        // Ambil semua data yang tersisa
        $result = $conn->query("SELECT id FROM users ORDER BY id ASC");
        $users = $result->fetch_all(MYSQLI_ASSOC);
        
        // Reset auto increment
        $conn->query("ALTER TABLE users AUTO_INCREMENT = 1");
        
        // Update ID secara berurutan
        foreach ($users as $index => $user) {
            $new_id = $index + 1;
            if ($user['id'] != $new_id) {
                $conn->query("UPDATE users SET id = $new_id WHERE id = " . $user['id']);
            }
        }
        
        header("Location: users.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    header("Location: users.php");
    exit();
}
?> 
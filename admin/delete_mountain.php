<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

if (isset($_GET['id'])) {
    $id = intval($_GET['id']);
    
    // Ambil info gambar sebelum menghapus
    $result = $conn->query("SELECT image_url FROM mountains WHERE id = $id");
    if ($result && $result->num_rows > 0) {
        $mountain = $result->fetch_assoc();
        $image_path = "../assets/images/" . $mountain['image_url'];
        
        // Hapus file gambar jika ada
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    // Hapus data dari database
    $sql = "DELETE FROM mountains WHERE id = $id";
    if ($conn->query($sql)) {
        // Ambil semua data yang tersisa
        $result = $conn->query("SELECT id FROM mountains ORDER BY id ASC");
        $mountains = $result->fetch_all(MYSQLI_ASSOC);
        
        // Reset auto increment
        $conn->query("ALTER TABLE mountains AUTO_INCREMENT = 1");
        
        // Update ID secara berurutan
        foreach ($mountains as $index => $mountain) {
            $new_id = $index + 1;
            if ($mountain['id'] != $new_id) {
                $conn->query("UPDATE mountains SET id = $new_id WHERE id = " . $mountain['id']);
            }
        }
        
        header("Location: mountains.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    header("Location: mountains.php");
    exit();
}
?> 
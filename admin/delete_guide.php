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
    $result = $conn->query("SELECT image_url FROM guides WHERE id = $id");
    if ($result && $result->num_rows > 0) {
        $guide = $result->fetch_assoc();
        $image_path = "../assets/images/" . $guide['image_url'];
        
        // Hapus file gambar jika ada
        if (file_exists($image_path)) {
            unlink($image_path);
        }
    }
    
    // Hapus data dari database
    $sql = "DELETE FROM guides WHERE id = $id";
    if ($conn->query($sql)) {
        // Ambil semua data yang tersisa
        $result = $conn->query("SELECT id FROM guides ORDER BY id ASC");
        $guides = $result->fetch_all(MYSQLI_ASSOC);
        
        // Reset auto increment
        $conn->query("ALTER TABLE guides AUTO_INCREMENT = 1");
        
        // Update ID secara berurutan
        foreach ($guides as $index => $guide) {
            $new_id = $index + 1;
            if ($guide['id'] != $new_id) {
                $conn->query("UPDATE guides SET id = $new_id WHERE id = " . $guide['id']);
            }
        }
        
        header("Location: guides.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }
} else {
    header("Location: guides.php");
    exit();
}
?> 
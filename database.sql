<?php
session_start();
require_once '../config.php';

// Cek login admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

// Ambil data gunung dari database
$result = $conn->query("SELECT * FROM mountains");

// Struktur tabel users
$conn->query("
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20) NOT NULL,
        role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
        active TINYINT(1) NOT NULL DEFAULT 1,
        profile_image VARCHAR(255) DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    );
");

// Tambah kolom profile_image ke tabel users
$conn->query("
    ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) DEFAULT NULL AFTER phone;
");
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Data Gunung - Admin Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
<div class="admin-container">
    <aside class="sidebar">
        <div class="logo">Admin Panel</div>
        <nav>
            <a href="index.php">Dashboard</a>
            <a href="users.php">Pengguna</a>
            <a href="guides.php">Guide</a>
            <a href="mountains.php" class="active">Gunung</a>
            <a href="trips.php">Trip</a>
            <a href="bookings.php">Booking</a>
            <a href="feedback.php">Feedback</a>
            <a href="profile.php">Profil</a>
            <a href="../logout.php">Logout</a>
        </nav>
    </aside>

    <main class="main">
        <div class="admin-header">
            <h1>Data Gunung</h1>
            <p>Berisi daftar gunung yang tersedia di sistem</p>
        </div>

        <div style="margin-bottom: 20px;">
            <a href="mountain_add.php" class="btn" style="background-color: #2e8b57; color: white; padding: 10px 15px; border-radius: 6px; text-decoration: none;">+ Tambah Gunung</a>
        </div>

        <div class="user-table">
            <table>
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama Gunung</th>
                        <th>Deskripsi</th>
                        <th>Tinggi (mdpl)</th>
                        <th>Gambar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $no = 1;
                    while ($row = $result->fetch_assoc()) {
                        echo "<tr>";
                        echo "<td>{$no}</td>";
                        echo "<td>{$row['name']}</td>";
                        echo "<td>" . substr(strip_tags($row['description']), 0, 100) . "...</td>";
                        echo "<td>{$row['height']}</td>";
                        echo "<td>";
                        if ($row['image_url']) {
                            echo "<img src='../assets/images/{$row['image_url']}' width='100'>";
                        } else {
                            echo "-";
                        }
                        echo "</td>";
                        echo "<td>
                                <a href='mountain_edit.php?id={$row['id']}' style='color: #2e8b57; margin-right: 10px;'>Edit</a>
                                <a href='mountain_delete.php?id={$row['id']}' style='color: red;' onclick='return confirm(\"Yakin ingin menghapus?\")'>Hapus</a>
                              </td>";
                        echo "</tr>";
                        $no++;
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
</body>
</html>

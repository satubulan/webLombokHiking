<?php 
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

// Ambil data gunung
$result = $conn->query("SELECT * FROM mountains ORDER BY id DESC");
$mountains = $result->fetch_all(MYSQLI_ASSOC);

// Jika form tambah gunung disubmit
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_mountain'])) {
    $name = $conn->real_escape_string($_POST['name']);
    $description = $conn->real_escape_string($_POST['description']);
    $height = intval($_POST['height']);
    $image_url = $conn->real_escape_string($_POST['image_url']);

    $conn->query("INSERT INTO mountains (name, description, height, image_url) VALUES ('$name', '$description', $height, '$image_url')");
    header("Location: mountains.php"); // Refresh agar data tampil terbaru
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Manajemen Gunung - Admin Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/style.css" />
    <link rel="stylesheet" href="../assets/css/mountains.css" />


    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
<div class="admin-layout" style="display:flex;min-height:100vh;">
    <!-- Sidebar kamu bisa sesuaikan/ambil dari file lain -->
    <aside class="admin-sidebar">
        <div class="nav-section-title">Admin Panel</div>
        <ul class="nav-links">
            <li><a href="index.php" class="nav-link"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li><a href="users.php" class="nav-link"><i class="fas fa-users"></i> Pengguna</a></li>
            <li><a href="guides.php" class="nav-link"><i class="fas fa-map-signs"></i> Guide</a></li>
            <li><a href="mountains.php" class="nav-link active"><i class="fas fa-mountain"></i> Gunung</a></li>
            <li><a href="trips.php" class="nav-link"><i class="fas fa-route"></i> Trip</a></li>
            <li><a href="bookings.php" class="nav-link"><i class="fas fa-calendar-alt"></i> Booking</a></li>
            <li><a href="feedback.php" class="nav-link"><i class="fas fa-comment-dots"></i> Feedback</a></li>
            <li><a href="profile.php" class="nav-link"><i class="fas fa-user-cog"></i> Profil</a></li>
            <li><a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>
    

    <main class="admin-main" style="flex:1;padding:30px;overflow-x:auto;">
        <h1 style="margin-bottom:20px;color:#2e8b57;">Manajemen Gunung</h1>

        <!-- Tombol untuk toggle form -->
        <button id="toggleFormBtn" class="btn btn-primary" style="margin-bottom:20px;">Tambah Gunung</button>

        <!-- Form Tambah Gunung (sebelumnya disembunyikan) -->
        <form id="mtnForm" style="display:none;" method="POST" action="mountains.php">
            <input type="hidden" name="add_mountain" value="1" />
            <label>Nama Gunung:</label>
            <input type="text" name="name" required />

            <label>Deskripsi:</label>
            <textarea name="description" rows="3" required></textarea>

            <label>Tinggi (m):</label>
            <input type="number" name="height" required />

            <label>URL Gambar:</label>
            <input type="text" name="image_url" required />

            <button type="submit">Simpan</button>
        </form>

        <!-- Tabel daftar gunung -->
        <div class="admin-table-container">
            <table class="admin-table" cellspacing="0" cellpadding="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama</th>
                        <th>Deskripsi</th>
                        <th>Tinggi (m)</th>
                        <th>Gambar</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($mountains) > 0): ?>
                        <?php foreach ($mountains as $m): ?>
                            <tr>
                                <td><?php echo $m['id']; ?></td>
                                <td><?php echo htmlspecialchars($m['name']); ?></td>
                                <td><?php echo htmlspecialchars($m['description']); ?></td>
                                <td><?php echo $m['height']; ?></td>
                                <td><img src="<?php echo htmlspecialchars($m['image_url']); ?>" alt="Gambar Gunung" style="width:80px; border-radius:4px;"></td>
                                <td>
                                    <a href="edit_mountain.php?id=<?php echo $m['id']; ?>" class="btn btn-edit"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="delete_mountain.php?id=<?php echo $m['id']; ?>" class="btn btn-delete" onclick="return confirm('Yakin ingin hapus gunung ini?')"><i class="fas fa-trash-alt"></i> Hapus</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" class="text-center">Tidak ada gunung terdaftar.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<script>
    // Toggle tampil/simpan form tambah gunung
    document.getElementById('toggleFormBtn').addEventListener('click', function(){
        const form = document.getElementById('mtnForm');
        if (form.style.display === 'none' || form.style.display === '') {
            form.style.display = 'block';
            this.textContent = 'Batal Tambah Gunung';
        } else {
            form.style.display = 'none';
            this.textContent = 'Tambah Gunung';
        }
    });
</script>
</body>
</html>

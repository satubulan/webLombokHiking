<?php
session_start();
require_once '../config.php';

// Cek login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

$adminId = $_SESSION['user_id'];

// Ambil data admin
$query = $conn->prepare("SELECT name, email, phone, profile_picture FROM users WHERE id = ?");
$query->bind_param("s", $adminId);
$query->execute();
$result = $query->get_result();
$user = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Profil - Admin Lombok Hiking</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        /* ===== Reset dan Dasar ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background-color: #f4f6f9;
            color: #333;
        }

        /* ===== Layout Utama ===== */
        .admin-layout {
            display: flex;
            min-height: 100vh;
        }

        .admin-sidebar {
            width: 220px;
            background-color: #2e8b57;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 20px;
        }

        .admin-sidebar .nav-section-title {
            font-size: 20px;
            font-weight: bold;
            margin-bottom: 30px;
            user-select: none;
        }

        .admin-sidebar .nav-links {
            list-style: none;
            flex-grow: 1;
        }

        .admin-sidebar .nav-links li {
            margin: 10px 0;
        }

        .admin-sidebar .nav-link {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 10px;
            border-radius: 6px;
            transition: background 0.3s;
            font-weight: 600;
        }

        .admin-sidebar .nav-link:hover,
        .admin-sidebar .nav-link.active {
            background-color: #246b46;
        }

        /* ===== Header ===== */
        .admin-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }

        .admin-header h1 {
            font-size: 24px;
            margin-bottom: 8px;
        }

        /* ===== Main Area ===== */
        .admin-main {
            flex: 1;
            padding: 30px;
            overflow-x: auto;
        }

        /* ===== Profile Container ===== */
        .profile-container {
            max-width: 600px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
            padding: 30px;
            margin: 0 auto;
            text-align: center; /* Center align text */
        }

        .profile-card {
            text-align: center;
        }

        /* ===== Profile Avatar ===== */
        .profile-avatar {
            margin-bottom: 20px;
            position: relative;
            display: flex; /* Use flexbox for centering */
            justify-content: center; /* Center horizontally */
            align-items: center; /* Center vertically */
        }

        .profile-avatar img,
        .profile-avatar .empty-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #2e8b57;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .profile-avatar .empty-avatar {
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: #999;
            background: #f0f0f0;
        }

        /* ===== Profile Info ===== */
        .profile-info h3 {
            font-size: 24px;
            margin-bottom: 20px;
            color: #2e8b57;
        }

        .info-item {
            margin-bottom: 15px;
            text-align: left;
            padding: 10px;
            border-radius: 8px;
            background: rgba(46, 139, 87, 0.1);
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .info-item label {
            font-weight: bold;
            color: #2e8b57;
        }

        .info-item p {
            margin: 0;
            color: #333;
        }

        /* ===== Responsive ===== */
        @media (max-width: 768px) {
            .admin-header {
                flex-direction: column;
                gap: 15px;
            }

            .profile-container {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="nav-section-title">Admin Panel</div>
        <ul class="nav-links">
            <li><a href="index.php" class="nav-link"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li><a href="users.php" class="nav-link"><i class="fas fa-users"></i> Pengguna</a></li>
            <li><a href="guides.php" class="nav-link"><i class="fas fa-map-signs"></i> Guide</a></li>
            <li><a href="mountains.php" class="nav-link"><i class="fas fa-mountain"></i> Gunung</a></li>
            <li><a href="trips.php" class="nav-link"><i class="fas fa-route"></i> Trip</a></li>
            <li><a href="feedback.php" class="nav-link"><i class="fas fa-comments"></i> Feedback</a></li>
            <li><a href="lihat_pembayaran.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Lihat Pembayaran</a></li>
            <li><a href="notifikasi.php" class="nav-link"><i class="fas fa-bell"></i> Notifikasi</a></li>
            <li><a href="profile.php" class="nav-link active"><i class="fas fa-user-cog"></i> Profil</a></li>
            <li><a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="admin-main">
        <div class="profile-container">
            <div class="profile-card">
                <div class="profile-avatar">
                    <?php if (!empty($user['profile_picture']) && file_exists('../assets/images/profiles/' . $user['profile_picture'])): ?>
                        <img src="../assets/images/profiles/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Foto Profil" />
                    <?php else: ?>
                        <div class="empty-avatar" aria-label="Foto profil kosong">
                            <i class="fas fa-user-circle"></i>
                        </div>
                    <?php endif; ?>
                </div>

                <div class="profile-info">
                    <h3>Informasi Profile</h3>
                    <div class="info-item">
                        <label>Nama:</label>
                        <p><?php echo htmlspecialchars($user['name']); ?></p>
                    </div>
                    <div class="info-item">
                        <label>Email:</label>
                        <p><?php echo htmlspecialchars($user['email']); ?></p>
                    </div>
                    <div class="info-item">
                        <label>Nomor Telepon:</label>
                        <p><?php echo htmlspecialchars($user['phone']); ?></p>
                    </div>
                    <button class="btn-edit-profile" onclick="openEditProfileModal()" style="margin-top:18px;padding:10px 22px;background:linear-gradient(135deg,#2e8b57,#3cb371);color:#fff;border:none;border-radius:8px;font-weight:700;cursor:pointer;">Edit Profile</button>
                    <button class="btn-edit-password" onclick="openEditPasswordModal()" style="margin-top:10px;padding:10px 22px;background:#fff;color:#2e8b57;border:1.5px solid #2e8b57;border-radius:8px;font-weight:700;cursor:pointer;">Ubah Password</button>
                </div>
            </div>
        </div>
        <!-- Modal Edit Profile -->
        <div id="editProfileModal" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(44,62,80,0.18);backdrop-filter:blur(2px);align-items:center;justify-content:center;">
            <div style="background:#fff;max-width:400px;width:95vw;border-radius:16px;box-shadow:0 8px 32px rgba(44,62,80,0.18);padding:32px 28px 22px 28px;position:relative;display:flex;flex-direction:column;gap:0;">
                <button type="button" onclick="closeEditProfileModal()" style="position:absolute;top:18px;right:18px;background:none;border:none;font-size:28px;color:#2e8b57;cursor:pointer;"><i class='fas fa-times'></i></button>
                <h2 style="text-align:center;margin-bottom:18px;color:#2e8b57;font-size:1.3rem;font-weight:700;">Edit Profile</h2>
                <form id="editProfileForm" enctype="multipart/form-data" autocomplete="off">
                    <div id="editProfileError" class="alert" style="display:none;background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;font-size:14px;margin-bottom:12px;padding:10px 14px;border-radius:7px;"></div>
                    <div class="form-group" style="margin-bottom:14px;text-align:left;">
                        <label for="edit_name" style="font-weight:600;color:#2e8b57;">Nama:</label>
                        <input type="text" id="edit_name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required style="width:100%;padding:10px;border:1px solid #e9ecef;border-radius:7px;font-size:15px;">
                    </div>
                    <div class="form-group" style="margin-bottom:14px;text-align:left;">
                        <label for="edit_email" style="font-weight:600;color:#2e8b57;">Email:</label>
                        <input type="email" id="edit_email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required style="width:100%;padding:10px;border:1px solid #e9ecef;border-radius:7px;font-size:15px;">
                    </div>
                    <div class="form-group" style="margin-bottom:14px;text-align:left;">
                        <label for="edit_phone" style="font-weight:600;color:#2e8b57;">Nomor Telepon:</label>
                        <input type="text" id="edit_phone" name="phone" value="<?php echo htmlspecialchars($user['phone']); ?>" required style="width:100%;padding:10px;border:1px solid #e9ecef;border-radius:7px;font-size:15px;">
                    </div>
                    <div class="form-group" style="margin-bottom:18px;text-align:left;">
                        <label for="edit_profile_picture" style="font-weight:600;color:#2e8b57;">Foto Profile:</label>
                        <input type="file" id="edit_profile_picture" name="profile_picture" accept="image/*" style="width:100%;">
                        <small style="color:#888;">Biarkan kosong jika tidak ingin mengubah foto</small>
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;background:linear-gradient(135deg,#2e8b57,#3cb371);color:#fff;border:none;border-radius:8px;padding:12px 0;font-size:16px;font-weight:700;cursor:pointer;">Simpan Perubahan</button>
                </form>
            </div>
        </div>
        <!-- Modal Ubah Password -->
        <div id="editPasswordModal" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(44,62,80,0.18);backdrop-filter:blur(2px);align-items:center;justify-content:center;">
            <div style="background:#fff;max-width:400px;width:95vw;border-radius:16px;box-shadow:0 8px 32px rgba(44,62,80,0.18);padding:32px 28px 22px 28px;position:relative;display:flex;flex-direction:column;gap:0;">
                <button type="button" onclick="closeEditPasswordModal()" style="position:absolute;top:18px;right:18px;background:none;border:none;font-size:28px;color:#2e8b57;cursor:pointer;"><i class='fas fa-times'></i></button>
                <h2 style="text-align:center;margin-bottom:18px;color:#2e8b57;font-size:1.3rem;font-weight:700;">Ubah Password</h2>
                <form id="editPasswordForm" autocomplete="off">
                    <div id="editPasswordError" class="alert" style="display:none;background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;font-size:14px;margin-bottom:12px;padding:10px 14px;border-radius:7px;"></div>
                    <div class="form-group" style="margin-bottom:14px;text-align:left;">
                        <label for="old_password" style="font-weight:600;color:#2e8b57;">Password Lama:</label>
                        <input type="password" id="old_password" name="old_password" required style="width:100%;padding:10px;border:1px solid #e9ecef;border-radius:7px;font-size:15px;">
                    </div>
                    <div class="form-group" style="margin-bottom:14px;text-align:left;">
                        <label for="new_password" style="font-weight:600;color:#2e8b57;">Password Baru:</label>
                        <input type="password" id="new_password" name="new_password" required minlength="6" style="width:100%;padding:10px;border:1px solid #e9ecef;border-radius:7px;font-size:15px;">
                    </div>
                    <div class="form-group" style="margin-bottom:18px;text-align:left;">
                        <label for="confirm_password" style="font-weight:600;color:#2e8b57;">Konfirmasi Password Baru:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required minlength="6" style="width:100%;padding:10px;border:1px solid #e9ecef;border-radius:7px;font-size:15px;">
                    </div>
                    <button type="submit" class="btn btn-primary" style="width:100%;background:linear-gradient(135deg,#2e8b57,#3cb371);color:#fff;border:none;border-radius:8px;padding:12px 0;font-size:16px;font-weight:700;cursor:pointer;">Ubah Password</button>
                </form>
            </div>
        </div>
        <script>
        function openEditProfileModal() {
            document.getElementById('editProfileModal').style.display = 'flex';
        }
        function closeEditProfileModal() {
            document.getElementById('editProfileModal').style.display = 'none';
        }
        function openEditPasswordModal() {
            document.getElementById('editPasswordModal').style.display = 'flex';
        }
        function closeEditPasswordModal() {
            document.getElementById('editPasswordModal').style.display = 'none';
        }
        // Handle Edit Profile Submit
        document.getElementById('editProfileForm').onsubmit = function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            fetch('edit_user.php', {
                method: 'POST',
                body: formData
            }).then(async res => {
                if(res.ok) {
                    const data = await res.json();
                    // Update tampilan profile tanpa reload
                    document.querySelector('.profile-info .info-item:nth-child(2) p').textContent = data.user.name;
                    document.querySelector('.profile-info .info-item:nth-child(3) p').textContent = data.user.email;
                    document.querySelector('.profile-info .info-item:nth-child(4) p').textContent = data.user.phone;
                    if(data.user.profile_picture) {
                        document.querySelector('.profile-avatar').innerHTML = `<img src="../assets/images/profiles/${data.user.profile_picture}" alt="Foto Profil" />`;
                    }
                    closeEditProfileModal();
                } else {
                    const text = await res.text();
                    document.getElementById('editProfileError').textContent = text || 'Gagal update profile!';
                    document.getElementById('editProfileError').style.display = 'block';
                }
            });
        };
        // Handle Edit Password Submit
        document.getElementById('editPasswordForm').onsubmit = function(e) {
            e.preventDefault();
            var formData = new FormData(this);
            fetch('edit_password.php', {
                method: 'POST',
                body: formData
            }).then(async res => {
                if(res.ok) {
                    alert('Password berhasil diubah!');
                    closeEditPasswordModal();
                } else {
                    const text = await res.text();
                    document.getElementById('editPasswordError').textContent = text || 'Gagal ubah password!';
                    document.getElementById('editPasswordError').style.display = 'block';
                }
            });
        };
        </script>
    </main>
</div>
</body>
</html>

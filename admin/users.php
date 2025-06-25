<?php 
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

$result = $conn->query("SELECT * FROM users ORDER BY id ASC");
$users = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Manajemen Pengguna - Admin Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/guides.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

</head>
<body>
<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="nav-section-title">Admin Panel</div>
        <ul class="nav-links">
            <li><a href="index.php" class="nav-link"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li><a href="users.php" class="nav-link active"><i class="fas fa-users"></i> Pengguna</a></li>
            <li><a href="guides.php" class="nav-link"><i class="fas fa-map-signs"></i> Guide</a></li>
            <li><a href="mountains.php" class="nav-link"><i class="fas fa-mountain"></i> Gunung</a></li>
            <li><a href="trips.php" class="nav-link"><i class="fas fa-route"></i> Trip</a></li>
            <li><a href="feedback.php" class="nav-link"><i class="fas fa-comments"></i> Feedback</a></li>
            <li><a href="lihat_pembayaran.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Lihat Pembayaran</a></li>
            <li><a href="notifikasi.php" class="nav-link"><i class="fas fa-bell"></i> Notifikasi</a></li>
            <li><a href="profile.php" class="nav-link"><i class="fas fa-user-cog"></i> Profil</a></li>
            <li><a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <!-- Main Content -->
    <main class="admin-main">
        <div class="admin-header">
            <h1>Daftar Pengguna</h1>
            <div class="header-actions">
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Cari pengguna...">
                </div>
            </div>
        </div>

        <div class="admin-table-container">
            <table class="admin-table" cellspacing="0" cellpadding="0">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($users) > 0): ?>
                        <?php foreach ($users as $index => $user): ?>
                            <tr data-id="<?php echo $user['id']; ?>">
                                <td class="user-id">#<?php echo $index + 1; ?></td>
                                <td>
                                    <?php 
                                    $imgPath = '../assets/images/profiles/' . $user['profile_picture'];
                                    if (!empty($user['profile_picture']) && file_exists($imgPath)) : ?>
                                        <img src="<?= $imgPath ?>" alt="Foto <?= htmlspecialchars($user['name']) ?>" class="profile-thumbnail">
                                    <?php else: ?>
                                        <div class="profile-thumbnail no-image">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="user-name">
                                    <div class="user-info">
                                        <span class="name"><?php echo htmlspecialchars($user['name']); ?></span>
                                    </div>
                                </td>
                                <td class="user-email">
                                    <i class="fas fa-envelope"></i>
                                    <?php echo htmlspecialchars($user['email']); ?>
                                </td>
                                <td class="user-phone">
                                    <i class="fas fa-phone"></i>
                                    <?php echo htmlspecialchars($user['phone']); ?>
                                </td>
                                <td>
                                    <span class="role-badge <?php echo strtolower($user['role']); ?>">
                                        <?php echo ucfirst(htmlspecialchars($user['role'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($user['active']): ?>
                                        <span class="status-badge active">
                                            <i class="fas fa-circle"></i>
                                            Aktif
                                        </span>
                                    <?php else: ?>
                                        <span class="status-badge inactive">
                                            <i class="fas fa-circle"></i>
                                            Nonaktif
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="action-buttons">
                                    <a href="#" class="btn btn-edit" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_user.php?id=<?php echo $user['id']; ?>" class="btn btn-delete" onclick="return confirm('Yakin ingin hapus user ini?')" title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-users"></i>
                                    <p>Tidak ada pengguna terdaftar.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Modal Edit User -->
<style>
#editUserModal .modal-card {
  background: #fff;
  max-width: 420px;
  width: 90vw;
  border-radius: 18px;
  box-shadow: 0 8px 32px rgba(44,62,80,0.18);
  padding: 36px 32px 28px 32px;
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 0;
}
#editUserModal h2 {
  text-align: center;
  margin-bottom: 18px;
  color: #2e8b57;
  font-size: 1.3rem;
  font-weight: 700;
}
#editUserModal .form-group {
  margin-bottom: 18px;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}
#editUserModal label {
  font-weight: 600;
  margin-bottom: 7px;
  color: #2c3e50;
}
#editUserModal input[type="text"],
#editUserModal input[type="email"],
#editUserModal input[type="tel"],
#editUserModal select {
  width: 100%;
  padding: 12px;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  font-size: 15px;
  margin-bottom: 0;
  transition: border 0.2s;
}
#editUserModal input[type="text"]:focus,
#editUserModal input[type="email"]:focus,
#editUserModal input[type="tel"]:focus,
#editUserModal select:focus {
  border-color: #2e8b57;
  outline: none;
}
#editUserModal input[type="checkbox"] {
  margin-right: 8px;
}
#editUserModal .btn-primary {
  width: 100%;
  background: linear-gradient(135deg, #2e8b57, #3cb371);
  color: #fff;
  border: none;
  border-radius: 8px;
  padding: 13px 0;
  font-size: 16px;
  font-weight: 700;
  cursor: pointer;
  box-shadow: 0 2px 8px rgba(46,139,87,0.10);
  transition: background 0.2s, box-shadow 0.2s;
}
#editUserModal .btn-primary:hover {
  background: linear-gradient(135deg, #249150, #2e8b57);
  box-shadow: 0 4px 16px rgba(46,139,87,0.18);
}
</style>
<div id="editUserModal" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(44,62,80,0.25);backdrop-filter:blur(2px);align-items:center;justify-content:center;">
  <div class="modal-card">
    <button type="button" onclick="closeEditModal()" style="position:absolute;top:18px;right:18px;background:none;border:none;font-size:28px;color:#2e8b57;cursor:pointer;"><i class='fas fa-times'></i></button>
    <h2>Edit Pengguna</h2>
    <form id="editUserForm">
      <input type="hidden" name="id" id="edit_id">
      <div class="form-group">
        <label for="edit_name">Nama:</label>
        <input type="text" id="edit_name" name="name" required>
      </div>
      <div class="form-group">
        <label for="edit_email">Email:</label>
        <input type="email" id="edit_email" name="email" required>
      </div>
      <div class="form-group">
        <label for="edit_phone">Telepon:</label>
        <input type="tel" id="edit_phone" name="phone" required>
      </div>
      <div class="form-group">
        <label for="edit_role">Role:</label>
        <select id="edit_role" name="role" required>
          <option value="user">User</option>
          <option value="guide">Guide</option>
          <option value="admin">Admin</option>
        </select>
      </div>
      <div class="form-group" style="margin-bottom:10px;">
        <label style="font-weight:400;"><input type="checkbox" id="edit_active" name="active"> Status Aktif</label>
      </div>
      <div class="form-group" style="margin-top:10px;">
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>
<script>
function openEditModal(user) {
  document.getElementById('edit_id').value = user.id;
  document.getElementById('edit_name').value = user.name;
  document.getElementById('edit_email').value = user.email;
  document.getElementById('edit_phone').value = user.phone;
  document.getElementById('edit_role').value = user.role;
  document.getElementById('edit_active').checked = user.active == 1;
  document.getElementById('editUserModal').style.display = 'flex';
}
function closeEditModal() {
  document.getElementById('editUserModal').style.display = 'none';
}
// Tangkap klik tombol edit
const editBtns = document.querySelectorAll('.btn-edit');
editBtns.forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    const tr = this.closest('tr');
    const user = {
      id: tr.getAttribute('data-id'),
      name: tr.querySelector('.user-name .name').textContent,
      email: tr.querySelector('.user-email').textContent.replace(/\s*\S+@\S+\s*/g, m => m.trim()),
      phone: tr.querySelector('.user-phone').textContent.replace(/\D/g, ''),
      role: tr.querySelector('.role-badge').textContent.trim().toLowerCase(),
      active: tr.querySelector('.status-badge.active') ? 1 : 0
    };
    openEditModal(user);
  });
});
// Submit form edit user pakai AJAX
const editUserForm = document.getElementById('editUserForm');
editUserForm.onsubmit = function(e) {
  e.preventDefault();
  const formData = new FormData(editUserForm);
  fetch('edit_user.php', {
    method: 'POST',
    body: formData
  }).then(res => {
    if(res.ok) window.location.reload();
    else alert('Gagal update user!');
  });
};
</script>

<script>
document.getElementById('searchInput').addEventListener('keyup', function() {
    let searchText = this.value.toLowerCase();
    let table = document.querySelector('.admin-table');
    let rows = table.getElementsByTagName('tr');

    for (let i = 1; i < rows.length; i++) {
        let row = rows[i];
        let cells = row.getElementsByTagName('td');
        let found = false;

        for (let j = 0; j < cells.length; j++) {
            let cell = cells[j];
            if (cell.textContent.toLowerCase().indexOf(searchText) > -1) {
                found = true;
                break;
            }
        }

        row.style.display = found ? '' : 'none';
    }
});
</script>
</body>
</html>

<?php
session_start();
require_once '../config.php';

// Cek login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}


// Ambil semua user dengan role guide
$result = $conn->query("SELECT u.*, g.specialization, g.experience, g.languages, g.bio, g.status AS guide_status, g.id AS guide_id FROM users u LEFT JOIN guide g ON u.id = g.user_id WHERE u.role = 'guide' ORDER BY u.id ASC");
$guides = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Manajemen Guide - Admin Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/guide.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
</head>
<body>
<div class="admin-layout">
    <!-- Sidebar -->
    <aside class="admin-sidebar">
        <div class="nav-section-title">Admin Panel</div>
        <ul class="nav-links">
            <li><a href="index.php" class="nav-link"><i class="fas fa-chart-line"></i> Dashboard</a></li>
            <li><a href="users.php" class="nav-link"><i class="fas fa-users"></i> Pengguna</a></li>
            <li><a href="guides.php" class="nav-link active"><i class="fas fa-map-signs"></i> Guide</a></li>
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
            <h1>Daftar Guide</h1>
            <div class="header-actions">
                <a href="guide_create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Guide
                </a>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Cari guide...">
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
                    <?php if (count($guides) > 0): ?>
                        <?php foreach ($guides as $index => $guide): ?>
                            <tr data-guide-id="<?php echo $guide['guide_id']; ?>" data-user-id="<?php echo $guide['id']; ?>" data-specialization="<?php echo htmlspecialchars($guide['specialization']); ?>" data-experience="<?php echo htmlspecialchars($guide['experience']); ?>" data-languages="<?php echo htmlspecialchars($guide['languages']); ?>" data-bio="<?php echo htmlspecialchars($guide['bio']); ?>" data-status="<?php echo htmlspecialchars($guide['guide_status']); ?>">
                                <td class="user-id">#<?php echo $index + 1; ?></td>
                                <td>
                                    <?php if ($guide['profile_picture']): ?>
                                        <img src="../assets/images/profiles/<?php echo htmlspecialchars($guide['profile_picture']); ?>" 
                                             alt="Foto <?php echo htmlspecialchars($guide['name']); ?>"
                                             class="profile-thumbnail">
                                    <?php else: ?>
                                        <div class="profile-thumbnail no-image">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="user-name">
                                    <div class="user-info">
                                        <span class="name"><?php echo htmlspecialchars($guide['name']); ?></span>
                                    </div>
                                </td>
                                <td class="user-email">
                                    <i class="fas fa-envelope"></i>
                                    <?php echo htmlspecialchars($guide['email']); ?>
                                </td>
                                <td class="user-phone">
                                    <i class="fas fa-phone"></i>
                                    <?php echo htmlspecialchars($guide['phone']); ?>
                                </td>
                                <td>
                                    <span class="role-badge guide">Guide</span>
                                </td>
                                <td>
                                    <?php if ($guide['guide_status'] === 'approved'): ?>
                                        <span class="status-badge active">
                                            <i class="fas fa-circle"></i>
                                            Aktif
                                        </span>
                                    <?php elseif ($guide['guide_status'] === 'pending'): ?>
                                        <span class="status-badge inactive">
                                            <i class="fas fa-circle"></i>
                                            Pending
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
                                    <a href="delete_guide.php?user_id=<?php echo $guide['id']; ?>" class="btn btn-delete" onclick="return confirm('Yakin ingin hapus guide ini?')" title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-map-signs"></i>
                                    <p>Tidak ada guide terdaftar.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<style>
.rating {
    display: flex;
    align-items: center;
    gap: 8px;
}
.rating i {
    color: #ffc107;
    font-size: 16px;
}
.rating-value {
    font-weight: 500;
    color: #666;
}
#editGuideModal .modal-card {
  background: #fff;
  max-width: 480px;
  width: 95vw;
  border-radius: 18px;
  box-shadow: 0 8px 32px rgba(44,62,80,0.18);
  padding: 36px 32px 28px 32px;
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 0;
  max-height: 90vh;
  overflow-y: auto;
  scrollbar-width: none; /* Firefox */
}
#editGuideModal .modal-card::-webkit-scrollbar {
  display: none; /* Chrome, Safari */
}
#editGuideModal h2 {
  text-align: center;
  margin-bottom: 18px;
  color: #2e8b57;
  font-size: 1.3rem;
  font-weight: 700;
}
#editGuideModal .form-group {
  margin-bottom: 18px;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}
#editGuideModal label {
  font-weight: 600;
  margin-bottom: 7px;
  color: #2c3e50;
}
#editGuideModal input[type="text"],
#editGuideModal input[type="email"],
#editGuideModal input[type="tel"],
#editGuideModal select,
#editGuideModal textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  font-size: 15px;
  margin-bottom: 0;
  transition: border 0.2s;
}
#editGuideModal input[type="text"]:focus,
#editGuideModal input[type="email"]:focus,
#editGuideModal input[type="tel"]:focus,
#editGuideModal select:focus,
#editGuideModal textarea:focus {
  border-color: #2e8b57;
  outline: none;
}
#editGuideModal .btn-primary {
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
#editGuideModal .btn-primary:hover {
  background: linear-gradient(135deg, #249150, #2e8b57);
  box-shadow: 0 4px 16px rgba(46,139,87,0.18);
}
#addGuideModal .modal-card {
  background: #fff;
  max-width: 480px;
  width: 95vw;
  border-radius: 18px;
  box-shadow: 0 8px 32px rgba(44,62,80,0.18);
  padding: 36px 32px 28px 32px;
  position: relative;
  display: flex;
  flex-direction: column;
  gap: 0;
  max-height: 90vh;
  overflow-y: auto;
  scrollbar-width: none;
}
#addGuideModal .modal-card::-webkit-scrollbar { display: none; }
#addGuideModal h2 {
  text-align: center;
  margin-bottom: 18px;
  color: #2e8b57;
  font-size: 1.3rem;
  font-weight: 700;
}
#addGuideModal .form-group {
  margin-bottom: 18px;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}
#addGuideModal label {
  font-weight: 600;
  margin-bottom: 7px;
  color: #2c3e50;
}
#addGuideModal input[type="text"],
#addGuideModal input[type="email"],
#addGuideModal input[type="password"],
#addGuideModal textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  font-size: 15px;
  margin-bottom: 0;
  transition: border 0.2s;
}
#addGuideModal input[type="text"]:focus,
#addGuideModal input[type="email"]:focus,
#addGuideModal input[type="password"]:focus,
#addGuideModal textarea:focus {
  border-color: #2e8b57;
  outline: none;
}
#addGuideModal .btn-primary {
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
#addGuideModal .btn-primary:hover {
  background: linear-gradient(135deg, #249150, #2e8b57);
  box-shadow: 0 4px 16px rgba(46,139,87,0.18);
}
#addGuideModal .alert {
  width: 100%;
  margin-bottom: 12px;
  padding: 10px 14px;
  border-radius: 7px;
  background: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
  font-size: 14px;
}
</style>
<div id="editGuideModal" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(44,62,80,0.25);backdrop-filter:blur(2px);align-items:center;justify-content:center;">
  <div class="modal-card">
    <button type="button" onclick="closeEditGuideModal()" style="position:absolute;top:18px;right:18px;background:none;border:none;font-size:28px;color:#2e8b57;cursor:pointer;"><i class='fas fa-times'></i></button>
    <h2>Edit Guide</h2>
    <form id="editGuideForm">
      <input type="hidden" name="guide_id" id="edit_guide_id">
      <input type="hidden" name="user_id" id="edit_user_id">
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
        <label for="edit_status">Status:</label>
        <select id="edit_status" name="status" required>
          <option value="1">Aktif</option>
          <option value="0">Non Aktif</option>
        </select>
      </div>
      <div class="form-group">
        <label for="edit_specialization">Spesialisasi:</label>
        <input type="text" id="edit_specialization" name="specialization" required>
      </div>
      <div class="form-group">
        <label for="edit_experience">Pengalaman:</label>
        <textarea id="edit_experience" name="experience" required></textarea>
      </div>
      <div class="form-group">
        <label for="edit_languages">Bahasa:</label>
        <input type="text" id="edit_languages" name="languages" required>
      </div>
      <div class="form-group">
        <label for="edit_bio">Bio:</label>
        <textarea id="edit_bio" name="bio" required></textarea>
      </div>
      <div class="form-group" style="margin-top:10px;">
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>
<div id="addGuideModal" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(44,62,80,0.25);backdrop-filter:blur(2px);align-items:center;justify-content:center;">
  <div class="modal-card">
    <button type="button" onclick="closeAddGuideModal()" style="position:absolute;top:18px;right:18px;background:none;border:none;font-size:28px;color:#2e8b57;cursor:pointer;"><i class='fas fa-times'></i></button>
    <h2>Tambah Guide Baru</h2>
    <form id="addGuideForm" autocomplete="off">
      <div id="addGuideError" class="alert" style="display:none;"></div>
      <div class="form-group">
        <label for="add_name">Nama Lengkap Guide:</label>
        <input type="text" id="add_name" name="name" placeholder="Masukkan nama lengkap guide" required>
      </div>
      <div class="form-group">
        <label for="add_email">Email Guide:</label>
        <input type="email" id="add_email" name="email" placeholder="Masukkan alamat email guide" required>
      </div>
      <div class="form-group">
        <label for="add_password">Password Guide:</label>
        <input type="password" id="add_password" name="password" placeholder="Minimal 6 karakter" required minlength="6">
      </div>
      <div class="form-group">
        <label for="add_phone">Nomor Telepon:</label>
        <input type="text" id="add_phone" name="phone" placeholder="Masukkan nomor telepon guide" required>
      </div>
      <div class="form-group">
        <label for="add_specialization">Spesialisasi:</label>
        <input type="text" id="add_specialization" name="specialization" placeholder="Contoh: Gunung Rinjani, Trekking, dll" required>
      </div>
      <div class="form-group">
        <label for="add_experience">Pengalaman:</label>
        <textarea id="add_experience" name="experience" placeholder="Ceritakan pengalaman guide" required></textarea>
      </div>
      <div class="form-group">
        <label for="add_languages">Bahasa:</label>
        <input type="text" id="add_languages" name="languages" placeholder="Contoh: Indonesia, Inggris" required>
      </div>
      <div class="form-group">
        <label for="add_bio">Bio:</label>
        <textarea id="add_bio" name="bio" placeholder="Deskripsi singkat tentang guide" required></textarea>
      </div>
      <div class="form-group" style="margin-top:10px;">
        <button type="submit" class="btn btn-primary">Tambah Guide</button>
      </div>
    </form>
  </div>
</div>
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
function openEditGuideModal(guide) {
  document.getElementById('edit_guide_id').value = guide.guide_id;
  document.getElementById('edit_user_id').value = guide.user_id;
  document.getElementById('edit_name').value = guide.name;
  document.getElementById('edit_email').value = guide.email;
  document.getElementById('edit_phone').value = guide.phone;
  document.getElementById('edit_status').value = (guide.status === 'approved' || guide.status === '1') ? '1' : '0';
  document.getElementById('edit_specialization').value = guide.specialization;
  document.getElementById('edit_experience').value = guide.experience;
  document.getElementById('edit_languages').value = guide.languages;
  document.getElementById('edit_bio').value = guide.bio;
  document.getElementById('editGuideModal').style.display = 'flex';
}
function closeEditGuideModal() {
  document.getElementById('editGuideModal').style.display = 'none';
}
const editBtns = document.querySelectorAll('.btn-edit');
editBtns.forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    const tr = this.closest('tr');
    const guide = {
      guide_id: tr.getAttribute('data-guide-id'),
      user_id: tr.getAttribute('data-user-id'),
      name: tr.querySelector('.user-name .name').textContent,
      email: tr.querySelector('.user-email').textContent.replace(/\s*\S+@\S+\s*/g, m => m.trim()),
      phone: tr.querySelector('.user-phone').textContent.replace(/\D/g, ''),
      status: tr.getAttribute('data-status'),
      specialization: tr.getAttribute('data-specialization'),
      experience: tr.getAttribute('data-experience'),
      languages: tr.getAttribute('data-languages'),
      bio: tr.getAttribute('data-bio')
    };
    openEditGuideModal(guide);
  });
});
// Submit form edit guide pakai AJAX
const editGuideForm = document.getElementById('editGuideForm');
editGuideForm.onsubmit = function(e) {
  e.preventDefault();
  const formData = new FormData(editGuideForm);
  fetch('edit_guide.php', {
    method: 'POST',
    body: formData
  }).then(res => {
    if(res.ok) window.location.reload();
    else alert('Gagal update guide!');
  });
};
function openAddGuideModal() {
  document.getElementById('addGuideForm').reset();
  document.getElementById('addGuideError').style.display = 'none';
  document.getElementById('addGuideModal').style.display = 'flex';
}
function closeAddGuideModal() {
  document.getElementById('addGuideModal').style.display = 'none';
}
document.querySelector('.header-actions .btn.btn-primary').addEventListener('click', function(e) {
  e.preventDefault();
  openAddGuideModal();
});
const addGuideForm = document.getElementById('addGuideForm');
addGuideForm.onsubmit = function(e) {
  e.preventDefault();
  const formData = new FormData(addGuideForm);
  fetch('guide_create.php', {
    method: 'POST',
    body: formData
  }).then(async res => {
    if(res.redirected) {
      window.location.href = res.url;
      return;
    }
    if(res.ok) {
      window.location.reload();
    } else {
      const text = await res.text();
      document.getElementById('addGuideError').textContent = text || 'Gagal menambah guide!';
      document.getElementById('addGuideError').style.display = 'block';
    }
  });
};
</script>
</body>
</html>

<?php 
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

// Ambil data gunung dengan urutan ascending
$result = $conn->query("SELECT * FROM mountains ORDER BY id ASC");
$mountains = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Manajemen Gunung - Admin Lombok Hiking</title>
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
            <li><a href="guides.php" class="nav-link"><i class="fas fa-map-signs"></i> Guide</a></li>
            <li><a href="mountains.php" class="nav-link active"><i class="fas fa-mountain"></i> Gunung</a></li>
            <li><a href="trips.php" class="nav-link"><i class="fas fa-route"></i> Trip</a></li>
            <li><a href="feedback.php" class="nav-link"><i class="fas fa-comments"></i> Feedback</a></li>
            <li><a href="lihat_pembayaran.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Lihat Pembayaran</a></li>
            <li><a href="notifikasi.php" class="nav-link"><i class="fas fa-bell"></i> Notifikasi</a></li>
            <li><a href="profile.php" class="nav-link"><i class="fas fa-user-cog"></i> Profil</a></li>
            <li><a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>
    
    <main class="admin-main">
        <div class="admin-header">
            <h1>Daftar Gunung</h1>
            <div class="header-actions">
                <a href="mountain_create.php" class="btn btn-primary">
                    <i class="fas fa-plus"></i> Tambah Gunung
                </a>
                <div class="search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" id="searchInput" placeholder="Cari gunung...">
                </div>
            </div>
        </div>

        <!-- Tabel daftar gunung -->
        <div class="admin-table-container">
            <table class="admin-table" cellspacing="0" cellpadding="0">
                <thead>   
                    <tr>
                        <th>ID</th>
                        <th>Foto</th>
                        <th>Nama</th>
                        <th>Tinggi (m)</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($mountains) > 0): ?>
                        <?php foreach ($mountains as $index => $mountain): ?>
                            <tr>
                                <td class="user-id">#<?php echo $index + 1; ?></td>
                                <td>
                                    <?php if (!empty($mountain['image'])): ?>
                                        <img src="../assets/images/mountains/<?php echo htmlspecialchars($mountain['image']); ?>" 
                                             alt="Foto <?php echo htmlspecialchars($mountain['name']); ?>"
                                             class="profile-thumbnail">
                                    <?php else: ?>
                                        <div class="profile-thumbnail no-image">
                                            <i class="fas fa-mountain"></i>
                                        </div>
                                    <?php endif; ?>
                                </td>
                                <td class="user-name">
                                    <div class="user-info">
                                        <span class="name"><?php echo htmlspecialchars($mountain['name']); ?></span>
                                    </div>
                                </td>
                                <td><?php echo number_format($mountain['height']); ?></td>
                                <td class="action-buttons">
                                    <a href="edit_mountain.php?id=<?php echo $mountain['id']; ?>" class="btn btn-edit" title="Edit" data-description="<?php echo htmlspecialchars($mountain['description'], ENT_QUOTES); ?>">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <a href="delete_mountain.php?id=<?php echo $mountain['id']; ?>" class="btn btn-delete" onclick="return confirm('Yakin ingin hapus gunung ini?')" title="Hapus">
                                        <i class="fas fa-trash-alt"></i>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">
                                <div class="empty-state">
                                    <i class="fas fa-mountain"></i>
                                    <p>Tidak ada gunung terdaftar.</p>
                                </div>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>

<!-- Modal Tambah Gunung -->
<style>
#addMountainModal .modal-card, #editMountainModal .modal-card {
  background: #fff;
  max-width: 420px;
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
#addMountainModal .modal-card::-webkit-scrollbar, #editMountainModal .modal-card::-webkit-scrollbar { display: none; }
#addMountainModal h2, #editMountainModal h2 {
  text-align: center;
  margin-bottom: 18px;
  color: #2e8b57;
  font-size: 1.3rem;
  font-weight: 700;
}
#addMountainModal .form-group, #editMountainModal .form-group {
  margin-bottom: 18px;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
}
#addMountainModal label, #editMountainModal label {
  font-weight: 600;
  margin-bottom: 7px;
  color: #2c3e50;
}
#addMountainModal input[type="text"],
#addMountainModal input[type="number"],
#addMountainModal textarea,
#addMountainModal input[type="file"],
#editMountainModal input[type="text"],
#editMountainModal input[type="number"],
#editMountainModal textarea,
#editMountainModal input[type="file"] {
  width: 100%;
  padding: 12px;
  border: 1px solid #e9ecef;
  border-radius: 8px;
  font-size: 15px;
  margin-bottom: 0;
  transition: border 0.2s;
}
#addMountainModal input[type="text"]:focus,
#addMountainModal input[type="number"]:focus,
#addMountainModal textarea:focus,
#editMountainModal input[type="text"]:focus,
#editMountainModal input[type="number"]:focus,
#editMountainModal textarea:focus {
  border-color: #2e8b57;
  outline: none;
}
#addMountainModal .btn-primary, #editMountainModal .btn-primary {
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
#addMountainModal .btn-primary:hover, #editMountainModal .btn-primary:hover {
  background: linear-gradient(135deg, #249150, #2e8b57);
  box-shadow: 0 4px 16px rgba(46,139,87,0.18);
}
#addMountainModal .alert, #editMountainModal .alert {
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
<div id="addMountainModal" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(44,62,80,0.25);backdrop-filter:blur(2px);align-items:center;justify-content:center;">
  <div class="modal-card">
    <button type="button" onclick="closeAddMountainModal()" style="position:absolute;top:18px;right:18px;background:none;border:none;font-size:28px;color:#2e8b57;cursor:pointer;"><i class='fas fa-times'></i></button>
    <h2>Tambah Gunung</h2>
    <form id="addMountainForm" autocomplete="off" enctype="multipart/form-data">
      <div id="addMountainError" class="alert" style="display:none;"></div>
      <div class="form-group">
        <label for="add_name">Nama Gunung:</label>
        <input type="text" id="add_name" name="name" required>
      </div>
      <div class="form-group">
        <label for="add_description">Deskripsi:</label>
        <textarea id="add_description" name="description" rows="4" required></textarea>
      </div>
      <div class="form-group">
        <label for="add_height">Tinggi (meter):</label>
        <input type="number" id="add_height" name="height" min="0" required>
      </div>
      <div class="form-group">
        <label for="add_image">Foto Gunung:</label>
        <input type="file" id="add_image" name="image" accept="image/*">
        <small>Format: JPG, JPEG, PNG (Max 5MB)</small>
      </div>
      <div class="form-group" style="margin-top:10px;">
        <button type="submit" class="btn btn-primary">Tambah Gunung</button>
      </div>
    </form>
  </div>
</div>
<div id="editMountainModal" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(44,62,80,0.25);backdrop-filter:blur(2px);align-items:center;justify-content:center;">
  <div class="modal-card">
    <button type="button" onclick="closeEditMountainModal()" style="position:absolute;top:18px;right:18px;background:none;border:none;font-size:28px;color:#2e8b57;cursor:pointer;"><i class='fas fa-times'></i></button>
    <h2>Edit Gunung</h2>
    <form id="editMountainForm" autocomplete="off" enctype="multipart/form-data">
      <input type="hidden" id="edit_id" name="id">
      <div id="editMountainError" class="alert" style="display:none;"></div>
      <div class="form-group">
        <label for="edit_name">Nama Gunung:</label>
        <input type="text" id="edit_name" name="name" required>
      </div>
      <div class="form-group">
        <label for="edit_description">Deskripsi:</label>
        <textarea id="edit_description" name="description" rows="4" required></textarea>
      </div>
      <div class="form-group">
        <label for="edit_height">Tinggi (meter):</label>
        <input type="number" id="edit_height" name="height" min="0" required>
      </div>
      <div class="form-group">
        <label for="edit_image">Foto Gunung:</label>
        <input type="file" id="edit_image" name="image" accept="image/*">
        <small>Biarkan kosong jika tidak ingin mengubah gambar. Format: JPG, JPEG, PNG (Max 5MB)</small>
      </div>
      <div class="form-group" style="margin-top:10px;">
        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>
<script>
function openAddMountainModal() {
  document.getElementById('addMountainForm').reset();
  document.getElementById('addMountainError').style.display = 'none';
  document.getElementById('addMountainModal').style.display = 'flex';
}
function closeAddMountainModal() {
  document.getElementById('addMountainModal').style.display = 'none';
}
function openEditMountainModal(mountain) {
  document.getElementById('edit_id').value = mountain.id;
  document.getElementById('edit_name').value = mountain.name;
  document.getElementById('edit_description').value = mountain.description;
  document.getElementById('edit_height').value = mountain.height;
  document.getElementById('editMountainError').style.display = 'none';
  document.getElementById('editMountainModal').style.display = 'flex';
}
function closeEditMountainModal() {
  document.getElementById('editMountainModal').style.display = 'none';
}
document.querySelector('.header-actions .btn.btn-primary').addEventListener('click', function(e) {
  e.preventDefault();
  openAddMountainModal();
});
const addMountainForm = document.getElementById('addMountainForm');
addMountainForm.onsubmit = function(e) {
  e.preventDefault();
  const formData = new FormData(addMountainForm);
  fetch('mountain_create.php', {
    method: 'POST',
    body: formData
  }).then(async res => {
    if(res.ok) {
      window.location.reload();
    } else {
      const text = await res.text();
      document.getElementById('addMountainError').textContent = text || 'Gagal menambah gunung!';
      document.getElementById('addMountainError').style.display = 'block';
    }
  });
};
// Edit button logic
const editBtns = document.querySelectorAll('.btn-edit');
editBtns.forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    const tr = this.closest('tr');
    const mountain = {
      id: tr.querySelector('.action-buttons .btn-edit').getAttribute('href').split('=')[1],
      name: tr.querySelector('.user-name .name').textContent,
      description: this.getAttribute('data-description'),
      height: tr.querySelectorAll('td')[3].textContent.replace(/\D/g, '')
    };
    openEditMountainModal(mountain);
  });
});
const editMountainForm = document.getElementById('editMountainForm');
editMountainForm.onsubmit = function(e) {
  e.preventDefault();
  const formData = new FormData(editMountainForm);
  fetch('edit_mountain.php', {
    method: 'POST',
    body: formData
  }).then(async res => {
    if(res.ok) {
      window.location.reload();
    } else {
      const text = await res.text();
      document.getElementById('editMountainError').textContent = text || 'Gagal update gunung!';
      document.getElementById('editMountainError').style.display = 'block';
    }
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

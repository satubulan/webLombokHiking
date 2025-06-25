<?php 
session_start();
require_once '../config.php';

// Cek login dan role admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

// Cek dan buat tabel trips jika belum ada
$check_table = "SHOW TABLES LIKE 'trips'";
$table_exists = $conn->query($check_table);

if ($table_exists->num_rows == 0) {
    // Buat tabel trips
    $create_table = "CREATE TABLE trips (
        id INT AUTO_INCREMENT PRIMARY KEY,
        mountain_id INT NOT NULL,
        guide_id INT NOT NULL,
        start_date DATE NOT NULL,
        end_date DATE NOT NULL,
        price DECIMAL(10,2) NOT NULL,
        max_participants INT NOT NULL,
        active TINYINT(1) DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (mountain_id) REFERENCES mountains(id),
        FOREIGN KEY (guide_id) REFERENCES guides(id)
    )";
    
    if (!$conn->query($create_table)) {
        die("Error creating table: " . $conn->error);
    }
}

// Ambil data gunung beserta gambar
$mountains_query = "SELECT id, name, image FROM mountains ORDER BY name ASC";
$mountains_result = $conn->query($mountains_query);
if (!$mountains_result) {
    die("Error fetching mountains: " . $conn->error);
}
$mountains = $mountains_result->fetch_all(MYSQLI_ASSOC);
// Buat array id=>image untuk lookup cepat
$mountainImages = [];
foreach ($mountains as $m) {
    $mountainImages[$m['id']] = $m['image'];
}

// Ambil guide dari tabel guide dan nama dari tabel users
$guides_query = "SELECT g.id, u.name FROM guide g JOIN users u ON g.user_id = u.id WHERE g.status = 'approved' ORDER BY u.name ASC";
$guides_result = $conn->query($guides_query);
if (!$guides_result) {
    die("Error fetching guides: " . $conn->error);
}
$guides = $guides_result->fetch_all(MYSQLI_ASSOC);

// Ambil semua tiket gunung (trip) dari mountain_tickets
$sql = "SELECT t.*, m.name AS mountain_name, m.image AS mountain_image FROM mountain_tickets t LEFT JOIN mountains m ON t.mountain_id = m.id ORDER BY t.id ASC";
$result = $conn->query($sql);
if (!$result) { die("Error fetching trips: " . $conn->error); }
$trips = $result->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manajemen Trip - Admin Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/guide.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        .trips-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .add-trip-btn {
            background: linear-gradient(135deg, #2e8b57, #3cb371);
            color: white;
            padding: 12px 24px;
            border: none;
            border-radius: 10px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(46, 139, 87, 0.3);
        }
        
        .add-trip-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(46, 139, 87, 0.4);
            color: white;
        }
        
        .trips-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(350px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .trip-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
        }
        
        .trip-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        
        .trip-image {
            height: 200px;
            background: linear-gradient(135deg, rgba(46, 139, 87, 0.8), rgba(60, 179, 113, 0.8));
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }
        
        .trip-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .trip-status {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            color: white;
            text-transform: uppercase;
        }
        
        .status-active {
            background: #28a745;
        }
        
        .status-inactive {
            background: #6c757d;
        }
        
        .trip-content {
            padding: 20px;
        }
        
        .trip-title {
            font-size: 1.3rem;
            font-weight: 700;
            margin-bottom: 10px;
            color: #2c3e50;
            line-height: 1.3;
        }
        
        .trip-destination {
            color: #2e8b57;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .trip-meta {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .meta-item {
            display: flex;
            align-items: center;
            gap: 8px;
            color: #6c757d;
            font-size: 14px;
        }
        
        .meta-item i {
            color: #2e8b57;
            width: 16px;
        }
        
        .trip-price {
            font-size: 1.4rem;
            font-weight: 700;
            color: #e67e22;
            margin-bottom: 15px;
            text-align: center;
            padding: 10px;
            background: #fff3e0;
            border-radius: 8px;
        }
        
        .trip-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .action-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            font-size: 12px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            flex: 1;
            justify-content: center;
            min-width: 80px;
        }
        
        .btn-primary-action {
            background: #2e8b57;
            color: white;
        }
        
        .btn-danger-action {
            background: #dc3545;
            color: white;
        }
        
        .action-btn:hover {
            transform: scale(1.05);
            opacity: 0.9;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        
        .empty-state i {
            font-size: 4rem;
            color: #e9ecef;
            margin-bottom: 20px;
        }
        
        .empty-state h3 {
            color: #6c757d;
            margin-bottom: 15px;
        }
        
        .empty-state p {
            color: #adb5bd;
            margin-bottom: 25px;
        }
        
        .admin-sidebar {
            width: 220px;
            background-color: #2e8b57;
            color: white;
            display: flex;
            flex-direction: column;
            padding: 20px;
            position: fixed;
            top: 0;
            left: 0;
            height: 100vh;
            z-index: 100;
        }
        .admin-main {
            flex: 1;
            padding: 30px;
            overflow-x: auto;
            margin-left: 220px;
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
            <li><a href="trips.php" class="nav-link active"><i class="fas fa-route"></i> Trip</a></li>
            <li><a href="feedback.php" class="nav-link"><i class="fas fa-comments"></i> Feedback</a></li>
            <li><a href="lihat_pembayaran.php" class="nav-link"><i class="fas fa-money-bill-wave"></i> Lihat Pembayaran</a></li>
            <li><a href="notifikasi.php" class="nav-link"><i class="fas fa-bell"></i> Notifikasi</a></li>
            <li><a href="profile.php" class="nav-link"><i class="fas fa-user-cog"></i> Profil</a></li>
            <li><a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>

    <main class="admin-main">
        <!-- Header -->
        <div class="trips-header">
            <div>
                <h1><i class="fas fa-route"></i> Kelola Trip Pendakian</h1>
                <p>Atur dan kelola trip pendakian yang tersedia</p>
            </div>
            <!-- Tombol tambah trip pop up -->
            <button type="button" class="add-trip-btn" onclick="openAddTripModal()">
                <i class="fas fa-plus"></i>
                <span>Tambah Trip Baru</span>
            </button>
        </div>

        <?php if (isset($error)): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <!-- Trips Grid -->
        <?php if (count($trips) > 0): ?>
            <div class="trips-grid">
                <?php foreach ($trips as $trip): ?>
                    <div class="trip-card"
                         data-id="<?php echo $trip['id']; ?>"
                         data-title="<?php echo htmlspecialchars($trip['title'], ENT_QUOTES); ?>"
                         data-type="<?php echo htmlspecialchars($trip['type'] ?? 'regular', ENT_QUOTES); ?>"
                         data-mountain_id="<?php echo $trip['mountain_id']; ?>"
                         data-price="<?php echo $trip['price']; ?>"
                         data-status="<?php echo $trip['status']; ?>"
                         style="display:flex;flex-direction:column;justify-content:flex-start;align-items:stretch;min-height:380px;">
                        <div class="trip-image" style="height:200px;overflow:hidden;border-top-left-radius:15px;border-top-right-radius:15px;">
                            <?php if (!empty($trip['mountain_image'])): ?>
                                <img src="../assets/images/mountains/<?php echo htmlspecialchars($trip['mountain_image']); ?>" alt="Foto <?php echo htmlspecialchars($trip['mountain_name']); ?>" style="width:100%;height:100%;object-fit:cover;">
                            <?php else: ?>
                                <div class="profile-thumbnail no-image" style="display:flex;align-items:center;justify-content:center;height:100%;background:#e9ecef;"><i class="fas fa-mountain" style="font-size:3rem;color:#b0b0b0;"></i></div>
                            <?php endif; ?>
                            <div class="trip-status status-<?php echo $trip['status'] == 'active' ? 'active' : 'inactive'; ?>" style="position:absolute;top:15px;right:15px;padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;color:white;text-transform:uppercase;background:<?php echo $trip['status']=='active'?'#28a745':'#6c757d'; ?>;box-shadow:0 2px 8px rgba(0,0,0,0.08);">
                                <?php echo $trip['status'] == 'active' ? 'AKTIF' : 'NONAKTIF'; ?>
                            </div>
                        </div>
                        <div class="trip-content" style="padding:22px 20px 18px 20px;display:flex;flex-direction:column;align-items:flex-start;gap:10px;flex:1;">
                            <h3 class="trip-title" style="font-size:1.25rem;font-weight:700;margin:0 0 6px 0;color:#2c3e50;line-height:1.2;word-break:break-word;"> <?php echo htmlspecialchars($trip['title']); ?> </h3>
                            <div class="trip-destination" style="color:#2e8b57;font-weight:600;display:flex;align-items:center;gap:7px;font-size:1rem;">
                                <i class="fas fa-mountain"></i>
                                <span><?php echo htmlspecialchars($trip['mountain_name']); ?></span>
                            </div>
                            <div class="trip-price" style="margin-top:18px;width:100%;background:#fff3e0;border-radius:8px;text-align:center;padding:13px 0 7px 0;font-size:1.35rem;font-weight:700;color:#e67e22;letter-spacing:0.5px;">
                                Rp <?php echo number_format($trip['price'], 0, ',', '.'); ?>
                                <small style="font-size:0.95rem;font-weight:400;color:#b97b2e;">/orang</small>
                            </div>
                            <div class="trip-actions" style="display:flex;gap:10px;width:100%;margin-top:18px;">
                                <a href="#" class="action-btn btn-primary-action"
                                   style="flex:1;background:#2e8b57;color:#fff;padding:10px 0;border-radius:7px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:7px;font-size:1rem;text-decoration:none;transition:background 0.2s;">
                                    <i class="fas fa-edit"></i>
                                    <span>Edit</span>
                                </a>
                                <a href="delete_trip.php?id=<?php echo $trip['id']; ?>" class="action-btn btn-danger-action" style="flex:1;background:#dc3545;color:#fff;padding:10px 0;border-radius:7px;font-weight:600;display:flex;align-items:center;justify-content:center;gap:7px;font-size:1rem;text-decoration:none;transition:background 0.2s;" onclick="return confirm('Yakin ingin hapus trip ini?')">
                                    <i class="fas fa-trash-alt"></i>
                                    <span>Hapus</span>
                                </a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <div class="empty-state">
                <i class="fas fa-route"></i>
                <h3>Belum Ada Trip</h3>
                <p>Belum ada trip pendakian yang terdaftar. Mulai tambahkan trip pertama!</p>
                <a href="trip_create.php" class="add-trip-btn">
                    <i class="fas fa-plus"></i>
                    <span>Tambah Trip Pertama</span>
                </a>
            </div>
        <?php endif; ?>
    </main>
</div>

<!-- Tambahkan modal pop up form tambah trip -->
<div id="addTripModal" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(44,62,80,0.25);backdrop-filter:blur(2px);align-items:center;justify-content:center;">
  <div class="modal-card" style="background:#fff;max-width:480px;width:95vw;border-radius:18px;box-shadow:0 8px 32px rgba(44,62,80,0.18);padding:36px 32px 28px 32px;position:relative;display:flex;flex-direction:column;gap:0;max-height:90vh;overflow-y:auto;">
    <button type="button" onclick="closeAddTripModal()" style="position:absolute;top:18px;right:18px;background:none;border:none;font-size:28px;color:#2e8b57;cursor:pointer;"><i class='fas fa-times'></i></button>
    <h2 style="text-align:center;margin-bottom:18px;color:#2e8b57;font-size:1.3rem;font-weight:700;">Tambah Trip</h2>
    <form id="addTripForm" autocomplete="off">
      <div id="addTripError" class="alert" style="display:none;background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;font-size:14px;margin-bottom:12px;padding:10px 14px;border-radius:7px;"></div>
      <div class="form-group">
        <label for="trip_title" style="font-weight:600;margin-bottom:7px;color:#2c3e50;">Judul Trip:</label>
        <input type="text" id="trip_title" name="title" required style="width:100%;padding:12px;border:1px solid #e9ecef;border-radius:8px;font-size:15px;">
      </div>
      <div class="form-group">
        <label for="trip_type" style="font-weight:600;margin-bottom:7px;color:#2c3e50;">Tipe Trip:</label>
        <select id="trip_type" name="type" required style="width:100%;padding:12px;border:1px solid #e9ecef;border-radius:8px;font-size:15px;">
          <option value="regular">Reguler</option>
          <option value="package">Package</option>
        </select>
      </div>
      <div class="form-group">
        <label for="trip_mountain" style="font-weight:600;margin-bottom:7px;color:#2c3e50;">Pilih Gunung:</label>
        <select id="trip_mountain" name="mountain_id" required style="width:100%;padding:12px;border:1px solid #e9ecef;border-radius:8px;font-size:15px;">
          <option value="">-- Pilih Gunung --</option>
          <?php foreach ($mountains as $mountain): ?>
            <option value="<?php echo $mountain['id']; ?>"><?php echo htmlspecialchars($mountain['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label for="trip_price" style="font-weight:600;margin-bottom:7px;color:#2c3e50;">Harga Tiket per Orang (Rp):</label>
        <input type="number" id="trip_price" name="price" min="0" step="1000" required style="width:100%;padding:12px;border:1px solid #e9ecef;border-radius:8px;font-size:15px;">
      </div>
      <div class="form-group">
        <label for="trip_status" style="font-weight:600;margin-bottom:7px;color:#2c3e50;">Status:</label>
        <select id="trip_status" name="status" required style="width:100%;padding:12px;border:1px solid #e9ecef;border-radius:8px;font-size:15px;">
          <option value="active">Aktif</option>
          <option value="inactive">Nonaktif</option>
        </select>
      </div>
      <div class="form-group" style="margin-top:10px;">
        <button type="submit" class="btn btn-primary" style="width:100%;background:linear-gradient(135deg,#2e8b57,#3cb371);color:#fff;border:none;border-radius:8px;padding:13px 0;font-size:16px;font-weight:700;cursor:pointer;box-shadow:0 2px 8px rgba(46,139,87,0.10);">Tambah Trip</button>
      </div>
    </form>
  </div>
</div>
<script>
function openAddTripModal() {
  document.getElementById('addTripForm').reset();
  document.getElementById('addTripError').style.display = 'none';
  document.getElementById('addTripModal').style.display = 'flex';
}
function closeAddTripModal() {
  document.getElementById('addTripModal').style.display = 'none';
}
const addTripForm = document.getElementById('addTripForm');
addTripForm.onsubmit = function(e) {
  e.preventDefault();
  const formData = new FormData(addTripForm);
  fetch('trip_create.php', {
    method: 'POST',
    body: formData
  }).then(async res => {
    if(res.ok) {
      window.location.reload();
    } else {
      const text = await res.text();
      document.getElementById('addTripError').textContent = text || 'Gagal menambah trip!';
      document.getElementById('addTripError').style.display = 'block';
    }
  });
};
</script>

<!-- Modal Edit Trip -->
<div id="editTripModal" style="display:none;position:fixed;z-index:9999;top:0;left:0;width:100vw;height:100vh;background:rgba(44,62,80,0.25);backdrop-filter:blur(2px);align-items:center;justify-content:center;">
  <div class="modal-card" style="background:#fff;max-width:480px;width:95vw;border-radius:18px;box-shadow:0 8px 32px rgba(44,62,80,0.18);padding:36px 32px 28px 32px;position:relative;display:flex;flex-direction:column;gap:0;max-height:90vh;overflow-y:auto;">
    <button type="button" onclick="closeEditTripModal()" style="position:absolute;top:18px;right:18px;background:none;border:none;font-size:28px;color:#2e8b57;cursor:pointer;"><i class='fas fa-times'></i></button>
    <h2 style="text-align:center;margin-bottom:18px;color:#2e8b57;font-size:1.3rem;font-weight:700;">Edit Trip</h2>
    <form id="editTripForm" autocomplete="off">
      <input type="hidden" id="edit_trip_id" name="id">
      <div id="editTripError" class="alert" style="display:none;background:#f8d7da;color:#721c24;border:1px solid #f5c6cb;font-size:14px;margin-bottom:12px;padding:10px 14px;border-radius:7px;"></div>
      <div class="form-group">
        <label for="edit_trip_title" style="font-weight:600;margin-bottom:7px;color:#2c3e50;">Judul Trip:</label>
        <input type="text" id="edit_trip_title" name="title" required style="width:100%;padding:12px;border:1px solid #e9ecef;border-radius:8px;font-size:15px;">
      </div>
      <div class="form-group">
        <label for="edit_trip_type" style="font-weight:600;margin-bottom:7px;color:#2c3e50;">Tipe Trip:</label>
        <select id="edit_trip_type" name="type" required style="width:100%;padding:12px;border:1px solid #e9ecef;border-radius:8px;font-size:15px;">
          <option value="regular">Reguler</option>
          <option value="package">Package</option>
        </select>
      </div>
      <div class="form-group">
        <label for="edit_trip_mountain" style="font-weight:600;margin-bottom:7px;color:#2c3e50;">Pilih Gunung:</label>
        <select id="edit_trip_mountain" name="mountain_id" required style="width:100%;padding:12px;border:1px solid #e9ecef;border-radius:8px;font-size:15px;">
          <option value="">-- Pilih Gunung --</option>
          <?php foreach ($mountains as $mountain): ?>
            <option value="<?php echo $mountain['id']; ?>"><?php echo htmlspecialchars($mountain['name']); ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label for="edit_trip_price" style="font-weight:600;margin-bottom:7px;color:#2c3e50;">Harga Tiket per Orang (Rp):</label>
        <input type="number" id="edit_trip_price" name="price" min="0" step="1000" required style="width:100%;padding:12px;border:1px solid #e9ecef;border-radius:8px;font-size:15px;">
      </div>
      <div class="form-group">
        <label for="edit_trip_status" style="font-weight:600;margin-bottom:7px;color:#2c3e50;">Status:</label>
        <select id="edit_trip_status" name="status" required style="width:100%;padding:12px;border:1px solid #e9ecef;border-radius:8px;font-size:15px;">
          <option value="active">Aktif</option>
          <option value="inactive">Nonaktif</option>
        </select>
      </div>
      <div class="form-group" style="margin-top:10px;">
        <button type="submit" class="btn btn-primary" style="width:100%;background:linear-gradient(135deg,#2e8b57,#3cb371);color:#fff;border:none;border-radius:8px;padding:13px 0;font-size:16px;font-weight:700;cursor:pointer;box-shadow:0 2px 8px rgba(46,139,87,0.10);">Simpan Perubahan</button>
      </div>
    </form>
  </div>
</div>
<script>
function openEditTripModal(trip) {
  document.getElementById('edit_trip_id').value = trip.id;
  document.getElementById('edit_trip_title').value = trip.title;
  document.getElementById('edit_trip_type').value = trip.type;
  document.getElementById('edit_trip_mountain').value = trip.mountain_id;
  document.getElementById('edit_trip_price').value = trip.price;
  document.getElementById('edit_trip_status').value = trip.status;
  document.getElementById('editTripError').style.display = 'none';
  document.getElementById('editTripModal').style.display = 'flex';
}
function closeEditTripModal() {
  document.getElementById('editTripModal').style.display = 'none';
}
// Event tombol edit
const editBtns = document.querySelectorAll('.btn-primary-action');
editBtns.forEach(btn => {
  btn.addEventListener('click', function(e) {
    e.preventDefault();
    const card = this.closest('.trip-card');
    const trip = {
      id: card.getAttribute('data-id'),
      title: card.getAttribute('data-title'),
      type: card.getAttribute('data-type'),
      mountain_id: card.getAttribute('data-mountain_id'),
      price: card.getAttribute('data-price'),
      status: card.getAttribute('data-status')
    };
    openEditTripModal(trip);
  });
});
const editTripForm = document.getElementById('editTripForm');
editTripForm.onsubmit = function(e) {
  e.preventDefault();
  const formData = new FormData(editTripForm);
  fetch('edit_trip.php', {
    method: 'POST',
    body: formData
  }).then(async res => {
    if(res.ok) {
      window.location.reload();
    } else {
      const text = await res.text();
      document.getElementById('editTripError').textContent = text || 'Gagal update trip!';
      document.getElementById('editTripError').style.display = 'block';
    }
  });
};
</script>

<script>
// Auto-hide alerts after 5 seconds
setTimeout(function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        alert.style.transition = 'opacity 0.5s ease';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    });
}, 5000);
</script>
</body>
</html>

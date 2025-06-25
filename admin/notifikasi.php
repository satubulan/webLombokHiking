<?php
session_start();
require_once '../config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../views/login.php');
    exit();
}

// Notifikasi pengajuan guide (dari notifikasi_guide)
$sql_pending = "SELECT ng.id as notif_id, u.id as user_id, u.name, u.email, u.phone, ng.spesialis, ng.pengalaman, ng.waktu_pengajuan FROM notifikasi_guide ng JOIN users u ON ng.user_id = u.id ORDER BY ng.waktu_pengajuan DESC";
$result_pending = $conn->query($sql_pending);
$pending_guides = $result_pending ? $result_pending->fetch_all(MYSQLI_ASSOC) : [];

// Notifikasi dari kontak me (dari notifikasi_contact_me)
$sql_contact = "SELECT nc.id as notif_id, u.name, u.email, nc.pesan, nc.waktu FROM notifikasi_contact_me nc JOIN users u ON nc.user_id = u.id ORDER BY nc.waktu DESC";
$result_contact = $conn->query($sql_contact);
$contact_notif = $result_contact ? $result_contact->fetch_all(MYSQLI_ASSOC) : [];
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8" />
    <title>Notifikasi - Admin Lombok Hiking</title>
    <link rel="stylesheet" href="../assets/css/guide.css" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
    <style>
        .notif-header { font-size: 1.7rem; font-weight: 700; color: #2e8b57; margin-bottom: 18px; }
        .notif-table-container { background: #fff; border-radius: 12px; box-shadow: 0 2px 12px rgba(0,0,0,0.07); padding: 24px; margin-bottom: 36px; }
        .notif-table th, .notif-table td { padding: 10px 12px; text-align: left; }
        .notif-table th { background: #f8f9fa; color: #2e8b57; font-weight: 700; }
        .notif-table tr:nth-child(even) { background: #f6f6f6; }
        .notif-table { width: 100%; border-collapse: collapse; margin-bottom: 0; }
        .notif-empty { text-align: center; color: #aaa; font-style: italic; padding: 18px 0; }
        @media (max-width: 700px) { .notif-table-container { padding: 10px; } .notif-header { font-size: 1.2rem; } }
    </style>
</head>
<body>
<div class="admin-layout">
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
            <li><a href="notifikasi.php" class="nav-link active"><i class="fas fa-bell"></i> Notifikasi</a></li>
            <li><a href="profile.php" class="nav-link"><i class="fas fa-user-cog"></i> Profil</a></li>
            <li><a href="../logout.php" class="nav-link"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
        </ul>
    </aside>
    <main class="admin-main">
        <div class="notif-header"><i class="fas fa-user-plus"></i> Notifikasi Pengajuan Guide</div>
        <div class="notif-table-container">
            <table class="notif-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        <th>Spesialisasi</th>
                        <th>Pengalaman</th>
                        <th>Waktu Pengajuan</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($pending_guides) > 0): foreach ($pending_guides as $g): ?>
                    <tr>
                        <td><?= htmlspecialchars($g['name']) ?></td>
                        <td><?= htmlspecialchars($g['email']) ?></td>
                        <td><?= htmlspecialchars($g['phone']) ?></td>
                        <td><?= htmlspecialchars($g['spesialis']) ?></td>
                        <td><?= htmlspecialchars($g['pengalaman']) ?></td>
                        <td><?= date('d M Y H:i', strtotime($g['waktu_pengajuan'])) ?></td>
                        <td>
                            <button class="btn-verif" data-user='<?= json_encode($g) ?>'>Verifikasi</button>
                            <button class="btn-tolak" data-id="<?= $g['notif_id'] ?>">Tolak</button>
                        </td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="7" class="notif-empty">Tidak ada pengajuan guide baru.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        <div class="notif-header"><i class="fas fa-envelope"></i> Notifikasi Kontak Masuk</div>
        <div class="notif-table-container">
            <table class="notif-table">
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Pesan</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (count($contact_notif) > 0): foreach ($contact_notif as $c): ?>
                    <tr>
                        <td><?= htmlspecialchars($c['name']) ?></td>
                        <td><?= htmlspecialchars($c['email']) ?></td>
                        <td><?= htmlspecialchars($c['pesan']) ?></td>
                        <td><?= date('d M Y H:i', strtotime($c['waktu'])) ?></td>
                    </tr>
                    <?php endforeach; else: ?>
                    <tr><td colspan="4" class="notif-empty">Tidak ada pesan masuk.</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </main>
</div>
<!-- Modal Verifikasi -->
<div id="modalVerif" style="display:none;position:fixed;z-index:9999;left:0;top:0;width:100vw;height:100vh;background:rgba(0,0,0,0.3);align-items:center;justify-content:center;">
    <div style="background:#fff;padding:32px 24px;border-radius:12px;max-width:400px;width:90%;position:relative;">
        <h3 style="margin-top:0;margin-bottom:18px;">Verifikasi Pengajuan Guide</h3>
        <div id="verifDetail"></div>
        <div style="margin-top:22px;text-align:right;">
            <button id="btnVerifAksi" style="background:#2e8b57;color:#fff;padding:7px 18px;border:none;border-radius:5px;">Verifikasi</button>
            <button id="btnTutupModal" style="background:#aaa;color:#fff;padding:7px 18px;border:none;border-radius:5px;">Tutup</button>
        </div>
    </div>
</div>
<script>
// Modal verifikasi
let modal = document.getElementById('modalVerif');
let verifDetail = document.getElementById('verifDetail');
let userVerif = null;
document.querySelectorAll('.btn-verif').forEach(btn => {
    btn.onclick = function() {
        userVerif = JSON.parse(this.dataset.user);
        verifDetail.innerHTML = `
            <b>Nama:</b> ${userVerif.name}<br>
            <b>Email:</b> ${userVerif.email}<br>
            <b>Telepon:</b> ${userVerif.phone}<br>
            <b>Spesialisasi:</b> ${userVerif.spesialis}<br>
            <b>Pengalaman:</b> ${userVerif.pengalaman}<br>
            <b>Waktu Pengajuan:</b> ${userVerif.waktu_pengajuan}
        `;
        modal.style.display = 'flex';
        document.getElementById('btnVerifAksi').onclick = function() {
            if (!userVerif) return;
            fetch('verifikasi_guide.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({ user_id: userVerif.user_id, notif_id: userVerif.notif_id, aksi: 'verifikasi' })
            }).then(res => res.json()).then(data => {
                alert(data.message);
                location.reload();
            });
        };
    };
});
document.getElementById('btnTutupModal').onclick = function() { modal.style.display = 'none'; };
document.querySelectorAll('.btn-tolak').forEach(btn => {
    btn.onclick = function() {
        if (!confirm('Tolak pengajuan guide ini?')) return;
        fetch('verifikasi_guide.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({ notif_id: this.dataset.id, aksi: 'tolak' })
        }).then(res => res.json()).then(data => {
            alert(data.message);
            location.reload();
        });
    };
});
</script>
</body>
</html> 
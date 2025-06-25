<?php
session_start();
require_once '../config.php';

// Cek login user
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'user') {
    header('Location: ../views/login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$error_message = '';
$success_message = '';

// Ambil semua tiket gunung (mountain_tickets) yang aktif
$mountain_tickets = $conn->query("SELECT t.*, m.name AS mountain_name FROM mountain_tickets t LEFT JOIN mountains m ON t.mountain_id = m.id WHERE t.status = 'active' ORDER BY t.id ASC");

// Ambil semua guide yang approved
$guides = $conn->query("SELECT g.*, u.name as guide_name FROM guide g JOIN users u ON g.user_id = u.id WHERE g.status = 'approved'");

// Ambil semua service_fee guide per gunung beserta nama guide
$guide_services = [];
$gs_result = $conn->query("SELECT gs.*, g.user_id, u.name as guide_name, m.name as mountain_name FROM guide_services gs JOIN guide g ON gs.guide_id = g.id JOIN users u ON g.user_id = u.id JOIN mountains m ON gs.mountain_id = m.id WHERE gs.active = 1");
while ($row = $gs_result->fetch_assoc()) {
    $guide_services[$row['mountain_id']][$row['guide_id']] = $row;
}

// Proses booking reguler
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['book_reguler'])) {
    $mountain_ticket_id = $_POST['mountain_ticket_id'];
    $guide_id = $_POST['guide_id'];
    $start_date = $_POST['start_date'];
    $end_date = $_POST['end_date'];
    $trip_price = str_replace('.', '', $_POST['trip_price']);
    $addon_fee = str_replace('.', '', $_POST['addon_fee']);
    $total_price = $trip_price + $addon_fee;
    $booking_id = uniqid('book_');
    $now = date('Y-m-d');

    // Insert ke tabel bookings dengan start_date, end_date, trip_price sebagai total_price, dan addon_fee
    $stmt = $conn->prepare("INSERT INTO bookings (id, user_id, mountain_ticket_id, selected_guide_id, start_date, end_date, booking_date, status, total_price, addon_fee, booking_type) VALUES (?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, 'regular')");
    if (!$stmt) { die('Query error: ' . $conn->error); }
    $stmt->bind_param("sssssssdd", $booking_id, $user_id, $mountain_ticket_id, $guide_id, $start_date, $end_date, $now, $trip_price, $addon_fee);
    if ($stmt->execute()) {
        // Insert ke tabel pembayaran
        $payment_code = 'PAY' . rand(10000000, 99999999);
        $amount = $total_price;
        $stmtPay = $conn->prepare("INSERT INTO pembayaran (booking_id, payment_code, amount) VALUES (?, ?, ?)");
        if (!$stmtPay) { die('Query error: ' . $conn->error); }
        $stmtPay->bind_param("ssd", $booking_id, $payment_code, $amount);
        $stmtPay->execute();

        // Ambil payment_code dari tabel pembayaran
        $stmtGet = $conn->prepare("SELECT payment_code FROM pembayaran WHERE booking_id=?");
        if (!$stmtGet) { die('Query error: ' . $conn->error); }
        $stmtGet->bind_param("s", $booking_id);
        $stmtGet->execute();
        $stmtGet->bind_result($payment_code);
        $stmtGet->fetch();
        $stmtGet->close();

        // Tampilkan payment_code dan form upload bukti pembayaran
        echo '<!DOCTYPE html>';
        echo '<html lang="id">';
        echo '<head>';
        echo '<meta charset="UTF-8">';
        echo '<meta name="viewport" content="width=device-width, initial-scale=1.0">';
        echo '<title>Kode Pembayaran & Upload Bukti</title>';
        echo '<link rel="stylesheet" href="../assets/css/style.css">';
        echo '<link rel="stylesheet" href="../assets/css/index.css">';
        echo '<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">';
        echo '</head>';
        echo '<body>';
        echo '<div class="admin-container">';
        echo '<aside class="sidebar">';
        echo '<div class="logo"><i class="fas fa-mountain"></i> Lombok Hiking</div>';
        echo '<nav>';
        echo '<a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>';
        echo '<a href="profile.php"><i class="fas fa-user"></i> Profil Saya</a>';
        echo '<a href="booking.php" class="active"><i class="fas fa-calendar-plus"></i> Booking Trip</a>';
        echo '<a href="status_pembayaran.php"><i class="fas fa-credit-card"></i> Status Pembayaran</a>';
        echo '<a href="paket_saya.php"><i class="fas fa-hiking"></i> Paket Saya</a>';
        echo '<a href="ajukan_guide.php"><i class="fas fa-user-plus"></i> Ajukan Diri Jadi Guide</a>';
        echo '<a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>';
        echo '</nav>';
        echo '</aside>';
        echo '<main class="main">';
        echo '<div class="booking-container">';
        echo '<div class="section-header"><h3><i class="fas fa-money-bill-wave"></i> Kode Pembayaran</h3></div>';
        echo '<div style="font-size:1.5em;font-weight:bold;margin:20px 0 30px 0;text-align:center;">'.$payment_code.'</div>';
        echo '<div class="section-header"><h3><i class="fas fa-upload"></i> Upload Bukti Pembayaran</h3></div>';
        echo '<form method="POST" enctype="multipart/form-data" style="max-width:400px;margin:40px auto 0 auto;">';
        echo '<input type="hidden" name="booking_id" value="'.$booking_id.'">';
        echo '<div class="form-group">';
        echo '<input type="file" name="bukti_pembayaran" accept="image/*" required style="margin-bottom:20px;">';
        echo '</div>';
        echo '<button type="submit" class="btn-primary"><i class="fas fa-upload"></i> Upload</button>';
        echo '</form>';
        echo '</div>';
        echo '</main>';
        echo '</div>';
        echo '</body>';
        echo '</html>';
        exit();
    } else {
        $error_message = "Gagal booking trip reguler.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Booking Trip Reguler</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/index.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <style>
        /* Tambahan style untuk memperkecil card paket trip */
        .trips-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
            gap: 25px;
        }
        .trip-card {
            max-width: 400px;
            margin: 0 auto;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="logo">
                <i class="fas fa-mountain"></i>
                Lombok Hiking
            </div>
            <nav>
                <a href="dashboard.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
                <a href="profile.php"><i class="fas fa-user"></i> Profil Saya</a>
                <a href="booking.php" class="active"><i class="fas fa-calendar-plus"></i> Booking Trip</a>
                <a href="status_pembayaran.php"><i class="fas fa-credit-card"></i> Status Pembayaran</a>
                <a href="paket_saya.php"><i class="fas fa-hiking"></i> Paket Saya</a>
                <a href="ajukan_guide.php"><i class="fas fa-user-plus"></i> Ajukan Diri Jadi Guide</a>
                <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </nav>
        </aside>
        <main class="main">
            <div class="form-container" style="max-width:500px;margin:40px auto 0 auto;">
                <form method="POST" id="formReguler">
                    <div class="form-group">
                        <label for="trip_selector" style="font-weight:600;margin-bottom:6px;display:block;">Pilih Trip Reguler</label>
                        <select id="trip_selector" name="mountain_ticket_id" onchange="onTripChange()" required style="width:100%;padding:12px;font-size:1.1rem;border-radius:6px;border:1px solid #ddd;">
                            <option value="">-- Pilih Tiket Gunung --</option>
                            <?php foreach ($mountain_tickets as $ticket): ?>
                                <option value="<?= $ticket['id'] ?>" data-mountain_id="<?= $ticket['mountain_id'] ?>" data-price="<?= $ticket['price'] ?>" data-type="<?= $ticket['type'] ?>"> <?= htmlspecialchars($ticket['title']) ?> - <?= htmlspecialchars($ticket['mountain_name']) ?> (<?= ucfirst($ticket['type']) ?>, Rp <?= number_format($ticket['price'], 0, ',', '.') ?>) </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group" id="guide_option_group" style="display:none;">
                        <label style="font-weight:600;margin-bottom:6px;display:block;">Opsi Guide</label>
                        <label style="margin-right:20px;"><input type="radio" name="with_guide" value="1" checked onchange="onGuideOptionChange()"> Pilih Guide</label>
                        <label><input type="radio" name="with_guide" value="0" onchange="onGuideOptionChange()"> Tanpa Guide</label>
                    </div>
                    <div id="formRegulerFields" style="display:none;">
                        <div class="form-group" id="guide_selector_group">
                            <label for="guide_selector" style="font-weight:600;margin-bottom:6px;display:block;">Pilih Guide</label>
                            <select id="guide_selector" name="guide_id" style="width:100%;padding:12px;font-size:1.1rem;border-radius:6px;border:1px solid #ddd;"></select>
                        </div>
                        <div class="form-group">
                            <label for="start_date" style="font-weight:600;margin-bottom:6px;display:block;">Tanggal Mulai</label>
                            <input type="date" id="start_date" name="start_date" onchange="onDateChange()" required style="width:100%;padding:12px;font-size:1.1rem;border-radius:6px;border:1px solid #ddd;">
                        </div>
                        <div class="form-group">
                            <label for="end_date" style="font-weight:600;margin-bottom:6px;display:block;">Tanggal Selesai</label>
                            <input type="date" id="end_date" name="end_date" onchange="onDateChange()" required style="width:100%;padding:12px;font-size:1.1rem;border-radius:6px;border:1px solid #ddd;">
                        </div>
                        <div class="form-group">
                            <label style="font-weight:600;margin-bottom:6px;display:block;">Harga Trip</label>
                            <input type="text" id="trip_price" readonly style="width:100%;padding:12px;font-size:1.1rem;border-radius:6px;border:1px solid #ddd;background:#f8f9fa;">
                        </div>
                        <div class="form-group" id="guide_price_group">
                            <label style="font-weight:600;margin-bottom:6px;display:block;">Harga Guide (per hari)</label>
                            <input type="text" id="guide_price" readonly style="width:100%;padding:12px;font-size:1.1rem;border-radius:6px;border:1px solid #ddd;background:#f8f9fa;">
                        </div>
                        <div class="form-group" id="guide_total_group">
                            <label style="font-weight:600;margin-bottom:6px;display:block;">Harga Guide x Hari</label>
                            <input type="text" id="guide_total" readonly style="width:100%;padding:12px;font-size:1.1rem;border-radius:6px;border:1px solid #ddd;background:#f8f9fa;">
                        </div>
                        <div class="form-group">
                            <label style="font-weight:600;margin-bottom:6px;display:block;">Total Harga</label>
                            <input type="text" id="total_price" name="total_price" readonly style="width:100%;padding:12px;font-size:1.1rem;border-radius:6px;border:1px solid #ddd;background:#f8f9fa;">
                        </div>
                        <input type="hidden" id="trip_price_hidden" name="trip_price">
                        <input type="hidden" id="addon_fee_hidden" name="addon_fee">
                        <button type="submit" class="btn btn-primary" name="book_reguler" style="width:100%;font-size:1.1rem;padding:14px 0;margin-top:10px;background:#2e8b57;">Book Sekarang</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
    <script>
    const guideServices = <?= json_encode($guide_services) ?>;
    function onTripChange() {
        const tripSel = document.getElementById('trip_selector');
        const ticketId = tripSel.value;
        const selected = tripSel.options[tripSel.selectedIndex];
        const mountainId = selected.getAttribute('data-mountain_id');
        const tripPrice = selected.getAttribute('data-price');
        const type = selected.getAttribute('data-type');
        if (type === 'regular') {
            document.getElementById('formRegulerFields').style.display = 'block';
            document.getElementById('guide_option_group').style.display = 'block';
            // Default: Pilih Guide
            document.querySelector('input[name="with_guide"][value="1"]').checked = true;
            onGuideOptionChange();
            // Populate guide dropdown
            const guideSel = document.getElementById('guide_selector');
            guideSel.innerHTML = '';
            if (guideServices[mountainId]) {
                for (const guideId in guideServices[mountainId]) {
                    const gs = guideServices[mountainId][guideId];
                    const option = document.createElement('option');
                    option.value = gs.guide_id;
                    option.text = gs.guide_name + ' (Rp ' + parseInt(gs.service_fee).toLocaleString() + '/hari)';
                    option.setAttribute('data-fee', gs.service_fee);
                    guideSel.appendChild(option);
                }
            }
            document.getElementById('trip_price').value = parseInt(tripPrice).toLocaleString();
            onGuideChange();
        } else {
            document.getElementById('formRegulerFields').style.display = 'none';
            document.getElementById('guide_option_group').style.display = 'none';
        }
    }
    function onGuideOptionChange() {
        const withGuide = document.querySelector('input[name="with_guide"]:checked').value;
        if (withGuide == '1') {
            document.getElementById('guide_selector_group').style.display = '';
            document.getElementById('guide_price_group').style.display = '';
            document.getElementById('guide_total_group').style.display = '';
            onGuideChange();
        } else {
            document.getElementById('guide_selector_group').style.display = 'none';
            document.getElementById('guide_price_group').style.display = 'none';
            document.getElementById('guide_total_group').style.display = 'none';
            // Hitung total hanya harga tiket
            const tripPrice = parseInt(document.getElementById('trip_price').value.replace(/\D/g, '')) || 0;
            document.getElementById('total_price').value = tripPrice.toLocaleString();
            document.getElementById('trip_price_hidden').value = tripPrice;
            document.getElementById('addon_fee_hidden').value = 0;
        }
    }
    function onGuideChange() {
        const guideSel = document.getElementById('guide_selector');
        const fee = guideSel.options[guideSel.selectedIndex]?.getAttribute('data-fee') || 0;
        document.getElementById('guide_price').value = parseInt(fee).toLocaleString();
        onDateChange();
    }
    function onDateChange() {
        const start = document.getElementById('start_date').value;
        const end = document.getElementById('end_date').value;
        const fee = parseInt(document.getElementById('guide_price').value.replace(/\D/g, '')) || 0;
        const tripPrice = parseInt(document.getElementById('trip_price').value.replace(/\D/g, '')) || 0;
        let days = 1;
        if (start && end) {
            const d1 = new Date(start);
            const d2 = new Date(end);
            days = Math.max(1, Math.ceil((d2-d1)/(1000*60*60*24))+1);
        }
        const withGuide = document.querySelector('input[name="with_guide"]:checked').value;
        let totalGuide = 0;
        if (withGuide == '1') {
            totalGuide = fee * days;
            document.getElementById('guide_total').value = totalGuide.toLocaleString();
            document.getElementById('guide_price_group').style.display = '';
            document.getElementById('guide_total_group').style.display = '';
        } else {
            document.getElementById('guide_total').value = '';
            document.getElementById('guide_price_group').style.display = 'none';
            document.getElementById('guide_total_group').style.display = 'none';
        }
        document.getElementById('total_price').value = (tripPrice + totalGuide).toLocaleString();
        // Update hidden fields
        document.getElementById('trip_price_hidden').value = tripPrice;
        document.getElementById('addon_fee_hidden').value = totalGuide;
    }
    document.addEventListener('DOMContentLoaded', function() {
        onTripChange();
    });
    </script>
</body>
</html>


<?php
// Start the session
session_start();

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: ../auth/login.php');
    exit();
}

// Include database connection
require_once '../config/database.php';

// Get dashboard statistics
// Count active users
$users_query = "SELECT COUNT(*) as total FROM users WHERE role = 'user' AND active = 1";
$users_result = $conn->query($users_query);
$users_count = $users_result->fetch_assoc()['total'];

// Count active guides
$guides_query = "SELECT COUNT(*) as total FROM guides WHERE active = 1";
$guides_result = $conn->query($guides_query);
$guides_count = $guides_result->fetch_assoc()['total'];

// Count mountains
$mountains_query = "SELECT COUNT(*) as total FROM mountains";
$mountains_result = $conn->query($mountains_query);
$mountains_count = $mountains_result->fetch_assoc()['total'];

// Count active trips
$trips_query = "SELECT COUNT(*) as total FROM trips WHERE start_date >= CURDATE()";
$trips_result = $conn->query($trips_query);
$trips_count = $trips_result->fetch_assoc()['total'];

// Count pending bookings
$bookings_query = "SELECT COUNT(*) as total FROM bookings WHERE status = 'pending'";
$bookings_result = $conn->query($bookings_query);
$bookings_count = $bookings_result->fetch_assoc()['total'];

// Count unread feedback
$feedback_query = "SELECT COUNT(*) as total FROM feedbacks WHERE replied = 0";
$feedback_result = $conn->query($feedback_query);
$feedback_count = $feedback_result->fetch_assoc()['total'];

// Get recent bookings
$recent_bookings_query = "SELECT b.id, b.booking_date, b.participants, b.total_price, b.status, 
                        u.name as user_name, t.title as trip_title 
                        FROM bookings b 
                        JOIN users u ON b.user_id = u.id 
                        JOIN trips t ON b.trip_id = t.id 
                        ORDER BY b.booking_date DESC LIMIT 5";
$recent_bookings_result = $conn->query($recent_bookings_query);
$recent_bookings = [];

if ($recent_bookings_result->num_rows > 0) {
    while($row = $recent_bookings_result->fetch_assoc()) {
        $recent_bookings[] = $row;
    }
}

// Get upcoming trips
$upcoming_trips_query = "SELECT t.id, t.title, t.start_date, t.current_participants, t.max_participants, m.name as mountain_name 
                        FROM trips t 
                        JOIN mountains m ON t.mountain_id = m.id 
                        WHERE t.start_date >= CURDATE() 
                        ORDER BY t.start_date ASC LIMIT 5";
$upcoming_trips_result = $conn->query($upcoming_trips_query);
$upcoming_trips = [];

if ($upcoming_trips_result->num_rows > 0) {
    while($row = $upcoming_trips_result->fetch_assoc()) {
        $upcoming_trips[] = $row;
    }
}

// Include admin header
require_once 'includes/admin-header.php';
?>

<div class="admin-dashboard">
    <div class="dashboard-header">
        <h1>Dashboard Admin</h1>
        <p class="welcome-message">Selamat datang, <strong><?php echo $_SESSION['user_name']; ?></strong>!</p>
    </div>
    
    <div class="stats-cards">
        <div class="stat-card users">
            <div class="stat-icon"><i class="fas fa-users"></i></div>
            <div class="stat-content">
                <h3>Total Pendaki</h3>
                <p class="stat-number"><?php echo $users_count; ?></p>
            </div>
        </div>
        
        <div class="stat-card guides">
            <div class="stat-icon"><i class="fas fa-hiking"></i></div>
            <div class="stat-content">
                <h3>Total Guide</h3>
                <p class="stat-number"><?php echo $guides_count; ?></p>
            </div>
        </div>
        
        <div class="stat-card mountains">
            <div class="stat-icon"><i class="fas fa-mountain"></i></div>
            <div class="stat-content">
                <h3>Total Gunung</h3>
                <p class="stat-number"><?php echo $mountains_count; ?></p>
            </div>
        </div>
        
        <div class="stat-card trips">
            <div class="stat-icon"><i class="fas fa-route"></i></div>
            <div class="stat-content">
                <h3>Trip Aktif</h3>
                <p class="stat-number"><?php echo $trips_count; ?></p>
            </div>
        </div>
        
        <div class="stat-card bookings">
            <div class="stat-icon"><i class="fas fa-clipboard-list"></i></div>
            <div class="stat-content">
                <h3>Booking Pending</h3>
                <p class="stat-number"><?php echo $bookings_count; ?></p>
            </div>
        </div>
        
        <div class="stat-card feedback">
            <div class="stat-icon"><i class="fas fa-comment-dots"></i></div>
            <div class="stat-content">
                <h3>Feedback Baru</h3>
                <p class="stat-number"><?php echo $feedback_count; ?></p>
            </div>
        </div>
    </div>
    
    <div class="dashboard-content">
        <div class="recent-bookings">
            <div class="section-header">
                <h2>Booking Terbaru</h2>
                <a href="manage-bookings.php" class="view-all">Lihat Semua</a>
            </div>
            
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Pendaki</th>
                            <th>Trip</th>
                            <th>Tanggal Booking</th>
                            <th>Jumlah</th>
                            <th>Total</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($recent_bookings) > 0): ?>
                            <?php foreach ($recent_bookings as $booking): ?>
                                <tr>
                                    <td><?php echo $booking['id']; ?></td>
                                    <td><?php echo $booking['user_name']; ?></td>
                                    <td><?php echo $booking['trip_title']; ?></td>
                                    <td><?php echo date('d M Y', strtotime($booking['booking_date'])); ?></td>
                                    <td><?php echo $booking['participants']; ?> orang</td>
                                    <td>Rp <?php echo number_format($booking['total_price'], 0, ',', '.'); ?></td>
                                    <td>
                                        <span class="status-badge <?php echo $booking['status']; ?>">
                                            <?php 
                                            if ($booking['status'] == 'pending') echo 'Menunggu';
                                            else if ($booking['status'] == 'confirmed') echo 'Dikonfirmasi';
                                            else echo 'Dibatalkan';
                                            ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="booking-detail.php?id=<?php echo $booking['id']; ?>" class="action-btn view">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="no-data">Tidak ada booking terbaru.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="upcoming-trips">
            <div class="section-header">
                <h2>Trip Mendatang</h2>
                <a href="manage-trips.php" class="view-all">Lihat Semua</a>
            </div>
            
            <div class="data-table">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Judul</th>
                            <th>Gunung</th>
                            <th>Tanggal Mulai</th>
                            <th>Peserta</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (count($upcoming_trips) > 0): ?>
                            <?php foreach ($upcoming_trips as $trip): ?>
                                <tr>
                                    <td><?php echo $trip['id']; ?></td>
                                    <td><?php echo $trip['title']; ?></td>
                                    <td><?php echo $trip['mountain_name']; ?></td>
                                    <td><?php echo date('d M Y', strtotime($trip['start_date'])); ?></td>
                                    <td><?php echo $trip['current_participants']; ?>/<?php echo $trip['max_participants']; ?></td>
                                    <td>
                                        <a href="trip-detail.php?id=<?php echo $trip['id']; ?>" class="action-btn view">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="edit-trip.php?id=<?php echo $trip['id']; ?>" class="action-btn edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="no-data">Tidak ada trip mendatang.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once 'includes/admin-footer.php'; ?>

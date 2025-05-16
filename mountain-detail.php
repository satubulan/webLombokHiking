<?php
// Start the session
session_start();

// Include database connection
require_once 'config/database.php';

// Check if mountain ID is provided
if (!isset($_GET['id']) || empty($_GET['id'])) {
    header('Location: mountains.php');
    exit();
}

$mountain_id = mysqli_real_escape_string($conn, $_GET['id']);

// Get mountain details
$query = "SELECT * FROM mountains WHERE id = '$mountain_id'";
$result = $conn->query($query);

if ($result->num_rows == 0) {
    // For testing purposes, let's use a default image and data if no mountain is found in the database
    $mountain = [
        'name' => 'Gunung Rinjani',
        'height' => '3726',
        'difficulty' => 'Hard',
        'location' => 'Lombok Timur, Nusa Tenggara Barat',
        'estimated_time' => '3 hari',
        'distance' => '38 km',
        'image_url' => 'lombok_hiking_foto/rinjani.jpg',
        'description' => 'Gunung Rinjani adalah gunung tertinggi kedua di Indonesia dengan keindahan danau Segara Anak di kalderanya.',
        'category' => 'High Peak,Lake,Volcanic'
    ];
} else {
    $mountain = $result->fetch_assoc();
    // In case image_url is not set in the database, we'll use our local images
    if (strpos($mountain['name'], 'Rinjani') !== false) {
        $mountain['image_url'] = 'lombok_hiking_foto/rinjani.jpg';
    } elseif (strpos($mountain['name'], 'Pergasingan') !== false) {
        $mountain['image_url'] = 'lombok_hiking_foto/pergasingan.jpg';
    } elseif (strpos($mountain['name'], 'Anak Dara') !== false) {
        $mountain['image_url'] = 'lombok_hiking_foto/anakdara.jpg';
    } else {
        $mountain['image_url'] = 'lombok_hiking_foto/background.jpg';
    }
}

// Get available trips for this mountain
$trips_query = "SELECT * FROM trips WHERE mountain_id = '$mountain_id' AND start_date >= CURDATE() ORDER BY start_date ASC";
$trips_result = $conn->query($trips_query);
$trips = [];

if ($trips_result && $trips_result->num_rows > 0) {
    while($row = $trips_result->fetch_assoc()) {
        $trips[] = $row;
    }
}

// Include header
require_once 'includes/header.php';
?>

<section class="mountain-detail">
    <div class="mountain-hero" style="background-image: linear-gradient(rgba(0,0,0,0.4), rgba(0,0,0,0.4)), url('<?php echo $mountain['image_url']; ?>');">
        <div class="container">
            <h1><?php echo $mountain['name']; ?></h1>
            <div class="mountain-metadata">
                <span><i class="fas fa-mountain"></i> <?php echo $mountain['height']; ?>m</span>
                <span class="difficulty <?php echo strtolower($mountain['difficulty']); ?>">
                    <i class="fas fa-hiking"></i> <?php echo $mountain['difficulty']; ?>
                </span>
                <span><i class="fas fa-map-marker-alt"></i> <?php echo $mountain['location']; ?></span>
                <span><i class="fas fa-clock"></i> <?php echo $mountain['estimated_time']; ?></span>
                <span><i class="fas fa-route"></i> <?php echo $mountain['distance']; ?></span>
            </div>
        </div>
    </div>
    
    <div class="container">
        <div class="mountain-content">
            <div class="mountain-main">
                <div class="mountain-categories">
                    <?php
                    $categories = explode(',', $mountain['category']);
                    foreach($categories as $category) {
                        echo "<span class='category-badge'>" . trim($category) . "</span>";
                    }
                    ?>
                </div>
                
                <div class="content-section">
                    <h2>Tentang <?php echo $mountain['name']; ?></h2>
                    <p><?php echo nl2br($mountain['description']); ?></p>
                </div>
                
                <?php if (count($trips) > 0): ?>
                    <div class="content-section">
                        <h2>Open Trip Tersedia</h2>
                        <div class="trip-list">
                            <?php foreach($trips as $trip): ?>
                                <div class="trip-card">
                                    <div class="trip-image">
                                        <img src="<?php echo $trip['image_url']; ?>" alt="<?php echo $trip['title']; ?>">
                                    </div>
                                    <div class="trip-details">
                                        <h3><?php echo $trip['title']; ?></h3>
                                        <div class="trip-info">
                                            <span><i class="far fa-calendar-alt"></i> <?php echo date('d M Y', strtotime($trip['start_date'])); ?> - <?php echo date('d M Y', strtotime($trip['end_date'])); ?></span>
                                            <span><i class="far fa-clock"></i> <?php echo $trip['duration']; ?> hari</span>
                                            <span><i class="fas fa-users"></i> <?php echo $trip['current_participants']; ?>/<?php echo $trip['max_participants']; ?> peserta</span>
                                        </div>
                                        <p><?php echo substr($trip['description'], 0, 150); ?>...</p>
                                        <div class="trip-pricing">
                                            <div class="price">
                                                <span class="amount">Rp <?php echo number_format($trip['price'], 0, ',', '.'); ?></span>
                                                <span class="per-person">per orang</span>
                                            </div>
                                            <a href="trip-detail.php?id=<?php echo $trip['id']; ?>" class="btn btn-primary">Detail Trip</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="content-section">
                        <div class="no-trips">
                            <h2>Tidak Ada Open Trip</h2>
                            <p>Saat ini belum ada jadwal open trip untuk gunung ini. Silakan cek kembali nanti atau hubungi kami untuk informasi lebih lanjut.</p>
                            <a href="contact.php" class="btn btn-secondary">Hubungi Kami</a>
                        </div>
                    </div>
                <?php endif; ?>
                
                <div class="content-section">
                    <h2>Lokasi</h2>
                    <div class="map-container">
                        <!-- Placeholder for map -->
                        <div class="map-placeholder">
                            <iframe 
                                width="100%" 
                                height="450" 
                                frameborder="0" 
                                style="border:0" 
                                src="https://www.google.com/maps/embed/v1/place?key=YOUR_API_KEY&q=<?php echo urlencode($mountain['name'] . ' ' . $mountain['location']); ?>" 
                                allowfullscreen>
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="mountain-sidebar">
                <div class="sidebar-widget booking-widget">
                    <h3>Tertarik Mendaki?</h3>
                    <p>Lihat jadwal open trip untuk gunung ini atau hubungi kami untuk trip private.</p>
                    <a href="#available-trips" class="btn btn-primary btn-block">Lihat Jadwal Trip</a>
                    <div class="divider">atau</div>
                    <a href="contact.php?subject=Private Trip ke <?php echo urlencode($mountain['name']); ?>" class="btn btn-secondary btn-block">Tanya Trip Private</a>
                </div>
                
                <div class="sidebar-widget info-widget">
                    <h3>Informasi Gunung</h3>
                    <ul class="info-list">
                        <li>
                            <span class="info-label">Ketinggian</span>
                            <span class="info-value"><?php echo $mountain['height']; ?> mdpl</span>
                        </li>
                        <li>
                            <span class="info-label">Lokasi</span>
                            <span class="info-value"><?php echo $mountain['location']; ?></span>
                        </li>
                        <li>
                            <span class="info-label">Kesulitan</span>
                            <span class="info-value difficulty-badge <?php echo strtolower($mountain['difficulty']); ?>"><?php echo $mountain['difficulty']; ?></span>
                        </li>
                        <li>
                            <span class="info-label">Estimasi Waktu</span>
                            <span class="info-value"><?php echo $mountain['estimated_time']; ?></span>
                        </li>
                        <li>
                            <span class="info-label">Jarak Tempuh</span>
                            <span class="info-value"><?php echo $mountain['distance']; ?></span>
                        </li>
                    </ul>
                </div>
                
                <div class="sidebar-widget">
                    <h3>Bagikan</h3>
                    <div class="social-share">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?php echo urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>" target="_blank" class="social-button facebook"><i class="fab fa-facebook-f"></i></a>
                        <a href="https://twitter.com/intent/tweet?url=<?php echo urlencode("http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>&text=<?php echo urlencode("Mari mendaki " . $mountain['name'] . " bersama LombokHiking!"); ?>" target="_blank" class="social-button twitter"><i class="fab fa-twitter"></i></a>
                        <a href="https://wa.me/?text=<?php echo urlencode("Mari mendaki " . $mountain['name'] . " bersama LombokHiking! " . "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>" target="_blank" class="social-button whatsapp"><i class="fab fa-whatsapp"></i></a>
                        <a href="mailto:?subject=<?php echo urlencode("Info pendakian " . $mountain['name']); ?>&body=<?php echo urlencode("Lihat info pendakian " . $mountain['name'] . " di LombokHiking: http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]"); ?>" class="social-button email"><i class="fas fa-envelope"></i></a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<style>
    /* Mountain Detail Specific Styles */
    .mountain-hero {
        background-size: cover;
        background-position: center;
        color: white;
        padding: 100px 0;
        position: relative;
        margin-bottom: 40px;
    }
    
    .mountain-hero h1 {
        font-size: 3rem;
        margin-bottom: 15px;
        text-shadow: 1px 1px 3px rgba(0,0,0,0.5);
    }
    
    .mountain-metadata {
        display: flex;
        flex-wrap: wrap;
        gap: 20px;
    }
    
    .mountain-metadata span {
        display: flex;
        align-items: center;
        font-size: 1.1rem;
        text-shadow: 1px 1px 2px rgba(0,0,0,0.5);
    }
    
    .mountain-metadata i {
        margin-right: 8px;
    }
    
    .mountain-metadata .difficulty {
        padding: 3px 10px;
        border-radius: 20px;
    }
    
    .mountain-metadata .difficulty.easy {
        background-color: var(--easy-color);
    }
    
    .mountain-metadata .difficulty.moderate {
        background-color: var(--moderate-color);
    }
    
    .mountain-metadata .difficulty.hard {
        background-color: var(--hard-color);
    }
    
    .mountain-metadata .difficulty.expert {
        background-color: var(--expert-color);
    }
    
    .mountain-content {
        display: grid;
        grid-template-columns: 2fr 1fr;
        gap: 30px;
    }
    
    .mountain-categories {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-bottom: 20px;
    }
    
    .category-badge {
        background-color: var(--light-bg);
        color: var(--text-color);
        padding: 5px 15px;
        border-radius: 20px;
        font-weight: 500;
        font-size: 0.9rem;
    }
    
    .content-section {
        margin-bottom: 40px;
    }
    
    .content-section h2 {
        font-size: 1.8rem;
        margin-bottom: 20px;
        position: relative;
        padding-bottom: 10px;
    }
    
    .content-section h2:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 50px;
        height: 3px;
        background-color: var(--primary-color);
    }
    
    .trip-list {
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    
    .trip-card {
        display: flex;
        background-color: white;
        border-radius: 8px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    
    .trip-image {
        width: 30%;
    }
    
    .trip-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }
    
    .trip-details {
        width: 70%;
        padding: 20px;
    }
    
    .trip-details h3 {
        font-size: 1.3rem;
        margin-bottom: 10px;
    }
    
    .trip-info {
        display: flex;
        flex-wrap: wrap;
        gap: 15px;
        margin-bottom: 15px;
        font-size: 0.9rem;
        color: var(--light-text);
    }
    
    .trip-info span i {
        margin-right: 5px;
    }
    
    .trip-pricing {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 15px;
    }
    
    .price .amount {
        font-size: 1.4rem;
        font-weight: 600;
        color: var(--primary-color);
    }
    
    .price .per-person {
        font-size: 0.8rem;
        color: var(--light-text);
    }
    
    .no-trips {
        background-color: #f8f9fa;
        padding: 30px;
        border-radius: 8px;
        text-align: center;
    }
    
    .no-trips h2 {
        margin-bottom: 15px;
    }
    
    .no-trips p {
        margin-bottom: 20px;
    }
    
    .map-container {
        height: 400px;
        border-radius: 8px;
        overflow: hidden;
    }
    
    .sidebar-widget {
        background-color: white;
        border-radius: 8px;
        padding: 25px;
        margin-bottom: 30px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.08);
    }
    
    .sidebar-widget h3 {
        font-size: 1.3rem;
        margin-bottom: 15px;
        position: relative;
        padding-bottom: 10px;
    }
    
    .sidebar-widget h3:after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 40px;
        height: 2px;
        background-color: var(--primary-color);
    }
    
    .btn-block {
        display: block;
        width: 100%;
        text-align: center;
    }
    
    .divider {
        text-align: center;
        margin: 15px 0;
        color: var(--light-text);
        position: relative;
    }
    
    .divider:before,
    .divider:after {
        content: '';
        position: absolute;
        top: 50%;
        width: 40%;
        height: 1px;
        background-color: var(--border-color);
    }
    
    .divider:before {
        left: 0;
    }
    
    .divider:after {
        right: 0;
    }
    
    .info-list {
        list-style: none;
        padding: 0;
    }
    
    .info-list li {
        display: flex;
        justify-content: space-between;
        padding: 12px 0;
        border-bottom: 1px solid var(--border-color);
    }
    
    .info-list li:last-child {
        border-bottom: none;
    }
    
    .info-label {
        color: var(--light-text);
    }
    
    .social-share {
        display: flex;
        gap: 10px;
    }
    
    .social-button {
        display: flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        color: white;
        text-decoration: none;
        transition: all 0.3s ease;
    }
    
    .social-button.facebook {
        background-color: #3b5998;
    }
    
    .social-button.twitter {
        background-color: #1da1f2;
    }
    
    .social-button.whatsapp {
        background-color: #25d366;
    }
    
    .social-button.email {
        background-color: #ea4335;
    }
    
    .social-button:hover {
        transform: translateY(-3px);
        box-shadow: 0 5px 10px rgba(0,0,0,0.1);
    }
    
    /* Responsive Design */
    @media screen and (max-width: 991px) {
        .mountain-content {
            grid-template-columns: 1fr;
        }
        
        .mountain-sidebar {
            order: -1;
        }
    }
    
    @media screen and (max-width: 767px) {
        .mountain-hero {
            padding: 50px 0;
        }
        
        .mountain-hero h1 {
            font-size: 2.3rem;
        }
        
        .trip-card {
            flex-direction: column;
        }
        
        .trip-image {
            width: 100%;
            height: 200px;
        }
        
        .trip-details {
            width: 100%;
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const bookingBtn = document.querySelector('.booking-widget .btn-primary');
        
        if (bookingBtn) {
            bookingBtn.addEventListener('click', function(e) {
                e.preventDefault();
                const targetSection = document.querySelector('.content-section:nth-child(2)');
                if (targetSection) {
                    window.scrollTo({
                        top: targetSection.offsetTop - 80,
                        behavior: 'smooth'
                    });
                }
            });
        }
    });
</script>
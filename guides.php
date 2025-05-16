
<?php
// Start the session
session_start();

// Include database connection
require_once 'config/database.php';

// Get all active guides from database
$query = "SELECT * FROM guides WHERE active = 1 ORDER BY experience DESC";
$result = $conn->query($query);
$guides = [];

if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $guides[] = $row;
    }
}

// Include header
require_once 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1 class="page-title">Guide Pendakian</h1>
        <nav class="breadcrumb">
            <a href="index.php">Beranda</a> / <span>Guide</span>
        </nav>
    </div>
</section>

<section class="guides-section">
    <div class="container">
        <div class="section-intro">
            <h2>Guide Profesional & Berpengalaman</h2>
            <p>Semua guide LombokHiking adalah penduduk lokal yang sangat mengenal medan pendakian di Lombok. Mereka telah melalui pelatihan dan memiliki sertifikasi keselamatan serta P3K.</p>
        </div>
        
        <div class="guide-cards">
            <?php if (count($guides) > 0): ?>
                <?php foreach ($guides as $guide): ?>
                    <div class="guide-card card">
                        <div class="guide-image">
                            <img src="<?php echo $guide['image_url']; ?>" alt="<?php echo $guide['name']; ?>">
                        </div>
                        <div class="guide-content">
                            <h3 class="guide-name"><?php echo $guide['name']; ?></h3>
                            <div class="guide-rating">
                                <?php
                                for ($i = 1; $i <= 5; $i++) {
                                    if ($i <= $guide['rating']) {
                                        echo '<i class="fas fa-star"></i>';
                                    } else if ($i - 0.5 <= $guide['rating']) {
                                        echo '<i class="fas fa-star-half-alt"></i>';
                                    } else {
                                        echo '<i class="far fa-star"></i>';
                                    }
                                }
                                ?>
                                <span class="rating-text"><?php echo $guide['rating']; ?>/5</span>
                            </div>
                            <p class="guide-experience"><i class="fas fa-award"></i> <?php echo $guide['experience']; ?> tahun pengalaman</p>
                            <div class="guide-specialization">
                                <?php
                                $specializations = explode(',', $guide['specialization']);
                                foreach($specializations as $spec) {
                                    echo "<span class='spec-tag'>" . trim($spec) . "</span>";
                                }
                                ?>
                            </div>
                            <p class="guide-languages"><i class="fas fa-language"></i> <?php echo $guide['languages']; ?></p>
                            <a href="guide-detail.php?id=<?php echo $guide['id']; ?>" class="btn btn-secondary">Profil Lengkap</a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">
                    <p>Tidak ada guide yang tersedia saat ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<section class="become-guide">
    <div class="container">
        <div class="cta-content">
            <h2>Ingin Menjadi Guide di LombokHiking?</h2>
            <p>Jika Anda adalah pendaki berpengalaman dan memiliki pengetahuan mendalam tentang gunung-gunung di Lombok, bergabunglah dengan tim kami.</p>
            <a href="contact.php?subject=Menjadi%20Guide" class="btn btn-primary">Hubungi Kami</a>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<style>
    .section-intro {
        text-align: center;
        max-width: 800px;
        margin: 0 auto 40px;
    }
    
    .section-intro h2 {
        margin-bottom: 15px;
    }
    
    .guide-cards {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
    }
    
    .guide-card {
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }
    
    .guide-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0,0,0,0.1);
    }
    
    .guide-image {
        height: 200px;
        overflow: hidden;
    }
    
    .guide-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }
    
    .guide-card:hover .guide-image img {
        transform: scale(1.05);
    }
    
    .guide-content {
        padding: 20px;
    }
    
    .guide-name {
        font-size: 1.4rem;
        margin-bottom: 10px;
    }
    
    .guide-rating {
        color: #f39c12;
        margin-bottom: 10px;
        display: flex;
        align-items: center;
    }
    
    .rating-text {
        margin-left: 8px;
        color: var(--text-color);
    }
    
    .guide-experience {
        color: var(--text-color);
        margin-bottom: 15px;
    }
    
    .guide-specialization {
        display: flex;
        flex-wrap: wrap;
        gap: 5px;
        margin-bottom: 15px;
    }
    
    .spec-tag {
        background-color: #f0f0f0;
        color: var(--text-color);
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.85rem;
    }
    
    .guide-languages {
        color: var(--text-color);
        margin-bottom: 15px;
        font-size: 0.9rem;
    }
    
    .become-guide {
        background-color: var(--light-bg);
        padding: 60px 0;
        margin-top: 40px;
    }
    
    .cta-content {
        text-align: center;
        max-width: 700px;
        margin: 0 auto;
    }
    
    .cta-content h2 {
        margin-bottom: 15px;
    }
    
    .cta-content p {
        margin-bottom: 25px;
    }
    
    .no-results {
        text-align: center;
        padding: 30px;
        background-color: var(--light-bg);
        border-radius: 8px;
        grid-column: 1 / -1;
    }
    
    @media screen and (max-width: 767px) {
        .guide-cards {
            grid-template-columns: 1fr;
        }
    }
</style>

<?php
// Start the session
session_start();

// Include database connection
require_once 'config/database.php';

// Get all mountains from database
$query = "SELECT * FROM mountains ORDER BY name ASC";

// Check if filter is applied
if (isset($_GET['difficulty']) && !empty($_GET['difficulty'])) {
    $difficulty = mysqli_real_escape_string($conn, $_GET['difficulty']);
    $query = "SELECT * FROM mountains WHERE difficulty = '$difficulty' ORDER BY name ASC";
}

if (isset($_GET['category']) && !empty($_GET['category'])) {
    $category = mysqli_real_escape_string($conn, $_GET['category']);
    $query = "SELECT * FROM mountains WHERE category LIKE '%$category%' ORDER BY name ASC";
}

$result = $conn->query($query);
$mountains = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $mountains[] = $row;
    }
} else {
    // If no database data is found, create sample data with your images
    $mountains = [
        [
            'id' => 'm1',
            'name' => 'Gunung Rinjani',
            'height' => '3726',
            'difficulty' => 'Hard',
            'location' => 'Lombok Timur, Nusa Tenggara Barat',
            'description' => 'Gunung Rinjani adalah gunung tertinggi kedua di Indonesia dengan keindahan danau Segara Anak di kalderanya.',
            'image_url' => 'lombok_hiking_foto/rinjani.jpg',
            'category' => 'High Peak,Lake,Volcanic'
        ],
        [
            'id' => 'm2',
            'name' => 'Bukit Pergasingan',
            'height' => '1700',
            'difficulty' => 'Easy',
            'location' => 'Sembalun, Lombok Timur',
            'description' => 'Bukit Pergasingan menawarkan pemandangan padang savana yang luas dengan latar belakang Gunung Rinjani.',
            'image_url' => 'lombok_hiking_foto/pergasingan.jpg',
            'category' => 'Beginner Friendly,Savanna'
        ],
        [
            'id' => 'm3',
            'name' => 'Gunung Anak Dara',
            'height' => '2100',
            'difficulty' => 'Moderate',
            'location' => 'Sembalun, Lombok Timur',
            'description' => 'Gunung Anak Dara menawarkan pemandangan padang rumput yang luas dan pemandangan Gunung Rinjani yang memukau.',
            'image_url' => 'lombok_hiking_foto/anakdara.jpg',
            'category' => 'Savanna,Beginner Friendly'
        ],
        [
            'id' => 'm4',
            'name' => 'Bukit Selong',
            'height' => '600',
            'difficulty' => 'Easy',
            'location' => 'Sembalun, Lombok Timur',
            'description' => 'Bukit Selong menawarkan panorama persawahan berbentuk terasering yang memukau.',
            'image_url' => 'lombok_hiking_foto/bukitselong.jpg', 
            'category' => 'Beginner Friendly,Savanna'
        ]
    ];
}

// Include header
require_once 'includes/header.php';
?>

<section class="page-header">
    <div class="container">
        <h1 class="page-title">Daftar Gunung di Lombok</h1>
        <nav class="breadcrumb">
            <a href="index.php">Beranda</a> / <span>Daftar Gunung</span>
        </nav>
    </div>
</section>

<section class="mountains-list">
    <div class="container">
        <div class="filter-bar">
            <div class="filter-group">
                <label>Filter berdasarkan:</label>
                <div class="filter-options">
                    <select id="difficultyFilter" onchange="applyFilter()">
                        <option value="">Tingkat Kesulitan</option>
                        <option value="Easy" <?php echo isset($_GET['difficulty']) && $_GET['difficulty'] == 'Easy' ? 'selected' : ''; ?>>Mudah</option>
                        <option value="Moderate" <?php echo isset($_GET['difficulty']) && $_GET['difficulty'] == 'Moderate' ? 'selected' : ''; ?>>Sedang</option>
                        <option value="Hard" <?php echo isset($_GET['difficulty']) && $_GET['difficulty'] == 'Hard' ? 'selected' : ''; ?>>Sulit</option>
                        <option value="Expert" <?php echo isset($_GET['difficulty']) && $_GET['difficulty'] == 'Expert' ? 'selected' : ''; ?>>Ekstrem</option>
                    </select>
                    
                    <select id="categoryFilter" onchange="applyFilter()">
                        <option value="">Kategori</option>
                        <option value="High Peak" <?php echo isset($_GET['category']) && $_GET['category'] == 'High Peak' ? 'selected' : ''; ?>>Puncak Tinggi</option>
                        <option value="Savanna" <?php echo isset($_GET['category']) && $_GET['category'] == 'Savanna' ? 'selected' : ''; ?>>Padang Savana</option>
                        <option value="Beginner Friendly" <?php echo isset($_GET['category']) && $_GET['category'] == 'Beginner Friendly' ? 'selected' : ''; ?>>Cocok untuk Pemula</option>
                        <option value="Waterfall" <?php echo isset($_GET['category']) && $_GET['category'] == 'Waterfall' ? 'selected' : ''; ?>>Air Terjun</option>
                        <option value="Lake" <?php echo isset($_GET['category']) && $_GET['category'] == 'Lake' ? 'selected' : ''; ?>>Danau</option>
                        <option value="Volcanic" <?php echo isset($_GET['category']) && $_GET['category'] == 'Volcanic' ? 'selected' : ''; ?>>Vulkanik</option>
                    </select>
                </div>
            </div>
            <div class="search-container">
                <form action="mountains.php" method="GET">
                    <input type="text" name="search" placeholder="Cari gunung..." value="<?php echo isset($_GET['search']) ? htmlspecialchars($_GET['search']) : ''; ?>">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </div>
        
        <div class="mountain-cards">
            <?php if (count($mountains) > 0): ?>
                <?php foreach ($mountains as $mountain): ?>
                    <div class="card mountain-card">
                        <div class="card-image">
                            <img src="<?php echo $mountain['image_url']; ?>" alt="<?php echo $mountain['name']; ?>">
                            <span class="height-badge"><?php echo $mountain['height']; ?>m</span>
                            <span class="difficulty-badge <?php echo strtolower($mountain['difficulty']); ?>"><?php echo $mountain['difficulty']; ?></span>
                        </div>
                        <div class="card-content">
                            <h3 class="card-title"><?php echo $mountain['name']; ?></h3>
                            <p class="location"><i class="fas fa-map-marker-alt"></i> <?php echo $mountain['location']; ?></p>
                            <p class="description"><?php echo substr($mountain['description'], 0, 100); ?>...</p>
                            <div class="card-footer">
                                <div class="categories">
                                    <?php
                                    $categories = explode(',', $mountain['category']);
                                    foreach($categories as $category) {
                                        echo "<span class='category-tag'>" . trim($category) . "</span>";
                                    }
                                    ?>
                                </div>
                                <a href="mountain-detail.php?id=<?php echo $mountain['id']; ?>" class="btn btn-primary">Detail</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="no-results">
                    <p>Tidak ada gunung yang ditemukan.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'includes/footer.php'; ?>

<script>
    function applyFilter() {
        const difficultySelect = document.getElementById('difficultyFilter');
        const categorySelect = document.getElementById('categoryFilter');
        const difficulty = difficultySelect.value;
        const category = categorySelect.value;
        
        let url = 'mountains.php';
        const params = [];
        
        if (difficulty) {
            params.push(`difficulty=${difficulty}`);
        }
        
        if (category) {
            params.push(`category=${category}`);
        }
        
        if (params.length > 0) {
            url += '?' + params.join('&');
        }
        
        window.location.href = url;
    }
</script>
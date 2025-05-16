<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>LombokHiking - Explore Mountains in Lombok</title>
    <!-- CSS Styles with relative path -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="site-header">
        <div class="container">
            <div class="header-wrapper">
                <a href="index.php" class="logo">
                    <h1>LombokHiking</h1>
                </a>
                <nav class="main-nav">
                    <ul class="nav-links">
                        <li><a href="index.php">Beranda</a></li>
                        <li><a href="mountains.php">Daftar Gunung</a></li>
                        <li><a href="categories.php">Kategori</a></li>
                        <li><a href="guides.php">Guide</a></li>
                        <li><a href="contact.php">Kontak</a></li>
                    </ul>
                </nav>
                <div class="nav-actions">
                    <div class="search-container">
                        <form action="search.php" method="GET">
                            <input type="text" name="query" placeholder="Cari...">
                            <button type="submit"><i class="fas fa-search"></i></button>
                        </form>
                    </div>
                    <div class="auth-buttons">
                        <?php if(isset($_SESSION['user_id'])): ?>
                            <a href="user/dashboard.php" class="btn btn-secondary">Dashboard</a>
                            <a href="auth/logout.php" class="btn btn-primary">Logout</a>
                        <?php else: ?>
                            <a href="auth/login.php" class="btn btn-secondary">Login</a>
                            <a href="auth/register.php" class="btn btn-primary">Register</a>
                        <?php endif; ?>
                    </div>
                    <div class="mobile-menu-toggle">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        </div>
    </header>
    <main>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - LombokHiking</title>
    <!-- CSS Styles -->
    <link rel="stylesheet" href="../assets/css/styles.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --sidebar-width: 250px;
            --sidebar-collapsed: 70px;
            --header-height: 60px;
            --sidebar-bg: #1A1F2C;
            --admin-primary: #3c9e64;
            --admin-secondary: #f39c12;
            --admin-light: #f0f2f5;
            --admin-text: #333;
            --admin-light-text: #888;
            --admin-border: #e0e0e0;
            --admin-danger: #f44336;
            --admin-warning: #ff9800;
            --admin-success: #4caf50;
            --admin-info: #2196f3;
        }
        
        /* Admin Layout */
        body {
            background-color: var(--admin-light);
            color: var(--admin-text);
        }
        
        .admin-layout {
            display: grid;
            grid-template-columns: var(--sidebar-width) 1fr;
            grid-template-rows: var(--header-height) 1fr;
            grid-template-areas:
                "sidebar header"
                "sidebar main";
            height: 100vh;
        }
        
        .admin-layout.collapsed {
            grid-template-columns: var(--sidebar-collapsed) 1fr;
        }
        
        .admin-sidebar {
            grid-area: sidebar;
            background-color: var(--sidebar-bg);
            color: white;
            position: fixed;
            top: 0;
            left: 0;
            width: var(--sidebar-width);
            height: 100%;
            z-index: 100;
            overflow-y: auto;
            transition: width 0.3s ease;
        }
        
        .admin-layout.collapsed .admin-sidebar {
            width: var(--sidebar-collapsed);
        }
        
        .admin-header {
            grid-area: header;
            background-color: white;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
            position: fixed;
            top: 0;
            left: var(--sidebar-width);
            right: 0;
            height: var(--header-height);
            z-index: 90;
            padding: 0 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            transition: left 0.3s ease;
        }
        
        .admin-layout.collapsed .admin-header {
            left: var(--sidebar-collapsed);
        }
        
        .admin-main {
            grid-area: main;
            margin-top: var(--header-height);
            padding: 20px;
            overflow-y: auto;
        }
        
        /* Admin Sidebar */
        .sidebar-header {
            display: flex;
            align-items: center;
            padding: 20px 15px;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .logo-icon {
            font-size: 1.8rem;
            color: var(--admin-primary);
        }
        
        .logo-text {
            margin-left: 10px;
            font-size: 1.4rem;
            font-weight: 600;
            overflow: hidden;
            transition: opacity 0.3s ease;
        }
        
        .admin-layout.collapsed .logo-text {
            opacity: 0;
            width: 0;
        }
        
        .sidebar-toggle {
            margin-left: auto;
            background: none;
            border: none;
            color: white;
            cursor: pointer;
            font-size: 1.2rem;
            padding: 5px;
        }
        
        .admin-layout.collapsed .sidebar-toggle {
            margin-left: 0;
        }
        
        .admin-nav {
            padding: 15px 0;
        }
        
        .nav-section-title {
            padding: 10px 15px;
            font-size: 0.8rem;
            text-transform: uppercase;
            color: rgba(255,255,255,0.6);
            overflow: hidden;
            transition: opacity 0.3s ease;
        }
        
        .admin-layout.collapsed .nav-section-title {
            opacity: 0;
            height: 0;
            padding: 0;
        }
        
        .nav-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .nav-item {
            margin: 5px 0;
        }
        
        .nav-link {
            display: flex;
            align-items: center;
            padding: 10px 15px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s ease;
            border-radius: 5px;
            margin: 0 10px;
        }
        
        .nav-link:hover, .nav-link.active {
            background-color: rgba(255,255,255,0.1);
            color: white;
        }
        
        .nav-icon {
            font-size: 1.1rem;
            width: 25px;
            text-align: center;
        }
        
        .nav-text {
            margin-left: 10px;
            overflow: hidden;
            transition: opacity 0.3s ease;
        }
        
        .admin-layout.collapsed .nav-text {
            opacity: 0;
            width: 0;
        }
        
        /* Admin Header */
        .toggle-menu {
            display: none;
            background: none;
            border: none;
            font-size: 1.2rem;
            cursor: pointer;
        }
        
        .search-form {
            flex: 1;
            margin: 0 20px;
            position: relative;
        }
        
        .search-form input {
            width: 100%;
            max-width: 400px;
            padding: 8px 15px;
            border-radius: 20px;
            border: 1px solid var(--admin-border);
            outline: none;
        }
        
        .search-form button {
            position: absolute;
            top: 0;
            right: 0;
            height: 100%;
            background: none;
            border: none;
            padding: 0 15px;
            cursor: pointer;
            color: var(--admin-light-text);
        }
        
        .header-actions {
            display: flex;
            align-items: center;
        }
        
        .notification-button {
            position: relative;
            background: none;
            border: none;
            font-size: 1.2rem;
            margin-right: 20px;
            cursor: pointer;
            color: var(--admin-text);
        }
        
        .notification-badge {
            position: absolute;
            top: -5px;
            right: -5px;
            background-color: var(--admin-danger);
            color: white;
            width: 18px;
            height: 18px;
            border-radius: 50%;
            font-size: 0.7rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .user-menu {
            display: flex;
            align-items: center;
            cursor: pointer;
            position: relative;
        }
        
        .user-avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            background-color: var(--admin-primary);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
            margin-right: 10px;
        }
        
        .user-name {
            font-weight: 500;
        }
        
        .user-role {
            font-size: 0.8rem;
            color: var(--admin-light-text);
        }
        
        .dropdown-icon {
            margin-left: 10px;
        }
        
        /* Dashboard Styles */
        .admin-dashboard {
            padding: 10px 0;
        }
        
        .dashboard-header {
            margin-bottom: 30px;
        }
        
        .dashboard-header h1 {
            font-size: 1.8rem;
            margin-bottom: 5px;
        }
        
        .welcome-message {
            color: var(--admin-light-text);
        }
        
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 20px;
            display: flex;
            align-items: center;
        }
        
        .stat-icon {
            width: 50px;
            height: 50px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            margin-right: 15px;
        }
        
        .stat-content {
            flex: 1;
        }
        
        .stat-content h3 {
            font-size: 1rem;
            margin-bottom: 5px;
            color: var(--admin-light-text);
        }
        
        .stat-number {
            font-size: 1.8rem;
            font-weight: 600;
            margin: 0;
        }
        
        .stat-card.users .stat-icon {
            background-color: rgba(33, 150, 243, 0.1);
            color: #2196f3;
        }
        
        .stat-card.guides .stat-icon {
            background-color: rgba(76, 175, 80, 0.1);
            color: #4caf50;
        }
        
        .stat-card.mountains .stat-icon {
            background-color: rgba(156, 39, 176, 0.1);
            color: #9c27b0;
        }
        
        .stat-card.trips .stat-icon {
            background-color: rgba(255, 152, 0, 0.1);
            color: #ff9800;
        }
        
        .stat-card.bookings .stat-icon {
            background-color: rgba(244, 67, 54, 0.1);
            color: #f44336;
        }
        
        .stat-card.feedback .stat-icon {
            background-color: rgba(0, 188, 212, 0.1);
            color: #00bcd4;
        }
        
        .dashboard-content {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        .recent-bookings, .upcoming-trips {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
            padding: 20px;
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--admin-border);
        }
        
        .section-header h2 {
            font-size: 1.3rem;
            margin: 0;
        }
        
        .view-all {
            color: var(--admin-primary);
            text-decoration: none;
        }
        
        .view-all:hover {
            text-decoration: underline;
        }
        
        .data-table {
            overflow-x: auto;
        }
        
        .data-table table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .data-table th, .data-table td {
            padding: 10px;
            text-align: left;
            border-bottom: 1px solid var(--admin-border);
        }
        
        .data-table th {
            font-weight: 600;
            color: var(--admin-light-text);
        }
        
        .status-badge {
            padding: 5px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }
        
        .status-badge.pending {
            background-color: rgba(255, 152, 0, 0.1);
            color: #ff9800;
        }
        
        .status-badge.confirmed {
            background-color: rgba(76, 175, 80, 0.1);
            color: #4caf50;
        }
        
        .status-badge.cancelled {
            background-color: rgba(244, 67, 54, 0.1);
            color: #f44336;
        }
        
        .action-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 30px;
            height: 30px;
            border-radius: 4px;
            margin-right: 5px;
            color: white;
            text-decoration: none;
        }
        
        .action-btn.view {
            background-color: var(--admin-info);
        }
        
        .action-btn.edit {
            background-color: var(--admin-warning);
        }
        
        .action-btn.delete {
            background-color: var(--admin-danger);
        }
        
        .no-data {
            text-align: center;
            padding: 20px;
            color: var(--admin-light-text);
        }
        
        /* Responsive Design */
        @media screen and (max-width: 991px) {
            .admin-layout {
                grid-template-columns: 0 1fr;
            }
            
            .admin-sidebar {
                left: -250px;
            }
            
            .admin-layout.mobile-open .admin-sidebar {
                left: 0;
            }
            
            .admin-header {
                left: 0;
            }
            
            .toggle-menu {
                display: block;
            }
            
            .sidebar-toggle {
                display: none;
            }
            
            .dashboard-content {
                grid-template-columns: 1fr;
            }
        }
        
        @media screen and (max-width: 767px) {
            .stats-cards {
                grid-template-columns: 1fr 1fr;
            }
            
            .search-form {
                display: none;
            }
        }
        
        @media screen and (max-width: 576px) {
            .stats-cards {
                grid-template-columns: 1fr;
            }
            
            .user-name, .user-role {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="admin-layout">
        <aside class="admin-sidebar">
            <div class="sidebar-header">
                <div class="logo-icon"><i class="fas fa-mountain"></i></div>
                <div class="logo-text">LombokHiking</div>
                <button class="sidebar-toggle"><i class="fas fa-bars"></i></button>
            </div>
            
            <nav class="admin-nav">
                <div class="nav-section-title">Menu</div>
                <ul class="nav-links">
                    <li class="nav-item">
                        <a href="dashboard.php" class="nav-link active">
                            <span class="nav-icon"><i class="fas fa-tachometer-alt"></i></span>
                            <span class="nav-text">Dashboard</span>
                        </a>
                    </li>
                </ul>
                
                <div class="nav-section-title">Manajemen</div>
                <ul class="nav-links">
                    <li class="nav-item">
                        <a href="manage-users.php" class="nav-link">
                            <span class="nav-icon"><i class="fas fa-users"></i></span>
                            <span class="nav-text">Kelola Pendaki</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="manage-guides.php" class="nav-link">
                            <span class="nav-icon"><i class="fas fa-hiking"></i></span>
                            <span class="nav-text">Kelola Guide</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="manage-mountains.php" class="nav-link">
                            <span class="nav-icon"><i class="fas fa-mountain"></i></span>
                            <span class="nav-text">Kelola Gunung</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="manage-trips.php" class="nav-link">
                            <span class="nav-icon"><i class="fas fa-route"></i></span>
                            <span class="nav-text">Kelola Trip</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="manage-bookings.php" class="nav-link">
                            <span class="nav-icon"><i class="fas fa-clipboard-list"></i></span>
                            <span class="nav-text">Kelola Booking</span>
                        </a>
                    </li>
                </ul>
                
                <div class="nav-section-title">Komunikasi</div>
                <ul class="nav-links">
                    <li class="nav-item">
                        <a href="manage-feedback.php" class="nav-link">
                            <span class="nav-icon"><i class="fas fa-comment-dots"></i></span>
                            <span class="nav-text">Kelola Feedback</span>
                        </a>
                    </li>
                </ul>
                
                <div class="nav-section-title">Akun</div>
                <ul class="nav-links">
                    <li class="nav-item">
                        <a href="profile.php" class="nav-link">
                            <span class="nav-icon"><i class="fas fa-user-cog"></i></span>
                            <span class="nav-text">Profil</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="../auth/logout.php" class="nav-link">
                            <span class="nav-icon"><i class="fas fa-sign-out-alt"></i></span>
                            <span class="nav-text">Keluar</span>
                        </a>
                    </li>
                </ul>
            </nav>
        </aside>
        
        <header class="admin-header">
            <button class="toggle-menu"><i class="fas fa-bars"></i></button>
            
            <form class="search-form">
                <input type="text" placeholder="Cari...">
                <button type="submit"><i class="fas fa-search"></i></button>
            </form>
            
            <div class="header-actions">
                <button class="notification-button">
                    <i class="fas fa-bell"></i>
                    <?php if ($feedback_count > 0): ?>
                        <span class="notification-badge"><?php echo $feedback_count; ?></span>
                    <?php endif; ?>
                </button>
                
                <div class="user-menu">
                    <div class="user-avatar"><?php echo substr($_SESSION['user_name'], 0, 1); ?></div>
                    <div class="user-info">
                        <div class="user-name"><?php echo $_SESSION['user_name']; ?></div>
                        <div class="user-role">Administrator</div>
                    </div>
                    <div class="dropdown-icon"><i class="fas fa-chevron-down"></i></div>
                </div>
            </div>
        </header>
        
        <main class="admin-main">

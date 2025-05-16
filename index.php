<?php
// Start the session
session_start();

// Include database connection
require_once 'config/database.php';

// Include header
require_once 'includes/header.php';

// Include homepage content
require_once 'views/home.php';

// Include footer
require_once 'includes/footer.php';
?>
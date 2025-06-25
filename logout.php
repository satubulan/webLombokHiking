<?php
// Mulai session
session_start();

// Hapus semua variabel session
$_SESSION = [];

// Jika session menggunakan cookie, hapus juga cookienya
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Hancurkan session
session_destroy();

// Redirect ke halaman utama
header('Location: indexx.php');
exit();

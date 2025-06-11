<?php
session_start();
session_unset();     // Menghapus semua variabel session
session_destroy();   // Mengakhiri session

header('Location: ../index.php'); // Redirect ke halaman utama (belum login)
exit();

<?php
session_start(); // Mulai atau lanjutkan sesi yang ada

// Hapus semua variabel sesi
$_SESSION = array();

// Jika ingin menghancurkan sesi sepenuhnya, hapus juga cookie sesi.
// Catatan: Ini akan menghancurkan sesi, dan bukan hanya data sesi!
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Akhirnya, hancurkan sesi.
session_destroy();

// Arahkan ke halaman login (atau halaman utama)
// Kita arahkan ke halaman login donatur agar Anda bisa langsung tes
header("Location: login_donatur.php");
exit;
?>
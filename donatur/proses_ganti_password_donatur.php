<?php
session_start();

// 1. Cek apakah donatur sudah login
if (!isset($_SESSION['donatur_id'])) {
    header("Location: ../login_donatur.php");
    exit();
}

require_once __DIR__ . '/../app/classes/Donatur.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 2. Ambil data dari form
    $password_lama = $_POST['password_lama'] ?? '';
    $password_baru = $_POST['password_baru'] ?? '';
    $konfirmasi_password_baru = $_POST['konfirmasi_password_baru'] ?? '';
    $donatur_id_session = $_SESSION['donatur_id'];

    // 3. Validasi Input Dasar
    if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password_baru)) {
        $_SESSION['error_message_password'] = "Semua field password wajib diisi.";
        header("Location: ganti_password_donatur.php");
        exit();
    }

    if (strlen($password_baru) < 6) {
        $_SESSION['error_message_password'] = "Password baru minimal harus 6 karakter.";
        header("Location: ganti_password_donatur.php");
        exit();
    }

    if ($password_baru !== $konfirmasi_password_baru) {
        $_SESSION['error_message_password'] = "Password baru dan konfirmasi password baru tidak cocok.";
        header("Location: ganti_password_donatur.php");
        exit();
    }

    // 4. Ambil objek donatur yang sedang login
    $donatur = Donatur::findById($donatur_id_session);

    if (!$donatur) {
        // Seharusnya tidak terjadi jika sesi valid
        $_SESSION['error_message_password'] = "Gagal memuat data profil Anda untuk mengganti password.";
        header("Location: ganti_password_donatur.php");
        exit();
    }

    // 5. Panggil metode gantiPassword()
    $hasilGantiPassword = $donatur->gantiPassword($password_lama, $password_baru);

    // 6. Berikan feedback
    if ($hasilGantiPassword === true) {
        $_SESSION['success_message_profil'] = "Password Anda berhasil diperbarui!"; // Pesan ini akan ditampilkan di profil_donatur.php
        // Arahkan ke halaman profil setelah berhasil ganti password
        header("Location: profil_donatur.php"); 
        exit();
    } else {
        // Jika $hasilGantiPassword berisi pesan error dari metode gantiPassword()
        $_SESSION['error_message_password'] = $hasilGantiPassword; // Pesan ini akan ditampilkan di ganti_password_donatur.php
        header("Location: ganti_password_donatur.php");
        exit();
    }

} else {
    // Jika bukan metode POST, redirect
    $_SESSION['error_message_password'] = "Metode request tidak valid.";
    header("Location: ganti_password_donatur.php");
    exit();
}
?>
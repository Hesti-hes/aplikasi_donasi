<?php
session_start(); // Wajib ada di paling atas untuk manajemen sesi

// Include kelas Donatur
require_once __DIR__ . '/app/classes/Donatur.php';

// Cek apakah request adalah POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Ambil data dari formulir
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // 2. Validasi Sederhana di Sisi Server (opsional, karena kelas Donatur juga memvalidasi)
    if (empty($email) || empty($password)) {
        $_SESSION['error_message'] = "Email dan Password wajib diisi.";
        header("Location: login_donatur.php");
        exit();
    }

    // 3. Panggil metode login statis dari kelas Donatur
    $loginResult = Donatur::login($email, $password);

    // 4. Proses hasil login
    if ($loginResult instanceof Donatur) {
        // Login berhasil, $loginResult adalah objek Donatur
        // Simpan informasi penting donatur ke dalam session
        $_SESSION['donatur_id'] = $loginResult->getId();
        $_SESSION['donatur_nama_lengkap'] = $loginResult->getNamaLengkap();
        $_SESSION['donatur_email'] = $loginResult->getEmail();
        // Anda bisa menambahkan info lain jika perlu, misal role jika ada

        // Hapus pesan error yang mungkin ada sebelumnya
        unset($_SESSION['error_message']);

        // Arahkan ke halaman dashboard donatur (akan kita buat nanti)
        header("Location: donatur/dashboard.php");
        exit();
    } else {
        // Login gagal, $loginResult adalah string pesan error dari metode login
        $_SESSION['error_message'] = $loginResult;
        header("Location: login_donatur.php");
        exit();
    }

} else {
    // Jika bukan metode POST, redirect ke halaman login
    $_SESSION['error_message'] = "Akses tidak sah.";
    header("Location: login_donatur.php");
    exit();
}
?>
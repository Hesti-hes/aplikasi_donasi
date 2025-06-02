<?php
session_start(); // Mulai session di awal file

// Include kelas Donatur dan KoneksiDB (Donatur.php sudah include KoneksiDB.php)
require_once __DIR__ . '/app/classes/Donatur.php';

// Cek apakah request adalah POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Ambil data dari formulir
    $nama_lengkap = $_POST['nama_lengkap'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $konfirmasi_password = $_POST['konfirmasi_password'] ?? '';
    $no_telepon = $_POST['no_telepon'] ?? null; // Opsional, bisa null
    $alamat = $_POST['alamat'] ?? null;       // Opsional, bisa null

    // 2. Validasi Sederhana di Sisi Server
    $errors = []; // Array untuk menyimpan pesan error

    if (empty($nama_lengkap)) {
        $errors[] = "Nama lengkap wajib diisi.";
    }
    if (empty($email)) {
        $errors[] = "Email wajib diisi.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid.";
    }
    if (empty($password)) {
        $errors[] = "Password wajib diisi.";
    } elseif (strlen($password) < 6) {
        $errors[] = "Password minimal 6 karakter.";
    }
    if ($password !== $konfirmasi_password) {
        $errors[] = "Password dan Konfirmasi Password tidak cocok.";
    }
    // Validasi untuk no_telepon dan alamat bisa ditambahkan jika ada aturan khusus

    // Jika ada error validasi dasar
    if (!empty($errors)) {
        $_SESSION['error_message'] = implode("<br>", $errors);
        header("Location: register_donatur.php"); // Kembali ke form registrasi
        exit();
    }

    // 3. Jika validasi dasar lolos, coba daftarkan menggunakan kelas Donatur
    $donatur = new Donatur();
    $donatur->setNamaLengkap($nama_lengkap);
    $donatur->setEmail($email);
    $donatur->setPassword($password); // Setter akan menghash password
    if ($no_telepon) {
        $donatur->setNoTelepon($no_telepon);
    }
    if ($alamat) {
        $donatur->setAlamat($alamat);
    }
    // Foto profil akan menggunakan default dari kelas Donatur

    // 4. Panggil metode register()
    $hasilRegistrasi = $donatur->register();

    // 5. Berikan feedback berdasarkan hasil registrasi
    if ($hasilRegistrasi === true) {
        $_SESSION['success_message'] = "Registrasi berhasil! Silakan login dengan akun Anda.";
        // Idealnya, arahkan ke halaman login_donatur.php yang akan kita buat nanti
        // Untuk sekarang, bisa kembali ke register_donatur.php untuk melihat pesannya
        header("Location: login_donatur.php"); // Arahkan ke login jika sukses
        exit();
    } else {
        // Jika $hasilRegistrasi berisi pesan error dari kelas Donatur
        $_SESSION['error_message'] = $hasilRegistrasi;
        header("Location: register_donatur.php");
        exit();
    }

} else {
    // Jika bukan metode POST, redirect ke halaman registrasi (atau halaman lain)
    $_SESSION['error_message'] = "Akses tidak sah.";
    header("Location: register_donatur.php");
    exit();
}
?>
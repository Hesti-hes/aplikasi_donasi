<?php
session_start(); // Wajib ada di paling atas

// Include kelas Admin
require_once __DIR__ . '/app/classes/Admin.php';

// Cek apakah request adalah POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Ambil data dari formulir
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // 2. Validasi Sederhana (opsional, kelas Admin juga memvalidasi)
    if (empty($email) || empty($password)) {
        $_SESSION['error_message_admin'] = "Email dan Password wajib diisi.";
        header("Location: login_admin.php");
        exit();
    }

    // 3. Panggil metode login statis dari kelas Admin
    $loginResult = Admin::login($email, $password);

    // 4. Proses hasil login
    if ($loginResult instanceof Admin) {
        // Login berhasil, $loginResult adalah objek Admin
        // Simpan informasi penting admin ke dalam session
        $_SESSION['admin_id'] = $loginResult->getId();
        $_SESSION['admin_nama_lengkap'] = $loginResult->getNamaLengkap();
        $_SESSION['admin_email'] = $loginResult->getEmail();
        // Anda bisa menambahkan info lain jika perlu

        // Hapus pesan error yang mungkin ada sebelumnya
        unset($_SESSION['error_message_admin']);

        // Arahkan ke halaman dashboard admin (akan kita buat nanti)
        header("Location: admin/dashboard.php");
        exit();
    } else {
        // Login gagal, $loginResult adalah string pesan error dari metode login
        $_SESSION['error_message_admin'] = $loginResult;
        header("Location: login_admin.php");
        exit();
    }

} else {
    // Jika bukan metode POST, redirect ke halaman login admin
    $_SESSION['error_message_admin'] = "Akses tidak sah.";
    header("Location: login_admin.php");
    exit();
}
?>
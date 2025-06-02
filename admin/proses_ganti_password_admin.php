<?php
session_start();

// 1. Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login_admin.php");
    exit();
}

require_once __DIR__ . '/../app/classes/Admin.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 2. Ambil data dari form
    $password_lama = $_POST['password_lama_admin'] ?? '';
    $password_baru = $_POST['password_baru_admin'] ?? '';
    $konfirmasi_password_baru = $_POST['konfirmasi_password_baru_admin'] ?? '';
    $admin_id_session = $_SESSION['admin_id'];

    // 3. Validasi Input Dasar
    if (empty($password_lama) || empty($password_baru) || empty($konfirmasi_password_baru)) {
        $_SESSION['error_message_password_admin'] = "Semua field password wajib diisi.";
        header("Location: ganti_password_admin.php");
        exit();
    }

    if (strlen($password_baru) < 6) {
        $_SESSION['error_message_password_admin'] = "Password baru minimal harus 6 karakter.";
        header("Location: ganti_password_admin.php");
        exit();
    }

    if ($password_baru !== $konfirmasi_password_baru) {
        $_SESSION['error_message_password_admin'] = "Password baru dan konfirmasi password baru tidak cocok.";
        header("Location: ganti_password_admin.php");
        exit();
    }

    // 4. Ambil objek admin yang sedang login
    $admin = Admin::findById($admin_id_session);

    if (!$admin) {
        // Seharusnya tidak terjadi jika sesi valid
        $_SESSION['error_message_password_admin'] = "Gagal memuat data profil Anda untuk mengganti password.";
        header("Location: ganti_password_admin.php");
        exit();
    }

    // 5. Panggil metode gantiPassword()
    $hasilGantiPassword = $admin->gantiPassword($password_lama, $password_baru);

    // 6. Berikan feedback
    if ($hasilGantiPassword === true) {
        // Menggunakan kunci sesi yang sama dengan yang dicek di profil_admin.php untuk pesan sukses
        $_SESSION['success_message_profil_admin'] = "Password Anda berhasil diperbarui! Silakan login kembali jika diperlukan.";
        // Arahkan ke halaman profil setelah berhasil ganti password
        header("Location: profil_admin.php"); 
        exit();
    } else {
        // Jika $hasilGantiPassword berisi pesan error dari metode gantiPassword()
        $_SESSION['error_message_password_admin'] = $hasilGantiPassword; 
        header("Location: ganti_password_admin.php");
        exit();
    }

} else {
    // Jika bukan metode POST, redirect
    $_SESSION['error_message_password_admin'] = "Metode request tidak valid.";
    header("Location: ganti_password_admin.php");
    exit();
}
?>
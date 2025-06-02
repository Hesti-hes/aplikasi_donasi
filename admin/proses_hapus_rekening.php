<?php
session_start();

// 1. Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login_admin.php");
    exit();
}

// Include kelas RekeningTujuan
require_once __DIR__ . '/../app/classes/RekeningTujuan.php';

// 2. Validasi ID Rekening dari URL (GET request)
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message_rekening'] = "Permintaan tidak valid: ID Rekening tidak ditemukan atau salah format.";
    header("Location: kelola_rekening.php");
    exit();
}

$rekening_id = (int)$_GET['id'];

// (Opsional) Anda bisa memanggil RekeningTujuan::findById($rekening_id) terlebih dahulu
// untuk mendapatkan detail rekening yang akan dihapus (misalnya untuk ditampilkan di pesan sukses),
// atau untuk memastikan rekening tersebut memang ada sebelum mencoba menghapus.
// Namun, metode delete() kita sudah menangani kasus jika ID tidak ditemukan.
// $rekening = RekeningTujuan::findById($rekening_id);
// if (!$rekening) {
//     $_SESSION['error_message_rekening'] = "Rekening dengan ID " . htmlspecialchars($rekening_id) . " tidak ditemukan.";
//     header("Location: kelola_rekening.php");
//     exit();
// }
// $nama_bank_dihapus = $rekening->getNamaBank(); // Contoh jika ingin digunakan di pesan

// 3. Panggil metode delete untuk menghapus rekening dari database
$hasilDelete = RekeningTujuan::delete($rekening_id);

if ($hasilDelete === true) {
    // $_SESSION['success_message_rekening'] = "Rekening tujuan '" . htmlspecialchars($nama_bank_dihapus) . "' berhasil dihapus."; // Jika menggunakan info dari findById
    $_SESSION['success_message_rekening'] = "Rekening tujuan (ID: " . htmlspecialchars($rekening_id) . ") berhasil dihapus.";
} else {
    // Jika gagal menghapus dari database (hasilDelete berisi pesan error string)
    $_SESSION['error_message_rekening'] = "Gagal menghapus rekening: " . (is_string($hasilDelete) ? $hasilDelete : "Terjadi kesalahan tidak diketahui.");
}

// 4. Redirect kembali ke halaman kelola rekening
header("Location: kelola_rekening.php");
exit();

?>
<?php
session_start();

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    // Jika belum login, biasanya skrip proses tidak boleh diakses langsung.
    // Kita bisa set error dan arahkan atau langsung exit.
    // Untuk keamanan, kita tidak berikan pesan spesifik di sesi untuk proses ini jika akses tidak sah.
    header("Location: ../login_admin.php");
    exit();
}

require_once __DIR__ . '/../app/classes/Kampanye.php';

// 1. Validasi ID Kampanye dari URL (GET request)
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message_kampanye'] = "Permintaan tidak valid: ID Kampanye tidak ditemukan atau salah format.";
    header("Location: kelola_kampanye.php");
    exit();
}

$kampanye_id = (int)$_GET['id'];

// 2. Ambil detail kampanye untuk mendapatkan nama file gambar (sebelum dihapus dari DB)
$kampanye = Kampanye::findById($kampanye_id);

if (!$kampanye) {
    $_SESSION['error_message_kampanye'] = "Kampanye dengan ID " . htmlspecialchars($kampanye_id) . " tidak ditemukan atau sudah dihapus.";
    header("Location: kelola_kampanye.php");
    exit();
}

// (Opsional: Verifikasi apakah admin yang login berhak menghapus kampanye ini,
// misalnya jika hanya admin pembuat yang boleh menghapus)
// if ($kampanye->getAdminId() != $_SESSION['admin_id']) {
//     $_SESSION['error_message_kampanye'] = "Anda tidak memiliki hak untuk menghapus kampanye ini.";
//     header("Location: kelola_kampanye.php");
//     exit();
// }

$namaGambarKampanye = $kampanye->getGambarKampanye(); // Dapatkan nama file gambar

// 3. Hapus record kampanye dari database
$hasilDelete = Kampanye::delete($kampanye_id);

if ($hasilDelete === true) {
    // 4. Jika record database berhasil dihapus, coba hapus file gambarnya dari server
    if ($namaGambarKampanye) { // Cek apakah ada nama file gambar
        $pathGambar = __DIR__ . '/../uploads/gambar_kampanye/' . $namaGambarKampanye;
        if (file_exists($pathGambar)) {
            if (@unlink($pathGambar)) {
                // File gambar berhasil dihapus
                $_SESSION['success_message_kampanye'] = "Kampanye '" . htmlspecialchars($kampanye->getJudul()) . "' dan file gambarnya berhasil dihapus.";
            } else {
                // Gagal hapus file gambar, tapi record DB sudah terhapus
                $_SESSION['warning_message_kampanye'] = "Kampanye '" . htmlspecialchars($kampanye->getJudul()) . "' berhasil dihapus dari database, tetapi file gambarnya gagal dihapus dari server. Silakan periksa folder uploads.";
            }
        } else {
            // File gambar tidak ditemukan di server (mungkin sudah terhapus sebelumnya atau nama file salah)
            $_SESSION['success_message_kampanye'] = "Kampanye '" . htmlspecialchars($kampanye->getJudul()) . "' berhasil dihapus. File gambar tidak ditemukan untuk dihapus.";
        }
    } else {
        // Tidak ada file gambar terkait dengan kampanye ini
        $_SESSION['success_message_kampanye'] = "Kampanye '" . htmlspecialchars($kampanye->getJudul()) . "' berhasil dihapus (tidak ada gambar terkait).";
    }
} else {
    // Jika gagal menghapus dari database (hasilDelete berisi pesan error string)
    $_SESSION['error_message_kampanye'] = "Gagal menghapus kampanye: " . (is_string($hasilDelete) ? $hasilDelete : "Terjadi kesalahan tidak diketahui.");
}

// Arahkan kembali ke halaman kelola kampanye
header("Location: kelola_kampanye.php");
exit();

?>
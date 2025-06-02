<?php
session_start();

// 1. Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login_admin.php");
    exit();
}

// Include kelas RekeningTujuan
require_once __DIR__ . '/../app/classes/RekeningTujuan.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 2. Ambil data dari form
    $nama_bank = $_POST['nama_bank'] ?? '';
    $nomor_rekening = $_POST['nomor_rekening'] ?? '';
    $atas_nama = $_POST['atas_nama'] ?? '';

    // 3. Validasi Data Sederhana
    $errors = [];
    if (empty($nama_bank)) {
        $errors[] = "Nama bank wajib diisi.";
    }
    if (empty($nomor_rekening)) {
        $errors[] = "Nomor rekening wajib diisi.";
    } elseif (!ctype_digit($nomor_rekening)) { // Cek apakah hanya berisi angka
        $errors[] = "Nomor rekening hanya boleh berisi angka.";
    }
    if (empty($atas_nama)) {
        $errors[] = "Atas nama (pemilik rekening) wajib diisi.";
    }

    // Jika ada error validasi, kembali ke form tambah rekening
    if (!empty($errors)) {
        $_SESSION['error_message_rekening'] = implode("<br>", $errors);
        $_SESSION['form_data_rekening'] = $_POST; // Simpan input untuk diisi kembali ke form
        header("Location: tambah_rekening.php");
        exit();
    }

    // 4. Jika validasi lolos, buat objek RekeningTujuan
    $rekening = new RekeningTujuan();
    $rekening->setNamaBank($nama_bank);
    $rekening->setNomorRekening($nomor_rekening);
    $rekening->setAtasNama($atas_nama);

    // 5. Panggil metode create()
    $hasilCreate = $rekening->create();

    // 6. Berikan feedback
    if (is_numeric($hasilCreate) && $hasilCreate > 0) { // Jika create mengembalikan ID
        $_SESSION['success_message_rekening'] = "Rekening tujuan baru berhasil ditambahkan dengan ID: " . $hasilCreate . "!";
        unset($_SESSION['form_data_rekening']); // Hapus data form dari sesi jika sukses
        // Arahkan kembali ke halaman tambah (untuk menambah lagi) atau ke halaman kelola rekening nanti
        header("Location: tambah_rekening.php"); 
        exit();
    } else {
        // Jika create mengembalikan pesan error string
        $_SESSION['error_message_rekening'] = "Gagal menambah rekening: " . (is_string($hasilCreate) ? $hasilCreate : "Terjadi kesalahan tidak diketahui.");
        $_SESSION['form_data_rekening'] = $_POST; // Simpan input untuk diisi kembali
        header("Location: tambah_rekening.php");
        exit();
    }

} else {
    // Jika bukan metode POST
    $_SESSION['error_message_rekening'] = "Metode request tidak valid.";
    header("Location: tambah_rekening.php"); // atau dashboard.php
    exit();
}
?>
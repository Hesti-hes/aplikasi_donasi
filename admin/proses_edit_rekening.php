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
    $rekening_id = $_POST['rekening_id'] ?? null;
    $nama_bank = $_POST['nama_bank'] ?? '';
    $nomor_rekening = $_POST['nomor_rekening'] ?? '';
    $atas_nama = $_POST['atas_nama'] ?? '';

    // 3. Validasi ID Rekening
    if (empty($rekening_id) || !is_numeric($rekening_id)) {
        $_SESSION['error_message_rekening'] = "ID Rekening tidak valid untuk diedit.";
        header("Location: kelola_rekening.php"); // Kembali ke halaman kelola jika ID bermasalah
        exit();
    }
    $rekening_id = (int)$rekening_id;

    // 4. Validasi Data Form lainnya
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

    // Jika ada error validasi, kembali ke form edit rekening
    if (!empty($errors)) {
        $_SESSION['error_message_rekening_edit'] = implode("<br>", $errors);
        $_SESSION['form_data_rekening_edit'] = $_POST; // Simpan input untuk diisi kembali
        header("Location: edit_rekening.php?id=" . $rekening_id);
        exit();
    }

    // 5. Ambil objek rekening yang ada dari database
    $rekening = RekeningTujuan::findById($rekening_id);

    if (!$rekening) {
        $_SESSION['error_message_rekening'] = "Rekening yang akan diedit tidak ditemukan.";
        header("Location: kelola_rekening.php");
        exit();
    }

    // 6. Set properti objek rekening dengan data baru
    $rekening->setNamaBank($nama_bank);
    $rekening->setNomorRekening($nomor_rekening);
    $rekening->setAtasNama($atas_nama);

    // 7. Panggil metode update()
    $hasilUpdate = $rekening->update();

    // 8. Berikan feedback
    if ($hasilUpdate === true) {
        $_SESSION['success_message_rekening'] = "Rekening tujuan (ID: " . $rekening_id . ") berhasil diperbarui!";
        unset($_SESSION['form_data_rekening_edit']); // Hapus data form dari sesi jika sukses
        header("Location: kelola_rekening.php"); 
        exit();
    } else {
        $_SESSION['error_message_rekening_edit'] = "Gagal memperbarui rekening: " . (is_string($hasilUpdate) ? $hasilUpdate : "Terjadi kesalahan tidak diketahui.");
        $_SESSION['form_data_rekening_edit'] = $_POST; // Simpan input untuk diisi kembali
        header("Location: edit_rekening.php?id=" . $rekening_id);
        exit();
    }

} else {
    // Jika bukan metode POST
    $_SESSION['error_message_rekening'] = "Metode request tidak valid.";
    header("Location: kelola_rekening.php");
    exit();
}
?>
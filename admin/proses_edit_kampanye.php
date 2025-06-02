<?php
session_start();

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    $_SESSION['error_message_kampanye_edit'] = "Akses tidak sah. Silakan login kembali.";
    // Jika kita tidak tahu ID kampanye, arahkan ke login umum admin
    header("Location: ../login_admin.php"); 
    exit();
}

require_once __DIR__ . '/../app/classes/Kampanye.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 1. Ambil data dari form
    $kampanye_id = $_POST['kampanye_id'] ?? null;
    $judul = $_POST['judul'] ?? '';
    $deskripsi_singkat = $_POST['deskripsi_singkat'] ?? '';
    $deskripsi_lengkap = $_POST['deskripsi_lengkap'] ?? '';
    $target_donasi = isset($_POST['target_donasi']) ? (float)$_POST['target_donasi'] : 0;
    $status_kampanye = $_POST['status_kampanye'] ?? 'aktif';
    $tanggal_mulai = !empty($_POST['tanggal_mulai']) ? $_POST['tanggal_mulai'] : null;
    $tanggal_selesai = !empty($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : null;
    // admin_id tidak diambil dari form, tapi bisa diverifikasi jika perlu
    $admin_id_session = $_SESSION['admin_id']; 

    $namaFileGambarKampanyeBaru = null; // Untuk menyimpan nama file gambar baru jika ada
    $gambarKampanyeLama = null; // Untuk menyimpan nama file gambar lama

    // 2. Validasi ID Kampanye
    if (empty($kampanye_id) || !is_numeric($kampanye_id)) {
        $_SESSION['error_message_kampanye'] = "ID Kampanye tidak valid untuk diedit.";
        header("Location: kelola_kampanye.php");
        exit();
    }
    $kampanye_id = (int)$kampanye_id;

    // 3. Validasi Data Sederhana (sama seperti tambah, tambahkan yang lebih spesifik jika perlu)
    $errors = [];
    if (empty($judul)) {
        $errors[] = "Judul kampanye wajib diisi.";
    }
    if (empty($deskripsi_lengkap)) {
        $errors[] = "Deskripsi lengkap kampanye wajib diisi.";
    }
    if ($target_donasi <= 0) {
        $errors[] = "Target donasi harus lebih besar dari 0.";
    }
    if ($tanggal_mulai && $tanggal_selesai && $tanggal_mulai > $tanggal_selesai) {
        $errors[] = "Tanggal mulai tidak boleh lebih akhir dari tanggal selesai.";
    }

    // 4. Ambil data kampanye yang ada untuk mendapatkan nama file gambar lama
    $kampanyeExisting = Kampanye::findById($kampanye_id);
    if (!$kampanyeExisting) {
        $_SESSION['error_message_kampanye'] = "Kampanye yang akan diedit tidak ditemukan.";
        header("Location: kelola_kampanye.php");
        exit();
    }
    // Pastikan admin yang login adalah pemilik kampanye (opsional, tergantung aturan bisnis)
    // if ($kampanyeExisting->getAdminId() != $admin_id_session) {
    //     $_SESSION['error_message_kampanye_edit'] = "Anda tidak berhak mengedit kampanye ini.";
    //     header("Location: edit_kampanye.php?id=" . $kampanye_id);
    //     exit();
    // }
    $gambarKampanyeLama = $kampanyeExisting->getGambarKampanye();

    // 5. Proses Upload Gambar Kampanye Baru (Jika ada file yang diupload)
    if (isset($_FILES['gambar_kampanye_baru']) && $_FILES['gambar_kampanye_baru']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['gambar_kampanye_baru']['tmp_name'];
        $fileName = $_FILES['gambar_kampanye_baru']['name'];
        $fileSize = $_FILES['gambar_kampanye_baru']['size'];
        $fileType = $_FILES['gambar_kampanye_baru']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $cleanFileName = preg_replace("/[^A-Za-z0-9\-_.]/", '', basename($fileNameCmps[0]));
        $namaFileGambarKampanyeBaru = time() . '_' . $cleanFileName . '.' . $fileExtension;

        $uploadFileDir = __DIR__ . '/../uploads/gambar_kampanye/';
        $dest_path_baru = $uploadFileDir . $namaFileGambarKampanyeBaru;

        $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileExtension, $allowedfileExtensions)) {
            if ($fileSize < 2000000) { // 2MB
                if (!move_uploaded_file($fileTmpPath, $dest_path_baru)) {
                    $errors[] = 'Gagal memindahkan file gambar baru.';
                    $namaFileGambarKampanyeBaru = null; 
                }
            } else {
                $errors[] = 'Ukuran file gambar baru terlalu besar. Maksimal 2MB.';
                $namaFileGambarKampanyeBaru = null;
            }
        } else {
            $errors[] = 'Tipe file gambar baru tidak valid. Hanya JPG, JPEG, PNG, GIF.';
            $namaFileGambarKampanyeBaru = null;
        }
    } elseif (isset($_FILES['gambar_kampanye_baru']) && $_FILES['gambar_kampanye_baru']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['gambar_kampanye_baru']['error'] != UPLOAD_ERR_OK) {
        $errors[] = 'Terjadi kesalahan saat mengupload gambar baru. Kode Error: ' . $_FILES['gambar_kampanye_baru']['error'];
    }

    // Jika ada error validasi data atau upload, kembali ke form edit
    if (!empty($errors)) {
        $_SESSION['error_message_kampanye_edit'] = implode("<br>", $errors);
        // Simpan kembali input user agar form bisa diisi ulang (kecuali file)
        $_SESSION['form_data_kampanye_edit'] = $_POST; 
        header("Location: edit_kampanye.php?id=" . $kampanye_id);
        exit();
    }

    // 6. Jika tidak ada error, update objek Kampanye
    // Kita gunakan objek $kampanyeExisting yang sudah di-load
    $kampanyeExisting->setJudul($judul);
    $kampanyeExisting->setDeskripsiSingkat($deskripsi_singkat);
    $kampanyeExisting->setDeskripsiLengkap($deskripsi_lengkap);
    $kampanyeExisting->setTargetDonasi($target_donasi);
    $kampanyeExisting->setStatusKampanye($status_kampanye);
    $kampanyeExisting->setTanggalMulai($tanggal_mulai);
    $kampanyeExisting->setTanggalSelesai($tanggal_selesai);
    // admin_id tidak diubah
    // dana_terkumpul tidak diubah dari form ini

    // Jika ada gambar baru yang berhasil diupload, update nama filenya dan hapus gambar lama
    if ($namaFileGambarKampanyeBaru) {
        $kampanyeExisting->setGambarKampanye($namaFileGambarKampanyeBaru);
        // Hapus gambar lama jika ada dan berbeda dari gambar baru
        if ($gambarKampanyeLama && $gambarKampanyeLama != $namaFileGambarKampanyeBaru) {
            $pathGambarLama = __DIR__ . '/../uploads/gambar_kampanye/' . $gambarKampanyeLama;
            if (file_exists($pathGambarLama)) {
                @unlink($pathGambarLama); // Hapus file lama, @ untuk menekan error jika gagal
            }
        }
    } // Jika tidak ada gambar baru, $kampanyeExisting->gambarKampanye tetap berisi nama file lama

    // 7. Panggil metode update()
    $hasilUpdate = $kampanyeExisting->update();

    // 8. Berikan feedback
    if ($hasilUpdate === true) {
        $_SESSION['success_message_kampanye'] = "Kampanye '" . htmlspecialchars($kampanyeExisting->getJudul()) . "' berhasil diperbarui!";
        unset($_SESSION['form_data_kampanye_edit']);
        header("Location: kelola_kampanye.php"); // Kembali ke halaman kelola kampanye
        exit();
    } else {
        $_SESSION['error_message_kampanye_edit'] = "Gagal memperbarui kampanye: " . (is_string($hasilUpdate) ? $hasilUpdate : "Terjadi kesalahan tidak diketahui.");
        $_SESSION['form_data_kampanye_edit'] = $_POST;
        header("Location: edit_kampanye.php?id=" . $kampanye_id);
        exit();
    }

} else {
    // Jika bukan metode POST
    $_SESSION['error_message_kampanye'] = "Metode request tidak valid.";
    header("Location: kelola_kampanye.php");
    exit();
}
?>
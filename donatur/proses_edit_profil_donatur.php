<?php
session_start();

// 1. Cek apakah donatur sudah login
if (!isset($_SESSION['donatur_id'])) {
    header("Location: ../login_donatur.php");
    exit();
}

require_once __DIR__ . '/../app/classes/Donatur.php';

$donatur_id_session = $_SESSION['donatur_id'];
$namaFileFotoBaru = null; // Untuk menyimpan nama file foto baru jika ada

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 2. Ambil data dari form
    // donatur_id dari form digunakan untuk cross-check, tapi utamakan dari sesi
    $form_donatur_id = $_POST['donatur_id'] ?? null; 
    $nama_lengkap = $_POST['nama_lengkap'] ?? '';
    $no_telepon = $_POST['no_telepon'] ?? '';
    $alamat = $_POST['alamat'] ?? '';

    // Cross-check ID donatur dari form dengan sesi
    if ($form_donatur_id != $donatur_id_session) {
        $_SESSION['error_message_profil_edit'] = "Terjadi kesalahan: ID pengguna tidak cocok.";
        header("Location: edit_profil_donatur.php");
        exit();
    }

    // 3. Validasi Data Form
    $errors = [];
    if (empty($nama_lengkap)) {
        $errors[] = "Nama lengkap wajib diisi.";
    }
    // Tambahkan validasi lain jika perlu untuk no_telepon atau alamat

    // 4. Ambil objek donatur yang ada dari database
    $donatur = Donatur::findById($donatur_id_session);
    if (!$donatur) {
        // Seharusnya tidak terjadi jika sesi valid
        $_SESSION['error_message_profil_edit'] = "Gagal memuat data profil Anda.";
        header("Location: edit_profil_donatur.php");
        exit();
    }
    $fotoProfilLama = $donatur->getFotoProfil();

    // 5. Proses Upload Foto Profil Baru (Jika ada file yang diupload)
    if (isset($_FILES['foto_profil_baru']) && $_FILES['foto_profil_baru']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['foto_profil_baru']['tmp_name'];
        $fileName = $_FILES['foto_profil_baru']['name'];
        $fileSize = $_FILES['foto_profil_baru']['size'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $cleanFileName = preg_replace("/[^A-Za-z0-9\-_.]/", '', basename($fileNameCmps[0]));
        $namaFileFotoBaru = "donatur_" . $donatur_id_session . '_' . time() . '_' . $cleanFileName . '.' . $fileExtension;

        $uploadFileDir = __DIR__ . '/../uploads/profil_donatur/'; 
        $dest_path_baru = $uploadFileDir . $namaFileFotoBaru;

        $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileExtension, $allowedfileExtensions)) {
            if ($fileSize < 1000000) { // 1MB untuk foto profil
                if (!move_uploaded_file($fileTmpPath, $dest_path_baru)) {
                    $errors[] = 'Gagal memindahkan file foto profil baru. Pastikan folder uploads/profil_donatur/ writable.';
                    $namaFileFotoBaru = null; 
                }
            } else {
                $errors[] = 'Ukuran file foto profil baru terlalu besar. Maksimal 1MB.';
                $namaFileFotoBaru = null;
            }
        } else {
            $errors[] = 'Tipe file foto profil baru tidak valid. Hanya JPG, JPEG, PNG, GIF.';
            $namaFileFotoBaru = null;
        }
    } elseif (isset($_FILES['foto_profil_baru']) && $_FILES['foto_profil_baru']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['foto_profil_baru']['error'] != UPLOAD_ERR_OK) {
        $errors[] = 'Terjadi kesalahan saat mengupload foto profil baru. Kode Error: ' . $_FILES['foto_profil_baru']['error'];
    }

    // Jika ada error validasi data atau upload, kembali ke form edit
    if (!empty($errors)) {
        $_SESSION['error_message_profil_edit'] = implode("<br>", $errors);
        $_SESSION['form_data_profil_edit'] = $_POST; 
        header("Location: edit_profil_donatur.php");
        exit();
    }

    // 6. Update properti objek donatur
    $donatur->setNamaLengkap($nama_lengkap);
    $donatur->setNoTelepon($no_telepon);
    $donatur->setAlamat($alamat);

    // Jika ada foto baru yang berhasil diupload, update nama filenya dan hapus foto lama (jika bukan default)
    if ($namaFileFotoBaru) {
        $donatur->setFotoProfil($namaFileFotoBaru);
        if ($fotoProfilLama && $fotoProfilLama != 'default_donatur.jpg' && $fotoProfilLama != $namaFileFotoBaru) {
            $pathFotoLama = __DIR__ . '/../uploads/profil_donatur/' . $fotoProfilLama;
            if (file_exists($pathFotoLama)) {
                @unlink($pathFotoLama); 
            }
        }
    } // Jika tidak ada foto baru, properti fotoProfil pada objek tidak diubah (tetap yang lama)

    // 7. Panggil metode updateProfil()
    $hasilUpdate = $donatur->updateProfil();

    // 8. Berikan feedback dan update sesi jika nama berubah
    if ($hasilUpdate === true) {
        $_SESSION['success_message_profil'] = "Profil Anda berhasil diperbarui!";
        // Update nama di sesi jika berubah, agar navbar langsung update
        if (isset($_SESSION['donatur_nama_lengkap']) && $_SESSION['donatur_nama_lengkap'] != $donatur->getNamaLengkap()) {
            $_SESSION['donatur_nama_lengkap'] = $donatur->getNamaLengkap();
        }
        unset($_SESSION['form_data_profil_edit']);
        header("Location: profil_donatur.php"); // Kembali ke halaman lihat profil
        exit();
    } else {
        $_SESSION['error_message_profil_edit'] = "Gagal memperbarui profil: " . (is_string($hasilUpdate) ? $hasilUpdate : "Terjadi kesalahan tidak diketahui.");
        // Jika update DB gagal setelah file baru diupload, file baru tersebut mungkin perlu dihapus
        // (logika ini bisa ditambahkan jika diperlukan untuk menjaga konsistensi)
        // if ($namaFileFotoBaru && isset($dest_path_baru) && file_exists($dest_path_baru)) {
        //     @unlink($dest_path_baru);
        // }
        $_SESSION['form_data_profil_edit'] = $_POST;
        header("Location: edit_profil_donatur.php");
        exit();
    }

} else {
    // Jika bukan metode POST
    $_SESSION['error_message_profil'] = "Metode request tidak valid.";
    header("Location: profil_donatur.php");
    exit();
}
?>
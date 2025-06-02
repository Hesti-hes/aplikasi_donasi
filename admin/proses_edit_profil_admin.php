<?php
session_start();

// 1. Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login_admin.php");
    exit();
}

require_once __DIR__ . '/../app/classes/Admin.php';

$admin_id_session = $_SESSION['admin_id'];
$namaFileFotoBaru = null; // Untuk menyimpan nama file foto baru jika ada

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 2. Ambil data dari form
    $form_admin_id = $_POST['admin_id'] ?? null; 
    $nama_lengkap = $_POST['nama_lengkap'] ?? '';
    $no_telepon = $_POST['no_telepon'] ?? '';
    // Email tidak diambil karena tidak diedit

    // Cross-check ID admin dari form dengan sesi
    if ($form_admin_id != $admin_id_session) {
        $_SESSION['error_message_profil_admin_edit'] = "Terjadi kesalahan: ID pengguna tidak cocok.";
        header("Location: edit_profil_admin.php"); // Redirect kembali ke form edit
        exit();
    }

    // 3. Validasi Data Form
    $errors = [];
    if (empty($nama_lengkap)) {
        $errors[] = "Nama lengkap wajib diisi.";
    }
    // Tambahkan validasi lain jika perlu untuk no_telepon

    // 4. Ambil objek admin yang ada dari database
    $admin = Admin::findById($admin_id_session);
    if (!$admin) {
        $_SESSION['error_message_profil_admin_edit'] = "Gagal memuat data profil Anda untuk pembaruan.";
        header("Location: edit_profil_admin.php");
        exit();
    }
    $fotoProfilLama = $admin->getFotoProfil();

    // 5. Proses Upload Foto Profil Baru (Jika ada file yang diupload)
    if (isset($_FILES['foto_profil_baru_admin']) && $_FILES['foto_profil_baru_admin']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['foto_profil_baru_admin']['tmp_name'];
        $fileName = $_FILES['foto_profil_baru_admin']['name'];
        $fileSize = $_FILES['foto_profil_baru_admin']['size'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $cleanFileName = preg_replace("/[^A-Za-z0-9\-_.]/", '', basename($fileNameCmps[0]));
        $namaFileFotoBaru = "admin_" . $admin_id_session . '_' . time() . '_' . $cleanFileName . '.' . $fileExtension;

        $uploadFileDir = __DIR__ . '/../uploads/profil_admin/'; 
        $dest_path_baru = $uploadFileDir . $namaFileFotoBaru;

        $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileExtension, $allowedfileExtensions)) {
            if ($fileSize < 1000000) { // 1MB untuk foto profil
                if (!move_uploaded_file($fileTmpPath, $dest_path_baru)) {
                    $errors[] = 'Gagal memindahkan file foto profil baru. Pastikan folder uploads/profil_admin/ writable.';
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
    } elseif (isset($_FILES['foto_profil_baru_admin']) && $_FILES['foto_profil_baru_admin']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['foto_profil_baru_admin']['error'] != UPLOAD_ERR_OK) {
        $errors[] = 'Terjadi kesalahan saat mengupload foto profil baru. Kode Error: ' . $_FILES['foto_profil_baru_admin']['error'];
    }

    // Jika ada error validasi data atau upload, kembali ke form edit
    if (!empty($errors)) {
        $_SESSION['error_message_profil_admin_edit'] = implode("<br>", $errors);
        $_SESSION['form_data_profil_admin_edit'] = $_POST; 
        header("Location: edit_profil_admin.php");
        exit();
    }

    // 6. Update properti objek admin
    $admin->setNamaLengkap($nama_lengkap);
    $admin->setNoTelepon($no_telepon);

    // Jika ada foto baru yang berhasil diupload, update nama filenya dan hapus foto lama (jika bukan default)
    if ($namaFileFotoBaru) {
        $admin->setFotoProfil($namaFileFotoBaru);
        if ($fotoProfilLama && $fotoProfilLama != 'default_admin.jpg' && $fotoProfilLama != $namaFileFotoBaru) {
            $pathFotoLama = __DIR__ . '/../uploads/profil_admin/' . $fotoProfilLama;
            if (file_exists($pathFotoLama)) {
                @unlink($pathFotoLama); 
            }
        }
    } // Jika tidak ada foto baru, properti fotoProfil pada objek tidak diubah (tetap yang lama)

    // 7. Panggil metode updateProfil()
    $hasilUpdate = $admin->updateProfil();

    // 8. Berikan feedback dan update sesi jika nama berubah
    if ($hasilUpdate === true) {
        $_SESSION['success_message_profil_admin'] = "Profil Anda berhasil diperbarui!";
        // Update nama di sesi jika berubah, agar navbar langsung update
        if (isset($_SESSION['admin_nama_lengkap']) && $_SESSION['admin_nama_lengkap'] != $admin->getNamaLengkap()) {
            $_SESSION['admin_nama_lengkap'] = $admin->getNamaLengkap();
        }
        unset($_SESSION['form_data_profil_admin_edit']);
        header("Location: profil_admin.php"); // Kembali ke halaman lihat profil
        exit();
    } else {
        $_SESSION['error_message_profil_admin_edit'] = "Gagal memperbarui profil: " . (is_string($hasilUpdate) ? $hasilUpdate : "Terjadi kesalahan tidak diketahui.");
        // Jika update DB gagal setelah file baru diupload, file baru tersebut mungkin perlu dihapus
        if ($namaFileFotoBaru && isset($dest_path_baru) && file_exists($dest_path_baru)) {
            @unlink($dest_path_baru); // Hapus file baru jika update DB gagal
        }
        $_SESSION['form_data_profil_admin_edit'] = $_POST;
        header("Location: edit_profil_admin.php");
        exit();
    }

} else {
    // Jika bukan metode POST
    $_SESSION['error_message_profil_admin'] = "Metode request tidak valid.";
    header("Location: profil_admin.php");
    exit();
}
?>
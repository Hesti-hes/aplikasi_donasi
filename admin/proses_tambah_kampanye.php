<?php
session_start();

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    // Jika belum login, mungkin arahkan ke login atau tampilkan error
    // Untuk skrip proses, lebih baik stop atau redirect jika tidak sah
    $_SESSION['error_message_kampanye'] = "Akses tidak sah. Silakan login kembali.";
    header("Location: ../login_admin.php"); // Kembali ke login admin
    exit();
}

require_once __DIR__ . '/../app/classes/Kampanye.php'; // Path ke kelas Kampanye

// Inisialisasi variabel untuk nama file gambar
$namaFileGambarKampanye = null;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Ambil data dari form (selain file)
    $judul = $_POST['judul'] ?? '';
    $deskripsi_singkat = $_POST['deskripsi_singkat'] ?? '';
    $deskripsi_lengkap = $_POST['deskripsi_lengkap'] ?? '';
    $target_donasi = isset($_POST['target_donasi']) ? (float)$_POST['target_donasi'] : 0;
    $status_kampanye = $_POST['status_kampanye'] ?? 'aktif';
    $tanggal_mulai = !empty($_POST['tanggal_mulai']) ? $_POST['tanggal_mulai'] : null;
    $tanggal_selesai = !empty($_POST['tanggal_selesai']) ? $_POST['tanggal_selesai'] : null;
    $admin_id = $_SESSION['admin_id']; // Ambil ID admin dari sesi

    // 2. Validasi Data Sederhana (Tambahkan validasi yang lebih detail sesuai kebutuhan)
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

    // 3. Proses Upload Gambar Kampanye (Jika ada file yang diupload)
    if (isset($_FILES['gambar_kampanye']) && $_FILES['gambar_kampanye']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['gambar_kampanye']['tmp_name'];
        $fileName = $_FILES['gambar_kampanye']['name'];
        $fileSize = $_FILES['gambar_kampanye']['size'];
        $fileType = $_FILES['gambar_kampanye']['type'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        // Sanitasi nama file & buat nama unik
        $cleanFileName = preg_replace("/[^A-Za-z0-9\-_.]/", '', basename($fileNameCmps[0]));
        $namaFileGambarKampanye = time() . '_' . $cleanFileName . '.' . $fileExtension;

        // Tentukan direktori upload (relatif dari root proyek)
        $uploadFileDir = __DIR__ . '/../uploads/gambar_kampanye/'; // Path dari file ini ke folder uploads
        $dest_path = $uploadFileDir . $namaFileGambarKampanye;

        // Validasi tipe file yang diizinkan
        $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif'];
        if (in_array($fileExtension, $allowedfileExtensions)) {
            // Validasi ukuran file (misalnya, maks 2MB)
            if ($fileSize < 2000000) { // 2MB dalam bytes
                if (move_uploaded_file($fileTmpPath, $dest_path)) {
                    // File berhasil diupload
                } else {
                    $errors[] = 'Gagal memindahkan file yang diupload. Pastikan folder uploads/gambar_kampanye/ writable.';
                    $namaFileGambarKampanye = null; // Reset jika gagal
                }
            } else {
                $errors[] = 'Ukuran file gambar terlalu besar. Maksimal 2MB.';
                $namaFileGambarKampanye = null;
            }
        } else {
            $errors[] = 'Tipe file gambar tidak valid. Hanya JPG, JPEG, PNG, GIF yang diizinkan.';
            $namaFileGambarKampanye = null;
        }
    } elseif (isset($_FILES['gambar_kampanye']) && $_FILES['gambar_kampanye']['error'] != UPLOAD_ERR_NO_FILE && $_FILES['gambar_kampanye']['error'] != UPLOAD_ERR_OK) {
        // Jika ada file tapi error selain UPLOAD_ERR_NO_FILE (tidak ada file dipilih)
        $errors[] = 'Terjadi kesalahan saat mengupload gambar. Kode Error: ' . $_FILES['gambar_kampanye']['error'];
    }
    // Jika tidak ada file yang diupload sama sekali, $namaFileGambarKampanye akan tetap null (atau bisa diisi default jika perlu)

    // Jika ada error validasi atau upload, kembali ke form
    if (!empty($errors)) {
        $_SESSION['error_message_kampanye'] = implode("<br>", $errors);
        // Simpan kembali input user ke session agar form bisa diisi ulang (opsional, untuk UX yang lebih baik)
        $_SESSION['form_data_kampanye'] = $_POST; 
        header("Location: tambah_kampanye.php");
        exit();
    }

    // 4. Jika semua validasi dan upload (jika ada) berhasil, buat objek Kampanye
    $kampanye = new Kampanye();
    $kampanye->setAdminId($admin_id);
    $kampanye->setJudul($judul);
    $kampanye->setDeskripsiSingkat($deskripsi_singkat);
    $kampanye->setDeskripsiLengkap($deskripsi_lengkap);
    if ($namaFileGambarKampanye) { // Hanya set jika ada gambar yang berhasil diupload
        $kampanye->setGambarKampanye($namaFileGambarKampanye);
    }
    $kampanye->setTargetDonasi($target_donasi);
    $kampanye->setStatusKampanye($status_kampanye);
    $kampanye->setTanggalMulai($tanggal_mulai);
    $kampanye->setTanggalSelesai($tanggal_selesai);
    // danaTerkumpul dan status default sudah diatur di constructor Kampanye jika perlu

    // 5. Panggil metode create()
    $hasilCreate = $kampanye->create();

    // 6. Berikan feedback
    if (is_numeric($hasilCreate) && $hasilCreate > 0) { // Jika create mengembalikan ID
        $_SESSION['success_message_kampanye'] = "Kampanye baru berhasil dibuat dengan ID: " . $hasilCreate . "!";
        unset($_SESSION['form_data_kampanye']); // Hapus data form jika sukses
        header("Location: tambah_kampanye.php"); // Kembali ke form tambah (atau ke kelola kampanye nanti)
        exit();
    } else {
        // Jika create mengembalikan pesan error string
        $_SESSION['error_message_kampanye'] = "Gagal membuat kampanye: " . (is_string($hasilCreate) ? $hasilCreate : "Terjadi kesalahan tidak diketahui.");
        $_SESSION['form_data_kampanye'] = $_POST;
        header("Location: tambah_kampanye.php");
        exit();
    }

} else {
    // Jika bukan metode POST
    $_SESSION['error_message_kampanye'] = "Metode request tidak valid.";
    header("Location: tambah_kampanye.php");
    exit();
}
?>
<?php
session_start();
var_dump($_POST); // Tambahkan ini untuk melihat semua data POST
// exit; // Anda bisa tambahkan exit di sini untuk berhenti dan hanya lihat var_dump
// ... sisa kode proses_kirim_donasi.php ...
?>
<?php
session_start();

// 1. Pastikan donatur sudah login
if (!isset($_SESSION['donatur_id'])) {
    // Jika belum login, skrip proses tidak boleh diakses
    // Arahkan ke login dengan pesan, bisa juga menyertakan ID kampanye jika ada
    $kampanye_id_redirect = isset($_POST['kampanye_id']) ? "&kampanye_id=" . $_POST['kampanye_id'] : '';
    $_SESSION['error_message'] = "Sesi Anda telah berakhir atau Anda belum login. Silakan login kembali untuk melanjutkan donasi.";
    header("Location: login_donatur.php?redirect=form_donasi.php" . $kampanye_id_redirect);
    exit();
}

require_once __DIR__ . '/app/classes/Donasi.php'; // Path ke kelas Donasi

$namaFileBuktiPembayaran = null; // Inisialisasi nama file
$dest_path = null; // Inisialisasi path tujuan file

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // 2. Ambil data dari form
    $kampanye_id = $_POST['kampanye_id'] ?? null;
    $jumlah_donasi = isset($_POST['jumlah_donasi']) ? (float)$_POST['jumlah_donasi'] : 0;
    $pesan_donatur = $_POST['pesan_donatur'] ?? '';
    $donatur_id = $_SESSION['donatur_id']; // Ambil ID donatur dari sesi

    // 3. Validasi Data Form
    $errors = [];
    if (empty($kampanye_id) || !is_numeric($kampanye_id)) {
        $errors[] = "ID Kampanye tidak valid.";
    }
    if ($jumlah_donasi < 10000) { // Minimal donasi Rp 10.000 (sesuai form)
        $errors[] = "Jumlah donasi minimal adalah Rp 10.000.";
    }
    if (!isset($_POST['konfirmasiData'])) { 
        $errors[] = "Anda harus menyetujui pernyataan konfirmasi.";
    }

    // 4. Proses Upload Bukti Pembayaran
    if (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] == UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['bukti_pembayaran']['tmp_name'];
        $fileName = $_FILES['bukti_pembayaran']['name'];
        $fileSize = $_FILES['bukti_pembayaran']['size'];
        $fileNameCmps = explode(".", $fileName);
        $fileExtension = strtolower(end($fileNameCmps));

        $cleanFileName = preg_replace("/[^A-Za-z0-9\-_.]/", '', basename($fileNameCmps[0]));
        // Membuat nama file lebih unik dengan ID donatur dan timestamp
        $namaFileBuktiPembayaran = "bukti_" . $donatur_id . '_' . time() . '_' . $cleanFileName . '.' . $fileExtension;

        $uploadFileDir = __DIR__ . '/uploads/bukti_pembayaran/'; 
        $dest_path = $uploadFileDir . $namaFileBuktiPembayaran; // Simpan path tujuan

        $allowedfileExtensions = ['jpg', 'jpeg', 'png', 'gif', 'pdf'];
        if (in_array($fileExtension, $allowedfileExtensions)) {
            if ($fileSize < 2000000) { // 2MB
                if (!move_uploaded_file($fileTmpPath, $dest_path)) {
                    $errors[] = 'Gagal memindahkan file bukti pembayaran. Pastikan folder uploads/bukti_pembayaran/ writable.';
                    $namaFileBuktiPembayaran = null; 
                }
            } else {
                $errors[] = 'Ukuran file bukti pembayaran terlalu besar. Maksimal 2MB.';
                $namaFileBuktiPembayaran = null;
            }
        } else {
            $errors[] = 'Tipe file bukti pembayaran tidak valid. Hanya JPG, JPEG, PNG, GIF, PDF yang diizinkan.';
            $namaFileBuktiPembayaran = null;
        }
    } elseif (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] == UPLOAD_ERR_NO_FILE) {
        $errors[] = 'Bukti pembayaran wajib diunggah.';
    } elseif (isset($_FILES['bukti_pembayaran']) && $_FILES['bukti_pembayaran']['error'] != UPLOAD_ERR_OK) {
        $errors[] = 'Terjadi kesalahan saat mengupload bukti pembayaran. Kode Error: ' . $_FILES['bukti_pembayaran']['error'];
    }
    
    if (empty($namaFileBuktiPembayaran) && (!isset($_FILES['bukti_pembayaran']) || (isset($_FILES['bukti_pembayaran']['error']) && $_FILES['bukti_pembayaran']['error'] == UPLOAD_ERR_NO_FILE)) ) {
         if (!in_array('Bukti pembayaran wajib diunggah.', $errors)) { 
            $errors[] = 'Bukti pembayaran wajib diunggah.';
         }
    }

    if (!empty($errors)) {
        $_SESSION['error_message_donasi'] = implode("<br>", $errors);
        $_SESSION['form_data_donasi'] = $_POST; 
        header("Location: form_donasi.php?kampanye_id=" . $kampanye_id);
        exit();
    }

    // 5. Jika semua validasi dan upload berhasil, buat objek Donasi
    $donasi = new Donasi();
    $donasi->setKampanyeId((int)$kampanye_id);
    $donasi->setDonaturId($donatur_id);
    $donasi->setJumlahDonasi($jumlah_donasi);
    $donasi->setBuktiPembayaran($namaFileBuktiPembayaran); 
    $donasi->setPesanDonatur($pesan_donatur);

    // 6. Panggil metode create()
    $hasilCreate = $donasi->create();

    // 7. Berikan feedback
    if (is_numeric($hasilCreate) && $hasilCreate > 0) {
       $_SESSION['success_message_donasi'] = "Donasi Anda telah kami terima. Terima kasih atas kebaikan Anda! Kami akan segera memverifikasinya.";
        unset($_SESSION['form_data_donasi']); 
        header("Location: detail_kampanye_publik.php?id=" . $kampanye_id . "&status=sukses_donasi"); 
        exit();
    } else {
        $_SESSION['error_message_donasi'] = "Gagal mengirim informasi donasi: " . (is_string($hasilCreate) ? $hasilCreate : "Terjadi kesalahan tidak diketahui.");
        $_SESSION['form_data_donasi'] = $_POST;
        if ($namaFileBuktiPembayaran && isset($dest_path) && file_exists($dest_path)) {
            @unlink($dest_path);
        }
        header("Location: form_donasi.php?kampanye_id=" . $kampanye_id);
        exit();
    }

} else {
    $_SESSION['error_message'] = "Metode request tidak valid.";
    header("Location: index.php"); 
    exit();
}
?>
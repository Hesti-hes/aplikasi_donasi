<?php
session_start();

// 1. Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login_admin.php");
    exit();
}

// Include kelas yang diperlukan
require_once __DIR__ . '/../app/classes/Donasi.php';
require_once __DIR__ . '/../app/classes/Kampanye.php';

// 2. Validasi parameter GET (id_donasi dan aksi)
if (!isset($_GET['id_donasi']) || !is_numeric($_GET['id_donasi']) || !isset($_GET['aksi'])) {
    $_SESSION['error_message_verifikasi'] = "Permintaan tidak valid atau parameter tidak lengkap.";
    header("Location: verifikasi_donasi.php");
    exit();
}

$id_donasi = (int)$_GET['id_donasi'];
$aksi = strtolower(trim($_GET['aksi'])); // 'setujui' atau 'tolak'

// 3. Ambil objek donasi yang akan diverifikasi
$donasi = Donasi::findById($id_donasi);

if (!$donasi) {
    $_SESSION['error_message_verifikasi'] = "Donasi dengan ID " . htmlspecialchars($id_donasi) . " tidak ditemukan.";
    header("Location: verifikasi_donasi.php");
    exit();
}

// Hanya proses jika statusnya masih 'menunggu_persetujuan'
if ($donasi->getStatusDonasi() !== 'menunggu_persetujuan') {
    $_SESSION['warning_message_verifikasi'] = "Donasi dengan ID " . htmlspecialchars($id_donasi) . " sudah pernah diproses (Status: " . htmlspecialchars($donasi->getStatusDonasi()) . ").";
    header("Location: verifikasi_donasi.php");
    exit();
}

// 4. Proses berdasarkan aksi
if ($aksi == 'setujui') {
    $hasilUpdateStatus = $donasi->updateStatus('disetujui'); // Objek $donasi diupdate statusnya di sini
    if ($hasilUpdateStatus === true) {
        // Jika status donasi berhasil diupdate menjadi 'disetujui',
        // update juga dana_terkumpul di kampanye terkait.
        $kampanye = Kampanye::findById($donasi->getKampanyeId());
        if ($kampanye) {
            $danaSebelumnya = $kampanye->getDanaTerkumpul();
            $danaBaru = $danaSebelumnya + $donasi->getJumlahDonasi();
            $kampanye->setDanaTerkumpul($danaBaru);
            
            $hasilUpdateKampanye = $kampanye->update(); 
            
            if ($hasilUpdateKampanye === true) {
                $_SESSION['success_message_verifikasi'] = "Donasi berhasil disetujui dan dana kampanye telah diperbarui.";
            } else {
                // Status donasi sudah 'disetujui', tapi update dana kampanye gagal
                // Ini kondisi yang perlu diwaspadai, mungkin perlu rollback status donasi atau notifikasi khusus
                $_SESSION['error_message_verifikasi'] = "Donasi (ID: " . $donasi->getId() . ") berhasil disetujui, TETAPI GAGAL memperbarui dana terkumpul pada kampanye. Silakan periksa manual. Error: " . (is_string($hasilUpdateKampanye) ? $hasilUpdateKampanye : "Tidak diketahui");
            }
        } else {
            $_SESSION['error_message_verifikasi'] = "Donasi (ID: " . $donasi->getId() . ") berhasil disetujui, TETAPI kampanye terkait tidak ditemukan untuk update dana.";
        }
    } else {
        $_SESSION['error_message_verifikasi'] = "Gagal menyetujui donasi (ID: " . $donasi->getId() . "). Error: " . (is_string($hasilUpdateStatus) ? $hasilUpdateStatus : "Tidak diketahui");
    }

} elseif ($aksi == 'tolak') {
    $hasilUpdateStatus = $donasi->updateStatus('ditolak');
    if ($hasilUpdateStatus === true) {
        $_SESSION['success_message_verifikasi'] = "Donasi berhasil DITOLAK.";
    } else {
        $_SESSION['error_message_verifikasi'] = "Gagal menolak donasi (ID: " . $donasi->getId() . "). Error: " . (is_string($hasilUpdateStatus) ? $hasilUpdateStatus : "Tidak diketahui");
    }

} else {
    $_SESSION['error_message_verifikasi'] = "Aksi tidak dikenal: '" . htmlspecialchars($aksi) . "'.";
}

// 5. Redirect kembali ke halaman verifikasi donasi
header("Location: verifikasi_donasi.php");
exit();

?>
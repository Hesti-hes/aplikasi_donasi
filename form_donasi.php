<?php
session_start();

// 1. Pastikan donatur sudah login
if (!isset($_SESSION['donatur_id'])) {
    $_SESSION['error_message'] = "Anda harus login terlebih dahulu untuk dapat berdonasi.";
    header("Location: login_donatur.php?redirect=form_donasi.php" . (isset($_GET['kampanye_id']) ? "&kampanye_id=" . $_GET['kampanye_id'] : ''));
    exit();
}

require_once __DIR__ . '/app/classes/Kampanye.php';
require_once __DIR__ . '/app/classes/RekeningTujuan.php';

$kampanye = null;
$rekening_tujuan_list = [];
$error_page_message = null; // Pesan error spesifik untuk halaman ini

// 2. Ambil dan validasi kampanye_id dari URL
if (!isset($_GET['kampanye_id']) || !is_numeric($_GET['kampanye_id'])) {
    $error_page_message = "Permintaan tidak valid. ID Kampanye tidak ditemukan atau format salah.";
} else {
    $kampanye_id = (int)$_GET['kampanye_id'];
    $kampanye = Kampanye::findById($kampanye_id);

    if (!$kampanye) {
        $error_page_message = "Kampanye dengan ID " . htmlspecialchars($kampanye_id) . " tidak ditemukan.";
    } elseif ($kampanye->getStatusKampanye() !== 'aktif') {
        $error_page_message = "Maaf, penggalangan dana untuk kampanye '" . htmlspecialchars($kampanye->getJudul()) . "' sudah tidak aktif.";
        $kampanye = null; // Jangan proses lebih lanjut jika tidak aktif
    } else {
        // 3. Ambil daftar rekening tujuan
        $rekening_tujuan_list = RekeningTujuan::getAllRekening();
        if (empty($rekening_tujuan_list)) {
            $error_page_message = "Belum ada rekening tujuan yang tersedia untuk donasi saat ini. Silakan hubungi admin.";
        }
    }
}

// Fungsi helper jika belum ada
if (!function_exists('formatRupiah')) {
    function formatRupiah($angka) {
        return "Rp " . number_format($angka, 0, ',', '.');
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulir Donasi - <?php echo $kampanye ? htmlspecialchars($kampanye->getJudul()) : 'Donasi'; ?> - DonasiBencana.ID</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f9f9f9; }
        .form-container { margin-top: 30px; margin-bottom: 50px; }
        .form-card { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .campaign-info-summary { background-color: #eef; padding: 15px; border-radius: 5px; margin-bottom: 20px; border-left: 5px solid #007bff; }
        .rekening-info { margin-bottom: 15px; padding: 10px; border: 1px solid #eee; border-radius: 5px; }
        .rekening-info strong { display: block; font-size: 1.1em; color: #0056b3; }
        .rekening-info span { color: #555; }
        .navbar-brand img { max-height: 40px; margin-right: 10px; }
        .footer { background-color: #343a40; color: white; padding: 20px 0; text-align: center; margin-top: 40px; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">DonasiBencana.ID</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="index.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php#daftar-kampanye">Kampanye Lain</a></li>
                    <?php if (isset($_SESSION['donatur_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="donatur/dashboard.php">Dashboard Saya</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                    <?php else: ?>
                        <li class="nav-item"><a class="nav-link" href="login_donatur.php">Login</a></li>
                        <li class="nav-item"><a class="nav-link" href="register_donatur.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container form-container">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if ($error_page_message): ?>
                    <div class="alert alert-danger text-center" role="alert">
                        <?php echo htmlspecialchars($error_page_message); ?>
                        <p class="mt-3"><a href="index.php" class="btn btn-primary">Kembali ke Daftar Kampanye</a></p>
                    </div>
                <?php elseif ($kampanye): // Hanya tampilkan form jika kampanye valid dan ditemukan ?>
                    <div class="form-card">
                        <h2 class="mb-4 text-center">Formulir Donasi</h2>
                        
                        <div class="campaign-info-summary">
                            <h5>Anda berdonasi untuk kampanye:</h5>
                            <h4><strong><?php echo htmlspecialchars($kampanye->getJudul()); ?></strong></h4>
                            <p>Target: <?php echo formatRupiah($kampanye->getTargetDonasi()); ?> | Terkumpul: <?php echo formatRupiah($kampanye->getDanaTerkumpul()); ?></p>
                        </div>

                        <h4 class="mt-4 mb-3">Rekening Tujuan Donasi:</h4>
                        <?php if (empty($rekening_tujuan_list)): ?>
                            <div class="alert alert-warning">Belum ada rekening tujuan yang terdaftar. Silakan hubungi admin.</div>
                        <?php else: ?>
                            <?php foreach ($rekening_tujuan_list as $rekening): ?>
                                <div class="rekening-info">
                                    <strong><?php echo htmlspecialchars($rekening->getNamaBank()); ?></strong>
                                    <span>No. Rek: <?php echo htmlspecialchars($rekening->getNomorRekening()); ?></span><br>
                                    <span>Atas Nama: <?php echo htmlspecialchars($rekening->getAtasNama()); ?></span>
                                </div>
                            <?php endforeach; ?>
                            <p class="mt-3 mb-3 text-muted"><small>Silakan transfer ke salah satu rekening di atas. Pastikan Anda menyimpan bukti transfer.</small></p>
                        <?php endif; ?>
                        
                        <hr>

                        <?php
                        if (isset($_SESSION['error_message_donasi'])) {
                            echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['error_message_donasi']) . '</div>';
                            unset($_SESSION['error_message_donasi']);
                        }
                        $form_data = $_SESSION['form_data_donasi'] ?? [];
                        unset($_SESSION['form_data_donasi']);
                        ?>

                        <form action="proses_kirim_donasi.php" method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="kampanye_id" value="<?php echo $kampanye->getId(); ?>">
                            
                            <div class="mb-3">
                                <label for="jumlah_donasi" class="form-label">Jumlah Donasi (Rp) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="jumlah_donasi" name="jumlah_donasi" 
                                value="<?php echo htmlspecialchars($form_data['jumlah_donasi'] ?? ''); ?>" 
                                placeholder="Contoh: 50000" required min="10000">
                                <div class="form-text">Minimal donasi Rp 10.000.</div>
                            </div>

                            <div class="mb-3">
                                <label for="bukti_pembayaran" class="form-label">Upload Bukti Pembayaran <span class="text-danger">*</span></label>
                                <input type="file" class="form-control" id="bukti_pembayaran" name="bukti_pembayaran" 
                                accept="image/jpeg, image/png, image/gif, application/pdf" required>
                                <div class="form-text">Format: JPG, PNG, GIF, PDF. Maksimal: 2MB.</div>
                            </div>

                            <div class="mb-3">
                                <label for="pesan_donatur" class="form-label">Pesan untuk Korban/Panitia (Opsional)</label>
                                <textarea class="form-control" id="pesan_donatur" name="pesan_donatur" rows="3"><?php echo htmlspecialchars($form_data['pesan_donatur'] ?? ''); ?></textarea>
                            </div>

                            <div class="form-check mb-3">
                                <input class="form-check-input" type="checkbox" value="setuju" id="konfirmasiData" name="konfirmasiData" required>
                                <label class="form-check-label" for="konfirmasiData">
                                    Saya menyatakan bahwa data yang saya masukkan adalah benar dan saya telah melakukan transfer sesuai jumlah donasi.
                                </label>
                            </div>

                            <div class="d-grid">
                                <button type="submit" class="btn btn-success btn-lg" <?php if(empty($rekening_tujuan_list) && !$error_page_message) echo 'disabled'; ?>>Kirim Informasi Donasi</button>
                            </div>
                        </form>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <footer class="footer">
        <div class="container">
            <p>&copy; <?php echo date("Y"); ?> Aplikasi Donasi Bencana. Semua Hak Dilindungi.</p>
        </div>
    </footer>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
?>
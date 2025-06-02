<?php
session_start(); 

require_once __DIR__ . '/app/classes/Kampanye.php'; // Memuat kelas Kampanye

// Inisialisasi variabel
$kampanye = null;
$error_message_page = null; // Menggunakan nama variabel yang berbeda untuk error halaman ini
$pesan_sukses_donasi = null;

// Ambil dan tampilkan pesan sukses donasi dari sesi jika ada via GET parameter
if (isset($_GET['status']) && $_GET['status'] == 'sukses_donasi') {
    if (isset($_SESSION['success_message_donasi'])) {
        $pesan_sukses_donasi = $_SESSION['success_message_donasi'];
        unset($_SESSION['success_message_donasi']); // Hapus pesan setelah diambil
    }
}

// Ambil dan validasi kampanye_id dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $error_message_page = "Permintaan tidak valid. ID Kampanye tidak ditemukan atau format salah.";
} else {
    $kampanye_id = (int)$_GET['id'];
    $kampanye = Kampanye::findById($kampanye_id); // Panggil metode statis dari kelas Kampanye

    if (!$kampanye) {
        $error_message_page = "Kampanye dengan ID " . htmlspecialchars($kampanye_id) . " tidak ditemukan.";
    }
    // Anda bisa menambahkan logika di sini jika ingin mengecek status kampanye, misalnya:
    // elseif ($kampanye->getStatusKampanye() !== 'aktif' && !isset($_GET['preview'])) { 
    //     $error_message_page = "Maaf, penggalangan dana untuk kampanye '" . htmlspecialchars($kampanye->getJudul()) . "' sudah tidak aktif.";
    //     $kampanye = null; // Jangan tampilkan detailnya jika tidak aktif dan bukan mode preview
    // }
}

// Fungsi helper (idealnya ini ada di file terpisah yang di-include sekali)
if (!function_exists('formatRupiah')) {
    function formatRupiah($angka) {
        return "Rp " . number_format($angka, 0, ',', '.');
    }
}
if (!function_exists('hitungPersentase')) {
    function hitungPersentase($terkumpul, $target) {
        if ($target <= 0) return 0;
        $persen = ($terkumpul / $target) * 100;
        return round(min($persen, 100));
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $kampanye ? htmlspecialchars($kampanye->getJudul()) : 'Detail Kampanye'; ?> - DonasiBencana.ID</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f9f9f9; }
        .campaign-image-detail { width: 100%; max-height: 450px; object-fit: cover; border-radius: 8px; margin-bottom: 20px; }
        .campaign-content { background-color: #fff; padding: 30px; border-radius: 8px; box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
        .progress-info span { font-weight: 500; }
        .btn-donasi-detail { font-size: 1.1rem; padding: 10px 20px; }
        .info-box { background-color: #e9ecef; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
        .info-box h5 { margin-bottom: 5px; color: #007bff; }
        .info-box p { margin-bottom: 0; font-size: 1.2rem; font-weight: bold; }
        .description-content h4, .description-content h5, .description-content h6 { margin-top: 20px; margin-bottom: 10px; color: #333; }
        .description-content p { line-height: 1.7; color: #555; }
        .status-badge { font-size: 1rem; }
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
                        <li class="nav-item"><a class="nav-link" href="donatur/dashboard.php">Dashboard Donatur</a></li>
                        <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                    <?php elseif (isset($_SESSION['admin_id'])): ?>
                       <li class="nav-item"><a class="nav-link" href="admin/dashboard.php">Dashboard Admin</a></li>
                       <li class="nav-item"><a class="nav-link" href="logout.php">Logout</a></li>
                   <?php else: ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownLogin" role="button" data-bs-toggle="dropdown" aria-expanded="false">Login</a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownLogin">
                            <li><a class="dropdown-item" href="login_donatur.php">Login Donatur</a></li>
                            <li><a class="dropdown-item" href="login_admin.php">Login Admin</a></li>
                        </ul>
                    </li>
                    <li class="nav-item"><a class="nav-link" href="register_donatur.php">Register Donatur</a></li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5 mb-5">
    <?php if ($pesan_sukses_donasi): ?>
        <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
            <?php echo htmlspecialchars($pesan_sukses_donasi); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if ($error_message_page): ?>
        <div class="alert alert-danger text-center" role="alert">
            <?php echo htmlspecialchars($error_message_page); ?>
            <p class="mt-3"><a href="index.php" class="btn btn-primary">Kembali ke Daftar Kampanye</a></p>
        </div>
    <?php elseif ($kampanye): ?>
        <div class="row">
            <div class="col-lg-7">
                <div class="campaign-content mb-4">
                    <?php if ($kampanye->getGambarKampanye()): ?>
                        <img src="uploads/gambar_kampanye/<?php echo htmlspecialchars($kampanye->getGambarKampanye()); ?>" 
                        alt="<?php echo htmlspecialchars($kampanye->getJudul()); ?>" class="campaign-image-detail img-fluid">
                    <?php else: ?>
                        <img src="uploads/gambar_kampanye/default_campaign.jpg" alt="Gambar Default Kampanye" class="campaign-image-detail img-fluid">
                    <?php endif; ?>
                    
                    <h1 class="mt-3 mb-3"><?php echo htmlspecialchars($kampanye->getJudul()); ?></h1>
                    <div class="description-content">
                        <?php echo nl2br(htmlspecialchars($kampanye->getDeskripsiLengkap())); // nl2br untuk menjaga format paragraf ?>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="campaign-content position-sticky" style="top: 100px;">
                    <h4>Detail Pendanaan</h4>
                    <hr>
                    <div class="info-box">
                        <h5>Dana Terkumpul</h5>
                        <p><?php echo formatRupiah($kampanye->getDanaTerkumpul()); ?></p>
                    </div>
                    <div class="info-box">
                        <h5>Target Donasi</h5>
                        <p><?php echo formatRupiah($kampanye->getTargetDonasi()); ?></p>
                    </div>
                    
                    <?php $persentase = hitungPersentase($kampanye->getDanaTerkumpul(), $kampanye->getTargetDonasi()); ?>
                    <div class="progress mt-1 mb-2" style="height: 20px;">
                        <div class="progress-bar bg-success progress-bar-striped progress-bar-animated" role="progressbar" 
                        style="width: <?php echo $persentase; ?>%;" 
                        aria-valuenow="<?php echo $persentase; ?>" aria-valuemin="0" aria-valuemax="100">
                        <?php echo $persentase; ?>%
                    </div>
                </div>
                <p class="text-center mb-3"><small class="text-muted"><?php echo $persentase; ?>% dari target tercapai</small></p>
                
                <div class="row text-center mb-3">
                    <div class="col">
                        <small class="text-muted">Dimulai: <?php echo $kampanye->getTanggalMulai() ? date('d M Y', strtotime($kampanye->getTanggalMulai())) : '-'; ?></small>
                    </div>
                    <div class="col">
                        <small class="text-muted">Berakhir: <?php echo $kampanye->getTanggalSelesai() ? date('d M Y', strtotime($kampanye->getTanggalSelesai())) : '-'; ?></small>
                    </div>
                </div>

                <?php 
                $status = $kampanye->getStatusKampanye();
                $badge_class = 'bg-secondary';
                $status_text = ucfirst($status);
                if ($status == 'aktif') $badge_class = 'bg-success';
                else if ($status == 'selesai') $badge_class = 'bg-info text-dark';
                else if ($status == 'ditutup') $badge_class = 'bg-danger';
                ?>
                <p class="text-center">Status Kampanye: <span class="badge <?php echo $badge_class; ?> status-badge"><?php echo $status_text; ?></span></p>
                
                <?php if ($kampanye->getStatusKampanye() == 'aktif'): ?>
                    <div class="d-grid gap-2 mt-4">
                        <a href="form_donasi.php?kampanye_id=<?php echo $kampanye->getId(); ?>" class="btn btn-success btn-donasi-detail btn-lg">
                            Donasi Sekarang
                        </a>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning text-center" role="alert">
                        Penggalangan dana untuk kampanye ini telah <?php echo strtolower($status_text); ?>.
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
<?php endif; ?>
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
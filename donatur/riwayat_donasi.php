<?php
session_start(); // Mulai sesi di awal

// Cek apakah donatur sudah login
if (!isset($_SESSION['donatur_id'])) {
    header("Location: ../login_donatur.php"); // Arahkan ke login_donatur.php di root folder
    exit();
}

// Include kelas yang diperlukan
require_once __DIR__ . '/../app/classes/Donasi.php';
require_once __DIR__ . '/../app/classes/Kampanye.php'; // Untuk mendapatkan judul kampanye

// Ambil ID donatur dari sesi
$donatur_id = $_SESSION['donatur_id'];

// Ambil semua data donasi untuk donatur ini, diurutkan dari yang terbaru
$riwayat_donasi = Donasi::findAllByDonaturId($donatur_id, 'DESC');

$donatur_nama = $_SESSION['donatur_nama_lengkap'] ?? 'Donatur';
$nomor = 1; // Untuk penomoran tabel

// Fungsi untuk format rupiah (bisa dipindah ke file helper nanti)
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
    <title>Riwayat Donasi Saya - DonasiBencana.ID</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet"> <style>
        body { background-color: #f4f7f6; }
        .content-area { padding-top: 20px; padding-bottom: 40px; }
        .table-actions { white-space: nowrap; }
        .img-thumbnail-bukti-riwayat { max-width: 100px; max-height: 70px; object-fit: cover; }
         /* Modal Styling (jika ingin menggunakan modal untuk bukti) */
        .modal-img { max-width: 100%; height: auto; display: block; margin: 0 auto; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success"> <div class="container">
            <a class="navbar-brand" href="../index.php">DonasiBencana.ID</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#donaturNavbar" aria-controls="donaturNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="donaturNavbar">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="riwayat_donasi.php">Riwayat Donasi</a></li>
                    <li class="nav-item"><a class="nav-link" href="profil_donatur.php">Profil Saya</a></li> </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            Halo, <?php echo htmlspecialchars($donatur_nama); ?>!
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-light" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container content-area">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Riwayat Donasi Saya</h1>
        </div>

        <?php
        // Menampilkan pesan sukses jika ada (misalnya setelah donasi diverifikasi)
        if (isset($_SESSION['success_message_riwayat'])) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . 
                 htmlspecialchars($_SESSION['success_message_riwayat']) . 
                 '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            unset($_SESSION['success_message_riwayat']);
        }
        ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">ID Donasi</th>
                        <th scope="col">Kampanye Tujuan</th>
                        <th scope="col">Jumlah Donasi</th>
                        <th scope="col">Tanggal Donasi</th>
                        <th scope="col">Status</th>
                        <th scope="col">Bukti Pembayaran</th>
                        <th scope="col">Pesan Anda</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($riwayat_donasi)): ?>
                        <tr>
                            <td colspan="8" class="text-center">Anda belum memiliki riwayat donasi. <a href="../index.php#daftar-kampanye">Mulai berdonasi sekarang!</a></td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($riwayat_donasi as $donasi): ?>
                            <?php
                                // Ambil judul kampanye
                                $kampanye_detail = Kampanye::findById($donasi->getKampanyeId());
                                $judul_kampanye = $kampanye_detail ? $kampanye_detail->getJudul() : 'Kampanye Tidak Ditemukan';
                            ?>
                            <tr>
                                <th scope="row"><?php echo $nomor++; ?></th>
                                <td><?php echo $donasi->getId(); ?></td>
                                <td>
                                    <a href="../detail_kampanye_publik.php?id=<?php echo $donasi->getKampanyeId(); ?>" title="Lihat Detail Kampanye">
                                        <?php echo htmlspecialchars($judul_kampanye); ?>
                                    </a>
                                </td>
                                <td><?php echo formatRupiah($donasi->getJumlahDonasi()); ?></td>
                                <td><?php echo htmlspecialchars($donasi->getTanggalDonasi() ? date('d M Y H:i', strtotime($donasi->getTanggalDonasi())) : '-'); ?></td>
                                <td>
                                    <?php 
                                    $status = $donasi->getStatusDonasi();
                                    $badge_class = 'bg-secondary'; // Default
                                    if ($status == 'menunggu_persetujuan') $badge_class = 'bg-warning text-dark';
                                    else if ($status == 'disetujui') $badge_class = 'bg-success';
                                    else if ($status == 'ditolak') $badge_class = 'bg-danger';
                                    echo '<span class="badge ' . $badge_class . '">' . ucfirst(str_replace('_', ' ', $status)) . '</span>';
                                    ?>
                                </td>
                                <td>
                                    <?php if ($donasi->getBuktiPembayaran()): ?>
                                        <a href="../uploads/bukti_pembayaran/<?php echo htmlspecialchars($donasi->getBuktiPembayaran()); ?>" target="_blank"
                                           title="Lihat Bukti Pembayaran" class="btn btn-sm btn-outline-info">
                                            <span data-feather="image"></span> Lihat
                                        </a>
                                    <?php else: ?>
                                        <small class="text-muted">Tidak ada bukti</small>
                                    <?php endif; ?>
                                </td>
                                <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($donasi->getPesanDonatur()); ?>">
                                    <?php echo htmlspecialchars($donasi->getPesanDonatur() ?: '-'); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <footer class="footer mt-auto py-3 bg-dark"> <div class="container text-center">
            <span class="text-light">&copy; <?php echo date("Y"); ?> Aplikasi Donasi Bencana.</span>
        </div>
    </footer>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script>
      feather.replace();
    </script>
</body>
</html>
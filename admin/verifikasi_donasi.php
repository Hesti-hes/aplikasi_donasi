<?php
session_start(); // Mulai sesi di awal

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login_admin.php");
    exit();
}

// Include kelas yang diperlukan
require_once __DIR__ . '/../app/classes/Donasi.php';
require_once __DIR__ . '/../app/classes/Kampanye.php'; // Untuk mendapatkan judul kampanye
require_once __DIR__ . '/../app/classes/Donatur.php';   // Untuk mendapatkan nama donatur

// Ambil semua donasi yang menunggu persetujuan, diurutkan dari yang terbaru
$donasi_pending = Donasi::findAllByStatus('menunggu_persetujuan', 'DESC');

$admin_nama = $_SESSION['admin_nama_lengkap'] ?? 'Administrator';
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
    <title>Verifikasi Donasi - Dashboard Admin</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; }
        .content-area { padding-top: 20px; padding-bottom: 40px; }
        .table-actions { white-space: nowrap; }
        .img-thumbnail-bukti { max-width: 150px; max-height: 100px; object-fit: cover; cursor: pointer; }
        /* Modal Styling */
        .modal-img { max-width: 100%; height: auto; display: block; margin: 0 auto; }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="dashboard.php">Admin Panel</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="kelola_kampanye.php">Kelola Kampanye</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="verifikasi_donasi.php">Verifikasi Donasi</a></li>
                    <li class="nav-item"><a class="nav-link" href="kelola_rekening.php">Kelola Rekening</a></li>
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            Login sebagai: <?php echo htmlspecialchars($admin_nama); ?>
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-danger" href="../logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container content-area">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Verifikasi Donasi</h1>
        </div>

        <?php
        // Menampilkan pesan sukses atau error dari proses verifikasi sebelumnya
        if (isset($_SESSION['success_message_verifikasi'])) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . 
                 htmlspecialchars($_SESSION['success_message_verifikasi']) . 
                 '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            unset($_SESSION['success_message_verifikasi']);
        }
        if (isset($_SESSION['error_message_verifikasi'])) {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . 
                 htmlspecialchars($_SESSION['error_message_verifikasi']) . 
                 '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            unset($_SESSION['error_message_verifikasi']);
        }
        ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">ID Donasi</th>
                        <th scope="col">Kampanye</th>
                        <th scope="col">Donatur</th>
                        <th scope="col">Jumlah</th>
                        <th scope="col">Bukti Bayar</th>
                        <th scope="col">Pesan</th>
                        <th scope="col">Tgl Donasi</th>
                        <th scope="col" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($donasi_pending)): ?>
                        <tr>
                            <td colspan="9" class="text-center">Tidak ada donasi yang menunggu persetujuan saat ini.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($donasi_pending as $donasi): ?>
                            <?php
                                // Ambil detail kampanye dan donatur
                                // Catatan: Ini bisa kurang efisien jika banyak donasi (N+1 query problem).
                                // Di masa depan, bisa dioptimalkan dengan JOIN di query Donasi::findAllByStatus()
                                // atau dengan mengambil semua kampanye/donatur sekali dan mencocokkan via array.
                                $kampanye_detail = Kampanye::findById($donasi->getKampanyeId());
                                $judul_kampanye = $kampanye_detail ? $kampanye_detail->getJudul() : 'Kampanye Dihapus/Tidak Ada';
                                
                                $donatur_detail = Donatur::findById($donasi->getDonaturId());
                                $nama_donatur = $donatur_detail ? $donatur_detail->getNamaLengkap() : 'Donatur Dihapus/Tidak Ada';
                            ?>
                            <tr>
                                <th scope="row"><?php echo $nomor++; ?></th>
                                <td><?php echo $donasi->getId(); ?></td>
                                <td><?php echo htmlspecialchars($judul_kampanye); ?></td>
                                <td><?php echo htmlspecialchars($nama_donatur); ?></td>
                                <td><?php echo formatRupiah($donasi->getJumlahDonasi()); ?></td>
                                <td>
                                    <?php if ($donasi->getBuktiPembayaran()): ?>
                                        <a href="../uploads/bukti_pembayaran/<?php echo htmlspecialchars($donasi->getBuktiPembayaran()); ?>" target="_blank">
                                            <img src="../uploads/bukti_pembayaran/<?php echo htmlspecialchars($donasi->getBuktiPembayaran()); ?>" 
                                                 alt="Bukti Bayar" class="img-thumbnail-bukti"
                                                 onerror="this.alt='Gagal memuat bukti'; this.parentNode.innerHTML = 'Lihat Bukti (' + this.alt + ')';">
                                        </a>
                                        <?php else: ?>
                                        <small class="text-muted">Tidak ada bukti</small>
                                    <?php endif; ?>
                                </td>
                                <td style="max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;" title="<?php echo htmlspecialchars($donasi->getPesanDonatur()); ?>">
                                    <?php echo htmlspecialchars($donasi->getPesanDonatur() ?: '-'); ?>
                                </td>
                                <td><?php echo htmlspecialchars($donasi->getTanggalDonasi() ? date('d M Y H:i', strtotime($donasi->getTanggalDonasi())) : '-'); ?></td>
                                <td class="text-center table-actions">
                                    <a href="proses_verifikasi_donasi.php?id_donasi=<?php echo $donasi->getId(); ?>&aksi=setujui" 
                                       class="btn btn-sm btn-success" title="Setujui Donasi"
                                       onclick="return confirm('Apakah Anda yakin ingin MENYETUJUI donasi ini?');">
                                        <span data-feather="check-circle"></span> Setujui
                                    </a>
                                    <a href="proses_verifikasi_donasi.php?id_donasi=<?php echo $donasi->getId(); ?>&aksi=tolak" 
                                       class="btn btn-sm btn-danger mt-1" title="Tolak Donasi"
                                       onclick="return confirm('Apakah Anda yakin ingin MENOLAK donasi ini?');">
                                        <span data-feather="x-circle"></span> Tolak
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
      <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
          <div class="modal-header">
            <h5 class="modal-title" id="imageModalLabel">Bukti Pembayaran</h5>
            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
          </div>
          <div class="modal-body text-center">
            <img src="" id="modalImageSrc" class="modal-img" alt="Bukti Pembayaran Detail">
          </div>
        </div>
      </div>
    </div>


    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script>
      feather.replace();

      // Script untuk menampilkan gambar di modal saat thumbnail diklik (Opsional)
      const imageModal = document.getElementById('imageModal');
      if (imageModal) {
          imageModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget; // Tombol/gambar yang memicu modal
            const imageUrl = button.tagName === 'IMG' ? button.src : button.href; // Ambil URL gambar
            const modalImage = imageModal.querySelector('#modalImageSrc');
            modalImage.src = imageUrl;

            const imageTitle = button.alt || 'Bukti Pembayaran';
            const modalTitle = imageModal.querySelector('#imageModalLabel');
            modalTitle.textContent = imageTitle;
          });

          // Atur event listener untuk semua thumbnail bukti pembayaran
          document.querySelectorAll('.img-thumbnail-bukti').forEach(img => {
              img.setAttribute('data-bs-toggle', 'modal');
              img.setAttribute('data-bs-target', '#imageModal');
          });
      }
    </script>
</body>
</html>

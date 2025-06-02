<?php
session_start(); // Mulai sesi di awal

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login_admin.php");
    exit();
}

// Include kelas Kampanye
require_once __DIR__ . '/../app/classes/Kampanye.php';

// Ambil semua data kampanye
$daftar_kampanye = Kampanye::findAll(); // Kita panggil metode statis findAll()

$admin_nama = $_SESSION['admin_nama_lengkap'] ?? 'Administrator';
$nomor = 1; // Untuk penomoran tabel

// Fungsi untuk format rupiah (bisa dipindah ke file helper nanti)
function formatRupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kampanye - Dashboard Admin</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; }
        .content-area { padding-top: 20px; padding-bottom: 40px; }
        .table-actions { white-space: nowrap; }
        .img-thumbnail-custom { max-width: 100px; max-height: 70px; object-fit: cover; }
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
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="kelola_kampanye.php">Kelola Kampanye</a></li>
                    <li class="nav-item"><a class="nav-link" href="verifikasi_donasi.php">Verifikasi Donasi</a></li>
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
            <h1 class="h2">Kelola Kampanye</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <a href="tambah_kampanye.php" class="btn btn-sm btn-outline-primary">
                    <span data-feather="plus-circle" class="align-text-bottom"></span>
                    Tambah Kampanye Baru
                </a>
            </div>
        </div>

        <?php
        // Menampilkan pesan sukses jika ada dari proses_tambah_kampanye.php atau proses lainnya
        if (isset($_SESSION['success_message_kampanye'])) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . 
                 htmlspecialchars($_SESSION['success_message_kampanye']) . 
                 '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            unset($_SESSION['success_message_kampanye']);
        }
        // Menampilkan pesan error jika ada
        if (isset($_SESSION['error_message_kampanye'])) {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . 
                 htmlspecialchars($_SESSION['error_message_kampanye']) . 
                 '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            unset($_SESSION['error_message_kampanye']);
        }
        ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">Gambar</th>
                        <th scope="col">Judul Kampanye</th>
                        <th scope="col">Target Donasi</th>
                        <th scope="col">Dana Terkumpul</th>
                        <th scope="col">Status</th>
                        <th scope="col">Tgl Mulai</th>
                        <th scope="col">Tgl Selesai</th>
                        <th scope="col" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftar_kampanye)): ?>
                        <tr>
                            <td colspan="9" class="text-center">Belum ada kampanye yang dibuat.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftar_kampanye as $kampanye): ?>
                            <tr>
                                <th scope="row"><?php echo $nomor++; ?></th>
                                <td>
                                    <?php if ($kampanye->getGambarKampanye()): ?>
                                        <img src="../uploads/gambar_kampanye/<?php echo htmlspecialchars($kampanye->getGambarKampanye()); ?>" 
                                             alt="<?php echo htmlspecialchars($kampanye->getJudul()); ?>" 
                                             class="img-thumbnail-custom">
                                    <?php else: ?>
                                        <small class="text-muted">Tidak ada gambar</small>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($kampanye->getJudul()); ?></td>
                                <td><?php echo formatRupiah($kampanye->getTargetDonasi()); ?></td>
                                <td><?php echo formatRupiah($kampanye->getDanaTerkumpul()); ?></td>
                                <td>
                                    <?php 
                                    $status = $kampanye->getStatusKampanye();
                                    $badge_class = 'bg-secondary';
                                    if ($status == 'aktif') $badge_class = 'bg-success';
                                    else if ($status == 'selesai') $badge_class = 'bg-info text-dark';
                                    else if ($status == 'ditutup') $badge_class = 'bg-danger';
                                    echo '<span class="badge ' . $badge_class . '">' . ucfirst($status) . '</span>';
                                    ?>
                                </td>
                                <td><?php echo htmlspecialchars($kampanye->getTanggalMulai() ? date('d M Y', strtotime($kampanye->getTanggalMulai())) : '-'); ?></td>
                                <td><?php echo htmlspecialchars($kampanye->getTanggalSelesai() ? date('d M Y', strtotime($kampanye->getTanggalSelesai())) : '-'); ?></td>
                                <td class="text-center table-actions">
                                    <a href="detail_kampanye.php?id=<?php echo $kampanye->getId(); ?>" class="btn btn-sm btn-info" title="Lihat Detail">
                                        <span data-feather="eye"></span>
                                    </a>
                                    <a href="edit_kampanye.php?id=<?php echo $kampanye->getId(); ?>" class="btn btn-sm btn-warning" title="Edit">
                                        <span data-feather="edit-2"></span>
                                    </a>
                                    <a href="proses_hapus_kampanye.php?id=<?php echo $kampanye->getId(); ?>" 
                                       class="btn btn-sm btn-danger" title="Hapus" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus kampanye ini: \'<?php echo htmlspecialchars(addslashes($kampanye->getJudul())); ?>\'? Tindakan ini tidak bisa dibatalkan.');">
                                        <span data-feather="trash-2"></span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script>
      feather.replace(); // Mengaktifkan ikon feather
    </script>
</body>
</html>
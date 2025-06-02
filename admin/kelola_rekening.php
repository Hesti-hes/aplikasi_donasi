<?php
session_start(); // Mulai sesi di awal

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login_admin.php");
    exit();
}

// Include kelas RekeningTujuan
require_once __DIR__ . '/../app/classes/RekeningTujuan.php';

// Ambil semua data rekening tujuan
$daftar_rekening = RekeningTujuan::getAllRekening(); // Kita panggil metode statis getAllRekening()

$admin_nama = $_SESSION['admin_nama_lengkap'] ?? 'Administrator';
$nomor = 1; // Untuk penomoran tabel
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Rekening Tujuan - Dashboard Admin</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; }
        .content-area { padding-top: 20px; padding-bottom: 40px; }
        .table-actions { white-space: nowrap; }
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
                    <li class="nav-item"><a class="nav-link" href="verifikasi_donasi.php">Verifikasi Donasi</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="kelola_rekening.php">Kelola Rekening</a></li>
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
            <h1 class="h2">Kelola Rekening Tujuan Donasi</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <a href="tambah_rekening.php" class="btn btn-sm btn-outline-primary">
                    <span data-feather="plus-circle" class="align-text-bottom"></span>
                    Tambah Rekening Baru
                </a>
            </div>
        </div>

        <?php
        // Menampilkan pesan sukses jika ada dari proses tambah/edit/hapus rekening
        if (isset($_SESSION['success_message_rekening'])) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . 
                 htmlspecialchars($_SESSION['success_message_rekening']) . 
                 '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            unset($_SESSION['success_message_rekening']);
        }
        // Menampilkan pesan error jika ada
        if (isset($_SESSION['error_message_rekening'])) {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . 
                 htmlspecialchars($_SESSION['error_message_rekening']) . 
                 '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            unset($_SESSION['error_message_rekening']);
        }
        ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover table-bordered">
                <thead class="table-dark">
                    <tr>
                        <th scope="col">#</th>
                        <th scope="col">ID</th>
                        <th scope="col">Nama Bank</th>
                        <th scope="col">Nomor Rekening</th>
                        <th scope="col">Atas Nama</th>
                        <th scope="col" class="text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($daftar_rekening)): ?>
                        <tr>
                            <td colspan="6" class="text-center">Belum ada rekening tujuan yang ditambahkan.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($daftar_rekening as $rekening): ?>
                            <tr>
                                <th scope="row"><?php echo $nomor++; ?></th>
                                <td><?php echo $rekening->getId(); ?></td>
                                <td><?php echo htmlspecialchars($rekening->getNamaBank()); ?></td>
                                <td><?php echo htmlspecialchars($rekening->getNomorRekening()); ?></td>
                                <td><?php echo htmlspecialchars($rekening->getAtasNama()); ?></td>
                                <td class="text-center table-actions">
                                    <a href="edit_rekening.php?id=<?php echo $rekening->getId(); ?>" class="btn btn-sm btn-warning" title="Edit">
                                        <span data-feather="edit-2"></span> Edit
                                    </a>
                                    <a href="proses_hapus_rekening.php?id=<?php echo $rekening->getId(); ?>" 
                                       class="btn btn-sm btn-danger" title="Hapus" 
                                       onclick="return confirm('Apakah Anda yakin ingin menghapus rekening ini: \'<?php echo htmlspecialchars(addslashes($rekening->getNamaBank() . ' - ' . $rekening->getNomorRekening())); ?>\'?');">
                                        <span data-feather="trash-2"></span> Hapus
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
      feather.replace();
    </script>
</body>
</html>
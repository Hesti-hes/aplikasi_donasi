<?php
session_start(); // Mulai sesi di awal

// Cek apakah admin sudah login, jika tidak, arahkan ke halaman login admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login_admin.php"); // Arahkan ke login_admin.php di root folder
    exit();
}

// Include kelas yang diperlukan
require_once __DIR__ . '/../app/classes/Kampanye.php';
require_once __DIR__ . '/../app/classes/Donasi.php'; // Pastikan path ini benar dan file Donasi.php ada

// Ambil informasi admin dari sesi
$admin_nama = $_SESSION['admin_nama_lengkap'] ?? 'Administrator';

// Ambil data dinamis untuk dashboard
$jumlah_kampanye_aktif = Kampanye::countByStatus('aktif');
$jumlah_donasi_pending = Donasi::countByStatus('menunggu_persetujuan'); 
// Anda bisa menambahkan pengambilan data lain di sini nanti, misal:
// $total_dana_terkumpul_semua_kampanye = ... (membutuhkan metode baru di kelas Kampanye atau Donasi)

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Aplikasi Donasi Bencana</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet"> 
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            background-color: #f4f7f6;
        }
        .sidebar {
            min-height: 100vh; 
            padding-top: 20px;
            background-color: #343a40;
            color: white;
        }
        .sidebar .nav-link { 
            color: #adb5bd;
            text-decoration: none;
            display: block;
            padding: 10px 15px;
        }
        .sidebar .nav-link:hover, .sidebar .nav-link.active {
            color: #ffffff;
            background-color: #495057;
        }
        .sidebar .sidebar-heading { 
            font-size: 0.9rem;
            text-transform: uppercase;
        }
        .content-area {
            padding-top: 20px;
            padding-left: 20px; 
        }
        .card .border-left-primary {
            border-left: .25rem solid #4e73df!important;
        }
        .card .border-left-success {
            border-left: .25rem solid #1cc88a!important;
        }
        .text-xs {
            font-size: .7rem;
        }
        .text-gray-300 {
            color: #dddfeb!important;
        }
        .text-gray-800 {
            color: #5a5c69!important;
        }
    </style>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                <div class="position-sticky pt-3">
                    <h5 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                        <span>ADMIN PANEL</span>
                    </h5>
                    <ul class="nav flex-column">
                        <li class="nav-item">
                            <a class="nav-link active" aria-current="page" href="dashboard.php">
                                <span data-feather="home" class="align-text-bottom"></span>
                                Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="kelola_kampanye.php">
                                <span data-feather="file-text" class="align-text-bottom"></span>
                                Kelola Kampanye
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="verifikasi_donasi.php">
                                <span data-feather="check-circle" class="align-text-bottom"></span>
                                Verifikasi Donasi
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="kelola_rekening.php">
                                <span data-feather="credit-card" class="align-text-bottom"></span>
                                Kelola Rekening
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="profil_admin.php"> 
                                <span data-feather="user" class="align-text-bottom"></span>
                                Profil Saya
                            </a>
                        </li>
                        <hr style="border-color: rgba(255,255,255,0.1);">
                        <li class="nav-item">
                            <a class="nav-link" href="../logout.php">
                                <span data-feather="log-out" class="align-text-bottom"></span>
                                Logout (<?php echo htmlspecialchars($admin_nama); ?>)
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 content-area">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard Admin</h1>
                </div>

                <h4>Selamat Datang, <?php echo htmlspecialchars($admin_nama); ?>!</h4>
                <p>Ini adalah halaman dashboard admin. Dari sini Anda dapat mengelola kampanye, memverifikasi donasi, dan mengatur berbagai aspek aplikasi.</p>
                
                <div class="row mt-4">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs fw-bold text-primary text-uppercase mb-1">
                                            Kampanye Aktif</div>
                                        <div class="h5 mb-0 fw-bold text-gray-800">
                                            <?php echo $jumlah_kampanye_aktif; // Menampilkan data dinamis ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="fas fa-bullhorn fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                     <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-success shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs fw-bold text-success text-uppercase mb-1">
                                            Donasi Menunggu Verifikasi</div>
                                        <div class="h5 mb-0 fw-bold text-gray-800">
                                            <?php echo $jumlah_donasi_pending; // Menampilkan data dinamis ?>
                                        </div>
                                    </div>
                                    <div class="col-auto">
                                         <i class="fas fa-clipboard-check fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>
                </main>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/feather-icons/dist/feather.min.js"></script>
    <script>
      feather.replace(); // Untuk mengaktifkan ikon feather
    </script>
</body>
</html>
<?php
session_start(); // Mulai sesi di awal

// Cek apakah donatur sudah login, jika tidak, arahkan ke halaman login
if (!isset($_SESSION['donatur_id'])) {
    // Set pesan error jika perlu (opsional, karena mungkin pengguna langsung ke URL ini)
    // $_SESSION['error_message'] = "Anda harus login untuk mengakses halaman ini.";
    header("Location: ../login_donatur.php"); // Arahkan ke login_donatur.php di root folder
    exit();
}

// Ambil informasi donatur dari sesi (opsional, tapi berguna)
$donatur_nama = $_SESSION['donatur_nama_lengkap'] ?? 'Donatur';
$donatur_email = $_SESSION['donatur_email'] ?? '';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Donatur - Aplikasi Donasi Bencana</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet"> <style>
        body {
            background-color: #f4f7f6;
        }
        .navbar {
            margin-bottom: 20px;
        }
        .content-area {
            background-color: #ffffff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="#">Dashboard Donatur</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            Halo, <?php echo htmlspecialchars($donatur_nama); ?>!
                        </span>
                    </li>
                    <li class="nav-item">
                        <a class="btn btn-light" href="../logout.php">Logout</a> </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="content-area">
            <h3 class="mb-4">Selamat Datang di Dashboard Anda!</h3>
            <p>Ini adalah halaman dashboard donatur. Dari sini Anda akan bisa melihat riwayat donasi Anda, mengelola profil, dan melihat kampanye yang sedang berlangsung.</p>
            
            <div class="row mt-4">
                <div class="col-md-4">
                    <div class="card text-white bg-primary mb-3">
                        <div class="card-header">Kampanye Aktif</div>
                        <div class="card-body">
                            <h5 class="card-title">Lihat Kampanye</h5>
                            <p class="card-text">Temukan dan dukung kampanye yang sedang membutuhkan bantuan.</p>
                            <a href="../index.php#daftar-kampanye" class="btn btn-light">Lihat Sekarang</a> </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-white bg-info mb-3">
                        <div class="card-header">Riwayat Donasi</div>
                        <div class="card-body">
                            <h5 class="card-title">Cek Donasi Anda</h5>
                            <p class="card-text">Lihat status dan detail donasi yang telah Anda berikan.</p>
                            <a href="riwayat_donasi.php" class="btn btn-light">Lihat Riwayat</a> </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card text-dark bg-warning mb-3">
                        <div class="card-header">Profil Saya</div>
                        <div class="card-body">
                            <h5 class="card-title">Kelola Akun</h5>
                            <p class="card-text">Perbarui informasi pribadi dan data akun Anda.</p>
                            <a href="profil_donatur.php" class="btn btn-dark">Edit Profil</a> </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script> </body>
</html>
<?php
session_start(); // Mulai sesi

require_once __DIR__ . '/app/classes/Kampanye.php'; // Path ke kelas Kampanye

// Ambil semua kampanye yang berstatus 'aktif'
$daftar_kampanye_aktif = Kampanye::findAll('aktif');

// Fungsi untuk format rupiah (bisa dipindah ke file helper nanti jika sering digunakan)
if (!function_exists('formatRupiah')) {
    function formatRupiah($angka) {
        return "Rp " . number_format($angka, 0, ',', '.');
    }
}

// Fungsi untuk menghitung persentase donasi terkumpul
if (!function_exists('hitungPersentase')) {
    function hitungPersentase($terkumpul, $target) {
        if ($target <= 0) return 0;
        $persen = ($terkumpul / $target) * 100;
        return round(min($persen, 100)); // Batasi maksimal 100%
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Aplikasi Donasi Bencana - Bantu Sesama</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f9f9f9;
        }
        .hero-section {
            background: linear-gradient(rgba(0, 0, 0, 0.5), rgba(0, 0, 0, 0.5)), url('https://images.unsplash.com/photo-1504805572947-34fad45aed93?ixlib=rb-1.2.1&auto=format&fit=crop&w=1350&q=80') no-repeat center center;
            /* Ganti URL gambar di atas dengan gambar yang lebih relevan dengan tema donasi bencana jika ada */
            background-size: cover;
            color: white;
            padding: 80px 0;
            text-align: center;
            margin-bottom: 30px;
        }
        .hero-section h1 {
            font-size: 3.5rem;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.7);
        }
        .hero-section p {
            font-size: 1.25rem;
            margin-bottom: 30px;
        }
        .campaign-card {
            margin-bottom: 30px;
            transition: transform .2s, box-shadow .2s;
            border: none;
            border-radius: 10px;
            overflow: hidden; /* Untuk memastikan gambar tidak keluar dari border-radius card */
        }
        .campaign-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        }
        .campaign-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        .campaign-card .card-body {
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            min-height: 230px; /* Beri tinggi minimal agar card body seimbang */
        }
        .campaign-title {
            font-weight: bold;
            color: #333;
            min-height: 3em; /* Jaga agar judul memiliki tinggi minimal (sekitar 2 baris) */
            margin-bottom: 0.5rem;
        }
        .campaign-description {
            font-size: 0.9rem;
            color: #555;
            flex-grow: 1; /* Agar deskripsi mengisi ruang yang tersedia */
            margin-bottom: 1rem;
        }
        .progress-info {
            font-size: 0.9rem;
            margin-top: auto; /* Dorong ke bawah jika card-body flex */
        }
        .footer {
            background-color: #343a40;
            color: white;
            padding: 20px 0;
            text-align: center;
            margin-top: 40px;
        }
        .btn-donasi {
            background-color: #28a745;
            border-color: #28a745;
            font-weight: bold;
        }
        .btn-donasi:hover {
            background-color: #218838;
            border-color: #1e7e34;
        }
        .navbar-brand img {
            max-height: 40px; /* Sesuaikan ukuran logo jika ada */
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                DonasiBencana.ID
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="index.php">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#daftar-kampanye">Kampanye</a>
                    </li>
                    <?php if (isset($_SESSION['donatur_id'])): ?>
                        <li class="nav-item">
                            <a class="nav-link" href="donatur/dashboard.php">Dashboard Donatur</a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="logout.php">Logout</a>
                        </li>
                    <?php elseif (isset($_SESSION['admin_id'])): ?>
                       <li class="nav-item">
                        <a class="nav-link" href="admin/dashboard.php">Dashboard Admin</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdownLogin" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Login
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarDropdownLogin">
                            <li><a class="dropdown-item" href="login_donatur.php">Login Donatur</a></li>
                            <li><a class="dropdown-item" href="login_admin.php">Login Admin</a></li>
                        </ul>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register_donatur.php">Register Donatur</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="hero-section">
    <div class="container">
        <h1>Ulurkan Tangan, Ringankan Beban</h1>
        <p>Setiap donasi Anda sangat berarti bagi mereka yang membutuhkan pertolongan akibat bencana.</p>
        <a href="#daftar-kampanye" class="btn btn-lg btn-donasi">Donasi Sekarang</a>
    </div>
</div>

<div class="container" id="daftar-kampanye">
    <h2 class="text-center mb-5 mt-4">Kampanye Aktif Membutuhkan Bantuan Anda</h2>

    <?php if (empty($daftar_kampanye_aktif)): ?>
        <div class="alert alert-info text-center" role="alert">
            Saat ini belum ada kampanye aktif. Silakan cek kembali nanti.
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4">
            <?php foreach ($daftar_kampanye_aktif as $kampanye): ?>
                <div class="col">
                    <div class="card h-100 campaign-card">
                        <img src="uploads/gambar_kampanye/<?php echo htmlspecialchars($kampanye->getGambarKampanye() ?: 'default_campaign.jpg'); ?>" 
                        class="card-img-top" 
                        alt="<?php echo htmlspecialchars($kampanye->getJudul()); ?>">
                        <div class="card-body"> 
                            <h5 class="card-title campaign-title"><?php echo htmlspecialchars($kampanye->getJudul()); ?></h5>
                            <p class="card-text campaign-description"><?php echo htmlspecialchars(substr($kampanye->getDeskripsiSingkat() ?: $kampanye->getDeskripsiLengkap(), 0, 100)) . '...'; ?></p>
                            <div class="progress-info mt-auto"> 
                                <div class="d-flex justify-content-between">
                                    <span>Terkumpul: <?php echo formatRupiah($kampanye->getDanaTerkumpul()); ?></span>
                                    <span>Target: <?php echo formatRupiah($kampanye->getTargetDonasi()); ?></span>
                                </div>
                                <?php $persentase = hitungPersentase($kampanye->getDanaTerkumpul(), $kampanye->getTargetDonasi()); ?>
                                <div class="progress mt-1 mb-2" style="height: 10px;">
                                    <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $persentase; ?>%;" aria-valuenow="<?php echo $persentase; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                                <small class="text-muted"><?php echo $persentase; ?>% tercapai</small>
                            </div>
                        </div>
                        <div class="card-footer bg-transparent border-top-0 text-center pb-3">
                            <a href="detail_kampanye_publik.php?id=<?php echo $kampanye->getId(); ?>" class="btn btn-primary btn-donasi w-100">Lihat Detail & Donasi</a>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<footer class="footer">
    <div class="container">
        <p>&copy; <?php echo date("Y"); ?> Aplikasi Donasi Bencana. Semua Hak Dilindungi.</p>
        <p>Dibangun dengan ❤️ untuk kemanusiaan.</p>
    </div>
</footer>

<script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>

<?php
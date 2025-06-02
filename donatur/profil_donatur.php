<?php
session_start(); // Mulai sesi di awal

// Cek apakah donatur sudah login
if (!isset($_SESSION['donatur_id'])) {
    header("Location: ../login_donatur.php"); 
    exit();
}

require_once __DIR__ . '/../app/classes/Donatur.php';

$donatur_id_session = $_SESSION['donatur_id'];
$donatur = Donatur::findById($donatur_id_session);

if (!$donatur) {
    // Seharusnya tidak terjadi jika sesi valid, tapi sebagai pengaman
    $_SESSION['error_message'] = "Gagal memuat data profil Anda. Silakan coba login kembali.";
    header("Location: ../login_donatur.php");
    exit();
}

$donatur_nama = $_SESSION['donatur_nama_lengkap'] ?? 'Donatur'; // Ambil dari sesi untuk konsistensi di navbar

// Path ke folder foto profil
$path_foto_profil_donatur = "../uploads/profil_donatur/";
$foto_profil_url = $path_foto_profil_donatur . htmlspecialchars($donatur->getFotoProfil() ?: 'default_donatur.jpg');
// Cek apakah file custom ada, jika tidak, gunakan default (meskipun getFotoProfil sudah handle default)
if (!file_exists($foto_profil_url) || empty($donatur->getFotoProfil())) {
    $foto_profil_url = $path_foto_profil_donatur . 'default_donatur.jpg';
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - <?php echo htmlspecialchars($donatur_nama); ?> - DonasiBencana.ID</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; }
        .content-area { padding-top: 20px; padding-bottom: 40px; }
        .profile-card {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .profile-picture {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 20px auto;
            display: block;
            border: 5px solid #eee;
        }
        .profile-info dt {
            font-weight: bold;
            color: #555;
        }
        .profile-info dd {
            margin-bottom: 0.8rem;
            color: #333;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-success">
        <div class="container">
            <a class="navbar-brand" href="../index.php">DonasiBencana.ID</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#donaturNavbar" aria-controls="donaturNavbar" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="donaturNavbar">
                <ul class="navbar-nav">
                    <li class="nav-item"><a class="nav-link" href="dashboard.php">Dashboard</a></li>
                    <li class="nav-item"><a class="nav-link" href="riwayat_donasi.php">Riwayat Donasi</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="profil_donatur.php">Profil Saya</a></li>
                </ul>
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
            <h1 class="h2">Profil Saya</h1>
        </div>

        <?php
        // Menampilkan pesan sukses/error dari proses edit profil/password nantinya
        if (isset($_SESSION['success_message_profil'])) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['success_message_profil']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            unset($_SESSION['success_message_profil']);
        }
        if (isset($_SESSION['error_message_profil'])) {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['error_message_profil']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            unset($_SESSION['error_message_profil']);
        }
        ?>

        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="profile-card">
                    <img src="<?php echo $foto_profil_url; ?>" alt="Foto Profil <?php echo htmlspecialchars($donatur->getNamaLengkap()); ?>" class="profile-picture">
                    
                    <h3 class="text-center mb-4"><?php echo htmlspecialchars($donatur->getNamaLengkap()); ?></h3>

                    <dl class="row profile-info">
                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($donatur->getEmail()); ?></dd>

                        <dt class="col-sm-4">Nomor Telepon:</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($donatur->getNoTelepon() ?: '- Belum diisi -'); ?></dd>

                        <dt class="col-sm-4">Alamat:</dt>
                        <dd class="col-sm-8"><?php echo nl2br(htmlspecialchars($donatur->getAlamat() ?: '- Belum diisi -')); ?></dd>
                        
                        <dt class="col-sm-4">Terdaftar Sejak:</dt>
                        <dd class="col-sm-8">
                            <?php 
                                // Asumsi getCreatedAt() mengembalikan format YYYY-MM-DD HH:MM:SS dari database
                                // dan kita ingin memuatnya ke objek Donatur
                                // Kita perlu memastikan metode findById() mengisi properti createdAt
                                // Untuk saat ini, jika $donatur->getCreatedAt() masih null dari findById(), kita tampilkan strip
                                echo $donatur->getCreatedAt() ? date('d F Y H:i', strtotime($donatur->getCreatedAt())) : '-'; 
                            ?>
                        </dd>
                    </dl>
                    
                    <hr>
                    <div class="text-center mt-4">
                        <a href="edit_profil_donatur.php" class="btn btn-primary me-2">
                            <span data-feather="edit-3" class="align-text-bottom"></span> Edit Profil
                        </a>
                        <a href="ganti_password_donatur.php" class="btn btn-warning">
                             <span data-feather="key" class="align-text-bottom"></span> Ganti Password
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="footer mt-auto py-3 bg-dark">
        <div class="container text-center">
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
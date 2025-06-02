<?php
session_start(); // Mulai sesi di awal

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login_admin.php"); 
    exit();
}

require_once __DIR__ . '/../app/classes/Admin.php'; // Path ke kelas Admin

$admin_id_session = $_SESSION['admin_id'];
$admin = Admin::findById($admin_id_session); // Menggunakan metode findById dari kelas Admin

if (!$admin) {
    // Seharusnya tidak terjadi jika sesi valid, tapi sebagai pengaman
    $_SESSION['error_message_admin_login'] = "Gagal memuat data profil Anda. Silakan coba login kembali.";
    header("Location: ../login_admin.php");
    exit();
}

$admin_nama_navbar = $_SESSION['admin_nama_lengkap'] ?? 'Administrator'; // Untuk navbar

// Tentukan path foto profil
$path_folder_profil_admin = "../uploads/profil_admin/"; // Relatif dari file ini
$nama_foto_dari_db = $admin->getFotoProfil(); // Ini akan 'default_admin.jpg' jika kosong dari DB

$foto_profil_url = $path_folder_profil_admin . 'default_admin.jpg'; // Default awal
if (!empty($nama_foto_dari_db) && $nama_foto_dari_db !== 'default_admin.jpg') {
    // Cek apakah file custom ada di server
    // Path untuk file_exists harus relatif dari skrip ini atau absolut server
    $path_file_custom_server = __DIR__ . '/' . $path_folder_profil_admin . $nama_foto_dari_db;
    if (file_exists($path_file_custom_server)) {
        $foto_profil_url = $path_folder_profil_admin . htmlspecialchars($nama_foto_dari_db);
    }
    // Jika file custom tidak ditemukan di server, $foto_profil_url akan tetap default_admin.jpg
}
// Pastikan default_admin.jpg itu sendiri ada, jika tidak, gambar mungkin rusak
// (Untuk onerror di img tag, path harus relatif dari root web atau absolut web)
$path_default_onerror = "../uploads/profil_admin/default_admin.jpg"; // Path untuk onerror

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya - <?php echo htmlspecialchars($admin_nama_navbar); ?> - Admin Panel</title>
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
                    <li class="nav-item"><a class="nav-link" href="kelola_rekening.php">Kelola Rekening</a></li>
                    <li class="nav-item"><a class="nav-link active" aria-current="page" href="profil_admin.php">Profil Saya</a></li> 
                </ul>
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <span class="navbar-text me-3">
                            Login sebagai: <?php echo htmlspecialchars($admin_nama_navbar); ?>
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
            <h1 class="h2">Profil Saya (Admin)</h1>
        </div>

        <?php
        if (isset($_SESSION['success_message_profil_admin'])) {
            echo '<div class="alert alert-success alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['success_message_profil_admin']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            unset($_SESSION['success_message_profil_admin']);
        }
        if (isset($_SESSION['error_message_profil_admin'])) {
            echo '<div class="alert alert-danger alert-dismissible fade show" role="alert">' . htmlspecialchars($_SESSION['error_message_profil_admin']) . '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button></div>';
            unset($_SESSION['error_message_profil_admin']);
        }
        ?>

        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="profile-card">
                    <img src="<?php echo $foto_profil_url; ?>" 
                         alt="Foto Profil <?php echo htmlspecialchars($admin->getNamaLengkap()); ?>" 
                         class="profile-picture"
                         onerror="this.onerror=null; this.src='<?php echo $path_default_onerror; ?>';">
                    
                    <h3 class="text-center mb-4"><?php echo htmlspecialchars($admin->getNamaLengkap()); ?></h3>

                    <dl class="row profile-info">
                        <dt class="col-sm-4">Email:</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($admin->getEmail()); ?></dd>

                        <dt class="col-sm-4">Nomor Telepon:</dt>
                        <dd class="col-sm-8"><?php echo htmlspecialchars($admin->getNoTelepon() ?: '- Belum diisi -'); ?></dd>
                        
                        <dt class="col-sm-4">Terdaftar Sejak:</dt>
                        <dd class="col-sm-8">
                            <?php 
                                // Pastikan Admin::findById() sudah mengisi properti createdAt di objek $admin
                                // dan kelas Admin sudah memiliki metode getCreatedAt()
                                echo $admin->getCreatedAt() ? date('d F Y H:i', strtotime($admin->getCreatedAt())) : '-'; 
                            ?>
                        </dd>
                    </dl>
                    
                    <hr>
                    <div class="text-center mt-4">
                        <a href="edit_profil_admin.php" class="btn btn-primary me-2">
                            <span data-feather="edit-3" class="align-text-bottom"></span> Edit Profil
                        </a>
                        <a href="ganti_password_admin.php" class="btn btn-warning">
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
?>
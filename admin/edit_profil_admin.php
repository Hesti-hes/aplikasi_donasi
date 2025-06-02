<?php
session_start(); // Mulai sesi di awal

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login_admin.php"); 
    exit();
}

require_once __DIR__ . '/../app/classes/Admin.php';

$admin_id_session = $_SESSION['admin_id'];
$admin = Admin::findById($admin_id_session);

if (!$admin) {
    $_SESSION['error_message_admin_login'] = "Gagal memuat data profil Anda untuk diedit. Silakan coba login kembali.";
    header("Location: ../login_admin.php");
    exit();
}

$admin_nama_navbar = $_SESSION['admin_nama_lengkap'] ?? 'Administrator'; // Untuk navbar

// Path ke folder foto profil admin
$path_foto_profil_admin = "../uploads/profil_admin/";

// Ambil data form lama jika ada (setelah redirect karena error di proses_edit)
$form_data = $_SESSION['form_data_profil_admin_edit'] ?? [];
unset($_SESSION['form_data_profil_admin_edit']); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profil Saya - <?php echo htmlspecialchars($admin_nama_navbar); ?> - Admin Panel</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; }
        .content-area { padding-top: 20px; padding-bottom: 40px; }
        .profile-edit-card {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .current-profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin-bottom: 15px;
            border: 3px solid #ddd;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark"> <div class="container-fluid">
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
            <h1 class="h2">Edit Profil Saya (Admin)</h1>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-7">
                <div class="profile-edit-card">
                    <?php
                    // Menampilkan pesan error jika ada dari proses_edit_profil_admin.php
                    if (isset($_SESSION['error_message_profil_admin_edit'])) {
                        echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['error_message_profil_admin_edit']) . '</div>';
                        unset($_SESSION['error_message_profil_admin_edit']); 
                    }
                    ?>
                    <form action="proses_edit_profil_admin.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="admin_id" value="<?php echo $admin->getId(); ?>">

                        <div class="mb-3 text-center">
                            <label class="form-label d-block">Foto Profil Saat Ini:</label>
                            <?php
                                $nama_foto_admin_db = $admin->getFotoProfil();
                                $url_foto_tampil = $path_foto_profil_admin . 'default_admin.jpg'; // Default
                                if (!empty($nama_foto_admin_db) && $nama_foto_admin_db !== 'default_admin.jpg') {
                                    $path_cek_foto = __DIR__ . '/' . $path_foto_profil_admin . $nama_foto_admin_db;
                                    if (file_exists($path_cek_foto)) {
                                        $url_foto_tampil = $path_foto_profil_admin . htmlspecialchars($nama_foto_admin_db);
                                    }
                                }
                            ?>
                            <img src="<?php echo $url_foto_tampil; ?>" 
                                 alt="Foto Profil <?php echo htmlspecialchars($admin->getNamaLengkap()); ?>" 
                                 class="current-profile-picture"
                                 onerror="this.src='<?php echo $path_foto_profil_admin . 'default_admin.jpg'; ?>'">
                        </div>

                        <div class="mb-3">
                            <label for="foto_profil_baru_admin" class="form-label">Ganti Foto Profil (Opsional)</label>
                            <input type="file" class="form-control" id="foto_profil_baru_admin" name="foto_profil_baru_admin" accept="image/jpeg, image/png, image/gif">
                            <div class="form-text">Kosongkan jika tidak ingin mengganti foto. Format: JPG, PNG, GIF. Maks: 1MB.</div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="nama_lengkap" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" 
                                   value="<?php echo htmlspecialchars($form_data['nama_lengkap'] ?? $admin->getNamaLengkap()); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="email" class="form-label">Email (Tidak dapat diubah)</label>
                            <input type="email" class="form-control" id="email" name="email_display" 
                                   value="<?php echo htmlspecialchars($admin->getEmail()); ?>" readonly disabled>
                        </div>

                        <div class="mb-3">
                            <label for="no_telepon" class="form-label">Nomor Telepon</label>
                            <input type="tel" class="form-control" id="no_telepon" name="no_telepon" 
                                   value="<?php echo htmlspecialchars($form_data['no_telepon'] ?? $admin->getNoTelepon() ?? ''); ?>" 
                                   placeholder="Contoh: 08123456789">
                        </div>
                        
                        <hr>
                        <p class="text-muted"><small>Untuk mengganti password, silakan gunakan menu "Ganti Password".</small></p>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="profil_admin.php" class="btn btn-secondary me-md-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan Profil</button>
                        </div>
                    </form>
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
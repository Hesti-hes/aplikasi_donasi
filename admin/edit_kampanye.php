<?php
session_start(); // Mulai sesi di awal

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login_admin.php");
    exit();
}

require_once __DIR__ . '/../app/classes/Kampanye.php'; // Path ke kelas Kampanye

$kampanye = null; // Inisialisasi variabel kampanye
$error_message_load = null; // Pesan error jika kampanye tidak ditemukan

// 1. Ambil ID kampanye dari URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $_SESSION['error_message_kampanye'] = "ID Kampanye tidak valid atau tidak ditemukan.";
    header("Location: kelola_kampanye.php"); // Kembali ke halaman kelola
    exit();
}

$kampanye_id = (int)$_GET['id'];

// 2. Panggil Kampanye::findById() untuk mendapatkan data kampanye
$kampanye = Kampanye::findById($kampanye_id);

if (!$kampanye) {
    // Jika kampanye tidak ditemukan
    $_SESSION['error_message_kampanye'] = "Kampanye dengan ID " . htmlspecialchars($kampanye_id) . " tidak ditemukan.";
    header("Location: kelola_kampanye.php");
    exit();
}

// Ambil informasi admin dari sesi
$admin_nama = $_SESSION['admin_nama_lengkap'] ?? 'Administrator';

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Kampanye - <?php echo htmlspecialchars($kampanye->getJudul()); ?> - Dashboard Admin</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; }
        .content-area { padding-top: 20px; padding-bottom: 40px; }
        .form-card { background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
        .current-image { max-width: 200px; max-height: 150px; margin-bottom: 10px; display: block; border: 1px solid #ddd; padding: 5px; }
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
        <div class="row justify-content-center">
            <div class="col-md-10 col-lg-8">
                <div class="form-card">
                    <h3 class="mb-4 text-center">Edit Kampanye: <?php echo htmlspecialchars($kampanye->getJudul()); ?></h3>

                    <?php
                    // Menampilkan pesan error jika ada dari proses_edit_kampanye.php
                    if (isset($_SESSION['error_message_kampanye_edit'])) {
                        echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['error_message_kampanye_edit']) . '</div>';
                        unset($_SESSION['error_message_kampanye_edit']);
                    }
                    ?>

                    <form action="proses_edit_kampanye.php" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="kampanye_id" value="<?php echo $kampanye->getId(); ?>">
                        
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul Kampanye <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="judul" name="judul" value="<?php echo htmlspecialchars($kampanye->getJudul()); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi_singkat" class="form-label">Deskripsi Singkat (Max 255 karakter)</label>
                            <input type="text" class="form-control" id="deskripsi_singkat" name="deskripsi_singkat" value="<?php echo htmlspecialchars($kampanye->getDeskripsiSingkat() ?? ''); ?>" maxlength="255">
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi_lengkap" class="form-label">Deskripsi Lengkap <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="deskripsi_lengkap" name="deskripsi_lengkap" rows="5" required><?php echo htmlspecialchars($kampanye->getDeskripsiLengkap() ?? ''); ?></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Gambar Utama Saat Ini:</label>
                            <?php if ($kampanye->getGambarKampanye()): ?>
                                <img src="../uploads/gambar_kampanye/<?php echo htmlspecialchars($kampanye->getGambarKampanye()); ?>" alt="Gambar Kampanye Saat Ini" class="current-image">
                                <p><small class="text-muted">File: <?php echo htmlspecialchars($kampanye->getGambarKampanye()); ?></small></p>
                            <?php else: ?>
                                <p class="text-muted">Tidak ada gambar utama saat ini.</p>
                            <?php endif; ?>
                            <label for="gambar_kampanye_baru" class="form-label mt-2">Ganti Gambar Utama (Opsional)</label>
                            <input type="file" class="form-control" id="gambar_kampanye_baru" name="gambar_kampanye_baru" accept="image/jpeg, image/png, image/gif">
                            <div class="form-text">Kosongkan jika tidak ingin mengganti gambar. Format: JPG, PNG, GIF. Maks: 2MB.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="target_donasi" class="form-label">Target Donasi (Rp) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="target_donasi" name="target_donasi" value="<?php echo htmlspecialchars($kampanye->getTargetDonasi()); ?>" required min="10000">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status_kampanye" class="form-label">Status Kampanye</label>
                                <select class="form-select" id="status_kampanye" name="status_kampanye">
                                    <option value="aktif" <?php echo ($kampanye->getStatusKampanye() == 'aktif') ? 'selected' : ''; ?>>Aktif</option>
                                    <option value="selesai" <?php echo ($kampanye->getStatusKampanye() == 'selesai') ? 'selected' : ''; ?>>Selesai</option>
                                    <option value="ditutup" <?php echo ($kampanye->getStatusKampanye() == 'ditutup') ? 'selected' : ''; ?>>Ditutup</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_mulai" class="form-label">Tanggal Mulai Kampanye</label>
                                <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai" value="<?php echo htmlspecialchars($kampanye->getTanggalMulai() ?? ''); ?>">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_selesai" class="form-label">Tanggal Selesai Kampanye</label>
                                <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai" value="<?php echo htmlspecialchars($kampanye->getTanggalSelesai() ?? ''); ?>">
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="kelola_kampanye.php" class="btn btn-secondary me-md-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
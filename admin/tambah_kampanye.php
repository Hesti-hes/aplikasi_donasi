<?php
session_start(); // Mulai sesi di awal

// Cek apakah admin sudah login, jika tidak, arahkan ke halaman login admin
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login_admin.php"); // Arahkan ke login_admin.php di root folder
    exit();
}

// Ambil informasi admin dari sesi (opsional, tapi berguna untuk header/sidebar nanti)
$admin_nama = $_SESSION['admin_nama_lengkap'] ?? 'Administrator';

// Di sini nanti kita bisa tambahkan logika untuk menampilkan pesan error/sukses dari proses sebelumnya
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Kampanye Baru - Dashboard Admin</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet"> <style>
        /* Anda bisa menggunakan styling yang sama dengan admin/dashboard.php atau kustomisasi */
        body {
            background-color: #f4f7f6;
        }
        .content-area {
            padding-top: 20px;
            padding-bottom: 40px;
        }
        .form-card {
            background-color: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
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
                    <h3 class="mb-4 text-center">Buat Kampanye Penggalangan Dana Baru</h3>

                    <?php
                    // Menampilkan pesan error jika ada dari proses_tambah_kampanye.php
                    if (isset($_SESSION['error_message_kampanye'])) {
                        echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['error_message_kampanye']) . '</div>';
                        unset($_SESSION['error_message_kampanye']); // Hapus pesan setelah ditampilkan
                    }
                    // Menampilkan pesan sukses jika ada
                    if (isset($_SESSION['success_message_kampanye'])) {
                        echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['success_message_kampanye']) . '</div>';
                        unset($_SESSION['success_message_kampanye']);
                    }
                    ?>

                    <form action="proses_tambah_kampanye.php" method="POST" enctype="multipart/form-data">
                        
                        <div class="mb-3">
                            <label for="judul" class="form-label">Judul Kampanye <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="judul" name="judul" required>
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi_singkat" class="form-label">Deskripsi Singkat (Max 255 karakter)</label>
                            <input type="text" class="form-control" id="deskripsi_singkat" name="deskripsi_singkat" maxlength="255">
                        </div>

                        <div class="mb-3">
                            <label for="deskripsi_lengkap" class="form-label">Deskripsi Lengkap <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="deskripsi_lengkap" name="deskripsi_lengkap" rows="5" required></textarea>
                            </div>

                        <div class="mb-3">
                            <label for="gambar_kampanye" class="form-label">Gambar Utama Kampanye</label>
                            <input type="file" class="form-control" id="gambar_kampanye" name="gambar_kampanye" accept="image/jpeg, image/png, image/gif">
                            <div class="form-text">Format yang didukung: JPG, PNG, GIF. Ukuran maksimal disarankan: 2MB.</div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="target_donasi" class="form-label">Target Donasi (Rp) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="target_donasi" name="target_donasi" placeholder="Contoh: 50000000" required min="10000">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="status_kampanye" class="form-label">Status Kampanye Awal</label>
                                <select class="form-select" id="status_kampanye" name="status_kampanye">
                                    <option value="aktif" selected>Aktif</option>
                                    <option value="selesai">Selesai</option>
                                    <option value="ditutup">Ditutup</option>
                                </select>
                                <div class="form-text">Biasanya kampanye baru dimulai dengan status "Aktif".</div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_mulai" class="form-label">Tanggal Mulai Kampanye</label>
                                <input type="date" class="form-control" id="tanggal_mulai" name="tanggal_mulai">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="tanggal_selesai" class="form-label">Tanggal Selesai Kampanye</label>
                                <input type="date" class="form-control" id="tanggal_selesai" name="tanggal_selesai">
                            </div>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="dashboard.php" class="btn btn-secondary me-md-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan Kampanye</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
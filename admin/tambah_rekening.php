<?php
session_start(); // Mulai sesi di awal

// Cek apakah admin sudah login
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login_admin.php"); // Arahkan ke login_admin.php di root folder
    exit();
}

$admin_nama = $_SESSION['admin_nama_lengkap'] ?? 'Administrator';

// Ambil data form lama jika ada (setelah redirect karena error)
$form_data = $_SESSION['form_data_rekening'] ?? [];
unset($_SESSION['form_data_rekening']); // Hapus setelah diambil
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Rekening Tujuan Baru - Dashboard Admin</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; }
        .content-area { padding-top: 20px; padding-bottom: 40px; }
        .form-card { background-color: #ffffff; padding: 30px; border-radius: 8px; box-shadow: 0 0 15px rgba(0,0,0,0.1); }
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
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="form-card">
                    <h3 class="mb-4 text-center">Tambah Rekening Tujuan Baru</h3>

                    <?php
                    // Menampilkan pesan error jika ada dari proses sebelumnya
                    if (isset($_SESSION['error_message_rekening'])) {
                        echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['error_message_rekening']) . '</div>';
                        unset($_SESSION['error_message_rekening']); 
                    }
                    // Menampilkan pesan sukses jika ada (misalnya setelah berhasil menambah)
                    // Biasanya pesan sukses akan ditampilkan di halaman kelola, tapi bisa juga di sini jika kembali ke form tambah
                    if (isset($_SESSION['success_message_rekening'])) {
                        echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['success_message_rekening']) . '</div>';
                        unset($_SESSION['success_message_rekening']);
                    }
                    ?>

                    <form action="proses_tambah_rekening.php" method="POST">
                        
                        <div class="mb-3">
                            <label for="nama_bank" class="form-label">Nama Bank <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nama_bank" name="nama_bank" 
                                   value="<?php echo htmlspecialchars($form_data['nama_bank'] ?? ''); ?>" required>
                        </div>

                        <div class="mb-3">
                            <label for="nomor_rekening" class="form-label">Nomor Rekening <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="nomor_rekening" name="nomor_rekening"
                                   value="<?php echo htmlspecialchars($form_data['nomor_rekening'] ?? ''); ?>" required 
                                   pattern="[0-9]+" title="Hanya masukkan angka untuk nomor rekening">
                        </div>

                        <div class="mb-3">
                            <label for="atas_nama" class="form-label">Atas Nama (Pemilik Rekening) <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="atas_nama" name="atas_nama"
                                   value="<?php echo htmlspecialchars($form_data['atas_nama'] ?? ''); ?>" required>
                        </div>

                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="kelola_rekening.php" class="btn btn-secondary me-md-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan Rekening</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="../assets/js/bootstrap.bundle.min.js"></script>
    </body>
</html>
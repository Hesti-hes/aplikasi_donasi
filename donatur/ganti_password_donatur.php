<?php
session_start(); // Mulai sesi di awal

// Cek apakah donatur sudah login
if (!isset($_SESSION['donatur_id'])) {
    header("Location: ../login_donatur.php"); 
    exit();
}

$donatur_nama_navbar = $_SESSION['donatur_nama_lengkap'] ?? 'Donatur'; // Untuk navbar

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ganti Password - <?php echo htmlspecialchars($donatur_nama_navbar); ?> - DonasiBencana.ID</title>
    <link href="../assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background-color: #f4f7f6; }
        .content-area { padding-top: 20px; padding-bottom: 40px; }
        .password-card {
            background-color: #fff;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
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
                            Halo, <?php echo htmlspecialchars($donatur_nama_navbar); ?>!
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
            <h1 class="h2">Ganti Password</h1>
        </div>

        <div class="row justify-content-center">
            <div class="col-md-7 col-lg-5">
                <div class="password-card">
                    <?php
                    // Menampilkan pesan error jika ada dari proses_ganti_password_donatur.php
                    if (isset($_SESSION['error_message_password'])) {
                        echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['error_message_password']) . '</div>';
                        unset($_SESSION['error_message_password']); 
                    }
                    // Menampilkan pesan sukses jika ada
                    if (isset($_SESSION['success_message_password'])) {
                        echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['success_message_password']) . '</div>';
                        unset($_SESSION['success_message_password']); 
                    }
                    ?>
                    <form action="proses_ganti_password_donatur.php" method="POST" id="formGantiPassword">
                        
                        <div class="mb-3">
                            <label for="password_lama" class="form-label">Password Lama <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_lama" name="password_lama" required>
                        </div>

                        <div class="mb-3">
                            <label for="password_baru" class="form-label">Password Baru <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="password_baru" name="password_baru" required minlength="6">
                            <div class="form-text">Minimal 6 karakter.</div>
                        </div>

                        <div class="mb-3">
                            <label for="konfirmasi_password_baru" class="form-label">Konfirmasi Password Baru <span class="text-danger">*</span></label>
                            <input type="password" class="form-control" id="konfirmasi_password_baru" name="konfirmasi_password_baru" required minlength="6">
                        </div>
                        
                        <div class="d-grid gap-2 d-md-flex justify-content-md-end mt-4">
                            <a href="profil_donatur.php" class="btn btn-secondary me-md-2">Batal</a>
                            <button type="submit" class="btn btn-primary">Simpan Password Baru</button>
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
    <script>
        // Validasi sederhana untuk konfirmasi password baru di sisi client
        const formGantiPassword = document.getElementById('formGantiPassword');
        if (formGantiPassword) {
            const passwordBaruInput = document.getElementById('password_baru');
            const konfirmasiPasswordBaruInput = document.getElementById('konfirmasi_password_baru');

            function validateNewPassword() {
                if (passwordBaruInput.value !== konfirmasiPasswordBaruInput.value) {
                    konfirmasiPasswordBaruInput.setCustomValidity("Password Baru dan Konfirmasi Password Baru tidak cocok.");
                } else {
                    konfirmasiPasswordBaruInput.setCustomValidity(''); // Kosongkan jika cocok
                }
            }
            if(passwordBaruInput && konfirmasiPasswordBaruInput) {
                passwordBaruInput.onchange = validateNewPassword;
                konfirmasiPasswordBaruInput.onkeyup = validateNewPassword;
            }
        }
    </script>
</body>
</html>
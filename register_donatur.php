<?php
// Di sini nanti kita bisa tambahkan logika PHP jika diperlukan sebelum HTML ditampilkan
// Misalnya, untuk menampilkan pesan error dari proses registrasi sebelumnya
session_start(); // Mulai session untuk menangani pesan feedback
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Donatur - Aplikasi Donasi Bencana</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .registration-container {
            margin-top: 50px;
            margin-bottom: 50px;
        }
        .card-header {
            background-color: #007bff;
            color: white;
        }
    </style>
</head>
<body>
    <div class="container registration-container">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="card shadow">
                    <div class="card-header text-center">
                        <h4>Registrasi Akun Donatur Baru</h4>
                    </div>
                    <div class="card-body p-4">
                        <?php
                        // Menampilkan pesan error jika ada dari proses sebelumnya
                        if (isset($_SESSION['error_message'])) {
                            echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['error_message']) . '</div>';
                            unset($_SESSION['error_message']); // Hapus pesan setelah ditampilkan
                        }
                        // Menampilkan pesan sukses jika ada
                        if (isset($_SESSION['success_message'])) {
                            echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['success_message']) . '</div>';
                            unset($_SESSION['success_message']); // Hapus pesan setelah ditampilkan
                        }
                        ?>

                        <form action="proses_register_donatur.php" method="POST">
                            <div class="mb-3">
                                <label for="nama_lengkap" class="form-label">Nama Lengkap <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="nama_lengkap" name="nama_lengkap" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">Alamat Email <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" required minlength="6">
                                <div id="passwordHelpBlock" class="form-text">
                                    Password minimal 6 karakter.
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="konfirmasi_password" class="form-label">Konfirmasi Password <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="konfirmasi_password" name="konfirmasi_password" required minlength="6">
                            </div>

                            <div class="mb-3">
                                <label for="no_telepon" class="form-label">Nomor Telepon (Opsional)</label>
                                <input type="tel" class="form-control" id="no_telepon" name="no_telepon" placeholder="Contoh: 08123456789">
                            </div>

                            <div class="mb-3">
                                <label for="alamat" class="form-label">Alamat (Opsional)</label>
                                <textarea class="form-control" id="alamat" name="alamat" rows="3"></textarea>
                            </div>
                            
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">Daftar</button>
                            </div>
                        </form>
                        <p class="mt-3 text-center">
                            Sudah punya akun? <a href="login_donatur.php">Login di sini</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
    <script>
        // Script sederhana untuk validasi konfirmasi password
        const passwordInput = document.getElementById('password');
        const confirmPasswordInput = document.getElementById('konfirmasi_password');

        function validatePassword() {
            if (passwordInput.value !== confirmPasswordInput.value) {
                confirmPasswordInput.setCustomValidity("Password dan Konfirmasi Password tidak cocok.");
            } else {
                confirmPasswordInput.setCustomValidity(''); // Kosongkan jika cocok
            }
        }

        passwordInput.onchange = validatePassword;
        confirmPasswordInput.onkeyup = validatePassword;
    </script>
</body>
</html>
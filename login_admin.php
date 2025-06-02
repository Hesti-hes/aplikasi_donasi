<?php
session_start(); // Mulai session untuk menangani pesan feedback

// Jika admin sudah login, arahkan ke dashboard admin
if (isset($_SESSION['admin_id'])) {
    header("Location: admin/dashboard.php"); // Nanti kita buat halaman ini
    exit();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Admin - Aplikasi Donasi Bencana</title>
    <link href="assets/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }
        .login-container {
            margin-top: 100px;
            margin-bottom: 50px;
        }
        .card-header {
            background-color: #dc3545; /* Warna merah untuk admin */
            color: white;
        }
    </style>
</head>
<body>
    <div class="container login-container">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-4">
                <div class="card shadow">
                    <div class="card-header text-center">
                        <h4>Login Administrator</h4>
                    </div>
                    <div class="card-body p-4">
                        <?php
                        // Menampilkan pesan error dari proses login sebelumnya
                        if (isset($_SESSION['error_message_admin'])) {
                            echo '<div class="alert alert-danger" role="alert">' . htmlspecialchars($_SESSION['error_message_admin']) . '</div>';
                            unset($_SESSION['error_message_admin']); // Hapus pesan setelah ditampilkan
                        }
                        // Menampilkan pesan sukses jika ada (misalnya, setelah logout)
                        if (isset($_SESSION['success_message_admin'])) {
                            echo '<div class="alert alert-success" role="alert">' . htmlspecialchars($_SESSION['success_message_admin']) . '</div>';
                            unset($_SESSION['success_message_admin']);
                        }
                        ?>

                        <form action="proses_login_admin.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">Alamat Email</label>
                                <input type="email" class="form-control" id="email" name="email" required autofocus>
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">Password</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>

                            <div class="d-grid mb-3">
                                <button type="submit" class="btn btn-danger">Login</button>
                            </div>
                        </form>
                        <p class="text-center">
                            <a href="index.php">Kembali ke Halaman Utama</a>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="assets/js/bootstrap.bundle.min.js"></script>
</body>
</html>
<?php
// GANTI "PasswordAdminSuperBaru" dengan password BARU yang Anda tentukan di langkah 1
$passwordAdminBaru = "admin123"; 

$hashPasswordAdmin = password_hash($passwordAdminBaru, PASSWORD_DEFAULT);

echo "Password Admin Baru yang Dipilih: " . htmlspecialchars($passwordAdminBaru) . "<br>";
echo "Hash Password BARU untuk Update Database: <strong style='color:purple;'>" . htmlspecialchars($hashPasswordAdmin) . "</strong>";
?>
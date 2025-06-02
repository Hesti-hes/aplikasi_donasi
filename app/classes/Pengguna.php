<?php

require_once __DIR__ . '/../../config/KoneksiDB.php';

abstract class Pengguna {
    // Properti dibuat protected agar bisa diakses oleh kelas anak
    protected $id;
    protected $namaLengkap;
    protected $email;
    protected $password; // Hash
    protected $noTelepon;
    protected $alamat; // Properti alamat untuk digunakan oleh kelas anak (misal: Donatur)
    protected $fotoProfil;
    protected $createdAt;
    protected $updatedAt;

    // Metode abstrak yang HARUS diimplementasikan oleh kelas anak
    protected abstract static function getTableName(): string;
    protected abstract static function getFotoProfilDefault(): string;

    public function __construct() {
        // Bisa ada inisialisasi umum di sini
    }

    // Getter dan Setter Umum
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = (int)$id;
    }

    public function getNamaLengkap() {
        return $this->namaLengkap;
    }

    public function setNamaLengkap($namaLengkap) {
        if (is_string($namaLengkap) && strlen(trim($namaLengkap)) > 0 && strlen(trim($namaLengkap)) <= 100) {
            $this->namaLengkap = trim($namaLengkap);
        }
    }

    public function getEmail() {
        return $this->email;
    }

    public function setEmail($email) {
        $email = trim($email);
        if (filter_var($email, FILTER_VALIDATE_EMAIL) && strlen($email) <= 100) {
            $this->email = $email;
        }
    }

    public function setPassword($plainPassword) {
        if (strlen($plainPassword) >= 6) {
            $this->password = password_hash($plainPassword, PASSWORD_DEFAULT);
        }
    }

    public function getPasswordHash() {
        return $this->password;
    }

    public function getNoTelepon() {
        return $this->noTelepon;
    }

    public function setNoTelepon($noTelepon) {
        $noTelepon = trim($noTelepon);
        if (empty($noTelepon) || (is_string($noTelepon) && strlen($noTelepon) <= 20)) {
             $this->noTelepon = !empty($noTelepon) ? $noTelepon : null;
        }
    }

    public function getFotoProfil() {
        return $this->fotoProfil;
    }

    public function setFotoProfil($fotoProfil) {
        $this->fotoProfil = !empty(trim($fotoProfil)) ? trim($fotoProfil) : static::getFotoProfilDefault();
    }
    
    public function getCreatedAt() {
        return $this->createdAt;
    }

    protected function setCreatedAtInternal($createdAt) {
        $this->createdAt = $createdAt;
    }

    public function getUpdatedAt() {
        return $this->updatedAt;
    }

    protected function setUpdatedAtInternal($updatedAt) {
        $this->updatedAt = $updatedAt;
    }

    // --- Metode Inti untuk Operasi Database ---

    protected static function _findByIdInternal(int $id, string $tableName) {
        if (empty($id) || !is_numeric($id)) {
            return false; 
        }
        $koneksi = KoneksiDB::getInstance();
        $sql = "SELECT * FROM " . $tableName . " WHERE id = :id";
        try {
            // Pastikan ID adalah integer untuk keamanan query (meskipun sudah divalidasi)
            return $koneksi->fetchOne($sql, [':id' => $id]); 
        } catch (PDOException $e) {
            error_log("Error _findByIdInternal (Table: {$tableName}, ID: {$id}): " . $e->getMessage());
            return false;
        }
    }
    
    protected static function _loginInternal(string $email, string $plainPassword, string $tableName) {
        if (empty($email) || empty($plainPassword)) {
            return "Email dan password tidak boleh kosong.";
        }
        $sql = "SELECT * FROM " . $tableName . " WHERE email = :email";
        try {
            $koneksi = KoneksiDB::getInstance();
            $userData = $koneksi->fetchOne($sql, [':email' => $email]);

            if ($userData) {
                if (password_verify($plainPassword, $userData['password'])) {
                    return $userData; 
                } else {
                    return "Password yang Anda masukkan salah.";
                }
            } else {
                $role = ($tableName === 'admin') ? 'admin' : 'donatur'; // Penyesuaian kecil untuk pesan error
                return "Email tidak terdaftar sebagai {$role}.";
            }
        } catch (PDOException $e) {
            error_log("Error _loginInternal (Table: {$tableName}): " . $e->getMessage());
            return "Terjadi kesalahan pada sistem saat login.";
        }
    }

    // ... (properti, constructor, getter/setter, metode abstrak lainnya tetap sama) ...
    // ... (metode _findByIdInternal, _loginInternal, _gantiPasswordInternal tetap sama) ...

    /**
     * Metode inti untuk memperbarui profil pengguna di tabel tertentu.
     * Tidak termasuk update password dan email.
     * Properti objek harus sudah di-set dengan nilai baru sebelum memanggil ini.
     */
    protected function _updateProfilInternal(string $tableName): bool|string {
        if (empty($this->id) || !is_numeric($this->id)) {
            return "ID Pengguna tidak valid untuk proses update profil.";
        }
        // Validasi namaLengkap ada di sini karena ini field inti
        if (empty($this->namaLengkap)) {
            return "Nama lengkap tidak boleh kosong.";
        }

        $koneksi = KoneksiDB::getInstance();
        
        $setClauses = [];
        $params = [];

        // Field yang pasti ada dan diupdate
        $setClauses[] = "nama_lengkap = :nama_lengkap";
        $params[':nama_lengkap'] = $this->namaLengkap;

        // Field opsional atau yang mungkin ada di kelas anak
        // Periksa apakah properti ada di objek saat ini sebelum menambahkannya ke query
        if (property_exists($this, 'noTelepon')) {
            $setClauses[] = "no_telepon = :no_telepon";
            $params[':no_telepon'] = $this->noTelepon; 
        }
        
        if (property_exists($this, 'fotoProfil')) {
            $setClauses[] = "foto_profil = :foto_profil";
            $params[':foto_profil'] = $this->fotoProfil;
        }
        
        // Khusus untuk alamat, yang hanya ada di Donatur
        if ($tableName === 'donatur' && property_exists($this, 'alamat')) {
            $setClauses[] = "alamat = :alamat";
            $params[':alamat'] = $this->alamat;
        }

        if (empty($setClauses)) {
            // Seharusnya tidak terjadi jika namaLengkap wajib, tapi sebagai pengaman
            return "Tidak ada data yang akan diperbarui."; 
        }

        // Tambahkan ID untuk klausa WHERE (gunakan nama parameter yang berbeda)
        $params[':id_where'] = $this->id;
        
        // updatedAt akan diupdate otomatis oleh MySQL jika diset ON UPDATE CURRENT_TIMESTAMP
        $sql = "UPDATE " . $tableName . " SET " . implode(", ", $setClauses) . " WHERE id = :id_where";

        try {
            $koneksi->execute($sql, $params);
            return true; 
        } catch (PDOException $e) {
            error_log("Error _updateProfilInternal (Table: {$tableName}, ID: {$this->id}): " . $e->getMessage());
            return "Terjadi kesalahan pada sistem saat memperbarui profil Anda. (Detail: " . $e->getMessage() . ")";
        }
    }

    // ... (metode _gantiPasswordInternal dan metode lainnya tetap sama) ..

    protected function _gantiPasswordInternal(string $passwordLama, string $passwordBaru, string $tableName): bool|string {
        if (empty($this->id) || !is_numeric($this->id)) {
            return "Tidak dapat mengganti password: ID Pengguna tidak valid.";
        }
        if (empty($passwordLama) || empty($passwordBaru)) {
            return "Password lama dan password baru tidak boleh kosong.";
        }
        if (strlen($passwordBaru) < 6) {
            return "Password baru minimal harus 6 karakter.";
        }

        $koneksi = KoneksiDB::getInstance();
        $sqlSelect = "SELECT password FROM " . $tableName . " WHERE id = :id";
        try {
            $dataPengguna = $koneksi->fetchOne($sqlSelect, [':id' => $this->id]);
            if (!$dataPengguna) {
                return "Gagal mengambil data pengguna untuk verifikasi password lama.";
            }
            $hashPasswordSaatIni = $dataPengguna['password'];
        } catch (PDOException $e) {
            error_log("Error _gantiPasswordInternal (select - Table: {$tableName}, ID: {$this->id}): " . $e->getMessage());
            return "Terjadi kesalahan saat memverifikasi password lama.";
        }

        if (password_verify($passwordLama, $hashPasswordSaatIni)) {
            $hashPasswordBaru = password_hash($passwordBaru, PASSWORD_DEFAULT);
            $sqlUpdate = "UPDATE " . $tableName . " SET password = :password_baru, updated_at = NOW() WHERE id = :id";
            $paramsUpdate = [
                ':password_baru' => $hashPasswordBaru,
                ':id'            => $this->id
            ];
            try {
                $koneksi->execute($sqlUpdate, $paramsUpdate);
                return true; 
            } catch (PDOException $e) {
                error_log("Error _gantiPasswordInternal (update - Table: {$tableName}, ID: {$this->id}): " . $e->getMessage());
                return "Terjadi kesalahan saat menyimpan password baru.";
            }
        } else {
            return "Password lama yang Anda masukkan salah.";
        }
    }
}
?>
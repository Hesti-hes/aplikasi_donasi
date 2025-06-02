<?php

require_once __DIR__. '/Pengguna.php'; // Include kelas induk Pengguna

class Donatur extends Pengguna { // Donatur sekarang adalah anak dari Pengguna

    // Properti spesifik untuk Donatur
    protected $alamat; // Kita buat protected agar bisa diakses di _updateProfilInternal parent jika perlu

    /**
     * Metode abstrak dari kelas Pengguna yang harus diimplementasikan.
     * Mengembalikan nama tabel database untuk Donatur.
     * @return string Nama tabel.
     */
    protected static function getTableName(): string {
        return 'donatur'; // Nama tabel untuk donatur
    }

    /**
     * Metode abstrak dari kelas Pengguna yang harus diimplementasikan.
     * Mengembalikan nama file foto profil default untuk Donatur.
     * @return string Nama file default.
     */
    protected static function getFotoProfilDefault(): string {
        return 'default_donatur.jpg';
    }

    /**
     * Constructor untuk kelas Donatur.
     */
    public function __construct() {
        parent::__construct(); // Panggil constructor parent (Pengguna)
        if (empty($this->fotoProfil)) {
            $this->setFotoProfil(null); 
        }
    }

    // Getter dan Setter untuk properti spesifik 'alamat'
    public function getAlamat() {
        return $this->alamat;
    }

    public function setAlamat($alamat) {
        $this->alamat = !empty(trim($alamat)) ? trim($alamat) : null;
    }

    /**
     * Fungsi untuk mendaftarkan donatur baru.
     * Metode ini tetap spesifik di Donatur karena prosesnya mungkin berbeda
     * atau melibatkan field spesifik seperti 'alamat'.
     * @return bool|string True jika berhasil, pesan error (string) jika gagal.
     */
    public function register(): bool|string {
        // Validasi properti yang diwarisi dan properti spesifik
        if (empty($this->namaLengkap) || empty($this->email) || empty($this->password)) {
            return "Data nama lengkap, email, dan password tidak boleh kosong.";
        }
        // fotoProfil akan di-set default oleh constructor jika kosong atau oleh setter parent

        $koneksi = KoneksiDB::getInstance();
        $sql = "INSERT INTO " . self::getTableName() . 
               " (nama_lengkap, email, password, no_telepon, alamat, foto_profil) 
                VALUES (:nama_lengkap, :email, :password, :no_telepon, :alamat, :foto_profil)";
        
        $params = [
            ':nama_lengkap' => $this->namaLengkap,
            ':email'        => $this->email,
            ':password'     => $this->password, // Sudah di-hash oleh setPassword() di parent
            ':no_telepon'   => $this->noTelepon,
            ':alamat'       => $this->alamat,   // Properti spesifik Donatur
            ':foto_profil'  => $this->fotoProfil
        ];

        try {
            $lastInsertId = $koneksi->execute($sql, $params, true);
            if ($lastInsertId) {
                $this->id = (int)$lastInsertId;
                return true;
            }
            return "Registrasi gagal, data tidak dapat disimpan.";
        } catch (PDOException $e) {
            if ($e->getCode() == '23000') { // Error duplikat email
                return "Email sudah terdaftar. Silakan gunakan email lain.";
            }
            error_log("Error Registrasi Donatur: " . $e->getMessage());
            return "Terjadi kesalahan pada sistem saat registrasi. (Code: " . $e->getCode() . ")";
        }
    }

    /**
     * Fungsi untuk proses login donatur.
     * @param string $email
     * @param string $plainPassword
     * @return Donatur|string Objek Donatur jika berhasil, pesan error (string) jika gagal.
     */
    public static function login(string $email, string $plainPassword): Donatur|string {
        $userData = parent::_loginInternal($email, $plainPassword, self::getTableName());
        if (is_array($userData)) {
            return self::hydrate($userData);
        }
        return $userData; // Ini akan berisi pesan error string dari _loginInternal
    }

    /**
     * Fungsi untuk mencari donatur berdasarkan ID.
     * @param int $id ID Donatur
     * @return Donatur|false Objek Donatur jika ditemukan, false jika tidak.
     */
    public static function findById(int $id): Donatur|false {
        $userData = parent::_findByIdInternal($id, self::getTableName());
        if (is_array($userData)) {
            return self::hydrate($userData);
        }
        return false;
    }

    /**
     * Memperbarui data profil donatur.
     * @return bool|string True jika berhasil, pesan error (string) jika gagal.
     */
    public function updateProfil(): bool|string {
        // Metode _updateProfilInternal di kelas Pengguna sudah bisa menangani 'alamat'
        // jika properti itu ada di objek dan tabelnya sesuai.
        return $this->_updateProfilInternal(self::getTableName());
    }

    /**
     * Mengganti password donatur.
     * @param string $passwordLama
     * @param string $passwordBaru
     * @return bool|string True jika berhasil, pesan error (string) jika gagal.
     */
    public function gantiPassword(string $passwordLama, string $passwordBaru): bool|string {
        return $this->_gantiPasswordInternal($passwordLama, $passwordBaru, self::getTableName());
    }
    
    /**
     * Helper method untuk mengubah array data menjadi objek Donatur.
     * @param array $data Array data dari database.
     * @return Donatur Objek Donatur yang sudah diisi.
     */
    protected static function hydrate(array $data): Donatur {
        $donatur = new self(); // 'self' merujuk ke kelas saat ini (Donatur)
        $donatur->setId($data['id']);
        $donatur->setNamaLengkap($data['nama_lengkap']);
        $donatur->setEmail($data['email']);
        // Password tidak di-set dari hidrasi
        $donatur->setNoTelepon($data['no_telepon'] ?? null);
        $donatur->setAlamat($data['alamat'] ?? null); // Set properti alamat
        $donatur->setFotoProfil($data['foto_profil'] ?? null); 
        
        if (isset($data['created_at'])) {
            $donatur->setCreatedAtInternal($data['created_at']);
        }
        if (isset($data['updated_at'])) {
            $donatur->setUpdatedAtInternal($data['updated_at']);
        }
        return $donatur;
    }

} // Akhir kelas Donatur
?>
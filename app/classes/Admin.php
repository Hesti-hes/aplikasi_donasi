<?php

// Pastikan path ini benar relatif terhadap lokasi Admin.php
require_once __DIR__ . '/Pengguna.php'; // PERBAIKAN: DIR dengan dua garis bawah
// KoneksiDB.php sudah di-include oleh Pengguna.php

class Admin extends Pengguna { // Admin sekarang adalah anak dari Pengguna

    // Properti spesifik untuk Admin (jika ada) bisa ditambahkan di sini.
    // Saat ini, semua properti dasar sudah ada di kelas Pengguna.

    /**
     * Metode abstrak dari kelas Pengguna yang harus diimplementasikan.
     * Mengembalikan nama tabel database untuk Admin.
     * @return string Nama tabel.
     */
    protected static function getTableName(): string {
        return 'admin'; // Nama tabel untuk admin
    }

    /**
     * Metode abstrak dari kelas Pengguna yang harus diimplementasikan.
     * Mengembalikan nama file foto profil default untuk Admin.
     * @return string Nama file default.
     */
    protected static function getFotoProfilDefault(): string {
        return 'default_admin.jpg';
    }

    /**
     * Constructor untuk kelas Admin.
     * Bisa memanggil constructor parent jika ada logika di sana.
     */
    public function __construct() {
        parent::__construct(); // Panggil constructor parent (Pengguna)
        // Atur foto profil default jika belum ada saat objek dibuat (setter di parent akan handle)
        if (empty($this->fotoProfil)) {
            // Memanggil setFotoProfil dari kelas Pengguna yang akan menggunakan getFotoProfilDefault() dari kelas Admin ini
            $this->setFotoProfil(null); 
        }
    }

    // Metode register untuk Admin (tetap placeholder atau implementasi khusus jika diperlukan)
    public function register() {
        // Logika registrasi admin biasanya tidak publik.
        return "Registrasi admin tidak diaktifkan. Akun admin dibuat manual.";
    }

    /**
     * Fungsi untuk proses login admin.
     * @param string $email
     * @param string $plainPassword
     * @return Admin|string Objek Admin jika berhasil, pesan error (string) jika gagal.
     */
    public static function login(string $email, string $plainPassword): Admin|string {
        $userData = parent::_loginInternal($email, $plainPassword, self::getTableName());
        if (is_array($userData)) {
            return self::hydrate($userData);
        }
        return $userData; // Ini akan berisi pesan error string dari _loginInternal
    }

    /**
     * Fungsi untuk mencari admin berdasarkan ID.
     * @param int $id ID Admin
     * @return Admin|false Objek Admin jika ditemukan, false jika tidak.
     */
    public static function findById(int $id): Admin|false {
        $userData = parent::_findByIdInternal($id, self::getTableName());
        if (is_array($userData)) {
            return self::hydrate($userData);
        }
        return false;
    }

    /**
     * Memperbarui data profil admin.
     * @return bool|string True jika berhasil, pesan error (string) jika gagal.
     */
    public function updateProfil(): bool|string {
        return $this->_updateProfilInternal(self::getTableName());
    }

    /**
     * Mengganti password admin.
     * @param string $passwordLama
     * @param string $passwordBaru
     * @return bool|string True jika berhasil, pesan error (string) jika gagal.
     */
    public function gantiPassword(string $passwordLama, string $passwordBaru): bool|string {
        return $this->_gantiPasswordInternal($passwordLama, $passwordBaru, self::getTableName());
    }
    
    /**
     * Helper method untuk mengubah array data menjadi objek Admin.
     * @param array $data Array data dari database.
     * @return Admin Objek Admin yang sudah diisi.
     */
    protected static function hydrate(array $data): Admin {
        $admin = new self(); // 'self' merujuk ke kelas saat ini (Admin)
        $admin->setId($data['id']);
        $admin->setNamaLengkap($data['nama_lengkap']);
        $admin->setEmail($data['email']);
        // Password tidak di-set dari hidrasi untuk keamanan
        $admin->setNoTelepon($data['no_telepon'] ?? null);
        $admin->setFotoProfil($data['foto_profil'] ?? null); // Setter akan handle default via getFotoProfilDefault()
        
        if (isset($data['created_at'])) {
            // Menggunakan setter internal yang diwarisi dari Pengguna
            $admin->setCreatedAtInternal($data['created_at']);
        }
        if (isset($data['updated_at'])) {
            // Menggunakan setter internal yang diwarisi dari Pengguna
            $admin->setUpdatedAtInternal($data['updated_at']);
        }
        return $admin;
    }

    // Metode spesifik lainnya untuk Admin bisa ditambahkan di sini

} // Akhir kelas Admin
?>
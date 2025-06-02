<?php

class KoneksiDB {
    private $host = "localhost"; // Sesuaikan dengan host database Anda
    private $db_name = "db_donasi_bencana"; // Ganti dengan nama database yang akan Anda buat
    private $username = "root"; // Sesuaikan dengan username database Anda
    private $password = ""; // Sesuaikan dengan password database Anda
    private $koneksi;
    private static $instance = null;

    // Constructor dibuat private untuk mencegah pembuatan instance langsung
    private function __construct() {
        $dsn = "mysql:host=" . $this->host . ";dbname=" . $this->db_name . ";charset=utf8mb4";
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // Laporkan error sebagai exception
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Hasil query sebagai array asosiatif
            PDO::ATTR_EMULATE_PREPARES   => false, // Gunakan native prepared statements
        ];

        try {
            $this->koneksi = new PDO($dsn, $this->username, $this->password, $options);
        } catch (PDOException $e) {
            // Sebaiknya error handling lebih baik di aplikasi production
            // Untuk sekarang, kita tampilkan pesan errornya
            die("Koneksi Gagal: " . $e->getMessage());
        }
    }

    // Metode untuk mendapatkan instance tunggal dari KoneksiDB (Singleton Pattern)
    public static function getInstance() {
        if (self::$instance == null) {
            self::$instance = new KoneksiDB();
        }
        return self::$instance;
    }

    // Metode untuk mendapatkan objek koneksi PDO
    public function getConnection() {
        return $this->koneksi;
    }

    // Contoh metode untuk menjalankan query umum (SELECT)
    public function query($sql, $params = []) {
        try {
            $stmt = $this->koneksi->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            // Error handling
            error_log("Query Error: " . $e->getMessage()); // Catat error ke log
            return false; // Atau throw exception lagi
        }
    }

    // Contoh metode untuk mengambil semua baris hasil query
    public function fetchAll($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetchAll() : false;
    }

    // Contoh metode untuk mengambil satu baris hasil query
    public function fetchOne($sql, $params = []) {
        $stmt = $this->query($sql, $params);
        return $stmt ? $stmt->fetch() : false;
    }

    // Contoh metode untuk menjalankan query INSERT, UPDATE, DELETE
    // Mengembalikan jumlah baris yang terpengaruh atau ID terakhir jika INSERT
    public function execute($sql, $params = [], $returnLastInsertId = false) {
        try {
            $stmt = $this->koneksi->prepare($sql);
            $stmt->execute($params);
            if ($returnLastInsertId) {
                return $this->koneksi->lastInsertId();
            }
            return $stmt->rowCount(); // Mengembalikan jumlah baris yang terpengaruh
        } catch (PDOException $e) {
            error_log("Execute Error: " . $e->getMessage());
            return false;
        }
    }
    
    // Mencegah cloning instance (bagian dari Singleton Pattern)
    private function __clone() {}

    // Mencegah unserialize instance (bagian dari Singleton Pattern)
    public function __wakeup() {}
}

?>
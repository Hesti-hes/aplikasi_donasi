<?php

require_once __DIR__ . '/../../config/KoneksiDB.php';

class Donasi {
    // Properties (sesuai dengan kolom tabel donasi)
    private $id;
    private $kampanyeId;
    private $donaturId;
    private $jumlahDonasi;
    private $buktiPembayaran; // Nama file bukti pembayaran
    private $pesanDonatur;
    private $statusDonasi; // ENUM('menunggu_persetujuan', 'disetujui', 'ditolak')
    private $tanggalDonasi; // Akan diisi dari database
    private $updatedAt;     // Akan diisi dari database

    // Constructor
    public function __construct() {
        // Default status untuk donasi baru
        $this->statusDonasi = 'menunggu_persetujuan';
    }

    // Getter dan Setter Methods
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = (int)$id;
    }

    public function getKampanyeId() {
        return $this->kampanyeId;
    }

    public function setKampanyeId($kampanyeId) {
        $this->kampanyeId = (int)$kampanyeId;
    }

    public function getDonaturId() {
        return $this->donaturId;
    }

    public function setDonaturId($donaturId) {
        $this->donaturId = (int)$donaturId;
    }

    public function getJumlahDonasi() {
        return $this->jumlahDonasi;
    }

    public function setJumlahDonasi($jumlahDonasi) {
        $this->jumlahDonasi = (float)$jumlahDonasi;
    }

    public function getBuktiPembayaran() {
        return $this->buktiPembayaran;
    }

    public function setBuktiPembayaran($buktiPembayaran) {
        $this->buktiPembayaran = trim($buktiPembayaran);
    }

    public function getPesanDonatur() {
        return $this->pesanDonatur;
    }

    public function setPesanDonatur($pesanDonatur) {
        $this->pesanDonatur = trim($pesanDonatur);
    }

    public function getStatusDonasi() {
        return $this->statusDonasi;
    }

    public function setStatusDonasi($statusDonasi) {
        $allowed_statuses = ['menunggu_persetujuan', 'disetujui', 'ditolak'];
        if (in_array($statusDonasi, $allowed_statuses)) {
            $this->statusDonasi = $statusDonasi;
        }
    }

    public function getTanggalDonasi() {
        return $this->tanggalDonasi;
    }
    
    protected function setTanggalDonasiInternal($tanggal) {
        $this->tanggalDonasi = $tanggal;
    }

    public function getUpdatedAt() {
        return $this->updatedAt;
    }

    protected function setUpdatedAtInternal($tanggal) {
        $this->updatedAt = $tanggal;
    }

    // --- Metode untuk Operasi Database ---

    /**
     * Menyimpan donasi baru ke database.
     * @return int|string ID donasi baru jika berhasil. Pesan error (string) jika gagal.
     */
    public function create() {
        if (empty($this->kampanyeId) || !is_numeric($this->kampanyeId)) {
            return "ID Kampanye tidak valid untuk donasi ini.";
        }
        if (empty($this->donaturId) || !is_numeric($this->donaturId)) {
            return "ID Donatur tidak valid untuk donasi ini.";
        }
        if (!isset($this->jumlahDonasi) || !is_numeric($this->jumlahDonasi) || $this->jumlahDonasi <= 0) {
            return "Jumlah donasi harus berupa angka positif.";
        }
        if (empty($this->buktiPembayaran)) {
            return "Bukti pembayaran wajib diunggah dan nama filenya harus ada.";
        }

        $koneksi = KoneksiDB::getInstance();
        $sql = "INSERT INTO donasi (kampanye_id, donatur_id, jumlah_donasi, 
                                    bukti_pembayaran, pesan_donatur, status_donasi)
                VALUES (:kampanye_id, :donatur_id, :jumlah_donasi, 
                        :bukti_pembayaran, :pesan_donatur, :status_donasi)";
        $params = [
            ':kampanye_id'      => $this->kampanyeId,
            ':donatur_id'       => $this->donaturId,
            ':jumlah_donasi'    => $this->jumlahDonasi,
            ':bukti_pembayaran' => $this->buktiPembayaran,
            ':pesan_donatur'    => $this->pesanDonatur,
            ':status_donasi'    => $this->statusDonasi
        ];
        try {
            $lastInsertId = $koneksi->execute($sql, $params, true);
            if ($lastInsertId) {
                $this->id = (int)$lastInsertId;
                return $this->id;
            }
            return "Penyimpanan donasi gagal, data tidak dapat disimpan."; 
        } catch (PDOException $e) {
            error_log("Error Create Donasi: " . $e->getMessage()); 
            if ($e->getCode() == '23000') {
                if (strpos(strtolower($e->getMessage()), 'fk_donasi_kampanye') !== false || strpos(strtolower($e->getMessage()), '`donasi_ibfk_1`') !== false) {
                     return "Gagal menyimpan donasi: ID Kampanye tidak valid atau tidak ditemukan.";
                } elseif (strpos(strtolower($e->getMessage()), 'fk_donasi_donatur') !== false || strpos(strtolower($e->getMessage()), '`donasi_ibfk_2`') !== false) {
                     return "Gagal menyimpan donasi: ID Donatur tidak valid atau tidak ditemukan.";
                }
                return "Gagal menyimpan donasi karena data tidak valid (misalnya ID Kampanye atau Donatur tidak ada).";
            }
            return "Terjadi kesalahan pada sistem saat menyimpan donasi. (Detail: " . $e->getMessage() . ")";
        }
    }

    /**
     * Mengambil satu data donasi berdasarkan ID.
     * @param int $id ID Donasi
     * @return Donasi|false Objek Donasi jika ditemukan, false jika tidak.
     */
    public static function findById($id) {
        $koneksi = KoneksiDB::getInstance();
        $sql = "SELECT * FROM donasi WHERE id = :id";
        try {
            $data = $koneksi->fetchOne($sql, [':id' => (int)$id]); 
            if ($data) {
                $donasi = new Donasi();
                $donasi->setId($data['id']);
                $donasi->setKampanyeId($data['kampanye_id']);
                $donasi->setDonaturId($data['donatur_id']);
                $donasi->setJumlahDonasi($data['jumlah_donasi']);
                $donasi->setBuktiPembayaran($data['bukti_pembayaran']);
                $donasi->setPesanDonatur($data['pesan_donatur']);
                $donasi->setStatusDonasi($data['status_donasi']);
                $donasi->setTanggalDonasiInternal($data['tanggal_donasi']); 
                $donasi->setUpdatedAtInternal($data['updated_at']);     
                return $donasi;
            }
            return false; 
        } catch (PDOException $e) {
            error_log("Error findById Donasi: " . $e->getMessage());
            return false; 
        }
    }

    /**
     * Mengambil semua donasi berdasarkan status tertentu.
     * @param string $status Status donasi yang dicari ('menunggu_persetujuan', 'disetujui', 'ditolak')
     * @param string $order Urutan (misalnya 'ASC' atau 'DESC' berdasarkan tanggal_donasi)
     * @return array Array berisi objek Donasi, atau array kosong jika tidak ada/error.
     */
    public static function findAllByStatus($status, $order = 'ASC') {
        if (empty($status)) {
            return [];
        }
        $allowed_statuses = ['menunggu_persetujuan', 'disetujui', 'ditolak'];
        if (!in_array($status, $allowed_statuses)) {
            error_log("Peringatan: Status tidak valid diminta di Donasi::findAllByStatus: " . $status);
            return [];
        }
        $order = strtoupper($order);
        if ($order !== 'ASC' && $order !== 'DESC') {
            $order = 'ASC';
        }

        $koneksi = KoneksiDB::getInstance();
        $sql = "SELECT * FROM donasi WHERE status_donasi = :status ORDER BY tanggal_donasi " . $order;
        $params = [':status' => $status];
        
        $list_donasi = [];
        try {
            $rows = $koneksi->fetchAll($sql, $params);
            if ($rows) {
                foreach ($rows as $row) {
                    $donasi = new Donasi();
                    $donasi->setId($row['id']);
                    $donasi->setKampanyeId($row['kampanye_id']);
                    $donasi->setDonaturId($row['donatur_id']);
                    $donasi->setJumlahDonasi($row['jumlah_donasi']);
                    $donasi->setBuktiPembayaran($row['bukti_pembayaran']);
                    $donasi->setPesanDonatur($row['pesan_donatur']);
                    $donasi->setStatusDonasi($row['status_donasi']);
                    $donasi->setTanggalDonasiInternal($row['tanggal_donasi']); 
                    $donasi->setUpdatedAtInternal($row['updated_at']);     
                    $list_donasi[] = $donasi;
                }
            }
        } catch (PDOException $e) {
            error_log("Error findAllByStatus Donasi: " . $e->getMessage());
        }
        return $list_donasi;
    }

    /**
     * Mengambil semua donasi untuk satu kampanye.
     * @param int $kampanyeId
     * @param string|null $status Opsional, filter berdasarkan status donasi
     * @return array Array berisi objek Donasi.
     */
    public static function findAllByKampanyeId($kampanyeId, $status = null) {
        // $koneksi = KoneksiDB::getInstance();
        // $sql = "SELECT * FROM donasi WHERE kampanye_id = :kampanye_id";
        // $params = [':kampanye_id' => (int)$kampanyeId];
        // if ($status !== null) {
        //     $sql .= " AND status_donasi = :status";
        //     $params[':status'] = $status;
        // }
        // $sql .= " ORDER BY tanggal_donasi DESC";
        // ... (logika fetch dan pembuatan objek mirip findAllByStatus) ...
        return []; // Placeholder
    }

    /**
     * Mengambil semua donasi dari satu donatur.
     * @param int $donaturId ID Donatur
     * @param string $order Urutan ('ASC' atau 'DESC' berdasarkan tanggal_donasi)
     * @return array Array berisi objek Donasi.
     */
    public static function findAllByDonaturId($donaturId, $order = 'DESC') {
        if (empty($donaturId) || !is_numeric($donaturId)) {
            return []; 
        }
        $order = strtoupper($order);
        if ($order !== 'ASC' && $order !== 'DESC') {
            $order = 'DESC'; 
        }

        $koneksi = KoneksiDB::getInstance();
        $sql = "SELECT * FROM donasi WHERE donatur_id = :donatur_id ORDER BY tanggal_donasi " . $order;
        $params = [':donatur_id' => (int)$donaturId];
        
        $list_donasi = [];
        try {
            $rows = $koneksi->fetchAll($sql, $params);
            if ($rows) {
                foreach ($rows as $row) {
                    $donasi = new Donasi();
                    $donasi->setId($row['id']);
                    $donasi->setKampanyeId($row['kampanye_id']);
                    $donasi->setDonaturId($row['donatur_id']);
                    $donasi->setJumlahDonasi($row['jumlah_donasi']);
                    $donasi->setBuktiPembayaran($row['bukti_pembayaran']);
                    $donasi->setPesanDonatur($row['pesan_donatur']);
                    $donasi->setStatusDonasi($row['status_donasi']);
                    $donasi->setTanggalDonasiInternal($row['tanggal_donasi']); 
                    $donasi->setUpdatedAtInternal($row['updated_at']);     
                    $list_donasi[] = $donasi;
                }
            }
        } catch (PDOException $e) {
            error_log("Error findAllByDonaturId Donasi: " . $e->getMessage());
        }
        return $list_donasi;
    }
    
    /**
     * Mengubah status donasi (misalnya oleh Admin).
     * @param string $newStatus Status baru ('disetujui', 'ditolak')
     * @return bool|string True jika berhasil, pesan error (string) jika gagal.
     */
    public function updateStatus($newStatus) {
        if (empty($this->id) || !is_numeric($this->id)) {
            return "ID Donasi tidak valid untuk update status.";
        }
        $allowed_statuses = ['disetujui', 'ditolak', 'menunggu_persetujuan']; 
        if (!in_array($newStatus, $allowed_statuses)) {
            return "Status baru ('" . htmlspecialchars($newStatus) . "') tidak valid.";
        }

        $koneksi = KoneksiDB::getInstance();
        $sql = "UPDATE donasi SET status_donasi = :status_donasi, updated_at = NOW() WHERE id = :id";
        $params = [
            ':status_donasi' => $newStatus,
            ':id'            => $this->id
        ];
        try {
            $koneksi->execute($sql, $params);
            $this->setStatusDonasi($newStatus); 
            return true; 
        } catch (PDOException $e) {
            error_log("Error UpdateStatus Donasi (ID: {$this->id}): " . $e->getMessage());
            return "Terjadi kesalahan pada sistem saat update status donasi. (Detail: " . $e->getMessage() . ")";
        }
    }

    /**
     * Menghitung jumlah donasi berdasarkan status tertentu.
     * @param string $status Status donasi yang dicari ('menunggu_persetujuan', 'disetujui', 'ditolak')
     * @return int Jumlah donasi dengan status tersebut.
     */
    public static function countByStatus($status) {
        if (empty($status)) {
            return 0; 
        }
        $allowed_statuses = ['menunggu_persetujuan', 'disetujui', 'ditolak'];
        if (!in_array($status, $allowed_statuses)) {
            error_log("Peringatan: Status tidak valid diminta di Donasi::countByStatus: " . $status);
            return 0; 
        }

        $koneksi = KoneksiDB::getInstance();
        $sql = "SELECT COUNT(*) as total FROM donasi WHERE status_donasi = :status";
        $params = [':status' => $status];
        
        try {
            $result = $koneksi->fetchOne($sql, $params);
            return $result ? (int)$result['total'] : 0;
        } catch (PDOException $e) {
            error_log("Error countByStatus Donasi: " . $e->getMessage());
            return 0; 
        }
    }
} // Akhir dari kelas Donasi
?>
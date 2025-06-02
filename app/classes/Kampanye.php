<?php

// Pastikan path ini benar relatif terhadap lokasi Kampanye.php
require_once __DIR__ . '/../../config/KoneksiDB.php';

class Kampanye {
    // Properties (sesuai dengan kolom tabel kampanye)
    private $id;
    private $adminId; // Foreign Key ke tabel admin
    private $judul;
    private $deskripsiSingkat;
    private $deskripsiLengkap;
    private $gambarKampanye; // Akan berisi nama file gambar
    private $targetDonasi;
    private $danaTerkumpul;
    private $tanggalMulai;
    private $tanggalSelesai;
    private $statusKampanye; // ENUM('aktif', 'selesai', 'ditutup')
    private $createdAt;
    private $updatedAt;

    // Constructor
    public function __construct() {
        $this->danaTerkumpul = 0.00;
        $this->statusKampanye = 'aktif'; // Default status untuk kampanye baru
    }

    // Getter dan Setter Methods
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = (int)$id;
    }

    public function getAdminId() {
        return $this->adminId;
    }

    public function setAdminId($adminId) {
        $this->adminId = (int)$adminId;
    }

    public function getJudul() {
        return $this->judul;
    }

    public function setJudul($judul) {
        $this->judul = trim($judul);
    }

    public function getDeskripsiSingkat() {
        return $this->deskripsiSingkat;
    }

    public function setDeskripsiSingkat($deskripsiSingkat) {
        $this->deskripsiSingkat = trim($deskripsiSingkat);
    }

    public function getDeskripsiLengkap() {
        return $this->deskripsiLengkap;
    }

    public function setDeskripsiLengkap($deskripsiLengkap) {
        $this->deskripsiLengkap = trim($deskripsiLengkap);
    }

    public function getGambarKampanye() {
        return $this->gambarKampanye;
    }

    public function setGambarKampanye($gambarKampanye) {
        $this->gambarKampanye = trim($gambarKampanye);
    }

    public function getTargetDonasi() {
        return $this->targetDonasi;
    }

    public function setTargetDonasi($targetDonasi) {
        $this->targetDonasi = (float)$targetDonasi;
    }

    public function getDanaTerkumpul() {
        return $this->danaTerkumpul;
    }

    public function setDanaTerkumpul($danaTerkumpul) {
        $this->danaTerkumpul = (float)$danaTerkumpul;
    }

    public function getTanggalMulai() {
        return $this->tanggalMulai;
    }

    public function setTanggalMulai($tanggalMulai) {
        $this->tanggalMulai = $tanggalMulai;
    }

    public function getTanggalSelesai() {
        return $this->tanggalSelesai;
    }

    public function setTanggalSelesai($tanggalSelesai) {
        $this->tanggalSelesai = $tanggalSelesai;
    }

    public function getStatusKampanye() {
        return $this->statusKampanye;
    }

    public function setStatusKampanye($statusKampanye) {
        $allowed_statuses = ['aktif', 'selesai', 'ditutup'];
        if (in_array($statusKampanye, $allowed_statuses)) {
            $this->statusKampanye = $statusKampanye;
        } else {
            $this->statusKampanye = 'aktif'; 
        }
    }

    public function getCreatedAt() {
        return $this->createdAt;
    }
    
    protected function setCreatedAtInternal($tanggal) {
        $this->createdAt = $tanggal;
    }

    public function getUpdatedAt() {
        return $this->updatedAt;
    }

    protected function setUpdatedAtInternal($tanggal) {
        $this->updatedAt = $tanggal;
    }

    // --- Metode untuk Operasi Database ---

    /**
     * Menyimpan kampanye baru ke database.
     * @return int|string ID kampanye baru jika berhasil. Pesan error (string) jika gagal.
     */
    public function create() {
        if (empty($this->judul)) {
            return "Judul kampanye tidak boleh kosong.";
        }
        if (empty($this->adminId) || !is_numeric($this->adminId)) {
            return "ID Admin tidak valid atau belum di-set untuk kampanye ini.";
        }
        if (!isset($this->targetDonasi) || !is_numeric($this->targetDonasi) || $this->targetDonasi < 0) {
            return "Target donasi harus berupa angka non-negatif.";
        }
        
        $koneksi = KoneksiDB::getInstance();
        $sql = "INSERT INTO kampanye (admin_id, judul, deskripsi_singkat, deskripsi_lengkap, 
                                    gambar_kampanye, target_donasi, dana_terkumpul, 
                                    tanggal_mulai, tanggal_selesai, status_kampanye)
                VALUES (:admin_id, :judul, :deskripsi_singkat, :deskripsi_lengkap, 
                        :gambar_kampanye, :target_donasi, :dana_terkumpul, 
                        :tanggal_mulai, :tanggal_selesai, :status_kampanye)";
        $params = [
            ':admin_id'           => $this->adminId,
            ':judul'              => $this->judul,
            ':deskripsi_singkat'  => $this->deskripsiSingkat,
            ':deskripsi_lengkap'  => $this->deskripsiLengkap,
            ':gambar_kampanye'    => $this->gambarKampanye,
            ':target_donasi'      => $this->targetDonasi,
            ':dana_terkumpul'     => $this->danaTerkumpul,
            ':tanggal_mulai'      => $this->tanggalMulai,
            ':tanggal_selesai'    => $this->tanggalSelesai,
            ':status_kampanye'    => $this->statusKampanye
        ];
        try {
            $lastInsertId = $koneksi->execute($sql, $params, true); 
            if ($lastInsertId) {
                $this->id = (int)$lastInsertId; 
                return $this->id; 
            }
            return "Pembuatan kampanye gagal, data tidak dapat disimpan."; 
        } catch (PDOException $e) {
            error_log("Error Create Kampanye: " . $e->getMessage()); 
            return "Terjadi kesalahan pada sistem saat membuat kampanye. (Detail: " . $e->getMessage() . ")";
        }
    }

    /**
     * Memperbarui data kampanye yang sudah ada di database.
     * @return bool|string True jika berhasil, pesan error (string) jika gagal.
     */
    public function update() {
        if (empty($this->id) || !is_numeric($this->id)) {
            return "ID Kampanye tidak valid atau tidak ada untuk proses update.";
        }
        if (empty($this->judul)) {
            return "Judul kampanye tidak boleh kosong.";
        }
        if (!isset($this->targetDonasi) || !is_numeric($this->targetDonasi) || $this->targetDonasi < 0) {
            return "Target donasi harus berupa angka non-negatif.";
        }
        if (!isset($this->danaTerkumpul) || !is_numeric($this->danaTerkumpul) || $this->danaTerkumpul < 0) {
            return "Dana terkumpul harus berupa angka non-negatif.";
        }

        $koneksi = KoneksiDB::getInstance();
        $sql = "UPDATE kampanye SET 
                    judul = :judul, 
                    deskripsi_singkat = :deskripsi_singkat, 
                    deskripsi_lengkap = :deskripsi_lengkap,
                    gambar_kampanye = :gambar_kampanye, 
                    target_donasi = :target_donasi, 
                    dana_terkumpul = :dana_terkumpul, 
                    tanggal_mulai = :tanggal_mulai, 
                    tanggal_selesai = :tanggal_selesai, 
                    status_kampanye = :status_kampanye
                WHERE id = :id";
        // Jika Anda ingin menambahkan klausa WHERE admin_id = :admin_id_original untuk keamanan tambahan (seperti di Langkah 37),
        // pastikan $this->adminId sudah di-set dengan benar (tidak diubah saat edit) dan tambahkan ke $params.
        $params = [
            ':id'                 => $this->id,
            ':judul'              => $this->judul,
            ':deskripsi_singkat'  => $this->deskripsiSingkat,
            ':deskripsi_lengkap'  => $this->deskripsiLengkap,
            ':gambar_kampanye'    => $this->gambarKampanye,
            ':target_donasi'      => $this->targetDonasi,
            ':dana_terkumpul'     => $this->danaTerkumpul,
            ':tanggal_mulai'      => $this->tanggalMulai,
            ':tanggal_selesai'    => $this->tanggalSelesai,
            ':status_kampanye'    => $this->statusKampanye
        ];
        try {
            $koneksi->execute($sql, $params);
            return true; 
        } catch (PDOException $e) {
            error_log("Error Update Kampanye (ID: {$this->id}): " . $e->getMessage());
            return "Terjadi kesalahan pada sistem saat memperbarui kampanye. (Detail: " . $e->getMessage() . ")";
        }
    }

    /**
     * Menghapus kampanye dari database berdasarkan ID.
     * @param int $id ID Kampanye yang akan dihapus
     * @return bool|string True jika berhasil, pesan error (string) jika gagal.
     */
    public static function delete($id) {
        if (empty($id) || !is_numeric($id)) {
            return "ID Kampanye tidak valid untuk proses hapus.";
        }
        $koneksi = KoneksiDB::getInstance();
        $sql = "DELETE FROM kampanye WHERE id = :id";
        try {
            $rowCount = $koneksi->execute($sql, [':id' => (int)$id]);
            if ($rowCount > 0) {
                return true; 
            } else {
                return "Kampanye dengan ID tersebut tidak ditemukan atau sudah terhapus."; 
            }
        } catch (PDOException $e) {
            error_log("Error Delete Kampanye (ID: {$id}): " . $e->getMessage());
            if ($e->getCode() == '23000') { 
                return "Gagal menghapus kampanye. Mungkin masih ada donasi terkait dengan kampanye ini.";
            }
            return "Terjadi kesalahan pada sistem saat menghapus kampanye. (Detail: " . $e->getMessage() . ")";
        }
    }

    /**
     * Mengambil satu data kampanye berdasarkan ID.
     * @param int $id ID Kampanye
     * @return Kampanye|false Objek Kampanye jika ditemukan, false jika tidak.
     */
    public static function findById($id) {
        if (empty($id) || !is_numeric($id)) {
            return false; 
        }
        $koneksi = KoneksiDB::getInstance();
        $sql = "SELECT * FROM kampanye WHERE id = :id";
        try {
            $data = $koneksi->fetchOne($sql, [':id' => (int)$id]); 
            if ($data) {
                $kampanye = new Kampanye();
                $kampanye->setId($data['id']);
                $kampanye->setAdminId($data['admin_id']);
                $kampanye->setJudul($data['judul']);
                $kampanye->setDeskripsiSingkat($data['deskripsi_singkat']);
                $kampanye->setDeskripsiLengkap($data['deskripsi_lengkap']);
                $kampanye->setGambarKampanye($data['gambar_kampanye']);
                $kampanye->setTargetDonasi($data['target_donasi']);
                $kampanye->setDanaTerkumpul($data['dana_terkumpul']);
                $kampanye->setTanggalMulai($data['tanggal_mulai']);
                $kampanye->setTanggalSelesai($data['tanggal_selesai']);
                $kampanye->setStatusKampanye($data['status_kampanye']);
                if (isset($data['created_at'])) $kampanye->setCreatedAtInternal($data['created_at']);
                if (isset($data['updated_at'])) $kampanye->setUpdatedAtInternal($data['updated_at']);
                return $kampanye;
            }
            return false; 
        } catch (PDOException $e) {
            error_log("Error FindById Kampanye (ID: {$id}): " . $e->getMessage());
            return false; 
        }
    }

    /**
     * Mengambil semua data kampanye, atau berdasarkan filter tertentu.
     * @param string|null $status Untuk filter berdasarkan status_kampanye ('aktif', 'selesai', 'ditutup'). Jika null, semua status.
     * @param int|null $limit Jumlah maksimal baris yang diambil (untuk pagination).
     * @param int|null $offset Jumlah baris yang dilewati dari awal (untuk pagination).
     * @return array Array berisi objek Kampanye, atau array kosong.
     */
    public static function findAll($status = null, $limit = null, $offset = null) {
        $koneksi = KoneksiDB::getInstance();
        $sql = "SELECT * FROM kampanye"; 
        $params = [];
        $conditions = [];

        if ($status !== null) {
            $allowed_statuses = ['aktif', 'selesai', 'ditutup'];
            if (in_array($status, $allowed_statuses)) {
                $conditions[] = "status_kampanye = :status";
                $params[':status'] = $status;
            }
        }

        if (!empty($conditions)) {
            $sql .= " WHERE " . implode(" AND ", $conditions);
        }
        
        $sql .= " ORDER BY id DESC"; 

        if ($limit !== null && is_numeric($limit) && $limit > 0) {
            $sql .= " LIMIT :limit";
            $params[':limit'] = (int)$limit; 
            if ($offset !== null && is_numeric($offset) && $offset >= 0) {
                $sql .= " OFFSET :offset";
                $params[':offset'] = (int)$offset; 
            }
        }
        
        $list_kampanye = [];
        try {
            $rows = $koneksi->fetchAll($sql, $params);
            if ($rows) {
                foreach ($rows as $row) {
                    $kampanye = new Kampanye();
                    $kampanye->setId($row['id']);
                    $kampanye->setAdminId($row['admin_id']);
                    $kampanye->setJudul($row['judul']);
                    $kampanye->setDeskripsiSingkat($row['deskripsi_singkat']);
                    $kampanye->setDeskripsiLengkap($row['deskripsi_lengkap']);
                    $kampanye->setGambarKampanye($row['gambar_kampanye']);
                    $kampanye->setTargetDonasi($row['target_donasi']);
                    $kampanye->setDanaTerkumpul($row['dana_terkumpul']);
                    $kampanye->setTanggalMulai($row['tanggal_mulai']);
                    $kampanye->setTanggalSelesai($row['tanggal_selesai']);
                    $kampanye->setStatusKampanye($row['status_kampanye']);
                    if (isset($row['created_at'])) $kampanye->setCreatedAtInternal($row['created_at']);
                    if (isset($row['updated_at'])) $kampanye->setUpdatedAtInternal($row['updated_at']);
                    $list_kampanye[] = $kampanye;
                }
            }
        } catch (PDOException $e) {
            error_log("Error FindAll Kampanye: " . $e->getMessage());
        }
        return $list_kampanye;
    }

    /**
     * Mengambil semua data kampanye yang dibuat oleh admin tertentu.
     * @param int $adminId ID Admin
     * @return array Array berisi objek Kampanye.
     */
    public static function findAllByAdmin($adminId) {
        if (empty($adminId) || !is_numeric($adminId)) {
            return [];
        }
        $koneksi = KoneksiDB::getInstance();
        $sql = "SELECT * FROM kampanye WHERE admin_id = :admin_id ORDER BY id DESC";
        $params = [':admin_id' => (int)$adminId];
        $list_kampanye = [];
        try {
            $rows = $koneksi->fetchAll($sql, $params);
            if ($rows) {
                foreach ($rows as $row) {
                    $kampanye = new Kampanye();
                    $kampanye->setId($row['id']);
                    $kampanye->setAdminId($row['admin_id']);
                    $kampanye->setJudul($row['judul']);
                    $kampanye->setDeskripsiSingkat($row['deskripsi_singkat']);
                    $kampanye->setDeskripsiLengkap($row['deskripsi_lengkap']);
                    $kampanye->setGambarKampanye($row['gambar_kampanye']);
                    $kampanye->setTargetDonasi($row['target_donasi']);
                    $kampanye->setDanaTerkumpul($row['dana_terkumpul']);
                    $kampanye->setTanggalMulai($row['tanggal_mulai']);
                    $kampanye->setTanggalSelesai($row['tanggal_selesai']);
                    $kampanye->setStatusKampanye($row['status_kampanye']);
                    if (isset($row['created_at'])) $kampanye->setCreatedAtInternal($row['created_at']);
                    if (isset($row['updated_at'])) $kampanye->setUpdatedAtInternal($row['updated_at']);
                    $list_kampanye[] = $kampanye;
                }
            }
        } catch (PDOException $e) {
            error_log("Error FindAllByAdmin Kampanye (AdminID: {$adminId}): " . $e->getMessage());
        }
        return $list_kampanye;
    }

    /**
     * Menghitung jumlah kampanye berdasarkan status.
     * @param string|null $status Jika null, hitung semua kampanye. 
     * Jika diisi ('aktif', 'selesai', 'ditutup'), hitung berdasarkan status tersebut.
     * @return int Jumlah kampanye.
     */
    public static function countByStatus($status = null) {
        $koneksi = KoneksiDB::getInstance();
        $sql = "SELECT COUNT(*) as total FROM kampanye";
        $params = [];

        if ($status !== null) {
            $allowed_statuses = ['aktif', 'selesai', 'ditutup'];
            if (in_array($status, $allowed_statuses)) {
                $sql .= " WHERE status_kampanye = :status";
                $params[':status'] = $status;
            }
        }

        try {
            $result = $koneksi->fetchOne($sql, $params);
            return $result ? (int)$result['total'] : 0;
        } catch (PDOException $e) {
            error_log("Error countByStatus Kampanye: " . $e->getMessage());
            return 0; 
        }
    }

} // Akhir dari kelas Kampanye
?>
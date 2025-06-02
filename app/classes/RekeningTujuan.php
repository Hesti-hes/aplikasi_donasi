<?php

require_once __DIR__ . '/../../config/KoneksiDB.php';

class RekeningTujuan {
    // Properties (sesuai dengan kolom tabel rekening_tujuan)
    private $id;
    private $namaBank;
    private $nomorRekening;
    private $atasNama;
    // Properti createdAt dan updatedAt bisa ditambahkan jika ingin di-load ke objek
    // private $createdAt; 
    // private $updatedAt;

    // Constructor (bisa dikosongkan atau diisi jika perlu inisialisasi)
    public function __construct() {
    }

    // Getter dan Setter Methods
    public function getId() {
        return $this->id;
    }

    public function setId($id) {
        $this->id = (int)$id;
    }

    public function getNamaBank() {
        return $this->namaBank;
    }

    public function setNamaBank($namaBank) {
        $this->namaBank = trim($namaBank);
    }

    public function getNomorRekening() {
        return $this->nomorRekening;
    }

    public function setNomorRekening($nomorRekening) {
        $this->nomorRekening = trim($nomorRekening);
    }

    public function getAtasNama() {
        return $this->atasNama;
    }

    public function setAtasNama($atasNama) {
        $this->atasNama = trim($atasNama);
    }

    // --- Metode untuk Operasi Database ---

    /**
     * Mengambil semua rekening tujuan dari database.
     * @return array Array berisi objek RekeningTujuan, atau array kosong jika tidak ada/error.
     */
    public static function getAllRekening() {
        $koneksi = KoneksiDB::getInstance();
        $sql = "SELECT * FROM rekening_tujuan ORDER BY id ASC"; 
        
        $list_rekening = [];
        try {
            $rows = $koneksi->fetchAll($sql); 
            if ($rows) {
                foreach ($rows as $row) {
                    $rekening = new RekeningTujuan();
                    $rekening->setId($row['id']);
                    $rekening->setNamaBank($row['nama_bank']);
                    $rekening->setNomorRekening($row['nomor_rekening']);
                    $rekening->setAtasNama($row['atas_nama']);
                    $list_rekening[] = $rekening;
                }
            }
        } catch (PDOException $e) {
            error_log("Error getAllRekening: " . $e->getMessage());
        }
        return $list_rekening;
    }

    /**
     * Mengambil satu data rekening berdasarkan ID.
     * @param int $id ID Rekening
     * @return RekeningTujuan|false Objek RekeningTujuan jika ditemukan, false jika tidak.
     */
    public static function findById($id) {
        if (empty($id) || !is_numeric($id)) {
            return false; 
        }

        $koneksi = KoneksiDB::getInstance();
        $sql = "SELECT * FROM rekening_tujuan WHERE id = :id";
        
        try {
            $data = $koneksi->fetchOne($sql, [':id' => (int)$id]); 
            
            if ($data) {
                $rekening = new RekeningTujuan();
                $rekening->setId($data['id']);
                $rekening->setNamaBank($data['nama_bank']);
                $rekening->setNomorRekening($data['nomor_rekening']);
                $rekening->setAtasNama($data['atas_nama']);
                return $rekening;
            }
            return false; 
        } catch (PDOException $e) {
            error_log("Error FindById RekeningTujuan: " . $e->getMessage());
            return false; 
        }
    }
    
    /**
     * Menambah rekening baru (oleh Admin).
     * Menggunakan properti objek yang sudah di-set.
     * @return int|string ID rekening baru jika berhasil, pesan error (string) jika gagal.
     */
    public function create() {
        if (empty($this->namaBank)) {
            return "Nama bank tidak boleh kosong.";
        }
        if (empty($this->nomorRekening)) {
            return "Nomor rekening tidak boleh kosong.";
        }
        if (empty($this->atasNama)) {
            return "Nama pemilik rekening (atas nama) tidak boleh kosong.";
        }

        $koneksi = KoneksiDB::getInstance();
        $sql = "INSERT INTO rekening_tujuan (nama_bank, nomor_rekening, atas_nama)
                VALUES (:nama_bank, :nomor_rekening, :atas_nama)";
        $params = [
            ':nama_bank'      => $this->namaBank,
            ':nomor_rekening' => $this->nomorRekening,
            ':atas_nama'      => $this->atasNama
        ];

        try {
            $lastInsertId = $koneksi->execute($sql, $params, true); 
            if ($lastInsertId) {
                $this->id = (int)$lastInsertId; 
                return $this->id; 
            }
            return "Penambahan rekening gagal, data tidak dapat disimpan."; 
        } catch (PDOException $e) {
            error_log("Error Create RekeningTujuan: " . $e->getMessage());
            if ($e->getCode() == '23000') { 
                 return "Gagal menambah rekening. Nomor rekening mungkin sudah terdaftar atau ada data tidak valid lainnya.";
            }
            return "Terjadi kesalahan pada sistem saat menambah rekening. (Detail: " . $e->getMessage() . ")";
        }
    }

    /**
     * Mengupdate data rekening yang sudah ada di database.
     * Menggunakan properti objek yang sudah di-set. ID rekening harus sudah ada di objek.
     * @return bool|string True jika berhasil, pesan error (string) jika gagal.
     */
    public function update() {
        if (empty($this->id) || !is_numeric($this->id)) {
            return "ID Rekening tidak valid atau tidak ada untuk proses update.";
        }
        if (empty($this->namaBank)) {
            return "Nama bank tidak boleh kosong.";
        }
        if (empty($this->nomorRekening)) {
            return "Nomor rekening tidak boleh kosong.";
        }
        if (empty($this->atasNama)) {
            return "Nama pemilik rekening (atas nama) tidak boleh kosong.";
        }

        $koneksi = KoneksiDB::getInstance();
        $sql = "UPDATE rekening_tujuan SET 
                    nama_bank = :nama_bank, 
                    nomor_rekening = :nomor_rekening, 
                    atas_nama = :atas_nama
                    -- updated_at akan otomatis diupdate oleh MySQL jika tabel diset ON UPDATE CURRENT_TIMESTAMP
                WHERE id = :id";
        $params = [
            ':id'              => $this->id,
            ':nama_bank'       => $this->namaBank,
            ':nomor_rekening'  => $this->nomorRekening,
            ':atas_nama'       => $this->atasNama
        ];

        try {
            $koneksi->execute($sql, $params);
            return true; 
        } catch (PDOException $e) {
            error_log("Error Update RekeningTujuan (ID: {$this->id}): " . $e->getMessage());
            if ($e->getCode() == '23000') { 
                 return "Gagal update rekening. Nomor rekening mungkin sudah digunakan oleh rekening lain.";
            }
            return "Terjadi kesalahan pada sistem saat memperbarui rekening. (Detail: " . $e->getMessage() . ")";
        }
    }

    /**
     * Menghapus rekening dari database berdasarkan ID.
     * @param int $id ID Rekening yang akan dihapus
     * @return bool|string True jika berhasil, pesan error (string) jika gagal.
     */
    public static function delete($id) {
        if (empty($id) || !is_numeric($id)) {
            return "ID Rekening tidak valid untuk proses hapus.";
        }

        $koneksi = KoneksiDB::getInstance();
        $sql = "DELETE FROM rekening_tujuan WHERE id = :id";
        
        try {
            $rowCount = $koneksi->execute($sql, [':id' => (int)$id]);
            
            if ($rowCount > 0) {
                return true; 
            } else {
                return "Rekening dengan ID tersebut tidak ditemukan atau sudah terhapus."; 
            }
        } catch (PDOException $e) {
            error_log("Error Delete RekeningTujuan (ID: {$id}): " . $e->getMessage());
            return "Terjadi kesalahan pada sistem saat menghapus rekening. (Detail: " . $e->getMessage() . ")";
        }
    }
} // Akhir dari kelas RekeningTujuan
?>
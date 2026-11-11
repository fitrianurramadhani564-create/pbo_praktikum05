<?php
require_once 'Mahasiswa.php';

class DataMahasiswa {
    public function __construct() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        if (!isset($_SESSION['data_mahasiswa'])) {
            $_SESSION['data_mahasiswa'] = [];
        }
    }

    public function getAll(): array {
        return $_SESSION['data_mahasiswa'];
    }

    public function cari(string $nim): ?Mahasiswa {
        return $_SESSION['data_mahasiswa'][$nim] ?? null;
    }

    public function tambah(Mahasiswa $m): void {
        if ($this->cari($m->getNim()) !== null) {
            throw new InvalidArgumentException("NIM '{$m->getNim()}' sudah terdaftar.");
        }
        $_SESSION['data_mahasiswa'][$m->getNim()] = $m;
    }

    public function ubah(Mahasiswa $m): void {
        if ($this->cari($m->getNim()) === null) {
            throw new InvalidArgumentException("Data Mahasiswa tidak ditemukan.");
        }
        $_SESSION['data_mahasiswa'][$m->getNim()] = $m;
    }

    public function hapus(string $nim): void {
        unset($_SESSION['data_mahasiswa'][$nim]);
    }
}
?>
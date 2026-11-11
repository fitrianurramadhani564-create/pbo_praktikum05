<?php
// Tugas 2 PBO - Validasi Stok Buku

class Buku {
    private string $kodeBuku;
    private string $judul;
    private int $stok;

    public function __construct(string $kodeBuku, string $judul, int $stok) {
        $kodeBuku = trim($kodeBuku);
        if ($kodeBuku === "") {
            throw new InvalidArgumentException("Kode buku tidak boleh kosong.");
        }
        $this->kodeBuku = $kodeBuku; // read-only

        $this->setJudul($judul);

        if ($stok < 0) {
            throw new InvalidArgumentException("Stok awal tidak boleh minus.");
        }
        $this->stok = $stok;
    }

    public function getKodeBuku(): string { return $this->kodeBuku; }
    public function getJudul(): string { return $this->judul; }
    public function getStok(): int { return $this->stok; }

    public function setJudul(string $judul): void {
        $judul = trim($judul);
        if ($judul === "") {
            throw new InvalidArgumentException("Judul buku wajib diisi.");
        }
        $this->judul = $judul;
    }

    public function tambahStok(int $jumlah): void {
        if ($jumlah <= 0) {
            throw new InvalidArgumentException("Tambah stok harus lebih dari 0.");
        }
        $this->stok += $jumlah;
    }

    public function kurangiStok(int $jumlah): void {
        if ($jumlah <= 0) {
            throw new InvalidArgumentException("Kurang stok harus lebih dari 0.");
        }
        if ($jumlah > $this->stok) {
            throw new InvalidArgumentException("Stok kurang / tidak cukup.");
        }
        $this->stok -= $jumlah;
    }
}

echo "<h3>Uji Coba Class Buku:</h3>";

try {
    // input data yang valid
    $buku1 = new Buku("BK-001", "Pemrograman Web PHP 8", 10);
    echo "Judul: <b>" . $buku1->getJudul() . "</b> (Stok Awal: " . $buku1->getStok() . ")<br>";

    $buku1->tambahStok(5);
    echo "Stok tambah 5 -> " . $buku1->getStok() . "<br>";

    $buku1->kurangiStok(3);
    echo "Stok kurang 3 -> " . $buku1->getStok() . "<br>";

    // tes error (hilangkan // kalau mau tes):
    // $bukuErr1 = new Buku("BK-002", "", 5);
    // $bukuErr2 = new Buku("BK-003", "Buku B", -2);
    // $buku1->kurangiStok(50);

} catch (InvalidArgumentException $e) {
    echo "<span style='color:red;'><b>Error:</b> " . $e->getMessage() . "</span>";
}
?>
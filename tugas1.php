<?php
// Access Modifier

class Produk {
    public string $nama;
    protected string $kategori;
    private int $harga;

    public function __construct(string $nama, string $kategori, int $harga) {
        $this->nama = $nama;
        $this->kategori = $kategori;
        $this->harga = $harga;
    }

    public function getHarga(): int {
        return $this->harga;
    }
}

class ProdukDigital extends Produk {
    public function getKategoriSubclass(): string {
        // atribut protected bisa dipanggil dari subclass
        return $this->kategori;
    }
}

// buat objek baru
$produk = new Produk("Laptop Asus", "Elektronik", 8500000);
$produkDigital = new ProdukDigital("E-Book PHP 8", "Buku Digital", 50000);

echo "<h3>Hasil Tes Modifier:</h3>";
echo "Nama (public): " . $produk->nama . "<br>";
echo "Kategori (protected): " . $produkDigital->getKategoriSubclass() . "<br>";
echo "Harga (private): Rp " . number_format($produk->getHarga(), 0, ',', '.') . "<br>";

// Coba buka comment di bawah buat cek error:
// echo $produk->kategori;
// echo $produk->harga;
?>
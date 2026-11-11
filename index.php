<?php
ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

require_once __DIR__ . '/Mahasiswa.php';
require_once __DIR__ . '/MahasiswaReguler.php';
require_once __DIR__ . '/MahasiswaBeasiswa.php';
require_once __DIR__ . '/DataMahasiswa.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function aman(mixed $v): string {
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

$repo = new DataMahasiswa($_SESSION['mahasiswa'] ?? []);
$error = '';
$edit = null;

if (isset($_GET['action']) && $_GET['action'] === 'hapus' && isset($_GET['nim'])) {
    $repo->hapus((string)$_GET['nim']);
    $_SESSION['mahasiswa'] = $repo->semua();
    header('Location: index.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['nim'])) {
    $edit = $repo->cari((string)$_GET['nim']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $nim = trim((string)($_POST['nim'] ?? ''));
        $nama = trim((string)($_POST['nama'] ?? ''));
        $prodi = trim((string)($_POST['prodi'] ?? ''));
        $semester = filter_var($_POST['semester'] ?? null, FILTER_VALIDATE_INT);
        $ukt = filter_var($_POST['ukt'] ?? null, FILTER_VALIDATE_INT);
        $jenis = (string)($_POST['jenis'] ?? 'reguler');
        $aksi = (string)($_POST['aksi'] ?? 'tambah');

        if ($semester === false || $ukt === false) {
            throw new InvalidArgumentException('Semester dan UKT harus bilangan bulat');
        }

        if ($jenis === 'reguler') {
            $m = new MahasiswaReguler($nim, $nama, $prodi, $semester, $ukt);
        } elseif ($jenis === 'beasiswa') {
            $potongan = filter_var($_POST['potongan'] ?? null, FILTER_VALIDATE_INT);
            if ($potongan === false) {
                throw new InvalidArgumentException('Potongan tidak valid');
            }
            $m = new MahasiswaBeasiswa($nim, $nama, $prodi, $semester, $ukt, $potongan);
        } else {
            throw new InvalidArgumentException('Jenis mahasiswa tidak dikenal');
        }

        $ok = ($aksi === 'ubah') 
            ? $repo->ubah((string)($_POST['nim_lama'] ?? ''), $m)
            : $repo->tambah($m);

        if (!$ok) {
            throw new InvalidArgumentException('NIM duplikat atau data edit tidak cocok');
        }

        $_SESSION['mahasiswa'] = $repo->semua();
        header('Location: index.php');
        exit;
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    }
}

// Ambil nilai UKT dan Potongan secara aman untuk form
$valUkt = '';
$valPotongan = '0';
if ($edit instanceof MahasiswaReguler) {
    $valUkt = (string)$edit->getUkt();
} elseif ($edit instanceof MahasiswaBeasiswa) {
    $valUkt = (string)$edit->getUkt();
    $valPotongan = (string)$edit->getPotonganPersen();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>CRUD Mahasiswa Inheritance - Pertemuan 06</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 15px; }
        h2 { margin-top: 0; margin-bottom: 10px; }
        .error { background: #f8d7da; color: #842029; padding: 8px; border-radius: 4px; margin-bottom: 10px; }
        .form-box { background: #f9f9f9; padding: 12px; border: 1px solid #ddd; width: 400px; margin-bottom: 15px; border-radius: 5px; }
        .form-box h3 { margin-top: 0; margin-bottom: 10px; font-size: 16px; }
        .form-group { margin-bottom: 6px; }
        .form-group label { display: inline-block; width: 130px; font-weight: bold; font-size: 14px; }
        .form-group input, .form-group select { width: 220px; padding: 4px; font-size: 14px; }
        button { padding: 6px 15px; cursor: pointer; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #ddd; padding: 6px 8px; text-align: left; font-size: 14px; }
        th { background: #eee; }
    </style>
    <script>
        function sesuaikanForm() {
            var jenis = document.getElementById('jenis').value;
            var boxPotongan = document.getElementById('box-potongan');
            if (jenis === 'beasiswa') {
                boxPotongan.style.display = 'block';
            } else {
                boxPotongan.style.display = 'none';
            }
        }
    </script>
</head>
<body onload="sesuaikanForm()">

    <h2>CRUD Data Mahasiswa (Inheritance - Pertemuan 06)</h2>

    <?php if ($error !== ''): ?>
        <div class="error"><?= aman($error) ?></div>
    <?php endif; ?>

    <div class="form-box">
        <h3><?= $edit ? 'Edit Mahasiswa' : 'Tambah Mahasiswa' ?></h3>
        <form action="index.php" method="POST">
            <input type="hidden" name="aksi" value="<?= $edit ? 'ubah' : 'tambah' ?>">
            <?php if ($edit): ?>
                <input type="hidden" name="nim_lama" value="<?= aman($edit->getNim()) ?>">
            <?php endif; ?>

            <div class="form-group">
                <label>NIM:</label>
                <input type="text" name="nim" value="<?= aman($edit?->getNim() ?? '') ?>" <?= $edit ? 'readonly style="background:#e9ecef;"' : 'required' ?>>
            </div>
            <div class="form-group">
                <label>Nama:</label>
                <input type="text" name="nama" value="<?= aman($edit?->getNama() ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Prodi:</label>
                <input type="text" name="prodi" value="<?= aman($edit?->getProdi() ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Semester (1-14):</label>
                <input type="number" name="semester" value="<?= aman((string)($edit?->getSemester() ?? '')) ?>" required>
            </div>
            <div class="form-group">
                <label>Jenis Mahasiswa:</label>
                <select name="jenis" id="jenis" onchange="sesuaikanForm()" required>
                    <option value="reguler" <?= ($edit && $edit->getJenis() === 'Reguler') ? 'selected' : '' ?>>Reguler</option>
                    <option value="beasiswa" <?= ($edit && $edit->getJenis() === 'Beasiswa') ? 'selected' : '' ?>>Beasiswa</option>
                </select>
            </div>
            <div class="form-group">
                <label>UKT (Rp):</label>
                <input type="number" name="ukt" value="<?= aman($valUkt) ?>" required>
            </div>
            <div class="form-group" id="box-potongan" style="display:none;">
                <label>Potongan (%):</label>
                <input type="number" name="potongan" value="<?= aman($valPotongan) ?>" min="0" max="100">
            </div>

            <div style="margin-top: 10px;">
                <button type="submit"><?= $edit ? 'Simpan' : 'Tambah' ?></button>
                <?php if ($edit): ?>
                    <a href="index.php" style="margin-left: 10px;">Batal</a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <h3 style="margin-bottom: 5px;">Daftar Mahasiswa</h3>
    <table>
        <thead>
            <tr>
                <th>NIM</th>
                <th>Nama</th>
                <th>Prodi</th>
                <th>Semester</th>
                <th>Jenis</th>
                <th>Biaya Akhir (Rp)</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php $daftar = $repo->semua(); ?>
            <?php if (empty($daftar)): ?>
                <tr><td colspan="7" style="text-align:center;">Data kosong.</td></tr>
            <?php else: ?>
                <?php foreach ($daftar as $m): ?>
                    <tr>
                        <td><?= aman($m->getNim()) ?></td>
                        <td><?= aman($m->getNama()) ?></td>
                        <td><?= aman($m->getProdi()) ?></td>
                        <td><?= aman((string)$m->getSemester()) ?></td>
                        <td><b><?= aman($m->getJenis()) ?></b></td>
                        <td>Rp <?= aman(number_format($m->hitungBiaya(), 0, ',', '.')) ?></td>
                        <td>
                            <a href="index.php?action=edit&nim=<?= urlencode($m->getNim()) ?>">Edit</a> | 
                            <a href="index.php?action=hapus&nim=<?= urlencode($m->getNim()) ?>" onclick="return confirm('Hapus data?')">Hapus</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>

</body>
</html>
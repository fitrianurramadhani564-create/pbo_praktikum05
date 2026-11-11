<?php
require_once 'DataMahasiswa.php';

$repo = new DataMahasiswa();
$error = '';
$edit = null;

function aman(string $data): string {
    return htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
}

if (isset($_GET['action']) && $_GET['action'] === 'hapus' && isset($_GET['nim'])) {
    $repo->hapus($_GET['nim']);
    header('Location: index.php');
    exit;
}

if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['nim'])) {
    $edit = $repo->cari($_GET['nim']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $isEdit = isset($_POST['is_edit']) && $_POST['is_edit'] === '1';

    try {
        $m = new Mahasiswa(
            (string)($_POST['nim'] ?? ''),
            (string)($_POST['nama'] ?? ''),
            (string)($_POST['fakultas'] ?? ''),
            (string)($_POST['prodi'] ?? ''),
            (string)($_POST['asal_sekolah'] ?? ''),
            (int)($_POST['semester'] ?? 0),
            (string)($_POST['hobi'] ?? ''),
            (string)($_POST['agama'] ?? '')
        );

        if ($isEdit) {
            $repo->ubah($m);
        } else {
            $repo->tambah($m);
        }

        header('Location: index.php');
        exit;
    } catch (InvalidArgumentException $e) {
        $error = $e->getMessage();
    }
}

$daftarMhs = $repo->getAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>CRUD Mahasiswa - Enkapsulasi</title>
    <style>
        body { font-family: sans-serif; margin: 20px; }
        .error { background: #f8d7da; color: #842029; padding: 10px; border-radius: 4px; margin-bottom: 15px; }
        .form-box { background: #f9f9f9; padding: 15px; border: 1px solid #ddd; width: 400px; margin-bottom: 25px; border-radius: 5px; }
        .form-group { margin-bottom: 10px; }
        .form-group label { display: inline-block; width: 130px; font-weight: bold; }
        .form-group input, .form-group select { width: 230px; padding: 5px; }
        table { width: 100%; border-collapse: collapse; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #eee; }
    </style>
</head>
<body>

    <h2>Aplikasi CRUD Mahasiswa (Pertemuan 05)</h2>

    <?php if ($error !== ''): ?>
        <div class="error"><?= aman($error) ?></div>
    <?php endif; ?>

    <div class="form-box">
        <h3><?= $edit ? 'Edit Mahasiswa' : 'Tambah Mahasiswa' ?></h3>
        <form action="index.php" method="POST">
            <?php if ($edit): ?>
                <input type="hidden" name="is_edit" value="1">
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
                <label>Fakultas:</label>
                <input type="text" name="fakultas" value="<?= aman($edit?->getFakultas() ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Prodi:</label>
                <input type="text" name="prodi" value="<?= aman($edit?->getProdi() ?? '') ?>" required>
            </div>
            <div class="form-group">
                <label>Asal Sekolah:</label>
                <input type="text" name="asal_sekolah" value="<?= aman($edit?->getAsalSekolah() ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Semester (1-14):</label>
                <input type="number" name="semester" value="<?= aman((string)($edit?->getSemester() ?? '')) ?>" required>
            </div>
            <div class="form-group">
                <label>Hobi:</label>
                <input type="text" name="hobi" value="<?= aman($edit?->getHobi() ?? '') ?>">
            </div>
            <div class="form-group">
                <label>Agama:</label>
                <select name="agama" required>
                    <option value="">-- Pilih Agama --</option>
                    <?php
                    $listAgama = ['Islam', 'Kristen', 'Katolik', 'Hindu', 'Buddha', 'Khonghucu'];
                    foreach ($listAgama as $ag):
                        $selected = ($edit?->getAgama() === $ag) ? 'selected' : '';
                    ?>
                        <option value="<?= $ag ?>" <?= $selected ?>><?= $ag ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit"><?= $edit ? 'Simpan' : 'Tambah' ?></button>
            <?php if ($edit): ?>
                <a href="index.php">Batal</a>
            <?php endif; ?>
        </form>
    </div>

    <h3>Daftar Mahasiswa</h3>
    <table>
        <thead>
            <tr>
                <th>NIM</th>
                <th>Nama</th>
                <th>Fakultas</th>
                <th>Prodi</th>
                <th>Asal Sekolah</th>
                <th>Semester</th>
                <th>Hobi</th>
                <th>Agama</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($daftarMhs)): ?>
                <tr><td colspan="9" style="text-align:center;">Data kosong.</td></tr>
            <?php else: ?>
                <?php foreach ($daftarMhs as $m): ?>
                    <tr>
                        <td><?= aman($m->getNim()) ?></td>
                        <td><?= aman($m->getNama()) ?></td>
                        <td><?= aman($m->getFakultas()) ?></td>
                        <td><?= aman($m->getProdi()) ?></td>
                        <td><?= aman($m->getAsalSekolah()) ?></td>
                        <td><?= aman((string)$m->getSemester()) ?></td>
                        <td><?= aman($m->getHobi()) ?></td>
                        <td><?= aman($m->getAgama()) ?></td>
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
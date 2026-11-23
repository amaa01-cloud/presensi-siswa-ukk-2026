<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$siswa      = new Siswa($db);
$kelasModel = new Kelas($db);
$listKelas  = $kelasModel->getAll();

$errors = [];
$mode   = 'baru';
$old    = ['nisn' => '', 'nama' => '', 'alamat' => '', 'id_kelas' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'hapus') {
        $siswa->delete(trim($_POST['nisn'] ?? ''));
        flash('success', 'Siswa berhasil dihapus.');
        redirect('siswa.php');
    }

    if ($aksi === 'simpan') {
        $mode = ($_POST['mode'] ?? '') === 'edit' ? 'edit' : 'baru';
        $old = [
            'nisn'     => trim($_POST['nisn'] ?? ''),
            'nama'     => trim($_POST['nama'] ?? ''),
            'alamat'   => trim($_POST['alamat'] ?? ''),
            'id_kelas' => trim($_POST['id_kelas'] ?? ''),
        ];
        $siswa->setNisn($old['nisn']);
        $siswa->setNama($old['nama']);
        $siswa->setAlamat($old['alamat']);
        $siswa->setIdKelas((int)$old['id_kelas']);

        $errors = $siswa->validate($mode === 'baru');
        if (!$errors) {
            try {
                if ($mode === 'baru') {
                    $siswa->create();
                    flash('success', 'Siswa berhasil ditambahkan.');
                } else {
                    $siswa->update();
                    flash('success', 'Data siswa berhasil diubah.');
                }
                redirect('siswa.php');
            } catch (PDOException $ex) {
                if ($ex->getCode() === '23000') {
                    $errors['nisn'] = 'NISN sudah terdaftar.';
                } else {
                    throw $ex;
                }
            }
        }
    }
} elseif (isset($_GET['edit'])) {
    $row = $siswa->find(trim($_GET['edit']));
    if ($row) {
        $mode = 'edit';
        $old = ['nisn' => $row['nisn'], 'nama' => $row['nama'], 'alamat' => (string)$row['alamat'], 'id_kelas' => $row['id_kelas']];
    }
}

// Pencarian siswa (nama / NISN)
$q = trim($_GET['q'] ?? '');
$daftar = $siswa->getAll($q);

$pageTitle = 'Kelola Siswa';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<h1>Kelola Siswa</h1>

<div class="card">
    <h2><?= $mode === 'edit' ? 'Ubah Siswa' : 'Tambah Siswa' ?></h2>
    <?php if (!$listKelas): ?>
        <div class="alert alert-error">Belum ada kelas. Tambahkan kelas terlebih dahulu di menu Kelas.</div>
    <?php endif; ?>
    <form method="post" novalidate>
        <input type="hidden" name="aksi" value="simpan">
        <input type="hidden" name="mode" value="<?= e($mode) ?>">
        <div class="grid-2">
            <div class="field <?= isset($errors['nisn']) ? 'has-error' : '' ?>">
                <label for="nisn">NISN (10 digit)</label>
                <input type="text" id="nisn" name="nisn" inputmode="numeric" maxlength="10"
                       value="<?= e($old['nisn']) ?>" <?= $mode === 'edit' ? 'readonly' : '' ?>>
                <?php if (isset($errors['nisn'])): ?><small class="error"><?= e($errors['nisn']) ?></small><?php endif; ?>
            </div>
            <div class="field <?= isset($errors['nama']) ? 'has-error' : '' ?>">
                <label for="nama">Nama</label>
                <input type="text" id="nama" name="nama" value="<?= e($old['nama']) ?>">
                <?php if (isset($errors['nama'])): ?><small class="error"><?= e($errors['nama']) ?></small><?php endif; ?>
            </div>
            <div class="field <?= isset($errors['id_kelas']) ? 'has-error' : '' ?>">
                <label for="id_kelas">Kelas</label>
                <select id="id_kelas" name="id_kelas">
                    <option value="">-- Pilih kelas --</option>
                    <?php foreach ($listKelas as $k): ?>
                        <option value="<?= e($k['id_kelas']) ?>" <?= (string)$old['id_kelas'] === (string)$k['id_kelas'] ? 'selected' : '' ?>>
                            <?= e($k['nama_kelas']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['id_kelas'])): ?><small class="error"><?= e($errors['id_kelas']) ?></small><?php endif; ?>
            </div>
            <div class="field <?= isset($errors['alamat']) ? 'has-error' : '' ?>">
                <label for="alamat">Alamat (opsional)</label>
                <input type="text" id="alamat" name="alamat" value="<?= e($old['alamat']) ?>">
                <?php if (isset($errors['alamat'])): ?><small class="error"><?= e($errors['alamat']) ?></small><?php endif; ?>
            </div>
        </div>
        <button class="btn btn-primary" type="submit"><?= $mode === 'edit' ? 'Simpan Perubahan' : 'Tambah' ?></button>
        <?php if ($mode === 'edit'): ?><a class="btn" href="siswa.php">Batal</a><?php endif; ?>
    </form>
</div>

<div class="card">
    <h2>Daftar Siswa</h2>
    <form method="get" class="inline-form">
        <input type="text" name="q" placeholder="Cari nama atau NISN..." value="<?= e($q) ?>">
        <button class="btn btn-primary" type="submit">Cari</button>
        <?php if ($q !== ''): ?><a class="btn" href="siswa.php">Reset</a><?php endif; ?>
    </form>
    <div class="table-wrap">
        <table>
            <thead><tr><th>No</th><th>NISN</th><th>Nama</th><th>Kelas</th><th>Alamat</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if (!$daftar): ?>
                <tr><td colspan="6" class="center muted">Data siswa tidak ditemukan.</td></tr>
            <?php endif; ?>
            <?php foreach ($daftar as $i => $s): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($s['nisn']) ?></td>
                    <td><?= e($s['nama']) ?></td>
                    <td><?= e($s['nama_kelas']) ?></td>
                    <td><?= e($s['alamat']) ?></td>
                    <td class="actions">
                        <a class="btn btn-sm" href="siswa.php?edit=<?= e($s['nisn']) ?>">Ubah</a>
                        <form method="post" onsubmit="return confirm('Hapus siswa ini beserta log presensinya?')">
                            <input type="hidden" name="aksi" value="hapus">
                            <input type="hidden" name="nisn" value="<?= e($s['nisn']) ?>">
                            <button class="btn btn-sm btn-danger" type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>

<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$kelas  = new Kelas($db);
$errors = [];
$old    = ['id' => '', 'nama_kelas' => '', 'jurusan' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $aksi = $_POST['aksi'] ?? '';

    if ($aksi === 'hapus') {
        $id = (int)($_POST['id'] ?? 0);
        $jumlah = $kelas->countSiswa($id);
        if ($jumlah > 0) {
            flash('error', "Kelas tidak dapat dihapus karena masih memiliki $jumlah siswa. Pindahkan atau hapus siswanya terlebih dahulu.");
        } else {
            try {
                $kelas->delete($id);
                flash('success', 'Kelas berhasil dihapus.');
            } catch (PDOException $ex) {
                flash('error', 'Kelas tidak dapat dihapus karena masih digunakan oleh data lain.');
            }
        }
        redirect('kelas.php');
    }

    if ($aksi === 'simpan') {
        $old = [
            'id'         => trim($_POST['id'] ?? ''),
            'nama_kelas' => trim($_POST['nama_kelas'] ?? ''),
            'jurusan'    => trim($_POST['jurusan'] ?? ''),
        ];
        $isEdit = $old['id'] !== '';
        $kelas->setId($isEdit ? (int)$old['id'] : null);
        $kelas->setNamaKelas($old['nama_kelas']);
        $kelas->setJurusan($old['jurusan']);

        $errors = $kelas->validate();
        if (!$errors) {
            $isEdit ? $kelas->update() : $kelas->create();
            flash('success', $isEdit ? 'Kelas berhasil diubah.' : 'Kelas berhasil ditambahkan.');
            redirect('kelas.php');
        }
    }
} elseif (isset($_GET['edit'])) {
    $row = $kelas->find((int)$_GET['edit']);
    if ($row) {
        $old = ['id' => $row['id_kelas'], 'nama_kelas' => $row['nama_kelas'], 'jurusan' => (string)$row['jurusan']];
    }
}

$daftar = $kelas->getAll();
$isEdit = $old['id'] !== '';

$pageTitle = 'Kelola Kelas';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<h1>Kelola Kelas</h1>

<div class="card">
    <h2><?= $isEdit ? 'Ubah Kelas' : 'Tambah Kelas' ?></h2>
    <form method="post" novalidate>
        <input type="hidden" name="aksi" value="simpan">
        <input type="hidden" name="id" value="<?= e($old['id']) ?>">
        <div class="grid-2">
            <div class="field <?= isset($errors['nama_kelas']) ? 'has-error' : '' ?>">
                <label for="nama_kelas">Nama Kelas</label>
                <input type="text" id="nama_kelas" name="nama_kelas" value="<?= e($old['nama_kelas']) ?>">
                <?php if (isset($errors['nama_kelas'])): ?><small class="error"><?= e($errors['nama_kelas']) ?></small><?php endif; ?>
            </div>
            <div class="field">
                <label for="jurusan">Jurusan</label>
                <input type="text" id="jurusan" name="jurusan" value="<?= e($old['jurusan']) ?>">
            </div>
        </div>
        <button class="btn btn-primary" type="submit"><?= $isEdit ? 'Simpan Perubahan' : 'Tambah' ?></button>
        <?php if ($isEdit): ?><a class="btn" href="kelas.php">Batal</a><?php endif; ?>
    </form>
</div>

<div class="card">
    <h2>Daftar Kelas</h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>No</th><th>Nama Kelas</th><th>Jurusan</th><th>Jumlah Siswa</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if (!$daftar): ?>
                <tr><td colspan="5" class="center muted">Belum ada data kelas.</td></tr>
            <?php endif; ?>
            <?php foreach ($daftar as $i => $k): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($k['nama_kelas']) ?></td>
                    <td><?= e($k['jurusan']) ?></td>
                    <td><?= e($k['jumlah_siswa']) ?></td>
                    <td class="actions">
                        <a class="btn btn-sm" href="kelas.php?edit=<?= e($k['id_kelas']) ?>">Ubah</a>
                        <form method="post" onsubmit="return confirm('Hapus kelas ini?')">
                            <input type="hidden" name="aksi" value="hapus">
                            <input type="hidden" name="id" value="<?= e($k['id_kelas']) ?>">
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

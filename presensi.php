<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$presensi   = new Presensi($db);
$kelasModel = new Kelas($db);

// Hapus log presensi salah catat (log tidak dapat diubah)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['aksi'] ?? '') === 'hapus') {
    $presensi->delete((int)($_POST['id'] ?? 0));
    flash('success', 'Log presensi berhasil dihapus.');
    $qs = $_SERVER['QUERY_STRING'] ?? '';
    redirect('presensi.php' . ($qs !== '' ? '?' . $qs : ''));
}

// Default: presensi hari ini. Jika form filter dikirim dengan tanggal kosong -> semua tanggal.
$tanggal  = isset($_GET['tanggal']) ? trim($_GET['tanggal']) : date('Y-m-d');
if ($tanggal !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $tanggal)) {
    $tanggal = date('Y-m-d');
}
$idKelas  = (int)($_GET['id_kelas'] ?? 0);
$q        = trim($_GET['q'] ?? '');

$daftar    = $presensi->getAll(['tanggal' => $tanggal, 'id_kelas' => $idKelas, 'q' => $q]);
$listKelas = $kelasModel->getAll();

$pageTitle = 'Rekap Presensi';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<h1>Rekap Presensi</h1>

<div class="card">
    <form method="get" class="filter-form">
        <div class="field">
            <label for="q">Cari nama / NISN</label>
            <input type="text" id="q" name="q" value="<?= e($q) ?>" placeholder="Nama atau NISN">
        </div>
        <div class="field">
            <label for="tanggal">Tanggal</label>
            <input type="date" id="tanggal" name="tanggal" value="<?= e($tanggal) ?>">
        </div>
        <div class="field">
            <label for="id_kelas">Kelas</label>
            <select id="id_kelas" name="id_kelas">
                <option value="0">Semua kelas</option>
                <?php foreach ($listKelas as $k): ?>
                    <option value="<?= e($k['id_kelas']) ?>" <?= $idKelas === (int)$k['id_kelas'] ? 'selected' : '' ?>><?= e($k['nama_kelas']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="field filter-actions">
            <button class="btn btn-primary" type="submit">Terapkan</button>
            <a class="btn" href="presensi.php">Reset (hari ini)</a>
        </div>
    </form>
</div>

<div class="card">
    <h2>
        <?= $tanggal !== '' ? 'Presensi tanggal ' . e(date('d-m-Y', strtotime($tanggal))) : 'Semua presensi' ?>
        <span class="badge"><?= count($daftar) ?> data</span>
    </h2>
    <div class="table-wrap">
        <table>
            <thead><tr><th>No</th><th>NISN</th><th>Nama Siswa</th><th>Kelas</th><th>Tanggal &amp; Waktu</th><th>Aksi</th></tr></thead>
            <tbody>
            <?php if (!$daftar): ?>
                <tr><td colspan="6" class="center muted">Belum ada data presensi.</td></tr>
            <?php endif; ?>
            <?php foreach ($daftar as $i => $p): ?>
                <tr>
                    <td><?= $i + 1 ?></td>
                    <td><?= e($p['nisn']) ?></td>
                    <td><?= e($p['nama']) ?></td>
                    <td><?= e($p['nama_kelas']) ?></td>
                    <td><?= e(format_waktu($p['tanggal_waktu'])) ?></td>
                    <td class="actions">
                        <form method="post" onsubmit="return confirm('Hapus log presensi ini?')">
                            <input type="hidden" name="aksi" value="hapus">
                            <input type="hidden" name="id" value="<?= e($p['id']) ?>">
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

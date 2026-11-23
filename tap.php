<?php
require_once __DIR__ . '/includes/init.php';
require_login();

$errors = [];
$hasil  = null;
$nisn   = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nisn = trim($_POST['nisn'] ?? '');
    $presensi = new Presensi($db);
    $presensi->setNisn($nisn);

    $errors = $presensi->validate();
    if (!$errors) {
        $hasil = $presensi->tap();
        $nisn = ''; // siap untuk tap berikutnya
    }
}

$pageTitle = 'Simulasi Tap Kartu';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<h1>Simulasi Tap Kartu</h1>
<div class="card card-narrow">
    <p class="muted">Form ini mewakili mesin RFID. Hanya NISN yang dikirim; <code>id</code> dan <code>tanggal_waktu</code> diisi otomatis oleh database.</p>

    <?php if ($hasil): ?>
        <div class="alert alert-success">
            Presensi berhasil dicatat:<br>
            <strong><?= e($hasil['nama']) ?></strong> (<?= e($hasil['nama_kelas']) ?>) &middot; <?= e(format_waktu($hasil['tanggal_waktu'])) ?>
        </div>
    <?php endif; ?>

    <form method="post" novalidate>
        <div class="field <?= isset($errors['nisn']) ? 'has-error' : '' ?>">
            <label for="nisn">NISN</label>
            <input type="text" id="nisn" name="nisn" inputmode="numeric" maxlength="10" value="<?= e($nisn) ?>" autofocus>
            <?php if (isset($errors['nisn'])): ?><small class="error"><?= e($errors['nisn']) ?></small><?php endif; ?>
        </div>
        <button class="btn btn-primary btn-block" type="submit">Tap Kartu</button>
    </form>
    <p class="center"><a href="presensi.php">Lihat rekap presensi hari ini &rarr;</a></p>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>

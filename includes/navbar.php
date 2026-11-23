<?php $current = basename($_SERVER['SCRIPT_NAME']); ?>
<nav class="navbar">
    <div class="nav-inner">
        <a class="brand" href="index.php">&#128203; Presensi Siswa</a>
        <?php if (is_logged_in()): ?>
            <button class="nav-toggle" type="button" onclick="document.getElementById('menu').classList.toggle('open')" aria-label="Menu">&#9776;</button>
            <ul class="menu" id="menu">
                <li><a class="<?= $current === 'presensi.php' ? 'active' : '' ?>" href="presensi.php">Rekap Presensi</a></li>
                <li><a class="<?= $current === 'tap.php' ? 'active' : '' ?>" href="tap.php">Tap Kartu</a></li>
                <li><a class="<?= $current === 'siswa.php' ? 'active' : '' ?>" href="siswa.php">Siswa</a></li>
                <li><a class="<?= $current === 'kelas.php' ? 'active' : '' ?>" href="kelas.php">Kelas</a></li>
                <li><a href="logout.php">Logout (<?= e($_SESSION['username'] ?? '') ?>)</a></li>
            </ul>
        <?php endif; ?>
    </div>
</nav>
<main class="container">
<?php
if (!empty($_SESSION['flash'])) {
    $f = $_SESSION['flash'];
    unset($_SESSION['flash']);
    echo '<div class="alert alert-' . e($f['type']) . '">' . e($f['msg']) . '</div>';
}

<?php
/**
 * Seeder: akun admin, data kelas awal, dan beberapa siswa contoh.
 * Jalankan SEKALI setelah import schema.sql:
 *   php seeder.php        (CLI)   atau buka http://localhost/presensi-siswa/seeder.php
 * Aman dijalankan ulang (tidak membuat data ganda).
 */
require_once __DIR__ . '/includes/init.php';
header('Content-Type: text/plain; charset=utf-8');

// 1. Admin
$user = new User($db);
if (!$user->findByUsername('admin')) {
    $u = new User($db);
    $u->setUsername('admin');
    $u->setPassword('admin123');
    $u->create();
    echo "[OK] Admin dibuat  -> username: admin | password: admin123\n";
} else {
    echo "[SKIP] Admin sudah ada\n";
}

// 2. Kelas awal
$dataKelas = [
    ['X PPLG 1', 'PPLG'], ['X PPLG 2', 'PPLG'],
    ['XI PPLG 1', 'PPLG'], ['XI PPLG 2', 'PPLG'],
    ['XII PPLG 1', 'PPLG'], ['XII PPLG 2', 'PPLG'],
];
foreach ($dataKelas as [$nama, $jurusah]) {
    $k = new Kelas($db);
    $k->setNamaKelas($nama);
    $k->setJurusan($jurusah);
    if ($k->namaExists()){ echo "[SKIP] Kelas $nama\n"; continue; }
    $k->create();
    echo "[OK] Kelas $nama\n";
}

// 3. Siswa contoh (opsional, untuk memudahkan pengujian)
$idKelas = (int)$db->query("SELECT id_kelas FROM kelas WHERE nama_kelas = 'XI PPLG 1'")->fetchColumn();
$contoh = [
    ['0012345678', 'Andi Pratama', 'Jl. Pahlawan No. 1, Samarinda'],
    ['0098765432', 'Siti Aminah',  'Jl. Juanda No. 12, Samarinda'],
    ['0045678901', 'Budi Santoso', null],
];
foreach ($contoh as [$nisn, $nama, $alamat]) {
    $s = new Siswa($db);
    $s->setNisn($nisn);
    $s->setNama($nama);
    $s->setAlamat($alamat);
    $s->setIdKelas($idKelas);
    if ($s->exists($nisn)) { echo "[SKIP] Siswa $nisn\n"; continue; }
    $s->create();
    echo "[OK] Siswa $nisn - $nama\n";
}
echo "\nSelesai. Hapus / amankan seeder.php setelah dipakai.\n";

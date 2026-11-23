<?php
session_start();

require_once __DIR__ . '/../config.php';
foreach (['Database', 'BaseModel', 'User', 'Kelas', 'Siswa', 'Presensi'] as $c) {
    require_once __DIR__ . "/../classes/$c.php";
}

/** Amankan output dari database / input pengguna. */
function e($v): string
{
    return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8');
}

function redirect(string $url): void
{
    header("Location: $url");
    exit;
}

function flash(string $type, string $msg): void
{
    $_SESSION['flash'] = ['type' => $type, 'msg' => $msg];
}

function is_logged_in(): bool
{
    return !empty($_SESSION['user_id']);
}

/** Semua halaman selain login wajib diproteksi. */
function require_login(): void
{
    if (!is_logged_in()) {
        redirect('login.php');
    }
}

function format_waktu(string $dt): string
{
    return date('d-m-Y H:i:s', strtotime($dt));
}

try {
    $db = (new Database())->getConnection();
} catch (PDOException $ex) {
    http_response_code(500);
    die('Koneksi database gagal. Periksa config.php dan pastikan schema.sql sudah diimpor.');
}

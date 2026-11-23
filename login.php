<?php
require_once __DIR__ . '/includes/init.php';

if (is_logged_in()) {
    redirect('presensi.php');
}

$error = '';
$username = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    $user = new User($db);
    // Pesan sama untuk username/password salah (tidak menyebutkan bagian mana yang salah)
    if ($username !== '' && $password !== '' && $user->findByUsername($username) && $user->verifyPassword($password)) {
        session_regenerate_id(true);
        $_SESSION['user_id']  = $user->getId();
        $_SESSION['username'] = $user->getUsername();
        redirect('presensi.php');
    }
    $error = 'Username atau password salah.';
}

$pageTitle = 'Login';
include __DIR__ . '/includes/header.php';
include __DIR__ . '/includes/navbar.php';
?>
<div class="card card-narrow">
    <h1>Login Administrator</h1>
    <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
    <form method="post" novalidate>
        <div class="field">
            <label for="username">Username</label>
            <input type="text" id="username" name="username" value="<?= e($username) ?>" autofocus>
        </div>
        <div class="field">
            <label for="password">Password</label>
            <input type="password" id="password" name="password">
        </div>
        <button class="btn btn-primary btn-block" type="submit">Masuk</button>
    </form>
</div>
<?php include __DIR__ . '/includes/footer.php'; ?>

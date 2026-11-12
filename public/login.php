<?php
/**
 * Login Admin & Staff - PawHouse
 */
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth.php';

// Jika sudah login, langsung ke dashboard admin
if (is_logged_in()) {
    redirect(base_url('admin/index.php'));
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    validate_csrf();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Silakan isi username dan password Anda.';
    } else {
        $pdo = db();
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && $password === $user['password']) {
            login_user($user);
            redirect(base_url('admin/index.php'), 'Selamat datang kembali, ' . $user['name'] . '!', 'success');
        } else {
            $error = 'Username atau password yang Anda masukkan salah.';
        }
    }
}

$pageTitle = 'Login Pengelola - PawHouse';
$activeNav = 'login';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="section" style="min-height: 70vh; display: flex; align-items: center;">
    <div class="container" style="max-width: 460px;">
        <div class="form-card">
            <div style="text-align: center; margin-bottom: 2rem;">
                <div style="width: 56px; height: 56px; background: var(--color-primary-soft); color: var(--color-primary); border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: 1.8rem; margin-bottom: 0.75rem;">
                    🔐
                </div>
                <h2 style="color: var(--color-primary); font-size: 1.6rem; font-weight: 800;">Login Petugas</h2>
                <p style="color: var(--color-text-muted); font-size: 0.9rem; margin-top: 0.35rem;">
                    Portal khusus staf dan administrator PawHouse
                </p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
                    <span><?= e($error) ?></span>
                </div>
            <?php endif; ?>

            <form action="<?= base_url('public/login.php') ?>" method="POST">
                <?= csrf_field() ?>

                <div class="form-group">
                    <label for="username" class="form-label">Username <span class="req">*</span></label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="Masukkan username" required autofocus value="<?= e($_POST['username'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label for="password" class="form-label">Password <span class="req">*</span></label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••" required>
                </div>

                <div style="margin-top: 1.75rem;">
                    <button type="submit" class="btn btn-primary btn-block btn-lg">
                        Masuk ke Dashboard
                    </button>
                </div>

                <div style="text-align: center; margin-top: 1.5rem; font-size: 0.85rem; color: var(--color-text-muted);">
                    <a href="<?= base_url('public/index.php') ?>">← Kembali ke Beranda Public</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>



<?php

require_once 'config/db.php';
require_once 'config/activity.php';
require_once 'config/auth.php';
require_once 'config/csrf.php';

if (logged_in()) {
    header('Location: ' . dashboard_for(user()['role']));
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $st = $pdo->prepare("
        SELECT *
        FROM Users
        WHERE email = ?
          AND role = 'admin'
          AND status = 'active'
        LIMIT 1
    ");

    $st->execute([$email]);
    $u = $st->fetch();

    if ($u && password_verify($password, $u['password'])) {
        session_regenerate_id(true);

        unset($u['password']);
        $_SESSION['user'] = $u;
        log_activity($pdo,(int)$u['id'],'Administrator login','Successful sign-in');

        header('Location: ' . dashboard_for($u['role']));
        exit;
    }

    $error = 'Invalid email or password.';
}

$title = 'Admin Login';
include 'partials/header.php';
?>

<style>

/* Background image */
body {
    background:
        linear-gradient(
            rgba(35, 24, 17, 0.45),
            rgba(35, 24, 17, 0.55)
        ),
      url("<?= e(asset_url('images/login-bg.png')) ?>");

    background-size: cover;
    background-position: center;
    background-repeat: no-repeat;
    background-attachment: fixed;
}

/* Login page positioning wrapper */
.login-page-layout {
    width: 100%;
    min-height: calc(100vh - 100px);
    display: flex;
    justify-content: center;
    align-items: flex-end;
    box-sizing: border-box;
    padding: 30px 15px 35px;
}

/* Login card */
.login-page-layout .form-card {
    width: 90%;
    max-width: 380px;
    margin: 0 auto;
    padding: 16px 24px;
    background: rgba(255, 255, 255, 0.52);
    border-radius: 16px;
    box-shadow: 0 15px 45px rgba(0, 0, 0, 0.30);
    box-sizing: border-box;
}

/* Card heading */
.login-page-layout .form-card h1 {
    font-size: 26px;
    line-height: 1.15;
    margin-top: 4px;
    margin-bottom: 8px;
}

/* Form spacing */
.login-page-layout .field {
    margin-bottom: 10px;
}

.login-page-layout .form-card p.muted {
    font-size: 12px;
    line-height: 1.4;
    margin-top: 10px;
    margin-bottom: 5px;
}

/* Password toggle positioning */
.login-page-layout .password-wrapper {
    position: relative;
}

/* Mobile layout */
@media (max-width: 600px) {
    body {
        background-attachment: scroll;
    }

    .login-page-layout {
        min-height: calc(100vh - 80px);
        padding: 20px 12px 25px;
    }

    .login-page-layout .form-card {
        width: 100%;
        max-width: 360px;
        padding: 18px 20px;
    }

    .login-page-layout .form-card h1 {
        font-size: 23px;
    }
}
</style>

<script>
sessionStorage.removeItem('bayangan_logged_out');
</script>

<div class="login-page-layout">

    <div class="form-card">

        <div class="eyebrow">Secure Access</div>

        <h1>Administrator Login</h1>

        <?php if ($error): ?>
            <div class="alert"><?= e($error) ?></div>
        <?php endif; ?>

        <form method="post">
            <?= csrf_field() ?>

            <div class="field">
                <label for="email">Email</label>

                <input
                    class="input"
                    id="email"
                    name="email"
                    type="email"
                    autocomplete="email"
                    required
                >
            </div>

            <div class="field password-field">
                <label for="password">Password</label>

                <div class="password-wrapper">
                    <input
                        class="input"
                        id="password"
                        name="password"
                        type="password"
                        autocomplete="current-password"
                        required
                    >

                    <button
                        type="button"
                        class="password-toggle"
                        id="togglePassword"
                        aria-label="Show password"
                        aria-pressed="false"
                        title="Show password"
                    >
                        <svg class="password-icon" viewBox="0 0 24 24" aria-hidden="true">
                            <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"></path>
                            <circle cx="12" cy="12" r="2.5"></circle>
                        </svg>
                    </button>
                </div>
            </div>

            <button class="btn login-submit" type="submit">
                Login
            </button>
        </form>
<p class="muted">
    Visitors can browse the Bayangan Archive without creating an account.
</p>

    </div>

</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const password = document.getElementById('password');
    const togglePassword = document.getElementById('togglePassword');

    if (!password || !togglePassword) {
        return;
    }

    togglePassword.addEventListener('click', function () {
        const isHidden = password.type === 'password';

        password.type = isHidden ? 'text' : 'password';

        this.setAttribute('aria-pressed', String(isHidden));
        this.setAttribute('aria-label', isHidden ? 'Hide password' : 'Show password');
        this.setAttribute('title', isHidden ? 'Hide password' : 'Show password');

        this.innerHTML = isHidden
            ? `<svg class="password-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M3 3l18 18"></path>
                <path d="M10.6 6.2A9.9 9.9 0 0 1 12 6c6 0 9.5 6 9.5 6a16.8 16.8 0 0 1-3.2 3.8"></path>
                <path d="M6.1 8.1A16.5 16.5 0 0 0 2.5 12s3.5 6 9.5 6a9.7 9.7 0 0 0 3.2-.5"></path>
                <path d="M9.9 9.9a3 3 0 0 0 4.2 4.2"></path>
            </svg>`
            : `<svg class="password-icon" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M2.5 12s3.5-6 9.5-6 9.5 6 9.5 6-3.5 6-9.5 6-9.5-6-9.5-6Z"></path>
                <circle cx="12" cy="12" r="2.5"></circle>
            </svg>`;

        if (isHidden) {
            password.focus();
        }
    });
});
</script>

<?php
include 'partials/footer.php';
?>

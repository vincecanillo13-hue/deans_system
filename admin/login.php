<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

$pageTitle = 'Staff Login';
$error = '';

if (!empty($_SESSION['admin_id'])) {
    header('Location: dashboard.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        $stmt = $pdo->prepare(
            'SELECT id, username, password_hash
             FROM admins
             WHERE username = ?
             LIMIT 1'
        );

        $stmt->execute([$username]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (
            $admin &&
            password_verify($password, $admin['password_hash'])
        ) {
            session_regenerate_id(true);

            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];

            header('Location: dashboard.php');
            exit;
        }

        $error = 'Invalid username or password.';
    } catch (PDOException $e) {
        error_log($e->getMessage());
        $error = 'Unable to log in right now. Please check the database setup.';
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<style>
.password-eye-wrap {
    position: relative;
    width: 100%;
}

.password-eye-wrap input {
    width: 100%;
    box-sizing: border-box;
    padding-right: 46px !important;
}

.password-eye-button {
    position: absolute !important;
    right: 8px !important;
    top: 50% !important;
    transform: translateY(-50%) !important;
    width: 32px !important;
    height: 32px !important;
    padding: 5px !important;
    margin: 0 !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    border: none !important;
    background: transparent !important;
    color: #475569 !important;
    cursor: pointer !important;
    z-index: 10 !important;
}

.password-eye-button svg {
    width: 21px;
    height: 21px;
    display: block;
    pointer-events: none;
}
</style>

<section class="card">
    <h2>Staff Login</h2>
    <p>Sign in to manage student requests.</p>

    <?php if ($error !== ''): ?>
        <div class="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= e(csrfToken()) ?>"
        >

        <label for="username">Username</label>
        <input
            type="text"
            id="username"
            name="username"
            maxlength="50"
            autocomplete="username"
            required
        >

        <label for="password">Password</label>

        <div class="password-eye-wrap">
            <input
                type="password"
                id="password"
                name="password"
                autocomplete="current-password"
                required
            >

            <button
                type="button"
                id="togglePassword"
                class="password-eye-button"
                aria-label="Show password"
                title="Show password"
            >
                <svg
                    id="eyeIcon"
                    xmlns="http://www.w3.org/2000/svg"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                    aria-hidden="true"
                >
                    <path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </button>
        </div>

        <button type="submit">Log In</button>
    </form>
</section>

<script>
(function () {
    const passwordField = document.getElementById('password');
    const toggleButton = document.getElementById('togglePassword');
    const eyeIcon = document.getElementById('eyeIcon');

    if (!passwordField || !toggleButton || !eyeIcon) return;

    toggleButton.addEventListener('click', function () {
        const showPassword = passwordField.type === 'password';

        passwordField.type = showPassword ? 'text' : 'password';

        if (showPassword) {
            eyeIcon.innerHTML =
                '<path d="M3 3l18 18"/>' +
                '<path d="M10.6 10.6a2 2 0 002.8 2.8"/>' +
                '<path d="M9.9 5.2A10.8 10.8 0 0112 5c6.4 0 10 7 10 7a15 15 0 01-3 3.8"/>' +
                '<path d="M6.2 6.2C3.5 8 2 12 2 12s3.6 7 10 7c1.2 0 2.3-.2 3.3-.6"/>';
        } else {
            eyeIcon.innerHTML =
                '<path d="M2 12s3.6-7 10-7 10 7 10 7-3.6 7-10 7S2 12 2 12z"/>' +
                '<circle cx="12" cy="12" r="3"/>';
        }

        const label = showPassword ? 'Hide password' : 'Show password';

        toggleButton.setAttribute('aria-label', label);
        toggleButton.title = label;
    });
})();
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
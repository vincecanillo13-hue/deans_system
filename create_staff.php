<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Create Staff Account';
$message = '';
$success = false;
$count = -1;

try {
    $count = (int) $pdo->query(
        'SELECT COUNT(*) FROM admins'
    )->fetchColumn();
} catch (PDOException $e) {
    error_log($e->getMessage());
    $message = 'Unable to connect to the staff accounts table. Please check your database.';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $count === 0) {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!preg_match('/^[a-zA-Z0-9._-]{4,50}$/', $username)) {
        $message = 'Username must be 4–50 characters using letters, numbers, dots, underscores, or hyphens.';
    } elseif (strlen($password) < 12) {
        $message = 'Password must contain at least 12 characters.';
    } else {
        try {
            // Check again to prevent creating duplicate staff accounts.
            $check = $pdo->query(
                'SELECT COUNT(*) FROM admins'
            );

            if ((int) $check->fetchColumn() > 0) {
                $message = 'A staff account already exists. No changes were made.';
                $count = 1;
            } else {
                // Securely hash the password.
                $hash = password_hash($password, PASSWORD_DEFAULT);

                // Use the actual column name in your database.
                $stmt = $pdo->prepare(
                    'INSERT INTO admins (username, password_hash)
                     VALUES (?, ?)'
                );

                $stmt->execute([$username, $hash]);

                $message = 'Staff account created successfully! Delete create_staff.php now, then log in.';
                $success = true;
                $count = 1;
            }
        } catch (PDOException $e) {
            error_log($e->getMessage());
            $message = 'Unable to create the account. Please check your database and PHP error log.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
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
    <h2>Create Staff Account</h2>
    <p>Create the first staff account for the Dean's Office Request Tracking System.</p>

    <?php if ($message !== ''): ?>
        <div class="alert"><?= e($message) ?></div>
    <?php endif; ?>

    <?php if ($count === 0 && !$success): ?>
        <form method="POST" action="">
            <div style="margin-bottom: 16px;">
                <label for="username">Username</label>
                <input
                    type="text"
                    id="username"
                    name="username"
                    minlength="4"
                    maxlength="50"
                    autocomplete="username"
                    placeholder="Enter staff username"
                    required
                    style="width: 100%; box-sizing: border-box; margin-top: 6px;"
                >
                <small>Use 4–50 letters, numbers, dots, underscores, or hyphens.</small>
            </div>

            <div style="margin-bottom: 16px;">
                <label for="password">Password</label>

                <div class="password-eye-wrap" style="margin-top: 6px;">
                    <input
                        type="password"
                        id="password"
                        name="password"
                        minlength="12"
                        autocomplete="new-password"
                        placeholder="Enter a strong password"
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

                <small>Use at least 12 characters.</small>
            </div>

            <button type="submit">Create Staff Account</button>
        </form>

    <?php elseif ($count > 0): ?>
        <p>A staff account already exists. Please go to the staff login page.</p>
        <p><a class="btn" href="admin/login.php">Go to Staff Login</a></p>
    <?php endif; ?>
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

<?php require_once __DIR__ . '/includes/footer.php'; ?>
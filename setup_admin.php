
<?php
require_once __DIR__ . '/config/database.php';

$message = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!preg_match('/^[a-zA-Z0-9._-]{4,50}$/', $username)) {
        $message = 'Username must be 4–50 characters using letters, numbers, dots, underscores, or hyphens.';
    } elseif (strlen($password) < 12) {
        $message = 'Password must contain at least 12 characters.';
    } else {
        try {
            $count = (int) $pdo->query(
                "SELECT COUNT(*) FROM admins"
            )->fetchColumn();

            if ($count > 0) {
                $message = 'A staff account already exists. No changes were made.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);

                $stmt = $pdo->prepare(
                    "INSERT INTO admins (username, password) VALUES (?, ?)"
                );
                $stmt->execute([$username, $hash]);

                $message = 'Staff account created successfully! Delete create_staff.php now, then log in.';
                $success = true;
            }
        } catch (PDOException $e) {
            $message = 'Database error. Please check your database connection.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Staff Account | Dean's Office</title>

    <link rel="stylesheet" href="assets/style.css">

    <style>
        body {
            margin: 0;
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            background: #f1f5f9;
            color: #1e293b;
        }

        .page-header {
            background: #173b65;
            color: white;
            padding: 22px 20px;
            text-align: center;
        }

        .page-header h1 {
            margin: 0 0 6px;
            font-size: 25px;
        }

        .page-header p {
            margin: 0;
            font-size: 14px;
            color: #dbeafe;
        }

        .main-content {
            width: calc(100% - 36px);
            max-width: 440px;
            margin: 55px auto;
        }

        .account-card {
            background: white;
            padding: 32px;
            border-radius: 10px;
            box-shadow: 0 4px 18px rgba(15, 23, 42, 0.09);
            border: 1px solid #e2e8f0;
        }

        .account-card h2 {
            margin: 0 0 8px;
            color: #173b65;
            font-size: 23px;
        }

        .description {
            color: #64748b;
            font-size: 14px;
            line-height: 1.6;
            margin: 0 0 25px;
        }

        .form-group {
            margin-bottom: 19px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            font-weight: bold;
            color: #334155;
        }

        .form-group input {
            display: block;
            width: 100%;
            box-sizing: border-box;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            background: white;
            color: #1e293b;
            font-size: 15px;
            outline: none;
        }

        .form-group input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.12);
        }

        .password-wrapper {
            position: relative;
        }

        .password-wrapper input {
            padding-right: 45px;
        }

        .password-toggle {
            position: absolute;
            right: 7px;
            top: 50%;
            transform: translateY(-50%);
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            border: none;
            border-radius: 5px;
            background: transparent;
            color: #64748b;
            cursor: pointer;
            font-size: 18px;
        }

        .password-toggle:hover {
            background: #f1f5f9;
        }

        .submit-button {
            display: block;
            width: 100%;
            padding: 13px;
            margin-top: 8px;
            border: none;
            border-radius: 6px;
            background: #173b65;
            color: white;
            font-size: 15px;
            font-weight: bold;
            cursor: pointer;
        }

        .submit-button:hover {
            background: #244f80;
        }

        .message {
            padding: 12px 14px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-size: 14px;
            line-height: 1.5;
            overflow-wrap: anywhere;
        }

        .message.error {
            background: #fef2f2;
            color: #b91c1c;
            border: 1px solid #fecaca;
        }

        .message.success {
            background: #f0fdf4;
            color: #166534;
            border: 1px solid #bbf7d0;
        }

        .security-note {
            margin: 18px 0 0;
            text-align: center;
            font-size: 12px;
            line-height: 1.5;
            color: #64748b;
        }

        .page-footer {
            text-align: center;
            padding: 18px;
            color: #64748b;
            font-size: 12px;
        }

        @media (max-width: 480px) {
            .main-content {
                margin: 28px auto;
            }

            .account-card {
                padding: 23px 19px;
            }

            .page-header h1 {
                font-size: 21px;
            }
        }
    </style>
</head>
<body>

    <header class="page-header">
        <h1>Dean's Office</h1>
        <p>Request Tracking System</p>
    </header>

    <main class="main-content">
        <section class="account-card">
            <h2>Create Staff Account</h2>
            <p class="description">
                Set up the administrator account used to manage and track student requests.
            </p>

            <?php if ($message !== ''): ?>
                <div class="message <?= $success ? 'success' : 'error' ?>">
                    <?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="form-group">
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
                    >
                </div>

                <div class="form-group">
                    <label for="password">Password</label>

                    <div class="password-wrapper">
                        <input
                            type="password"
                            id="password"
                            name="password"
                            minlength="12"
                            autocomplete="new-password"
                            placeholder="At least 12 characters"
                            required
                        >

                        <button
                            type="button"
                            class="password-toggle"
                            id="togglePassword"
                            aria-label="Show password"
                            title="Show password"
                        >
                            👁
                        </button>
                    </div>
                </div>

                <button type="submit" class="submit-button">
                    Create Staff Account
                </button>
            </form>

            <p class="security-note">
                Use a strong password with at least 12 characters.
            </p>
        </section>
    </main>

    <footer class="page-footer">
        Dean's Office Request Tracking System
    </footer>

    <script>
        const passwordField = document.getElementById('password');
        const toggleButton = document.getElementById('togglePassword');

        toggleButton.addEventListener('click', function () {
            const isHidden = passwordField.type === 'password';

            passwordField.type = isHidden ? 'text' : 'password';
            this.textContent = isHidden ? '🙈' : '👁';

            const label = isHidden ? 'Hide password' : 'Show password';
            this.setAttribute('aria-label', label);
            this.title = label;
        });
    </script>

</body>
</html>

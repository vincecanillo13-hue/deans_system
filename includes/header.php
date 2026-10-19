
<?php
require_once __DIR__ . '/functions.php';

$pageTitle = $pageTitle ?? "Dean's Office Request Tracking System";
$isStaffLoggedIn = !empty($_SESSION['admin_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>

    <link rel="stylesheet" href="/dean_request_tracking_system/assets/style.css">

    <style>
        .site-header .header-nav {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
            margin-left: auto;
        }

        .site-header .header-nav a,
        .site-header .header-nav button {
            display: inline-block;
            box-sizing: border-box;
            padding: 10px 18px;
            margin: 0;
            border: none;
            border-radius: 6px;
            background-color: #2563eb;
            color: #ffffff;
            font-family: inherit;
            font-size: 14px;
            font-weight: 600;
            line-height: 1.4;
            text-decoration: none;
            text-align: center;
            cursor: pointer;
            transition: background-color 0.2s ease;
        }

        .site-header .header-nav a:hover,
        .site-header .header-nav button:hover {
            background-color: #1d4ed8;
            color: #ffffff;
        }

        .site-header .header-nav form {
            display: inline-flex;
            margin: 0;
        }

        @media (max-width: 600px) {
            .site-header .header-nav {
                justify-content: flex-start;
                margin-left: 0;
            }
        }
    </style>
</head>
<body>

<header class="site-header">
    <div class="container" style="
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 20px;
        flex-wrap: wrap;
    ">
        <div style="flex: 1; min-width: 200px;">
            <h1 style="margin: 0;">Dean's Office</h1>
            <p style="margin: 5px 0 0;">Request Tracking System</p>
        </div>

        <nav class="header-nav">

            <?php if ($isStaffLoggedIn): ?>

                <a href="/dean_request_tracking_system/admin/dashboard.php">
                    Staff Dashboard
                </a>

                <form
                    method="POST"
                    action="/dean_request_tracking_system/admin/logout.php"
                >
                    <input
                        type="hidden"
                        name="csrf_token"
                        value="<?= e(csrfToken()) ?>"
                    >

                    <button type="submit">Logout</button>
                </form>

            <?php else: ?>

                <a href="/dean_request_tracking_system/">
                    Home
                </a>

                <a href="/dean_request_tracking_system/track.php">
                    Track Request
                </a>

                <a href="/dean_request_tracking_system/admin/login.php">
                    Staff Login
                </a>

            <?php endif; ?>

        </nav>
    </div>
</header>

<main class="container">

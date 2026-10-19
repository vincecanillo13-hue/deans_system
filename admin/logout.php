
<?php
require_once __DIR__ . '/../includes/functions.php';

// Logout must be submitted through the header form.
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: dashboard.php');
    exit;
}

// Check the security token before ending the session.
verifyCsrfToken();

// Clear the staff session.
$_SESSION = [];

// Expire the session cookie.
if (ini_get('session.use_cookies')) {
    $params = session_get_cookie_params();

    setcookie(
        session_name(),
        '',
        time() - 42000,
        $params['path'],
        $params['domain'],
        $params['secure'],
        $params['httponly']
    );
}

// Destroy the session.
session_destroy();

// Redirect to the public home page.
header('Location: /dean_request_tracking_system/');
exit;


<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$pageTitle = 'View Request';
$error = '';
$notice = '';

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

$allowedStatuses = [
    'Pending',
    'In Progress',
    'Completed',
    'Rejected'
];

// Handle status updates.
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $status = trim($_POST['status'] ?? '');

    if (!$id || $id < 1) {
        $error = 'Invalid request ID.';
    } elseif (!in_array($status, $allowedStatuses, true)) {
        $error = 'Please select a valid status.';
    } else {
        try {
            // Confirm the request exists before updating.
            $check = $pdo->prepare(
                'SELECT id FROM requests WHERE id = ? LIMIT 1'
            );
            $check->execute([$id]);

            if (!$check->fetch(PDO::FETCH_ASSOC)) {
                $error = 'Request not found.';
            } else {
                $stmt = $pdo->prepare(
                    'UPDATE requests
                     SET status = ?, updated_at = NOW()
                     WHERE id = ?'
                );

                $stmt->execute([$status, $id]);

                // Read the saved value back from the database.
                $verify = $pdo->prepare(
                    'SELECT status FROM requests WHERE id = ? LIMIT 1'
                );
                $verify->execute([$id]);
                $savedRequest = $verify->fetch(PDO::FETCH_ASSOC);

                if (
                    $savedRequest &&
                    $savedRequest['status'] === $status
                ) {
                    header(
                        'Location: view.php?id=' . $id . '&updated=1'
                    );
                    exit;
                }

                $error = 'The status could not be saved correctly. Check the status column in your database.';
            }
        } catch (PDOException $e) {
            error_log('Request update error: ' . $e->getMessage());
            $error = 'Unable to update the request. Check the database status column and try again.';
        }
    }
}

if (isset($_GET['updated']) && $_GET['updated'] === '1') {
    $notice = 'Request status updated successfully.';
}

// Load request details.
$request = null;

if ($id && $id > 0) {
    try {
        $stmt = $pdo->prepare(
            'SELECT *
             FROM requests
             WHERE id = ?
             LIMIT 1'
        );

        $stmt->execute([$id]);
        $request = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$request) {
            $error = 'Request not found.';
        }
    } catch (PDOException $e) {
        error_log('Request loading error: ' . $e->getMessage());
        $error = 'Unable to load the request.';
    }
} elseif ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $error = 'Invalid request ID.';
}

require_once __DIR__ . '/../includes/header.php';
?>

<style>
    .dashboard-back-button {
        display: inline-block;
        box-sizing: border-box;
        padding: 10px 18px;
        margin: 8px 0 16px;
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

    .dashboard-back-button:hover {
        background-color: #1d4ed8;
        color: #ffffff;
    }

    .current-status {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 5px;
        background-color: #e5e7eb;
        color: #111827;
        font-weight: 600;
    }

    .status-form select {
        display: block;
        max-width: 100%;
        margin: 8px 0 16px;
    }
</style>

<section class="card">
    <h2>Request Details</h2>

    <a class="dashboard-back-button" href="dashboard.php">
        &larr; Back to Dashboard
    </a>

    <?php if ($notice !== ''): ?>
        <div class="alert"><?= e($notice) ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <?php if (!$request): ?>

        <p>Request not found. Please return to the dashboard and select a request.</p>

    <?php else: ?>

        <p>
            <strong>Tracking Number:</strong>
            <?= e($request['tracking_code'] ?? '') ?>
        </p>

        <p>
            <strong>Student Name:</strong>
            <?= e($request['student_name'] ?? '') ?>
        </p>

        <p>
            <strong>Student ID:</strong>
            <?= e($request['student_id'] ?? '') ?>
        </p>

        <p>
            <strong>Email:</strong>
            <?= e($request['email'] ?? '') ?>
        </p>

        <p>
            <strong>Course and Year:</strong>
            <?= e($request['course_year'] ?? '') ?>
        </p>

        <p>
            <strong>Request Type:</strong>
            <?= e($request['request_type'] ?? '') ?>
        </p>

        <p>
            <strong>Subject:</strong>
            <?= e($request['subject'] ?? '') ?>
        </p>

        <p><strong>Request Details:</strong></p>

        <p><?= nl2br(e($request['details'] ?? '')) ?></p>

        <p>
            <strong>Date Submitted:</strong>
            <?= e($request['created_at'] ?? '') ?>
        </p>

        <p>
            <strong>Last Updated:</strong>
            <?= e($request['updated_at'] ?? '') ?>
        </p>

        <p>
            <strong>Current Status:</strong>
            <span class="current-status">
                <?= e($request['status'] ?? 'Unknown') ?>
            </span>
        </p>

        <hr>

        <h3>Update Request Status</h3>

        <form
            class="status-form"
            method="POST"
            action="view.php?id=<?= (int) $request['id'] ?>"
        >
            <input
                type="hidden"
                name="csrf_token"
                value="<?= e(csrfToken()) ?>"
            >

            <input
                type="hidden"
                name="id"
                value="<?= (int) $request['id'] ?>"
            >

            <label for="status">Status</label>

            <select id="status" name="status" required>
                <?php foreach ($allowedStatuses as $option): ?>
                    <option
                        value="<?= e($option) ?>"
                        <?= ($request['status'] ?? '') === $option
                            ? 'selected'
                            : '' ?>
                    >
                        <?= e($option) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <button type="submit">Save Changes</button>
        </form>

    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

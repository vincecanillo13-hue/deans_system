
<?php
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

requireAdmin();

$pageTitle = 'Staff Dashboard';
$error = '';
$notice = '';
$requests = [];

// Handle request deletion.
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    verifyCsrfToken();

    $deleteId = filter_input(INPUT_POST, 'delete_id', FILTER_VALIDATE_INT);

    if (!$deleteId || $deleteId < 1) {
        $error = 'Invalid request selected for deletion.';
    } else {
        try {
            $stmt = $pdo->prepare('DELETE FROM requests WHERE id = ?');
            $stmt->execute([$deleteId]);

            if ($stmt->rowCount() > 0) {
                header('Location: dashboard.php?deleted=1');
                exit;
            } else {
                $error = 'The request was not found or was already deleted.';
            }
        } catch (PDOException $e) {
            error_log('Request deletion error: ' . $e->getMessage());
            $error = 'Unable to delete this request. It may be linked to other records.';
        }
    }
}

if (isset($_GET['deleted']) && $_GET['deleted'] === '1') {
    $notice = 'Request deleted successfully.';
}

// Load submitted requests.
try {
    $stmt = $pdo->query(
        'SELECT id, tracking_code, student_name, student_id,
                request_type, status, created_at
         FROM requests
         ORDER BY created_at DESC'
    );

    $requests = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log('Dashboard request loading error: ' . $e->getMessage());
    $error = 'Unable to load requests. Please check your database setup.';
}

require_once __DIR__ . '/../includes/header.php';
?>

<style>
    /* Keep both action buttons on the same horizontal line. */
    .request-actions {
        display: flex !important;
        flex-direction: row !important;
        align-items: center;
        justify-content: flex-start;
        gap: 8px;
        flex-wrap: nowrap !important;
        white-space: nowrap;
        min-width: 235px;
    }

    .request-actions .btn {
        display: inline-flex !important;
        flex: 0 0 auto !important;
        align-items: center;
        justify-content: center;
        box-sizing: border-box;
        padding: 10px 12px;
        margin: 0;
        border: none;
        border-radius: 6px;
        background-color: #2563eb;
        color: #ffffff;
        font-family: inherit;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.4;
        text-decoration: none;
        text-align: center;
        white-space: nowrap;
        cursor: pointer;
    }

    .request-actions .btn:hover {
        background-color: #1d4ed8;
        color: #ffffff;
    }

    .request-actions .btn-delete {
        background-color: #dc2626;
    }

    .request-actions .btn-delete:hover {
        background-color: #b91c1c;
    }

    .request-actions form {
        display: inline-flex !important;
        flex: 0 0 auto !important;
        margin: 0;
        padding: 0;
    }

    /* Give the Actions column enough space for both buttons. */
    .requests-table {
        width: 100%;
        border-collapse: collapse;
    }

    .requests-table th,
    .requests-table td {
        padding: 12px;
        vertical-align: middle;
    }

    .requests-table th:last-child,
    .requests-table td:last-child {
        min-width: 250px;
        white-space: nowrap;
    }
</style>

<section class="card">
    <h2>Staff Dashboard</h2>

    <p>
        Welcome, <?= e($_SESSION['admin_username'] ?? 'Staff') ?>.
    </p>

    <?php if ($notice !== ''): ?>
        <div class="alert"><?= e($notice) ?></div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert"><?= e($error) ?></div>
    <?php endif; ?>
</section>

<section class="card">
    <h2>Submitted Requests</h2>

    <?php if (!$requests && $error === ''): ?>

        <p>No requests have been submitted yet.</p>

    <?php elseif ($requests): ?>

        <div style="overflow-x: auto;">
            <table class="requests-table">
                <thead>
                    <tr>
                        <th>Tracking No.</th>
                        <th>Student</th>
                        <th>Student ID</th>
                        <th>Request Type</th>
                        <th>Status</th>
                        <th>Date Submitted</th>
                        <th>Action</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($requests as $item): ?>
                        <tr>
                            <td><?= e($item['tracking_code']) ?></td>
                            <td><?= e($item['student_name']) ?></td>
                            <td><?= e($item['student_id']) ?></td>
                            <td><?= e($item['request_type']) ?></td>
                            <td><?= e($item['status']) ?></td>
                            <td><?= e($item['created_at']) ?></td>

                            <td>
                                <div class="request-actions">
                                    <a
                                        class="btn"
                                        href="view.php?id=<?= (int) $item['id'] ?>"
                                    >View / Update</a>

                                    <form
                                        method="POST"
                                        action="dashboard.php"
                                        onsubmit="return confirm('Are you sure you want to permanently delete this request? This action cannot be undone.');"
                                    >
                                        <input
                                            type="hidden"
                                            name="csrf_token"
                                            value="<?= e(csrfToken()) ?>"
                                        >

                                        <input
                                            type="hidden"
                                            name="delete_id"
                                            value="<?= (int) $item['id'] ?>"
                                        >

                                        <button
                                            type="submit"
                                            class="btn btn-delete"
                                        >Delete</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

    <?php endif; ?>
</section>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>

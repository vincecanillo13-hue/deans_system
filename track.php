
<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = "Track Your Request";
$request = null;
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    $trackingCode = strtoupper(trim($_POST['tracking_code'] ?? ''));

    if ($trackingCode === '') {
        $error = 'Please enter your tracking number.';
    } else {
        try {
            $stmt = $pdo->prepare(
                "SELECT tracking_code, request_type, status,
                        created_at, updated_at
                 FROM requests
                 WHERE tracking_code = ?
                 LIMIT 1"
            );

            $stmt->execute([$trackingCode]);
            $request = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$request) {
                $error = 'No request was found. Please check your tracking number.';
            }
        } catch (PDOException $e) {
            error_log('Track request error: ' . $e->getMessage());
            $error = 'Unable to check your request right now. Please try again later.';
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="card">
    <h2>Track Your Request</h2>
    <p>Enter the tracking number you received after submitting your request.</p>

    <?php if ($error !== ''): ?>
        <div class="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="track.php">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= e(csrfToken()) ?>"
        >

        <label for="tracking_code">Tracking Number</label>
        <input
            type="text"
            id="tracking_code"
            name="tracking_code"
            placeholder="Example: DRS-A1B2C3D4E5"
            maxlength="30"
            value="<?= e($_POST['tracking_code'] ?? '') ?>"
            required
        >

        <button type="submit">Check Status</button>
    </form>
</section>

<?php if ($request): ?>
    <section class="card">
        <h2>Request Status</h2>

        <p>
            <strong>Tracking Number:</strong>
            <?= e($request['tracking_code'] ?? '') ?>
        </p>

        <p>
            <strong>Request Type:</strong>
            <?= e($request['request_type'] ?? '') ?>
        </p>

        <p>
            <strong>Status:</strong>
            <?= e($request['status'] ?? '') ?>
        </p>

        <p>
            <strong>Date Submitted:</strong>
            <?= e($request['created_at'] ?? 'Not available') ?>
        </p>

        <?php if (!empty($request['updated_at'])): ?>
            <p>
                <strong>Last Updated:</strong>
                <?= e($request['updated_at']) ?>
            </p>
        <?php endif; ?>
    </section>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

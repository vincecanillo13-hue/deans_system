<?php
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Submit a Request';
$success = '';
$error = '';

$studentName = '';
$studentId = '';
$email = '';
$courseYear = '';
$requestType = '';
$subject = '';
$description = '';

$allowedTypes = [
    'Certificate of Enrollment',
    'Good Moral Certificate',
    'Transcript of Records',
    'Request for Documents',
    'Other'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfToken();

    $studentName = trim($_POST['student_name'] ?? '');
    $studentId = trim($_POST['student_id'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $courseYear = trim($_POST['course_year'] ?? '');
    $requestType = trim($_POST['request_type'] ?? '');
    $subject = trim($_POST['subject'] ?? '');
    $description = trim($_POST['description'] ?? '');

    if (
        $studentName === '' ||
        $studentId === '' ||
        $email === '' ||
        $courseYear === '' ||
        $requestType === '' ||
        $subject === '' ||
        $description === ''
    ) {
        $error = 'Please complete all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (!in_array($requestType, $allowedTypes, true)) {
        $error = 'Please select a valid request type.';
    } elseif (
        strlen($studentName) > 150 ||
        strlen($studentId) > 50 ||
        strlen($email) > 190 ||
        strlen($courseYear) > 100 ||
        strlen($subject) > 255 ||
        strlen($description) > 5000
    ) {
        $error = 'One or more fields exceed the allowed length.';
    } else {
        try {
            $trackingCode = generateTrackingCode();

            $stmt = $pdo->prepare(
                "INSERT INTO requests (
                    tracking_code,
                    student_name,
                    student_id,
                    email,
                    course_year,
                    request_type,
                    subject,
                    details,
                    status,
                    created_at,
                    updated_at
                ) VALUES (
                    ?, ?, ?, ?, ?, ?, ?, ?, 'Pending', NOW(), NOW()
                )"
            );

            $stmt->execute([
                $trackingCode,
                $studentName,
                $studentId,
                $email,
                $courseYear,
                $requestType,
                $subject,
                $description
            ]);

            $_SESSION['new_tracking_code'] = $trackingCode;

            header('Location: index.php?submitted=1');
            exit;
        } catch (PDOException $e) {
            error_log('Request submission error: ' . $e->getMessage());
            $error = 'Unable to submit your request right now. Please try again.';
        }
    }
}

if (isset($_GET['submitted']) && $_GET['submitted'] === '1') {
    $success = $_SESSION['new_tracking_code'] ?? '';
    unset($_SESSION['new_tracking_code']);
}

require_once __DIR__ . '/includes/header.php';
?>

<section class="card">
    <h2>Student Request Form</h2>
    <p>Complete this form to submit a request to the Dean's Office.</p>

    <?php if ($success !== ''): ?>
        <div class="alert">
            <h3>Request submitted successfully!</h3>
            <p>Your tracking number is:</p>
            <strong><?= e($success) ?></strong>
            <p>Save this number so you can check your request status.</p>
            <a class="btn" href="track.php">Track Your Request</a>
        </div>
    <?php endif; ?>

    <?php if ($error !== ''): ?>
        <div class="alert"><?= e($error) ?></div>
    <?php endif; ?>

    <form method="POST" action="index.php">
        <input
            type="hidden"
            name="csrf_token"
            value="<?= e(csrfToken()) ?>"
        >

        <label for="student_name">Full Name</label>
        <input
            type="text"
            id="student_name"
            name="student_name"
            maxlength="150"
            value="<?= e($studentName) ?>"
            required
        >

        <label for="student_id">Student ID</label>
        <input
            type="text"
            id="student_id"
            name="student_id"
            maxlength="50"
            value="<?= e($studentId) ?>"
            required
        >

        <label for="email">Email Address</label>
        <input
            type="email"
            id="email"
            name="email"
            maxlength="190"
            value="<?= e($email) ?>"
            required
        >

        <label for="course_year">Course and Year</label>
        <input
            type="text"
            id="course_year"
            name="course_year"
            maxlength="100"
            placeholder="e.g. BSCS - 2nd Year"
            value="<?= e($courseYear) ?>"
            required
        >

        <label for="request_type">Request Type</label>
        <select id="request_type" name="request_type" required>
            <option value="">-- Select Request Type --</option>
            <?php foreach ($allowedTypes as $type): ?>
                <option
                    value="<?= e($type) ?>"
                    <?= $requestType === $type ? 'selected' : '' ?>
                >
                    <?= e($type) ?>
                </option>
            <?php endforeach; ?>
        </select>

        <label for="subject">Subject / Document Needed</label>
        <input
            type="text"
            id="subject"
            name="subject"
            maxlength="255"
            placeholder="Briefly describe your request"
            value="<?= e($subject) ?>"
            required
        >

        <label for="description">Request Details</label>
        <textarea
            id="description"
            name="description"
            maxlength="5000"
            required
        ><?= e($description) ?></textarea>

        <button type="submit">Submit Request</button>
    </form>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
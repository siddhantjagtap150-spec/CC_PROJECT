<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
requireStudent();

$pageTitle = 'Student Dashboard';

try {
    $mysqli = getDbConnection();
    $stmt = $mysqli->prepare('SELECT COUNT(*) AS total, MAX(upload_date) AS latest_date FROM assignments WHERE student_id = ?');
    $stmt->bind_param('i', $_SESSION['user_id']);
    $stmt->execute();
    $summary = $stmt->get_result()->fetch_assoc();
} catch (Throwable $e) {
    $summary = ['total' => 0, 'latest_date' => null];
}

require_once __DIR__ . '/includes/header.php';
$currentUser = getCurrentUser();
?>
<div class="container">
    <section class="card hero-card">
        <div class="card-header">
            <h2>Welcome back, <?= e($currentUser['name'] ?? 'Student') ?>!</h2>
        </div>
        <div class="stats-grid">
            <div class="stat-card">
                <span class="stat-label">Assignments Submitted</span>
                <strong><?= e((string) ($summary['total'] ?? 0)) ?></strong>
            </div>
            <div class="stat-card">
                <span class="stat-label">Latest Submission</span>
                <strong><?= e($summary['latest_date'] ? formatDate($summary['latest_date']) : 'No submissions yet') ?></strong>
            </div>
        </div>
        <div class="stack-actions">
            <a class="btn btn-primary" href="upload.php">Upload Assignment</a>
            <a class="btn btn-secondary" href="submissions.php">My Submissions</a>
            <a class="btn btn-secondary" href="profile.php">Profile</a>
            <a class="btn btn-danger" href="logout.php">Logout</a>
        </div>
    </section>
</div>
<?php require_once __DIR__ . '/includes/footer.php';

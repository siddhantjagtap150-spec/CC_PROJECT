<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
requireStudent();

$pageTitle = 'My Submissions';

try {
    $mysqli = getDbConnection();
    $stmt = $mysqli->prepare('SELECT id, title, filename, upload_date FROM assignments WHERE student_id = ? ORDER BY upload_date DESC');
    $currentUser = getCurrentUser();
    $stmt->bind_param('i', $currentUser['id']);
    $stmt->execute();
    $result = $stmt->get_result();
    $assignments = $result->fetch_all(MYSQLI_ASSOC);
} catch (Throwable $e) {
    $assignments = [];
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="container">
    <section class="card">
        <div class="card-header">
            <h2>My Submissions</h2>
        </div>
        <?php if (empty($assignments)): ?>
            <div class="empty-state">
                <p>You have not uploaded any assignments yet.</p>
                <a class="btn btn-primary" href="upload.php">Upload one now</a>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Assignment Title</th>
                            <th>File Name</th>
                            <th>Upload Date</th>
                            <th>Download</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($assignments as $assignment): ?>
                            <tr>
                                <td><?= e($assignment['title']) ?></td>
                                <td><?= e($assignment['filename']) ?></td>
                                <td><?= e(formatDate($assignment['upload_date'])) ?></td>
                                <td>
                                    <a class="btn btn-small btn-primary" href="download.php?id=<?= (int) $assignment['id'] ?>">Download</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </section>
</div>
<?php require_once __DIR__ . '/includes/footer.php';

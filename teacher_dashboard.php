<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
requireTeacher();

$pageTitle = 'Teacher Dashboard';

try {
    $mysqli = getDbConnection();
    $stmt = $mysqli->prepare('SELECT a.id, u.name AS student_name, u.email AS student_email, a.title AS assignment_title, a.filename, a.upload_date FROM assignments a INNER JOIN users u ON u.id = a.student_id ORDER BY a.upload_date DESC');
    $stmt->execute();
    $submissions = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} catch (Throwable $e) {
    $submissions = [];
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="container">
    <section class="card">
        <div class="card-header">
            <h2>Teacher Dashboard</h2>
        </div>
        <?php if (empty($submissions)): ?>
            <div class="empty-state">
                <p>No student submissions are available yet.</p>
            </div>
        <?php else: ?>
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>Student Name</th>
                            <th>Student Email</th>
                            <th>Assignment Title</th>
                            <th>File Name</th>
                            <th>Upload Date</th>
                            <th>Download</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($submissions as $submission): ?>
                            <tr>
                                <td><?= e($submission['student_name']) ?></td>
                                <td><?= e($submission['student_email']) ?></td>
                                <td><?= e($submission['assignment_title']) ?></td>
                                <td><?= e($submission['filename']) ?></td>
                                <td><?= e(formatDate($submission['upload_date'])) ?></td>
                                <td>
                                    <a class="btn btn-small btn-primary" href="download.php?id=<?= (int) $submission['id'] ?>">Download</a>
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

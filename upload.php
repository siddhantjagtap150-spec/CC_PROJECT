<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
requireStudent();
require_once __DIR__ . '/aws/s3.php';

$pageTitle = 'Upload Assignment';
$formTitle = '';
$uploadError = '';

if (isPostRequest()) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $uploadError = 'Your session expired. Please try again.';
    } else {
        $formTitle = trim((string) ($_POST['title'] ?? ''));

        if ($formTitle === '') {
            $uploadError = 'Assignment title is required.';
        }

        if ($uploadError === '') {
            $validation = validateUploadedAssignment($_FILES['assignment_file'] ?? []);
            if (!$validation['ok']) {
                $uploadError = $validation['message'];
            } else {
                $safeName = safeFileName($_FILES['assignment_file']['name']);
                $uploadDir = uploadDirectory();
                $targetPath = $uploadDir . DIRECTORY_SEPARATOR . $safeName;

                try {
                    $storage = new LocalStorage($uploadDir);
                    $fileName = $storage->upload($_FILES['assignment_file']['tmp_name'], $safeName);

                    $mysqli = getDbConnection();
                    $stmt = $mysqli->prepare('INSERT INTO assignments (student_id, title, filename, upload_date) VALUES (?, ?, ?, NOW())');
                    $studentId = $_SESSION['user_id'];
                    $stmt->bind_param('iss', $studentId, $formTitle, $fileName);

                    if ($stmt->execute()) {
                        setFlash('success', 'Assignment uploaded successfully.');
                        redirect('submissions.php');
                    }

                    $uploadError = 'Unable to save assignment details. Please try again later.';
                    if (file_exists($targetPath)) {
                        unlink($targetPath);
                    }
                } catch (Throwable $e) {
                    $uploadError = 'The upload could not be completed. Please check the file and try again.';
                }
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="container narrow-container">
    <section class="card form-card">
        <div class="card-header">
            <h2>Upload Assignment</h2>
        </div>
        <?php if ($uploadError !== ''): ?>
            <div class="alert alert-danger" role="alert"><?= e($uploadError) ?></div>
        <?php endif; ?>

        <form method="post" enctype="multipart/form-data" class="auth-form">
            <?= csrfField(); ?>
            <div class="form-group">
                <label for="title">Assignment Title</label>
                <input id="title" name="title" type="text" value="<?= e($formTitle) ?>" required>
            </div>
            <div class="form-group">
                <label for="assignment_file">Assignment File</label>
                <input id="assignment_file" name="assignment_file" type="file" accept=".pdf,.doc,.docx,.ppt,.pptx,.zip,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,application/vnd.ms-powerpoint,application/vnd.openxmlformats-officedocument.presentationml.presentation,application/zip" required>
                <small class="field-help">Accepted formats: PDF, DOC, DOCX, PPT, PPTX or ZIP. Maximum file size: 10 MB.</small>
            </div>
            <button class="btn btn-primary full-width" type="submit">Submit assignment</button>
        </form>
    </section>
</div>
<?php require_once __DIR__ . '/includes/footer.php';

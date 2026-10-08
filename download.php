<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$assignmentId = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if ($assignmentId === false || $assignmentId === null || $assignmentId <= 0) {
    setFlash('danger', 'Invalid assignment reference.');
    redirect('dashboard.php');
}

$currentUser = getCurrentUser();

try {
    $mysqli = getDbConnection();
    $stmt = $mysqli->prepare('SELECT a.id, a.student_id, a.filename, a.title FROM assignments a WHERE a.id = ? LIMIT 1');
    $stmt->bind_param('i', $assignmentId);
    $stmt->execute();
    $assignment = $stmt->get_result()->fetch_assoc();
} catch (Throwable $e) {
    $assignment = null;
}

if (!$assignment) {
    setFlash('danger', 'The assignment was not found.');
    redirect(($currentUser['role'] ?? 'student') === 'teacher' ? 'teacher_dashboard.php' : 'dashboard.php');
}

$allowedRole = $currentUser['role'] ?? '';
if ($allowedRole === 'student' && (int) $assignment['student_id'] !== (int) $currentUser['id']) {
    setFlash('danger', 'You can only access your own submissions.');
    redirect('submissions.php');
}

if ($allowedRole !== 'teacher' && $allowedRole !== 'student') {
    setFlash('danger', 'Access denied.');
    redirect('login.php');
}

if ($allowedRole === 'teacher') {
    $currentUser['role'] = 'teacher';
}

$storedFileName = basename((string) $assignment['filename']);
$fullPath = realpath(__DIR__ . '/uploads/' . $storedFileName);
if ($fullPath === false || !is_file($fullPath)) {
    setFlash('danger', 'The requested file does not exist or is unavailable.');
    redirect(($currentUser['role'] ?? 'student') === 'teacher' ? 'teacher_dashboard.php' : 'submissions.php');
}

header('Content-Description: File Transfer');
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . basename($storedFileName) . '"');
header('Content-Length: ' . filesize($fullPath));
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');
readfile($fullPath);
exit;

<?php

declare(strict_types=1);

require_once __DIR__ . '/../db.php';

function redirect(string $target): void
{
    header('Location: ' . $target);
    exit;
}

function e(mixed $value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}

function setFlash(string $type, string $message): void
{
    initializeSession();
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash(): ?array
{
    initializeSession();
    if (!isset($_SESSION['flash']) || !is_array($_SESSION['flash'])) {
        return null;
    }

    $flash = $_SESSION['flash'];
    unset($_SESSION['flash']);
    return $flash;
}

function getCurrentUser(): ?array
{
    initializeSession();
    if (empty($_SESSION['user_id'])) {
        return null;
    }

    return [
        'id' => (int) $_SESSION['user_id'],
        'name' => $_SESSION['user_name'] ?? '',
        'email' => $_SESSION['user_email'] ?? '',
        'role' => $_SESSION['user_role'] ?? 'student',
    ];
}

function generateCsrfToken(): string
{
    initializeSession();
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function verifyCsrfToken(?string $token): bool
{
    initializeSession();
    if ($token === null || $token === '') {
        return false;
    }

    return hash_equals($_SESSION['csrf_token'] ?? '', $token);
}

function csrfField(): string
{
    return '<input type="hidden" name="csrf_token" value="' . e(generateCsrfToken()) . '">';
}

function isPostRequest(): bool
{
    return ($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'POST';
}

function formatDate(string $date): string
{
    if ($date === '' || $date === null) {
        return 'N/A';
    }

    try {
        $dt = new DateTimeImmutable($date);
        return $dt->format('d M Y, H:i');
    } catch (Exception $e) {
        return 'N/A';
    }
}

function uploadDirectory(): string
{
    $config = require __DIR__ . '/../config/config.php';
    return rtrim((string) ($config['UPLOAD_DIR'] ?? __DIR__ . '/../uploads'), DIRECTORY_SEPARATOR);
}

function safeFileName(string $originalFileName): string
{
    $base = strtolower(pathinfo($originalFileName, PATHINFO_FILENAME));
    $extension = strtolower(pathinfo($originalFileName, PATHINFO_EXTENSION));
    $sanitized = preg_replace('/[^a-z0-9_-]+/', '_', $base);
    $sanitized = trim($sanitized, '_-');

    if ($sanitized === '') {
        $sanitized = 'assignment';
    }

    if ($extension !== '') {
        return $sanitized . '_' . bin2hex(random_bytes(6)) . '.' . $extension;
    }

    return $sanitized . '_' . bin2hex(random_bytes(6));
}

function validateUploadedAssignment(array $file): array
{
    if (!isset($file['tmp_name']) || !is_uploaded_file($file['tmp_name'])) {
        return ['ok' => false, 'message' => 'Please choose a valid assignment file to upload.'];
    }

    if ($file['error'] !== UPLOAD_ERR_OK) {
        $messages = [
            UPLOAD_ERR_INI_SIZE => 'The uploaded file exceeds the server limit.',
            UPLOAD_ERR_FORM_SIZE => 'The uploaded file exceeds the form limit.',
            UPLOAD_ERR_PARTIAL => 'The file was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was selected for upload.',
            UPLOAD_ERR_NO_TMP_DIR => 'Temporary upload folder is missing.',
            UPLOAD_ERR_CANT_WRITE => 'The file could not be written to disk.',
            UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the upload.',
        ];

        $message = $messages[$file['error']] ?? 'There was an error while uploading the file.';
        return ['ok' => false, 'message' => $message];
    }

    $allowedExtensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'zip'];
    $blockedExtensions = ['php', 'php3', 'php4', 'phtml', 'exe', 'sh'];
    $allowedMimeTypes = [
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-powerpoint',
        'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'application/zip',
        'application/x-zip-compressed',
        'application/octet-stream',
    ];

    $fileName = $file['name'] ?? '';
    $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

    if ($extension === '' || !in_array($extension, $allowedExtensions, true)) {
        return ['ok' => false, 'message' => 'Unsupported file type. Please upload a PDF, DOC, DOCX, PPT, PPTX or ZIP file.'];
    }

    if (in_array($extension, $blockedExtensions, true)) {
        return ['ok' => false, 'message' => 'Executable files are not allowed.'];
    }

    $sizeInBytes = (int) $file['size'];
    if ($sizeInBytes <= 0 || $sizeInBytes > 10 * 1024 * 1024) {
        return ['ok' => false, 'message' => 'File size must be between 1 byte and 10 MB.'];
    }

    $mimeType = null;
    if (function_exists('finfo_open') && function_exists('finfo_file')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        if ($finfo !== false) {
            $mimeType = finfo_file($finfo, $file['tmp_name']);
            finfo_close($finfo);
        }
    }

    if ($mimeType !== null && !in_array($mimeType, $allowedMimeTypes, true)) {
        return ['ok' => false, 'message' => 'The uploaded file type is not permitted for assignment submission.'];
    }

    return ['ok' => true, 'message' => 'File accepted.', 'extension' => $extension, 'mime' => $mimeType];
}

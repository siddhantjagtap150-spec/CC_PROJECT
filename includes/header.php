<?php

declare(strict_types=1);

require_once __DIR__ . '/functions.php';
initializeSession();
$currentUser = getCurrentUser();
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? e($pageTitle) : 'Cloud Assignment Portal'; ?></title>
    <link rel="stylesheet" href="css/style.css">
    <script defer src="js/script.js"></script>
</head>
<body>
    <div class="app-shell">
        <header class="topbar">
            <div class="container nav-wrap">
                <a class="brand" href="index.php">Cloud Assignment Portal</a>
                <nav class="main-nav" aria-label="Main navigation">
                    <?php if ($currentUser): ?>
                        <span class="nav-greeting">Hi, <?= e(explode(' ', trim((string) $currentUser['name']))[0] ?: 'there') ?></span>
                        <?php if (($currentUser['role'] ?? '') === 'student'): ?>
                            <a href="dashboard.php">Dashboard</a>
                            <a href="upload.php">Upload</a>
                            <a href="submissions.php">My Submissions</a>
                            <a href="profile.php">Profile</a>
                        <?php else: ?>
                            <a href="teacher_dashboard.php">Teacher Dashboard</a>
                            <a href="profile.php">Profile</a>
                        <?php endif; ?>
                        <a class="nav-logout" href="logout.php">Log out</a>
                    <?php else: ?>
                        <a href="index.php">Home</a>
                        <a href="login.php">Login</a>
                        <a class="nav-register" href="register.php">Create account</a>
                    <?php endif; ?>
                </nav>
            </div>
        </header>

        <?php if ($flash): ?>
            <div class="container">
                <div class="alert alert-<?= e($flash['type']) ?>" role="alert">
                    <?= e($flash['message']) ?>
                </div>
            </div>
        <?php endif; ?>

        <main class="page-content">

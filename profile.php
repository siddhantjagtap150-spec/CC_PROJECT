<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/auth.php';
requireLogin();

$pageTitle = 'Profile';
$currentUser = getCurrentUser();

require_once __DIR__ . '/includes/header.php';
?>
<div class="container narrow-container">
    <section class="card profile-card">
        <div class="card-header">
            <h2>Profile</h2>
        </div>
        <div class="profile-details">
            <p><strong>Name:</strong> <?= e($currentUser['name'] ?? '') ?></p>
            <p><strong>Email:</strong> <?= e($currentUser['email'] ?? '') ?></p>
            <p><strong>Role:</strong> <?= e(ucfirst($currentUser['role'] ?? 'student')) ?></p>
        </div>
    </section>
</div>
<?php require_once __DIR__ . '/includes/footer.php';

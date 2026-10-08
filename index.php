<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/header.php';

if ($currentUser) {
    if (($currentUser['role'] ?? 'student') === 'teacher') {
        redirect('teacher_dashboard.php');
    }
    redirect('dashboard.php');
}

$pageTitle = 'Student Assignment Portal';
?>
<div class="container">
    <section class="hero">
        <div class="hero-copy">
            <span class="eyebrow">A simpler way to manage coursework</span>
            <h1>Submit assignments with confidence.</h1>
            <p>Upload your coursework, keep track of what you have submitted, and give teachers one place to review student work.</p>
            <div class="stack-actions">
                <a class="btn btn-primary" href="register.php">Get started</a>
                <a class="btn btn-secondary" href="login.php">I already have an account</a>
            </div>
        </div>
        <div class="hero-panel">
            <div class="mini-card">
                <h3>For students</h3>
                <ul>
                    <li>Create your student account</li>
                    <li>Upload coursework in a few steps</li>
                    <li>Check your past submissions any time</li>
                </ul>
            </div>
            <div class="mini-card">
                <h3>For teachers</h3>
                <ul>
                    <li>See submissions in one place</li>
                    <li>Download student files</li>
                    <li>Find student and assignment details</li>
                </ul>
            </div>
        </div>
    </section>
</div>
<?php require_once __DIR__ . '/includes/footer.php';

<?php

declare(strict_types=1);

require_once __DIR__ . '/functions.php';

function requireLogin(): void
{
    initializeSession();
    if (empty($_SESSION['user_id'])) {
        setFlash('danger', 'Please log in to access that page.');
        redirect('login.php');
    }
}

function requireStudent(): void
{
    requireLogin();
    $role = $_SESSION['user_role'] ?? '';
    if ($role === 'teacher') {
        setFlash('danger', 'You are being redirected to the teacher dashboard.');
        redirect('teacher_dashboard.php');
    }

    if ($role !== 'student') {
        setFlash('danger', 'This area is restricted to students.');
        redirect('login.php');
    }
}

function requireTeacher(): void
{
    requireLogin();
    $role = $_SESSION['user_role'] ?? '';
    if ($role === 'student') {
        setFlash('danger', 'You are being redirected to the student dashboard.');
        redirect('dashboard.php');
    }

    if ($role !== 'teacher') {
        setFlash('danger', 'This area is restricted to teachers.');
        redirect('login.php');
    }
}

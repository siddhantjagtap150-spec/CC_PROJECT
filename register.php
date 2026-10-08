<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
initializeSession();

if ($user = getCurrentUser()) {
    redirect(($user['role'] ?? 'student') === 'teacher' ? 'teacher_dashboard.php' : 'dashboard.php');
}

$pageTitle = 'Register';
$errors = [];
$formData = ['name' => '', 'email' => ''];

if (isPostRequest()) {
    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        $errors[] = 'Your session has expired. Please try again.';
    } else {
        $formData['name'] = trim((string) ($_POST['name'] ?? ''));
        $formData['email'] = trim((string) ($_POST['email'] ?? ''));
        $password = (string) ($_POST['password'] ?? '');
        $confirmPassword = (string) ($_POST['confirm_password'] ?? '');

        if ($formData['name'] === '') {
            $errors[] = 'Full name is required.';
        }

        if ($formData['email'] === '' || !filter_var($formData['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Please enter a valid email address.';
        }

        if (strlen($password) < 6) {
            $errors[] = 'Password must be at least 6 characters long.';
        }

        if ($password !== $confirmPassword) {
            $errors[] = 'Passwords do not match.';
        }

        if (empty($errors)) {
            try {
                $mysqli = getDbConnection();
                $stmt = $mysqli->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
                $stmt->bind_param('s', $formData['email']);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {
                    $errors[] = 'An account with that email already exists.';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $role = 'student';
                    $insert = $mysqli->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)');
                    $insert->bind_param('ssss', $formData['name'], $formData['email'], $hash, $role);
                    if ($insert->execute()) {
                        setFlash('success', 'Registration successful. You can log in now.');
                        redirect('login.php');
                    } else {
                        $errors[] = 'Unable to create your account at the moment. Please try again later.';
                    }
                }
            } catch (Throwable $e) {
                $errors[] = 'A database error prevented registration. Please try again later.';
            }
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="container narrow-container">
    <section class="card form-card">
        <div class="card-header">
            <h2>Create Account</h2>
        </div>
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger" role="alert">
                <ul class="message-list">
                    <?php foreach ($errors as $error): ?>
                        <li><?= e($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        <form method="post" class="auth-form" novalidate>
            <?= csrfField(); ?>
            <div class="form-group">
                <label for="name">Full Name</label>
                <input id="name" name="name" type="text" value="<?= e($formData['name']) ?>" required>
            </div>
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="<?= e($formData['email']) ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" minlength="6" autocomplete="new-password" aria-describedby="password-help" required>
                <small class="field-help" id="password-help">Use at least 6 characters.</small>
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input id="confirm_password" name="confirm_password" type="password" minlength="6" autocomplete="new-password" required>
            </div>
            <button class="btn btn-primary full-width" type="submit">Create my account</button>
        </form>
        <p class="form-footer">Already have an account? <a href="login.php">Login here</a>.</p>
    </section>
</div>
<?php require_once __DIR__ . '/includes/footer.php';

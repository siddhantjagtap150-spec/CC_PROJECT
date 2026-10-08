<?php

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
initializeSession();

if ($user = getCurrentUser()) {
    redirect(($user['role'] ?? 'student') === 'teacher' ? 'teacher_dashboard.php' : 'dashboard.php');
}

$pageTitle = 'Login';
$loginEmail = '';

if (isPostRequest()) {
    $loginEmail = trim((string) ($_POST['email'] ?? ''));
    $password = (string) ($_POST['password'] ?? '');

    if (!verifyCsrfToken($_POST['csrf_token'] ?? null)) {
        setFlash('danger', 'Your session expired. Please try again.');
    } else {
        try {
            $mysqli = getDbConnection();
            $stmt = $mysqli->prepare('SELECT id, name, email, password, role FROM users WHERE email = ? LIMIT 1');
            $stmt->bind_param('s', $loginEmail);
            $stmt->execute();
            $result = $stmt->get_result();
            $user = $result->fetch_assoc();

            if ($user && password_verify($password, $user['password'])) {
                session_regenerate_id(true);
                $_SESSION['user_id'] = (int) $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'];

                setFlash('success', 'Welcome back, ' . $user['name'] . '!');
                redirect(($user['role'] ?? 'student') === 'teacher' ? 'teacher_dashboard.php' : 'dashboard.php');
            }

            setFlash('danger', 'Invalid email or password. Please try again.');
        } catch (Throwable $e) {
            setFlash('danger', 'Unable to sign in at the moment. Please try again later.');
        }
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="container narrow-container">
    <section class="card form-card">
        <div class="card-header">
            <h2>Login</h2>
        </div>
        <form method="post" class="auth-form" novalidate>
            <?= csrfField(); ?>
            <div class="form-group">
                <label for="email">Email</label>
                <input id="email" name="email" type="email" value="<?= e($loginEmail) ?>" required>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input id="password" name="password" type="password" required>
            </div>
            <button class="btn btn-primary full-width" type="submit">Login</button>
        </form>
        <p class="form-footer">Need an account? <a href="register.php">Register now</a>.</p>
    </section>
</div>
<?php require_once __DIR__ . '/includes/footer.php';

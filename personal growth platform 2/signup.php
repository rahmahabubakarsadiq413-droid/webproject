<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Sign Up';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['full_name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $confirm = $_POST['confirm_password'] ?? '';

    if ($name === '' || $email === '' || $pass === '') {
        $error = 'Please fill in all fields.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($pass) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($pass !== $confirm) {
        $error = 'Passwords do not match.';
    } else {
        $check = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $check->execute([$email]);
        if ($check->fetch()) {
            $error = 'An account with that email already exists.';
        } else {
            $stmt = $pdo->prepare('INSERT INTO users (full_name, email, password_hash, coins) VALUES (?,?,?,0)');
            $stmt->execute([$name, $email, password_hash($pass, PASSWORD_DEFAULT)]);
            $_SESSION['user_id'] = $pdo->lastInsertId();
            header('Location: dashboard.php');
            exit;
        }
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
  <h1>Start Your Growth Journey</h1>
  <p class="sub">Create your account and begin learning at your own pace.</p>
  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" novalidate>
    <div class="field">
      <label>Full Name</label>
      <input type="text" name="full_name" value="<?= e($_POST['full_name'] ?? '') ?>" placeholder="Enter your full name" required>
    </div>
    <div class="field">
      <label>Email Address</label>
      <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" placeholder="Enter your email" required>
    </div>
    <div class="field">
      <label>Password</label>
      <input type="password" name="password" placeholder="Create a password" required>
    </div>
    <div class="field">
      <label>Confirm Password</label>
      <input type="password" name="confirm_password" placeholder="Confirm your password" required>
    </div>
    <button type="submit" class="btn btn-primary">Create Account</button>
  </form>
  <div class="auth-switch">Already have an account? <a href="login.php">Log in</a></div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

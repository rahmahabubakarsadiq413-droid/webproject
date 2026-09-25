<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
$pageTitle = 'Log In';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $pass = $_POST['password'] ?? '';
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$user || !password_verify($pass, $user['password_hash'])) {
        $error = 'Incorrect email or password.';
    } else {
        $_SESSION['user_id'] = $user['id'];
        header('Location: dashboard.php');
        exit;
    }
}
require_once __DIR__ . '/includes/header.php';
?>
<div class="auth-wrap">
  <h1>Welcome Back</h1>
  <p class="sub">Continue your learning journey.</p>
  <?php if ($error): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
  <form method="post" novalidate>
    <div class="field">
      <label>Email Address</label>
      <input type="email" name="email" value="<?= e($_POST['email'] ?? '') ?>" placeholder="Enter your email" required>
    </div>
    <div class="field">
      <label>Password</label>
      <input type="password" name="password" placeholder="Enter your password" required>
    </div>
    <button type="submit" class="btn btn-primary">Log In</button>
  </form>
  <div class="auth-switch">Don't have an account? <a href="signup.php">Sign up</a></div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';
$__user = current_user($pdo);
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?= isset($pageTitle) ? e($pageTitle) . ' · Nayfli' : 'Nayfli — Learn. Grow. Lead.' ?></title>
<link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
<nav class="nav">
  <a href="index.php" class="brand">
    <img src="assets/images/logo.png" alt="Nayfli logo">
  </a>
  <ul class="nav-links">
    <li><a href="index.php">Home</a></li>
    <li><a href="index.php#learn">Learn</a></li>
    <li><a href="index.php#learn">Programs</a></li>
    <li><a href="#">Resources</a></li>
    <li><a href="#">Contact</a></li>
  </ul>
  <div class="nav-right">
    <?php if ($__user): ?>
      <span class="coin-pill">
        <svg viewBox="0 0 24 24"><?= icon_svg('coin') ?></svg>
        <?= (int)$__user['coins'] ?>
      </span>
      <a href="dashboard.php" class="btn btn-ghost">Hi, <?= e(explode(' ', $__user['full_name'])[0]) ?></a>
      <a href="logout.php" class="btn btn-outline">Log out</a>
    <?php else: ?>
      <a href="login.php" class="btn btn-ghost">Log in</a>
      <a href="signup.php" class="btn btn-primary">Start Learning</a>
    <?php endif; ?>
  </div>
</nav>

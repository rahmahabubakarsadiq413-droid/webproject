<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT c.*, cat.slug AS cat_slug, cat.name AS cat_name, cat.icon, cat.accent FROM courses c JOIN categories cat ON cat.id = c.category_id WHERE c.id = ?');
$stmt->execute([$id]);
$course = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$course) { header('Location: index.php'); exit; }

$pageTitle = $course['title'];
$__user = current_user($pdo);

$lessons = $pdo->prepare('SELECT * FROM lessons WHERE course_id = ? ORDER BY sort_order');
$lessons->execute([$id]);
$lessons = $lessons->fetchAll(PDO::FETCH_ASSOC);

$doneIds = [];
if ($__user) {
    $d = $pdo->prepare('SELECT lesson_id FROM user_progress WHERE user_id = ?');
    $d->execute([$__user['id']]);
    $doneIds = $d->fetchAll(PDO::FETCH_COLUMN);
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="container">
  <a href="category.php?slug=<?= e($course['cat_slug']) ?>" class="back-link">&larr; Back to <?= e($course['cat_name']) ?></a>
  <div class="cat-header">
    <div class="ic" style="background:<?= e($course['accent']) ?>">
      <svg viewBox="0 0 24 24"><?= icon_svg($course['icon']) ?></svg>
    </div>
    <div>
      <h1><?= e($course['title']) ?></h1>
      <p><?= count($lessons) ?> lessons &middot; earn coins as you complete each one</p>
    </div>
  </div>

  <div class="lesson-list">
    <?php foreach ($lessons as $i => $l): $isDone = in_array($l['id'], $doneIds); ?>
      <a class="lesson-row <?= $isDone ? 'done' : '' ?>" href="lesson.php?id=<?= (int)$l['id'] ?>">
        <div class="lesson-left">
          <div class="lesson-num"><?= $isDone ? '&#10003;' : $i + 1 ?></div>
          <div>
            <div class="lesson-title"><?= e($l['title']) ?></div>
            <div class="lesson-sub">
              <svg width="13" height="13" viewBox="0 0 24 24" style="stroke:currentColor;fill:none;stroke-width:1.8"><?= icon_svg('clock') ?></svg>
              <?= ceil($l['duration_seconds'] / 60) ?> min read
            </div>
          </div>
        </div>
        <span class="chip">+<?= (int)$l['coin_reward'] ?> coins</span>
      </a>
    <?php endforeach; ?>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();
$pageTitle = 'Dashboard';
$__user = current_user($pdo);

// total lessons completed
$doneStmt = $pdo->prepare('SELECT COUNT(*) FROM user_progress WHERE user_id = ?');
$doneStmt->execute([$__user['id']]);
$lessonsDone = (int)$doneStmt->fetchColumn();

// courses in progress (at least 1 lesson done, not all done)
$courses = $pdo->query('SELECT id, title, category_id FROM courses')->fetchAll(PDO::FETCH_ASSOC);
$inProgress = [];
foreach ($courses as $c) {
    $p = course_progress($pdo, $__user['id'], $c['id']);
    if ($p['done'] > 0 && $p['done'] < $p['total']) {
        $catStmt = $pdo->prepare('SELECT name, slug, icon, accent FROM categories WHERE id = ?');
        $catStmt->execute([$c['category_id']]);
        $cat = $catStmt->fetch(PDO::FETCH_ASSOC);
        $inProgress[] = array_merge($c, $p, $cat);
    }
}

require_once __DIR__ . '/includes/header.php';
?>
<div class="container">
  <div class="dash-top">
    <div class="dash-hello">
      <h1>Good to see you, <?= e(explode(' ', $__user['full_name'])[0]) ?> 👋</h1>
      <p>Continue where you left off, or explore a new learning area.</p>
    </div>
    <a href="index.php#learn" class="btn btn-primary">Explore Learning Areas</a>
  </div>

  <div class="stat-cards">
    <div class="stat-card">
      <div class="stat-ic"><svg viewBox="0 0 24 24"><?= icon_svg('coin') ?></svg></div>
      <div><div class="stat-num"><?= (int)$__user['coins'] ?></div><div class="stat-label">Coins earned</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-ic"><svg viewBox="0 0 24 24"><?= icon_svg('bulb') ?></svg></div>
      <div><div class="stat-num"><?= $lessonsDone ?></div><div class="stat-label">Lessons completed</div></div>
    </div>
    <div class="stat-card">
      <div class="stat-ic"><svg viewBox="0 0 24 24"><?= icon_svg('shield') ?></svg></div>
      <div><div class="stat-num"><?= count($inProgress) ?></div><div class="stat-label">Courses in progress</div></div>
    </div>
  </div>

  <div class="row-head"><h3>Continue Learning</h3></div>
  <?php if (!$inProgress): ?>
    <p style="color:var(--sub)">You haven't started a course yet — pick a learning area above to begin and start earning coins.</p>
  <?php else: ?>
    <div class="course-grid">
      <?php foreach ($inProgress as $c): ?>
        <a class="course-card" href="course.php?id=<?= (int)$c['id'] ?>">
          <h4><?= e($c['title']) ?></h4>
          <div class="course-meta"><?= e($c['name']) ?></div>
          <div class="progress-track"><div class="progress-fill" style="width:<?= $c['pct'] ?>%"></div></div>
          <div class="progress-nums"><span><?= $c['done'] ?>/<?= $c['total'] ?></span><span><?= $c['pct'] ?>%</span></div>
        </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';

$slug = $_GET['slug'] ?? '';
$stmt = $pdo->prepare('SELECT * FROM categories WHERE slug = ?');
$stmt->execute([$slug]);
$category = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$category) { header('Location: index.php'); exit; }

$pageTitle = $category['name'];
$__user = current_user($pdo);

$courses = $pdo->prepare('SELECT * FROM courses WHERE category_id = ? ORDER BY sort_order');
$courses->execute([$category['id']]);
$courses = $courses->fetchAll(PDO::FETCH_ASSOC);

foreach ($courses as &$c) {
    $lc = $pdo->prepare('SELECT COUNT(*) FROM lessons WHERE course_id = ?');
    $lc->execute([$c['id']]);
    $c['lesson_count'] = (int)$lc->fetchColumn();
    $c['progress'] = $__user ? course_progress($pdo, $__user['id'], $c['id']) : ['done'=>0,'total'=>$c['lesson_count'],'pct'=>0];
}
unset($c);

require_once __DIR__ . '/includes/header.php';
?>
<div class="container">
  <a href="index.php" class="back-link">&larr; Back</a>
  <div class="cat-header">
    <div class="ic" style="background:<?= e($category['accent']) ?>">
      <svg viewBox="0 0 24 24"><?= icon_svg($category['icon']) ?></svg>
    </div>
    <div>
      <h1><?= e($category['name']) ?></h1>
      <p><?= e($category['description']) ?></p>
    </div>
  </div>

  <div class="row-head">
    <h3>Popular Courses</h3>
    <a href="#">View All</a>
  </div>

  <div class="course-grid">
    <?php foreach ($courses as $c): $p = $c['progress']; ?>
      <a class="course-card" href="course.php?id=<?= (int)$c['id'] ?>">
        <h4><?= e($c['title']) ?></h4>
        <div class="course-meta"><?= $c['lesson_count'] ?> lessons</div>
        <div class="progress-track"><div class="progress-fill" style="width:<?= $p['pct'] ?>%"></div></div>
        <div class="progress-nums"><span><?= $p['done'] ?>/<?= $p['total'] ?></span><span><?= $p['pct'] ?>%</span></div>
      </a>
    <?php endforeach; ?>
  </div>
</div>
<?php require_once __DIR__ . '/includes/footer.php'; ?>

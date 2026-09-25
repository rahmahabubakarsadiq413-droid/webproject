<?php
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_login();

$id = (int)($_GET['id'] ?? 0);
$stmt = $pdo->prepare('SELECT l.*, c.title AS course_title, c.id AS course_id, cat.slug AS cat_slug FROM lessons l JOIN courses c ON c.id = l.course_id JOIN categories cat ON cat.id = c.category_id WHERE l.id = ?');
$stmt->execute([$id]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$lesson) { header('Location: index.php'); exit; }

$pageTitle = $lesson['title'];
$__user = current_user($pdo);

$already = $pdo->prepare('SELECT 1 FROM user_progress WHERE user_id = ? AND lesson_id = ?');
$already->execute([$__user['id'], $id]);
$alreadyDone = (bool)$already->fetchColumn();

$prog = course_progress($pdo, $__user['id'], $lesson['course_id']);

require_once __DIR__ . '/includes/header.php';
?>
<div class="lesson-wrap">
  <a href="course.php?id=<?= (int)$lesson['course_id'] ?>" class="back-link">&larr; Back to <?= e($lesson['course_title']) ?></a>

  <div class="lesson-top">
    <div class="timer-box" id="timerBox">
      <svg viewBox="0 0 24 24"><?= icon_svg('clock') ?></svg>
      <span id="timerLabel"><?= $alreadyDone ? 'Completed' : gmdate('i:s', (int)$lesson['duration_seconds']) ?></span>
    </div>
    <span class="chip">+<?= (int)$lesson['coin_reward'] ?> coins on completion</span>
  </div>

  <h1><?= e($lesson['title']) ?></h1>
  <div class="lesson-progress-line"><?= e($lesson['course_title']) ?> &middot; <?= $prog['done'] ?>/<?= $prog['total'] ?> lessons complete (<?= $prog['pct'] ?>%)</div>
  <div class="progress-track" style="margin-bottom:22px"><div class="progress-fill" style="width:<?= $prog['pct'] ?>%"></div></div>

  <div class="lesson-body"><?= e($lesson['body']) ?></div>

  <div class="reward-note">
    <svg viewBox="0 0 24 24"><?= icon_svg('coin') ?></svg>
    Read for the full reading session to unlock your coin reward.
  </div>

  <button id="completeBtn" class="btn btn-primary complete-btn" <?= $alreadyDone ? '' : 'disabled' ?>>
    <?= $alreadyDone ? 'Already Completed' : 'Read the timer down to complete' ?>
  </button>
</div>

<div class="overlay" id="coinOverlay">
  <div class="coin-modal">
    <svg class="big-coin" viewBox="0 0 24 24"><?= icon_svg('coin') ?></svg>
    <h3>Lesson complete!</h3>
    <p id="coinModalText">You earned coins for finishing this reading session.</p>
    <a href="course.php?id=<?= (int)$lesson['course_id'] ?>" class="btn btn-primary">Continue</a>
  </div>
</div>

<script>
(function(){
  const alreadyDone = <?= $alreadyDone ? 'true' : 'false' ?>;
  const lessonId = <?= (int)$lesson['id'] ?>;
  const coinReward = <?= (int)$lesson['coin_reward'] ?>;
  let seconds = <?= (int)$lesson['duration_seconds'] ?>;
  const label = document.getElementById('timerLabel');
  const btn = document.getElementById('completeBtn');
  const box = document.getElementById('timerBox');

  function fmt(s){
    const m = Math.floor(s/60).toString().padStart(2,'0');
    const r = (s%60).toString().padStart(2,'0');
    return m+':'+r;
  }

  if (!alreadyDone) {
    const tick = setInterval(() => {
      seconds--;
      if (seconds <= 0) {
        clearInterval(tick);
        label.textContent = 'Time!';
        box.style.borderColor = '#1f7a3b';
        box.style.color = '#1f7a3b';
        btn.disabled = false;
        btn.textContent = 'Mark as Complete (+' + coinReward + ' coins)';
      } else {
        label.textContent = fmt(seconds);
      }
    }, 1000);

    btn.addEventListener('click', function(){
      btn.disabled = true;
      btn.textContent = 'Saving...';
      fetch('api/complete_lesson.php', {
        method: 'POST',
        headers: {'Content-Type':'application/x-www-form-urlencoded'},
        body: 'lesson_id=' + lessonId
      })
      .then(r => r.json())
      .then(data => {
        if (data.ok) {
          document.getElementById('coinModalText').textContent =
            'You earned ' + data.reward + ' coins. Your balance is now ' + data.new_balance + ' coins.';
          document.getElementById('coinOverlay').classList.add('show');
        } else {
          btn.textContent = data.message || 'Something went wrong — try again';
          btn.disabled = false;
        }
      })
      .catch(() => {
        btn.textContent = 'Network error — try again';
        btn.disabled = false;
      });
    });
  }
})();
</script>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

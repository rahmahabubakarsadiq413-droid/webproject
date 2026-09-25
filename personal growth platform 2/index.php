<?php
$pageTitle = 'Home';
require_once __DIR__ . '/includes/header.php';
$categories = $pdo->query('SELECT * FROM categories ORDER BY id')->fetchAll(PDO::FETCH_ASSOC);
?>

<section class="hero">
  <div class="container">
    <div class="hero-copy">
      <div class="eyebrow">Empowering the Next Generation of Female Leaders</div>
      <h1>Learn. Grow.<br><span>Lead.</span></h1>
      <p>A self-paced learning platform designed to help young women develop confidence, leadership skills, and practical knowledge for life and the future.</p>
      <div class="hero-actions">
        <a href="<?= $__user ? 'dashboard.php' : 'signup.php' ?>" class="btn btn-primary">Start your journey</a>
        <a href="#learn" class="btn btn-outline">Explore Courses</a>
      </div>
    </div>
    <div class="hero-img">
      <img src="https://images.unsplash.com/photo-1531123897727-8f129e1688ce?w=800&q=80" alt="Young woman learning on a laptop">
    </div>
  </div>
</section>

<section class="section container">
  <h2>Why learn <b>With Us</b></h2>
  <div class="why-grid">
    <div class="why-card">
      <div class="why-icon"><svg viewBox="0 0 24 24"><?= icon_svg('clock') ?></svg></div>
      <div><h4>Learn at Your Space</h4><p>Study whenever and wherever you are comfortable.</p></div>
    </div>
    <div class="why-card">
      <div class="why-icon"><svg viewBox="0 0 24 24"><?= icon_svg('monitor') ?></svg></div>
      <div><h4>Curated Content</h4><p>Carefully selected lessons that are practical, relevant, and inspiring.</p></div>
    </div>
    <div class="why-card">
      <div class="why-icon"><svg viewBox="0 0 24 24"><?= icon_svg('bulb') ?></svg></div>
      <div><h4>Practical &amp; Actionable</h4><p>Apply what you learn with activities, reflections, and real-life examples.</p></div>
    </div>
    <div class="why-card">
      <div class="why-icon"><svg viewBox="0 0 24 24"><?= icon_svg('coin') ?></svg></div>
      <div><h4>Track Your Growth</h4><p>Earn coins, set goals, track progress, and celebrate every milestone.</p></div>
    </div>
  </div>
</section>

<section class="section container" id="learn">
  <h2>Explore Learning Areas</h2>
  <div class="cat-grid">
    <?php foreach ($categories as $c): ?>
      <a class="cat-card" href="category.php?slug=<?= e($c['slug']) ?>">
        <div class="ic" style="background:<?= e($c['accent']) ?>">
          <svg viewBox="0 0 24 24"><?= icon_svg($c['icon']) ?></svg>
        </div>
        <span><?= e($c['name']) ?></span>
      </a>
    <?php endforeach; ?>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>

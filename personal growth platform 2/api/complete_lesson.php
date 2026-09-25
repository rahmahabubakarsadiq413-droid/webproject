<?php
require_once __DIR__ . '/../includes/db.php';
require_once __DIR__ . '/../includes/functions.php';
header('Content-Type: application/json');

if (empty($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['ok' => false, 'message' => 'Please log in.']);
    exit;
}

$userId = (int)$_SESSION['user_id'];
$lessonId = (int)($_POST['lesson_id'] ?? 0);

$stmt = $pdo->prepare('SELECT * FROM lessons WHERE id = ?');
$stmt->execute([$lessonId]);
$lesson = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$lesson) {
    http_response_code(404);
    echo json_encode(['ok' => false, 'message' => 'Lesson not found.']);
    exit;
}

$check = $pdo->prepare('SELECT 1 FROM user_progress WHERE user_id = ? AND lesson_id = ?');
$check->execute([$userId, $lessonId]);

if ($check->fetch()) {
    $bal = $pdo->prepare('SELECT coins FROM users WHERE id = ?');
    $bal->execute([$userId]);
    echo json_encode(['ok' => true, 'reward' => 0, 'new_balance' => (int)$bal->fetchColumn(), 'message' => 'Already completed.']);
    exit;
}

$pdo->beginTransaction();
try {
    $ins = $pdo->prepare('INSERT INTO user_progress (user_id, lesson_id) VALUES (?, ?)');
    $ins->execute([$userId, $lessonId]);

    $upd = $pdo->prepare('UPDATE users SET coins = coins + ? WHERE id = ?');
    $upd->execute([$lesson['coin_reward'], $userId]);

    $pdo->commit();

    $bal = $pdo->prepare('SELECT coins FROM users WHERE id = ?');
    $bal->execute([$userId]);

    echo json_encode([
        'ok' => true,
        'reward' => (int)$lesson['coin_reward'],
        'new_balance' => (int)$bal->fetchColumn(),
    ]);
} catch (Exception $e) {
    $pdo->rollBack();
    http_response_code(500);
    echo json_encode(['ok' => false, 'message' => 'Could not save progress.']);
}

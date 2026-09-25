<?php
// includes/functions.php
if (session_status() === PHP_SESSION_NONE) session_start();

function current_user(PDO $pdo) {
    if (empty($_SESSION['user_id'])) return null;
    $stmt = $pdo->prepare('SELECT id, full_name, email, coins FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    $u = $stmt->fetch(PDO::FETCH_ASSOC);
    return $u ?: null;
}

function require_login() {
    if (empty($_SESSION['user_id'])) {
        header('Location: login.php');
        exit;
    }
}

function e($str) { return htmlspecialchars($str ?? '', ENT_QUOTES, 'UTF-8'); }

function course_progress(PDO $pdo, int $userId, int $courseId): array {
    $total = $pdo->prepare('SELECT COUNT(*) FROM lessons WHERE course_id = ?');
    $total->execute([$courseId]);
    $totalCount = (int)$total->fetchColumn();

    $done = $pdo->prepare('
        SELECT COUNT(*) FROM user_progress up
        JOIN lessons l ON l.id = up.lesson_id
        WHERE up.user_id = ? AND l.course_id = ?
    ');
    $done->execute([$userId, $courseId]);
    $doneCount = (int)$done->fetchColumn();

    $pct = $totalCount > 0 ? round(($doneCount / $totalCount) * 100) : 0;
    return ['done' => $doneCount, 'total' => $totalCount, 'pct' => $pct];
}

function icon_svg(string $icon): string {
    $icons = [
        'users'   => '<path d="M9 11a3 3 0 100-6 3 3 0 000 6zM17 11a3 3 0 100-6 3 3 0 000 6zM2 20c0-3.3 3.1-6 7-6s7 2.7 7 6M14 14.2c3.4.4 6 2.7 6 5.8" stroke-linecap="round" stroke-linejoin="round"/>',
        'shield'  => '<path d="M12 3l7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6l7-3z" stroke-linecap="round" stroke-linejoin="round"/>',
        'wallet'  => '<path d="M3 7a2 2 0 012-2h12a2 2 0 012 2v10a2 2 0 01-2 2H5a2 2 0 01-2-2V7z" stroke-linecap="round" stroke-linejoin="round"/><path d="M16 12h3" stroke-linecap="round"/>',
        'chat'    => '<path d="M4 5h16v11H8l-4 4V5z" stroke-linecap="round" stroke-linejoin="round"/>',
        'monitor' => '<path d="M3 5h18v11H3zM8 20h8M12 16v4" stroke-linecap="round" stroke-linejoin="round"/>',
        'lotus'   => '<path d="M12 21c-4-1-7-4-7-8 2 0 4 1 5 2 0-3-2-5-2-8 2 1 4 3 4 6 0-3 2-5 4-6 0 3-2 5-2 8 1-1 3-2 5-2 0 4-3 7-7 8z" stroke-linecap="round" stroke-linejoin="round"/>',
        'bulb'    => '<path d="M9 18h6M10 21h4M12 3a6 6 0 00-3 11c.6.4 1 1.2 1 2h4c0-.8.4-1.6 1-2a6 6 0 00-3-11z" stroke-linecap="round" stroke-linejoin="round"/>',
        'coin'    => '<circle cx="12" cy="12" r="9"/><path d="M9.5 15.5c.5.6 1.4 1 2.5 1 1.7 0 3-.9 3-2s-1.3-1.7-3-2-3-.9-3-2 1.3-2 3-2c1.1 0 2 .4 2.5 1" stroke-linecap="round" stroke-linejoin="round"/><path d="M12 7v1.3M12 15.7V17" stroke-linecap="round"/>',
        'clock'   => '<circle cx="12" cy="12" r="9"/><path d="M12 7v5l3.5 2" stroke-linecap="round" stroke-linejoin="round"/>',
    ];
    return $icons[$icon] ?? $icons['bulb'];
}

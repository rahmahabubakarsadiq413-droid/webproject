<?php
// includes/db.php
// Single PDO/SQLite connection + auto schema creation + seed data.
// Swapping to MySQL later only requires changing this file's DSN.

$dbFile = __DIR__ . '/../data/nayfli.sqlite';
$isNew  = !file_exists($dbFile);

try {
    $pdo = new PDO('sqlite:' . $dbFile);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec('PRAGMA foreign_keys = ON');
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

if ($isNew) {
    $pdo->exec("
        CREATE TABLE users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            full_name TEXT NOT NULL,
            email TEXT NOT NULL UNIQUE,
            password_hash TEXT NOT NULL,
            coins INTEGER NOT NULL DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE categories (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            slug TEXT NOT NULL UNIQUE,
            name TEXT NOT NULL,
            description TEXT NOT NULL,
            icon TEXT NOT NULL,
            accent TEXT NOT NULL
        );

        CREATE TABLE courses (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            category_id INTEGER NOT NULL REFERENCES categories(id) ON DELETE CASCADE,
            title TEXT NOT NULL,
            sort_order INTEGER NOT NULL DEFAULT 0
        );

        CREATE TABLE lessons (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            course_id INTEGER NOT NULL REFERENCES courses(id) ON DELETE CASCADE,
            title TEXT NOT NULL,
            body TEXT NOT NULL,
            duration_seconds INTEGER NOT NULL DEFAULT 120,
            coin_reward INTEGER NOT NULL DEFAULT 10,
            sort_order INTEGER NOT NULL DEFAULT 0
        );

        CREATE TABLE user_progress (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            user_id INTEGER NOT NULL REFERENCES users(id) ON DELETE CASCADE,
            lesson_id INTEGER NOT NULL REFERENCES lessons(id) ON DELETE CASCADE,
            completed_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            UNIQUE(user_id, lesson_id)
        );
    ");

    $categories = [
        ['personal-leadership', 'Personal Leadership', 'Build confidence, set goals, and develop the mindset to lead your life and inspire others.', 'users', '#f6c9b0'],
        ['career-development', 'Career Development', 'Explore career paths, gain employability skills, and prepare for a successful professional journey.', 'shield', '#bfe3c8'],
        ['financial-literacy', 'Financial Literacy', 'Learn how to manage money, save, budget, and make smart financial decisions.', 'wallet', '#e6c9ee'],
        ['communication-skills', 'Communication Skills', 'Improve how you speak, listen, and express yourself with clarity and confidence.', 'chat', '#f6c9b0'],
        ['digital-skills', 'Digital Skills', 'Gain essential digital skills to study, work, and grow in today\'s digital world.', 'monitor', '#bcdcf4'],
        ['health-wellness', 'Health and Wellness', 'Take care of your physical and mental well-being for a balanced and happy life.', 'lotus', '#dcd2f0'],
        ['entrepreneurship', 'Entrepreneurship & Innovation', 'Turn your ideas into action and build the skills to start and grow your own ventures.', 'bulb', '#f6c9b0'],
    ];
    $catIns = $pdo->prepare('INSERT INTO categories (slug, name, description, icon, accent) VALUES (?,?,?,?,?)');
    foreach ($categories as $c) $catIns->execute($c);

    $catIds = $pdo->query('SELECT id, slug FROM categories')->fetchAll(PDO::FETCH_KEY_PAIR);
    // fetch as slug=>id
    $catIds = array_flip($catIds);

    $courseData = [
        'personal-leadership' => ['Effective Communication', 'Goal Setting for Your Future', 'Mindset & Growth', 'Time Management for Success'],
        'career-development'  => ['Discover Your Career Paths', 'Resume Building', 'Interview Preparation', 'Professional Etiquette'],
        'financial-literacy'  => ['Money Management Basics', 'Budgeting Made Easy', 'Saving and Investing 101', 'Financial Goals'],
        'communication-skills'=> ['Effective Communication', 'Public Speaking with Confidence', 'Active Listening', 'Writing with Impact'],
        'digital-skills'      => ['Computer Basics', 'Microsoft Office Essentials', 'Internet Safety', 'Online Research Skills'],
        'health-wellness'     => ['Mental Health Awareness', 'Stress Management', 'Healthy Living Basics', 'Self-Care Habits'],
        'entrepreneurship'    => ['Starting Your Business', 'Business Planning Basics', 'Marketing Essentials', 'Innovation & Creativity'],
    ];

    $lessonTitles = ['Getting Started', 'Core Concepts', 'Putting It Into Practice', 'Common Pitfalls', 'Building the Habit', 'Reflect & Review'];
    $lessonBodyTpl = "This lesson walks you through practical, real-world guidance on %s. Take your time reading through each point below, think about how it applies to your own life, and complete the short reflection at the end.\n\n1. Understand where you are starting from and why this topic matters for your growth.\n2. Learn the key idea in simple, actionable terms you can use today.\n3. Practice with a small, real example from your own life.\n4. Reflect on one change you will make this week.\n\nTake this lesson at your own pace — the timer below just helps you stay present while you read.";

    $courseIns = $pdo->prepare('INSERT INTO courses (category_id, title, sort_order) VALUES (?,?,?)');
    $lessonIns = $pdo->prepare('INSERT INTO lessons (course_id, title, body, duration_seconds, coin_reward, sort_order) VALUES (?,?,?,?,?,?)');

    foreach ($courseData as $slug => $courses) {
        $catId = $catIds[$slug];
        foreach ($courses as $ci => $title) {
            $courseIns->execute([$catId, $title, $ci]);
            $courseId = $pdo->lastInsertId();
            // vary lesson counts 4,5,6 like the mockups
            $lessonCount = [4,5,6][($ci) % 3];
            for ($li = 0; $li < $lessonCount; $li++) {
                $lt = $lessonTitles[$li % count($lessonTitles)];
                $duration = 90 + ($li * 30); // 90s..~4min, grows per lesson
                $reward = 10 + ($li * 2);
                $lessonIns->execute([$courseId, $lt, sprintf($lessonBodyTpl, strtolower($title)), $duration, $reward, $li]);
            }
        }
    }
}

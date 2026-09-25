# Nayfli — Learn. Grow. Lead.

A full-stack PHP + SQLite rebuild of the Nayfli learning platform mockups: home page,
7 learning-area category pages, course/lesson lists, a reading-session **timer**,
a **coin** reward system, and **progress bars** throughout — plus real sign up / log in.

## Requirements
- PHP 8.1+ with the `pdo_sqlite` extension (bundled with PHP by default)
- No MySQL/other server needed — it uses a self-contained SQLite file

## Run it
From this folder:

```bash
php -S localhost:8000
```

Then open **http://localhost:8000** in your browser.

On first run, `data/nayfli.sqlite` is created automatically and seeded with the
7 categories and their courses/lessons (icons, colors, lesson counts and reading
durations all match your original mockups). Just create an account via **Start
Learning** to try the full flow.

## How the requested features work
- **Timer**: each lesson (`lesson.php`) has a `duration_seconds` value set per lesson.
  A live JS countdown runs on the page; the "Mark as Complete" button stays disabled
  until the timer hits zero, so a session has to actually run its course.
- **Coins**: when a lesson is completed, `api/complete_lesson.php` verifies the
  lesson exists, checks the user hasn't already completed it (no double-dipping),
  then atomically inserts a `user_progress` row and increments `users.coins` — all
  server-side, so the reward can't be faked from the browser console.
- **Progress bars**: `course_progress()` in `includes/functions.php` computes
  completed/total lessons per course and renders the same orange progress-bar
  style used across category pages, course pages, the lesson header, and the
  dashboard.
- **Logo**: `assets/images/logo.png` is your Nayfli logo with the white background
  removed (transparent PNG), used in the nav on every page.

## File structure
```
index.php              Home page (hero + "Explore Learning Areas")
signup.php / login.php / logout.php   Session-based auth (password_hash/verify)
dashboard.php           Coin balance, lessons completed, courses in progress
category.php?slug=...   One of the 7 learning areas + its 4 popular courses
course.php?id=...       Lesson list for a course, with per-lesson coin chip
lesson.php?id=...       Reading page: timer, lesson body, complete button
api/complete_lesson.php AJAX endpoint: awards coins + records progress
includes/db.php         PDO/SQLite connection, schema + seed data
includes/functions.php  Auth helpers, progress calculation, icon SVGs
assets/css/style.css    Full stylesheet matching the original design
data/                   SQLite database lives here (auto-created)
```

## Notes / next steps if you deploy this for real
- Swap SQLite for MySQL by changing the DSN in `includes/db.php` — the rest of
  the code uses plain PDO and needs no other changes.
- Add CSRF tokens to the forms and rate-limit `login.php` before production use.
- The hero image on the home page points to an external Unsplash URL — replace
  with a hosted image of your choice if you want it fully self-contained.

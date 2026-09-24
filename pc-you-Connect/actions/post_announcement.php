<?php
require_once '../auth.php';
require_once '../db.php';
require_admin('../index.php');

// ── Only accept POST ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../announcements.php");
    exit;
}

// ── Collect & sanitize inputs ────────────────────────────────
$title   = trim($_POST['title']   ?? '');
$content = trim($_POST['content'] ?? '');
$category = trim($_POST['category'] ?? '');
$announcementID = trim($_POST['announcement_id'] ?? '');
$user_id = current_user_id();

// ── Validation ───────────────────────────────────────────────

// 1. Required fields
if (empty($title) || empty($content) || empty($category)) {
    header("Location: ../announcements.php?error=required");
    exit;
}

if (!in_array($category, ['important', 'event', 'maintenance', 'general'], true)) {
    header("Location: ../announcements.php?error=invalid_category");
    exit;
}

// 2. Title length (5–150 characters)
if (strlen($title) < 5) {
    header("Location: ../announcements.php?error=title_short");
    exit;
}
if (strlen($title) > 150) {
    header("Location: ../announcements.php?error=title_long");
    exit;
}

// 3. Content minimum length (at least 10 characters)
if (strlen($content) < 10) {
    header("Location: ../announcements.php?error=content_short");
    exit;
}

// 4. Content max length (5000 characters)
if (strlen($content) > 5000) {
    header("Location: ../announcements.php?error=content_long");
    exit;
}

if ($announcementID !== '') {
    if (!ctype_digit($announcementID) || (int) $announcementID <= 0) {
        header("Location: ../announcements.php?error=invalid_id");
        exit;
    }

    $stmt = $conn->prepare(
        'UPDATE announcements SET category = ?, title = ?, content = ? WHERE id = ?'
    );
    $announcementID = (int) $announcementID;
    $stmt->bind_param('sssi', $category, $title, $content, $announcementID);
    $stmt->execute();
    $stmt->close();
} else {
    $stmt = $conn->prepare(
        'INSERT INTO announcements (category, title, content, created_by) VALUES (?, ?, ?, ?)'
    );
    $stmt->bind_param('sssi', $category, $title, $content, $user_id);
    $stmt->execute();
    $stmt->close();
}

header("Location: ../announcements.php?success=posted");
exit;
?>

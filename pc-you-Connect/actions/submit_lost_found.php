<?php
require_once '../auth.php';
require_once '../db.php';
require_login('../index.php');

// ── Only accept POST ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../lostandfound.php");
    exit;
}

// ── Collect & sanitize inputs ────────────────────────────────
$type        = trim($_POST['type']        ?? '');
$item_name   = trim($_POST['item_name']   ?? '');
$category    = trim($_POST['category']    ?? 'Others');
$description = trim($_POST['description'] ?? '');
$location    = trim($_POST['location']    ?? '');
$user_id     = current_user_id();

// ── Validation ───────────────────────────────────────────────

// 1. Required fields
if (empty($type) || empty($item_name)) {
    header("Location: ../lostandfound.php?error=required");
    exit;
}

// 2. Type must only be 'lost' or 'found'
if (!in_array($type, ['lost', 'found'])) {
    header("Location: ../lostandfound.php?error=invalid_type");
    exit;
}

// 3. Item name length (2–100 characters)
if (strlen($item_name) < 2) {
    header("Location: ../lostandfound.php?error=name_short");
    exit;
}
if (strlen($item_name) > 100) {
    header("Location: ../lostandfound.php?error=name_long");
    exit;
}

// 4. Description max length (optional but capped)
if (strlen($description) > 1000) {
    header("Location: ../lostandfound.php?error=desc_long");
    exit;
}

// 5. Location max length (optional but capped)
if (strlen($location) > 100) {
    header("Location: ../lostandfound.php?error=location_long");
    exit;
}

$stmt = $conn->prepare(
    "INSERT INTO lost_found (user_id, type, item_name, category, description, location, status)
     VALUES (?, ?, ?, ?, ?, ?, 'unclaimed')"
);
$stmt->bind_param('isssss', $user_id, $type, $item_name, $category, $description, $location);
$stmt->execute();
$stmt->close();

header("Location: ../lostandfound.php?success=submitted");
exit;
?>

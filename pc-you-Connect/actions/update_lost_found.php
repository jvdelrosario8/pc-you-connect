<?php
require_once '../auth.php';
require_admin('../index.php');

// ── Only accept POST ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/lost_found.php");
    exit;
}

// ── Collect inputs ───────────────────────────────────────────
$record_id  = trim($_POST['record_id'] ?? '');
$new_status = trim($_POST['status']    ?? '');

// ── Validation ───────────────────────────────────────────────

// 1. Record ID must be a positive integer
if (!filter_var($record_id, FILTER_VALIDATE_INT) || (int)$record_id <= 0) {
    header("Location: ../admin/lost_found.php?error=invalid_id");
    exit;
}

// 2. Status must only be 'open' or 'resolved'
if (!in_array($new_status, ['open', 'resolved'])) {
    header("Location: ../admin/lost_found.php?error=invalid_status");
    exit;
}

// ── TODO: Uncomment when database is ready ───────────────────
/*
require '../config/db.php';

// Make sure the record exists
$stmt = $pdo->prepare("SELECT * FROM lost_found WHERE id = ?");
$stmt->execute([$record_id]);
$record = $stmt->fetch();

if (!$record) {
    header("Location: ../admin/lost_found.php?error=not_found");
    exit;
}

// Update the status
$stmt = $pdo->prepare("UPDATE lost_found SET status = ? WHERE id = ?");
$stmt->execute([$new_status, $record_id]);
*/

// ── Temporary success (remove when DB is ready) ──────────────
header("Location: ../admin/lost_found.php?success=updated");
exit;
?>

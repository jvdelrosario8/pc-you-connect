<?php
require_once '../auth.php';
require_login('../index.php');

// ── Only accept POST ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../pages/reserve.php");
    exit;
}

// ── Collect & sanitize inputs ────────────────────────────────
$facility_id = trim($_POST['facility_id'] ?? '');
$date        = trim($_POST['date']        ?? '');
$time_start  = trim($_POST['time_start']  ?? '');
$time_end    = trim($_POST['time_end']    ?? '');
$user_id     = current_user_id();

// ── Validation ───────────────────────────────────────────────

// 1. Required fields
if (empty($facility_id) || empty($date) || empty($time_start) || empty($time_end)) {
    header("Location: ../pages/reserve.php?error=required");
    exit;
}

// 2. Facility ID must be a positive integer
if (!filter_var($facility_id, FILTER_VALIDATE_INT) || (int)$facility_id <= 0) {
    header("Location: ../pages/reserve.php?error=invalid_facility");
    exit;
}

// 3. Date must be valid and not in the past
if (!strtotime($date)) {
    header("Location: ../pages/reserve.php?error=invalid_date");
    exit;
}
if (strtotime($date) < strtotime(date('Y-m-d'))) {
    header("Location: ../pages/reserve.php?error=past_date");
    exit;
}

// 4. Times must be valid
if (!strtotime($time_start) || !strtotime($time_end)) {
    header("Location: ../pages/reserve.php?error=invalid_time");
    exit;
}

// 5. End time must be after start time
if ($time_end <= $time_start) {
    header("Location: ../pages/reserve.php?error=time_order");
    exit;
}

// 6. Minimum 30-minute reservation
$duration_minutes = (strtotime($time_end) - strtotime($time_start)) / 60;
if ($duration_minutes < 30) {
    header("Location: ../pages/reserve.php?error=too_short");
    exit;
}

// 7. Maximum 8-hour reservation
if ($duration_minutes > 480) {
    header("Location: ../pages/reserve.php?error=too_long");
    exit;
}

// ── TODO: Uncomment when database is ready ───────────────────
/*
require '../config/db.php';

// Check if facility exists and is available
$stmt = $pdo->prepare("SELECT * FROM facilities WHERE id = ? AND status = 'available'");
$stmt->execute([$facility_id]);
$facility = $stmt->fetch();

if (!$facility) {
    header("Location: ../pages/reserve.php?error=unavailable");
    exit;
}

// Check for conflicting reservations on the same facility, date, and time
$stmt = $pdo->prepare("
    SELECT id FROM reservations
    WHERE facility_id = ?
      AND date = ?
      AND status != 'rejected'
      AND (
          (time_start < ? AND time_end > ?)
      )
");
$stmt->execute([$facility_id, $date, $time_end, $time_start]);
$conflict = $stmt->fetch();

if ($conflict) {
    header("Location: ../pages/reserve.php?error=conflict");
    exit;
}

// Insert the reservation
$stmt = $pdo->prepare("
    INSERT INTO reservations (user_id, facility_id, date, time_start, time_end, status)
    VALUES (?, ?, ?, ?, ?, 'pending')
");
$stmt->execute([$user_id, $facility_id, $date, $time_start, $time_end]);
*/

// ── Temporary success (remove when DB is ready) ──────────────
header("Location: ../pages/reserve.php?success=submitted");
exit;
?>

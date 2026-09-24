<?php
require_once '../auth.php';
require_admin('../index.php');

// ── Only accept POST ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../admin/reservations.php");
    exit;
}

// ── Collect inputs ───────────────────────────────────────────
$reservation_id = trim($_POST['reservation_id'] ?? '');
$new_status     = trim($_POST['status']         ?? '');

// ── Validation ───────────────────────────────────────────────

// 1. Reservation ID must be a positive integer
if (!filter_var($reservation_id, FILTER_VALIDATE_INT) || (int)$reservation_id <= 0) {
    header("Location: ../admin/reservations.php?error=invalid_id");
    exit;
}

// 2. Status must only be 'approved' or 'rejected'
if (!in_array($new_status, ['approved', 'rejected'])) {
    header("Location: ../admin/reservations.php?error=invalid_status");
    exit;
}

// ── TODO: Uncomment when database is ready ───────────────────
/*
require '../config/db.php';

// Make sure the reservation exists and is still pending
$stmt = $pdo->prepare("SELECT * FROM reservations WHERE id = ?");
$stmt->execute([$reservation_id]);
$reservation = $stmt->fetch();

if (!$reservation) {
    header("Location: ../admin/reservations.php?error=not_found");
    exit;
}

if ($reservation['status'] !== 'pending') {
    header("Location: ../admin/reservations.php?error=already_processed");
    exit;
}

// Update the status
$stmt = $pdo->prepare("UPDATE reservations SET status = ? WHERE id = ?");
$stmt->execute([$new_status, $reservation_id]);
*/

// ── Temporary success (remove when DB is ready) ──────────────
$msg = $new_status === 'approved' ? 'Reservation approved.' : 'Reservation rejected.';
header("Location: ../admin/reservations.php?success=" . urlencode($msg));
exit;
?>

<?php
require_once '../auth.php';
require_once '../db.php';
require_admin('../index.php');

// ── Only accept POST ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../facilities.php");
    exit;
}

// ── Collect inputs ───────────────────────────────────────────
$reservation_id = trim($_POST['reservation_id'] ?? '');
$new_status     = trim($_POST['status']         ?? '');
$adminID        = current_user_id();

// ── Validation ───────────────────────────────────────────────

// 1. Reservation ID must be a positive integer
if (!filter_var($reservation_id, FILTER_VALIDATE_INT) || (int)$reservation_id <= 0) {
    header("Location: ../facilities.php?error=invalid_id");
    exit;
}

// 2. Status must only be 'approved' or 'rejected'
if (!in_array($new_status, ['approved', 'rejected'])) {
    header("Location: ../facilities.php?error=invalid_status");
    exit;
}

// Make sure the reservation exists and is still pending
$stmt = $conn->prepare("SELECT facility_id, date, time_start, time_end, status FROM reservations WHERE id = ?");
$stmt->bind_param('i', $reservation_id);
$stmt->execute();
$reservation = $stmt->get_result()->fetch_assoc();

if (!$reservation) {
    header("Location: ../facilities.php?error=not_found");
    exit;
}

if ($reservation['status'] !== 'pending') {
    header("Location: ../facilities.php?error=already_processed");
    exit;
}

if ($new_status === 'approved') {
    $conflictStmt = $conn->prepare(
        "SELECT id FROM reservations
         WHERE facility_id = ? AND date = ? AND status = 'approved' AND id <> ?
           AND time_start < ? AND time_end > ?
         LIMIT 1"
    );
    $conflictStmt->bind_param(
        'isiss',
        $reservation['facility_id'],
        $reservation['date'],
        $reservation_id,
        $reservation['time_end'],
        $reservation['time_start']
    );
    $conflictStmt->execute();
    if ($conflictStmt->get_result()->num_rows > 0) {
        header("Location: ../facilities.php?error=" . urlencode('Cannot approve: this time conflicts with an approved booking.'));
        exit;
    }
}

$updateStmt = $conn->prepare("UPDATE reservations SET status = ?, reviewed_by = ?, updated_at = CURRENT_TIMESTAMP WHERE id = ? AND status = 'pending'");
$updateStmt->bind_param('sii', $new_status, $adminID, $reservation_id);
$updateStmt->execute();

$msg = $new_status === 'approved' ? 'Reservation approved.' : 'Reservation rejected.';
header("Location: ../facilities.php?success=" . urlencode($msg));
exit;
?>

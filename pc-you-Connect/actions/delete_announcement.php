<?php
require_once '../auth.php';
require_once '../db.php';
require_admin('../index.php');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../announcements.php');
    exit;
}

$announcementID = trim($_POST['announcement_id'] ?? '');

if (!ctype_digit($announcementID) || (int) $announcementID <= 0) {
    header('Location: ../announcements.php?error=invalid_id');
    exit;
}

$announcementID = (int) $announcementID;
$stmt = $conn->prepare('DELETE FROM announcements WHERE id = ?');
$stmt->bind_param('i', $announcementID);
$stmt->execute();
$stmt->close();

header('Location: ../announcements.php?success=deleted');
exit;
?>
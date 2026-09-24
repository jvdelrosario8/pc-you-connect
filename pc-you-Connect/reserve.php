<?php
require_once 'auth.php';
require_once 'db.php';
require_login('index.php');

$userID = current_user_id();
$firstName = $_SESSION['first_name'] ?? 'Student';
$lastName = $_SESSION['last_name'] ?? '';
$fullName = trim($firstName . ' ' . $lastName);

$facilities = [];
$facilityResult = $conn->query("SELECT id, name, location, capacity FROM facilities WHERE status = 'available' ORDER BY name");
if ($facilityResult) {
  while ($row = $facilityResult->fetch_assoc()) $facilities[] = $row;
}
$schedules = [];
$scheduleResult = $conn->query(
  "SELECT facility_id, date, time_start, time_end
   FROM reservations
   WHERE status != 'rejected' AND date >= CURDATE()"
);
if ($scheduleResult) {
  while ($row = $scheduleResult->fetch_assoc()) $schedules[] = $row;
}

$reservations = [];
$reservationStmt = $conn->prepare(
  "SELECT r.date, r.time_start, r.time_end, r.status, f.name AS facility_name
   FROM reservations r
   INNER JOIN facilities f ON f.id = r.facility_id
   WHERE r.user_id = ?
   ORDER BY r.date DESC, r.time_start DESC
   LIMIT 5"
);
$reservationStmt->bind_param('i', $userID);
$reservationStmt->execute();
$reservationResult = $reservationStmt->get_result();
while ($row = $reservationResult->fetch_assoc()) $reservations[] = $row;

$success = $_GET['success'] ?? '';
$error = $_GET['error'] ?? '';
$errorMessages = [
  'required' => 'Complete all reservation fields.',
  'invalid_facility' => 'Choose a valid facility.',
  'unavailable' => 'That facility is currently unavailable.',
  'invalid_date' => 'Enter a valid reservation date.',
  'past_date' => 'Reservations must be for today or a future date.',
  'invalid_time' => 'Enter valid start and end times.',
  'time_order' => 'The end time must be after the start time.',
  'too_short' => 'Reservations must be at least 30 minutes long.',
  'too_long' => 'Reservations can be at most 8 hours long.',
  'conflict' => 'That facility is already booked for the selected time.'
];
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PC-YOU! Connect - Reserve a Facility</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="reserve.css">
<link rel="stylesheet" href="reserve-calendar.css">
</head>
<body>
<header class="reserve-nav"><a class="brand" href="reserve.php"><img src="assets/icon.jpg" alt="PC-YOU! Connect logo"><span>PC-YOU! Connect</span></a><div class="nav-user"><span><?php echo htmlspecialchars($fullName); ?></span><a href="actions/logout.php">Sign out</a></div></header>
<main class="reserve-shell">
  <section class="reserve-intro"><div><span class="eyebrow">STUDENT SERVICES</span><h1>Reserve a campus facility</h1><p>Send a booking request to the admin team. Your reservation is confirmed only after it is approved.</p></div><div class="intro-mark"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M4 21V7l8-4 8 4v14"/><path d="M9 21v-6h6v6M4 11h16"/></svg></div></section>
  <?php if ($success): ?><div class="notice success">Request submitted. The admin team will review your reservation.</div><?php elseif ($error): ?><div class="notice error"><?php echo htmlspecialchars($errorMessages[$error] ?? 'Something went wrong. Please try again.'); ?></div><?php endif; ?>
  <div class="reserve-grid">
    <section class="panel form-panel"><div class="panel-heading"><div><h2>New reservation request</h2><p>Select a facility, date, and time.</p></div><span class="pending-label">PENDING REVIEW</span></div><form action="actions/submit_reservation.php" method="POST"><label for="facility_id">Facility</label><select id="facility_id" name="facility_id" required><option value="">Choose a facility</option><?php foreach ($facilities as $facility): ?><option value="<?php echo (int) $facility['id']; ?>"><?php echo htmlspecialchars($facility['name']); ?> - <?php echo htmlspecialchars($facility['location']); ?></option><?php endforeach; ?></select><div class="capacity-note">Facilities have different capacities. Confirm your group size with the admin if needed.</div><div class="field-row"><div><label for="date">Date</label><input id="date" name="date" type="date" min="<?php echo date('Y-m-d'); ?>" required></div><div><label for="time_start">Start time</label><input id="time_start" name="time_start" type="time" value="09:00" required></div><div><label for="time_end">End time</label><input id="time_end" name="time_end" type="time" value="10:00" required></div></div><div class="calendar-box"><div class="calendar-head"><button type="button" class="calendar-nav" id="previousMonth" aria-label="Previous month">&#8249;</button><strong id="calendarMonth"></strong><button type="button" class="calendar-nav" id="nextMonth" aria-label="Next month">&#8250;</button></div><div class="calendar-weekdays"><span>Sun</span><span>Mon</span><span>Tue</span><span>Wed</span><span>Thu</span><span>Fri</span><span>Sat</span></div><div class="calendar-days" id="calendarDays"></div><div class="calendar-legend"><span><i class="available-dot"></i>Available</span><span><i class="taken-dot"></i>Not available</span></div></div><button class="submit-request" type="submit">Submit reservation request <span>→</span></button></form></section>
    <aside class="panel guide-panel"><h2>Before you submit</h2><ul><li><span>01</span><div><strong>Pick your schedule</strong><p>Requests must be at least 30 minutes and no longer than 8 hours.</p></div></li><li><span>02</span><div><strong>Check availability</strong><p>The admin will check the selected date and time for conflicts.</p></div></li><li><span>03</span><div><strong>Wait for approval</strong><p>Your request stays pending until the admin approves or denies it.</p></div></li></ul></aside>
  </div>
  <section class="panel history-panel"><div class="panel-heading"><div><h2>My recent requests</h2><p>Track the status of your facility bookings.</p></div></div><?php if (!$reservations): ?><div class="empty-history">You have not submitted a facility request yet.</div><?php else: ?><div class="history-list"><?php foreach ($reservations as $reservation): ?><div class="history-row"><div><strong><?php echo htmlspecialchars($reservation['facility_name']); ?></strong><p><?php echo date('M j, Y', strtotime($reservation['date'])); ?> • <?php echo date('g:i A', strtotime($reservation['time_start'])); ?> - <?php echo date('g:i A', strtotime($reservation['time_end'])); ?></p></div><span class="status <?php echo htmlspecialchars($reservation['status']); ?>"><?php echo strtoupper(htmlspecialchars($reservation['status'])); ?></span></div><?php endforeach; ?></div><?php endif; ?></section>
</main>
<script>window.reservationSchedules = <?php echo json_encode($schedules, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;</script>
<script src="reserve.js"></script>
</body>
</html>

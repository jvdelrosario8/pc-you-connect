<?php
require_once 'auth.php';
require_once 'db.php';
require_admin();

$adminID = current_user_id();
$firstName = $_SESSION['first_name'] ?? 'Admin';
$lastName = $_SESSION['last_name'] ?? '';
$fullName = trim($firstName . ' ' . $lastName);
$selectedStatus = $_GET['status'] ?? 'pending';
$allowedStatuses = ['pending', 'approved', 'rejected', 'all'];
if (!in_array($selectedStatus, $allowedStatuses, true)) {
  $selectedStatus = 'pending';
}

function facilityTimeAgo($timestamp)
{
  $seconds = max(0, time() - strtotime($timestamp));
  if ($seconds < 60) return 'just now';
  if ($seconds < 3600) return floor($seconds / 60) . ' min ago';
  if ($seconds < 86400) {
    $hours = floor($seconds / 3600);
    return $hours . ($hours === 1 ? ' hr ago' : ' hrs ago');
  }
  $days = floor($seconds / 86400);
  return $days . ($days === 1 ? ' day ago' : ' days ago');
}

$counts = ['all' => 0, 'pending' => 0, 'approved' => 0, 'rejected' => 0];
$countResult = $conn->query("SELECT status, COUNT(*) AS total FROM reservations GROUP BY status");
if ($countResult) {
  while ($row = $countResult->fetch_assoc()) {
    if (isset($counts[$row['status']])) $counts[$row['status']] = (int) $row['total'];
    $counts['all'] += (int) $row['total'];
  }
}

$requests = [];
$statusSql = $selectedStatus === 'all' ? '' : "WHERE r.status = '" . $conn->real_escape_string($selectedStatus) . "'";
$requestResult = $conn->query(
  "SELECT r.id, r.user_id, r.date, r.time_start, r.time_end, r.status, r.created_at,
          f.name AS facility_name, f.location, f.capacity,
      s.StudentID, s.FirstName, s.LastName,
      EXISTS (SELECT 1 FROM reservations conflict
        WHERE conflict.facility_id = r.facility_id
          AND conflict.date = r.date
          AND conflict.id <> r.id
          AND conflict.status = 'approved'
          AND conflict.time_start < r.time_end
          AND conflict.time_end > r.time_start) AS approved_conflict,
      EXISTS (SELECT 1 FROM reservations conflict
        WHERE conflict.facility_id = r.facility_id
          AND conflict.date = r.date
          AND conflict.id <> r.id
          AND conflict.status = 'pending'
          AND conflict.time_start < r.time_end
          AND conflict.time_end > r.time_start) AS pending_conflict
   FROM reservations r
   INNER JOIN facilities f ON f.id = r.facility_id
   LEFT JOIN studentlogincredentials s ON s.StudentID = r.user_id
   $statusSql
   ORDER BY CASE r.status WHEN 'pending' THEN 0 ELSE 1 END, r.date ASC, r.time_start ASC"
);
if ($requestResult) {
  while ($row = $requestResult->fetch_assoc()) $requests[] = $row;
}
$facilityCatalog = [];
$facilityCatalogResult = $conn->query("SELECT name, location, capacity FROM facilities WHERE status = 'available' ORDER BY name");
if ($facilityCatalogResult) {
  while ($row = $facilityCatalogResult->fetch_assoc()) $facilityCatalog[] = $row;
}

$setupError = $requestResult === false;
$flash = $_GET['success'] ?? $_GET['error'] ?? '';
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PC-YOU! Connect - Facilities</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="admin_dashboard_styles.css">
<link rel="stylesheet" href="facilities.css">
</head>
<body>
<div class="app">
  <aside class="sidebar">
    <div class="brand">
      <div class="brand-logo"><img src="assets/icon.jpg" alt="PC-YOU! Connect logo"></div>
      <div><div class="brand-name">PC-YOU! Connect</div><div class="brand-sub">Campus Portal</div></div>
    </div>
    <div class="nav-section-label">MAIN MENU</div>
    <ul class="nav-list">
      <li><a href="admin_dashboard.php" class="nav-item" data-transition><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>Dashboard</a></li>
      <li><a href="facilities.php" class="nav-item active" data-transition><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 21V7l8-4 8 4v14" stroke-linejoin="round"/><path d="M9 21v-6h6v6" stroke-linejoin="round"/></svg>Facilities</a></li>
      <li><a href="lostandfound.php" class="nav-item" data-transition><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M4 6l1.5 13a2 2 0 0 0 2 1.8h9a2 2 0 0 0 2-1.8L20 6M9 6V4.5A1.5 1.5 0 0 1 10.5 3h3A1.5 1.5 0 0 1 15 4.5V6"/><path d="M9.5 11.5h5"/></svg>Lost &amp; Found</a></li>
      <li><a href="announcements.php" class="nav-item" data-transition><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m3 11 18-5v12L3 14v-3z" stroke-linejoin="round"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>Announcements</a></li>
    </ul>
    <div class="nav-section-label">ACCOUNT</div>
    <ul class="nav-list">
      <li class="nav-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.5"/><path d="M4.5 20c1.4-3.6 4.4-5.5 7.5-5.5s6.1 1.9 7.5 5.5"/></svg>Profile</li>
      <li class="nav-item"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 13.5c.1-.5.1-1 0-1.5l1.6-1.4-2-3.4-2 .6a7.9 7.9 0 0 0-1.3-.8l-.3 2.1h-4l-.3-2.1c-.5.2-.9.5-1.3.8l-2-.6-2 3.4L6 12c-.1.5-.1 1 0 1.5l-1.6 1.4 2 3.4 2-.6c.4.3.8.6 1.3.8l.3 2.1h4l.3-2.1c.5-.2.9-.5 1.3-.8l2 .6 2-3.4-1.6-1.4Z"/></svg>Settings</li>
    </ul>
    <div class="sidebar-spacer"></div>
    <a href="actions/logout.php" class="signout" onclick="return confirm('Are you sure you want to log out?');"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4M16 17l5-5-5-5M21 12H9"/></svg>Sign Out</a>
  </aside>

  <main class="main">
    <div class="topbar"><div class="topbar-right">
      <button class="icon-btn" id="themeToggle" aria-label="Toggle dark mode" type="button"><svg id="themeIconSun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="4.5"/><path d="M12 2v2M12 20v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M2 12h2M20 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/></svg><svg id="themeIconMoon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="display:none;"><path d="M20 14.5A8.5 8.5 0 0 1 9.5 4a8.5 8.5 0 1 0 10.5 10.5Z"/></svg></button>
      <div class="icon-btn"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 9a6 6 0 0 0-12 0c0 5-2 6-2 6h16s-2-1-2-6Z"/><path d="M10.5 19a1.6 1.6 0 0 0 3 0"/></svg></div>
      <div class="user-chip"><div class="user-avatar"></div><div><div class="user-name"><?php echo htmlspecialchars($fullName); ?></div><div class="user-role">User ID: <?php echo htmlspecialchars($adminID); ?></div></div></div>
    </div></div>

    <div class="page-heading facilities-heading"><div><h1>Facilities</h1><p>Review student booking requests and keep campus spaces running smoothly.</p></div><span class="facility-date"><?php echo date('F j, Y'); ?></span></div>

    <?php if ($flash): ?><div class="facility-alert <?php echo isset($_GET['error']) ? 'is-error' : 'is-success'; ?>"><?php echo htmlspecialchars($flash); ?></div><?php endif; ?>
    <?php if ($setupError): ?><div class="facility-alert is-error">Facility requests are not available yet. Import the updated <strong>db/pcyouconnect.sql</strong> schema first.</div><?php endif; ?>

    <div class="stat-grid facility-stats">
      <div class="card stat-card"><div class="stat-top"><div class="stat-icon blue"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 3v4M17 3v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Z"/></svg></div><span class="badge blue-badge">ALL</span></div><div class="stat-number"><?php echo $counts['all']; ?></div><div class="stat-label">Total Requests</div></div>
      <div class="card stat-card"><div class="stat-top"><div class="stat-icon yellow"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg></div><span class="badge yellow">ACTION NEEDED</span></div><div class="stat-number"><?php echo $counts['pending']; ?></div><div class="stat-label">Pending Review</div></div>
      <div class="card stat-card"><div class="stat-top"><div class="stat-icon green-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg></div><span class="badge green">CONFIRMED</span></div><div class="stat-number"><?php echo $counts['approved']; ?></div><div class="stat-label">Approved</div></div>
      <div class="card stat-card"><div class="stat-top"><div class="stat-icon purple"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 3 3 20h18L12 3Z"/><path d="M12 9v5M12 17v.01"/></svg></div><span class="badge purple">DECLINED</span></div><div class="stat-number"><?php echo $counts['rejected']; ?></div><div class="stat-label">Rejected</div></div>
    </div>

    <div class="facilities-layout">
      <section class="card facility-requests">
        <div class="facility-panel-head"><div><h2>Booking Requests</h2><p>Check the schedule before confirming a student request.</p></div><div class="facility-tabs"><?php foreach ($allowedStatuses as $status): ?><a class="<?php echo $selectedStatus === $status ? 'active' : ''; ?>" href="facilities.php?status=<?php echo urlencode($status); ?>"><?php echo ucfirst($status); ?><?php if ($status !== 'all'): ?><span><?php echo $counts[$status]; ?></span><?php endif; ?></a><?php endforeach; ?></div></div>
        <?php if (!$requests): ?><div class="facility-empty"><div class="empty-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M7 3v4M17 3v4M4 9h16M5 5h14a1 1 0 0 1 1 1v14H4V6a1 1 0 0 1 1-1Z"/></svg></div><strong>No <?php echo $selectedStatus === 'all' ? '' : $selectedStatus; ?> requests</strong><p>New student facility bookings will appear here.</p></div><?php else: ?>
          <div class="request-list">
          <?php foreach ($requests as $request): $studentName = trim(($request['FirstName'] ?? '') . ' ' . ($request['LastName'] ?? '')); if (!$studentName) $studentName = 'Student #' . $request['user_id']; ?>
            <article class="request-row"><div class="request-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"><rect x="3" y="6" width="18" height="14" rx="2"/><path d="M8 6l1.5-2.5h5L16 6"/><circle cx="12" cy="13" r="3.2"/></svg></div><div class="request-main"><div class="request-title-line"><h3><?php echo htmlspecialchars($request['facility_name']); ?></h3><span class="pill facility-pill <?php echo htmlspecialchars($request['status']); ?>"><?php echo strtoupper(htmlspecialchars($request['status'])); ?></span></div><p class="request-student"><?php echo htmlspecialchars($studentName); ?><?php if (!empty($request['StudentID'])): ?> <span>• ID <?php echo htmlspecialchars($request['StudentID']); ?></span><?php endif; ?></p><div class="request-meta"><span><?php echo date('M j, Y', strtotime($request['date'])); ?></span><span><?php echo date('g:i A', strtotime($request['time_start'])); ?> - <?php echo date('g:i A', strtotime($request['time_end'])); ?></span><span><?php echo htmlspecialchars($request['location']); ?></span></div><div class="availability <?php echo $request['approved_conflict'] ? 'is-taken' : ($request['pending_conflict'] ? 'is-pending' : 'is-available'); ?>"><span></span><?php echo $request['approved_conflict'] ? 'Date and time already taken' : ($request['pending_conflict'] ? 'Another request is awaiting review' : 'Date and time available'); ?></div></div><div class="request-actions"><?php if ($request['status'] === 'pending'): ?><form action="actions/update_status.php" method="POST"><input type="hidden" name="reservation_id" value="<?php echo (int) $request['id']; ?>"><input type="hidden" name="status" value="approved"><button class="action-button approve" type="submit">Approve</button></form><form action="actions/update_status.php" method="POST"><input type="hidden" name="reservation_id" value="<?php echo (int) $request['id']; ?>"><input type="hidden" name="status" value="rejected"><button class="action-button deny" type="submit">Deny</button></form><?php else: ?><span class="request-age"><?php echo facilityTimeAgo($request['created_at']); ?></span><?php endif; ?></div></article>
          <?php endforeach; ?></div>
        <?php endif; ?>
      </section>

      <aside class="facility-side">
        <section class="card suggestion-card"><div class="suggestion-heading"><div class="suggestion-icon"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 18h6M10 22h4M8.5 14.5A6 6 0 1 1 15.5 14c-.9.8-1.5 1.7-1.5 2.5h-4c0-.8-.6-1.7-1.5-2Z"/></svg></div><div><h2>Admin suggestions</h2><p>Small checks for faster decisions.</p></div></div><ul class="suggestion-list"><li><span class="suggestion-check">1</span><div><strong>Check for conflicts</strong><p>Compare the requested time with approved bookings for the same space.</p></div></li><li><span class="suggestion-check">2</span><div><strong>Confirm capacity</strong><p>Make sure the group size fits the facility before approving.</p></div></li><li><span class="suggestion-check">3</span><div><strong>Respond promptly</strong><p>Students can plan better when pending requests are reviewed daily.</p></div></li></ul></section>
        <section class="card facilities-guide"><div class="facility-panel-head"><div><h2>Available facilities</h2><p>Spaces students can reserve</p></div></div><?php foreach ($facilityCatalog as $facility): ?><div class="catalog-row"><span class="catalog-dot"></span><div><strong><?php echo htmlspecialchars($facility['name']); ?></strong><p><?php echo htmlspecialchars($facility['location']); ?> • Up to <?php echo (int) $facility['capacity']; ?> people</p></div></div><?php endforeach; ?></section>
      </aside>
    </div>
  </main>
</div>
<script>
  (function () {
    var savedTheme = localStorage.getItem('pcu-theme');
    if (savedTheme) document.documentElement.setAttribute('data-theme', savedTheme);
    var toggle = document.getElementById('themeToggle');
    if (toggle) toggle.addEventListener('click', function () { var next = document.documentElement.getAttribute('data-theme') === 'dark' ? 'light' : 'dark'; document.documentElement.setAttribute('data-theme', next); localStorage.setItem('pcu-theme', next); document.getElementById('themeIconSun').style.display = next === 'dark' ? 'none' : ''; document.getElementById('themeIconMoon').style.display = next === 'dark' ? '' : 'none'; });
    var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    document.addEventListener('click', function (event) {
      var link = event.target.closest('a[data-transition]');
      if (!link || event.ctrlKey || event.metaKey || event.shiftKey || event.button !== 0) return;
      var href = link.getAttribute('href');
      if (!href) return;
      event.preventDefault();
      if (reduceMotion) { window.location.href = href; return; }
      document.body.classList.add('page-leaving');
      setTimeout(function () { window.location.href = href; }, 360);
    });
    window.addEventListener('pageshow', function (event) {
      if (event.persisted) document.body.classList.remove('page-leaving');
    });
  })();
</script>
</body>
</html>

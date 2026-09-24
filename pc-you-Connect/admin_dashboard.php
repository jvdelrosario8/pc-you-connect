<?php
require_once 'auth.php';
require_once 'db.php';
require_admin();
$adminID = current_user_id();
$firstName = $_SESSION['first_name'] ?? 'Admin';
$lastName = $_SESSION['last_name'] ?? '';
$fullName = trim($firstName . ' ' . $lastName);

function dashboardTimeAgo($timestamp)
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

$announcementCount = 0;
$recentAnnouncements = [];
$announcementCountResult = $conn->query("SELECT COUNT(*) AS total FROM announcements");
if ($announcementCountResult) {
  $announcementCount = (int) $announcementCountResult->fetch_assoc()['total'];
}
$recentAnnouncementsResult = $conn->query(
  "SELECT category, title, content, created_at
   FROM announcements
   ORDER BY created_at DESC
   LIMIT 3"
);
if ($recentAnnouncementsResult) {
  while ($row = $recentAnnouncementsResult->fetch_assoc()) {
    $recentAnnouncements[] = $row;
  }
}

$lostFoundTotal = 0;
$lostFoundPending = 0;
$lostFoundStatsResult = $conn->query(
  "SELECT COUNT(*) AS total,
          COALESCE(SUM(status = 'unclaimed'), 0) AS pending
   FROM lost_found"
);
if ($lostFoundStatsResult) {
  $lostFoundStats = $lostFoundStatsResult->fetch_assoc();
  $lostFoundTotal = (int) $lostFoundStats['total'];
  $lostFoundPending = (int) $lostFoundStats['pending'];
}
$recentLostFound = [];
$recentLostFoundResult = $conn->query(
  "SELECT item_name, location, status, created_at
   FROM lost_found
   ORDER BY created_at DESC
   LIMIT 3"
);
if ($recentLostFoundResult) {
  while ($row = $recentLostFoundResult->fetch_assoc()) {
    $recentLostFound[] = $row;
  }
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PC-YOU! Connect — Admin Dashboard</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="admin_dashboard_styles.css">
</head>

<body>
  <div class="app">

    <aside class="sidebar">
      <div class="brand">
        <div class="brand-logo">
          <img src="assets/icon.jpg" alt="Justine Neil">
          <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8">
            <path d="M12 3 2 8l10 5 10-5-10-5Z" stroke-linejoin="round" />
            <path d="M6 10.5V16c0 1.5 2.7 3 6 3s6-1.5 6-3v-5.5" stroke-linejoin="round" />
          </svg>
        </div>
        <div>
          <div class="brand-name">PC-YOU! Connect</div>
          <div class="brand-sub">Campus Portal</div>
        </div>
      </div>

      <div class="nav-section-label">MAIN MENU</div>
      <ul class="nav-list">
        <li>
          <a href="admin_dashboard.php" class="nav-item active" data-transition>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <rect x="3" y="3" width="7" height="7" rx="1.5" />
              <rect x="14" y="3" width="7" height="7" rx="1.5" />
              <rect x="3" y="14" width="7" height="7" rx="1.5" />
              <rect x="14" y="14" width="7" height="7" rx="1.5" />
            </svg>
            Dashboard
          </a>
        </li>
        <li>
          <a href="facilities.php" class="nav-item" data-transition>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path d="M4 21V7l8-4 8 4v14" stroke-linejoin="round" />
              <path d="M9 21v-6h6v6" stroke-linejoin="round" />
            </svg>
            Facilities
          </a>
        </li>
        <li>
          <a href="lostandfound.php" class="nav-item" data-transition>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path
                d="M4 6h16M4 6l1.5 13a2 2 0 0 0 2 1.8h9a2 2 0 0 0 2-1.8L20 6M9 6V4.5A1.5 1.5 0 0 1 10.5 3h3A1.5 1.5 0 0 1 15 4.5V6" />
              <path d="M9.5 11.5h5" />
            </svg>
            Lost &amp; Found
          </a>
        </li>
        <li>
          <a href="announcements.php" class="nav-item" data-transition>
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path d="m3 11 18-5v12L3 14v-3z" stroke-linejoin="round" />
              <path d="M11.6 16.8a3 3 0 1 1-5.8-1.6" />
            </svg>
            Announcements
          </a>
        </li>
      </ul>
    
      <div class="nav-section-label">ACCOUNT</div>
      <ul class="nav-list">
        <li class="nav-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="12" cy="8" r="3.5" />
            <path d="M4.5 20c1.4-3.6 4.4-5.5 7.5-5.5s6.1 1.9 7.5 5.5" />
          </svg>
          Profile
        </li>
        <li class="nav-item">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
            <circle cx="12" cy="12" r="3" />
            <path
              d="M19.4 13.5c.1-.5.1-1 0-1.5l1.6-1.4-2-3.4-2 .6a7.9 7.9 0 0 0-1.3-.8l-.3-2.1H10l-.3 2.1c-.5.2-.9.5-1.3.8l-2-.6-2 3.4L6 12c-.1.5-.1 1 0 1.5l-1.6 1.4 2 3.4 2-.6c.4.3.8.6 1.3.8l.3 2.1h4l.3-2.1c.5-.2.9-.5 1.3-.8l2 .6 2-3.4-1.6-1.4Z" />
          </svg>
          Settings
        </li>
      </ul>

      <div class="sidebar-spacer"></div>

      <a href="actions/logout.php" class="signout" onclick="return confirm('Are you sure you want to log out?');">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
          <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4" />
          <path d="M16 17l5-5-5-5" />
          <path d="M21 12H9" />
        </svg>
        Sign Out
      </a>
    </aside>

    <main class="main">

      <div class="topbar">
        <div class="topbar-right">
          <button class="icon-btn" id="themeToggle" aria-label="Toggle dark mode" type="button">
            <svg id="themeIconSun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <circle cx="12" cy="12" r="4.5" />
              <path
                d="M12 2v2M12 20v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M2 12h2M20 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4" />
            </svg>
            <svg id="themeIconMoon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
              style="display:none;">
              <path d="M20 14.5A8.5 8.5 0 0 1 9.5 4a8.5 8.5 0 1 0 10.5 10.5Z" />
            </svg>
          </button>
          <div class="icon-btn">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
              <path d="M18 9a6 6 0 0 0-12 0c0 5-2 6-2 6h16s-2-1-2-6Z" />
              <path d="M10.5 19a1.6 1.6 0 0 0 3 0" />
            </svg>
          </div>
          <div class="user-chip">
            <div class="user-avatar"></div>
            <div>
              <div class="user-name"><?php echo htmlspecialchars($fullName); ?></div>
              <div class="user-role">User ID: <?php echo htmlspecialchars($adminID); ?></div>
            </div>
          </div>
        </div>
      </div>

      <div class="page-heading">
        <h1>Good morning, <?php echo htmlspecialchars($firstName); ?>!</h1>
        <p>Here's what's happening on campus today.</p>
      </div>

      <div class="stat-grid">
        <div class="card stat-card">
          <div class="stat-top">
            <div class="stat-icon blue">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <rect x="4" y="3" width="16" height="18" rx="1.5" />
                <path d="M9 8h1M14 8h1M9 12h1M14 12h1M9 16h1M14 16h1" />
              </svg>
            </div>
            <span class="badge green">+100%</span>
          </div>
          <div class="stat-number">5</div>
          <div class="stat-label">Active Facilities</div>
        </div>

        <div class="card stat-card">
          <div class="stat-top">
            <div class="stat-icon yellow">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path
                  d="M4 6h16M4 6l1.5 13a2 2 0 0 0 2 1.8h9a2 2 0 0 0 2-1.8L20 6M9 6V4.5A1.5 1.5 0 0 1 10.5 3h3A1.5 1.5 0 0 1 15 4.5V6" />
                <circle cx="12" cy="12.5" r="2.3" />
              </svg>
            </div>
            <span class="badge yellow"><?php echo $lostFoundPending; ?> pending</span>
          </div>
          <div class="stat-number"><?php echo $lostFoundTotal; ?></div>
          <div class="stat-label">Lost &amp; Found Items</div>
        </div>

        <div class="card stat-card">
          <div class="stat-top">
            <div class="stat-icon purple">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="m3 11 18-5v12L3 14v-3z" stroke-linejoin="round" />
                <path d="M11.6 16.8a3 3 0 1 1-5.8-1.6" />
              </svg>
            </div>
            <span class="badge purple">LIVE</span>
          </div>
          <div class="stat-number"><?php echo $announcementCount; ?></div>
          <div class="stat-label">Announcements</div>
        </div>
      </div>

      <div class="content-grid">
        <div class="card panel">
          <div class="panel-head">
            <h2>Recent Announcements</h2>
            <span class="view-all">View All
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M5 12h14M13 6l6 6-6 6" />
              </svg>
            </span>
          </div>
          <div class="announce-list">
            <?php foreach ($recentAnnouncements as $announcement): ?>
            <a href="announcements.php" class="announce-item" data-transition>
              <div class="announce-body">
                <div class="announce-meta">
                  <span class="tag <?php echo htmlspecialchars($announcement['category'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(strtoupper($announcement['category']), ENT_QUOTES, 'UTF-8'); ?></span>
                  <span class="time-ago"><?php echo dashboardTimeAgo($announcement['created_at']); ?></span>
                </div>
                <h3 class="announce-title"><?php echo htmlspecialchars($announcement['title'], ENT_QUOTES, 'UTF-8'); ?></h3>
                <p class="announce-desc"><?php echo htmlspecialchars($announcement['content'], ENT_QUOTES, 'UTF-8'); ?></p>
              </div>
            </a>
            <?php endforeach; ?>
            <?php if (!$recentAnnouncements): ?>
            <div class="empty-state">No announcements yet.</div>
            <?php endif; ?>
          </div>
        </div>

        <div>
          <div class="panel-head" style="margin-bottom:14px;">
            <h2>Quick Actions</h2>
          </div>
          <div class="quick-actions">
            <a href="facilities.php" class="qa-btn" data-transition>
              <div class="qa-icon navy">
                <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8">
                  <rect x="3" y="4" width="18" height="17" rx="2" />
                  <path d="M3 9h18M8 2v4M16 2v4" />
                </svg>
              </div>
              <div class="qa-label">Book Facility</div>
            </a>
            <a href="lostandfound.php" class="qa-btn" data-transition>
              <div class="qa-icon amber">
                <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8">
                  <rect x="3" y="8" width="18" height="13" rx="1.5" />
                  <path d="M3 8l2-4h14l2 4" />
                  <path d="M9 12a3 3 0 0 0 6 0" />
                </svg>
              </div>
              <div class="qa-label">Report Item</div>
            </a>
            <div class="qa-btn full">
              <div class="qa-icon violet">
                <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8">
                  <path d="M18 9a6 6 0 0 0-12 0c0 5-2 6-2 6h16s-2-1-2-6Z" />
                  <path d="M10.5 19a1.6 1.6 0 0 0 3 0" />
                </svg>
              </div>
              <div class="qa-label">All Updates</div>
            </div>
          </div>

          <div class="info-banner">
            <div class="info-banner-head">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                <circle cx="12" cy="12" r="9" />
                <path d="M12 11v5.5M12 8v.01" />
              </svg>
              PCU INFO
            </div>
            <p>Semester ends on Dec 15, 2026.</p>
          </div>
        </div>
      </div>

      <div class="bottom-grid">
        <div class="card panel">
          <div class="panel-head">
            <h2>Recent Lost &amp; Found</h2>
            <a href="lostandfound.php" class="view-all" data-transition>View All
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M5 12h14M13 6l6 6-6 6" />
              </svg>
            </a>
          </div>

          <?php foreach ($recentLostFound as $lostFound): ?>
          <div class="list-row">
            <div class="row-icon blue">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                <path d="M4 6h16M4 6l1.5 13a2 2 0 0 0 2 1.8h9a2 2 0 0 0 2-1.8L20 6M9 6V4.5A1.5 1.5 0 0 1 10.5 3h3A1.5 1.5 0 0 1 15 4.5V6" />
                <path d="M9.5 11.5h5" />
              </svg>
            </div>
            <div class="row-body">
              <div class="row-title"><?php echo htmlspecialchars($lostFound['item_name'], ENT_QUOTES, 'UTF-8'); ?></div>
              <div class="row-sub"><?php echo htmlspecialchars($lostFound['location'] ?: 'Location not provided', ENT_QUOTES, 'UTF-8'); ?> &bull; <?php echo dashboardTimeAgo($lostFound['created_at']); ?></div>
            </div>
            <span class="pill <?php echo $lostFound['status'] === 'turned in' ? 'completed' : htmlspecialchars($lostFound['status'], ENT_QUOTES, 'UTF-8'); ?>"><?php echo htmlspecialchars(strtoupper($lostFound['status']), ENT_QUOTES, 'UTF-8'); ?></span>
          </div>
          <?php endforeach; ?>
          <?php if (!$recentLostFound): ?>
          <div class="empty-state">No Lost &amp; Found items yet.</div>
          <?php endif; ?>
        </div>

        <div class="card panel">
          <div class="panel-head">
            <h2>Facilities Appointment</h2>
            <a href="facilities.php" class="view-all" data-transition>View All
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                <path d="M5 12h14M13 6l6 6-6 6" />
              </svg>
            </a>
          </div>

          <div class="list-row">
            <div class="row-thumb">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                <rect x="3" y="6" width="18" height="14" rx="2" />
                <path d="M8 6l1.5-2.5h5L16 6" />
                <circle cx="12" cy="13" r="3.2" />
              </svg>
            </div>
            <div class="row-body">
              <div class="row-title">7th Floor Conference room</div>
              <div class="row-sub">Sept 4, 2026 • 9:00 AM – 12:00 PM</div>
            </div>
            <span class="pill upcoming">UPCOMING</span>
          </div>

          <div class="list-row">
            <div class="row-thumb">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                <rect x="3" y="6" width="18" height="14" rx="2" />
                <path d="M8 6l1.5-2.5h5L16 6" />
                <circle cx="12" cy="13" r="3.2" />
              </svg>
            </div>
            <div class="row-body">
              <div class="row-title">Gymnasium</div>
              <div class="row-sub">Sept 9, 2026 • 1:00 PM – 5:00 PM</div>
            </div>
            <span class="pill upcoming">UPCOMING</span>
          </div>

          <div class="list-row">
            <div class="row-thumb">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6">
                <rect x="3" y="6" width="18" height="14" rx="2" />
                <path d="M8 6l1.5-2.5h5L16 6" />
                <circle cx="12" cy="13" r="3.2" />
              </svg>
            </div>
            <div class="row-body">
              <div class="row-title">Audio Visual Room</div>
              <div class="row-sub">Aug 20, 2026 • 10:00 AM – 12:00 PM</div>
            </div>
            <span class="pill completed">COMPLETED</span>
          </div>
        </div>
      </div>

    </main>
  </div>

  <script>
    (function () {
      var root = document.documentElement;
      var toggle = document.getElementById('themeToggle');
      var sunIcon = document.getElementById('themeIconSun');
      var moonIcon = document.getElementById('themeIconMoon');

      function applyTheme(theme) {
        root.setAttribute('data-theme', theme);
        if (theme === 'dark') {
          sunIcon.style.display = 'none';
          moonIcon.style.display = 'block';
        } else {
          sunIcon.style.display = 'block';
          moonIcon.style.display = 'none';
        }
        localStorage.setItem('pcu-theme', theme);
      }

      var saved = localStorage.getItem('pcu-theme');
      var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
      applyTheme(saved || (prefersDark ? 'dark' : 'light'));

      toggle.addEventListener('click', function () {
        var current = root.getAttribute('data-theme');
        applyTheme(current === 'dark' ? 'light' : 'dark');
      });
    })();

    /* ---------- PAGE TRANSITION ----------
       Fades the page out before going to Facilities / Lost & Found.
       The fade-in on arrival is handled in admin_dashboard_styles.css. */
    (function () {
      var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;

      document.addEventListener('click', function (e) {
        var link = e.target.closest('a[data-transition]');
        if (!link) return;
        if (e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0) return;
        var href = link.getAttribute('href');
        if (!href) return;
        e.preventDefault();
        if (reduceMotion) { window.location.href = href; return; }
        document.body.classList.add('page-leaving');
        setTimeout(function () { window.location.href = href; }, 360);
      });

      // When coming back with the browser's Back button, show the page again.
      window.addEventListener('pageshow', function (e) {
        if (e.persisted) document.body.classList.remove('page-leaving');
      });
    })();
  </script>
</body>

</html>
<?php
require_once 'auth.php';
require_once 'db.php';
require_admin();
$adminID = current_user_id();
$firstName = $_SESSION['first_name'] ?? 'Admin';
$lastName = $_SESSION['last_name'] ?? '';
$fullName = trim($firstName . ' ' . $lastName);

$lostItems = [];
$result = $conn->query(
  "SELECT id, type, item_name, category, description, location, status, created_at
   FROM lost_found
   ORDER BY created_at DESC"
);

if ($result) {
  while ($row = $result->fetch_assoc()) {
    $lostItems[] = [
      'id' => (int) $row['id'],
      'name' => $row['item_name'],
      'category' => $row['category'],
      'location' => $row['location'] ?? '',
      'reportedAt' => strtotime($row['created_at']) * 1000,
      'status' => $row['status'],
      'description' => $row['description'] ?? '',
      'photo' => null,
    ];
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PC-YOU! Connect — Lost &amp; Found</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<!-- Shared layout (sidebar, topbar, cards, colors, dark mode, page transition) -->
<link rel="stylesheet" href="admin_dashboard_styles.css">
<!-- Lost & Found page styles -->
<link rel="stylesheet" href="lostandfound.css">
</head>
<body>
<div class="app">

  <aside class="sidebar">
    <div class="brand">
      <div class="brand-logo">
        <img src="assets/icon.jpg" alt="PC-YOU! Connect logo">
      </div>
      <div>
        <div class="brand-name">PC-YOU! Connect</div>
        <div class="brand-sub">Campus Portal</div>
      </div>
    </div>

    <div class="nav-section-label">MAIN MENU</div>
    <ul class="nav-list">
      <li>
        <a href="admin_dashboard.php" class="nav-item" data-transition>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><rect x="3" y="3" width="7" height="7" rx="1.5"/><rect x="14" y="3" width="7" height="7" rx="1.5"/><rect x="3" y="14" width="7" height="7" rx="1.5"/><rect x="14" y="14" width="7" height="7" rx="1.5"/></svg>
          Dashboard
        </a>
      </li>
      <li>
        <a href="facilities.php" class="nav-item" data-transition>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 21V7l8-4 8 4v14" stroke-linejoin="round"/><path d="M9 21v-6h6v6" stroke-linejoin="round"/></svg>
          Facilities
        </a>
      </li>
      <li>
        <a href="lostandfound.php" class="nav-item active" data-transition>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M4 6l1.5 13a2 2 0 0 0 2 1.8h9a2 2 0 0 0 2-1.8L20 6M9 6V4.5A1.5 1.5 0 0 1 10.5 3h3A1.5 1.5 0 0 1 15 4.5V6"/><path d="M9.5 11.5h5"/></svg>
          Lost &amp; Found
        </a>
      </li>
      <li class="nav-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m3 11 18-5v12L3 14v-3z" stroke-linejoin="round"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>
        Announcements
      </li>
    </ul>

    <div class="nav-section-label">ACCOUNT</div>
    <ul class="nav-list">
      <li class="nav-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="8" r="3.5"/><path d="M4.5 20c1.4-3.6 4.4-5.5 7.5-5.5s6.1 1.9 7.5 5.5"/></svg>
        Profile
      </li>
      <li class="nav-item">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="3"/><path d="M19.4 13.5c.1-.5.1-1 0-1.5l1.6-1.4-2-3.4-2 .6a7.9 7.9 0 0 0-1.3-.8l-.3-2.1H10l-.3 2.1c-.5.2-.9.5-1.3.8l-2-.6-2 3.4L6 12c-.1.5-.1 1 0 1.5l-1.6 1.4 2 3.4 2-.6c.4.3.8.6 1.3.8l.3 2.1h4l.3-2.1c.5-.2.9-.5 1.3-.8l2 .6 2-3.4-1.6-1.4Z"/></svg>
        Settings
      </li>
    </ul>

    <div class="sidebar-spacer"></div>

    <a href="actions/logout.php" class="signout" onclick="return confirm('Are you sure you want to log out?');">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><path d="M16 17l5-5-5-5"/><path d="M21 12H9"/></svg>
      Sign Out
    </a>
  </aside>

  <main class="main">

    <div class="topbar">
      <div class="search">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
        Search anything...
      </div>
      <div class="topbar-right">
        <button class="icon-btn" id="themeToggle" aria-label="Toggle dark mode" type="button">
          <svg id="themeIconSun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="4.5"/><path d="M12 2v2M12 20v2M4.2 4.2l1.4 1.4M18.4 18.4l1.4 1.4M2 12h2M20 12h2M4.2 19.8l1.4-1.4M18.4 5.6l1.4-1.4"/></svg>
          <svg id="themeIconMoon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="display:none;"><path d="M20 14.5A8.5 8.5 0 0 1 9.5 4a8.5 8.5 0 1 0 10.5 10.5Z"/></svg>
        </button>
        <div class="icon-btn">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M18 9a6 6 0 0 0-12 0c0 5-2 6-2 6h16s-2-1-2-6Z"/><path d="M10.5 19a1.6 1.6 0 0 0 3 0"/></svg>
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

    <div class="lf-heading">
      <div class="page-heading">
        <h1>Lost &amp; Found</h1>
        <p>Manage and track lost and found items on campus.</p>
      </div>
      <button type="button" class="lf-btn lf-btn-primary" id="addItemBtn">+ Add Item</button>
    </div>

    <!-- Stat cards (numbers filled in by lostandfound.js) -->
    <div class="stat-grid lf-stat-grid">
      <div class="card stat-card">
        <div class="stat-top">
          <div class="stat-icon blue">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
          </div>
        </div>
        <div class="stat-number" id="statTotal">0</div>
        <div class="stat-label">Total Items</div>
      </div>
      <div class="card stat-card">
        <div class="stat-top">
          <div class="stat-icon yellow">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M10.3 3.9 1.8 18a2 2 0 0 0 1.7 3h17a2 2 0 0 0 1.7-3L13.7 3.9a2 2 0 0 0-3.4 0Z"/><path d="M12 9v4M12 17h.01"/></svg>
          </div>
          <span class="badge yellow">UNCLAIMED</span>
        </div>
        <div class="stat-number" id="statUnclaimed">0</div>
        <div class="stat-label">Unclaimed</div>
      </div>
      <div class="card stat-card">
        <div class="stat-top">
          <div class="stat-icon lf-green">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9"/><path d="m8 12 3 3 5-6"/></svg>
          </div>
          <span class="badge green">CLAIMED</span>
        </div>
        <div class="stat-number" id="statClaimed">0</div>
        <div class="stat-label">Claimed</div>
      </div>
      <div class="card stat-card">
        <div class="stat-top">
          <div class="stat-icon purple">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M21 8v13H3V8M1 3h22v5H1zM10 12h4"/></svg>
          </div>
          <span class="badge purple">TURNED IN</span>
        </div>
        <div class="stat-number" id="statTurnedIn">0</div>
        <div class="stat-label">Turned In</div>
      </div>
    </div>

    <!-- Items table -->
    <div class="card lf-table-card">
      <div class="lf-table-head">
        <h2>All Items</h2>
        <div class="lf-tabs" id="statusTabs">
          <button type="button" class="on" data-filter="all">All</button>
          <button type="button" data-filter="unclaimed">Unclaimed</button>
          <button type="button" data-filter="claimed">Claimed</button>
          <button type="button" data-filter="turned in">Turned In</button>
        </div>
        <div class="lf-search">
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path d="M21 21l-4.3-4.3"/></svg>
          <input type="text" id="itemSearch" placeholder="Search items...">
        </div>
      </div>
      <div class="lf-table-wrap">
        <table class="lf-table">
          <thead>
            <tr>
              <th>#</th>
              <th>Item</th>
              <th>Category</th>
              <th>Location Found</th>
              <th>Date Reported</th>
              <th>Status</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody id="itemRows"></tbody>
        </table>
        <div class="lf-empty" id="emptyState" hidden>
          <b>No items match</b>
          Try another filter or search term, or add a new item.
        </div>
      </div>
      <div class="lf-table-foot">
        <span id="showingText">Showing 0 of 0 items</span>
        <div class="lf-pager" id="pager"></div>
      </div>
    </div>

  </main>
</div>

<!-- Add / Edit item modal -->
<div class="lf-overlay" id="itemModal" hidden>
  <div class="lf-modal" role="dialog" aria-modal="true" aria-labelledby="itemModalTitle">
    <div class="lf-modal-head">
      <h3 id="itemModalTitle">Report New Item</h3>
      <button type="button" class="lf-x" data-close aria-label="Close">&times;</button>
    </div>
    <form id="itemForm" action="actions/submit_lost_found.php" method="POST" novalidate>
      <input type="hidden" name="type" value="found">
      <div class="lf-field">
        <label for="fName">Item Name</label>
        <input class="lf-input" id="fName" name="item_name" type="text" placeholder="eg. Silver watch">
      </div>
      <div class="lf-field">
        <label for="fCategory">Category</label>
        <select class="lf-input" id="fCategory" name="category">
          <option>Accessories</option>
          <option>Electronics</option>
          <option>ID / Cards</option>
          <option>Bags</option>
          <option>Keys</option>
          <option>School Supplies</option>
          <option>Others</option>
        </select>
      </div>
      <div class="lf-field">
        <label for="fLocation">Location Found</label>
        <input class="lf-input" id="fLocation" name="location" type="text" placeholder="eg. NAB 311">
      </div>
      <div class="lf-field" id="statusField" hidden>
        <label for="fStatus">Status</label>
        <select class="lf-input" id="fStatus">
          <option value="unclaimed">Unclaimed</option>
          <option value="claimed">Claimed</option>
          <option value="turned in">Turned In</option>
        </select>
      </div>
      <div class="lf-field">
        <label>Add photo</label>
        <label class="lf-upload" id="uploadBox">
          <span class="lf-upload-empty" id="uploadEmpty">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6"><path d="M14.5 4h-5L7 7H4a2 2 0 0 0-2 2v9a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2V9a2 2 0 0 0-2-2h-3l-2.5-3Z"/><circle cx="12" cy="13" r="3.5"/></svg>
            <b>Upload Item Photo</b>
            <small>Add a photo of the item to help the owner identify it</small>
          </span>
          <img id="uploadPreview" alt="" hidden>
          <input type="file" id="fPhoto" accept="image/*" hidden>
        </label>
        <button type="button" class="lf-remove-photo" id="removePhoto" hidden>Remove photo</button>
      </div>
      <div class="lf-field">
        <label for="fDescription">Description</label>
        <textarea class="lf-input" id="fDescription" name="description" placeholder="Describe the item..."></textarea>
      </div>
      <div class="lf-modal-actions">
        <button type="button" class="lf-btn lf-btn-ghost" data-close>Cancel</button>
        <button type="submit" class="lf-btn lf-btn-primary" id="itemSubmit">Submit</button>
      </div>
    </form>
  </div>
</div>

<!-- View item modal -->
<div class="lf-overlay" id="viewModal" hidden>
  <div class="lf-modal lf-modal-sm" role="dialog" aria-modal="true" aria-labelledby="viewTitle">
    <div class="lf-modal-head">
      <h3 id="viewTitle">Item</h3>
      <button type="button" class="lf-x" data-close aria-label="Close">&times;</button>
    </div>
    <div id="viewBody"></div>
    <div class="lf-modal-actions" id="viewActions"></div>
  </div>
</div>

<!-- Delete confirmation modal -->
<div class="lf-overlay" id="deleteModal" hidden>
  <div class="lf-modal lf-modal-sm" role="dialog" aria-modal="true" aria-labelledby="deleteTitle">
    <div class="lf-modal-head">
      <h3 id="deleteTitle">Delete item?</h3>
      <button type="button" class="lf-x" data-close aria-label="Close">&times;</button>
    </div>
    <p class="lf-delete-text" id="deleteText"></p>
    <div class="lf-modal-actions">
      <button type="button" class="lf-btn lf-btn-ghost" data-close>Cancel</button>
      <button type="button" class="lf-btn lf-btn-danger" id="confirmDelete">Delete</button>
    </div>
  </div>
</div>

<div class="lf-toast" id="toast" hidden></div>

<script>
  window.initialLostFoundItems = <?php echo json_encode($lostItems, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT); ?>;
</script>
<script src="lostandfound.js"></script>
</body>
</html>

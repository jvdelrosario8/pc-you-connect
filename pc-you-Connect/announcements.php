<?php
require_once 'auth.php';
require_once 'db.php';
require_admin();
$adminID   = current_user_id();
$firstName = $_SESSION['first_name'] ?? 'Admin';
$lastName  = $_SESSION['last_name']  ?? '';
$fullName  = trim($firstName . ' ' . $lastName);

$announcements = [];
$result = $conn->query(
    "SELECT id, category, title, content, created_at
     FROM announcements
     ORDER BY created_at DESC"
);

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $row['tag'] = $row['category'];
        $row['label'] = strtoupper($row['category']);
        $row['time'] = date('M j, Y g:i A', strtotime($row['created_at']));
        $announcements[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Announcements — PC-YOU! Connect</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="admin_dashboard_styles.css">
  <style>
    /* ── Tag colour: general ── */
    .tag.general {
      background: var(--blue-bg);
      color: var(--blue-icon);
    }

    /* ── Pill for announcements ── */
    .pill.published  { background: var(--badge-green-bg);  color: var(--badge-green-text); }
    .pill.draft      { background: var(--badge-yellow-bg); color: var(--badge-yellow-text); }

    /* ── Page header row ── */
    .page-header-row {
      display: flex;
      align-items: center;
      justify-content: space-between;
      margin-bottom: 26px;
      flex-wrap: wrap;
      gap: 16px;
    }

    /* ── "Post Announcement" button ── */
    .btn-post {
      display: inline-flex;
      align-items: center;
      gap: 8px;
      background: var(--navy);
      color: #fff;
      border: none;
      border-radius: 10px;
      padding: 11px 20px;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      font-family: inherit;
      transition: opacity .15s ease, transform .12s ease;
      white-space: nowrap;
    }
    .btn-post:hover { opacity: .88; transform: translateY(-1px); }
    .btn-post svg   { width: 17px; height: 17px; flex-shrink: 0; }

    /* ── Filter bar ── */
    .filter-bar {
      display: flex;
      align-items: center;
      gap: 8px;
      margin-bottom: 20px;
      flex-wrap: wrap;
    }
    .filter-btn {
      padding: 7px 15px;
      border-radius: 8px;
      border: 1px solid var(--border);
      background: var(--card);
      color: var(--muted);
      font-size: 13px;
      font-weight: 600;
      cursor: pointer;
      font-family: inherit;
      transition: background .15s ease, color .15s ease, border-color .15s ease;
    }
    .filter-btn:hover { background: var(--nav-hover); }
    .filter-btn.active {
      background: var(--navy);
      color: #fff;
      border-color: transparent;
    }

    /* ── Announcements table card ── */
    .announce-full-list {
      display: flex;
      flex-direction: column;
    }
    .announce-full-item {
      display: flex;
      align-items: flex-start;
      gap: 18px;
      padding: 20px 0;
      border-bottom: 1px solid var(--border);
    }
    .announce-full-item:last-child { border-bottom: none; padding-bottom: 6px; }

    /* body */
    .af-body { flex: 1; min-width: 0; }
    .af-meta  {
      display: flex;
      align-items: center;
      gap: 10px;
      margin-bottom: 7px;
      flex-wrap: wrap;
    }
    .af-title {
      font-size: 15px;
      font-weight: 700;
      color: var(--text);
      margin: 0 0 5px;
      line-height: 1.4;
    }
    .af-desc {
      font-size: 13.5px;
      color: var(--muted);
      margin: 0;
      line-height: 1.55;
      display: -webkit-box;
      -webkit-line-clamp: 2;
      -webkit-box-orient: vertical;
      overflow: hidden;
    }

    /* action buttons */
    .af-actions {
      display: flex;
      align-items: center;
      gap: 8px;
      flex-shrink: 0;
      margin-top: 2px;
    }
    .act-btn {
      width: 34px;
      height: 34px;
      border-radius: 8px;
      border: 1px solid var(--border);
      background: var(--card);
      display: flex;
      align-items: center;
      justify-content: center;
      cursor: pointer;
      color: var(--muted);
      transition: background .15s ease, color .15s ease;
    }
    .act-btn svg { width: 15px; height: 15px; }
    .act-btn:hover { background: var(--nav-hover); color: var(--text); }
    .act-btn.delete:hover { background: #fde7e7; color: #d6403f; border-color: #fbb; }

    /* ── Empty state ── */
    .empty-state {
      text-align: center;
      padding: 60px 20px;
      color: var(--muted);
    }
    .empty-state svg  { width: 48px; height: 48px; margin-bottom: 14px; opacity: .45; }
    .empty-state h3   { font-size: 17px; font-weight: 700; color: var(--text); margin: 0 0 8px; }
    .empty-state p    { font-size: 14px; margin: 0; }

    /* ── Modal backdrop ── */
    .modal-backdrop {
      position: fixed;
      inset: 0;
      z-index: 200;
      display: none;
      align-items: center;
      justify-content: center;
      padding: 20px;
      background: rgba(0,0,0,.45);
      backdrop-filter: blur(2px);
    }
    .modal-backdrop.open { display: flex; }

    /* ── Post modal ── */
    .post-modal {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 20px;
      width: min(580px, 96vw);
      box-shadow: 0 24px 60px rgba(0,0,0,.22);
      overflow: hidden;
      animation: modalIn .2s ease both;
    }
    @keyframes modalIn {
      from { opacity: 0; transform: translateY(16px) scale(.97); }
      to   { opacity: 1; transform: none; }
    }
    .modal-header {
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 22px 26px 18px;
      border-bottom: 1px solid var(--border);
    }
    .modal-header h2 { font-size: 18px; font-weight: 700; margin: 0; color: var(--text); }
    .modal-close {
      width: 34px;
      height: 34px;
      border-radius: 50%;
      border: none;
      background: transparent;
      cursor: pointer;
      color: var(--muted);
      font-size: 22px;
      line-height: 1;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: inherit;
      transition: background .15s;
    }
    .modal-close:hover { background: var(--nav-hover); color: var(--text); }

    .modal-body { padding: 24px 26px; }

    .form-group { margin-bottom: 18px; }
    .form-label {
      display: block;
      font-size: 13.5px;
      font-weight: 600;
      color: var(--text);
      margin-bottom: 7px;
    }
    .form-label span { color: #d6403f; margin-left: 2px; }
    .form-input, .form-select, .form-textarea {
      width: 100%;
      border: 1px solid var(--border);
      border-radius: 10px;
      padding: 11px 14px;
      font-size: 14px;
      font-family: inherit;
      color: var(--text);
      background: var(--bg);
      outline: none;
      transition: border-color .15s ease;
      box-sizing: border-box;
    }
    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus { border-color: var(--navy); }
    .form-textarea {
      resize: vertical;
      min-height: 110px;
      line-height: 1.55;
    }
    .form-select { appearance: none; background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='8' viewBox='0 0 12 8'%3E%3Cpath d='M1 1l5 5 5-5' stroke='%236B7280' stroke-width='1.6' fill='none' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E"); background-repeat: no-repeat; background-position: right 14px center; padding-right: 38px; }
    .char-count { font-size: 12px; color: var(--muted-2); text-align: right; margin-top: 5px; }

    .modal-footer {
      display: flex;
      justify-content: flex-end;
      gap: 10px;
      padding: 18px 26px 22px;
      border-top: 1px solid var(--border);
    }
    .btn-cancel {
      padding: 10px 20px;
      border-radius: 10px;
      border: 1px solid var(--border);
      background: var(--card);
      color: var(--text);
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      font-family: inherit;
      transition: background .15s;
    }
    .btn-cancel:hover { background: var(--nav-hover); }
    .btn-submit {
      padding: 10px 22px;
      border-radius: 10px;
      border: none;
      background: var(--navy);
      color: #fff;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      font-family: inherit;
      transition: opacity .15s, transform .12s;
    }
    .btn-submit:hover { opacity: .88; transform: translateY(-1px); }

    /* ── Delete confirm modal ── */
    .confirm-modal {
      background: var(--card);
      border: 1px solid var(--border);
      border-radius: 18px;
      width: min(400px, 94vw);
      box-shadow: 0 24px 60px rgba(0,0,0,.22);
      padding: 32px 28px 26px;
      text-align: center;
      animation: modalIn .2s ease both;
    }
    .confirm-icon {
      width: 54px;
      height: 54px;
      border-radius: 50%;
      background: #fde7e7;
      color: #d6403f;
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto 16px;
    }
    .confirm-icon svg { width: 24px; height: 24px; }
    .confirm-modal h3 { font-size: 18px; font-weight: 700; color: var(--text); margin: 0 0 8px; }
    .confirm-modal p  { font-size: 14px; color: var(--muted); margin: 0 0 24px; line-height: 1.5; }
    .confirm-btns { display: flex; gap: 10px; justify-content: center; }
    .btn-del-confirm {
      padding: 10px 22px;
      border-radius: 10px;
      border: none;
      background: #d6403f;
      color: #fff;
      font-size: 14px;
      font-weight: 600;
      cursor: pointer;
      font-family: inherit;
      transition: opacity .15s;
    }
    .btn-del-confirm:hover { opacity: .88; }

    /* ── Toast notification ── */
    .toast {
      position: fixed;
      bottom: 30px;
      right: 30px;
      background: #111827;
      color: #fff;
      padding: 14px 20px;
      border-radius: 12px;
      font-size: 14px;
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 10px;
      box-shadow: 0 8px 30px rgba(0,0,0,.22);
      z-index: 9999;
      opacity: 0;
      transform: translateY(10px);
      transition: opacity .25s ease, transform .25s ease;
      pointer-events: none;
    }
    .toast.show { opacity: 1; transform: none; }
    .toast svg   { width: 18px; height: 18px; flex-shrink: 0; }
    .toast.success { background: #1a3a25; color: #5fd98b; }
    .toast.error   { background: #3a1a1a; color: #f08585; }
  </style>
</head>
<body>
<div class="app">

  <!-- ═══════════════════ SIDEBAR ═══════════════════ -->
  <aside class="sidebar">
    <div class="brand">
      <div class="brand-logo">
        <img src="assets/icon.jpg" alt="PC-YOU Connect">
        <svg viewBox="0 0 24 24" fill="none" stroke="#fff" stroke-width="1.8"><path d="M12 3 2 8l10 5 10-5-10-5Z" stroke-linejoin="round"/><path d="M6 10.5V16c0 1.5 2.7 3 6 3s6-1.5 6-3v-5.5" stroke-linejoin="round"/></svg>
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
        <a href="lostandfound.php" class="nav-item" data-transition>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M4 6h16M4 6l1.5 13a2 2 0 0 0 2 1.8h9a2 2 0 0 0 2-1.8L20 6M9 6V4.5A1.5 1.5 0 0 1 10.5 3h3A1.5 1.5 0 0 1 15 4.5V6"/><path d="M9.5 11.5h5"/></svg>
          Lost &amp; Found
        </a>
      </li>
      <li>
        <a href="announcements.php" class="nav-item active" data-transition>
          <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="m3 11 18-5v12L3 14v-3z" stroke-linejoin="round"/><path d="M11.6 16.8a3 3 0 1 1-5.8-1.6"/></svg>
          Announcements
        </a>
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

  <!-- ═══════════════════ MAIN ═══════════════════ -->
  <main class="main">

    <!-- Top bar -->
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

    <!-- Page header -->
    <div class="page-header-row">
      <div class="page-heading" style="margin-bottom:0;">
        <h1>Announcements</h1>
        <p>Manage and publish campus-wide announcements.</p>
      </div>
      <button class="btn-post" id="openPostModal">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 5v14M5 12h14"/></svg>
        Post Announcement
      </button>
    </div>

    <!-- Filter bar -->
    <div class="filter-bar">
      <button class="filter-btn active" data-filter="all">All <span id="countAll">(<?php echo count($announcements); ?>)</span></button>
      <button class="filter-btn" data-filter="important">Important</button>
      <button class="filter-btn" data-filter="event">Event</button>
      <button class="filter-btn" data-filter="maintenance">Maintenance</button>
      <button class="filter-btn" data-filter="general">General</button>
    </div>

    <!-- Announcements list card -->
    <div class="card panel">
      <div class="announce-full-list" id="announceList">

        <?php foreach ($announcements as $a): ?>
        <div class="announce-full-item" data-tag="<?php echo $a['tag']; ?>">

          <!-- body -->
          <div class="af-body">
            <div class="af-meta">
              <span class="tag <?php echo $a['tag']; ?>"><?php echo $a['label']; ?></span>
              <span class="time-ago"><?php echo $a['time']; ?></span>
            </div>
            <h3 class="af-title"><?php echo htmlspecialchars($a['title']); ?></h3>
            <p class="af-desc"><?php echo htmlspecialchars($a['content']); ?></p>
          </div>

          <!-- actions -->
          <div class="af-actions">
            <button class="act-btn" title="Edit" onclick="openEdit(<?php echo $a['id']; ?>, '<?php echo addslashes($a['tag']); ?>', '<?php echo addslashes($a['title']); ?>', '<?php echo addslashes($a['content']); ?>')">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5Z"/></svg>
            </button>
            <button class="act-btn delete" title="Delete" onclick="openDelete(<?php echo $a['id']; ?>, '<?php echo addslashes($a['title']); ?>')">
              <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/><path d="M10 11v6M14 11v6"/></svg>
            </button>
          </div>

        </div>
        <?php endforeach; ?>

      </div>
    </div>

  </main>
</div>

<!-- ═══════════ POST / EDIT MODAL ═══════════ -->
<div class="modal-backdrop" id="postModal">
  <div class="post-modal">
    <div class="modal-header">
      <h2 id="modalTitle">Post Announcement</h2>
      <button class="modal-close" id="closePostModal">&#x2715;</button>
    </div>
    <div class="modal-body">
      <form id="postForm" action="actions/post_announcement.php" method="POST">
        <input type="hidden" name="announcement_id" id="fieldID" value="">

        <div class="form-group">
          <label class="form-label" for="fieldCategory">Category <span>*</span></label>
          <select class="form-select" name="category" id="fieldCategory" required>
            <option value="">Select a category</option>
            <option value="important">Important</option>
            <option value="event">Event</option>
            <option value="maintenance">Maintenance</option>
            <option value="general">General</option>
          </select>
        </div>

        <div class="form-group">
          <label class="form-label" for="fieldTitle">Title <span>*</span></label>
          <input class="form-input" type="text" name="title" id="fieldTitle"
                 placeholder="e.g. Prelim Exam Schedule Released" maxlength="150" required>
          <div class="char-count"><span id="titleCount">0</span> / 150</div>
        </div>

        <div class="form-group">
          <label class="form-label" for="fieldContent">Content <span>*</span></label>
          <textarea class="form-textarea" name="content" id="fieldContent"
                    placeholder="Write the full announcement here..." maxlength="5000" required></textarea>
          <div class="char-count"><span id="contentCount">0</span> / 5000</div>
        </div>
      </form>
    </div>
    <div class="modal-footer">
      <button class="btn-cancel" id="cancelPost">Cancel</button>
      <button class="btn-submit" id="submitPost">Publish</button>
    </div>
  </div>
</div>

<!-- ═══════════ DELETE CONFIRM MODAL ═══════════ -->
<div class="modal-backdrop" id="deleteModal">
  <div class="confirm-modal">
    <div class="confirm-icon">
      <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 6h18M8 6V4a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/></svg>
    </div>
    <h3>Delete Announcement?</h3>
    <p id="deleteDesc">This action cannot be undone.</p>
    <form class="confirm-btns" action="actions/delete_announcement.php" method="POST">
      <input type="hidden" name="announcement_id" id="deleteID" value="">
      <button class="btn-cancel" id="cancelDelete" type="button">Cancel</button>
      <button class="btn-del-confirm" id="confirmDelete" type="submit">Delete</button>
    </form>
  </div>
</div>

<!-- Toast -->
<div class="toast" id="toast">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M20 6 9 17l-5-5"/></svg>
  <span id="toastMsg"></span>
</div>

<script>
/* ─── Theme ─── */
(function(){
  var root   = document.documentElement;
  var toggle = document.getElementById('themeToggle');
  var sun    = document.getElementById('themeIconSun');
  var moon   = document.getElementById('themeIconMoon');
  function applyTheme(t){
    root.setAttribute('data-theme', t);
    sun.style.display  = t === 'dark' ? 'none'  : 'block';
    moon.style.display = t === 'dark' ? 'block' : 'none';
    localStorage.setItem('pcu-theme', t);
  }
  var saved = localStorage.getItem('pcu-theme');
  var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
  applyTheme(saved || (prefersDark ? 'dark' : 'light'));
  toggle.addEventListener('click', function(){
    applyTheme(root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
  });
})();

/* ─── Page transition ─── */
(function(){
  var reduceMotion = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
  document.addEventListener('click', function(e){
    var link = e.target.closest('a[data-transition]');
    if (!link) return;
    if (e.ctrlKey || e.metaKey || e.shiftKey || e.button !== 0) return;
    var href = link.getAttribute('href');
    if (!href) return;
    e.preventDefault();
    if (reduceMotion){ window.location.href = href; return; }
    document.body.classList.add('page-leaving');
    setTimeout(function(){ window.location.href = href; }, 220);
  });
  window.addEventListener('pageshow', function(e){
    if (e.persisted) document.body.classList.remove('page-leaving');
  });
})();

/* ─── Filter ─── */
document.querySelectorAll('.filter-btn').forEach(function(btn){
  btn.addEventListener('click', function(){
    document.querySelectorAll('.filter-btn').forEach(function(b){ b.classList.remove('active'); });
    btn.classList.add('active');
    var filter = btn.dataset.filter;
    document.querySelectorAll('.announce-full-item').forEach(function(item){
      item.style.display = (filter === 'all' || item.dataset.tag === filter) ? '' : 'none';
    });
  });
});

/* ─── Toast helper ─── */
function showToast(msg, type){
  var t = document.getElementById('toast');
  var m = document.getElementById('toastMsg');
  m.textContent = msg;
  t.className = 'toast ' + (type || '');
  t.classList.add('show');
  setTimeout(function(){ t.classList.remove('show'); }, 3000);
}

/* ─── Post Modal ─── */
var postModal    = document.getElementById('postModal');
var modalTitle   = document.getElementById('modalTitle');
var submitBtn    = document.getElementById('submitPost');
var titleInput   = document.getElementById('fieldTitle');
var contentInput = document.getElementById('fieldContent');
var titleCount   = document.getElementById('titleCount');
var contentCount = document.getElementById('contentCount');

function openPostModal(){
  modalTitle.textContent        = 'Post Announcement';
  submitBtn.textContent         = 'Publish';
  document.getElementById('fieldID').value       = '';
  document.getElementById('fieldCategory').value = '';
  titleInput.value   = '';
  contentInput.value = '';
  titleCount.textContent   = '0';
  contentCount.textContent = '0';
  postModal.classList.add('open');
  document.body.style.overflow = 'hidden';
  titleInput.focus();
}

function closePostModal(){
  postModal.classList.remove('open');
  document.body.style.overflow = '';
}

document.getElementById('openPostModal').addEventListener('click', openPostModal);
document.getElementById('closePostModal').addEventListener('click', closePostModal);
document.getElementById('cancelPost').addEventListener('click', closePostModal);
postModal.addEventListener('click', function(e){ if(e.target === postModal) closePostModal(); });

titleInput.addEventListener('input', function(){
  titleCount.textContent = this.value.length;
});
contentInput.addEventListener('input', function(){
  contentCount.textContent = this.value.length;
});

/* ─── Edit ─── */
function openEdit(id, tag, title, content){
  modalTitle.textContent        = 'Edit Announcement';
  submitBtn.textContent         = 'Save Changes';
  document.getElementById('fieldID').value       = id;
  document.getElementById('fieldCategory').value = tag;
  titleInput.value   = title;
  contentInput.value = content;
  titleCount.textContent   = title.length;
  contentCount.textContent = content.length;
  postModal.classList.add('open');
  document.body.style.overflow = 'hidden';
  titleInput.focus();
}

/* ─── Submit ─── */
document.getElementById('submitPost').addEventListener('click', function(){
  var category = document.getElementById('fieldCategory').value.trim();
  var title    = titleInput.value.trim();
  var content  = contentInput.value.trim();

  if (!category || !title || !content){
    showToast('Please fill in all required fields.', 'error');
    return;
  }
  if (title.length < 5){
    showToast('Title must be at least 5 characters.', 'error');
    return;
  }
  if (content.length < 10){
    showToast('Content must be at least 10 characters.', 'error');
    return;
  }

  document.getElementById('postForm').submit();
});

/* ─── Delete Modal ─── */
var deleteModal = document.getElementById('deleteModal');
var deleteTarget = null;

function openDelete(id, title){
  deleteTarget = id;
  document.getElementById('deleteID').value = id;
  document.getElementById('deleteDesc').textContent = 'Are you sure you want to delete "' + title + '"? This action cannot be undone.';
  deleteModal.classList.add('open');
  document.body.style.overflow = 'hidden';
}

function closeDelete(){
  deleteModal.classList.remove('open');
  document.body.style.overflow = '';
  deleteTarget = null;
}

document.getElementById('cancelDelete').addEventListener('click', closeDelete);
deleteModal.addEventListener('click', function(e){ if(e.target === deleteModal) closeDelete(); });
</script>
</body>
</html>
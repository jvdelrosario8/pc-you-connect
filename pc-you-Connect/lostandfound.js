/* =========================================================
   LOST & FOUND PAGE
   Items are kept in the `items` array below (front-end only).
   Your database has no lost & found table yet, so changes
   reset when the page is reloaded.
   ========================================================= */

(function () {
  var PER_PAGE = 7;

  var items = window.initialLostFoundItems || [];
  var nextId = 100;

  var state = { filter: 'all', query: '', page: 1, editingId: null, deletingId: null, photo: null };

  /* ---------- Icons ---------- */
  var ICONS = {
    watch: '<circle cx="12" cy="12" r="7"/><path d="M12 9v3l2 1.5"/><path d="M9 3h6M9 21h6"/>',
    idcard: '<rect x="3" y="5" width="18" height="14" rx="2"/><circle cx="9" cy="12" r="2.2"/><path d="M14 10h4M14 14h4"/>',
    laptop: '<rect x="4" y="4" width="16" height="11" rx="1.5"/><path d="M2 19h20l-1.5-3H3.5L2 19Z" stroke-linejoin="round"/>',
    bag: '<path d="M5 8a3 3 0 0 1 3-3h8a3 3 0 0 1 3 3v12a1 1 0 0 1-1 1H6a1 1 0 0 1-1-1Z"/><path d="M9 5V3h6v2M8 13h8v4H8z"/>',
    key: '<circle cx="7.5" cy="15.5" r="4.5"/><path d="m10.7 12.3 9.3-9.3M17 6l3 3M14 9l2 2"/>',
    pencil: '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
    box: '<path d="M21 8v13H3V8M1 3h22v5H1zM10 12h4"/>',
    eye: '<path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7S2 12 2 12Z"/><circle cx="12" cy="12" r="3"/>',
    edit: '<path d="M12 20h9"/><path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>',
    trash: '<path d="M3 6h18M8 6V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6M10 11v6M14 11v6"/>'
  };
  function svg(name) {
    return '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">' + ICONS[name] + '</svg>';
  }
  var CATEGORY = {
    'Accessories':     { icon: 'watch',  color: 'pink' },
    'ID / Cards':      { icon: 'idcard', color: 'purple' },
    'Electronics':     { icon: 'laptop', color: 'blue' },
    'Bags':            { icon: 'bag',    color: 'pink' },
    'Keys':            { icon: 'key',    color: 'yellow' },
    'School Supplies': { icon: 'pencil', color: 'green' },
    'Others':          { icon: 'box',    color: 'purple' }
  };
  var STATUS = {
    'unclaimed': { label: 'UNCLAIMED', cls: 'unclaimed' },
    'claimed':   { label: 'CLAIMED',   cls: 'claimed' },
    'turned in': { label: 'TURNED IN', cls: 'turned-in' }
  };

  /* ---------- Helpers ---------- */
  function $(id) { return document.getElementById(id); }
  function esc(s) {
    return String(s == null ? '' : s).replace(/[&<>"']/g, function (c) {
      return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c];
    });
  }
  function timeAgo(t) {
    var mins = Math.floor((Date.now() - t) / 60000);
    if (mins < 1) return 'just now';
    if (mins < 60) return mins + ' min ago';
    var hrs = Math.floor(mins / 60);
    if (hrs < 24) return hrs + (hrs === 1 ? ' hr ago' : ' hrs ago');
    var days = Math.floor(hrs / 24);
    return days + (days === 1 ? ' day ago' : ' days ago');
  }
  function category(c) { return CATEGORY[c] || CATEGORY.Others; }
  function findItem(id) { return items.filter(function (i) { return i.id === id; })[0]; }

  var toastTimer;
  function toast(msg) {
    var t = $('toast');
    t.textContent = msg;
    t.hidden = false;
    clearTimeout(toastTimer);
    toastTimer = setTimeout(function () { t.hidden = true; }, 2500);
  }

  /* ---------- Rendering ---------- */
  function filteredItems() {
    var q = state.query.toLowerCase();
    return items
      .slice()
      .sort(function (a, b) { return b.reportedAt - a.reportedAt; })
      .filter(function (i) {
        var matchesStatus = state.filter === 'all' || i.status === state.filter;
        var text = (i.name + ' ' + i.category + ' ' + i.location + ' ' + i.description).toLowerCase();
        return matchesStatus && (!q || text.indexOf(q) !== -1);
      });
  }

  function renderStats() {
    function count(s) { return items.filter(function (i) { return i.status === s; }).length; }
    $('statTotal').textContent = items.length;
    $('statUnclaimed').textContent = count('unclaimed');
    $('statClaimed').textContent = count('claimed');
    $('statTurnedIn').textContent = count('turned in');
  }

  function renderTable() {
    var list = filteredItems();
    var pages = Math.max(1, Math.ceil(list.length / PER_PAGE));
    if (state.page > pages) state.page = pages;
    var start = (state.page - 1) * PER_PAGE;
    var rows = list.slice(start, start + PER_PAGE);

    $('itemRows').innerHTML = rows.map(function (i, n) {
      var cat = category(i.category);
      var st = STATUS[i.status];
      var thumb = i.photo ? '<img src="' + i.photo + '" alt="">' : svg(cat.icon);
      return '<tr>' +
        '<td class="lf-num">' + (start + n + 1) + '</td>' +
        '<td><div class="lf-item"><span class="row-icon ' + cat.color + '">' + thumb + '</span>' + esc(i.name) + '</div></td>' +
        '<td><span class="lf-category">' + esc(i.category) + '</span></td>' +
        '<td>' + esc(i.location) + '</td>' +
        '<td>' + timeAgo(i.reportedAt) + '</td>' +
        '<td><span class="pill ' + st.cls + '">' + st.label + '</span></td>' +
        '<td><div class="lf-actions">' +
          '<button type="button" class="lf-act" title="View" data-view="' + i.id + '">' + svg('eye') + '</button>' +
          '<button type="button" class="lf-act edit" title="Edit" data-edit="' + i.id + '">' + svg('edit') + '</button>' +
          '<button type="button" class="lf-act delete" title="Delete" data-delete="' + i.id + '">' + svg('trash') + '</button>' +
        '</div></td>' +
      '</tr>';
    }).join('');

    $('emptyState').hidden = rows.length > 0;
    document.querySelector('.lf-table').style.display = rows.length ? '' : 'none';
    $('showingText').textContent = 'Showing ' + rows.length + ' of ' + list.length + ' items';

    var pager = '<button type="button" data-page="' + (state.page - 1) + '"' + (state.page <= 1 ? ' disabled' : '') + '>Prev</button>';
    for (var p = 1; p <= pages; p++) {
      pager += '<button type="button" data-page="' + p + '" class="' + (p === state.page ? 'on' : '') + '">' + p + '</button>';
    }
    pager += '<button type="button" data-page="' + (state.page + 1) + '"' + (state.page >= pages ? ' disabled' : '') + '>Next</button>';
    $('pager').innerHTML = pager;
  }

  function render() {
    renderStats();
    renderTable();
  }

  /* ---------- Modals ---------- */
  function openModal(id) {
    $(id).hidden = false;
    var first = $(id).querySelector('input:not([type=file]), select, textarea, .lf-btn');
    if (first) first.focus();
  }
  function closeModals() {
    ['itemModal', 'viewModal', 'deleteModal'].forEach(function (id) { $(id).hidden = true; });
  }

  function clearErrors() {
    document.querySelectorAll('#itemForm .error').forEach(function (el) { el.classList.remove('error'); });
    document.querySelectorAll('#itemForm .lf-error').forEach(function (el) { el.remove(); });
  }
  function showError(inputId, msg) {
    var el = $(inputId);
    el.classList.add('error');
    el.insertAdjacentHTML('afterend', '<div class="lf-error">' + msg + '</div>');
  }

  function setPhoto(dataUrl) {
    state.photo = dataUrl;
    $('uploadPreview').hidden = !dataUrl;
    $('uploadEmpty').hidden = !!dataUrl;
    $('removePhoto').hidden = !dataUrl;
    if (dataUrl) $('uploadPreview').src = dataUrl; else $('uploadPreview').removeAttribute('src');
    $('fPhoto').value = '';
  }

  function openItemForm(item) {
    clearErrors();
    state.editingId = item ? item.id : null;
    $('itemModalTitle').textContent = item ? 'Edit Item' : 'Report New Item';
    $('itemSubmit').textContent = item ? 'Save changes' : 'Submit';
    $('fName').value = item ? item.name : '';
    $('fCategory').value = item ? item.category : 'Accessories';
    $('fLocation').value = item ? item.location : '';
    $('fDescription').value = item ? item.description : '';
    $('statusField').hidden = !item;
    if (item) $('fStatus').value = item.status;
    setPhoto(item ? item.photo : null);
    openModal('itemModal');
  }

  function saveItem(e) {
    e.preventDefault();
    clearErrors();
    var name = $('fName').value.trim();
    var location = $('fLocation').value.trim();
    var ok = true;
    if (!name) { showError('fName', 'Enter the item name.'); ok = false; }
    if (!location) { showError('fLocation', 'Enter where the item was found.'); ok = false; }
    if (!ok) return;

    var data = {
      name: name,
      category: $('fCategory').value,
      location: location,
      description: $('fDescription').value.trim(),
      photo: state.photo
    };

    if (state.editingId) {
      var item = findItem(state.editingId);
      Object.keys(data).forEach(function (k) { item[k] = data[k]; });
      item.status = $('fStatus').value;
      toast('Item updated');
    } else {
      e.target.submit();
      return;
    }
    closeModals();
    render();
  }

  function openView(id) {
    var i = findItem(id);
    var st = STATUS[i.status];
    $('viewTitle').textContent = i.name;
    $('viewBody').innerHTML =
      (i.photo ? '<img class="lf-detail-photo" src="' + i.photo + '" alt="">' : '') +
      '<dl class="lf-detail">' +
        '<dt>Status</dt><dd><span class="pill ' + st.cls + '">' + st.label + '</span></dd>' +
        '<dt>Category</dt><dd>' + esc(i.category) + '</dd>' +
        '<dt>Location found</dt><dd>' + esc(i.location) + '</dd>' +
        '<dt>Reported</dt><dd>' + timeAgo(i.reportedAt) + '</dd>' +
        '<dt>Description</dt><dd>' + (i.description ? esc(i.description) : '<span style="color:var(--muted)">No description added.</span>') + '</dd>' +
      '</dl>';
    $('viewActions').innerHTML = i.status === 'unclaimed'
      ? '<button type="button" class="lf-btn lf-btn-ghost" data-status="turned in" data-id="' + i.id + '">Mark turned in</button>' +
        '<button type="button" class="lf-btn lf-btn-primary" data-status="claimed" data-id="' + i.id + '">Mark as claimed</button>'
      : '<button type="button" class="lf-btn lf-btn-ghost" data-status="unclaimed" data-id="' + i.id + '">Mark unclaimed</button>' +
        '<button type="button" class="lf-btn lf-btn-primary" data-close>Done</button>';
    openModal('viewModal');
  }

  function openDelete(id) {
    state.deletingId = id;
    $('deleteText').textContent = '"' + findItem(id).name + '" will be removed from Lost & Found. This can\'t be undone.';
    openModal('deleteModal');
  }

  function setActiveTab() {
    document.querySelectorAll('#statusTabs button').forEach(function (b) {
      b.classList.toggle('on', b.getAttribute('data-filter') === state.filter);
    });
  }

  /* ---------- Events ---------- */
  $('addItemBtn').addEventListener('click', function () { openItemForm(null); });
  $('itemForm').addEventListener('submit', saveItem);

  $('statusTabs').addEventListener('click', function (e) {
    var b = e.target.closest('button');
    if (!b) return;
    state.filter = b.getAttribute('data-filter');
    state.page = 1;
    setActiveTab();
    renderTable();
  });

  $('itemSearch').addEventListener('input', function (e) {
    state.query = e.target.value;
    state.page = 1;
    renderTable();
  });

  $('pager').addEventListener('click', function (e) {
    var b = e.target.closest('button');
    if (!b || b.disabled) return;
    state.page = parseInt(b.getAttribute('data-page'), 10);
    renderTable();
  });

  $('itemRows').addEventListener('click', function (e) {
    var b = e.target.closest('button');
    if (!b) return;
    if (b.hasAttribute('data-view')) openView(+b.getAttribute('data-view'));
    if (b.hasAttribute('data-edit')) openItemForm(findItem(+b.getAttribute('data-edit')));
    if (b.hasAttribute('data-delete')) openDelete(+b.getAttribute('data-delete'));
  });

  $('viewActions').addEventListener('click', function (e) {
    var b = e.target.closest('[data-status]');
    if (!b) return;
    findItem(+b.getAttribute('data-id')).status = b.getAttribute('data-status');
    closeModals();
    toast('Status updated');
    render();
  });

  $('confirmDelete').addEventListener('click', function () {
    items = items.filter(function (i) { return i.id !== state.deletingId; });
    closeModals();
    toast('Item deleted');
    render();
  });

  $('fPhoto').addEventListener('change', function () {
    var file = this.files[0];
    if (!file) return;
    if (file.size > 5 * 1024 * 1024) { toast('Choose an image under 5 MB'); return; }
    var reader = new FileReader();
    reader.onload = function () { setPhoto(reader.result); };
    reader.readAsDataURL(file);
  });
  $('removePhoto').addEventListener('click', function () { setPhoto(null); });

  document.querySelectorAll('.lf-overlay').forEach(function (overlay) {
    overlay.addEventListener('click', function (e) {
      if (e.target === overlay || e.target.closest('[data-close]')) closeModals();
    });
  });
  document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeModals(); });

  document.addEventListener('input', function (e) {
    if (e.target.classList.contains('error')) {
      e.target.classList.remove('error');
      var msg = e.target.nextElementSibling;
      if (msg && msg.classList.contains('lf-error')) msg.remove();
    }
  });

  render();
})();


/* =========================================================
   DARK MODE TOGGLE (same behavior as admin_dashboard.php)
   ========================================================= */
(function () {
  var root = document.documentElement;
  var toggle = document.getElementById('themeToggle');
  var sunIcon = document.getElementById('themeIconSun');
  var moonIcon = document.getElementById('themeIconMoon');

  function applyTheme(theme) {
    root.setAttribute('data-theme', theme);
    sunIcon.style.display = theme === 'dark' ? 'none' : 'block';
    moonIcon.style.display = theme === 'dark' ? 'block' : 'none';
    localStorage.setItem('pcu-theme', theme);
  }

  var saved = localStorage.getItem('pcu-theme');
  var prefersDark = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches;
  applyTheme(saved || (prefersDark ? 'dark' : 'light'));

  toggle.addEventListener('click', function () {
    applyTheme(root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark');
  });
})();


/* =========================================================
   PAGE TRANSITION
   Fades the page out before going to another page.
   The fade-in on arrival is handled in admin_dashboard_styles.css.
   ========================================================= */
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
    setTimeout(function () { window.location.href = href; }, 220);
  });

  // When coming back with the browser's Back button, show the page again.
  window.addEventListener('pageshow', function (e) {
    if (e.persisted) document.body.classList.remove('page-leaving');
  });
})();

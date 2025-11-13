<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['idToken']) || !isset($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

// Get user information from session
// NOTE: Changed default user name to Instructor
$userName = isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Instructor';
$userEmail = isset($_SESSION['email']) ? $_SESSION['email'] : '';

// Generate avatar initials from name
$avatarInitials = 'U';
if (!empty($userName)) {
    $nameParts = explode(' ', $userName);
    if (count($nameParts) >= 2) {
        $avatarInitials = strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1));
    } else {
        $avatarInitials = strtoupper(substr($userName, 0, 2));
    }
}

// --- LOGOUT FUNCTION ---
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: login.php'); // redirect after logout
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Instructor Dashboard — BSU</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <style>
    :root{
      --red: #c9413b;
      --bg: #f5f6f8;
      --card: #fff;
      --muted: #7a7a7a;
      --accent: #3b82f6;
      --sidebar-w: 240px;
    }
    *{box-sizing:border-box;margin:0;padding:0;font-family:Poppins, Arial, sans-serif}
    body{background:var(--bg);color:#111}

    /* === TOP BAR === */
    .topbar{
      height:72px;background:var(--red);color:#fff;display:flex;align-items:center;
      justify-content:space-between;padding:0 20px;box-shadow:0 2px 6px rgba(0,0,0,0.1);
      position:sticky;top:0;z-index:100;
    }
    .left-area{display:flex;align-items:center;gap:12px}
    .hamburger{width:36px;height:36px;display:grid;place-items:center;cursor:pointer}
    .bar{width:18px;height:2px;background:#fff;margin:3px 0}
    .logo{height:40px;width:40px;border-radius:6px;background:#fff;padding:2px}
    .title{font-weight:600}
    .subtitle{font-size:12px;color:rgba(255,255,255,0.85)}

    /* === RIGHT AREA === */
    .top-controls{display:flex;align-items:center;gap:16px}
    .icon-btn{position:relative;width:44px;height:44px;border:none;background:transparent;color:#fff;cursor:pointer}
    .badge{
      position:absolute;top:8px;right:10px;background:#ff3b30;color:white;font-weight:600;
      font-size:11px;width:18px;height:18px;border-radius:9px;display:grid;place-items:center;
    }
    .profile{display:flex;align-items:center;gap:8px;position:relative;cursor:pointer}
    .avatar{width:36px;height:36px;border-radius:50%;background:#fff;color:#c9413b;display:grid;place-items:center;font-weight:700}
    .dropdown{
      position:absolute;top:56px;right:0;background:var(--card);border-radius:8px;
      box-shadow:0 6px 20px rgba(0,0,0,0.12);display:none;padding:8px;min-width:160px;
    }
    .dropdown a{display:block;padding:8px;border-radius:6px;color:#111;text-decoration:none}
    .dropdown a:hover{background:#f2f2f2}
    .dropdown a.logout{color:#c9413b;font-weight:600}

    /* === MAIN LAYOUT === */
    .page{display:flex;gap:20px;padding:20px;align-items:flex-start}
    .sidebar{
      width:var(--sidebar-w);background:var(--card);border-radius:10px;padding:16px;
      box-shadow:0 4px 12px rgba(0,0,0,0.04);min-height:calc(100vh - 72px);
      transition:width 0.3s;
    }
    .sidebar.collapsed{width:72px}
    .sidebar.collapsed .nav-item div:not(.ico){display:none}
    .nav-item{display:flex;align-items:center;gap:12px;padding:10px;border-radius:8px;color:#222;text-decoration:none;margin-bottom:8px}
    .nav-item:hover{background:#f4f6fb}
    .nav-item .ico{width:28px;height:28px;border-radius:6px;background:#eef2ff;display:grid;place-items:center;flex-shrink:0}

    .content{flex:1;}
    .main-grid{display:grid;grid-template-columns:1fr 1fr 320px;gap:20px}
    
    /* === CARDS === */
    .card{background:var(--card);padding:16px;border-radius:10px;box-shadow:0 4px 18px rgba(0,0,0,0.04);margin-bottom:12px}
    .card:last-child{margin-bottom:0}
    h3{margin-bottom:12px;font-size:16px;font-weight:600}

    /* === COLLAPSIBLE SECTIONS === */
    .collapsible-header{
      display:flex;align-items:center;justify-content:space-between;cursor:pointer;
      padding:8px 0;user-select:none;
    }
    .collapsible-header h3{margin:0;flex:1}
    .collapsible-icon{
      width:24px;height:24px;display:grid;place-items:center;
      transition:transform 0.3s ease;color:var(--muted);
    }
    .collapsible-section.active .collapsible-icon{transform:rotate(180deg)}
    .collapsible-content{
      max-height:0;overflow:hidden;transition:max-height 0.3s ease;
      padding-top:0;
    }
    .collapsible-section.active .collapsible-content{
      max-height:1000px;padding-top:12px;
    }
    .collapsible-content p{color:var(--muted);margin:0}
    .collapsible-content *{overflow:visible;word-wrap:break-word}

    /* === INDICATORS === */
    .status-indicator{
      display:inline-flex;align-items:center;gap:6px;padding:6px 10px;
      background:#fff3cd;border-left:3px solid #ffc107;border-radius:4px;
      font-size:13px;color:#856404;margin-top:8px;
    }
    .status-indicator.no-data{
      background:#f8d7da;border-left-color:#dc3545;color:#721c24;
    }
    .status-indicator::before{
      content:'ℹ️';font-size:14px;
    }

    /* === BUTTONS === */
    .btn-add{
      background:var(--accent);color:#fff;padding:8px 16px;border-radius:8px;
      text-decoration:none;display:inline-block;margin-top:12px;
      border:none;cursor:pointer;font-size:14px;font-weight:500;
      transition:background 0.2s;
    }
    .btn-add:hover{background:#2563eb}

    /* === RIGHT SIDEBAR === */
    .right-sidebar .card{margin-bottom:12px}
    .calendar-card{background:var(--card);padding:16px;border-radius:10px;box-shadow:0 4px 18px rgba(0,0,0,0.04)}
    .time-card{background:var(--card);padding:16px;border-radius:10px;box-shadow:0 4px 18px rgba(0,0,0,0.04)}
    .time-display{font-weight:600;font-size:18px;margin-bottom:4px}
    .time-label{font-size:12px;color:var(--muted)}
    .calendar-grid{display:grid;grid-template-columns:repeat(7,1fr);gap:4px;font-size:12px;margin-top:10px}
    .calendar-day-header{text-align:center;color:#888;font-weight:500;padding:4px}
    .calendar-day{text-align:center;padding:6px 4px;border-radius:4px}
    .calendar-day.today{background:#3b82f6;color:#fff;font-weight:600}
    .calendar-month{font-size:14px;font-weight:600;margin-bottom:8px;color:#111}

    footer{margin-top:20px;padding:12px;text-align:center;color:var(--muted);font-size:12px}
  </style>
</head>
<body>

  <div class="topbar">
    <div class="left-area">
      <div class="hamburger" id="hamburger">
        <div class="bar"></div><div class="bar"></div><div class="bar"></div>
      </div>
      <img src="images/bsu_logo.png" alt="BSU Logo" class="logo">
      <div>
        <div class="title">BATANGAS STATE UNIVERSITY</div>
        <div class="subtitle">Instructor Dashboard</div>
      </div>
    </div>

    <div class="top-controls">
      <button class="icon-btn" id="notifBtn" title="Notifications">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
          <path d="M15 17H9a3 3 0 0 0 6 0z" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 7h18s-3 0-3-7" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span class="badge" id="notifBadge">3</span>
      </button>

      <div class="profile" id="profileBtn">
        <div style="font-size:13px;text-align:right">Hi, <?php echo htmlspecialchars($userName); ?></div>
        <div class="avatar"><?php echo htmlspecialchars($avatarInitials); ?></div>

        <div class="dropdown" id="profileDropdown">
          <a href="#">View Profile</a>
          <a href="?action=logout" class="logout">Logout</a>
        </div>
      </div>
    </div>
  </div>

  <div class="page">
    <aside class="sidebar" id="sidebar">
      <a href="#" class="nav-item"><div class="ico">🏠</div><div>Dashboard</div></a>
      <a href="#" class="nav-item"><div class="ico">📚</div><div>My Courses</div></a>
      <a href="#" class="nav-item"><div class="ico">📝</div><div>Content Management</div></a>
      <a href="#" class="nav-item"><div class="ico">📊</div><div>Student Analytics</div></a>
    </aside>

    <main class="content">
      <div class="main-grid">
        <section>
          <div class="card">
            <h3>Welcome, Instructor <?php echo htmlspecialchars($userName); ?>!</h3>
            <p style="color:var(--muted)">This is your overview panel. You can manage your courses, student content, and review performance analytics here.</p>
          </div>
          <footer>
            © <?php echo date('Y'); ?> Batangas State University
          </footer>
        </section>

        <section>
          <div class="card collapsible-section" id="announcements-section">
            <div class="collapsible-header" onclick="toggleCollapsible('announcements-section')">
              <h3>Announcements & News</h3>
              <div class="collapsible-icon">▼</div>
            </div>
            <div class="collapsible-content">
              <p style="color:var(--muted);margin-bottom:8px">Check for latest faculty updates and important academic notices.</p>
              <div class="status-indicator no-data">
                No new faculty announcements available at this time.
              </div>
            </div>
          </div>

          <div class="card collapsible-section" id="courses-section">
            <div class="collapsible-header" onclick="toggleCollapsible('courses-section')">
              <h3>My Courses</h3>
              <div class="collapsible-icon">▼</div>
            </div>
            <div class="collapsible-content">
              <p style="color:var(--muted);margin-bottom:8px">Manage lessons, quizzes, and settings for your current courses.</p>
              <div class="status-indicator">
                You currently have 3 active courses. Click below to manage them.
              </div>
              <button class="btn-add" onclick="window.location.href='#'">+ Create New Course</button>
            </div>
          </div>
        </section>

        <aside class="right-sidebar">
          <div class="calendar-card">
            <h3>Calendar</h3>
            <div id="miniCalendar"></div>
          </div>

          <div class="time-card">
            <h3>Current Time</h3>
            <div class="time-display" id="timeNow"></div>
            <div class="time-label">Local Time</div>
          </div>

          <div class="card collapsible-section" id="grading-section">
            <div class="collapsible-header" onclick="toggleCollapsible('grading-section')">
              <h3>Pending Submissions</h3>
              <div class="collapsible-icon">▼</div>
            </div>
            <div class="collapsible-content">
              <p style="color:var(--muted);margin-bottom:8px">View assignments and quizzes that require your grading attention.</p>
              <div class="status-indicator">
                You have 12 pending submissions across all courses.
              </div>
              <button class="btn-add" onclick="window.location.href='#'">Go to Gradebook</button>
            </div>
          </div>
        </aside>
      </div>
    </main>
  </div>

<script>
  // Sidebar toggle
  const hamburger = document.getElementById('hamburger');
  const sidebar = document.getElementById('sidebar');
  hamburger.addEventListener('click', () => sidebar.classList.toggle('collapsed'));

  // Notification badge (for demo only)
  document.getElementById('notifBtn').addEventListener('click', () => {
    document.getElementById('notifBadge').style.display = 'none';
    alert("No new notifications.");
  });

  // Profile dropdown
  const profileBtn = document.getElementById('profileBtn');
  const dropdown = document.getElementById('profileDropdown');
  profileBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
  });
  document.addEventListener('click', () => dropdown.style.display = 'none');

  // Collapsible sections - close previous one before opening new one
  let activeSection = null;
  function toggleCollapsible(sectionId) {
    const section = document.getElementById(sectionId);
    
    // If clicking the same section, just toggle it
    if (section.classList.contains('active')) {
      section.classList.remove('active');
      activeSection = null;
    } else {
      // Close the previously active section first
      if (activeSection && activeSection !== section) {
        activeSection.classList.remove('active');
      }
      // Open the clicked section
      section.classList.add('active');
      activeSection = section;
    }
  }

  // Time updater
  function updateTime() {
    const t = new Date();
    document.getElementById('timeNow').innerText = t.toLocaleString();
  }
  setInterval(updateTime, 1000);
  updateTime();

  // Simple calendar
  function buildMiniCalendar() {
    const el = document.getElementById('miniCalendar');
    const now = new Date();
    const year = now.getFullYear(), month = now.getMonth();
    const first = new Date(year, month, 1);
    const last = new Date(year, month + 1, 0);
    
    let html = '<div class="calendar-month">' + now.toLocaleString(undefined,{month:'long',year:'numeric'}) + '</div>';
    html += '<div class="calendar-grid">';
    
    // Day headers
    const days = ['S','M','T','W','T','F','S'];
    days.forEach(d => html += '<div class="calendar-day-header">'+d+'</div>');
    
    // Empty cells for days before month starts
    for (let i=0;i<first.getDay();i++) html += '<div></div>';
    
    // Days of the month
    for (let d=1; d<=last.getDate(); d++){
      const isToday = d === now.getDate();
      const cls = isToday ? 'calendar-day today' : 'calendar-day';
      html += '<div class="'+cls+'">'+d+'</div>';
    }
    
    html += '</div>';
    el.innerHTML = html;
  }
  buildMiniCalendar();
</script>

</body>
</html>

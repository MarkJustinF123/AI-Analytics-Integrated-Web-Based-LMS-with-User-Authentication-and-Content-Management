<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['idToken']) || !isset($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

// Get user information from session
$userName = isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Student';
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
  <title>Student Dashboard — BSU</title>
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
    
    /* UPDATED: Compact and long hamburger icon */
    .hamburger{
      width:30px; 
      height:30px;
      display:grid;
      place-items:center;
      cursor:pointer;
      border-radius: 4px;
      transition: background 0.2s;
    }
    .hamburger:hover {
      background: rgba(0,0,0,0.1);
    }
    .bar{
      width: 18px;  /* Longer bars */
      height: 2px;
      background: #fff;
      margin: 2px 0; /* Spaced out */
    }
    
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
    .sidebar.collapsed{
      width:72px;
    }
    .sidebar.collapsed .nav-item div:not(.ico){
      display:none;
    }
    
    .nav-item{display:flex;align-items:center;gap:12px;padding:10px;border-radius:8px;color:#222;text-decoration:none;margin-bottom:8px}
    .nav-item:hover{background:#f4f6fb}
    .nav-item .ico{width:28px;height:28px;border-radius:6px;background:#eef2ff;display:grid;place-items:center;flex-shrink:0}

    .content{flex:1;}
    
    /* === Main Grid Layout === */
    .main-grid{
      display:grid;
      grid-template-columns:1fr 1fr 320px;
      gap:20px;
    }
    .welcome-card {
      grid-column: 1 / 3;
    }
    .right-sidebar {
      grid-column: 3 / 4;
      grid-row: 1 / 3;
    }
    
    /* === CARDS === */
    .card{background:var(--card);padding:16px;border-radius:10px;box-shadow:0 4px 18px rgba(0,0,0,0.04);margin-bottom:12px}
    .card:last-child{margin-bottom:0}
    
    /* UPDATED: Remove bottom margin from welcome card to reduce gap */
    .welcome-card .card {
      margin-bottom: 0;
    }
    
    h3{margin-bottom:12px;font-size:16px;font-weight:600}

    /* === Card Header Flex (for 'Class' card) === */
    .card-header-flex {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 12px;
    }
    .card-header-flex h3 {
      margin-bottom: 0;
    }
    .card-header-flex .btn-add {
      margin-top: 0;
    }

    /* REMOVED: Collapsible CSS rules */

    /* UPDATED: Card content area for scrolling */
    .card-content {
      max-height: 250px; /* Set a max height */
      overflow-y: auto;  /* Add scrollbar if content overflows */
    }

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
<body class="student">

  <div class="topbar">
    <div class="left-area">
      <div class="hamburger" id="hamburger">
        <div class="bar"></div><div class="bar"></div><div class="bar"></div>
      </div>
      <img src="images/bsu_logo.png" alt="BSU Logo" class="logo">
      <div>
        <div class="title">BATANGAS STATE UNIVERSITY</div>
        <div class="subtitle">Student Dashboard</div>
      </div>
    </div>

    <div class="top-controls">
      <button class="icon-btn" id="notifBtn" title="Notifications">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none">
          <path d="M15 17H9a3 3 0 0 0 6 0z" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
          <path d="M18 8a6 6 0 1 0-12 0c0 7-3 7-3 7h18s-3 0-3-7" stroke="#fff" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"/>
        </svg>
        <span class="badge" id="notifBadge">1</span>
      </button>

      <div class="profile" id="profileBtn">
        <div style="font-size:13px;text-align:right">Hi, <?php echo htmlspecialchars($userName); ?></div>
        <div class="avatar"><?php echo htmlspecialchars($avatarInitials); ?></div>

        <div class="dropdown" id="profileDropdown">
          <a href="ViewProfileStudent.php">View Profile</a>
          <a href="?action=logout" class="logout">Logout</a>
        </div>
      </div>
    </div>
  </div>

  <div class="page">
    <aside class="sidebar" id="sidebar">
      <a href="#" class="nav-item"><div class="ico">🏠</div><div>Dashboard</div></a>
      <a href="#" class="nav-item"><div class="ico">📚</div><div>Classes</div></a>
      <a href="#" class="nav-item"><div class="ico">📝</div><div>Enroll</div></a>
      <a href="#" class="nav-item"><div class="ico">📊</div><div>Activities</div></a>
    </aside>

    <main class="content">
      <div class="main-grid">
      
        <section class="welcome-card">
          <div class="card">
            <h3>Welcome to Student Dashboard, <?php echo htmlspecialchars($userName); ?>!</h3>
            <p style="color:var(--muted); margin-top: 4px;">
              Welcome aboard! Your entire student life is organized right here. Dive into your classes, mark your calendar, and let's make this a fantastic school year. We're thrilled to have you as a part of the Red Spartans!
            </p>
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
          
          <div class="card" id="activities-section">
            <h3>Pending Activities</h3>
            <div class="card-content scrollable"> 
              <p style="color:var(--muted);margin-bottom:8px">View your pending assignments and tasks.</p>
              <div class="status-indicator no-data">
                No pending activities at this time.
              </div>
              </div>
          </div>
          
        </aside>

        <section>
          <div class="card" id="announcements-section">
            <h3>Announcements & News</h3>
            <div class="card-content"> <p style="color:var(--muted);margin-bottom:8px">Check for latest updates and important notices.</p>
              <div class="status-indicator no-data">
                No announcements available at this time.
              </div>
            </div>
          </div>
        </section>

        <section>
          <div class="card" id="class-section">
            <div class="card-header-flex">
              <h3>Class</h3>
              <button class="btn-add" onclick="window.location.href='#'">+ Add Class</button>
            </div>
            <div class="card-content"> <p style="color:var(--muted);margin-bottom:8px">Manage your enrolled classes.</p>
              <div class="status-indicator">
                You have not enrolled in any class yet.
              </div>
            </div>
          </div>
        </section>

      </div> <footer>
        © <?php echo date('Y'); ?> Batangas State University
      </footer>
    </main>
  </div>

<script>
  // Sidebar toggle script
  const hamburger = document.getElementById('hamburger');
  const sidebar = document.getElementById('sidebar');
  hamburger.addEventListener('click', () => {
    sidebar.classList.toggle('collapsed');
  });

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

  // REMOVED: toggleCollapsible function

  // Time updater
  function updateTime() {
    const t = new Date();
    document.getElementById('timeNow').innerText = t.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
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
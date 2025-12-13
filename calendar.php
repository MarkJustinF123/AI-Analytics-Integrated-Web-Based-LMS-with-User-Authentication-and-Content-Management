<?php
session_start();
// Check if user is logged in (same logic as dashboard)
if (!isset($_SESSION['idToken']) || !isset($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}
$userName = isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Instructor';
$avatarInitials = 'U';
if (!empty($userName)) {
    $nameParts = explode(' ', $userName);
    $avatarInitials = count($nameParts) >= 2 ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1)) : strtoupper(substr($userName, 0, 2));
}
// Logout function
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Calendar — BSU</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="dashboard2.css">
  <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <style>
    /* Specific styles for the Calendar Page */
    .calendar-container {
        max-width: 1200px;
        margin: 24px auto;
        background: var(--gg-card);
        border-radius: 8px;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        padding: 32px;
    }
    .calendar-container h1 {
        font-size: 28px;
        font-weight: 500;
        margin: 0 0 24px 0;
    }
    
    /* --- CALENDAR GRID STYLES --- */
    .month-title {
        font-size: 20px;
        font-weight: 500;
        margin-bottom: 16px;
        text-align: center;
        color: var(--gg-primary-text);
    }
    .calendar-grid-wrapper {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        min-height: 700px;
        background: #fcfcfc;
        border: 1px solid var(--gg-border);
        border-radius: 6px;
        border-right: none;
        border-bottom: none;
    }
    .calendar-day-header, .calendar-day-cell {
        border-right: 1px solid var(--gg-border);
        border-bottom: 1px solid var(--gg-border);
        padding: 8px;
        text-align: left;
        font-size: 14px;
    }
    .calendar-day-header {
        text-align: center;
        font-weight: 600;
        padding: 12px 8px;
        background: #f1f3f4;
        color: var(--gg-secondary-text);
    }
    .calendar-day-cell {
        min-height: 100px;
        position: relative;
    }
    .calendar-day-cell span {
        font-weight: 500;
        color: var(--gg-primary-text);
        display: block;
    }
    .calendar-day-cell.today span {
        background-color: var(--gg-school-red); /* Highlight today's date */
        color: white;
        width: 24px;
        height: 24px;
        border-radius: 50%;
        display: grid;
        place-items: center;
        font-size: 13px;
    }
  </style>
</head>
<body>

  <header class="header">
    <div class="header-left">
      <a href="InstructorDashboard.php" class="header-menu">
        <span class="material-icons">menu</span>
      </a>
      <span class="header-title">Calendar</span>
    </div>
    <div class="header-right">
      <div class="profile" id="profileBtn">
        <div class="avatar"><?php echo htmlspecialchars($avatarInitials); ?></div>
        <div class="dropdown" id="profileDropdown">
          <a href="#">View Profile</a>
          <a href="?action=logout" class="logout">Logout</a>
        </div>
      </div>
    </div>
  </header>

  <div class="page-container">
    
    <aside class="sidebar" id="sidebar">
      <a href="InstructorDashboard.php" class="nav-item">
        <span class="material-icons nav-item-icon">home</span>
        <div>Home</div>
      </a>
      <a href="calendar.php" class="nav-item active">
        <span class="material-icons nav-item-icon">calendar_today</span>
        <div>Calendar</div>
      </a>
      
      <div class="nav-item collapsible-header" id="teaching-header">
        <span class="material-icons nav-item-icon">school</span>
        <div>Teaching</div>
        <span class="material-icons" id="teaching-icon" style="margin-left:auto; transform: rotate(0deg); transition: transform 0.2s;">keyboard_arrow_down</span>
      </div>
      
      <div id="teaching-classes-list" style="display:block; padding-left: 20px;">
        </div>
    </aside>

    <main class="content-area" style="padding:0;">
        <div class="calendar-container">
            <h1>My Schedule</h1>
            <div class="month-title">November 2025</div>
            <div class="calendar-grid-wrapper">
                
                <div class="calendar-day-header">Sun</div>
                <div class="calendar-day-header">Mon</div>
                <div class="calendar-day-header">Tue</div>
                <div class="calendar-day-header">Wed</div>
                <div class="calendar-day-header">Thu</div>
                <div class="calendar-day-header">Fri</div>
                <div class="calendar-day-header">Sat</div>

                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                <div></div>
                
                <?php
                    // November 2025 has 30 days. The date today is Nov 13, 2025.
                    $current_day = 13;
                    for ($i = 1; $i <= 30; $i++) {
                        $class = ($i == $current_day) ? 'calendar-day-cell today' : 'calendar-day-cell';
                        echo "<div class='{$class}'><span>{$i}</span></div>";
                    }
                ?>
            </div>
        </div>
    </main>
  </div>

<script>
  // Global Vars (Needed for functions)
  const STRAPI_API_TOKEN = "097902687e71fa3c9d71b0bd7e46a5a998ccf78fea375dd83b0ba138fd83c74818f80a3b4411b02fe72a826206f775ded3c4d86a2de908242d265a39b4e097f064a316880f796c47650f6e88c6326d1dd0df3868929c3301368caa0bd5227ffae6a991867d182f02194dad1fd717c2136b14a23b17d9c6fbe11178d04a45ad4c";

  // --- NEW FUNCTION: Load all classes for the sidebar ---
  async function loadSidebarClasses() {
    const listContainer = document.getElementById('teaching-classes-list');
    listContainer.innerHTML = '';
    const strapiURL = 'http://localhost:1337/api/courses';

    try {
        const response = await fetch(strapiURL);
        if (!response.ok) throw new Error('Could not fetch sidebar courses');
        
        const result = await response.json();
        const courses = result.data;

        courses.forEach(course => {
            const link = document.createElement('a');
            link.href = `manage-course.php?id=${course.id}`;
            link.className = 'nav-item';
            link.style.padding = '8px 24px';
            link.style.fontSize = '14px';
            link.style.fontWeight = '400';
            
            const initials = course.title.substring(0, 2).toUpperCase();
            
            link.innerHTML = `
                <div class="avatar" style="width: 24px; height: 24px; background: #666; font-size: 11px;">${initials}</div>
                <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">${course.title}</div>
            `;
            listContainer.appendChild(link);
        });
    } catch (error) {
        console.error('Error loading sidebar classes:', error);
        listContainer.innerHTML = '<p style="padding: 10px; color:red;">Failed to load classes.</p>';
    }
    
    // Set up the toggle now that content is loaded
    const header = document.getElementById('teaching-header');
    const list = document.getElementById('teaching-classes-list');
    
    // Ensure the list is collapsed by default on this page
    list.style.display = 'none';

    header.addEventListener('click', () => {
        const isCollapsed = list.style.display === 'none';
        list.style.display = isCollapsed ? 'block' : 'none';
    });
  }

  // Standard Dropdown Toggle Logic
  const profileBtn = document.getElementById('profileBtn');
  const dropdown = document.getElementById('profileDropdown');
  profileBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
  });
  document.addEventListener('click', () => dropdown.style.display = 'none');
  
  // Run functions on load
  document.addEventListener('DOMContentLoaded', loadSidebarClasses);
</script>
</body>
</html>
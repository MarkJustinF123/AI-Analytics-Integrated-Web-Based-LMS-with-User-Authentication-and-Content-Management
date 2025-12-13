<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['idToken']) || !isset($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

// User Info
$userName = isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Student';
$avatarInitials = 'U';
if (!empty($userName)) {
    $nameParts = explode(' ', $userName);
    $avatarInitials = count($nameParts) >= 2 ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1)) : strtoupper(substr($userName, 0, 2));
}

// Logout Logic
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
  <title>Student Portal — BSU</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

  <style>
    :root {
      --bsu-red: #d32f2f;
      --bsu-dark: #1a1a1a;
      --bg-light: #f4f7f6;
    }
    body {
      font-family: 'Inter', sans-serif;
      background-color: var(--bg-light);
    }

    /* --- ANIMATION KEYFRAMES --- */
    @keyframes slideUpFade {
        0% {
            opacity: 0;
            transform: translateY(30px); 
        }
        100% {
            opacity: 1;
            transform: translateY(0); 
        }
    }

    .reveal-item {
        opacity: 0; 
        animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    /* Sidebar */
    .sidebar {
      min-height: 100vh;
      background: linear-gradient(180deg, var(--bsu-red) 0%, #b71c1c 100%);
      color: white;
      transition: all 0.3s;
    }
    .sidebar .nav-link {
      color: rgba(255,255,255,0.8);
      padding: 12px 20px;
      border-radius: 8px;
      margin-bottom: 5px;
      display: flex;
      align-items: center;
      gap: 10px;
      font-weight: 500;
      cursor: pointer;
    }
    .sidebar .nav-link:hover, .sidebar .nav-link.active {
      background-color: rgba(255,255,255,0.2);
      color: white;
    }
    
    /* Sub-menu styling */
    .sidebar-submenu .nav-link {
        padding: 8px 20px 8px 45px;
        font-size: 0.9rem;
        color: rgba(255,255,255,0.6);
    }
    .sidebar-submenu .nav-link:hover {
        color: white;
        background: rgba(0,0,0,0.1);
    }

    /* Unified Brand Styling */
    .sidebar .brand, .offcanvas-header .brand-area {
      padding: 20px;
      font-size: 1.2rem;
      font-weight: 700;
      border-bottom: 1px solid rgba(255,255,255,0.1);
      margin-bottom: 20px;
      display: flex;
      align-items: center;
      gap: 10px;
      color: white;
      width: 100%;
    }
    .logo-img {
        background: white;
        border-radius: 50%;
        padding: 2px;
        width: 35px;
        height: 35px;
    }

    /* Main Content */
    .main-content { padding: 20px; }

    /* --- MOBILE NAVBAR STYLING (MATCHING ACTIVITIES PAGE) --- */
    .mobile-nav { 
        background: var(--bsu-red); 
        color: white; 
        padding: 10px 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    
    .mobile-nav .navbar-brand {
        font-weight: 700;
        font-size: 1rem;
        color: white;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    /* Cards */
    .card {
      border: none;
      border-radius: 12px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.03);
      margin-bottom: 20px;
    }
    .card-header {
      background: white;
      border-bottom: 1px solid #f0f0f0;
      font-weight: 600;
      padding: 15px 20px;
      border-radius: 12px 12px 0 0 !important;
    }
    
    /* Welcome Banner */
    .welcome-banner {
      background: linear-gradient(135deg, #2c3e50 0%, #4ca1af 100%);
      color: white;
      border-radius: 12px;
      padding: 30px;
      margin-bottom: 25px;
    }

    /* Announcement Style */
    .announcement-item {
        background: white; border-radius: 8px; padding: 15px; 
        border-left: 5px solid #ff9800; /* Orange Accent */
        box-shadow: 0 1px 4px rgba(0,0,0,0.05);
        margin-bottom: 10px;
        display: flex; gap: 15px; align-items: center;
    }
    .announcement-icon { color: #ff9800; font-size: 24px; }
    .announcement-meta { font-size: 0.75rem; color: #888; margin-top: 5px; }
    .announcement-title { font-weight: 700; font-size: 0.95rem; margin-bottom: 2px; }

    /* Widgets */
    .avatar-circle {
      width: 40px; height: 40px; background-color: var(--bsu-red); 
      color: white; border-radius: 50%; display: flex; 
      align-items: center; justify-content: center; font-weight: bold;
    }
    
    /* Calendar */
    .calendar-day { text-align: center; padding: 5px; border-radius: 5px; font-size: 0.9rem; }
    .calendar-day.today { background-color: var(--bsu-red); color: white; font-weight: bold; }
    .calendar-header { font-weight: bold; text-align: center; color: #888; font-size: 0.8rem; }

    /* Dropdown Toggle Clean */
    .user-dropdown-toggle::after { display: none; } 

    /* Course Grid */
    .course-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
        gap: 20px;
        margin-top: 15px;
    }
    .course-card {
        background: white;
        border-radius: 16px;
        border: 1px solid rgba(0,0,0,0.05);
        transition: transform 0.2s, box-shadow 0.2s;
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: 100%;
    }
    .course-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1);
    }
    .course-header-strip {
        height: 8px;
        background: var(--bsu-red);
        width: 100%;
    }
    .course-body {
        padding: 20px;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
    }
    .course-code {
        font-size: 0.75rem;
        font-weight: 700;
        color: #888;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 5px;
    }
    .course-title {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1a1a1a;
        margin-bottom: 15px;
        line-height: 1.4;
    }
    .course-footer {
        margin-top: auto;
        padding-top: 15px;
        border-top: 1px solid #f0f0f0;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .enter-btn {
        font-size: 0.85rem;
        font-weight: 600;
        color: var(--bsu-red);
        text-decoration: none;
        display: flex;
        align-items: center;
        gap: 5px;
    }
    .enter-btn:hover { text-decoration: underline; }

    /* Activity List */
    .activity-list { display: flex; flex-direction: column; gap: 10px; }
    .activity-item { display: flex; align-items: center; background: white; border: 1px solid #f0f0f0; padding: 12px; border-radius: 10px; transition: 0.2s; }
    .activity-item:hover { background: #fafafa; border-color: #e0e0e0; }
    .act-icon { width: 40px; height: 40px; border-radius: 10px; display: flex; align-items: center; justify-content: center; margin-right: 15px; flex-shrink: 0; }
    .bg-quiz { background: #e3f2fd; color: #1976d2; }
    .bg-assign { background: #fce4ec; color: #c2185b; }
    .act-info { flex-grow: 1; overflow: hidden; }
    .act-title { font-size: 0.9rem; font-weight: 600; color: #333; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .act-course { font-size: 0.75rem; color: #888; }
    .act-due { font-size: 0.8rem; font-weight: 500; color: var(--bsu-red); white-space: nowrap; margin-left: 10px;}

    /* Offcanvas Header Fixes */
    .offcanvas-header { padding: 0; } 
    .offcanvas-header .btn-close { 
        position: absolute; 
        right: 20px; 
        top: 25px; 
        filter: invert(1); /* White Close Icon */
        opacity: 1;
    }

  </style>
</head>
<body>

<nav class="navbar navbar-dark mobile-nav d-md-none">
  <div class="container-fluid">
    <div class="d-flex align-items-center gap-3">
        <button class="navbar-toggler border-0 p-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
          <span class="material-icons text-white">menu</span>
        </button>
        
        <a class="navbar-brand" href="#">
            <img src="images/bsu_logo.png" alt="Logo" width="30" height="30" class="logo-img">
            Student Portal
        </a>
    </div>
  </div>
</nav>

<div class="container-fluid">
  <div class="row">
    
    <div class="col-md-3 col-lg-2 d-none d-md-block sidebar p-0">
      <div class="brand">
        <img src="images/bsu_logo.png" alt="BSU" class="logo-img">
        <span>Student Portal</span>
      </div>
      <nav class="nav flex-column px-2">
        <a class="nav-link active" href="StudentDashboard.php"><span class="material-icons">dashboard</span> Dashboard</a>
        
        <a class="nav-link" data-bs-toggle="collapse" href="#desktopClassesSubmenu" role="button" aria-expanded="false">
            <span class="material-icons">class</span> My Classes
            <span class="material-icons ms-auto" style="font-size: 16px">keyboard_arrow_down</span>
        </a>
        <div class="collapse sidebar-submenu" id="desktopClassesSubmenu">
            <div id="sidebarClassListDesktop">
                <span class="nav-link small text-white-50">Loading...</span>
            </div>
        </div>

        <a class="nav-link" href="StudentActivities.php"><span class="material-icons">assignment</span> Activities</a>
        <a class="nav-link" href="StudentAnalytics.php"><span class="material-icons">insights</span> Performance Analytics</a>
      </nav>
    </div>

    <div class="offcanvas offcanvas-start sidebar" tabindex="-1" id="offcanvasSidebar">
      <div class="offcanvas-header">
        <div class="brand-area">
            <img src="images/bsu_logo.png" alt="BSU" class="logo-img">
            <span>Student Portal</span>
        </div>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
      </div>
      <div class="offcanvas-body p-0">
         <nav class="nav flex-column px-2">
            <a class="nav-link active" href="StudentDashboard.php"><span class="material-icons">dashboard</span> Dashboard</a>
            
            <a class="nav-link" data-bs-toggle="collapse" href="#mobileClassesSubmenu" role="button" aria-expanded="false">
                <span class="material-icons">class</span> My Classes
                <span class="material-icons ms-auto" style="font-size: 16px">keyboard_arrow_down</span>
            </a>
            <div class="collapse sidebar-submenu" id="mobileClassesSubmenu">
                <div id="sidebarClassListMobile">
                    </div>
            </div>

            <a class="nav-link" href="StudentActivities.php"><span class="material-icons">assignment</span> Activities</a>
            <a class="nav-link" href="StudentAnalytics.php"><span class="material-icons">insights</span> Performance Analytics</a>
         </nav>
      </div>
    </div>

    <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 main-content">
      
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">Dashboard</h1>
        
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none user-dropdown-toggle" id="profileDropdown" data-bs-toggle="dropdown" aria-expanded="false">
               <div class="text-end d-none d-md-block me-3">
                   <small class="text-muted d-block" style="font-size: 0.8rem;">Current User</small>
                   <span class="fw-bold text-dark"><?php echo htmlspecialchars($userName); ?></span>
               </div>
               <div class="avatar-circle shadow-sm">
                   <?php echo htmlspecialchars($avatarInitials); ?>
               </div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0" aria-labelledby="profileDropdown">
                <li><a class="dropdown-item text-danger fw-bold" href="?action=logout"><span class="material-icons align-middle me-2 fs-6">logout</span> Logout</a></li>
            </ul>
        </div>

      </div>

      <div class="welcome-banner shadow-sm reveal-item" style="animation-delay: 0s">
        <h2 class="fw-bold">Hello, <?php echo htmlspecialchars($userName); ?>! 👋</h2>
        <p class="mb-0 opacity-75">Here is what's happening with your courses today.</p>
      </div>

      <div class="row">
        
        <div class="col-lg-8">
            
            <div class="d-flex justify-content-between align-items-center mb-3 reveal-item" style="animation-delay: 0.1s">
                <h5 class="fw-bold m-0 text-dark"><span class="material-icons align-middle text-danger me-2">school</span>My Classes</h5>
                <button class="btn btn-sm btn-dark rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#enrollModal">+ Enroll New</button>
            </div>

            <div id="classListContainer" class="mb-5">
                <div class="text-center py-5 text-muted">
                    <div class="spinner-border spinner-border-sm text-danger mb-2" role="status"></div>
                    <p class="small">Loading your courses...</p>
                </div>
            </div>

            <h5 class="fw-bold mb-3 text-dark reveal-item" style="animation-delay: 0.3s"><span class="material-icons align-middle text-warning me-2">campaign</span>Announcements</h5>
            
            <div id="announcementListContainer" class="reveal-item" style="animation-delay: 0.4s">
                <div class="text-center py-5"><div class="spinner-border spinner-border-sm text-secondary"></div></div>
            </div>

        </div>

        <div class="col-lg-4">
            
            <div class="card bg-dark text-white border-0 shadow reveal-item" style="animation-delay: 0.2s">
                <div class="card-body text-center p-4">
                    <h2 id="timeDisplay" class="fw-bold mb-0 display-6">00:00</h2>
                    <small class="text-white-50" id="dateDisplay">Loading Date...</small>
                </div>
            </div>

            <div class="card reveal-item" style="animation-delay: 0.3s">
                <div class="card-header bg-white border-bottom-0 pt-3 pb-0">
                    <span class="fw-bold"><span class="material-icons fs-5 align-middle me-2 text-primary">task_alt</span>Upcoming Work</span>
                </div>
                <div class="card-body">
                    <div id="activitiesListContainer" class="activity-list">
                        <p class="text-muted small text-center py-3">No Upcoming Works!</p>
                    </div>
                </div>
            </div>

            <div class="card reveal-item" style="animation-delay: 0.4s">
                <div class="card-body">
                    <h6 class="fw-bold mb-3">Calendar</h6>
                    <div id="miniCalendar"></div>
                </div>
            </div>

        </div>
      </div>
      
      <footer class="pt-3 mt-4 text-muted border-top text-center small">
        &copy; <?php echo date('Y'); ?> Batangas State University • Student Portal
      </footer>

    </main>
  </div>
</div>

<div class="modal fade" id="enrollModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0">
        <h5 class="modal-title fw-bold">Enroll in a Class</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <p class="text-muted small">Ask your instructor for the <strong>Class Code</strong> and enter it below.</p>
        <div class="form-floating mb-3">
          <input type="text" class="form-control" id="classCodeInput" placeholder="e.g. BSU-101">
          <label for="classCodeInput">Class Code</label>
        </div>
        <div id="enrollMessage" class="alert d-none"></div>
      </div>
      <div class="modal-footer border-0">
        <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-danger" id="confirmEnrollBtn">Join Class</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  const USER_TOKEN = "<?php echo $_SESSION['idToken']; ?>";
  const authHeaders = { 
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${USER_TOKEN}` 
  };
  
  let myEnrolledCourseIds = []; 

  // --- 1. FETCH CLASSES & STORE IDs ---
  async function fetchMyClasses() {
      const gridContainer = document.getElementById('classListContainer');
      const sidebarDesktop = document.getElementById('sidebarClassListDesktop');
      const sidebarMobile = document.getElementById('sidebarClassListMobile');

      const url = `http://localhost:1337/api/users/me?populate=courses`; 

      try {
          const response = await fetch(url, { headers: authHeaders });
          if (response.status === 401) throw new Error('Session expired.');
          const user = await response.json();
          const courses = user.courses || [];
          
          // STORE IDs FOR FILTERING
          myEnrolledCourseIds = courses.map(c => (c.id || c.documentId).toString()); 

          if (courses.length === 0) {
              gridContainer.innerHTML = `<div class="text-center py-5 card border-dashed reveal-item">No Classes Enrolled</div>`;
              const emptyHtml = '<span class="nav-link small text-white-50">No classes enrolled</span>';
              sidebarDesktop.innerHTML = emptyHtml;
              sidebarMobile.innerHTML = emptyHtml;
              return;
          }

          let gridHtml = '<div class="course-grid">';
          let sidebarHtml = '';

          courses.forEach((course, index) => {
              const linkId = course.id; 
              const courseTitle = course.title || "Untitled Course";
              const delay = (index * 0.1) + 0.2;

              gridHtml += `
                <div class="course-card reveal-item" style="animation-delay: ${delay}s">
                    <div class="course-header-strip"></div>
                    <div class="course-body">
                        <div class="course-code">${course.class_code || 'NO CODE'}</div>
                        <div class="course-title">${courseTitle}</div>
                        <div class="course-footer">
                            <span class="badge bg-light text-dark border">Enrolled</span>
                            <a href="StudentViewCourse.php?courseId=${linkId}" class="enter-btn">
                                Enter Class <span class="material-icons" style="font-size:16px">arrow_forward</span>
                            </a>
                        </div>
                    </div>
                </div>
              `;

              sidebarHtml += `
                <a class="nav-link" href="StudentViewCourse.php?courseId=${linkId}">
                    <span class="material-icons" style="font-size: 14px;">chevron_right</span>
                    ${courseTitle}
                </a>
              `;
          });
          gridHtml += '</div>';
          
          gridContainer.innerHTML = gridHtml;
          sidebarDesktop.innerHTML = sidebarHtml;
          sidebarMobile.innerHTML = sidebarHtml;
          
          fetchActivities(courses);
          fetchAnnouncements(); // Call Announcement Fetcher

      } catch (error) {
          console.error(error);
          gridContainer.innerHTML = `<div class="alert alert-danger">Error loading classes. Please refresh.</div>`;
      }
  }

  // --- 2. FETCH ANNOUNCEMENTS (CLIENT-SIDE FILTER FIX) ---
  async function fetchAnnouncements() {
      const container = document.getElementById('announcementListContainer');
      try {
          // 1. FETCH ALL (To avoid 400 Bad Request from complex filters)
          const res = await fetch(`http://localhost:1337/api/announcements?populate=*&sort=createdAt:desc`, { headers: authHeaders });
          
          if (!res.ok) throw new Error('Failed to fetch');
          const json = await res.json();
          
          // 2. FILTER CLIENT-SIDE
          const filtered = json.data.filter(item => {
              const attr = item.attributes || item; // Handle v4/v5
              
              // Keep if Global (course is null)
              if (!attr.course || !attr.course.data) return true;

              // Keep if Course ID matches Enrolled ID
              const cData = attr.course.data;
              const cId = (cData.id || cData.documentId).toString();
              return myEnrolledCourseIds.includes(cId);
          });

          if (filtered.length === 0) {
              container.innerHTML = `<p class="text-muted small text-center py-3">No announcements.</p>`;
              return;
          }

          let html = '';
          filtered.slice(0, 3).forEach((item, index) => {
              const delay = (index * 0.1) + 0.4;
              const attr = item.attributes || item;
              const title = attr.title || 'Untitled Update';
              const content = attr.content || 'No message.';
              const date = new Date(attr.createdAt).toLocaleDateString();
              
              // Handle Course Name
              let courseName = 'General';
              if(attr.course && attr.course.data) {
                   const cAttr = attr.course.data.attributes || attr.course.data;
                   courseName = cAttr.title;
              }

              html += `
                <div class="announcement-item reveal-item" style="animation-delay: ${delay}s">
                    <div class="announcement-icon"><span class="material-icons">campaign</span></div>
                    <div style="flex-grow:1">
                        <div class="announcement-title">${title}</div>
                        <div class="small text-muted mb-1">${content}</div>
                        <div class="announcement-meta">${courseName} • ${date}</div>
                    </div>
                </div>`;
          });
          container.innerHTML = `<div class="activity-list">${html}</div>`;

      } catch (e) {
          console.error(e);
          container.innerHTML = `<small class="text-danger">Could not load announcements.</small>`;
      }
  }

  // ... (Keep Activities, Enroll, Utils SAME as before) ...
  async function fetchActivities(myCourses) {
      // ... (Your existing fetchActivities code here)
      const container = document.getElementById('activitiesListContainer');
      const myCourseIds = myCourses.map(c => c.id); 
      try {
          const [assignRes, quizRes] = await Promise.all([
              fetch('http://localhost:1337/api/assignments?populate=course', {headers: authHeaders}),
              fetch('http://localhost:1337/api/quizzes?populate=course', {headers: authHeaders})
          ]);
          const assigns = await assignRes.json(); const quizzes = await quizRes.json(); let allTasks = [];
          const getCId = (item) => item.course && item.course.id ? item.course.id : null;
          if(assigns.data) assigns.data.forEach(task => { if (myCourseIds.includes(getCId(task))) allTasks.push({ type: 'Assignment', title: task.title, courseName: task.course.title, date: task.duedate, icon: 'assignment', style: 'bg-assign' }); });
          if(quizzes.data) quizzes.data.forEach(task => { if (myCourseIds.includes(getCId(task))) allTasks.push({ type: 'Quiz', title: task.title, courseName: task.course.title, date: task.dueDate, icon: 'quiz', style: 'bg-quiz' }); });
          allTasks.sort((a, b) => new Date(b.date) - new Date(a.date));
          if (allTasks.length === 0) { container.innerHTML = `<p class="text-muted small text-center py-3">Hooray! No pending work.</p>`; return; }
          let html = '';
          allTasks.slice(0, 5).forEach((task, index) => {
              let dateDisplay = task.date ? new Date(task.date).toLocaleDateString('en-US', {month:'short', day:'numeric'}) : 'No Date';
              html += `<div class="activity-item reveal-item"><div class="act-icon ${task.style}"><span class="material-icons" style="font-size:20px">${task.icon}</span></div><div class="act-info"><span class="act-title">${task.title}</span><span class="act-course">${task.courseName}</span></div><div class="act-due">${dateDisplay}</div></div>`;
          });
          container.innerHTML = html;
      } catch (e) { container.innerHTML = `<small class="text-danger">Could not load tasks.</small>`; }
  }

  const enrollBtn = document.getElementById('confirmEnrollBtn');
  const classCodeInput = document.getElementById('classCodeInput');
  const enrollMessage = document.getElementById('enrollMessage');
  enrollBtn.addEventListener('click', async () => {
      const code = classCodeInput.value.trim();
      if (!code) { showMessage('Please enter a code.', 'danger'); return; }
      enrollBtn.disabled = true; enrollBtn.innerText = "Checking...";
      try {
          const findUrl = `http://localhost:1337/api/courses?filters[class_code][$eq]=${code}`;
          const findRes = await fetch(findUrl, { headers: authHeaders });
          const findData = await findRes.json();
          if (!findData.data || findData.data.length === 0) { showMessage('Invalid Class Code.', 'danger'); enrollBtn.disabled = false; enrollBtn.innerText = "Join Class"; return; }
          const courseToAdd = findData.data[0];
          const meUrl = `http://localhost:1337/api/users/me?populate=courses`;
          const meRes = await fetch(meUrl, { headers: authHeaders });
          const meData = await meRes.json();
          const currentCourseIds = meData.courses ? meData.courses.map(c => c.id) : [];
          if (currentCourseIds.includes(courseToAdd.id)) { showMessage('Already enrolled!', 'warning'); enrollBtn.disabled = false; enrollBtn.innerText = "Join Class"; return; }
          const newCourseIds = [...currentCourseIds, courseToAdd.id];
          const updateUrl = `http://localhost:1337/api/users/${meData.id}`;
          await fetch(updateUrl, { method: 'PUT', headers: authHeaders, body: JSON.stringify({ courses: newCourseIds }) });
          showMessage(`Success! Joined <strong>${courseToAdd.title}</strong>`, 'success');
          setTimeout(() => location.reload(), 1000);
      } catch (error) { console.error(error); showMessage('Error enrolling.', 'danger'); enrollBtn.disabled = false; enrollBtn.innerText = "Join Class"; }
  });
  function showMessage(msg, type) { enrollMessage.innerHTML = msg; enrollMessage.className = `alert alert-${type}`; enrollMessage.classList.remove('d-none'); }
  
  function updateTime() {
    const now = new Date();
    document.getElementById('timeDisplay').innerText = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    document.getElementById('dateDisplay').innerText = now.toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
  }
  setInterval(updateTime, 1000); updateTime();

  function buildMiniCalendar() {
    const el = document.getElementById('miniCalendar');
    const now = new Date();
    const year = now.getFullYear(), month = now.getMonth();
    const first = new Date(year, month, 1);
    const last = new Date(year, month + 1, 0);
    let html = '<div class="py-2 text-center fw-bold small">' + now.toLocaleString(undefined,{month:'long',year:'numeric'}) + '</div>';
    html += '<div class="d-grid" style="grid-template-columns: repeat(7, 1fr); gap: 2px; font-size:0.8rem">';
    ['S','M','T','W','T','F','S'].forEach(d => html += `<div class="text-center text-muted">${d}</div>`);
    for (let i=0;i<first.getDay();i++) html += '<div></div>';
    for (let d=1; d<=last.getDate(); d++){ const cls = (d === now.getDate()) ? 'bg-danger text-white rounded-circle' : ''; html += `<div class="text-center ${cls}">${d}</div>`; }
    html += '</div>';
    el.innerHTML = html;
  }
  buildMiniCalendar();
  fetchMyClasses();
</script>
</body>
</html>
<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['idToken']) || !isset($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

// Get user information
$userName = isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Instructor';
$userEmail = isset($_SESSION['email']) ? $_SESSION['email'] : '';

// Get Strapi ID
$strapiUserId = isset($_SESSION['user']['strapi_id']) ? $_SESSION['user']['strapi_id'] : null;

if (!$strapiUserId) {
    session_destroy();
    header('Location: login.php');
    exit;
}

// Avatar Initials
$avatarInitials = 'U';
if (!empty($userName)) {
    $nameParts = explode(' ', $userName);
    $avatarInitials = count($nameParts) >= 2 ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1)) : strtoupper(substr($userName, 0, 2));
}

// Logout
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
  <title>Instructor Dashboard — BSU</title>
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
      min-height: 100vh;
      overflow-x: hidden;
    }

    /* --- ANIMATION --- */
    @keyframes slideUpFade {
        0% { opacity: 0; transform: translateY(30px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .reveal-item {
        animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    /* --- SIDEBAR --- */
    .sidebar { min-height: 100vh; background: linear-gradient(180deg, var(--bsu-red) 0%, #b71c1c 100%); color: white; transition: all 0.3s; }
    .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 20px; border-radius: 8px; margin-bottom: 5px; display: flex; align-items: center; gap: 10px; font-weight: 500; cursor: pointer; transition: all 0.2s; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: rgba(255,255,255,0.2); color: white; }
    .sidebar .brand, .offcanvas-header .brand-area { padding: 20px; font-size: 1.2rem; font-weight: 700; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; color: white; width: 100%; }
    .logo-img { background: white; border-radius: 50%; padding: 2px; width: 35px; height: 35px; }

    /* Submenu (My Courses) */
    .sidebar-submenu .nav-link {
        padding: 8px 20px 8px 45px;
        font-size: 0.9rem;
        color: rgba(255,255,255,0.6);
    }
    .sidebar-submenu .nav-link:hover {
        color: white;
        background: rgba(0,0,0,0.1);
    }

    /* --- MOBILE NAV --- */
    .mobile-nav { background: var(--bsu-red); color: white; padding: 10px 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .mobile-nav .navbar-brand { color: white; font-weight: 700; font-size: 1rem; display: flex; align-items: center; gap: 10px; }

    /* --- MAIN CONTENT --- */
    .main-content { padding: 20px; padding-bottom: 100px; }
    @media (max-width: 767.98px) { .main-content { padding-top: 20px; } }

    /* --- CARDS --- */
    .card { border: none; border-radius: 12px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); margin-bottom: 20px; background: white; }
    .welcome-banner { background: linear-gradient(135deg, #2c3e50 0%, #4ca1af 100%); color: white; border-radius: 12px; padding: 30px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    
    .course-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(280px, 1fr)); gap: 20px; margin-top: 15px; }
    .course-card { background: white; border-radius: 16px; border: 1px solid rgba(0,0,0,0.05); transition: transform 0.2s, box-shadow 0.2s; overflow: hidden; display: flex; flex-direction: column; height: 100%; box-shadow: 0 2px 8px rgba(0,0,0,0.03); }
    .course-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.1); }
    .course-header-strip { height: 8px; background: var(--bsu-red); width: 100%; }
    .course-body { padding: 20px; flex-grow: 1; display: flex; flex-direction: column; }
    .course-code { font-size: 0.75rem; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 5px; }
    .course-title { font-size: 1.1rem; font-weight: 700; color: #1a1a1a; margin-bottom: 15px; line-height: 1.4; }
    .course-footer { margin-top: auto; padding-top: 15px; border-top: 1px solid #f0f0f0; display: flex; justify-content: space-between; align-items: center; }
    .enter-btn { font-size: 0.85rem; font-weight: 600; color: var(--bsu-red); text-decoration: none; display: flex; align-items: center; gap: 5px; }
    .enter-btn:hover { text-decoration: underline; }

    .user-dropdown-toggle::after { display: none; }
    .avatar-circle { width: 40px; height: 40px; background-color: var(--bsu-red); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }

    /* Offcanvas */
    .offcanvas-header { padding: 0; }
    .offcanvas-header .btn-close { position: absolute; right: 20px; top: 25px; filter: invert(1); opacity: 1; }

    /* Calendar Widget */
    .calendar-day { text-align: center; padding: 8px; border-radius: 50%; font-size: 0.9rem; width: 35px; height: 35px; display: flex; align-items: center; justify-content: center; margin: 2px auto; }
    .calendar-day.today { background-color: var(--bsu-red); color: white; font-weight: bold; }
    .calendar-header-row { font-weight: bold; text-align: center; color: #888; font-size: 0.8rem; margin-bottom: 5px; }

  </style>
</head>
<body>

<nav class="navbar navbar-dark mobile-nav d-md-none sticky-top">
  <div class="container-fluid justify-content-start align-items-center">
    <button class="navbar-toggler border-0 p-0 me-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
      <span class="material-icons text-white">menu</span>
    </button>
    <span class="navbar-brand mb-0 h1 fw-bold text-white">Instructor Portal</span>
  </div>
</nav>

<div class="container-fluid">
  <div class="row">
    
    <div class="col-md-3 col-lg-2 d-none d-md-block sidebar p-0">
      <div class="brand">
          <img src="images/bsu_logo.png" alt="BSU" class="logo-img">
          <span>Instructor Portal</span>
      </div>
      <nav class="nav flex-column px-2">
        <a class="nav-link active" href="#"><span class="material-icons">dashboard</span> Dashboard</a>
        
        <a class="nav-link" data-bs-toggle="collapse" href="#desktopCoursesSubmenu" role="button" aria-expanded="false">
            <span class="material-icons">school</span> My Courses
            <span class="material-icons ms-auto" style="font-size: 16px">keyboard_arrow_down</span>
        </a>
        <div class="collapse sidebar-submenu" id="desktopCoursesSubmenu">
            <div id="sidebarClassListDesktop">
                <span class="nav-link small text-white-50">Loading...</span>
            </div>
        </div>

        <a class="nav-link" href="PerformanceAnalytics.php"><span class="material-icons">insights</span> Performance Analytics</a>
        <a class="nav-link" href="instructor-announcements.php"><span class="material-icons">campaign</span> Announcements</a>
        
       
      </nav>
    </div>

    <div class="offcanvas offcanvas-start sidebar" tabindex="-1" id="offcanvasSidebar">
      <div class="offcanvas-header">
        <div class="brand-area">
            <img src="images/bsu_logo.png" alt="BSU" class="logo-img">
            <span>Instructor Portal</span>
        </div>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
      </div>
      <div class="offcanvas-body p-0">
         <nav class="nav flex-column px-2">
            <a class="nav-link active" href="#"><span class="material-icons">dashboard</span> Dashboard</a>
            
            <a class="nav-link" data-bs-toggle="collapse" href="#mobileCoursesSubmenu" role="button" aria-expanded="false">
                <span class="material-icons">school</span> My Courses
                <span class="material-icons ms-auto" style="font-size: 16px">keyboard_arrow_down</span>
            </a>
            <div class="collapse sidebar-submenu" id="mobileCoursesSubmenu">
                <div id="sidebarClassListMobile">
                    </div>
            </div>

            <a class="nav-link" href="PerformanceAnalytics.php"><span class="material-icons">insights</span> Performance Analytics</a>
            <a class="nav-link" href="instructor-announcements.php"><span class="material-icons">campaign</span> Announcements</a>
            
            <div class="mt-4 border-top border-white-50 pt-2">
                 <a class="nav-link text-warning" href="?action=logout"><span class="material-icons">logout</span> Logout</a>
            </div>
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
                <li><a class="dropdown-item" href="#">View Profile</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger fw-bold" href="?action=logout"><span class="material-icons align-middle me-2 fs-6">logout</span> Logout</a></li>
            </ul>
        </div>
      </div>

      <div class="row">
        
        <div class="col-lg-8">
            
            <div class="welcome-banner shadow-sm reveal-item" style="animation-delay: 0s;">
                <h2 class="fw-bold">Hello, <?php echo htmlspecialchars($userName); ?>! 👨‍🏫</h2>
                <p class="mb-0 opacity-75">Manage your courses, assignments, and student progress here.</p>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-3 reveal-item" style="animation-delay: 0.1s;">
                <h5 class="fw-bold m-0 text-dark"><span class="material-icons align-middle text-danger me-2">school</span>My Courses</h5>
                <button class="btn btn-dark rounded-pill px-3 d-flex align-items-center gap-2" id="createCourseBtn">
                    <span class="material-icons" style="font-size: 18px;">add</span> Create New
                </button>
            </div>

            <div id="courseGridContainer" class="mb-5">
                <div class="text-center py-5">
                    <div class="spinner-border text-danger" role="status"></div>
                    <p class="text-muted mt-2">Loading your courses...</p>
                </div>
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
                <div class="card-body">
                    <h6 class="fw-bold mb-3 border-bottom pb-2">Calendar</h6>
                    <div id="miniCalendar"></div>
                </div>
            </div>

        </div>

      </div>

    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  const STRAPI_API_TOKEN = "097902687e71fa3c9d71b0bd7e46a5a998ccf78fea375dd83b0ba138fd83c74818f80a3b4411b02fe72a826206f775ded3c4d86a2de908242d265a39b4e097f064a316880f796c47650f6e88c6326d1dd0df3868929c3301368caa0bd5227ffae6a991867d182f02194dad1fd717c2136b14a23b17d9c6fbe11178d04a45ad4c";

  const currentInstructorId = <?php echo json_encode($strapiUserId); ?>;
  const currentInstructorName = "<?php echo htmlspecialchars($userName); ?>";

  const authHeaders = { 
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${STRAPI_API_TOKEN}` 
  };

  // --- 1. CREATE COURSE ---
  document.getElementById('createCourseBtn').addEventListener('click', async () => {
    const title = prompt("Please enter a title for the new course:");
    if (!title) return; 

    const strapiURL = 'http://localhost:1337/api/courses';
    
    const courseData = {
      data: {
        title: title,
        description: "New course created from the dashboard",
        instructor_name: currentInstructorName, 
        instructor: currentInstructorId, 
        publishedAt: new Date()
      }
    };

    try {
      const response = await fetch(strapiURL, {
        method: 'POST',
        headers: authHeaders,
        body: JSON.stringify(courseData)
      });

      if (!response.ok) throw new Error('Could not create course');

      alert(`Successfully created course: "${title}"`);
      fetchAndDisplayCourses(); 
      
    } catch (error) {
      console.error(error);
      alert("Error creating course.");
    }
  });

  // --- 2. FETCH COURSES ---
  async function fetchAndDisplayCourses() {
    const strapiURL = `http://localhost:1337/api/courses?filters[instructor][id][$eq]=${currentInstructorId}&populate=*`;
    const gridContainer = document.getElementById('courseGridContainer');
    
    // Sidebar Lists
    const sidebarListDesktop = document.getElementById('sidebarClassListDesktop');
    const sidebarListMobile = document.getElementById('sidebarClassListMobile');
    
    try {
      const response = await fetch(strapiURL, { method: 'GET', headers: authHeaders }); 
      if (!response.ok) throw new Error('Could not fetch courses');
      
      const result = await response.json();
      const courses = result.data;

      if (!courses || courses.length === 0) {
        gridContainer.innerHTML = `
            <div class="text-center py-5 border rounded bg-white reveal-item">
                <span class="material-icons fs-1 text-muted">school</span>
                <p class="text-muted mt-2">You haven't created any courses yet.</p>
                <p class="small text-secondary">Click "Create New" above to get started.</p>
            </div>`;
        
        sidebarListDesktop.innerHTML = '<span class="nav-link small text-white-50">No courses yet</span>';
        sidebarListMobile.innerHTML = '<span class="nav-link small text-white-50">No courses yet</span>';
        return;
      }

      let gridHtml = '<div class="course-grid">';
      let sidebarHtml = '';
      
      courses.forEach((course, index) => {
          const delay = (index * 0.1) + 0.2;
          const linkId = course.documentId || course.id;
          const title = course.title || "Untitled Course";
          const code = course.class_code || "NO CODE";

          // Grid
          gridHtml += `
            <div class="course-card reveal-item" style="animation-delay: ${delay}s">
                <div class="course-header-strip"></div>
                <div class="course-body">
                    <div class="course-code">${code}</div>
                    <div class="course-title">${title}</div>
                    <div class="course-footer">
                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-10">Active</span>
                        <a href="manage-course.php?id=${linkId}" class="enter-btn">
                            Manage <span class="material-icons" style="font-size:16px">settings</span>
                        </a>
                    </div>
                </div>
            </div>
          `;

          // Sidebar
          sidebarHtml += `
            <a href="manage-course.php?id=${linkId}" class="nav-link">
                <span class="material-icons" style="font-size: 14px;">chevron_right</span>
                ${title}
            </a>
          `;
      });
      
      gridHtml += '</div>';
      gridContainer.innerHTML = gridHtml;
      
      sidebarListDesktop.innerHTML = sidebarHtml;
      sidebarListMobile.innerHTML = sidebarHtml;

    } catch (error) {
      console.error(error);
      gridContainer.innerHTML = `<div class="alert alert-danger">Error loading courses. Check console.</div>`;
    }
  }
  
  // --- 3. UTILS ---
  function updateTime() {
    const now = new Date();
    document.getElementById('timeDisplay').innerText = now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    document.getElementById('dateDisplay').innerText = now.toLocaleDateString(undefined, { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
  }
  
  function buildMiniCalendar() {
    const el = document.getElementById('miniCalendar');
    const now = new Date();
    const year = now.getFullYear(), month = now.getMonth();
    const first = new Date(year, month, 1);
    const last = new Date(year, month + 1, 0);
    const monthName = now.toLocaleString(undefined, {month:'long', year:'numeric'});
    
    let html = `<div class="text-center fw-bold small mb-2">${monthName}</div>`;
    html += '<div class="d-grid" style="grid-template-columns: repeat(7, 1fr); gap: 2px;">';
    ['S','M','T','W','T','F','S'].forEach(d => html += `<div class="calendar-header-row">${d}</div>`);
    for (let i=0; i<first.getDay(); i++) html += '<div></div>';
    for (let d=1; d<=last.getDate(); d++){
      const cls = (d === now.getDate()) ? 'today' : '';
      html += `<div class="calendar-day ${cls}">${d}</div>`;
    }
    html += '</div>';
    el.innerHTML = html;
  }

  setInterval(updateTime, 1000);
  updateTime();
  
  document.addEventListener('DOMContentLoaded', () => {
    fetchAndDisplayCourses();
    buildMiniCalendar();
  });

</script>

</body>
</html>
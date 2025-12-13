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
  <title>My Activities — BSU</title>
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

    /* --- ANIMATION KEYFRAMES --- */
    @keyframes slideUpFade {
        0% { opacity: 0; transform: translateY(30px); }
        100% { opacity: 1; transform: translateY(0); }
    }

    .reveal-item {
        animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    /* --- SIDEBAR STYLING --- */
    .sidebar { min-height: 100vh; background: linear-gradient(180deg, var(--bsu-red) 0%, #b71c1c 100%); color: white; transition: all 0.3s; }
    .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 20px; border-radius: 8px; margin-bottom: 10px; display: flex; align-items: center; gap: 10px; font-weight: 500; cursor: pointer; transition: all 0.2s ease; }
    .sidebar .nav-link:hover { background-color: rgba(255,255,255,0.15); color: white; transform: translateX(5px); box-shadow: 0 4px 8px rgba(0,0,0,0.1); }
    .sidebar .nav-link.active { background-color: rgba(255,255,255,0.25); color: white; font-weight: 700; box-shadow: 0 4px 12px rgba(0,0,0,0.2); }
    .sidebar .nav-link:active { transform: scale(0.96); background-color: rgba(255,255,255,0.4); }

    /* Shared Brand Styling (Desktop & Mobile Sidebar) */
    .sidebar .brand, .offcanvas-header .brand { 
      padding: 20px; font-size: 1.2rem; font-weight: 700; 
      border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; 
      display: flex; align-items: center; gap: 10px; color: white;
    }
    .logo-img { background: white; border-radius: 50%; padding: 2px; width: 35px; height: 35px; }

    /* Main Content */
    .main-content { padding: 20px; padding-bottom: 100px; }
    
    /* --- MOBILE NAVBAR STYLING --- */
    .mobile-nav {
        background-color: var(--bsu-red);
        color: white;
        padding: 10px 15px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    .mobile-nav .navbar-brand {
        color: white;
        font-weight: 700;
        font-size: 1.1rem;
    }

    /* --- Activity Page Specific Styles --- */
    .course-section { margin-bottom: 60px; animation: slideUpFade 0.5s ease-in-out both; }
    .course-header { background: white; padding: 15px 20px; border-radius: 10px; border-left: 5px solid var(--bsu-red); box-shadow: 0 2px 5px rgba(0,0,0,0.05); margin-bottom: 20px; display: flex; justify-content: space-between; align-items: center; }
    .course-title { font-weight: 700; font-size: 1.1rem; color: #333; margin: 0; }
    
    .task-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(300px, 1fr)); gap: 20px; }
    @media (max-width: 576px) { .task-grid { grid-template-columns: 1fr; } }

    .task-card { background: white; border: 1px solid #e0e0e0; border-radius: 12px; padding: 20px; transition: transform 0.2s, box-shadow 0.2s; overflow: hidden; }
    .task-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.1); border-color: var(--bsu-red); }
    
    .type-badge { display: inline-block; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; margin-bottom: 10px; }
    .badge-assignment { background: #e3f2fd; color: #1565c0; }
    .badge-quiz { background: #fce4ec; color: #c2185b; }

    .task-title { font-weight: 700; font-size: 1rem; margin-bottom: 5px; color: #222; }
    .task-meta { font-size: 0.85rem; color: #666; display: flex; align-items: center; gap: 5px; margin-bottom: 15px; }
    .btn-task { width: 100%; border-radius: 8px; font-weight: 600; font-size: 0.9rem; }
    
    /* --- ACTIVE TAB STYLING --- */
    .btn-check:checked + .btn-outline-danger {
        background-color: var(--bsu-red) !important;
        color: white !important;
        border-color: var(--bsu-red) !important;
        box-shadow: 0 4px 10px rgba(211, 47, 47, 0.3);
    }

    /* --- HEADER LAYOUT FIXES --- */
    .header-wrapper {
        border-bottom: 1px solid #e0e0e0;
        padding-bottom: 15px;
        margin-bottom: 30px;
        position: relative;
    }

    .header-main-area {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap; 
        margin-bottom: 5px;
    }

    /* Desktop Controls (Only Filters now) */
    .header-controls-desktop {
        display: flex;
        align-items: center;
        gap: 15px;
        margin-left: auto; 
    }
    
    /* Mobile Layout */
    @media (max-width: 768px) {
        .header-content-wrapper { padding-bottom: 15px; }

        /* 1. Tabs: Flow naturally (relative), centered, with top margin */
        .filter-group-mobile {
            position: relative; 
            width: 100%; 
            justify-content: center !important; 
            margin-top: 20px; 
        }

        /* 2. Title: Centered */
        .main-title-area { 
             text-align: center;
             width: 100%;
             padding-top: 10px; 
             margin-bottom: 0;
        }
        
        .header-controls-desktop { display: none !important; }
        .filter-group-mobile { display: flex !important; }
    }

    @media (min-width: 769px) {
        .filter-group-mobile { display: none !important; }
    }
    
    /* Fix Offcanvas Header padding/margin to match Desktop Brand */
    .offcanvas-header { padding: 0; }
    .offcanvas-header .brand { width: 100%; margin-bottom: 0; border-bottom: none; }
    .offcanvas-header .btn-close { 
        position: absolute; 
        right: 15px; 
        top: 22px; 
        z-index: 10; 
        filter: invert(1); /* Make close button white */
    }
  </style>
</head>
<body>

<nav class="navbar navbar-dark mobile-nav d-md-none fixed-top">
  <div class="container-fluid">
    <div class="d-flex align-items-center gap-3">
        <button class="navbar-toggler border-0 p-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
          <span class="material-icons text-white">menu</span>
        </button>
        <span class="navbar-brand mb-0 h1">Student Portal</span>
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
        <a class="nav-link" href="StudentDashboard.php"><span class="material-icons">dashboard</span> Dashboard</a>
        <a class="nav-link active" href="#"><span class="material-icons">assignment</span> Activities</a>
      </nav>
    </div>

    <div class="offcanvas offcanvas-start sidebar" tabindex="-1" id="offcanvasSidebar">
      <div class="offcanvas-header">
        <div class="brand">
            <img src="images/bsu_logo.png" alt="BSU" class="logo-img">
            <span>Student Portal</span>
        </div>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
      </div>
      <div class="offcanvas-body p-0">
         <nav class="nav flex-column px-2">
            <a class="nav-link" href="StudentDashboard.php"><span class="material-icons">dashboard</span> Dashboard</a>
            <a class="nav-link active" href="#"><span class="material-icons">assignment</span> Activities</a>
         </nav>
      </div>
    </div>

    <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 py-4 mt-5 mt-md-0">
      
      <div class="header-wrapper">
        
        <div class="header-main-area">
            
            <div class="main-title-area">
                <h2 class="fw-bold m-0 reveal-item" style="animation-delay: 0s;">My Activities</h2>
            </div>
            
            <div class="header-controls-desktop">
                <div class="btn-group btn-group-custom" role="group">
                    <input type="radio" class="btn-check" name="filter_d" id="filterAll_d" checked>
                    <label class="btn btn-outline-danger" for="filterAll_d">All</label>

                    <input type="radio" class="btn-check" name="filter_d" id="filterAssign_d">
                    <label class="btn btn-outline-danger" for="filterAssign_d">Assignments</label>

                    <input type="radio" class="btn-check" name="filter_d" id="filterQuiz_d">
                    <label class="btn btn-outline-danger" for="filterQuiz_d">Quizzes</label>
                </div>
            </div>
            
        </div>
        
        <div class="btn-group btn-group-custom filter-group-mobile" role="group">
            <input type="radio" class="btn-check" name="filter_m" id="filterAll_m" checked>
            <label class="btn btn-outline-danger" for="filterAll_m">All</label>

            <input type="radio" class="btn-check" name="filter_m" id="filterAssign_m">
            <label class="btn btn-outline-danger" for="filterAssign_m">Assignments</label>

            <input type="radio" class="btn-check" name="filter_m" id="filterQuiz_m">
            <label class="btn btn-outline-danger" for="filterQuiz_m">Quizzes</label>
        </div>

      </div> <div id="activitiesContainer" style="min-height: 50vh;">
          <div class="text-center py-5">
              <div class="spinner-border text-danger" role="status"></div>
              <p class="mt-2 text-muted">Loading your tasks...</p>
          </div>
      </div>

    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  const USER_TOKEN = "<?php echo $_SESSION['idToken']; ?>";
  const authHeaders = { 
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${USER_TOKEN}` 
  };
  
  function applyReveal() {
      const allReveals = document.querySelectorAll('[data-reveal]');
      allReveals.forEach((element, index) => {
          const delay = (index * 0.1) + 0.1;
          element.style.animationDelay = `${delay}s`;
          element.classList.add('reveal-item');
      });
  }

  async function loadActivitiesPage() {
      const container = document.getElementById('activitiesContainer');
      
      try {
          // 1. Get My Enrolled Courses
          const userRes = await fetch(`http://localhost:1337/api/users/me?populate=courses`, { headers: authHeaders });
          const userData = await userRes.json();
          const myCourses = userData.courses || [];
          
          const myCourseMap = {};
          myCourses.forEach(c => { myCourseMap[c.id] = c; });
          const myCourseIds = Object.keys(myCourseMap).map(Number);

          if (myCourses.length === 0) {
              container.innerHTML = `<div class="alert alert-warning" data-reveal>You are not enrolled in any courses yet.</div>`;
              applyReveal();
              return;
          }

          // 2. Fetch Tasks
          const [assignRes, quizRes] = await Promise.all([
              fetch('http://localhost:1337/api/assignments?populate=course', {headers: authHeaders}),
              fetch('http://localhost:1337/api/quizzes?populate=course', {headers: authHeaders})
          ]);

          const assigns = await assignRes.json();
          const quizzes = await quizRes.json();

          let groupedTasks = {};

          const processTask = (task, type) => {
              if (!task.course || !myCourseIds.includes(task.course.id)) return; 
              
              const courseID = task.course.id;
              const courseName = task.course.title;
              const courseCode = task.course.class_code || "GEN";
              
              if (!groupedTasks[courseName]) {
                  groupedTasks[courseName] = {
                      code: courseCode,
                      courseId: courseID, 
                      tasks: []
                  };
              }

              const taskObj = {
                  id: task.id,
                  documentId: task.documentId || task.id,
                  title: task.title,
                  dueDate: type === 'Quiz' ? task.dueDate : task.duedate,
                  type: type,
                  points: task.total_points || (task.points || 100),
                  courseId: courseID 
              };

              groupedTasks[courseName].tasks.push(taskObj);
          };

          if(assigns.data) assigns.data.forEach(t => processTask(t, 'Assignment'));
          if(quizzes.data) quizzes.data.forEach(t => processTask(t, 'Quiz'));

          window.groupedTasksGlobal = groupedTasks;
          renderFilteredTasks('All');

      } catch (error) {
          console.error(error);
          container.innerHTML = `<div class="alert alert-danger">Error loading data.</div>`;
      }
  }

  function renderFilteredTasks(filterType) {
      const container = document.getElementById('activitiesContainer');
      const groupedTasks = window.groupedTasksGlobal || {};

      if (Object.keys(groupedTasks).length === 0) {
          container.innerHTML = `<div class="text-center py-5" data-reveal>... No pending activities found.</div>`;
          applyReveal();
          return;
      }

      let html = '';

      for (const [courseName, data] of Object.entries(groupedTasks)) {
          const visibleTasks = data.tasks.filter(t => filterType === 'All' || t.type === filterType);
          if (visibleTasks.length === 0) continue; 

          html += `
            <div class="course-section filter-item" data-reveal>
                <div class="course-header">
                    <h3 class="course-title">${courseName}</h3>
                    <span class="badge bg-dark">${data.code}</span>
                </div>
                <div class="task-grid">
          `;
          
          visibleTasks.forEach(task => {
              const badgeClass = task.type === 'Quiz' ? 'badge-quiz' : 'badge-assignment';
              const icon = task.type === 'Quiz' ? 'timer' : 'upload_file';
              const btnClass = task.type === 'Quiz' ? 'btn-outline-danger' : 'btn-outline-primary';
              const dateStr = task.dueDate ? new Date(task.dueDate).toLocaleDateString() : 'No Due Date';
              
              const link = task.type === 'Quiz' 
                  ? `student-view-quiz.php?id=${task.documentId}&courseId=${task.courseId}` 
                  : `student-view-assignment.php?id=${task.documentId}&courseId=${task.courseId}`;
              
              html += `
                <div class="task-card task-item" data-type="${task.type}" data-reveal>
                    <div class="type-badge ${badgeClass}">
                        <span class="material-icons" style="font-size:12px; vertical-align:text-bottom">${icon}</span> ${task.type}
                    </div>
                    <div class="task-title">${task.title}</div>
                    <div class="task-meta">
                        <span class="material-icons" style="font-size:14px">event</span> Due: ${dateStr}
                    </div>
                     <div class="task-meta">
                        <span class="material-icons" style="font-size:14px">star</span> Points: ${task.points}
                    </div>
                    <a href="${link}" class="btn btn-sm ${btnClass} btn-task">Open ${task.type}</a>
                </div>
              `;
          });
          html += `</div></div>`;
      }
      
      if(html === '') html = `<div class="text-center py-5 text-muted" data-reveal>No ${filterType}s found.</div>`;
      container.innerHTML = html;
      applyReveal();
  }

  function setupFilters() {
      const radios = document.querySelectorAll('input[type=radio]');
      
      radios.forEach(radio => {
          radio.addEventListener('change', (e) => {
              const id = e.target.id;
              let type = 'All';
              
              if(id.includes('Assign')) type = 'Assignment';
              if(id.includes('Quiz')) type = 'Quiz';

              if(type === 'All') {
                  document.getElementById('filterAll_d').checked = true;
                  document.getElementById('filterAll_m').checked = true;
              } else if (type === 'Assignment') {
                  document.getElementById('filterAssign_d').checked = true;
                  document.getElementById('filterAssign_m').checked = true;
              } else {
                  document.getElementById('filterQuiz_d').checked = true;
                  document.getElementById('filterQuiz_m').checked = true;
              }
              
              renderFilteredTasks(type);
          });
      });
  }

  setupFilters();
  loadActivitiesPage();
</script>
</body>
</html>
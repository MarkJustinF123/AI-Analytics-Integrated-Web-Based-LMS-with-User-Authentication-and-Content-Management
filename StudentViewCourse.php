<?php
session_start();

// 1. Logout Logic
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: login.php'); 
    exit;
}

// 2. Security Check
if (!isset($_SESSION['idToken']) || !isset($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

// 3. User Info Setup
$userName = isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Student';
$userEmail = isset($_SESSION['email']) ? $_SESSION['email'] : ''; 
$userId = isset($_SESSION['user']['strapi_id']) ? $_SESSION['user']['strapi_id'] : null;
$avatarInitials = 'U';
if (!empty($userName)) {
    $nameParts = explode(' ', $userName);
    $avatarInitials = count($nameParts) >= 2 ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1)) : strtoupper(substr($userName, 0, 2));
}

// 4. Get Course ID
$course_id = isset($_GET['courseId']) ? htmlspecialchars($_GET['courseId']) : '';

if (empty($course_id)) {
    echo "Error: No course specified.";
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Course View — BSU</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

  <style>
    :root { --bsu-red: #d32f2f; --bg-light: #f4f7f6; }
    body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); }

    /* Sidebar */
    .sidebar { min-height: 100vh; background: linear-gradient(180deg, var(--bsu-red) 0%, #b71c1c 100%); color: white; }
    .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 20px; margin-bottom: 5px; display: flex; align-items: center; gap: 10px; cursor: pointer; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: rgba(255,255,255,0.2); color: white; border-radius: 8px;}
    
    /* Unified Brand Styling */
    .sidebar .brand, .offcanvas-header .brand-area { 
        padding: 20px; font-size: 1.2rem; font-weight: 700; 
        border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; 
        display: flex; align-items: center; gap: 10px; color: white;
        width: 100%;
    }
    .logo-img { background: white; border-radius: 50%; padding: 2px; width: 35px; height: 35px; }
    
    /* Mobile Nav */
    .mobile-nav { background: var(--bsu-red); color: white; padding: 10px 15px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }

    /* Main Content Padding */
    .main-content { padding: 20px; }
    
    /* FIX: Adjusted padding for mobile to reduce gap */
    @media (max-width: 767.98px) {
        .main-content {
            padding-top: 20px; /* Standard padding */
        }
    }

    .user-dropdown-toggle::after { display: none; }
    .avatar-circle { width: 40px; height: 40px; background-color: var(--bsu-red); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }

    /* Course Banner */
    .course-hero {
        background: linear-gradient(135deg, #1e1e1e 0%, #3a3a3a 100%);
        color: white;
        border-radius: 12px;
        padding: 40px;
        margin-bottom: 30px;
        position: relative;
        overflow: hidden;
    }
    .course-hero::after {
        content: '📚';
        font-size: 150px;
        position: absolute;
        right: -20px;
        bottom: -40px;
        opacity: 0.1;
        transform: rotate(-15deg);
    }
    
    /* Filter Tabs */
    .filter-btn {
        border: 1px solid #e0e0e0;
        background: white;
        color: #555;
        padding: 8px 16px;
        border-radius: 20px;
        font-size: 0.9rem;
        margin-right: 8px;
        transition: all 0.2s;
    }
    .filter-btn.active, .filter-btn:hover {
        background: var(--bsu-red);
        color: white;
        border-color: var(--bsu-red);
    }

    /* Content Cards */
    .content-card {
        background: white;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
        border-left: 5px solid transparent;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
        transition: transform 0.2s;
        cursor: pointer;
        text-decoration: none;
        color: inherit;
        display: block;
        opacity: 1; 
    }
    .content-card:hover { transform: translateX(5px); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    
    /* Tap Animation Trigger */
    .content-card.tapped { animation: tappedScale 0.15s ease-out forwards; }
    @keyframes tappedScale {
      0% { transform: scale(1); box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
      50% { transform: scale(0.98); background-color: #ffe0e0; box-shadow: 0 0 15px var(--bsu-red); }
      100% { transform: scale(1); background-color: white; box-shadow: 0 4px 12px rgba(0,0,0,0.08); }
    }

    /* Type Indicators */
    .type-lesson { border-left-color: #3b82f6; } 
    .type-assignment { border-left-color: #d32f2f; } 
    .type-quiz { border-left-color: #9333ea; } 

    .icon-box {
        width: 40px; height: 40px; border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        margin-right: 15px;
    }
    .bg-light-blue { background: #eef2ff; color: #3b82f6; }
    .bg-light-red { background: #fef2f2; color: #d32f2f; }
    .bg-light-purple { background: #f3e8ff; color: #9333ea; }

    /* Instructor Sidebar Card */
    .instructor-card {
        background: white;
        border-radius: 12px;
        padding: 30px 20px;
        text-align: center;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03);
    }
    .instructor-avatar {
        width: 80px; height: 80px; 
        background-color: var(--bsu-red); color: white;
        border-radius: 50%; font-size: 2rem; font-weight: 700;
        display: flex; align-items: center; justify-content: center;
        margin: 0 auto 15px auto;
    }

    /* Roster Styles */
    .roster-card {
        background: white;
        border-radius: 12px;
        padding: 15px;
        margin-bottom: 15px;
        box-shadow: 0 2px 5px rgba(0,0,0,0.05);
        display: flex;
        align-items: center;
        transition: transform 0.2s;
    }
    .roster-card:hover { transform: translateY(-3px); }

    /* Entrance Animation */
    @keyframes slideUpFade {
        0% { opacity: 0; transform: translateY(30px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .reveal-item {
        animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) both;
    }

    /* Offcanvas Fixes */
    .offcanvas-header { padding: 0; } 
    .offcanvas-header .btn-close { 
        position: absolute; 
        right: 20px; 
        top: 25px; 
        filter: invert(1); /* White Close Icon */
        opacity: 1;
    }
    
    /* Grade Item */
    .grade-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 15px;
        border-bottom: 1px solid #eee;
    }
    .grade-item:last-child { border-bottom: none; }
    .grade-score { font-weight: 700; color: var(--bsu-red); font-size: 1.1rem; }
    
  </style>
</head>
<body>

<nav class="navbar navbar-dark mobile-nav d-md-none sticky-top">
  <div class="container-fluid justify-content-start align-items-center">
    
    <button class="navbar-toggler border-0 p-0 me-3" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
      <span class="material-icons text-white">menu</span>
    </button>
    
    <span class="navbar-brand mb-0 h1 fw-bold text-white">Student Portal</span>
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
        <div class="my-3 border-top border-light opacity-25"></div>
        
        <a class="nav-link active" id="nav-course-home" href="#" onclick="switchView(event, 'home')">
            <span class="material-icons">class</span> Course Home
        </a>
        
        <a class="nav-link" id="nav-classmates" href="#" onclick="switchView(event, 'classmates')">
            <span class="material-icons">people</span> Classmates
        </a>
        
        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#gradesModal" onclick="fetchGrades()">
            <span class="material-icons">grade</span> My Grades
        </a>
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
            <a class="nav-link" href="StudentDashboard.php"><span class="material-icons">dashboard</span> Dashboard</a>
            
            <a class="nav-link active" id="nav-mobile-course-home" href="#" onclick="switchView(event, 'home')" data-bs-dismiss="offcanvas">
                <span class="material-icons">class</span> Course Home
            </a>
            <a class="nav-link" id="nav-mobile-classmates" href="#" onclick="switchView(event, 'classmates')" data-bs-dismiss="offcanvas">
                <span class="material-icons">people</span> Classmates
            </a>
            
            <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#gradesModal" onclick="fetchGrades()" data-bs-dismiss="offcanvas">
                <span class="material-icons">grade</span> My Grades
            </a>
         </nav>
      </div>
    </div>

    <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 main-content">
      
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2" id="pageTitle">Course Overview</h1>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none user-dropdown-toggle" id="profileDropdown" data-bs-toggle="dropdown">
               <div class="text-end d-none d-md-block me-3"><span class="fw-bold text-dark"><?php echo htmlspecialchars($userName); ?></span></div>
               <div class="avatar-circle shadow-sm"><?php echo htmlspecialchars($avatarInitials); ?></div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                <li><a class="dropdown-item text-danger" href="?action=logout">Logout</a></li>
            </ul>
        </div>
      </div>

      <div id="view-course-home">
          <div class="course-hero shadow">
            <span class="badge bg-danger mb-2" id="courseCodeDisplay">Loading...</span>
            <h1 class="fw-bold display-6" id="courseTitleDisplay">Loading Course...</h1>
            <p class="mb-0 opacity-75" id="courseDescDisplay">Please wait while we fetch the details.</p>
          </div>

          <div class="row">
            <div class="col-lg-8">
                <div class="d-flex mb-4 overflow-auto">
                    <button class="filter-btn active" onclick="filterContent('all', event)">All Content</button>
                    <button class="filter-btn" onclick="filterContent('Lesson', event)">Lessons</button>
                    <button class="filter-btn" onclick="filterContent('Assignment', event)">Assignments</button>
                    <button class="filter-btn" onclick="filterContent('Quiz', event)">Quizzes</button>
                </div>

                <div id="courseContentFeed">
                    <div class="text-center py-5">
                        <div class="spinner-border text-danger" role="status"></div>
                        <p class="text-muted mt-2">Loading learning materials...</p>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="instructor-card mb-4">
                    <div class="instructor-avatar shadow-sm" id="instructorAvatar">IN</div>
                    <h5 class="fw-bold mb-1" id="instructorNameDisplay">Course Instructor</h5>
                    <p class="text-muted small">Course Facilitator</p>
                    
                </div>
            </div>
          </div>
      </div>

      <div id="view-classmates" style="display:none;">
          <div class="row" id="classmatesList">
              <div class="text-center py-5">
                  <div class="spinner-border text-danger" role="status"></div>
                  <p class="text-muted mt-2">Loading classmates...</p>
              </div>
          </div>
      </div>

      <footer class="pt-3 mt-4 text-muted border-top text-center small">&copy; <?php echo date('Y'); ?> Batangas State University</footer>

    </main>
  </div>
</div>

<div class="modal fade" id="gradesModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
    <div class="modal-content border-0 shadow">
      <div class="modal-header border-bottom-0">
        <h5 class="modal-title fw-bold"><span class="material-icons align-middle text-danger me-2">grade</span>My Grades</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body p-0">
        <div id="gradesListContainer">
            <div class="text-center py-5">
                <div class="spinner-border text-secondary spinner-border-sm"></div>
                <p class="small text-muted mt-2">Fetching scores...</p>
            </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  const USER_TOKEN = "<?php echo $_SESSION['idToken']; ?>";
  const COURSE_ID_INPUT = "<?php echo $course_id; ?>"; 
  const CURRENT_USER_EMAIL = "<?php echo $userEmail; ?>";
  const USER_ID = <?php echo json_encode($userId); ?>;
  
  let COURSE_INSTRUCTOR_EMAIL = null;

  const authHeaders = { 
      'Content-Type': 'application/json',
      'Authorization': `Bearer ${USER_TOKEN}` 
  };

  let allContentItems = [];

  /* --- VIEW SWITCHER --- */
  function switchView(event, viewName) {
      if(event) event.preventDefault();
      document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));
      
      const homeView = document.getElementById('view-course-home');
      const classmatesView = document.getElementById('view-classmates');
      const pageTitle = document.getElementById('pageTitle');

      if (viewName === 'home') {
          homeView.style.display = 'block';
          classmatesView.style.display = 'none';
          pageTitle.innerText = 'Course Overview';
          
          document.getElementById('nav-course-home').classList.add('active');
          const mob = document.getElementById('nav-mobile-course-home');
          if(mob) mob.classList.add('active');

      } else if (viewName === 'classmates') {
          homeView.style.display = 'none';
          classmatesView.style.display = 'block';
          pageTitle.innerText = 'Classmates';
          
          document.getElementById('nav-classmates').classList.add('active');
          const mob = document.getElementById('nav-mobile-classmates');
          if(mob) mob.classList.add('active');
          
          if(COURSE_INSTRUCTOR_EMAIL) loadClassmates();
          else loadCourseDetails().then(() => loadClassmates());
      }
      
      const offcanvasEl = document.getElementById('offcanvasSidebar');
      const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
      if (bsOffcanvas) bsOffcanvas.hide();
  }

  /* --- DATA FETCHING --- */
  async function loadCourseDetails() {
      try {
          const response = await fetch(`http://localhost:1337/api/courses?populate=instructor`, { headers: authHeaders });
          const result = await response.json();
          const courseData = result.data.find(c => c.documentId === COURSE_ID_INPUT || c.id.toString() === COURSE_ID_INPUT);

          if (!courseData) {
              document.getElementById('courseTitleDisplay').innerText = "Course Not Found";
              document.getElementById('courseContentFeed').innerHTML = "";
              return;
          }

          const course = courseData.attributes || courseData;
          document.getElementById('courseTitleDisplay').innerText = course.title || 'Untitled';
          document.getElementById('courseCodeDisplay').innerText = course.class_code || 'CODE';
          document.getElementById('courseDescDisplay').innerText = course.description || 'Welcome to the course!';

          if (course.instructor && course.instructor.data) {
               const instr = course.instructor.data.attributes || course.instructor.data;
               document.getElementById('instructorNameDisplay').innerText = instr.username;
               document.getElementById('instructorAvatar').innerText = instr.username.substring(0,2).toUpperCase();
               COURSE_INSTRUCTOR_EMAIL = instr.email;
          } else if (course.instructor_name) {
               document.getElementById('instructorNameDisplay').innerText = course.instructor_name;
               document.getElementById('instructorAvatar').innerText = course.instructor_name.substring(0,2).toUpperCase();
          }
      } catch (error) { console.error(error); }
  }

  async function loadCourseContent() {
      const feed = document.getElementById('courseContentFeed');
      try {
          const [lessonRes, assignRes, quizRes] = await Promise.all([
              fetch('http://localhost:1337/api/lessons?populate=course', {headers: authHeaders}),
              fetch('http://localhost:1337/api/assignments?populate=course', {headers: authHeaders}),
              fetch('http://localhost:1337/api/quizzes?populate=course', {headers: authHeaders})
          ]);

          const lessons = await lessonRes.json();
          const assigns = await assignRes.json();
          const quizzes = await quizRes.json();

          allContentItems = [];

          const normalize = (item) => item.attributes ? { ...item.attributes, id: item.id, documentId: item.documentId || item.attributes.documentId } : item;
          
          const belongsToCourse = (item) => {
              if (!item || !item.course) return false;
              const c = item.course;
              const target = COURSE_ID_INPUT.toString();
              if (c.id && c.id.toString() === target) return true;
              if (c.documentId && c.documentId === target) return true;
              if (c.data) {
                  if (c.data.id && c.data.id.toString() === target) return true;
                  if (c.data.documentId && c.data.documentId === target) return true;
              }
              return false;
          };

          if (lessons.data) lessons.data.map(normalize).forEach(i => {
              if (belongsToCourse(i)) allContentItems.push(formatItem(i, 'Lesson', 'bookmark', 'bg-light-blue', 'type-lesson', `student-view-lesson.php`));
          });
          if (assigns.data) assigns.data.map(normalize).forEach(i => {
              if (belongsToCourse(i)) allContentItems.push(formatItem(i, 'Assignment', 'assignment', 'bg-light-red', 'type-assignment', `student-view-assignment.php`));
          });
          if (quizzes.data) quizzes.data.map(normalize).forEach(i => {
              if (belongsToCourse(i)) allContentItems.push(formatItem(i, 'Quiz', 'quiz', 'bg-light-purple', 'type-quiz', `student-view-quiz.php`));
          });

          allContentItems.sort((a, b) => new Date(b.rawDate) - new Date(a.rawDate));
          renderItems(allContentItems);

      } catch (error) {
          console.error(error);
          feed.innerHTML = `<div class="alert alert-danger">Error loading content.</div>`;
      }
  }

  async function loadClassmates() {
      const list = document.getElementById('classmatesList');
      list.innerHTML = `<div class="col-12 text-center py-5"><div class="spinner-border text-danger" role="status"></div><p class="text-muted mt-2">Loading classmates...</p></div>`;

      try {
          const response = await fetch(`http://localhost:1337/api/users?populate=*`, { headers: authHeaders });
          const users = await response.json();

          const classmates = users.filter(user => {
              if (user.email === CURRENT_USER_EMAIL) return false; 
              if (COURSE_INSTRUCTOR_EMAIL && user.email === COURSE_INSTRUCTOR_EMAIL) return false;
              if (user.role && (user.role.name === 'Instructor' || user.role.type === 'teacher')) return false; 
              
              if (!user.courses) return false;
              let userCourses = user.courses;
              if (userCourses.data) userCourses = userCourses.data; 
              if (!Array.isArray(userCourses)) return false;

              return userCourses.some(c => c.documentId === COURSE_ID_INPUT || c.id.toString() === COURSE_ID_INPUT);
          });

          if (classmates.length === 0) {
              list.innerHTML = `
                <div class="col-12 text-center py-5">
                    <span class="material-icons fs-1 text-muted">person_off</span>
                    <p class="text-muted mt-2">It's quiet here...<br><small>No other students enrolled yet.</small></p>
                </div>`;
              return;
          }

          let html = '';
          classmates.forEach((user, index) => {
              const initials = (user.username || 'U').substring(0,2).toUpperCase();
              const delay = index * 0.1; 
              const cleanName = (user.username || 'User').replace(/\d+$/, '');

              html += `
                <div class="col-md-6 col-lg-4 reveal-item" style="animation-delay: ${delay}s">
                    <div class="roster-card d-flex align-items-center">
                        <div class="avatar-circle me-3" style="background: #eee; color: #333;">${initials}</div>
                        <div>
                            <div class="fw-bold text-dark">${cleanName}</div>
                        </div>
                    </div>
                </div>
              `;
          });
          list.innerHTML = html;

      } catch (error) {
          console.error(error);
          list.innerHTML = `<div class="col-12 alert alert-danger">Error loading roster.</div>`;
      }
  }

  // --- FETCH GRADES (Robust) ---
  async function fetchGrades() {
      const container = document.getElementById('gradesListContainer');
      try {
          // 1. Fetch All Submissions for current User
          const res = await fetch(`http://localhost:1337/api/submissions?filters[users_permissions_user][id][$eq]=${USER_ID}&populate=assignment`, { headers: authHeaders });
          if (!res.ok) throw new Error("Failed to fetch grades");
          
          const json = await res.json();
          const submissions = json.data;

          if (submissions.length === 0) {
              container.innerHTML = `<div class="text-center py-5 text-muted">No grades recorded yet.</div>`;
              return;
          }
          
          let html = '';
          // 2. Filter for current course assignments
          // We need to know if the assignment belongs to this course.
          // Since we populated assignment, we might need to fetch assignment details or filter if possible.
          // Simplest for now: Show all recent grades, or try to filter if assignment->course data is available.
          // Let's just show all graded items for now.
          
          let hasGrades = false;

          submissions.forEach(sub => {
              const attr = sub.attributes || sub;
              // Only show if graded
              if (attr.grade !== null && attr.grade !== undefined) {
                  hasGrades = true;
                  const assign = attr.assignment?.data?.attributes || attr.assignment?.data || attr.assignment || {};
                  const title = assign.title || 'Untitled Assignment';
                  
                  html += `
                    <div class="grade-item">
                        <div class="d-flex align-items-center">
                            <span class="material-icons text-success me-3">assignment_turned_in</span>
                            <span class="fw-bold text-dark">${title}</span>
                        </div>
                        <span class="grade-score">${attr.grade}/100</span>
                    </div>
                  `;
              }
          });
          
          if (!hasGrades) {
             container.innerHTML = `<div class="text-center py-5 text-muted">No graded work yet.</div>`;
          } else {
             container.innerHTML = html;
          }

      } catch (e) {
          console.error(e);
          container.innerHTML = `<div class="alert alert-danger m-3">Could not load grades.</div>`;
      }
  }

  function formatItem(item, type, icon, bgClass, borderClass, link) {
      const createdAt = item.createdAt || item.publishedAt || new Date().toISOString();
      const title = item.title || 'Untitled';
      const id = item.documentId || item.id;
      return {
          type: type, title: title,
          date: new Date(createdAt).toLocaleDateString('en-US', { month: 'long', day: 'numeric' }),
          rawDate: createdAt, icon: icon, bgClass: bgClass, borderClass: borderClass,
          url: `${link}?id=${encodeURIComponent(id)}&courseId=${encodeURIComponent(COURSE_ID_INPUT)}`
      };
  }

  function renderItems(items) {
      const feed = document.getElementById('courseContentFeed');
      if (items.length === 0) {
          feed.innerHTML = `<div class="text-center py-5 border rounded bg-white"><span class="material-icons fs-1 text-muted">inbox</span><p class="text-muted mt-2">No content posted yet.</p></div>`;
          return;
      }
      let html = '';
      items.forEach((item, index) => {
          const delayMs = index * 70; 
          html += `
            <a href="${item.url}" class="content-card ${item.borderClass} reveal-item" style="animation-delay: ${delayMs}ms;" onmousedown="handleMouseDown(event)" onclick="handleCardTap(event, '${item.url}')">
                <div class="d-flex align-items-center">
                    <div class="icon-box ${item.bgClass}"><span class="material-icons">${item.icon}</span></div>
                    <div class="flex-grow-1">
                        <h5 class="fw-bold mb-1 text-dark">${item.title}</h5>
                        <div class="text-muted small"><span class="fw-bold">${item.type}</span> • Posted on ${item.date}</div>
                    </div>
                    <span class="material-icons text-muted">chevron_right</span>
                </div>
            </a>
          `;
      });
      feed.innerHTML = html;
  }

  function handleCardTap(event, url) {
    event.preventDefault(); 
    const card = event.currentTarget;
    card.classList.add('tapped');
    setTimeout(() => { window.location.href = url; }, 200);
  }
  
  function handleMouseDown(event) { event.currentTarget.classList.add('tapped'); }

  window.filterContent = function(type, ev) {
      if (ev && ev.target) {
          document.querySelectorAll('.filter-btn').forEach(btn => btn.classList.remove('active'));
          ev.target.classList.add('active');
      }
      if (type === 'all') renderItems(allContentItems);
      else renderItems(allContentItems.filter(item => item.type === type));
  }

  document.addEventListener('DOMContentLoaded', () => {
      loadCourseDetails();
      loadCourseContent();
  });
</script>
</body>
</html>
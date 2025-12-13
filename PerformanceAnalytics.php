<?php
session_start();
if (!isset($_SESSION['idToken'])) { header('Location: login.php'); exit; }

$userName = $_SESSION['user']['name'] ?? 'Instructor';
$instructorId = $_SESSION['user']['strapi_id'] ?? null; 

$avatarInitials = 'U';
if (!empty($userName)) {
    $nameParts = explode(' ', $userName);
    $avatarInitials = count($nameParts) >= 2 ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1)) : strtoupper(substr($userName, 0, 2));
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Performance Insights — BSU</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

<style>
    :root { --bsu-red: #d32f2f; --bg-light: #f4f7f6; }
    body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); }

    /* Sidebar */
    .sidebar { min-height: 100vh; background: linear-gradient(180deg, var(--bsu-red) 0%, #b71c1c 100%); color: white; }
    .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 20px; margin-bottom: 5px; display: flex; align-items: center; gap: 10px; cursor: pointer; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: rgba(255,255,255,0.2); color: white; border-radius: 8px; }
    .sidebar .brand { padding: 20px; font-size: 1.2rem; font-weight: 700; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; color: white; }
    .logo-img { background: white; border-radius: 50%; padding: 2px; width: 35px; height: 35px; }
    .mobile-nav { background: var(--bsu-red); padding: 10px 15px; }
    
    /* Main */
    .main-content { padding: 30px; padding-bottom: 100px; }

    /* Tabs */
    .nav-pills { gap: 10px; overflow-x: auto; flex-wrap: nowrap; padding-bottom: 10px; }
    .nav-pills .nav-link {
        color: #555; background: white; border: 1px solid #ddd; 
        border-radius: 50px; padding: 8px 20px; font-weight: 600; 
        white-space: nowrap; cursor: pointer; transition: all 0.2s;
    }
    .nav-pills .nav-link.active {
        background-color: var(--bsu-red); color: white; border-color: var(--bsu-red);
        box-shadow: 0 4px 10px rgba(211, 47, 47, 0.2);
    }

    /* Summary Card (New Feature) */
    .summary-card {
        background: linear-gradient(135deg, #2c3e50 0%, #34495e 100%);
        color: white; border-radius: 16px; padding: 25px; margin-bottom: 30px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        animation: slideUpFade 0.5s ease-out forwards;
    }
    .summary-stat { text-align: center; border-right: 1px solid rgba(255,255,255,0.1); }
    .summary-stat:last-child { border-right: none; }
    .stat-val { font-size: 1.8rem; font-weight: 700; }
    .stat-label { font-size: 0.75rem; opacity: 0.8; text-transform: uppercase; letter-spacing: 1px; }
    
    .class-ai-feedback {
        background: rgba(255,255,255,0.1); padding: 20px; border-radius: 10px; margin-top: 20px;
        font-size: 0.95rem; line-height: 1.6; border: 1px solid rgba(255,255,255,0.1); display: none;
    }

    /* Student Card */
    .student-card {
        background: white; border-radius: 16px; padding: 20px; margin-bottom: 15px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.05);
        cursor: pointer; transition: transform 0.2s;
        animation: slideUpFade 0.4s ease-out forwards; opacity: 0;
    }
    .student-card:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(0,0,0,0.08); }

    .score-circle { width: 45px; height: 45px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1rem; }
    .score-high { background: #d1e7dd; color: #0f5132; }
    .score-mid { background: #fff3cd; color: #856404; }
    .score-low { background: #f8d7da; color: #721c24; }
    
    .feedback-box { display: none; margin-top: 15px; padding: 15px; background: #f8fafd; border-radius: 10px; border-left: 4px solid #0d6efd; font-size: 0.9rem; color: #444; }
    .feedback-header { font-weight: 700; color: #0d6efd; display: flex; align-items: center; gap: 8px; margin-bottom: 10px; }

    /* Utils */
    .user-dropdown-toggle::after { display: none; }
    .avatar-circle { width: 40px; height: 40px; background-color: var(--bsu-red); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }
    .offcanvas-header .btn-close { position: absolute; right: 20px; top: 25px; filter: invert(1); }

    @keyframes slideUpFade { to { opacity: 1; transform: translateY(0); } }
    @media (max-width: 768px) { .sidebar { display: none; } .main-content { padding: 20px; } .summary-stat { border-right: none; border-bottom: 1px solid rgba(255,255,255,0.1); padding-bottom: 10px; margin-bottom: 10px; } }
</style>
</head>
<body>

<nav class="navbar navbar-dark mobile-nav d-md-none sticky-top">
  <div class="container-fluid justify-content-start"><button class="navbar-toggler border-0 p-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar"><span class="material-icons text-white">menu</span></button></div>
</nav>

<div class="container-fluid">
  <div class="row">
    
    <div class="col-md-3 col-lg-2 d-none d-md-block sidebar p-0">
      <div class="brand"><img src="images/bsu_logo.png" class="logo-img"> Instructor Portal</div>
      <nav class="nav flex-column px-2">
        <a class="nav-link" href="InstructorDashboard.php"><span class="material-icons">dashboard</span> Dashboard</a>
        <a class="nav-link active" href="#"><span class="material-icons">insights</span> Analytics</a>
      </nav>
    </div>

    <div class="offcanvas offcanvas-start sidebar" tabindex="-1" id="offcanvasSidebar">
        <div class="offcanvas-header"><div class="brand">Instructor Portal</div><button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button></div>
        <div class="offcanvas-body p-0">
            <nav class="nav flex-column px-2">
                <a class="nav-link" href="InstructorDashboard.php"><span class="material-icons">dashboard</span> Dashboard</a>
                <a class="nav-link active" href="#"><span class="material-icons">insights</span> Analytics</a>
                <a class="nav-link" href="instructor-announcements.php"><span class="material-icons">campaign</span> Announcements</a>
            </nav>
        </div>
    </div>

    <main class="col-md-9 col-lg-10 ms-sm-auto main-content">
      
      <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
        <h2 class="h2 fw-bold m-0">Performance Insights</h2>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none user-dropdown-toggle" data-bs-toggle="dropdown">
               <div class="text-end d-none d-md-block me-3"><small class="text-muted d-block" style="font-size: 0.8rem;">Current User</small><span class="fw-bold text-dark"><?php echo htmlspecialchars($userName); ?></span></div>
               <div class="avatar-circle shadow-sm"><?php echo htmlspecialchars($avatarInitials); ?></div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0"><li><a class="dropdown-item text-danger" href="?action=logout">Logout</a></li></ul>
        </div>
      </div>

      <div class="row justify-content-center">
          <div class="col-lg-10">
              
              <ul class="nav nav-pills mb-4" id="courseTabs">
                  <li class="nav-item"><span class="nav-link active">Loading...</span></li>
              </ul>

              <div id="classSummaryContainer" class="summary-card" style="display:none;">
                  <div class="row align-items-center">
                      <div class="col-md-3 summary-stat"><div class="stat-val" id="statAvg">--</div><div class="stat-label">Average</div></div>
                      <div class="col-md-3 summary-stat"><div class="stat-val" id="statHigh">--</div><div class="stat-label">Highest</div></div>
                      <div class="col-md-3 summary-stat"><div class="stat-val" id="statLow">--</div><div class="stat-label">Lowest</div></div>
                      <div class="col-md-3 text-center pt-3 pt-md-0">
                           <button class="btn btn-light btn-sm fw-bold rounded-pill w-100" onclick="generateClassFeedback()">
                               <span class="material-icons align-middle fs-6 me-1">auto_awesome</span> Class Analysis
                           </button>
                      </div>
                  </div>
                  <div id="classAiFeedback" class="class-ai-feedback"></div>
              </div>

              <div id="insightsFeed">
                  <div class="text-center py-5 text-muted">Select a course to view insights.</div>
              </div>

          </div>
      </div>

    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const STRAPI_TOKEN = "097902687e71fa3c9d71b0bd7e46a5a998ccf78fea375dd83b0ba138fd83c74818f80a3b4411b02fe72a826206f775ded3c4d86a2de908242d265a39b4e097f064a316880f796c47650f6e88c6326d1dd0df3868929c3301368caa0bd5227ffae6a991867d182f02194dad1fd717c2136b14a23b17d9c6fbe11178d04a45ad4c";
  const authHeaders = { 'Authorization': `Bearer ${STRAPI_TOKEN}` };

  let allSubmissions = [];
  let allCourses = [];
  let courseAssignmentsMap = {}; 
  
  // Store data for AI Context
  let currentCourseScores = [];
  let currentCourseName = "";

  async function init() {
      await fetchCourses();        // 1. Get Courses
      await fetchAssignmentsMap(); // 2. Map Assignments to Courses
      await fetchSubmissions();    // 3. Get All Submissions
      
      renderTabs();
      
      // Auto-load first course
      if(allCourses.length > 0) {
          filterByCourse(allCourses[0].id);
      } else {
          document.getElementById('insightsFeed').innerHTML = "<p class='text-center py-5 text-muted'>No courses found.</p>";
          document.getElementById('courseTabs').innerHTML = "";
      }
  }

  // 1. FETCH COURSES (Safe)
  async function fetchCourses() {
      try {
          const res = await fetch(`http://localhost:1337/api/courses`, { headers: authHeaders });
          const json = await res.json();
          if (!json.data) return;
          allCourses = json.data.map(c => ({
              id: c.documentId || c.id,
              title: c.attributes ? c.attributes.title : c.title,
              code: c.attributes ? c.attributes.class_code : c.class_code
          }));
      } catch(e) { console.error(e); }
  }

  // 2. FETCH ASSIGNMENTS (For Mapping)
  async function fetchAssignmentsMap() {
      try {
          // Fetch assignments and populate 'course' to link them
          const res = await fetch(`http://localhost:1337/api/assignments?populate=course`, { headers: authHeaders });
          const json = await res.json();
          
          if(json.data) {
              json.data.forEach(a => {
                  const aId = String(a.documentId || a.id);
                  const attr = a.attributes || a;
                  const cData = attr.course?.data || attr.course;
                  
                  if(cData) {
                      const cId = String(cData.documentId || cData.id);
                      if(!courseAssignmentsMap[cId]) courseAssignmentsMap[cId] = [];
                      courseAssignmentsMap[cId].push(aId);
                  }
              });
          }
      } catch(e) { console.error(e); }
  }

  // 3. FETCH SUBMISSIONS (Safe All Fetch)
  async function fetchSubmissions() {
      try {
          // Use populate=* for safety
          const url = `http://localhost:1337/api/submissions?populate=*&sort=updatedAt:desc`;
          const res = await fetch(url, { headers: authHeaders });
          const json = await res.json();
          allSubmissions = json.data || [];
      } catch(e) { console.error(e); }
  }

  // 4. RENDER TABS
  function renderTabs() {
      const tabContainer = document.getElementById('courseTabs');
      if(allCourses.length === 0) { tabContainer.innerHTML = ""; return; }
      
      let html = '';
      allCourses.forEach((course, index) => {
          const activeClass = index === 0 ? 'active' : '';
          html += `<li class="nav-item"><button class="nav-link ${activeClass} course-tab-btn" data-id="${course.id}">${course.title}</button></li>`;
      });
      tabContainer.innerHTML = html;

      document.querySelectorAll('.course-tab-btn').forEach(btn => {
          btn.addEventListener('click', function() {
              document.querySelectorAll('.course-tab-btn').forEach(b => b.classList.remove('active'));
              this.classList.add('active');
              filterByCourse(this.getAttribute('data-id'));
          });
      });
  }

  // 5. FILTER & CALCULATE STATS
  function filterByCourse(courseId) {
      const container = document.getElementById('insightsFeed');
      const summaryCard = document.getElementById('classSummaryContainer');
      
      // Identify Course & Valid Assignments
      const courseObj = allCourses.find(c => c.id == courseId);
      currentCourseName = courseObj ? courseObj.title : "Class";
      const validAssignIds = courseAssignmentsMap[courseId] || [];

      // Filter
      const filtered = allSubmissions.filter(sub => {
          const attr = sub.attributes || sub;
          
          // Check Graded Status
          const status = attr.submission_status || attr.status;
          const isGraded = status === 'graded' || (attr.grade !== null);
          if (!isGraded) return false;

          // Check Assignment Link
          const assignData = attr.assignment?.data || attr.assignment;
          if(!assignData) return false;
          const aId = String(assignData.documentId || assignData.id);
          
          return validAssignIds.includes(aId);
      });

      // Update Summary Stats
      currentCourseScores = filtered.map(s => parseInt((s.attributes ? s.attributes.grade : s.grade) || 0));
      
      if (currentCourseScores.length > 0) {
          const avg = Math.round(currentCourseScores.reduce((a,b)=>a+b,0)/currentCourseScores.length);
          document.getElementById('statAvg').innerText = avg + '%';
          document.getElementById('statHigh').innerText = Math.max(...currentCourseScores) + '%';
          document.getElementById('statLow').innerText = Math.min(...currentCourseScores) + '%';
          summaryCard.style.display = 'block';
          // Hide previous AI feedback
          document.getElementById('classAiFeedback').style.display = 'none'; 
      } else {
          summaryCard.style.display = 'none';
      }

      if (filtered.length === 0) {
          container.innerHTML = `<div class="text-center py-5 text-muted"><p>No graded submissions found for this course.</p></div>`;
          return;
      }

      // Render List
      let html = '';
      filtered.forEach(sub => {
          const attr = sub.attributes || sub;
          const id = sub.documentId || sub.id;
          
          // Safe Name Extraction
          let studentName = "Student";
          let initial = "S";
          const uData = attr.users_permissions_user || attr.student;
          if(uData) {
              const uObj = uData.data || uData;
              if(uObj && (uObj.attributes?.username || uObj.username)) {
                  studentName = uObj.attributes ? uObj.attributes.username : uObj.username;
                  initial = studentName.substring(0,2).toUpperCase();
              }
          }
          
          const assignTitle = attr.assignment?.data?.attributes?.title || attr.assignment?.title || "Assignment";
          const score = attr.grade;
          const feedback = attr.ai_feedback || "No feedback.";
          
          let scoreClass = score >= 90 ? 'score-high' : (score >= 75 ? 'score-mid' : 'score-low');

          html += `
            <div class="student-card" onclick="toggleFeedback('${id}')">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <div class="avatar-circle" style="background:#eee; color:#555;">${initial}</div>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">${studentName}</h6>
                            <small class="text-muted">${assignTitle}</small>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="score-circle ${scoreClass}">${score}</div>
                        <span class="material-icons text-muted">expand_more</span>
                    </div>
                </div>
                <div class="feedback-box" id="feedback-${id}">
                    <div class="feedback-header"><span class="material-icons">auto_awesome</span> Individual Feedback</div>
                    <div>${marked.parse(feedback)}</div>
                </div>
            </div>
          `;
      });
      container.innerHTML = html;
  }

  // --- GENERATE CLASS FEEDBACK (Real AI Backend) ---
  async function generateClassFeedback() {
      const feedbackDiv = document.getElementById('classAiFeedback');
      feedbackDiv.style.display = 'block';
      feedbackDiv.innerHTML = '<div class="text-center py-3"><span class="spinner-border spinner-border-sm text-warning"></span> Analyzing class performance...</div>';
      
      try {
          // Call the PHP Backend Script (Ensure api/class_feedback.php exists!)
          const res = await fetch('api/class_feedback.php', {
              method: 'POST',
              headers: {'Content-Type': 'application/json'},
              body: JSON.stringify({ scores: currentCourseScores, courseName: currentCourseName })
          });
          
          const data = await res.json();
          
          if(data.feedback) {
              feedbackDiv.innerHTML = `
                 <div style="font-weight:700; margin-bottom:10px; color:#ffd700; display:flex; align-items:center; gap:5px;">
                    <span class="material-icons">auto_awesome</span> AI Class Analysis
                 </div>
                 ${marked.parse(data.feedback)}`;
          } else {
              feedbackDiv.innerText = "AI Summary unavailable.";
          }
      } catch(e) { 
          console.error(e); 
          feedbackDiv.innerText = "Could not generate summary (Check API/Backend)."; 
      }
  }

  window.toggleFeedback = function(id) {
      const box = document.getElementById(`feedback-${id}`);
      box.style.display = (box.style.display === "block") ? "none" : "block";
  }

  document.addEventListener('DOMContentLoaded', init);
</script>
</body>
</html>
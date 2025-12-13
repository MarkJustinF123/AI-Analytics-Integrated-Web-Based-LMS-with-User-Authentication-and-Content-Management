<?php
session_start();
if (!isset($_SESSION['idToken'])) { header('Location: login.php'); exit; }

$userName = $_SESSION['user']['name'] ?? 'Student';
// Get the ID from session to filter data
$studentId = $_SESSION['user']['strapi_id'] ?? null; 

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
<title>My Performance — BSU</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

<style>
    :root { --bsu-red: #d32f2f; --bg-light: #f4f7f6; }
    body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); }

    /* SIDEBAR */
    .sidebar { min-height: 100vh; background: linear-gradient(180deg, var(--bsu-red) 0%, #b71c1c 100%); color: white; }
    .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 20px; margin-bottom: 5px; display: flex; align-items: center; gap: 10px; cursor: pointer; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: rgba(255,255,255,0.2); color: white; border-radius: 8px; }
    .sidebar .brand { padding: 20px; font-size: 1.2rem; font-weight: 700; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; color: white; }
    .logo-img { background: white; border-radius: 50%; padding: 2px; width: 35px; height: 35px; }
    .mobile-nav { background: var(--bsu-red); padding: 10px 15px; }
    
    /* MAIN */
    .main-content { padding: 30px; padding-bottom: 100px; }

    /* STUDENT CARD */
    .student-card {
        background: white; border-radius: 16px; padding: 25px; margin-bottom: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.05);
        transition: transform 0.2s; cursor: pointer;
        animation: slideUpFade 0.5s ease-out forwards; opacity: 0; 
    }
    .student-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }

    .score-box { text-align: center; padding-left: 20px; border-left: 1px solid #eee; min-width: 90px; }
    .score-val { font-size: 2.2rem; font-weight: 800; line-height: 1; }
    .score-label { font-size: 0.7rem; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: 1px; }

    .score-high { color: #198754; } 
    .score-mid { color: #fd7e14; } 
    .score-low { color: #dc3545; }

    /* FEEDBACK DROPDOWN */
    .feedback-box {
        display: none; /* Hidden by default */
        background: #f8fafd; padding: 20px; border-radius: 12px; margin-top: 20px;
        border-left: 4px solid #0d6efd; font-size: 0.95rem; color: #444; line-height: 1.6;
    }
    .feedback-header { font-weight: 700; color: #0d6efd; display: flex; align-items: center; gap: 8px; margin-bottom: 10px; }

    .user-dropdown-toggle::after { display: none; }
    .avatar-circle { width: 40px; height: 40px; background-color: var(--bsu-red); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }
    .offcanvas-header .btn-close { position: absolute; right: 20px; top: 25px; filter: invert(1); }

    @keyframes slideUpFade { to { opacity: 1; transform: translateY(0); } }
    @media (max-width: 768px) { .sidebar { display: none; } .main-content { padding: 20px; } }
</style>
</head>
<body>

<nav class="navbar navbar-dark mobile-nav d-md-none sticky-top">
  <div class="container-fluid justify-content-start"><button class="navbar-toggler border-0 p-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar"><span class="material-icons text-white">menu</span></button></div>
</nav>

<div class="container-fluid">
  <div class="row">
    
    <div class="col-md-3 col-lg-2 d-none d-md-block sidebar p-0">
      <div class="brand"><img src="images/bsu_logo.png" class="logo-img"> Student Portal</div>
      <nav class="nav flex-column px-2">
        <a class="nav-link" href="StudentDashboard.php"><span class="material-icons">dashboard</span> Dashboard</a>
        <a class="nav-link active" href="#"><span class="material-icons">insights</span> My Analytics</a>
      </nav>
    </div>

    <div class="offcanvas offcanvas-start sidebar" tabindex="-1" id="offcanvasSidebar">
        <div class="offcanvas-header"><div class="brand">Student Portal</div><button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button></div>
        <div class="offcanvas-body p-0">
            <nav class="nav flex-column px-2">
                <a class="nav-link" href="StudentDashboard.php"><span class="material-icons">dashboard</span> Dashboard</a>
                <a class="nav-link active" href="#"><span class="material-icons">insights</span> My Analytics</a>
                <a class="nav-link" href="student-announcements.php"><span class="material-icons">campaign</span> Announcements</a>
            </nav>
        </div>
    </div>

    <main class="col-md-9 col-lg-10 ms-sm-auto main-content">
      
      <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
        <h2 class="h2 fw-bold m-0">My Performance</h2>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none user-dropdown-toggle" data-bs-toggle="dropdown">
               <div class="text-end d-none d-md-block me-3"><small class="text-muted d-block" style="font-size: 0.8rem;">Student</small><span class="fw-bold text-dark"><?php echo htmlspecialchars($userName); ?></span></div>
               <div class="avatar-circle shadow-sm"><?php echo htmlspecialchars($avatarInitials); ?></div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0"><li><a class="dropdown-item text-danger" href="?action=logout">Logout</a></li></ul>
        </div>
      </div>

      <div class="row justify-content-center">
          <div class="col-lg-9" id="insightsContainer">
              <div class="text-center py-5">
                  <div class="spinner-border text-danger" role="status"></div>
                  <p class="text-muted mt-3">Loading your grades...</p>
              </div>
          </div>
      </div>

    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const STRAPI_TOKEN = "097902687e71fa3c9d71b0bd7e46a5a998ccf78fea375dd83b0ba138fd83c74818f80a3b4411b02fe72a826206f775ded3c4d86a2de908242d265a39b4e097f064a316880f796c47650f6e88c6326d1dd0df3868929c3301368caa0bd5227ffae6a991867d182f02194dad1fd717c2136b14a23b17d9c6fbe11178d04a45ad4c";
  
  // Pass PHP Session ID to JS
  const STUDENT_ID = <?php echo json_encode($studentId); ?>;
  
  const authHeaders = { 'Authorization': `Bearer ${STRAPI_TOKEN}` };

  async function loadAnalytics() {
      const container = document.getElementById('insightsContainer');
      
      try {
          // 1. SAFE FETCH: Use simple wildcard populate
          // This avoids 400 Bad Request errors caused by complex filters
          const url = `http://localhost:1337/api/submissions?populate=*&sort=updatedAt:desc`;
          
          const res = await fetch(url, { headers: authHeaders });
          
          if (!res.ok) {
              console.error("API Error:", res.status);
              container.innerHTML = `<div class="alert alert-danger text-center">Error loading data (Status ${res.status}).</div>`;
              return;
          }
          
          const json = await res.json();

          // 2. CLIENT-SIDE FILTER: Only show MY graded work
          const myItems = json.data.filter(sub => {
              const attr = sub.attributes || sub;
              
              // Check User (Handle both v4 nested and v5 flat)
              const uData = attr.users_permissions_user || attr.student;
              // Safe Access: Check if object exists, then check data wrapper, then check ID
              let uId = null;
              if (uData) {
                  uId = uData.id || (uData.data && uData.data.id); 
              }
              
              // Check Status
              const status = attr.submission_status || attr.status;
              const hasGrade = (attr.grade !== null && attr.grade !== undefined);
              
              // LOGIC: Must be MY ID and (Graded OR has a score)
              // Loose comparison (==) handles string vs int ID differences
              return (uId == STUDENT_ID) && (status === 'graded' || hasGrade);
          });

          if (myItems.length === 0) {
              container.innerHTML = `
                <div class="text-center py-5 text-muted" style="border: 2px dashed #eee; border-radius: 12px;">
                    <span class="material-icons fs-1 mb-2" style="opacity:0.5">school</span>
                    <p>No graded assignments found.</p>
                    <small>Wait for your instructor to grade your work.</small>
                </div>`;
              return;
          }

          let html = '';
          myItems.forEach((sub, index) => {
              const attr = sub.attributes || sub;
              const id = sub.documentId || sub.id;
              const delay = index * 0.1;

              // Safe Assignment Title
              let assignTitle = 'Assignment';
              const aRel = attr.assignment;
              if (aRel) {
                  // Handle v4/v5 structure
                  const aData = aRel.data || aRel; 
                  if (aData && (aData.attributes?.title || aData.title)) {
                      assignTitle = aData.attributes ? aData.attributes.title : aData.title;
                  }
              }

              const score = attr.grade || 0;
              let scoreColor = 'score-mid';
              if(score >= 90) scoreColor = 'score-high';
              if(score < 75) scoreColor = 'score-low';

              const aiFeedback = attr.ai_feedback || "Feedback pending...";

              // Render Student-View Card
              html += `
                <div class="student-card" style="animation-delay: ${delay}s;" onclick="toggleFeedback('${id}')">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-none d-sm-flex align-items-center justify-content-center bg-light rounded-circle" style="width:55px; height:55px;">
                                <span class="material-icons text-primary">assignment_turned_in</span>
                            </div>
                            <div>
                                <h5 class="fw-bold mb-1 text-dark">${assignTitle}</h5>
                                <div class="text-muted small">Click to view feedback</div>
                            </div>
                        </div>
                        <div class="d-flex align-items-center gap-3">
                            <div class="score-box">
                                <div class="score-val ${scoreColor}">${score}</div>
                                <div class="score-label">Grade</div>
                            </div>
                            <span class="material-icons text-muted" id="icon-${id}">expand_more</span>
                        </div>
                    </div>
                    
                    <div class="feedback-box" id="feedback-${id}">
                        <div class="feedback-header"><span class="material-icons">auto_awesome</span> AI Assessment</div>
                        <div>${marked.parse(aiFeedback)}</div>
                    </div>
                </div>
              `;
          });

          container.innerHTML = html;

      } catch (error) {
          console.error("Analytics Error:", error);
          container.innerHTML = `<div class="alert alert-danger text-center">Error processing data. Check console.</div>`;
      }
  }

  // Toggle Accordion
  window.toggleFeedback = function(id) {
      const box = document.getElementById(`feedback-${id}`);
      const icon = document.getElementById(`icon-${id}`);
      if(box.style.display === "block") {
          box.style.display = "none";
          icon.innerText = "expand_more";
      } else {
          box.style.display = "block";
          icon.innerText = "expand_less";
      }
  }

  document.addEventListener('DOMContentLoaded', loadAnalytics);
</script>
</body>
</html>
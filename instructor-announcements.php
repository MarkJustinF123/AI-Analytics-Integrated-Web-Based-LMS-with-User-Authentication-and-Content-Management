<?php
session_start();

// 1. Check Auth
if (!isset($_SESSION['idToken']) || !isset($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

// 2. Logout Logic
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit;
}

// 3. User Info
$userName = isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Instructor';
$avatarInitials = 'U';
if (!empty($userName)) {
    $nameParts = explode(' ', $userName);
    $avatarInitials = count($nameParts) >= 2 ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1)) : strtoupper(substr($userName, 0, 2));
}

// Get Strapi ID (Critical for linking announcement to instructor)
$strapiUserId = isset($_SESSION['user']['strapi_id']) ? $_SESSION['user']['strapi_id'] : null;
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Announcements — BSU</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

  <style>
    :root { --bsu-red: #d32f2f; --bsu-dark: #1a1a1a; --bg-light: #f4f7f6; }
    body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); min-height: 100vh; }

    /* SIDEBAR */
    .sidebar { min-height: 100vh; background: linear-gradient(180deg, var(--bsu-red) 0%, #b71c1c 100%); color: white; }
    .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 20px; border-radius: 8px; margin-bottom: 5px; display: flex; align-items: center; gap: 10px; cursor: pointer; font-weight: 500; transition: all 0.2s; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: rgba(255,255,255,0.2); color: white; }
    .sidebar .brand { padding: 20px; font-size: 1.2rem; font-weight: 700; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; color: white; width: 100%; }
    .logo-img { background: white; border-radius: 50%; padding: 2px; width: 35px; height: 35px; }

    /* MAIN CONTENT */
    .main-content { padding: 20px; padding-bottom: 100px; }
    .mobile-nav { background: var(--bsu-red); color: white; padding: 10px 15px; }
    .user-dropdown-toggle::after { display: none; }
    .avatar-circle { width: 40px; height: 40px; background-color: var(--bsu-red); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }

    /* CARDS */
    .welcome-banner { background: linear-gradient(135deg, #ff9800 0%, #f57c00 100%); color: white; border-radius: 16px; padding: 30px; margin-bottom: 25px; box-shadow: 0 4px 15px rgba(255, 152, 0, 0.2); }
    
    .content-card {
        background: white; border-radius: 12px; padding: 20px; margin-bottom: 15px;
        border: 1px solid #eee; border-left: 5px solid #ff9800; 
        box-shadow: 0 2px 8px rgba(0,0,0,0.03); transition: transform 0.2s;
        display: flex; align-items: center; gap: 15px;
    }
    .content-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
    
    .icon-box { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; background: #fff3e0; color: #ef6c00; }

    /* BUTTONS */
    .btn-create {
        background-color: #222; color: white; border-radius: 50px; padding: 10px 25px;
        font-weight: 600; border: none; display: flex; align-items: center; gap: 8px;
        box-shadow: 0 4px 10px rgba(0,0,0,0.2);
    }
    .btn-create:hover { background-color: black; transform: scale(1.02); }

    .offcanvas-header { padding: 0; } .offcanvas-header .btn-close { position: absolute; right: 20px; top: 25px; filter: invert(1); opacity: 1; }
  </style>
</head>
<body>

<nav class="navbar navbar-dark mobile-nav d-md-none sticky-top">
  <div class="container-fluid justify-content-start"><button class="navbar-toggler border-0 p-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar"><span class="material-icons text-white">menu</span></button></div>
</nav>

<div class="container-fluid">
  <div class="row">
    
    <div class="col-md-3 col-lg-2 d-none d-md-block sidebar p-0">
      <div class="brand"><img src="images/bsu_logo.png" width="29" class="me-2"> Instructor Portal</div>
      <nav class="nav flex-column px-2">
        <a class="nav-link" href="InstructorDashboard.php"><span class="material-icons">dashboard</span> Dashboard</a>
        <a class="nav-link active" href="#"><span class="material-icons">campaign</span> Announcements</a>
      </nav>
    </div>

    <div class="offcanvas offcanvas-start sidebar" tabindex="-1" id="offcanvasSidebar">
      <div class="offcanvas-header"><div class="brand">Instructor Portal</div><button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas"></button></div>
      <div class="offcanvas-body p-0">
         <nav class="nav flex-column px-2">
            <a class="nav-link" href="InstructorDashboard.php"><span class="material-icons">dashboard</span> Dashboard</a>
            <a class="nav-link" href="PerformanceAnalytics.php"><span class="material-icons">insights</span> Performance Analytics</a>
            <a class="nav-link active" href="#"><span class="material-icons">campaign</span> Announcements</a>
         </nav>
      </div>
    </div>

    <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 main-content">
      
      <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
        <h1 class="h2">Announcements</h1>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none user-dropdown-toggle" id="profileDropdown" data-bs-toggle="dropdown">
               <div class="text-end d-none d-md-block me-3"><small class="text-muted d-block" style="font-size: 0.8rem;">Current User</small><span class="fw-bold text-dark"><?php echo htmlspecialchars($userName); ?></span></div>
               <div class="avatar-circle shadow-sm"><?php echo htmlspecialchars($avatarInitials); ?></div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0"><li><a class="dropdown-item text-danger" href="?action=logout">Logout</a></li></ul>
        </div>
      </div>

      <div class="welcome-banner shadow-sm reveal-item" style="animation-delay: 0s;">
        <h2 class="fw-bold">Class Updates 📢</h2>
        <p class="mb-0 opacity-90">Post important updates, reminders, or news for your students.</p>
      </div>

      <div class="d-flex justify-content-between align-items-center mb-4 reveal-item" style="animation-delay: 0.1s;">
          <h5 class="fw-bold m-0 text-dark">Posted Announcements</h5>
          <button class="btn-create" data-bs-toggle="modal" data-bs-target="#createModal">
              <span class="material-icons">add</span> New Post
          </button>
      </div>

      <div id="announcementList">
          <div class="text-center py-5">
              <div class="spinner-border text-secondary" role="status"></div>
              <p class="text-muted mt-2">Loading announcements...</p>
          </div>
      </div>

    </main>
  </div>
</div>

<div class="modal fade" id="createModal" tabindex="-1">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-0 shadow-lg" style="border-radius: 16px;">
      <div class="modal-header border-bottom-0 pb-0">
        <h5 class="modal-title fw-bold">New Announcement</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
          <div class="mb-3">
              <label class="form-label fw-bold text-secondary">Title</label>
              <input type="text" class="form-control" id="postTitle" placeholder="e.g., Midterm Exam Reschedule">
          </div>
          <div class="mb-3">
              <label class="form-label fw-bold text-secondary">Message</label>
              <textarea class="form-control" id="postContent" rows="4" placeholder="Type your announcement here..."></textarea>
          </div>
          <div class="mb-3">
              <label class="form-label fw-bold text-secondary">Post to Course</label>
              <select class="form-select" id="courseSelect">
                  <option value="" selected>Loading courses...</option>
              </select>
              <div class="form-text text-muted">Select "All Courses" to post to everyone.</div>
          </div>
      </div>
      <div class="modal-footer border-top-0 pt-0">
        <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-dark rounded-pill px-4" id="confirmPostBtn">Post Announcement</button>
      </div>
    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const STRAPI_API_TOKEN = "097902687e71fa3c9d71b0bd7e46a5a998ccf78fea375dd83b0ba138fd83c74818f80a3b4411b02fe72a826206f775ded3c4d86a2de908242d265a39b4e097f064a316880f796c47650f6e88c6326d1dd0df3868929c3301368caa0bd5227ffae6a991867d182f02194dad1fd717c2136b14a23b17d9c6fbe11178d04a45ad4c";
  const currentInstructorId = <?php echo json_encode($strapiUserId); ?>;
  const authHeaders = { 'Content-Type': 'application/json', 'Authorization': `Bearer ${STRAPI_API_TOKEN}` };

  // --- 1. LOAD COURSES FOR DROPDOWN ---
  async function loadCoursesDropdown() {
      const select = document.getElementById('courseSelect');
      try {
          const res = await fetch(`http://localhost:1337/api/courses?filters[instructor][id][$eq]=${currentInstructorId}`, { headers: authHeaders });
          const json = await res.json();
          
          if (json.data.length === 0) {
              select.innerHTML = '<option value="" disabled>No courses available</option>';
              return;
          }
          
          let options = '<option value="all">All My Courses</option>';
          json.data.forEach(c => {
             const id = c.documentId || c.id;
             options += `<option value="${id}">${c.title}</option>`;
          });
          select.innerHTML = options;
          
      } catch(e) { select.innerHTML = '<option>Error loading courses</option>'; }
  }

  // --- 2. CREATE ANNOUNCEMENT ---
  document.getElementById('confirmPostBtn').addEventListener('click', async () => {
      const title = document.getElementById('postTitle').value;
      const content = document.getElementById('postContent').value;
      const courseVal = document.getElementById('courseSelect').value;
      const btn = document.getElementById('confirmPostBtn');

      if(!title || !content) { alert("Please fill all fields."); return; }
      
      btn.disabled = true; btn.textContent = "Posting...";

      // Prepare Data
      // Note: If 'course' is 'all', we might send null or handle logic in backend.
      // For now, we send null if 'all', or the course ID.
      const courseToSend = (courseVal === 'all') ? null : courseVal;

      const body = {
          data: {
              title: title,
              content: content,
              course: courseToSend,
              instructor: currentInstructorId,
              publishedAt: new Date() // Publish immediately
          }
      };

      try {
          // Ensure 'announcements' collection exists in Strapi
          const res = await fetch('http://localhost:1337/api/announcements', { 
              method: 'POST', 
              headers: authHeaders, 
              body: JSON.stringify(body) 
          });
          
          if(!res.ok) throw new Error('Failed to post');
          
          alert("Announcement Posted!");
          location.reload();
      } catch(e) {
          console.error(e);
          alert("Error posting. Check console.");
          btn.disabled = false; btn.textContent = "Post Announcement";
      }
  });

  // --- 3. LOAD ANNOUNCEMENTS LIST ---
  async function loadAnnouncements() {
      const container = document.getElementById('announcementList');
      try {
          // Filter announcements by Instructor
          const res = await fetch(`http://localhost:1337/api/announcements?filters[instructor][id][$eq]=${currentInstructorId}&populate=course&sort=createdAt:desc`, { headers: authHeaders });
          
          if (!res.ok) {
              container.innerHTML = `<div class="text-center py-5 text-muted">Could not load announcements.</div>`;
              return;
          }

          const json = await res.json();
          if (json.data.length === 0) {
              container.innerHTML = `<div class="text-center py-5 text-muted">No announcements posted yet.</div>`;
              return;
          }

          let html = '';
          json.data.forEach((item, index) => {
              const delay = index * 0.1;
              const date = new Date(item.createdAt).toLocaleDateString();
              const courseName = item.course ? item.course.title : 'All Courses';
              const id = item.documentId || item.id;

              html += `
                <div class="content-card reveal-item" style="animation-delay: ${delay}s">
                    <div class="icon-box"><span class="material-icons">campaign</span></div>
                    <div class="flex-grow-1">
                        <h6 class="fw-bold mb-1">${item.title}</h6>
                        <p class="small text-muted mb-1">${item.content}</p>
                        <div class="d-flex gap-3 small">
                             <span class="text-secondary"><span class="material-icons" style="font-size:12px; vertical-align:middle;">class</span> ${courseName}</span>
                             <span class="text-secondary"><span class="material-icons" style="font-size:12px; vertical-align:middle;">event</span> ${date}</span>
                        </div>
                    </div>
                    <button class="btn btn-light btn-sm rounded-circle text-danger" onclick="deleteAnnouncement('${id}')"><span class="material-icons">delete</span></button>
                </div>`;
          });
          container.innerHTML = html;

      } catch (e) { console.error(e); container.innerHTML = '<p class="text-center text-danger">Error loading feed.</p>'; }
  }

  async function deleteAnnouncement(id) {
      if(!confirm("Delete this announcement?")) return;
      await fetch(`http://localhost:1337/api/announcements/${id}`, { method: 'DELETE', headers: authHeaders });
      loadAnnouncements();
  }

  document.addEventListener('DOMContentLoaded', () => {
      loadCoursesDropdown();
      loadAnnouncements();
  });
</script>
</body>
</html>
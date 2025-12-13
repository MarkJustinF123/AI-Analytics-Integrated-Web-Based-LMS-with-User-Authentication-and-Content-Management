<?php
session_start();
if (!isset($_SESSION['idToken']) || !isset($_SESSION['email'])) { header('Location: login.php'); exit; }
if (isset($_GET['action']) && $_GET['action'] === 'logout') { session_unset(); session_destroy(); header('Location: login.php'); exit; }

$userName = isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Instructor';
$avatarInitials = 'U';
if (!empty($userName)) {
    $nameParts = explode(' ', $userName);
    $avatarInitials = count($nameParts) >= 2 ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1)) : strtoupper(substr($userName, 0, 2));
}

$course_id = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '';
if (empty($course_id)) { echo "No course ID provided."; exit; }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Manage Course — BSU</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

  <style>
    :root { --bsu-red: #d32f2f; --bsu-dark: #1a1a1a; --bg-light: #f4f7f6; }
    body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); min-height: 100vh; }

    /* SIDEBAR */
    .sidebar { min-height: 100vh; background: linear-gradient(180deg, var(--bsu-red) 0%, #b71c1c 100%); color: white; }
    .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 20px; margin-bottom: 5px; display: flex; align-items: center; gap: 10px; cursor: pointer; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: rgba(255,255,255,0.2); color: white; border-radius: 8px; }
    .sidebar .brand { padding: 20px; font-size: 1.2rem; font-weight: 700; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; color: white; }
    .logo-img { background: white; border-radius: 50%; padding: 2px; width: 35px; height: 35px; }
    .mobile-nav { background: var(--bsu-red); padding: 10px 15px; }
    
    /* MAIN CONTENT */
    .main-content { padding: 20px; padding-bottom: 100px; }
    @media (max-width: 768px) { .main-content { padding-top: 20px; } }

    /* HERO */
    .course-hero { background: linear-gradient(135deg, #1a1a1a 0%, #2c2c2c 100%); color: white; border-radius: 16px; padding: 40px; margin-bottom: 30px; position: relative; overflow: hidden; box-shadow: 0 4px 15px rgba(0,0,0,0.1); }
    .course-hero::after { content: '🎓'; font-size: 150px; position: absolute; right: -20px; bottom: -40px; opacity: 0.1; transform: rotate(-15deg); }
    .class-code-badge { background-color: var(--bsu-red); color: white; padding: 5px 12px; border-radius: 4px; font-size: 0.85rem; font-weight: 700; display: inline-flex; align-items: center; gap: 5px; cursor: pointer; }

    /* TABS */
    .nav-pills .nav-link { color: #555; font-weight: 600; padding: 8px 20px; border-radius: 50px; background: white; border: 1px solid #eee; margin-right: 10px; transition: all 0.2s; }
    .nav-pills .nav-link.active { background-color: var(--bsu-red); color: white; border-color: var(--bsu-red); box-shadow: 0 4px 10px rgba(211, 47, 47, 0.3); }

    /* --- DROPDOWN Z-INDEX FIX --- */
    /* This ensures the create button container sits ABOVE the lesson cards */
    .actions-bar {
        position: relative;
        z-index: 100; 
    }
    .dropdown-menu {
        z-index: 1050; /* Bootstrap standard high z-index */
    }

    /* CARDS */
    .content-card { background: white; border-radius: 12px; border: 1px solid #eee; border-left: 5px solid transparent; box-shadow: 0 2px 8px rgba(0,0,0,0.03); margin-bottom: 15px; transition: transform 0.2s; overflow: hidden; }
    .content-card:hover { transform: translateY(-3px); box-shadow: 0 8px 20px rgba(0,0,0,0.08); }
    
    /* Accordion Parts */
    .card-header-row { padding: 20px; display: flex; align-items: center; gap: 15px; cursor: pointer; }
    .card-body-row { display: none; padding: 0 20px 20px 80px; border-top: 1px solid #f9f9f9; background-color: #fff; color: #444; }
    .card-body-row.show { display: block; }

    /* Colors */
    .type-lesson { border-left-color: #3b82f6 !important; } .bg-lesson { background: #eef2ff; color: #3b82f6; }
    .type-assignment { border-left-color: #d32f2f !important; } .bg-assign { background: #fef2f2; color: #d32f2f; }
    .type-quiz { border-left-color: #9333ea !important; } .bg-quiz { background: #f3e8ff; color: #9333ea; }
    .icon-box { width: 45px; height: 45px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

    /* Attachments */
    .file-attachment { display: inline-flex; align-items: center; gap: 8px; padding: 6px 12px; background: #f8f9fa; border: 1px solid #e0e0e0; border-radius: 8px; font-size: 0.85rem; color: #333; text-decoration: none; margin-top: 10px; margin-right: 10px; transition: 0.2s; }
    .file-attachment:hover { border-color: var(--bsu-red); background: white; }

    .btn-create { background-color: #222; color: white; border-radius: 50px; padding: 10px 25px; font-weight: 600; border: none; display: flex; align-items: center; gap: 8px; }
    .avatar-circle { width: 40px; height: 40px; background-color: var(--bsu-red); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }
    .user-dropdown-toggle::after { display: none; }
    .offcanvas-header { padding: 0; } .offcanvas-header .btn-close { position: absolute; right: 20px; top: 25px; filter: invert(1); opacity: 1; }
    
    /* Animations */
    @keyframes slideUpFade { 0% { opacity: 0; transform: translateY(30px); } 100% { opacity: 1; transform: translateY(0); } }
    .reveal-item { animation: slideUpFade 0.6s cubic-bezier(0.16, 1, 0.3, 1) both; }

  </style>
</head>
<body>

<nav class="navbar navbar-dark mobile-nav d-md-none sticky-top">
  <div class="container-fluid justify-content-start"><button class="navbar-toggler border-0 p-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar"><span class="material-icons text-white">menu</span></button></div>
</nav>

<div class="container-fluid">
  <div class="row">
    <div class="bsu-top-header col-md-3 col-lg-2 d-none d-md-block sidebar p-0">
      <div class="brand"><img src="images/bsu_logo.png" width="29" class="me-2"> Instructor Portal</div>
      <nav class="nav flex-column px-2">
        <a class="nav-link" href="InstructorDashboard.php"><span class="material-icons">dashboard</span> Dashboard</a>
        <a class="nav-link active" id="nav-content" href="#" onclick="switchView(event, 'content')"><span class="material-icons">class</span> Course Content</a>
        <a class="nav-link" id="nav-students" href="#" onclick="switchView(event, 'students')"><span class="material-icons">group</span> Students</a>
      </nav>
    </div>

    <div class="offcanvas offcanvas-start sidebar" tabindex="-1" id="offcanvasSidebar">
      <div class="offcanvas-header"><div class="brand">Instructor Portal</div><button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas"></button></div>
      <div class="offcanvas-body p-0">
         <nav class="nav flex-column px-2">
            <a class="nav-link" href="InstructorDashboard.php"><span class="material-icons">dashboard</span> Dashboard</a>
            <a class="nav-link active" id="nav-mobile-content" href="#" onclick="switchView(event, 'content')" data-bs-dismiss="offcanvas"><span class="material-icons">class</span> Course Content</a>
            <a class="nav-link" id="nav-mobile-students" href="#" onclick="switchView(event, 'students')" data-bs-dismiss="offcanvas"><span class="material-icons">group</span> Students</a>
         </nav>
      </div>
    </div>

    <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 main-content">
      
      <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
        <h1 class="h2">Manage Course</h1>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none user-dropdown-toggle" id="profileDropdown" data-bs-toggle="dropdown">
               <div class="text-end d-none d-md-block me-3"><small class="text-muted d-block" style="font-size: 0.8rem;">Current User</small><span class="fw-bold text-dark"><?php echo htmlspecialchars($userName); ?></span></div>
               <div class="avatar-circle shadow-sm"><?php echo htmlspecialchars($avatarInitials); ?></div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0"><li><a class="dropdown-item text-danger" href="?action=logout">Logout</a></li></ul>
        </div>
      </div>

      <div id="view-content">
          <div class="course-hero shadow reveal-item" style="animation-delay: 0s;">
            <div class="mb-2"><span class="class-code-badge" onclick="copyClassCode()"><span class="material-icons" style="font-size:14px">content_copy</span> Code: <span id="class-code-display">...</span></span></div>
            <h1 class="fw-bold display-6" id="course-header-title">Loading...</h1>
            <p class="mb-0 opacity-75">Manage your content.</p>
          </div>

          <div class="d-flex flex-column flex-md-row justify-content-between align-items-center mb-4 gap-3 reveal-item actions-bar" style="animation-delay: 0.1s;">
              <ul class="nav nav-pills" id="courseTab" role="tablist">
                <li class="nav-item"><button class="nav-link active" id="feed-tab" data-bs-toggle="tab" data-bs-target="#feed-content" type="button">Activity Feed</button></li>
                <li class="nav-item"><button class="nav-link" id="curriculum-tab" data-bs-toggle="tab" data-bs-target="#curriculum-content" type="button">Curriculum</button></li>
              </ul>
              <div class="dropdown" id="create-action-wrapper" style="display:none;">
                <button class="btn-create dropdown-toggle" type="button" data-bs-toggle="dropdown"><span class="material-icons">add</span> Create Content</button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                    <li><a class="dropdown-item" href="#" id="create-lesson"><span class="material-icons align-middle me-2 text-primary">bookmark</span> Lesson</a></li>
                    <li><a class="dropdown-item" href="#" id="create-assignment"><span class="material-icons align-middle me-2 text-danger">assignment</span> Assignment</a></li>
                    <li><a class="dropdown-item" href="#" id="create-quiz"><span class="material-icons align-middle me-2 text-secondary">quiz</span> Quiz</a></li>
                </ul>
              </div>
          </div>

          <div class="tab-content" id="courseTabContent">
            <div class="tab-pane fade show active" id="feed-content" role="tabpanel">
                <div id="stream-feed-area"><div class="text-center py-5"><div class="spinner-border text-muted"></div></div></div>
            </div>

            <div class="tab-pane fade" id="curriculum-content" role="tabpanel">
                <h5 class="fw-bold mb-3 text-secondary">Lessons</h5><div id="lessons-list" class="mb-5"></div>
                <h5 class="fw-bold mb-3 text-secondary">Assignments</h5><div id="assignments-list" class="mb-5"></div>
                <h5 class="fw-bold mb-3 text-secondary">Quizzes</h5><div id="quizzes-list" class="mb-5"></div>
            </div>
          </div>
      </div>

      <div id="view-students" style="display:none;">
           <div class="card p-4 shadow-sm border-0"><h5 class="fw-bold border-bottom pb-3 mb-3">Enrolled Students</h5><div id="students-list"><div class="text-center py-5"><div class="spinner-border text-danger"></div></div></div></div>
      </div>

    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const STRAPI_API_TOKEN = "097902687e71fa3c9d71b0bd7e46a5a998ccf78fea375dd83b0ba138fd83c74818f80a3b4411b02fe72a826206f775ded3c4d86a2de908242d265a39b4e097f064a316880f796c47650f6e88c6326d1dd0df3868929c3301368caa0bd5227ffae6a991867d182f02194dad1fd717c2136b14a23b17d9c6fbe11178d04a45ad4c";
  let courseId_input = "<?php echo $course_id; ?>"; 
  const userName = "<?php echo htmlspecialchars($userName); ?>";
  const authHeaders = { 'Content-Type': 'application/json', 'Authorization': `Bearer ${STRAPI_API_TOKEN}` };

  /* --- SWITCHER --- */
  function switchView(event, viewName) {
      if(event) event.preventDefault();
      document.querySelectorAll('.nav-link').forEach(el => el.classList.remove('active'));
      const contentView = document.getElementById('view-content');
      const studentsView = document.getElementById('view-students');
      if (viewName === 'content') {
          contentView.style.display = 'block'; studentsView.style.display = 'none';
          document.getElementById('nav-content').classList.add('active');
          const mob = document.getElementById('nav-mobile-content'); if(mob) mob.classList.add('active');
      } else if (viewName === 'students') {
          contentView.style.display = 'none'; studentsView.style.display = 'block';
          document.getElementById('nav-students').classList.add('active');
          const mob = document.getElementById('nav-mobile-students'); if(mob) mob.classList.add('active');
          loadStudents();
      }
      const offcanvasEl = document.getElementById('offcanvasSidebar');
      const bsOffcanvas = bootstrap.Offcanvas.getInstance(offcanvasEl);
      if(bsOffcanvas) bsOffcanvas.hide();
  }
  
  /* --- TOGGLE CREATE BUTTON --- */
  document.querySelectorAll('.nav-link[data-bs-toggle="tab"]').forEach(tabBtn => {
      tabBtn.addEventListener('shown.bs.tab', function (e) {
          const targetId = e.target.getAttribute('data-bs-target');
          const createBtn = document.getElementById('create-action-wrapper');
          if (targetId === '#curriculum-content') createBtn.style.display = 'block';
          else createBtn.style.display = 'none';
      });
  });

  function isCourseMatch(itemCourse) {
      if (!itemCourse) return false;
      const target = courseId_input.toString();
      if (itemCourse.id && itemCourse.id.toString() === target) return true;
      if (itemCourse.documentId && itemCourse.documentId === target) return true;
      if (itemCourse.data) {
           const c = itemCourse.data;
           if (c.id && c.id.toString() === target) return true;
           if (c.documentId && c.documentId === target) return true;
      }
      return false;
  }

  function copyClassCode() {
    const code = document.getElementById('class-code-display').textContent;
    if (code) { navigator.clipboard.writeText(code); alert('Copied!'); }
  }

  async function deleteItem(type, documentId) {
      let endpoint = type + 's'; if(type === 'quiz') endpoint = 'quizzes';
      if(!confirm("Delete this item?")) return;
      try {
          await fetch(`http://localhost:1337/api/${endpoint}/${documentId}`, { method: 'DELETE', headers: authHeaders });
          alert("Deleted."); loadAllData();
      } catch(e) { alert("Error deleting."); }
  }

  async function loadCourseData() {
      try {
          const res = await fetch('http://localhost:1337/api/courses', { headers: authHeaders });
          const json = await res.json();
          const course = json.data.find(c => c.documentId === courseId_input || c.id.toString() === courseId_input);
          if(course) {
              document.getElementById('course-header-title').textContent = course.title;
              document.getElementById('class-code-display').textContent = course.class_code || '---';
              document.getElementById('create-lesson').onclick = () => location.href=`create-lesson.php?courseId=${courseId_input}`;
              document.getElementById('create-assignment').onclick = () => location.href=`create-assignment.php?courseId=${courseId_input}`;
              document.getElementById('create-quiz').onclick = () => location.href=`create-quiz.php?courseId=${courseId_input}`;
          }
      } catch(e) { console.error(e); }
  }

  /* --- ACCORDION LOADER (For Curriculum) --- */
  async function loadContent(endpoint, type, containerId, icon, bgClass, borderClass) {
      const container = document.getElementById(containerId);
      try {
          const res = await fetch(`http://localhost:1337/api/${endpoint}?populate=*`, { headers: authHeaders });
          const json = await res.json();
          const items = (json.data || []).filter(item => isCourseMatch(item.course));
          
          if(items.length === 0) { container.innerHTML = `<p class="text-muted small">No ${type} yet.</p>`; return; }
          
          let html = '';
          items.forEach(item => {
              const id = item.documentId || item.id;
              const content = item.content || item.description || item.instructions || 'No text content.';
              const parsedContent = marked.parse(content);
              
              let filesHtml = '';
              let files = [];
              if (item.files && Array.isArray(item.files)) files = item.files;
              else if (item.files && item.files.data) files = item.files.data;
              if(item.attachments && item.attachments.data) files = item.attachments.data;

              if (files.length > 0) {
                  files.forEach(f => {
                      const fAttr = f.attributes || f;
                      let url = fAttr.url; if(!url.startsWith('http')) url = `http://localhost:1337${url}`;
                      filesHtml += `<a href="${url}" target="_blank" class="file-attachment"><span class="material-icons" style="font-size:14px">attachment</span> ${fAttr.name}</a>`;
                  });
              }

              html += `
                <div class="content-card ${borderClass} reveal-item" style="display:block">
                    <div class="card-header-row" onclick="toggleCard('card-body-${id}')">
                        <div class="d-flex align-items-center w-100">
                            <div class="icon-box ${bgClass} me-3"><span class="material-icons">${icon}</span></div>
                            <div class="flex-grow-1">
                                <h6 class="fw-bold mb-1">${item.title}</h6>
                                <p class="small text-muted mb-0">Posted: ${new Date(item.createdAt).toLocaleDateString()}</p>
                            </div>
                             <div class="dropdown ms-3" onclick="event.stopPropagation()">
                                <button class="btn btn-light btn-sm rounded-circle" data-bs-toggle="dropdown"><span class="material-icons" style="font-size:18px">more_vert</span></button>
                                <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                    <li><a class="dropdown-item" href="edit-${type}.php?id=${id}&courseId=${courseId_input}">Edit</a></li>
                                    <li><a class="dropdown-item text-danger" href="#" onclick="deleteItem('${type}', '${id}')">Delete</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    <div class="card-body-row" id="card-body-${id}">
                        <div class="mt-2 mb-3">${parsedContent}</div>
                        <div class="d-flex flex-wrap">${filesHtml}</div>
                    </div>
                </div>
              `;
          });
          container.innerHTML = html;
      } catch(e) { container.innerHTML = 'Error loading.'; }
  }
  
  function toggleCard(id) {
      const el = document.getElementById(id);
      el.classList.toggle('show');
  }

  /* --- LINK LOADER (For Stream) --- */
  async function loadStream() {
      const container = document.getElementById('stream-feed-area');
      const url = "populate=*";
      try {
          const [l, a, q] = await Promise.all([
              fetch(`http://localhost:1337/api/lessons?${url}`, {headers:authHeaders}).then(r=>r.json()),
              fetch(`http://localhost:1337/api/assignments?${url}`, {headers:authHeaders}).then(r=>r.json()),
              fetch(`http://localhost:1337/api/quizzes?${url}`, {headers:authHeaders}).then(r=>r.json())
          ]);
          
          let items = [];
          (l.data||[]).filter(i=>isCourseMatch(i.course)).forEach(i=>items.push({...i, t:'Lesson', ico:'bookmark', bg:'bg-lesson', bc:'type-lesson', type:'lesson'}));
          (a.data||[]).filter(i=>isCourseMatch(i.course)).forEach(i=>items.push({...i, t:'Assignment', ico:'assignment', bg:'bg-assign', bc:'type-assignment', type:'assignment'}));
          (q.data||[]).filter(i=>isCourseMatch(i.course)).forEach(i=>items.push({...i, t:'Quiz', ico:'quiz', bg:'bg-quiz', bc:'type-quiz', type:'quiz'}));
          
          items.sort((x,y) => new Date(y.createdAt) - new Date(x.createdAt));
          if(items.length===0) { container.innerHTML = `<div class="text-center py-5 text-muted">No activity yet.</div>`; return; }
          
          let html = '';
          items.forEach(i => {
              const id = i.documentId || i.id;
              
              // LINK LOGIC: Lesson -> View, Others -> Edit
              let linkUrl = `edit-${i.type}.php?id=${id}&courseId=${courseId_input}`;
              if (i.type === 'lesson') linkUrl = `view-lesson.php?id=${id}&courseId=${courseId_input}`;
              if (i.type === 'assignment') linkUrl = `view-assignment.php?id=${id}&courseId=${courseId_input}`; 

              html += `
<div class="content-card ${i.bc} reveal-item mb-3" onclick="location.href='${linkUrl}'">
    <div class="d-flex align-items-center w-100 p-3">
        <div class="icon-box ${i.bg} me-3">
            <span class="material-icons">${i.ico}</span>
        </div>
        <div class="flex-grow-1">
            <h6 class="fw-bold mb-0">You posted a new ${i.t}: ${i.title}</h6>
            <small class="text-muted">${new Date(i.createdAt).toLocaleDateString()}</small>
        </div>
    </div>
</div>`;

          });
          container.innerHTML = html;
      } catch(e) { console.error(e); }
  }

  async function loadStudents() {
      const container = document.getElementById('students-list');
      try {
          const res = await fetch(`http://localhost:1337/api/users?populate=*`, { headers: authHeaders });
          const json = await res.json();
          const students = json.filter(u => {
               if(!u.courses) return false;
               let cList = u.courses; if(cList.data) cList = cList.data; if(!Array.isArray(cList)) return false;
               return cList.some(c => c.documentId === courseId_input || c.id.toString() === courseId_input);
          });
          if(students.length === 0) { container.innerHTML = `<p class="text-muted text-center">No students enrolled.</p>`; return; }
          let html = '<ul class="list-group list-group-flush">';
          students.forEach(s => {
             const init = (s.username || 'U').substring(0,2).toUpperCase();
             const name = (s.username || 'User').replace(/\d+$/, ''); 
             html += `
                <li class="list-group-item d-flex align-items-center gap-3 py-3 border-0">
                    <div class="avatar-circle" style="width:40px;height:40px;background:#eee;color:#333;">${init}</div>
                    <div><div class="fw-bold text-dark">${name}</div><div class="small text-muted">${s.email}</div></div>
                </li>`;
          });
          container.innerHTML = html + '</ul>';
      } catch(e) { console.error(e); container.innerHTML = '<div class="alert alert-danger">Error loading list.</div>'; }
  }

  function loadAllData() {
      loadCourseData();
      loadContent('lessons', 'lesson', 'lessons-list', 'bookmark', 'bg-lesson', 'type-lesson');
      loadContent('assignments', 'assignment', 'assignments-list', 'assignment', 'bg-assign', 'type-assignment');
      loadContent('quizzes', 'quiz', 'quizzes-list', 'quiz', 'bg-quiz', 'type-quiz');
      loadStream();
      loadStudents();
  }

  document.addEventListener('DOMContentLoaded', loadAllData);
</script>
</body>
</html>
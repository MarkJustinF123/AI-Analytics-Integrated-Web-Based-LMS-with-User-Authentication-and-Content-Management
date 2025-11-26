<?php
session_start();

// --- 1. LOGOUT LOGIC (Moved to Top) ---
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

// 3. User Info
$userName = isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Instructor';
$avatarInitials = 'U';
if (!empty($userName)) {
    $nameParts = explode(' ', $userName);
    $avatarInitials = count($nameParts) >= 2 ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1)) : strtoupper(substr($userName, 0, 2));
}

// 4. Get IDs
$course_id = isset($_GET['courseId']) ? htmlspecialchars($_GET['courseId']) : '';
$lesson_id = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '';

if (empty($course_id) || empty($lesson_id)) {
    echo "Error: Missing info.";
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>View Lesson — BSU</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

  <style>
    :root { --bsu-red: #d32f2f; --bg-soft: #f4f6f8; --text-dark: #1a1a1a; }
    body { font-family: 'Inter', sans-serif; background-color: var(--bg-soft); color: var(--text-dark); }

    /* SIDEBAR */
    .sidebar { min-height: 100vh; background: linear-gradient(180deg, var(--bsu-red) 0%, #b71c1c 100%); color: white; }
    .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 20px; margin-bottom: 5px; display: flex; align-items: center; gap: 10px; cursor: pointer; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: rgba(255,255,255,0.2); color: white; border-radius: 8px;}
    .sidebar .brand { padding: 20px; font-size: 1.2rem; font-weight: 700; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; color: white; width: 100%; }
    .logo-img { background: white; border-radius: 50%; padding: 2px; width: 35px; height: 35px; }

    /* MOBILE NAV */
    .mobile-nav { background: var(--bsu-red); color: white; padding: 10px 15px; }

    /* TOP HEADER */
    .top-header {
        background: white; padding: 15px 30px; 
        box-shadow: 0 1px 5px rgba(0,0,0,0.05);
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 30px;
    }
    .avatar-circle { width: 36px; height: 36px; background-color: var(--bsu-red); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.85rem; }

    /* CONTENT CARD */
    .content-card {
        background: white; border-radius: 16px; padding: 40px;
        border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 2px 10px rgba(0,0,0,0.02);
        margin-bottom: 50px;
    }
    .lesson-icon { width: 48px; height: 48px; background: #eef2ff; color: #3b82f6; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .lesson-title { font-size: 2rem; font-weight: 700; margin: 0; line-height: 1.2; }

    /* ATTACHMENT CHIPS */
    .file-chip { 
        display: inline-flex; align-items: center; gap: 10px; 
        background: white; border: 1px solid #e0e0e0; 
        padding: 10px 16px; border-radius: 12px; 
        text-decoration: none; color: #333; font-weight: 500; transition: 0.2s; 
        margin-right: 10px; margin-bottom: 10px;
    }
    .file-chip:hover { border-color: var(--bsu-red); background: #fffbfb; color: var(--bsu-red); }

    /* VIDEO */
    .video-wrapper { position: relative; padding-bottom: 56.25%; height: 0; margin-bottom: 30px; border-radius: 12px; overflow: hidden; background: #000; }
    .video-wrapper iframe { position: absolute; top: 0; left: 0; width: 100%; height: 100%; }

    /* UTILS */
    .user-dropdown-toggle::after { display: none; }
    .offcanvas-header { padding: 0; } .offcanvas-header .btn-close { position: absolute; right: 20px; top: 25px; filter: invert(1); opacity: 1; }
    
    /* Main Content */
    .main-content { padding: 0; }
    @media (max-width: 768px) { .main-content { padding-top: 0; } }
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
        <a class="nav-link" href="manage-course.php?id=<?php echo $course_id; ?>"><span class="material-icons">arrow_back</span> Back to Course</a>
        <div class="my-3 border-top border-light opacity-25"></div>
        
        <a class="nav-link active" href="#"><span class="material-icons">bookmark</span> Lesson View</a>
      </nav>
    </div>

    <div class="offcanvas offcanvas-start sidebar" tabindex="-1" id="offcanvasSidebar">
      <div class="offcanvas-header"><div class="brand">Instructor Portal</div><button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas"></button></div>
      <div class="offcanvas-body p-0">
         <nav class="nav flex-column px-2">
            <a class="nav-link" href="manage-course.php?id=<?php echo $course_id; ?>"><span class="material-icons">arrow_back</span> Back to Course</a>
            <a class="nav-link active" href="#"><span class="material-icons">bookmark</span> Lesson View</a>
         </nav>
      </div>
    </div>

    <main class="col-md-9 col-lg-10 ms-sm-auto px-0">
      
      <div class="top-header">
          <h5 class="m-0 fw-bold text-dark">Lesson Preview</h5>
          
          <div class="d-flex align-items-center gap-3">
              <div class="dropdown">
                <a href="#" class="d-flex align-items-center text-decoration-none user-dropdown-toggle" data-bs-toggle="dropdown">
                  <div class="text-end me-2 d-none d-sm-block">
                      <div style="font-size: 0.75rem; color: #888;">Instructor</div>
                      <div style="font-weight: 600; font-size: 0.9rem; color:#333;"><?php echo htmlspecialchars($userName); ?></div>
                  </div>
                  <div class="avatar-circle"><?php echo htmlspecialchars($avatarInitials); ?></div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0"><li><a class="dropdown-item text-danger" href="?action=logout">Logout</a></li></ul>
              </div>
          </div>
      </div>

      <div class="container-fluid px-4 px-md-5">
        <div class="row justify-content-center">
            
            <div class="col-lg-10 mb-5">
                <div class="content-card">
                    
                    <div class="d-flex justify-content-between align-items-start border-bottom pb-4 mb-4">
                        <div class="d-flex gap-3">
                            <div class="lesson-icon"><span class="material-icons fs-2">bookmark</span></div>
                            <div>
                                <h1 class="lesson-title" id="lessonTitle">Loading...</h1>
                                <div class="text-muted small mt-1" id="lessonDate">Loading info...</div>
                            </div>
                        </div>

                        <div class="dropdown">
                            <button class="btn btn-light btn-sm rounded-circle" data-bs-toggle="dropdown">
                                <span class="material-icons">more_vert</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li>
                                    <a class="dropdown-item" href="edit-lesson.php?id=<?php echo $lesson_id; ?>&courseId=<?php echo $course_id; ?>">
                                        <span class="material-icons align-middle me-2 fs-6">edit</span> Edit
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <a class="dropdown-item text-danger" href="#" onclick="deleteLesson()">
                                        <span class="material-icons align-middle me-2 fs-6">delete</span> Delete
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <div id="videoArea" style="display:none;"></div>

                    <div class="lead text-secondary mb-5" id="lessonContent" style="font-size:1.05rem; line-height:1.8;">
                        <div class="text-center py-5"><div class="spinner-border text-danger"></div></div>
                    </div>

                    <div id="attachmentsSection" style="display:none;">
                        <h6 class="fw-bold text-dark mb-3">Attachments</h6>
                        <div class="d-flex flex-wrap gap-3" id="lessonFiles"></div>
                    </div>

                </div>
            </div>

        </div>
      </div>

    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
  const USER_TOKEN = "<?php echo $_SESSION['idToken']; ?>";
  const LESSON_ID = "<?php echo $lesson_id; ?>";
  const COURSE_ID = "<?php echo $course_id; ?>";
  const authHeaders = { 'Content-Type': 'application/json', 'Authorization': `Bearer ${USER_TOKEN}` };

  async function loadLesson() {
      try {
          // 1. FIX: Fetch ALL data without complex filters (Avoids 400 Error)
          const response = await fetch(`http://localhost:1337/api/lessons?populate=*`, { headers: authHeaders });
          if(!response.ok) throw new Error("API Error");

          const result = await response.json();
          
          // 2. FIX: Client-Side Match (Supports String OR Number ID)
          const lessonData = result.data.find(l => l.documentId === LESSON_ID || l.id.toString() === LESSON_ID);
          
          if (!lessonData) throw new Error("Lesson data missing");

          const lesson = lessonData.attributes || lessonData;

          // 3. Populate
          document.getElementById('lessonTitle').innerText = lesson.title;
          const date = new Date(lesson.createdAt).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' });
          document.getElementById('lessonDate').innerText = `Posted on ${date}`;
          
          document.getElementById('lessonContent').innerHTML = marked.parse(lesson.content || lesson.description || "No content available.");

          // Video
          if (lesson.video_url) {
              const videoId = getYoutubeId(lesson.video_url);
              if (videoId) {
                  document.getElementById('videoArea').innerHTML = `<div class="video-wrapper"><iframe src="https://www.youtube.com/embed/${videoId}" frameborder="0" allowfullscreen></iframe></div>`;
                  document.getElementById('videoArea').style.display = 'block';
              }
          }

          // Attachments (Robust Check)
          let files = [];
          if (lesson.files && Array.isArray(lesson.files)) files = lesson.files; 
          else if (lesson.files && lesson.files.data) files = lesson.files.data;
          else if (lesson.attachments && lesson.attachments.data) files = lesson.attachments.data;

          if (files.length > 0) {
              document.getElementById('attachmentsSection').style.display = 'block';
              const filesCont = document.getElementById('lessonFiles');
              filesCont.innerHTML = '';
              
              files.forEach(f => {
                  const fAttr = f.attributes || f;
                  let url = fAttr.url;
                  if (!url.startsWith('http')) url = `http://localhost:1337${url}`;
                  
                  const icon = (fAttr.mime && fAttr.mime.startsWith('image/')) ? 'image' : 'description';
                  
                  // Clean Display (Icon + Name only)
                  filesCont.innerHTML += `
                    <a href="${url}" target="_blank" class="file-chip">
                        <span class="material-icons fs-5 text-primary">${icon}</span> ${fAttr.name}
                    </a>`;
              });
          }
      } catch (error) { 
          console.error(error);
          document.getElementById('lessonContent').innerHTML = `<div class="alert alert-danger">Error loading lesson.</div>`; 
      }
  }

  async function deleteLesson() {
      if(!confirm("Are you sure you want to delete this lesson?")) return;
      try {
          await fetch(`http://localhost:1337/api/lessons/${LESSON_ID}`, { method: 'DELETE', headers: authHeaders });
          alert("Deleted successfully.");
          window.location.href = `manage-course.php?id=${COURSE_ID}`;
      } catch(e) { alert("Error deleting."); }
  }

  function getYoutubeId(url) {
      const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
      const match = url.match(regExp);
      return (match && match[2].length === 11) ? match[2] : null;
  }

  document.addEventListener('DOMContentLoaded', loadLesson);
</script>
</body>
</html>
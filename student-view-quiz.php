<?php
session_start();

// 1. Security Check
if (!isset($_SESSION['idToken']) || !isset($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

// 2. User Info Setup
$userName = isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Student';
$avatarInitials = 'U';
if (!empty($userName)) {
    $nameParts = explode(' ', $userName);
    $avatarInitials = count($nameParts) >= 2 ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1)) : strtoupper(substr($userName, 0, 2));
}

// 3. Get IDs
$course_id = isset($_GET['courseId']) ? htmlspecialchars($_GET['courseId']) : '';
$quiz_id = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '';

if (empty($course_id) || empty($quiz_id)) { echo "Error: Missing IDs."; exit; }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Quiz Details</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

  <style>
    :root {
      --bsu-red: #d32f2f;
      --bg-soft: #f4f6f8;
      --text-dark: #1a1a1a;
      --gclass-blue: #1a73e8;
    }
    body { font-family: 'Inter', sans-serif; background-color: var(--bg-soft); color: var(--text-dark); }

    /* --- Sidebar --- */
    .sidebar { min-height: 100vh; background: linear-gradient(180deg, var(--bsu-red) 0%, #b71c1c 100%); color: white; transition: all 0.3s; }
    .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 20px; border-radius: 8px; margin-bottom: 5px; display: flex; align-items: center; gap: 10px; font-weight: 500; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: rgba(255,255,255,0.2); color: white; }
    .sidebar .brand { padding: 20px; font-size: 1.2rem; font-weight: 700; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
    .logo-img { background: white; border-radius: 50%; padding: 2px; width: 35px; height: 35px; }
    .mobile-nav { background: var(--bsu-red); color: white; }

    /* --- Header (Fixed to be Full Width) --- */
    .top-header {
        background: white;
        padding: 15px 30px; 
        box-shadow: 0 1px 5px rgba(0,0,0,0.05);
        display: flex; justify-content: space-between; align-items: center;
        margin-bottom: 30px;
        /* Ensure full width logic */
        width: 100%;
    }
    .avatar-circle { width: 36px; height: 36px; background-color: var(--bsu-red); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.85rem; }

    /* --- LEFT PANEL: Content Card --- */
    .content-card {
        background: white; border-radius: 16px; padding: 40px;
        border: 1px solid rgba(0,0,0,0.05); box-shadow: 0 4px 20px rgba(0,0,0,0.02);
        height: 100%;
    }
    .quiz-icon { width: 48px; height: 48px; background: #f3e8ff; color: #9333ea; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .assign-title { font-size: 2rem; font-weight: 700; color: #111; margin: 0; line-height: 1.2; }
    .assign-meta { font-size: 0.9rem; color: #666; margin-top: 5px; }
    
    .instruction-text { font-size: 1rem; line-height: 1.7; color: #444; white-space: pre-line; margin-bottom: 30px; }
    .file-chip { display: inline-flex; align-items: center; gap: 10px; background: white; border: 1px solid #e0e0e0; padding: 10px 16px; border-radius: 12px; text-decoration: none; color: #333; font-weight: 500; font-size: 0.9rem; transition: 0.2s; margin-right: 10px; margin-bottom: 10px; }
    .file-chip:hover { border-color: var(--bsu-red); color: var(--bsu-red); background: #fffbfb; }

    /* --- Quiz Link Button Style --- */
    .quiz-link-container {
        background-color: #e8f0fe;
        border: 1px solid #d2e3fc;
        border-radius: 8px;
        padding: 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 30px;
        margin-top: 20px;
        text-decoration: none;
        transition: background 0.2s;
    }
    .quiz-link-container:hover { background-color: #d2e3fc; cursor: pointer; }
    .quiz-link-left { display: flex; align-items: center; gap: 15px; }
    .quiz-link-title { font-weight: 600; color: var(--gclass-blue); font-size: 1.05rem; }
    .quiz-link-sub { font-size: 0.85rem; color: #5f6368; }

    .user-dropdown-toggle::after { display: none; }
  </style>
</head>
<body>

<nav class="navbar navbar-dark mobile-nav d-md-none">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center gap-2" href="#">
      <img src="images/bsu_logo.png" alt="Logo" width="30" height="30" class="logo-img">
      <span style="font-size: 1rem; font-weight: 700;">BSU Portal</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar">
      <span class="navbar-toggler-icon"></span>
    </button>
  </div>
</nav>

<div class="container-fluid p-0"> <div class="row g-0"> <div class="col-md-3 col-lg-2 d-none d-md-block sidebar p-0">
      <div class="brand">
          <img src="images/bsu_logo.png" alt="BSU" class="logo-img">
          <span>Student Portal</span>
      </div>
      <nav class="nav flex-column px-2">
        <a class="nav-link" href="StudentViewCourse.php?courseId=<?php echo $course_id; ?>"><span class="material-icons">arrow_back</span> Back to Course</a>
        <a class="nav-link active" href="#"><span class="material-icons">quiz</span> Quiz Details</a>
      </nav>
    </div>

    <div class="offcanvas offcanvas-start sidebar" tabindex="-1" id="offcanvasSidebar">
      <div class="offcanvas-header">
        <div class="brand mb-0 border-0">BSU Student Portal</div>
        <button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas"></button>
      </div>
      <div class="offcanvas-body p-0">
         <nav class="nav flex-column px-2">
            <a class="nav-link" href="StudentViewCourse.php?courseId=<?php echo $course_id; ?>"><span class="material-icons">arrow_back</span> Back to Course</a>
            <a class="nav-link active" href="#"><span class="material-icons">quiz</span> Quiz Details</a>
         </nav>
      </div>
    </div>

    <main class="col-md-9 col-lg-10 ms-sm-auto px-0"> <div class="top-header">
          <h5 class="m-0 fw-bold text-dark">Quiz</h5>
          <div class="d-flex align-items-center gap-3">
              <div class="text-end d-none d-md-block">
                  <div style="font-size: 0.75rem; color: #888;">Student</div>
                  <div style="font-weight: 600; font-size: 0.9rem;"><?php echo htmlspecialchars($userName); ?></div>
              </div>
              <div class="avatar-circle"><?php echo htmlspecialchars($avatarInitials); ?></div>
          </div>
      </div>

      <div class="container" style="max-width: 900px; padding-bottom: 50px;">
        <div class="row">
            <div class="col-12">
                <div class="content-card">
                    <div class="d-flex gap-3 border-bottom pb-4 mb-4">
                        <div class="quiz-icon"><span class="material-icons fs-2">quiz</span></div>
                        <div>
                            <h1 class="assign-title" id="quiz-title">Loading...</h1>
                            <div class="assign-meta" id="quiz-meta">Loading info...</div>
                        </div>
                    </div>

                    <div id="quiz-link-area"></div>

                    <h6 class="fw-bold text-dark mb-3">Instructions</h6>
                    <div class="instruction-text" id="quiz-desc">
                        Loading...
                    </div>

                    <div id="attachments-section" style="display:none;">
                        <h6 class="fw-bold text-dark mb-3">Attachments</h6>
                        <div class="d-flex flex-wrap gap-3" id="quiz-files"></div>
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
  const DOC_ID = "<?php echo $quiz_id; ?>";
  const authHeaders = { 'Content-Type': 'application/json', 'Authorization': `Bearer ${USER_TOKEN}` };

  async function loadData() {
    try {
      const res = await fetch(`http://localhost:1337/api/quizzes/${DOC_ID}?populate=*`, { headers: authHeaders });
      if (!res.ok) throw new Error('Failed');
      const json = await res.json();
      const item = json.data;

      document.getElementById('quiz-title').textContent = item.title;
      
      const pts = item.points ? item.points : '100';
      let dateDisplay = "No Due Date";
      if (item.dueDate) {
          const d = new Date(item.dueDate);
          dateDisplay = "Due " + d.toLocaleDateString('en-US', { month: 'long', day: 'numeric' });
      }
      document.getElementById('quiz-meta').textContent = `${dateDisplay} • ${pts} Points`;
      document.getElementById('quiz-desc').textContent = item.description || 'No instructions provided.';

      // Handle Link (The Blue Box)
      if (item.link) {
          document.getElementById('quiz-link-area').innerHTML = `
            <a href="${item.link}" target="_blank" class="quiz-link-container">
                <div class="quiz-link-left">
                    <span class="material-icons" style="color: #1a73e8; font-size: 28px;">link</span>
                    <div>
                        <div class="quiz-link-title">Open Quiz Link</div>
                        <div class="quiz-link-sub">Click to start the quiz in a new tab</div>
                    </div>
                </div>
                <span class="material-icons text-muted">open_in_new</span>
            </a>`;
      }

      // Handle Files
      const filesCont = document.getElementById('quiz-files');
      filesCont.innerHTML = '';
      if (item.files && item.files.length > 0) {
        document.getElementById('attachments-section').style.display = 'block';
        item.files.forEach(f => {
            const u = `http://localhost:1337${f.url}`;
            const icon = (f.mime && f.mime.startsWith('image/')) ? 'image' : 'description';
            filesCont.innerHTML += `
            <a href="${u}" target="_blank" class="file-chip">
                <span class="material-icons fs-5 text-secondary">${icon}</span> ${f.name}
            </a>`;
        });
      }
    } catch (e) { console.error(e); }
  }
  loadData();
</script>
</body>
</html>
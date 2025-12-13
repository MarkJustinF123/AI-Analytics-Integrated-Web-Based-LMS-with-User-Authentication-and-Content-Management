<?php
session_start();
if (!isset($_SESSION['idToken']) || !isset($_SESSION['email'])) { header('Location: login.php'); exit; }

$userName = isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Student';
$avatarInitials = 'U';
if (!empty($userName)) {
    $nameParts = explode(' ', $userName);
    $avatarInitials = count($nameParts) >= 2 ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1)) : strtoupper(substr($userName, 0, 2));
}

$course_id = isset($_GET['courseId']) ? htmlspecialchars($_GET['courseId']) : '';
$lesson_id = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '';
if (empty($course_id) || empty($lesson_id)) { echo "Error: Missing IDs."; exit; }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Lesson View</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

  <style>
    :root {
      --bsu-red: #d32f2f;
      --bg-soft: #f4f6f8;
      --text-dark: #1a1a1a;
    }
    body { font-family: 'Inter', sans-serif; background-color: var(--bg-soft); color: var(--text-dark); }

    /* SIDEBAR */
    .sidebar { min-height: 100vh; background: linear-gradient(180deg, var(--bsu-red) 0%, #b71c1c 100%); color: white; }
    .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 20px; margin-bottom: 5px; display: flex; align-items: center; gap: 10px; }
    .sidebar .nav-link:hover { background-color: rgba(255,255,255,0.2); color: white; border-radius: 8px;}
    .sidebar .brand { padding: 20px; font-size: 1.2rem; font-weight: 700; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
    .logo-img { background: white; border-radius: 50%; padding: 2px; width: 35px; height: 35px; }
    .mobile-nav { background: var(--bsu-red); color: white; }

    /* HEADER (Same as Assignment) */
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
    }
    .lesson-icon { width: 48px; height: 48px; background: #eef2ff; color: #3b82f6; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .lesson-title { font-size: 2rem; font-weight: 700; margin: 0; line-height: 1.2; }
    
    .file-chip { display: inline-flex; align-items: center; gap: 10px; background: white; border: 1px solid #e0e0e0; padding: 10px 16px; border-radius: 12px; text-decoration: none; color: #333; font-weight: 500; transition: 0.2s; }
    .file-chip:hover { border-color: var(--bsu-red); background: #fffbfb; }

    .user-dropdown-toggle::after { display: none; }
  </style>
</head>
<body>

<nav class="navbar navbar-dark mobile-nav d-md-none">
  <div class="container-fluid">
    <a class="navbar-brand d-flex align-items-center gap-2" href="#">
      <span class="material-icons">menu</span>
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar"><span class="navbar-toggler-icon"></span></button>
  </div>
</nav>

<div class="container-fluid">
  <div class="row">
    
    <div class="col-md-3 col-lg-2 d-none d-md-block sidebar p-0">
      <div class="brand"><img src="images/bsu_logo.png" class="logo-img"><span>Student Portal</span></div>
      <nav class="nav flex-column px-2">
        <a class="nav-link" href="StudentViewCourse.php?courseId=<?php echo $course_id; ?>"><span class="material-icons">arrow_back</span> Back to Course</a>
        <a class="nav-link active" href="#"><span class="material-icons">bookmark</span> Lesson View</a>
      </nav>
    </div>

    <main class="col-md-9 col-lg-10 ms-sm-auto px-0">
      
      <div class="top-header">
          <h5 class="m-0 fw-bold text-dark">Lesson</h5>
          <div class="d-flex align-items-center gap-3">
              <div class="text-end">
                  <div style="font-size: 0.75rem; color: #888;">Student</div>
                  <div style="font-weight: 600; font-size: 0.9rem;"><?php echo htmlspecialchars($userName); ?></div>
              </div>
              <div class="avatar-circle"><?php echo htmlspecialchars($avatarInitials); ?></div>
          </div>
      </div>

      <div class="container-fluid px-5">
        <div class="row justify-content-center">
            
            <div class="col-lg-10 mb-5">
                <div class="content-card">
                    <div class="d-flex gap-3 border-bottom pb-4 mb-4">
                        <div class="lesson-icon"><span class="material-icons fs-2">bookmark</span></div>
                        <div>
                            <h1 class="lesson-title" id="lesson-title">Loading...</h1>
                            <div class="text-muted small mt-1" id="lesson-date">Loading info...</div>
                        </div>
                    </div>

                    <div class="lead text-secondary mb-5" id="lesson-content" style="font-size:1.05rem; line-height:1.8; white-space: pre-line;">
                        Loading content...
                    </div>

                    <div id="attachments-section" style="display:none;">
                        <h6 class="fw-bold text-dark mb-3">Attachments</h6>
                        <div class="d-flex flex-wrap gap-3" id="lesson-files"></div>
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
  const DOC_ID = "<?php echo $lesson_id; ?>";
  const authHeaders = { 'Content-Type': 'application/json', 'Authorization': `Bearer ${USER_TOKEN}` };

  async function loadData() {
    try {
      const res = await fetch(`http://localhost:1337/api/lessons/${DOC_ID}?populate=*`, { headers: authHeaders });
      if (!res.ok) throw new Error('Failed');
      const json = await res.json();
      const item = json.data;

      document.getElementById('lesson-title').textContent = item.title;
      const dateStr = new Date(item.createdAt).toLocaleDateString('en-US', { month: 'long', day: 'numeric' });
      document.getElementById('lesson-date').textContent = `Posted on ${dateStr}`;
      document.getElementById('lesson-content').textContent = item.content || 'No text content.';

      const filesCont = document.getElementById('lesson-files');
      filesCont.innerHTML = '';
      if (item.files && item.files.length > 0) {
        document.getElementById('attachments-section').style.display = 'block';
        item.files.forEach(f => {
            const u = `http://localhost:1337${f.url}`;
            const icon = (f.mime && f.mime.startsWith('image/')) ? 'image' : 'description';
            filesCont.innerHTML += `
            <a href="${u}" target="_blank" class="file-chip">
                <span class="material-icons fs-5 text-primary">${icon}</span> ${f.name}
            </a>`;
        });
      }
    } catch (e) { console.error(e); }
  }
  loadData();
</script>
</body>
</html>
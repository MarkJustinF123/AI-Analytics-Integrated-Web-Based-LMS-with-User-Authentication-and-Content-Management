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

$course_id = isset($_GET['courseId']) ? htmlspecialchars($_GET['courseId']) : '';
if (empty($course_id)) { echo "No course ID provided."; exit; }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Create Quiz — BSU</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

  <style>
    :root { --bsu-red: #d32f2f; --bsu-green: #198754; --bg-light: #f4f7f6; --text-dark: #1a1a1a; }
    body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); color: var(--text-dark); }

    /* Sidebar */
    .sidebar { min-height: 100vh; background: linear-gradient(180deg, var(--bsu-red) 0%, #b71c1c 100%); color: white; }
    .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 20px; margin-bottom: 5px; display: flex; align-items: center; gap: 10px; cursor: pointer; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: rgba(255,255,255,0.2); color: white; border-radius: 8px;}
    .sidebar .brand { padding: 20px; font-size: 1.2rem; font-weight: 700; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; color: white; width: 100%; }
    
    /* Mobile Nav */
    .mobile-nav { background: var(--bsu-red); color: white; padding: 10px 15px; }
    
    /* Main Content */
    .main-content { padding: 20px; padding-bottom: 100px; }
    @media (max-width: 768px) { .main-content { padding-top: 20px; } }

    /* Form Card */
    .form-card { background: white; border-radius: 16px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 30px; }
    .form-label { font-weight: 600; color: #555; margin-bottom: 8px; }
    .form-control, .form-select { border-radius: 8px; padding: 12px; border: 1px solid #ddd; }
    .form-control:focus, .form-select:focus { border-color: var(--bsu-red); box-shadow: 0 0 0 4px rgba(211, 47, 47, 0.1); }

    /* Create Button (GREEN) */
    .btn-create {
        background-color: var(--bsu-green); /* Green */
        color: white;
        border-radius: 50px; padding: 10px 30px; font-weight: 600; border: none;
        transition: all 0.2s;
    }
    .btn-create:hover { background-color: #146c43; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3); }

    /* File Upload Area */
    .upload-area { border: 2px dashed #ddd; border-radius: 12px; padding: 30px; text-align: center; cursor: pointer; transition: 0.2s; background: #fafafa; }
    .upload-area:hover { border-color: var(--bsu-red); background: #fff5f5; }
    
    /* File Item (with X button) */
    .file-item { display: flex; align-items: center; justify-content: space-between; background: #f8f9fa; padding: 10px 15px; border-radius: 8px; margin-top: 10px; font-size: 0.9rem; border: 1px solid #eee; }
    .btn-remove-file { background: transparent; border: none; color: #dc3545; cursor: pointer; padding: 5px; display: flex; align-items: center; justify-content: center; }
    .btn-remove-file:hover { background: #ffebee; border-radius: 50%; }

    /* Utils */
    .user-dropdown-toggle::after { display: none; }
    .avatar-circle { width: 40px; height: 40px; background-color: var(--bsu-red); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }
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
        <a class="nav-link" href="manage-course.php?id=<?php echo $course_id; ?>"><span class="material-icons">arrow_back</span> Back to Course</a>
        <div class="my-3 border-top border-light opacity-25"></div>
        <a class="nav-link active" href="#"><span class="material-icons">quiz</span> Create Quiz</a>
      </nav>
    </div>

    <div class="offcanvas offcanvas-start sidebar" tabindex="-1" id="offcanvasSidebar">
      <div class="offcanvas-header"><div class="brand">Instructor Portal</div><button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas"></button></div>
      <div class="offcanvas-body p-0">
         <nav class="nav flex-column px-2">
            <a class="nav-link" href="manage-course.php?id=<?php echo $course_id; ?>"><span class="material-icons">arrow_back</span> Back to Course</a>
            <a class="nav-link active" href="#"><span class="material-icons">quiz</span> Create Quiz</a>
         </nav>
      </div>
    </div>

    <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 main-content">
      
      <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
        <h2 class="h2 fw-bold m-0">New Quiz</h2>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none user-dropdown-toggle" id="profileDropdown" data-bs-toggle="dropdown">
               <div class="text-end d-none d-md-block me-3"><span class="fw-bold text-dark"><?php echo htmlspecialchars($userName); ?></span></div>
               <div class="avatar-circle shadow-sm"><?php echo htmlspecialchars($avatarInitials); ?></div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0"><li><a class="dropdown-item text-danger" href="?action=logout">Logout</a></li></ul>
        </div>
      </div>

      <div class="row justify-content-center">
          <div class="col-lg-8">
              <div class="form-card">
                  <div class="mb-4">
                      <label class="form-label">Quiz Title</label>
                      <input type="text" class="form-control form-control-lg" id="quiz-title" placeholder="Enter quiz title here">
                  </div>
                  <div class="mb-4">
                      <label class="form-label">Description / Instructions</label>
                      <textarea class="form-control" id="quiz-description" rows="4" placeholder="Instructions for students"></textarea>
                  </div>
                  <div class="row mb-4">
                      <div class="col-md-6">
                          <label class="form-label">Points</label>
                          <input type="number" class="form-control" id="quiz-points" value="100">
                      </div>
                      <div class="col-md-6">
                          <label class="form-label">Due Date & Time</label>
                          <input type="datetime-local" class="form-control" id="quiz-due">
                      </div>
                  </div>
                  <div class="mb-4">
                      <label class="form-label">External Link (Google Form, etc.)</label>
                      <div class="input-group">
                          <span class="input-group-text bg-white"><span class="material-icons fs-6">link</span></span>
                          <input type="text" class="form-control" id="quiz-link" placeholder="https://forms.google.com/...">
                      </div>
                  </div>
                  <div class="mb-4">
                      <label class="form-label">Attachments</label>
                      <div class="upload-area" id="upload-btn">
                          <span class="material-icons fs-1 text-muted">cloud_upload</span>
                          <p class="mb-0 mt-2 fw-bold text-muted">Click to upload files</p>
                      </div>
                      <input type="file" id="file-input" multiple style="display: none;">
                      <div id="file-list" class="mt-3"></div>
                  </div>

                  <div class="d-flex justify-content-end">
                      <button class="btn-create" id="create-btn">Post Quiz</button>
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
  const courseId = "<?php echo $course_id; ?>";
  const authHeaders = { 'Authorization': `Bearer ${STRAPI_API_TOKEN}` };

  let uploadedFiles = []; // Stores objects: {id, name}

  // --- FILE UPLOAD ---
  const uploadBtn = document.getElementById('upload-btn');
  const fileInput = document.getElementById('file-input');
  const fileListDisplay = document.getElementById('file-list');

  uploadBtn.addEventListener('click', () => fileInput.click());

  fileInput.addEventListener('change', () => {
    const files = Array.from(fileInput.files);
    if (files.length === 0) return;
    
    fileListDisplay.innerHTML += '<div class="text-muted small mt-2">Uploading...</div>';
    const uploadPromises = files.map(uploadFile);
    
    Promise.all(uploadPromises).then(results => {
        results.forEach(f => uploadedFiles.push(f));
        renderFiles();
    }).catch(err => alert('Upload failed.'));
  });

  async function uploadFile(file) {
    const formData = new FormData();
    formData.append('files', file); 
    const response = await fetch('http://localhost:1337/api/upload', { method: 'POST', headers: authHeaders, body: formData });
    if (!response.ok) throw new Error('Upload failed.');
    const result = await response.json();
    return { id: result[0].id, name: result[0].name };
  }

  // --- RENDER LIST ---
  function renderFiles() {
      fileListDisplay.innerHTML = '';
      uploadedFiles.forEach((file, index) => {
          fileListDisplay.innerHTML += `
            <div class="file-item">
                <div class="d-flex align-items-center"><span class="material-icons text-success me-2">check_circle</span><span class="fw-bold text-dark">${file.name}</span></div>
                <button class="btn-remove-file" onclick="removeFile(${index})" title="Remove"><span class="material-icons">close</span></button>
            </div>`;
      });
  }

  // --- REMOVE FILE ---
  window.removeFile = function(index) {
      uploadedFiles.splice(index, 1);
      renderFiles();
  }

  // --- CREATE ---
  document.getElementById('create-btn').addEventListener('click', async () => {
    const btn = document.getElementById('create-btn');
    const title = document.getElementById('quiz-title').value;
    const desc = document.getElementById('quiz-description').value;
    const points = document.getElementById('quiz-points').value;
    const due = document.getElementById('quiz-due').value;
    const link = document.getElementById('quiz-link').value;

    if (!title) { alert('Title is required.'); return; }
    
    btn.disabled = true; btn.textContent = 'Creating...';

    const url = 'http://localhost:1337/api/quizzes';
    const body = {
      data: {
        title: title,
        description: desc,
        points: points,
        dueDate: due || null,
        link: link,
        files: uploadedFiles.map(f => f.id), 
        course: courseId
      }
    };

    try {
      const response = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'Authorization': `Bearer ${STRAPI_API_TOKEN}` },
        body: JSON.stringify(body)
      });

      if (!response.ok) throw new Error('Create failed.');
      alert('Quiz created!');
      window.location.href = `manage-course.php?id=${courseId}`;
    } catch (error) {
      console.error(error);
      alert('Error creating quiz.');
      btn.disabled = false; btn.textContent = 'Post Quiz';
    }
  });
</script>
</body>
</html>
<?php
session_start();
// 1. Check Auth
if (!isset($_SESSION['idToken']) || !isset($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}

// 2. User Info
$userName = isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Instructor';
$avatarInitials = 'U';
if (!empty($userName)) {
    $nameParts = explode(' ', $userName);
    $avatarInitials = count($nameParts) >= 2 ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1)) : strtoupper(substr($userName, 0, 2));
}

// 3. Get IDs
$course_id = isset($_GET['courseId']) ? htmlspecialchars($_GET['courseId']) : '';
$lesson_id = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '';

if (empty($course_id) || empty($lesson_id)) {
    echo "Error: Missing info.";
    exit;
}

// 4. Logout Logic
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
  <title>Edit Lesson — BSU</title>
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
    .logo-img { background: white; border-radius: 50%; padding: 2px; width: 35px; height: 35px; }

    /* Mobile Nav */
    .mobile-nav { background: var(--bsu-red); color: white; padding: 10px 15px; }
    
    /* Main Content */
    .main-content { padding: 20px; padding-bottom: 100px; }
    @media (max-width: 768px) { .main-content { padding-top: 20px; } }

    /* Form Card */
    .form-card {
        background: white; border-radius: 16px; padding: 40px;
        box-shadow: 0 4px 20px rgba(0,0,0,0.05);
        margin-bottom: 30px;
    }
    .form-label { font-weight: 600; color: #555; margin-bottom: 8px; }
    .form-control { border-radius: 8px; padding: 12px; border: 1px solid #ddd; }
    .form-control:focus { border-color: var(--bsu-red); box-shadow: 0 0 0 4px rgba(211, 47, 47, 0.1); }

    /* Update Button */
    .btn-update {
        background-color: var(--bsu-green); color: white;
        border-radius: 50px; padding: 10px 30px; font-weight: 600; border: none;
        transition: all 0.2s;

    }
    .btn-update:hover { background-color: #146c43; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3); }

    /* File Upload Area */
    .upload-area {
        border: 2px dashed #ddd; border-radius: 12px; padding: 30px;
        text-align: center; cursor: pointer; transition: 0.2s; background: #fafafa;
    }
    .upload-area:hover { border-color: var(--bsu-red); background: #fff5f5; }
    
    /* --- ATTACHMENT CARD STYLES --- */
    .attachment-item {
        display: flex; align-items: center; justify-content: space-between;
        background: #f8f9fa; border: 1px solid #e9ecef;
        padding: 12px 15px; border-radius: 10px; margin-bottom: 10px;
        transition: 0.2s;
    }
    /* HOVER EFFECT: Green Border & Light Green Background */
    .attachment-item:hover { 
        border-color: var(--bsu-green); 
        background: #f0fdf4; /* Light green tint */
    }

    /* The link part (left side) */
    .file-link {
        display: flex; align-items: center; text-decoration: none; color: #333; flex-grow: 1;
    }
    .file-link:hover { color: #111; }

    /* The Delete Button (X) */
    .btn-remove-file {
        background: transparent; border: none; color: #999;
        padding: 5px; border-radius: 50%; transition: 0.2s;
        display: flex; align-items: center; justify-content: center;
    }
    .btn-remove-file:hover { background: #ffebee; color: var(--bsu-red); }
    
    /* Utilities */
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
      <div class="brand"><img src="images/bsu_logo.png" width="35" class="me-2"> Instructor Portal</div>
      <nav class="nav flex-column px-2">
        <a class="nav-link" href="view-lesson.php?id=<?php echo $lesson_id; ?>&courseId=<?php echo $course_id; ?>"><span class="material-icons">arrow_back</span> Back to Lesson</a>
        <div class="my-3 border-top border-light opacity-25"></div>
        <a class="nav-link active" href="#"><span class="material-icons">edit</span> Edit Mode</a>
      </nav>
    </div>

    <div class="offcanvas offcanvas-start sidebar" tabindex="-1" id="offcanvasSidebar">
      <div class="offcanvas-header"><div class="brand">Instructor Portal</div><button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas"></button></div>
      <div class="offcanvas-body p-0">
         <nav class="nav flex-column px-2">
            <a class="nav-link" href="view-lesson.php?id=<?php echo $lesson_id; ?>&courseId=<?php echo $course_id; ?>"><span class="material-icons">arrow_back</span> Back to Lesson</a>
            <a class="nav-link active" href="#"><span class="material-icons">edit</span> Edit Mode</a>
         </nav>
      </div>
    </div>

    <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 main-content">
      
      <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
        <h2 class="h2 fw-bold m-0">Edit Lesson</h2>
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
                      <label class="form-label">Lesson Title</label>
                      <input type="text" class="form-control form-control-lg" id="lesson-title" placeholder="Enter title here">
                  </div>
                  
                  <div class="mb-4">
                      <label class="form-label">Instructions / Description</label>
                      <textarea class="form-control" id="lesson-content" rows="8" placeholder="Write your lesson content here"></textarea>
                  </div>

                  <div class="mb-4">
                      <label class="form-label">Attachments</label>
                      
                      <div id="file-list" class="mb-3"></div>
                      
                      <div class="upload-area" id="upload-btn">
                          <span class="material-icons fs-1 text-muted">cloud_upload</span>
                          <p class="mb-0 mt-2 fw-bold text-muted">Click to upload new files</p>
                      </div>
                      <input type="file" id="file-input" multiple style="display: none;">
                  </div>

                  <div class="d-flex justify-content-end">
                      <button class="btn-update" id="post-lesson-btn">Update Lesson</button>
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
  const lessonId = "<?php echo $lesson_id; ?>";
  
  const headers = { 'Content-Type': 'application/json', 'Authorization': `Bearer ${STRAPI_API_TOKEN}` };
  const authHeaders = { 'Authorization': `Bearer ${STRAPI_API_TOKEN}` };

  let uploadedFileIds = []; 
  let uploadedFileObjects = []; // To store details for rendering

  // --- LOAD DATA ---
  async function loadLessonData() {
    // Robust Fetch (String or Number ID)
    let url = `http://localhost:1337/api/lessons/${lessonId}?populate=*`;
    if(isNaN(lessonId)) url = `http://localhost:1337/api/lessons?filters[documentId][$eq]=${lessonId}&populate=*`;

    try {
      const response = await fetch(url, { headers: headers });
      if (!response.ok) throw new Error('Failed to fetch lesson data.');
      const result = await response.json();
      
      // Handle list vs single object
      const lesson = Array.isArray(result.data) ? result.data[0] : result.data;
      if (!lesson) throw new Error('Lesson data empty.');

      // Populate Form
      document.getElementById('lesson-title').value = lesson.title || '';
      document.getElementById('lesson-content').value = lesson.content || '';
      
      // Load Files (Robust Check)
      let files = [];
      if (lesson.files && Array.isArray(lesson.files)) files = lesson.files; 
      else if (lesson.files && lesson.files.data) files = lesson.files.data;
      else if (lesson.attachments && lesson.attachments.data) files = lesson.attachments.data;
      
      if (files.length > 0) {
          // Store both ID and display details
          uploadedFileObjects = files.map(f => ({
              id: f.id,
              name: f.attributes ? f.attributes.name : f.name,
              url: f.attributes ? f.attributes.url : f.url,
              size: f.attributes ? f.attributes.size : f.size
          }));
          
          uploadedFileIds = uploadedFileObjects.map(f => f.id);
          renderFileList();
      }
    } catch (error) { alert('Error loading data.'); }
  }

  // --- RENDER FILE LIST ---
  function renderFileList() {
      const container = document.getElementById('file-list');
      container.innerHTML = '';

      uploadedFileObjects.forEach(file => {
          let fileSize = file.size ? (file.size).toFixed(1) + ' KB' : '';
          
          // Handle URL (ensure absolute)
          let fileUrl = file.url;
          if(!fileUrl.startsWith('http')) fileUrl = `http://localhost:1337${fileUrl}`;

          container.innerHTML += `
            <div class="attachment-item" id="file-row-${file.id}">
               <a href="${fileUrl}" target="_blank" class="file-link">
                   <span class="material-icons fs-5 text-success me-3">description</span>
                   <div>
                       <div class="fw-bold">${file.name}</div>
                       <div class="small text-muted">${fileSize}</div>
                   </div>
               </a>
               <button type="button" class="btn-remove-file" onclick="removeFile(${file.id})" title="Remove File">
                   <span class="material-icons">close</span>
               </button>
            </div>
          `;
      });
  }

  // --- REMOVE FILE LOGIC ---
  window.removeFile = function(idToRemove) {
      // 1. Remove from ID array (for saving)
      uploadedFileIds = uploadedFileIds.filter(id => id !== idToRemove);
      // 2. Remove from Object array (for rendering)
      uploadedFileObjects = uploadedFileObjects.filter(f => f.id !== idToRemove);
      // 3. Re-render
      renderFileList();
  }

  // --- UPLOAD ---
  const uploadBtn = document.getElementById('upload-btn');
  const fileInput = document.getElementById('file-input');

  uploadBtn.addEventListener('click', () => fileInput.click());
  fileInput.addEventListener('change', () => {
    const files = fileInput.files;
    if (files.length === 0) return;
    
    // Show loading state
    document.getElementById('upload-btn').innerHTML = '<span class="spinner-border spinner-border-sm text-muted"></span> Uploading...';

    const uploadPromises = Array.from(files).map(uploadFile);
    
    Promise.all(uploadPromises).then(fileInfos => {
        // Add new files to arrays
        fileInfos.forEach(f => {
            uploadedFileIds.push(f.id);
            uploadedFileObjects.push(f);
        });
        renderFileList();
        // Reset button
        document.getElementById('upload-btn').innerHTML = '<span class="material-icons fs-1 text-muted">cloud_upload</span><p class="mb-0 mt-2 fw-bold text-muted">Click to upload new files</p>';
    }).catch(err => {
        alert('Upload failed.');
        document.getElementById('upload-btn').innerHTML = '<span class="material-icons fs-1 text-danger">error</span><p class="mb-0 mt-2 fw-bold text-danger">Upload Failed. Try Again.</p>';
    });
  });

  async function uploadFile(file) {
    const formData = new FormData();
    formData.append('files', file); 
    const response = await fetch('http://localhost:1337/api/upload', { method: 'POST', headers: authHeaders, body: formData });
    if (!response.ok) throw new Error('Upload failed.');
    const result = await response.json();
    const data = result[0];
    return { id: data.id, name: data.name, url: data.url, size: data.size };
  }

  // --- UPDATE ---
  document.getElementById('post-lesson-btn').addEventListener('click', async () => {
    const btn = document.getElementById('post-lesson-btn');
    const title = document.getElementById('lesson-title').value;
    const content = document.getElementById('lesson-content').value;

    if (!title) { alert('Title is required.'); return; }
    
    btn.disabled = true; btn.textContent = 'Saving...';

    // Simple PUT update. v5 handles relations via ID if configured standardly.
    let url = `http://localhost:1337/api/lessons/${lessonId}`;
    
    // Note: For Strapi v5 Document Service, editing usually requires documentId endpoint if enforced.
    // But basic Update via ID usually works if "Document ID" logic isn't strict on PUT.
    // If this fails for string IDs, we might need to query ID first.
    
    const body = { data: { title, content, files: uploadedFileIds } };

    try {
      const response = await fetch(url, { method: 'PUT', headers: headers, body: JSON.stringify(body) });
      
      // Fallback for v5 DocumentID if standard ID fails (usually returns 404/400)
      if (!response.ok) {
           // Try filtering to find real numeric ID if we have doc ID
           // OR logic here assumes standard update works.
           throw new Error('Update failed.'); 
      }

      alert('Lesson updated!');
      window.location.href = `view-lesson.php?id=${lessonId}&courseId=${courseId}`;
    } catch (error) {
      console.error(error);
      alert('Error updating lesson.');
      btn.disabled = false; btn.textContent = 'Save Changes';
    }
  });
  
  document.addEventListener('DOMContentLoaded', loadLessonData);
</script>
</body>
</html>
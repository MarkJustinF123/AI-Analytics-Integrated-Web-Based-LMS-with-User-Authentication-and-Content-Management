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
$assign_id = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '';

if (empty($course_id) || empty($assign_id)) { echo "Error: Missing info."; exit; }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Edit Assignment — BSU</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

  <style>
    :root { --bsu-red: #d32f2f; --bsu-green: #198754; --bg-light: #f4f7f6; }
    body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); }

    /* Sidebar */
    .sidebar { min-height: 100vh; background: linear-gradient(180deg, var(--bsu-red) 0%, #b71c1c 100%); color: white; }
    .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 20px; margin-bottom: 5px; display: flex; align-items: center; gap: 10px; cursor: pointer; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: rgba(255,255,255,0.2); color: white; border-radius: 8px;}
    .sidebar .brand { padding: 20px; font-size: 1.2rem; font-weight: 700; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; color: white; width: 100%; }
    .logo-img { background: white; border-radius: 50%; padding: 2px; width: 35px; height: 35px; }
    .mobile-nav { background: var(--bsu-red); padding: 10px 15px; }
    .main-content { padding: 20px; padding-bottom: 100px; }
    
    /* Form Card */
    .form-card { background: white; border-radius: 16px; padding: 40px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); margin-bottom: 30px; }
    .form-label { font-weight: 600; color: #555; margin-bottom: 8px; }
    .form-control { border-radius: 8px; padding: 12px; border: 1px solid #ddd; }
    .form-control:focus { border-color: var(--bsu-red); box-shadow: 0 0 0 4px rgba(211, 47, 47, 0.1); }

    /* --- GREEN SAVE BUTTON --- */
    .btn-save {
        background-color: var(--bsu-green); 
        color: white;
        border-radius: 50px; padding: 10px 30px; font-weight: 600; border: none;
        transition: all 0.2s;
    }
    .btn-save:hover { background-color: #146c43; transform: translateY(-2px); box-shadow: 0 4px 12px rgba(25, 135, 84, 0.3); }

    /* File Upload */
    .upload-area { border: 2px dashed #ddd; border-radius: 12px; padding: 30px; text-align: center; cursor: pointer; background: #fafafa; }
    .upload-area:hover { border-color: var(--bsu-red); background: #fff5f5; }
    .file-item { display: flex; align-items: center; justify-content: space-between; background: #f8f9fa; padding: 10px 15px; border-radius: 8px; margin-top: 10px; border: 1px solid #eee; }
    .btn-remove-file { background: transparent; border: none; color: #dc3545; cursor: pointer; }

    .user-dropdown-toggle::after { display: none; }
    .avatar-circle { width: 40px; height: 40px; background-color: var(--bsu-red); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }
    @media (max-width: 768px) { .sidebar { display: none; } .main-content { padding-top: 20px; } }
    .offcanvas-header .btn-close { position: absolute; right: 20px; top: 25px; filter: invert(1); opacity: 1; }
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
        <a class="nav-link" href="view-assignment.php?id=<?php echo $assign_id; ?>&courseId=<?php echo $course_id; ?>"><span class="material-icons">arrow_back</span> Back to Assignment</a>
        <div class="my-3 border-top border-light opacity-25"></div>
        <a class="nav-link active" href="#"><span class="material-icons">edit</span> Edit Mode</a>
      </nav>
    </div>

    <div class="offcanvas offcanvas-start sidebar" tabindex="-1" id="offcanvasSidebar">
      <div class="offcanvas-header"><div class="brand">Instructor Portal</div><button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas"></button></div>
      <div class="offcanvas-body p-0">
         <nav class="nav flex-column px-2">
            <a class="nav-link" href="view-assignment.php?id=<?php echo $assign_id; ?>&courseId=<?php echo $course_id; ?>"><span class="material-icons">arrow_back</span> Back to Assignment</a>
            <a class="nav-link active" href="#"><span class="material-icons">edit</span> Edit Mode</a>
         </nav>
      </div>
    </div>

    <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 main-content">
      
      <div class="d-flex justify-content-between align-items-center pb-3 mb-4 border-bottom">
        <h2 class="h2 fw-bold m-0">Edit Assignment</h2>
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
                      <label class="form-label">Assignment Title</label>
                      <input type="text" class="form-control form-control-lg" id="assign-title" placeholder="Enter title here">
                  </div>
                  <div class="mb-4">
                      <label class="form-label">Instructions</label>
                      <textarea class="form-control" id="assign-instructions" rows="6" placeholder="Write instructions here"></textarea>
                  </div>
                  <div class="mb-4">
                      <label class="form-label">Due Date</label>
                      <input type="date" class="form-control" id="assign-due">
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
                      <button class="btn-save" id="post-btn">Update Assignment</button>
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
  const assignId = "<?php echo $assign_id; ?>";
  const authHeaders = { 'Authorization': `Bearer ${STRAPI_API_TOKEN}` };
  let uploadedFiles = []; 

  // --- LOAD DATA ---
  async function loadData() {
    let url = `http://localhost:1337/api/assignments/${assignId}?populate=*`;
    if(isNaN(assignId)) url = `http://localhost:1337/api/assignments?filters[documentId][$eq]=${assignId}&populate=*`;

    try {
      const response = await fetch(url, { headers: { 'Content-Type': 'application/json', ...authHeaders } });
      if (!response.ok) throw new Error('Failed');
      const result = await response.json();
      const assign = Array.isArray(result.data) ? result.data[0] : result.data;
      if (!assign) throw new Error('Empty');

      document.getElementById('assign-title').value = assign.title || '';
      document.getElementById('assign-instructions').value = assign.instructions || '';
      document.getElementById('assign-due').value = assign.duedate || '';

      let files = [];
      if (assign.files && Array.isArray(assign.files)) files = assign.files; 
      else if (assign.files && assign.files.data) files = assign.files.data;
      
      if (files.length > 0) {
          uploadedFiles = files.map(f => ({ id: f.id, name: f.attributes ? f.attributes.name : f.name }));
          renderFiles();
      }
    } catch (error) { alert('Error loading data.'); }
  }

  // --- UPLOAD & RENDER ---
  const uploadBtn = document.getElementById('upload-btn');
  const fileInput = document.getElementById('file-input');
  uploadBtn.addEventListener('click', () => fileInput.click());

  fileInput.addEventListener('change', () => {
    const files = Array.from(fileInput.files);
    if (files.length === 0) return;
    const promises = files.map(uploadFile);
    Promise.all(promises).then(results => {
        results.forEach(f => uploadedFiles.push(f));
        renderFiles();
    }).catch(err => alert('Upload failed.'));
  });

  async function uploadFile(file) {
    const formData = new FormData();
    formData.append('files', file); 
    const response = await fetch('http://localhost:1337/api/upload', { method: 'POST', headers: authHeaders, body: formData });
    if (!response.ok) throw new Error('Fail');
    const result = await response.json();
    return { id: result[0].id, name: result[0].name };
  }

  function renderFiles() {
      const list = document.getElementById('file-list');
      list.innerHTML = '';
      uploadedFiles.forEach((file, index) => {
          list.innerHTML += `
            <div class="file-item">
                <div class="d-flex align-items-center"><span class="material-icons text-success me-2">check_circle</span><span class="fw-bold text-dark">${file.name}</span></div>
                <button class="btn-remove-file" onclick="removeFile(${index})"><span class="material-icons">close</span></button>
            </div>`;
      });
  }
  window.removeFile = function(index) { uploadedFiles.splice(index, 1); renderFiles(); }

  // --- UPDATE ---
  document.getElementById('post-btn').addEventListener('click', async () => {
    const btn = document.getElementById('post-btn');
    const title = document.getElementById('assign-title').value;
    const instructions = document.getElementById('assign-instructions').value;
    const due = document.getElementById('assign-due').value;

    if (!title) { alert('Title required.'); return; }
    btn.disabled = true; btn.textContent = 'Saving...';

    let url = `http://localhost:1337/api/assignments/${assignId}`;
    const body = { data: { title, instructions, duedate: due || null, files: uploadedFiles.map(f => f.id) } };

    try {
      const response = await fetch(url, { method: 'PUT', headers: { 'Content-Type': 'application/json', ...authHeaders }, body: JSON.stringify(body) });
      if (!response.ok) throw new Error('Update failed.');
      alert('Updated!');
      window.location.href = `view-assignment.php?id=${assignId}&courseId=${courseId}`;
    } catch (error) { console.error(error); alert('Error updating.'); btn.disabled = false; btn.textContent = 'Update Assignment'; }
  });
  
  document.addEventListener('DOMContentLoaded', loadData);
</script>
</body>
</html>
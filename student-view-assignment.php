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
$userName = isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Student';
$avatarInitials = 'U';
if (!empty($userName)) {
    $nameParts = explode(' ', $userName);
    $avatarInitials = count($nameParts) >= 2 ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1)) : strtoupper(substr($userName, 0, 2));
}

// 4. Get IDs
$course_id = isset($_GET['courseId']) ? htmlspecialchars($_GET['courseId']) : '';
$assign_id = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '';

if (empty($course_id) || empty($assign_id)) {
    echo "Error: Missing info.";
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Assignment — BSU</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

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

    /* Top Header */
    .top-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; padding: 10px 0; border-bottom: 1px solid #eee; }

    /* Content Card */
    .card-box { background: white; border-radius: 16px; padding: 30px; box-shadow: 0 2px 10px rgba(0,0,0,0.03); border: 1px solid rgba(0,0,0,0.05); margin-bottom: 30px; }
    
    /* Assignment Header */
    .assign-icon { width: 50px; height: 50px; background: #fef2f2; color: var(--bsu-red); border-radius: 12px; display: flex; align-items: center; justify-content: center; margin-right: 20px; flex-shrink: 0; }
    .assign-title { font-size: 1.5rem; font-weight: 700; margin-bottom: 5px; }
    .points-badge { background: #f8f9fa; border: 1px solid #ddd; padding: 5px 12px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; color: #555; white-space: nowrap; }

    /* Submission Form */
    .form-label { font-weight: 600; margin-bottom: 8px; }
    .form-control { border-radius: 10px; padding: 12px; border: 1px solid #ddd; background: #fafafa; }
    .form-control:disabled { background: #f9f9f9; cursor: not-allowed; }
    .form-control:focus { background: white; border-color: var(--bsu-red); box-shadow: 0 0 0 4px rgba(211, 47, 47, 0.1); }

    /* File Upload */
    .upload-area { border: 2px dashed #ddd; border-radius: 12px; padding: 20px; text-align: center; cursor: pointer; transition: 0.2s; background: #fafafa; }
    .upload-area:hover { border-color: var(--bsu-red); background: #fff5f5; }

    /* Selected File Item */
    .selected-file { display: flex; align-items: center; justify-content: space-between; background: #f0fdf4; border: 1px solid #bbf7d0; padding: 10px 15px; border-radius: 8px; margin-top: 10px; }
    .btn-remove-file { background: transparent; border: none; color: #dc2626; cursor: pointer; display: flex; align-items: center; padding: 5px; border-radius: 50%; }
    .btn-remove-file:hover { background: #fee2e2; }

    /* Buttons */
    .btn-submit { background-color: var(--bsu-green); color: white; border-radius: 50px; padding: 12px 40px; font-weight: 600; border: none; transition: all 0.2s; width: 100%; }
    .btn-submit:hover { background-color: #157347; transform: translateY(-2px); box-shadow: 0 4px 15px rgba(25, 135, 84, 0.3); }
    
    /* Submitted State */
    .btn-submitted { background-color: #6c757d; cursor: not-allowed; box-shadow: none !important; transform: none !important; }

    /* Attachments */
    .file-chip { display: inline-flex; align-items: center; gap: 8px; background: white; border: 1px solid #eee; padding: 8px 14px; border-radius: 10px; text-decoration: none; color: #333; font-size: 0.9rem; margin-right: 10px; transition: 0.2s; }
    .file-chip:hover { border-color: var(--bsu-red); color: var(--bsu-red); }

    /* Utils */
    .user-dropdown-toggle::after { display: none; }
    .avatar-circle { width: 40px; height: 40px; background-color: var(--bsu-red); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }
    .logo-img { background: white; border-radius: 50%; padding: 2px; width: 35px; height: 35px; }
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
      <div class="brand"><img src="images/bsu_logo.png" width="35" class="me-2"> Student Portal</div>
      <nav class="nav flex-column px-2">
        <a class="nav-link" href="StudentViewCourse.php?courseId=<?php echo $course_id; ?>"><span class="material-icons">arrow_back</span> Back to Course</a>
      </nav>
      <a class="nav-link active" href="assignment-view.php?courseId=<?php echo $course_id; ?>&id=<?php echo $assign_id; ?>">
    <span class="material-icons">assignment</span> Assignment View
</a>

    </div>

    <div class="offcanvas offcanvas-start sidebar" tabindex="-1" id="offcanvasSidebar">
      <div class="offcanvas-header"><div class="brand">Student Portal</div><button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas"></button></div>
      <div class="offcanvas-body p-0">
         <nav class="nav flex-column px-2">
            <a class="nav-link" href="StudentViewCourse.php?courseId=<?php echo $course_id; ?>"><span class="material-icons">arrow_back</span> Back to Course</a>
         </nav>
      </div>
    </div>

    <main class="col-md-9 col-lg-10 ms-sm-auto px-md-4 main-content">
      
      <div class="top-header">
          <h4 class="fw-bold m-0 text-dark">Assignment</h4>
          <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none user-dropdown-toggle" id="profileDropdown" data-bs-toggle="dropdown">
               <div class="text-end d-none d-md-block me-3"><small class="text-muted d-block" style="font-size: 0.8rem;">Student</small><span class="fw-bold text-dark"><?php echo htmlspecialchars($userName); ?></span></div>
               <div class="avatar-circle shadow-sm"><?php echo htmlspecialchars($avatarInitials); ?></div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0"><li><a class="dropdown-item text-danger" href="?action=logout">Logout</a></li></ul>
          </div>
      </div>

      <div class="row justify-content-center">
          <div class="col-lg-8">
              
              <div class="card-box">
                  <div class="d-flex justify-content-between align-items-start mb-4">
                      <div class="d-flex align-items-center">
                          <div class="assign-icon"><span class="material-icons fs-3">assignment</span></div>
                          <div>
                              <h1 class="assign-title" id="assignTitle">Loading...</h1>
                              <div class="text-muted small" id="assignDue">Due: --</div>
                          </div>
                      </div>
                      <span class="points-badge">100 Points</span>
                  </div>
                  
                  <div class="text-secondary mb-4" id="assignInstructions" style="line-height: 1.6; font-size: 1rem;">
                      Loading instructions...
                  </div>

                  <div id="attachmentSection" style="display:none; border-top: 1px solid #eee; padding-top: 20px;">
                      <h6 class="fw-bold text-dark mb-3">Reference Materials</h6>
                      <div id="fileList" class="d-flex flex-wrap"></div>
                  </div>
              </div>

              <div class="card-box">
                  <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                      <h5 class="fw-bold m-0">Your Work</h5>
                      <span id="submissionStatus" class="badge bg-success d-none">Turned In</span>
                  </div>
                  
                  <div class="mb-4">
                      <label class="form-label">Text Response</label>
                      <textarea class="form-control" id="studentAnswer" rows="6" placeholder="Type your answer here"></textarea>
                  </div>

                  <div class="mb-4" id="uploadSection">
                      <label class="form-label">Attach File (Optional)</label>
                      
                      <div class="upload-area" id="upload-trigger">
                          <span class="material-icons fs-2 text-muted">cloud_upload</span>
                          <p class="mb-0 small text-muted">Click to attach a file</p>
                      </div>
                      <input type="file" id="studentFile" style="display: none;">
                      
                      <div id="selectedFileContainer"></div>
                  </div>
                  
                  <div id="existingFileDisplay" class="mb-4 d-none">
                      <label class="form-label">Attached File</label>
                      <div id="existingFileLink"></div>
                  </div>
                  
                  <div class="mt-4">
                      <button class="btn-submit" id="turnInBtn">
                          <span class="material-icons align-middle me-2" style="font-size:18px">send</span> Turn In
                      </button>
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
  const ASSIGN_ID = "<?php echo $assign_id; ?>";
  const USER_ID = <?php echo json_encode($_SESSION['user']['strapi_id']); ?>; 
  const authHeaders = { 'Authorization': `Bearer ${STRAPI_API_TOKEN}` };
  
  // --- LOAD ASSIGNMENT ---
  async function loadAssignment() {
      try {
          let url = `http://localhost:1337/api/assignments/${ASSIGN_ID}?populate=*`;
          if(isNaN(ASSIGN_ID)) url = `http://localhost:1337/api/assignments?filters[documentId][$eq]=${ASSIGN_ID}&populate=*`;

          const res = await fetch(url, { headers: { 'Content-Type': 'application/json', ...authHeaders } });
          const json = await res.json();
          const data = Array.isArray(json.data) ? json.data[0] : json.data;
          const assign = data.attributes || data;

          document.getElementById('assignTitle').innerText = assign.title;
          const date = assign.duedate ? new Date(assign.duedate).toLocaleDateString() : 'No Due Date';
          document.getElementById('assignDue').innerText = `Due: ${date}`;
          document.getElementById('assignInstructions').innerHTML = marked.parse(assign.instructions || 'No instructions.');

          let files = [];
          if (assign.files && Array.isArray(assign.files)) files = assign.files;
          else if (assign.files && assign.files.data) files = assign.files.data;

          if (files.length > 0) {
              document.getElementById('attachmentSection').style.display = 'block';
              const cont = document.getElementById('fileList');
              files.forEach(f => {
                  const attr = f.attributes || f;
                  let u = attr.url.startsWith('http') ? attr.url : `http://localhost:1337${attr.url}`;
                  cont.innerHTML += `<a href="${u}" target="_blank" class="file-chip"><span class="material-icons fs-6 align-middle text-primary">attachment</span> ${attr.name}</a>`;
              });
          }
          
          // IMPORTANT: Check for existing submission
          checkSubmission();

      } catch (e) { console.error(e); }
  }

    // --- CHECK SUBMISSION (SERVER-SIDE FILTERING -- FIXED) ---
    async function checkSubmission() {
        try {
            const query =
                `filters[users_permissions_user][id][$eq]=${USER_ID}` +
                `&filters[assignment][documentId][$eq]=${ASSIGN_ID}` + 
                `&populate=*`;

            const url = `http://localhost:1337/api/submissions?${query}`;

            const res = await fetch(url, { headers: authHeaders });
            const json = await res.json();

            if (json.data.length > 0) {
                lockUI(json.data[0]);
            }
        } catch (e) {
            console.error("Error checking submission:", e);
        }
    }


  // --- LOCK UI ---
  function lockUI(submission) {
      const attr = submission.attributes || submission;
      
      // Disable Fields
      document.getElementById('studentAnswer').value = attr.content || '';
      document.getElementById('studentAnswer').disabled = true;
      document.getElementById('uploadSection').style.display = 'none';
      
      // Show Badge
      document.getElementById('submissionStatus').classList.remove('d-none');

      // Disable Button
      const btn = document.getElementById('turnInBtn');
      btn.innerHTML = '<span class="material-icons align-middle me-2">check_circle</span> Turned In';
      btn.classList.add('btn-submitted');
      btn.disabled = true;
      
      // Show Submitted File
      if (attr.file) {
          let fData = attr.file.data || attr.file;
          if (Array.isArray(fData)) fData = fData[0]; // Get first file
          
          if (fData) {
              const fAttr = fData.attributes || fData;
              let u = fAttr.url.startsWith('http') ? fAttr.url : `http://localhost:1337${fAttr.url}`;
              document.getElementById('existingFileDisplay').classList.remove('d-none');
              document.getElementById('existingFileLink').innerHTML = 
                 `<a href="${u}" target="_blank" class="file-chip"><span class="material-icons fs-6 align-middle text-primary">attachment</span> ${fAttr.name}</a>`;
          }
      }
  }


  // --- FILE HANDLING ---
  const fileInput = document.getElementById('studentFile');
  const uploadTrigger = document.getElementById('upload-trigger');
  const fileContainer = document.getElementById('selectedFileContainer');

  uploadTrigger.addEventListener('click', () => fileInput.click());

  fileInput.addEventListener('change', () => {
      if (fileInput.files.length > 0) {
          const file = fileInput.files[0];
          fileContainer.innerHTML = `
            <div class="selected-file">
                <div class="d-flex align-items-center gap-2">
                    <span class="material-icons text-success">check_circle</span>
                    <span class="fw-bold small">${file.name}</span>
                </div>
                <button class="btn-remove-file" onclick="removeSelectedFile()"><span class="material-icons">close</span></button>
            </div>`;
      }
  });

  window.removeSelectedFile = function() {
      fileInput.value = ''; // Clear input
      fileContainer.innerHTML = ''; // Clear UI
  }

  // --- SUBMIT WORK ---
  document.getElementById('turnInBtn').addEventListener('click', async () => {
      const answer = document.getElementById('studentAnswer').value;
      const btn = document.getElementById('turnInBtn');

      if(!answer && fileInput.files.length === 0) { alert("Please add a response or file."); return; }

      btn.disabled = true; 
      btn.innerHTML = '<span class="spinner-border spinner-border-sm"></span> Submitting...';

      // 1. UPLOAD FILE IF EXISTS
      let uploadedFileId = null;
      if(fileInput.files.length > 0) {
          try {
              const formData = new FormData();
              formData.append('files', fileInput.files[0]);
              const uploadRes = await fetch('http://localhost:1337/api/upload', { method: 'POST', headers: authHeaders, body: formData });
              if(!uploadRes.ok) throw new Error("Upload failed");
              const uploadJson = await uploadRes.json();
              uploadedFileId = uploadJson[0].id;
          } catch(e) { alert("File upload error."); btn.disabled = false; return; }
      }

      // 2. PREPARE DATA (No AI Feedback)
      const submissionBody = {
          data: {
              content: answer,
              users_permissions_user: USER_ID, 
              assignment: ASSIGN_ID,
              submission_status: 'submitted',
              file: uploadedFileId ? [uploadedFileId] : null
          }
      };

      try {
          const res = await fetch('http://localhost:1337/api/submissions', {
              method: 'POST',
              headers: { 'Content-Type': 'application/json', ...authHeaders },
              body: JSON.stringify(submissionBody)
          });

          if(!res.ok) {
              const err = await res.json();
              console.error("API Error:", err);
              throw new Error('Submission failed');
          }
          
          const newSub = await res.json();
          alert("Assignment Turned In!");
          lockUI(newSub.data); // Immediately lock UI

      } catch(e) {
          console.error(e);
          alert("Error submitting assignment. Check console.");
          btn.disabled = false;
          btn.innerHTML = 'Turn In';
      }
  });

  document.addEventListener('DOMContentLoaded', loadAssignment);
</script>
</body>
</html>
<?php
session_start();
// 1. Auth
if (!isset($_SESSION['idToken']) || !isset($_SESSION['email'])) { header('Location: login.php'); exit; }
if (isset($_GET['action']) && $_GET['action'] === 'logout') { session_unset(); session_destroy(); header('Location: login.php'); exit; }

$userName = isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Instructor';
$avatarInitials = 'U';
if (!empty($userName)) {
    $nameParts = explode(' ', $userName);
    $avatarInitials = count($nameParts) >= 2 ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1)) : strtoupper(substr($userName, 0, 2));
}

// 2. IDs
$course_id = isset($_GET['courseId']) ? htmlspecialchars($_GET['courseId']) : '';
$assign_id = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '';
if (empty($course_id) || empty($assign_id)) { echo "Error: Missing info."; exit; }
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>View Assignment — BSU</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>

  <style>
    :root { --bsu-red: #d32f2f; --bsu-green: #198754; --bg-soft: #f4f6f8; --text-dark: #1a1a1a; }
    body { font-family: 'Inter', sans-serif; background-color: var(--bg-soft); color: var(--text-dark); }

    /* SIDEBAR */
    .sidebar { min-height: 100vh; background: linear-gradient(180deg, var(--bsu-red) 0%, #b71c1c 100%); color: white; }
    .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 20px; margin-bottom: 5px; display: flex; align-items: center; gap: 10px; cursor: pointer; }
    .sidebar .nav-link:hover { background-color: rgba(255,255,255,0.2); color: white; border-radius: 8px; }
    .sidebar .brand { padding: 20px; font-size: 1.2rem; font-weight: 700; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; color: white; width: 100%; }
    .logo-img { background: white; border-radius: 50%; padding: 2px; width: 35px; height: 35px; }
    .mobile-nav { background: var(--bsu-red); padding: 10px 15px; }

    /* HEADER */
    .top-header { background: white; padding: 15px 30px; box-shadow: 0 1px 5px rgba(0,0,0,0.05); display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; }
    .avatar-circle { width: 36px; height: 36px; background-color: var(--bsu-red); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 0.85rem; }

    /* CARD */
    .assign-container { background: white; border-radius: 16px; box-shadow: 0 2px 10px rgba(0,0,0,0.02); border: 1px solid rgba(0,0,0,0.05); overflow: hidden; min-height: 80vh; margin-bottom: 50px; }
    .assign-header { padding: 40px 40px 0 40px; }
    .icon-box { width: 48px; height: 48px; background: #fef2f2; color: #d32f2f; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .assign-title { font-size: 1.8rem; font-weight: 700; margin: 0; line-height: 1.2; }
    .meta-info { font-size: 0.9rem; color: #666; margin-top: 5px; display: flex; gap: 15px; align-items: center; }

    /* TABS */
    .nav-tabs { border-bottom: 1px solid #eee; padding: 0 40px; margin-top: 30px; }
    .nav-tabs .nav-link { border: none; color: #666; font-weight: 500; padding: 15px 0; margin-right: 30px; background: transparent; border-bottom: 3px solid transparent; }
    .nav-tabs .nav-link.active { color: var(--bsu-red); border-bottom-color: var(--bsu-red); font-weight: 600; }
    .tab-content { padding: 40px; }

    /* ATTACHMENTS */
    .file-chip { display: inline-flex; align-items: center; gap: 10px; background: white; border: 1px solid #e0e0e0; padding: 10px 16px; border-radius: 12px; text-decoration: none; color: #333; font-weight: 500; transition: 0.2s; margin-right: 10px; margin-bottom: 10px; }
    .file-chip:hover { border-color: var(--bsu-red); background: #fffbfb; color: var(--bsu-red); }

    /* STUDENT WORK TABLE */
    .student-row { display: flex; align-items: center; padding: 15px 0; border-bottom: 1px solid #eee; }
    .student-info { display: flex; align-items: center; gap: 15px; flex: 2; }
    .status-badge { font-size: 0.75rem; font-weight: 600; padding: 4px 10px; border-radius: 20px; }
    .status-submitted { background: #e0f2fe; color: #0369a1; } /* Blue for Submitted */
    .status-graded { background: #dcfce7; color: #166534; }    /* Green for Graded */
    .status-missing { background: #fee2e2; color: #ef4444; }

    /* Grade Inputs */
    .grade-input { width: 70px; text-align: center; border: 1px solid #ddd; border-radius: 6px; padding: 6px; font-weight: 600; }
    .grade-input:focus { border-color: var(--bsu-green); outline: none; }

    .user-dropdown-toggle::after { display: none; }
    .offcanvas-header { padding: 0; } .offcanvas-header .btn-close { position: absolute; right: 20px; top: 25px; filter: invert(1); opacity: 1; }
    @media (max-width: 768px) { .main-content { padding: 0; } }
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
        <a class="nav-link active" href="#"><span class="material-icons">assignment</span> Assignment View</a>
      </nav>
    </div>

    <div class="offcanvas offcanvas-start sidebar" tabindex="-1" id="offcanvasSidebar">
      <div class="offcanvas-header"><div class="brand">Instructor Portal</div><button type="button" class="btn-close btn-close-white text-reset" data-bs-dismiss="offcanvas"></button></div>
      <div class="offcanvas-body p-0">
         <nav class="nav flex-column px-2">
            <a class="nav-link" href="manage-course.php?id=<?php echo $course_id; ?>"><span class="material-icons">arrow_back</span> Back to Course</a>
            <a class="nav-link active" href="#"><span class="material-icons">assignment</span> Assignment View</a>
         </nav>
      </div>
    </div>

    <main class="col-md-9 col-lg-10 ms-sm-auto px-0">
      <div class="top-header">
          <h5 class="m-0 fw-bold text-dark">Assignment Details</h5>
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
            <div class="col-lg-11 mb-5">
                <div class="assign-container">
                    
                    <div class="assign-header d-flex justify-content-between align-items-start">
                        <div class="d-flex gap-3">
                            <div class="icon-box"><span class="material-icons fs-3">assignment</span></div>
                            <div>
                                <h1 class="assign-title" id="assignTitle">Loading...</h1>
                                <div class="meta-info">
                                    <span><span class="material-icons" style="font-size:14px; vertical-align: text-bottom;">event</span> <span id="assignDue">--</span></span>
                                    <span>•</span>
                                    <span>100 Points</span>
                                </div>
                            </div>
                        </div>

                        <div class="dropdown">
                            <button class="btn btn-light btn-sm rounded-circle" data-bs-toggle="dropdown">
                                <span class="material-icons">more_vert</span>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                                <li><a class="dropdown-item" href="edit-assignment.php?id=<?php echo $assign_id; ?>&courseId=<?php echo $course_id; ?>"><span class="material-icons align-middle me-2 fs-6">edit</span> Edit</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger" href="#" onclick="deleteAssignment()"><span class="material-icons align-middle me-2 fs-6">delete</span> Delete</a></li>
                            </ul>
                        </div>
                    </div>

                    <ul class="nav nav-tabs" id="assignTab" role="tablist">
                      <li class="nav-item"><button class="nav-link active" id="instructions-tab" data-bs-toggle="tab" data-bs-target="#instructions" type="button">Instructions</button></li>
                      <li class="nav-item"><button class="nav-link" id="student-work-tab" data-bs-toggle="tab" data-bs-target="#student-work" type="button">Student Work</button></li>
                    </ul>

                    <div class="tab-content">
                      <div class="tab-pane fade show active" id="instructions">
                          <div id="assignContent" class="lead text-secondary" style="font-size:1rem; line-height:1.8;"><div class="text-center py-5"><div class="spinner-border text-danger"></div></div></div>
                          <div id="attachmentsSection" class="mt-5 pt-3 border-top" style="display:none;">
                              <h6 class="fw-bold text-dark mb-3">Attachments</h6>
                              <div class="d-flex flex-wrap gap-3" id="assignFiles"></div>
                          </div>
                      </div>

                      <div class="tab-pane fade" id="student-work">
                          <div id="submissionList">
                              <p class="text-muted text-center py-5">Loading submissions...</p>
                          </div>
                      </div>
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
  const STRAPI_API_TOKEN = "097902687e71fa3c9d71b0bd7e46a5a998ccf78fea375dd83b0ba138fd83c74818f80a3b4411b02fe72a826206f775ded3c4d86a2de908242d265a39b4e097f064a316880f796c47650f6e88c6326d1dd0df3868929c3301368caa0bd5227ffae6a991867d182f02194dad1fd717c2136b14a23b17d9c6fbe11178d04a45ad4c";
  const ASSIGN_ID = "<?php echo $assign_id; ?>";
  const COURSE_ID = "<?php echo $course_id; ?>";
  const authHeaders = { 'Content-Type': 'application/json', 'Authorization': `Bearer ${STRAPI_API_TOKEN}` };

  async function loadAssignment() {
      try {
          const response = await fetch(`http://localhost:1337/api/assignments?populate=*`, { headers: authHeaders });
          const json = await response.json();
          const data = json.data.find(a => a.documentId === ASSIGN_ID || a.id.toString() === ASSIGN_ID);
          if (!data) throw new Error("Assignment not found");
          
          const assign = data.attributes || data;

          document.getElementById('assignTitle').innerText = assign.title;
          const date = assign.duedate ? new Date(assign.duedate).toLocaleDateString() : 'No Due Date';
          document.getElementById('assignDue').innerText = `Due: ${date}`;
          document.getElementById('assignContent').innerHTML = marked.parse(assign.instructions || "No instructions provided.");

          // Load Files (same logic as before)
          let files = [];
          if (assign.files && Array.isArray(assign.files)) files = assign.files;
          else if (assign.files && assign.files.data) files = assign.files.data;

          if (files.length > 0) {
              document.getElementById('attachmentsSection').style.display = 'block';
              const cont = document.getElementById('assignFiles');
              cont.innerHTML = '';
              files.forEach(f => {
                  const attr = f.attributes || f;
                  let u = attr.url.startsWith('http') ? attr.url : `http://localhost:1337${attr.url}`;
                  cont.innerHTML += `<a href="${u}" target="_blank" class="file-chip"><span class="material-icons fs-6 align-middle text-primary">attachment</span> ${attr.name}</a>`;
              });
          }
          loadSubmissions();
      } catch (e) { document.getElementById('assignContent').innerHTML = `<div class="alert alert-danger">Error loading.</div>`; }
  }

  // --- LOAD SUBMISSIONS ---
  async function loadSubmissions() {
      const container = document.getElementById('submissionList');
      try {
          const res = await fetch(`http://localhost:1337/api/submissions?populate=*`, { headers: authHeaders });
          if (!res.ok) return;
          const json = await res.json();
          
          const submissions = json.data.filter(sub => {
              const attr = sub.attributes || sub;
              const assignData = attr.assignment?.data || attr.assignment;
              if(!assignData) return false;
              return (assignData.documentId === ASSIGN_ID || assignData.id.toString() === ASSIGN_ID);
          });

          if (submissions.length === 0) { container.innerHTML = `<div class="text-center py-5 text-muted">No submissions yet.</div>`; return; }

          let html = '';
          submissions.forEach(sub => {
              const attr = sub.attributes || sub;
              const id = sub.documentId || sub.id; // Use this for updating

              // User Data
              const userRel = attr.users_permissions_user || attr.student; 
              let studentName = 'Student';
              let init = 'U';
              if (userRel) {
                   const userObj = userRel.data ? (userRel.data.attributes || userRel.data) : userRel;
                   if (userObj) { studentName = userObj.username; init = studentName.substring(0,2).toUpperCase(); }
              }
              
              // Status & Grade
              const status = attr.submission_status || 'submitted';
              let badgeClass = 'status-submitted';
              if(status === 'graded') badgeClass = 'status-graded';
              else if(status === 'missing') badgeClass = 'status-missing';

              const gradeValue = attr.grade || '';
              
              // File
              let fileLink = '';
              if (attr.file) {
                  let fData = attr.file.data || attr.file;
                  if(Array.isArray(fData)) fData = fData[0];
                  if(fData && fData.url) {
                      let url = fData.url.startsWith('http') ? fData.url : `http://localhost:1337${fData.url}`;
                      fileLink = `<a href="${url}" target="_blank" class="text-primary small text-decoration-none ms-2"><span class="material-icons align-middle" style="font-size:14px">attachment</span> File</a>`;
                  }
              }
              
              const aiFeedback = attr.ai_feedback ? attr.ai_feedback.replace(/'/g, "\\'") : '';

              html += `
                <div class="student-row">
                    <div class="student-info">
                        <div class="avatar-circle" style="width:35px;height:35px;background:#eee;color:#333;font-size:0.8rem;">${init}</div>
                        <div>
                            <div class="fw-bold text-dark">${studentName}</div>
                            ${fileLink}
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <span class="status-badge ${badgeClass}">${status}</span>
                        
                        ${aiFeedback ? `<button class="btn btn-sm btn-light border" onclick="alert('AI Feedback:\\n${aiFeedback}')"><span class="material-icons align-middle" style="font-size:16px; color:#0d6efd;">auto_awesome</span></button>` : ''}

                        <div class="d-flex align-items-center gap-1">
                           <input type="number" class="grade-input" id="grade-${id}" value="${gradeValue}" placeholder="/100">
                           <button class="btn btn-sm btn-outline-success rounded-pill" onclick="saveGrade('${id}')">Save</button>
                        </div>
                    </div>
                </div>`;
          });
          container.innerHTML = html;
      } catch (e) { console.error(e); }
  }

  // --- SAVE GRADE FUNCTION ---
  async function saveGrade(submissionId) {
      const gradeInput = document.getElementById(`grade-${submissionId}`);
      const gradeVal = gradeInput.value;
      
      if(gradeVal === '') { alert("Please enter a grade"); return; }
      
      const btn = gradeInput.nextElementSibling;
      const originalText = btn.innerText;
      btn.innerText = "...";
      btn.disabled = true;

      // Assuming Strapi v5 /api/submissions/[DocumentID]
      // Note: Ensure your Submission collection has 'grade' field (Number)
      
      const body = {
          data: {
              grade: parseInt(gradeVal),
              submission_status: 'graded'
          }
      };

      try {
          const res = await fetch(`http://localhost:1337/api/submissions/${submissionId}`, {
              method: 'PUT',
              headers: authHeaders,
              body: JSON.stringify(body)
          });
          
          if(!res.ok) throw new Error('Failed to grade');
          
          alert("Grade Saved!");
          loadSubmissions(); // Refresh list to show updated status
      } catch(e) {
          console.error(e);
          alert("Error saving grade.");
          btn.innerText = originalText;
          btn.disabled = false;
      }
  }

  async function deleteAssignment() {
      if(!confirm("Delete this assignment?")) return;
      let url = `http://localhost:1337/api/assignments/${ASSIGN_ID}`;
      try {
          let res = await fetch(url, { method: 'DELETE', headers: authHeaders });
          if(!res.ok) throw new Error('Failed');
          alert("Deleted."); window.location.href = `manage-course.php?id=${COURSE_ID}`;
      } catch(e) { alert("Error deleting."); }
  }

  document.addEventListener('DOMContentLoaded', loadAssignment);
</script>
</body>
</html>
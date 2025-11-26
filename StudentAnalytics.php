<?php
session_start();
// Check Auth
if (!isset($_SESSION['idToken'])) { header('Location: login.php'); exit; }

// User Info
$userName = $_SESSION['user']['name'] ?? 'Instructor';
$avatarInitials = 'U';
if (!empty($userName)) {
    $nameParts = explode(' ', $userName);
    $avatarInitials = count($nameParts) >= 2 ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1)) : strtoupper(substr($userName, 0, 2));
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<title>Performance Analytics — BSU</title>
<meta name="viewport" content="width=device-width, initial-scale=1">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">

<style>
    :root { --bsu-red: #d32f2f; --bsu-dark: #1a1a1a; --bg-light: #f4f7f6; }
    body { font-family: 'Inter', sans-serif; background-color: var(--bg-light); }

    /* --- ANIMATIONS --- */
    @keyframes slideUpFade {
        0% { opacity: 0; transform: translateY(40px); }
        100% { opacity: 1; transform: translateY(0); }
    }
    .reveal-card {
        opacity: 0; /* Start hidden */
        animation: slideUpFade 0.8s cubic-bezier(0.2, 0.8, 0.2, 1) forwards;
    }

    /* Sidebar */
    .sidebar { min-height: 100vh; background: linear-gradient(180deg, var(--bsu-red) 0%, #b71c1c 100%); color: white; }
    .sidebar .nav-link { color: rgba(255,255,255,0.8); padding: 12px 20px; margin-bottom: 5px; display: flex; align-items: center; gap: 10px; }
    .sidebar .nav-link:hover, .sidebar .nav-link.active { background-color: rgba(255,255,255,0.2); color: white; border-radius: 8px; }
    .sidebar .brand { padding: 20px; font-size: 1.2rem; font-weight: 700; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
    .logo-img { background: white; border-radius: 50%; padding: 2px; width: 35px; height: 35px; }
    
    /* Main Layout */
    .main-content { padding: 30px; padding-bottom: 100px; }

    /* Header */
    .top-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 30px; border-bottom: 1px solid #eee; padding-bottom: 15px; }
    .avatar-circle { width: 40px; height: 40px; background-color: var(--bsu-red); color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-weight: bold; }

    /* Student Card */
    .student-card {
        background: white; border-radius: 16px; padding: 25px; margin-bottom: 25px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.05); border: 1px solid rgba(0,0,0,0.02);
        transition: transform 0.2s;
    }
    .student-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(0,0,0,0.08); }

    /* Score Badge */
    .score-box {
        text-align: center; min-width: 80px;
        border-left: 1px solid #eee; padding-left: 20px;
    }
    .score-val { font-size: 2.2rem; font-weight: 800; line-height: 1; }
    .score-label { font-size: 0.7rem; text-transform: uppercase; letter-spacing: 1px; color: #888; font-weight: 600; }
    
    /* Colors for Scores */
    .score-high { color: #198754; }
    .score-mid { color: #fd7e14; }
    .score-low { color: #dc3545; }

    /* AI Feedback Section */
    .ai-feedback {
        background: #f8fafd; border-radius: 12px; padding: 20px; margin-top: 20px;
        border-left: 4px solid #0d6efd;
    }
    .ai-header { font-size: 0.9rem; font-weight: 700; color: #0d6efd; margin-bottom: 10px; display: flex; align-items: center; gap: 8px; }
    
    /* Insights List */
    .insight-list { list-style: none; padding: 0; margin: 0; }
    .insight-item { margin-bottom: 8px; font-size: 0.95rem; display: flex; gap: 10px; }
    .insight-icon { margin-top: 2px; font-size: 18px; }
    
    /* Utils */
    .user-dropdown-toggle::after { display: none; }
    .mobile-nav { background: var(--bsu-red); padding: 10px 15px; }
    .offcanvas-header .btn-close { position: absolute; right: 20px; top: 25px; filter: invert(1); opacity: 1; }

    @media (max-width: 768px) { .sidebar { display: none; } .main-content { padding: 20px; } }
</style>
</head>
<body>

<nav class="navbar navbar-dark mobile-nav d-md-none sticky-top">
  <div class="container-fluid justify-content-start"><button class="navbar-toggler border-0 p-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasSidebar"><span class="material-icons text-white">menu</span></button></div>
</nav>

<div class="container-fluid">
  <div class="row">
    
    <div class="col-md-3 col-lg-2 d-none d-md-block sidebar p-0">
      <div class="brand"><img src="images/bsu_logo.png" width="29" class="me-2"> Student Portal</div>
      <nav class="nav flex-column px-2">
        <a class="nav-link" href="StudentDashboard.php"><span class="material-icons">dashboard</span> Dashboard</a>
        <a class="nav-link active" href="#"><span class="material-icons">insights</span>Analytics View</a>
      </nav>
    </div>

    <div class="offcanvas offcanvas-start sidebar" tabindex="-1" id="offcanvasSidebar">
        <div class="offcanvas-header"><div class="brand">Student Portal</div><button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button></div>
        <div class="offcanvas-body p-0">
            <nav class="nav flex-column px-2">
                <a class="nav-link" href="StudentDashboard.php"><span class="material-icons">dashboard</span> Dashboard</a>
                <a class="nav-link active" href="#"><span class="material-icons">insights</span> Analytics</a>
                <a class="nav-link" href="instructor-announcements.php"><span class="material-icons">campaign</span> Announcements</a>
            </nav>
        </div>
    </div>

    <main class="col-md-9 col-lg-10 ms-sm-auto main-content">
      
      <div class="top-header">
        <h2 class="fw-bold m-0">Performance Insights</h2>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-decoration-none user-dropdown-toggle" data-bs-toggle="dropdown">
               <div class="text-end d-none d-md-block me-3"><small class="text-muted d-block" style="font-size: 0.8rem;">Current User</small><span class="fw-bold text-dark"><?php echo htmlspecialchars($userName); ?></span></div>
               <div class="avatar-circle shadow-sm"><?php echo htmlspecialchars($avatarInitials); ?></div>
            </a>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0"><li><a class="dropdown-item text-danger" href="?action=logout">Logout</a></li></ul>
        </div>
      </div>

      <div class="row justify-content-center">
          <div class="col-lg-9">
              <p class="text-muted mb-4">AI-generated analysis of recent student submissions.</p>

              <div class="student-card reveal-item" style="animation-delay: 0.1s;">
                  <div class="d-flex justify-content-between align-items-center">
                      <div class="d-flex align-items-center gap-3">
                          <div class="d-none d-sm-flex align-items-center justify-content-center bg-light rounded-circle" style="width:55px; height:55px; font-size:1.2rem; font-weight:bold; color:#555;">RC</div>
                          <div>
                              <h5 class="fw-bold mb-1 text-dark">Rovic Cuevas</h5>
                              <div class="text-muted small">Assignment: <strong>Introduction to Data Structures</strong></div>
                              <div class="badge bg-light text-dark border mt-1">BSU-102</div>
                          </div>
                      </div>
                      <div class="score-box">
                          <div class="score-val score-high">95</div>
                          <div class="score-label">Grade</div>
                      </div>
                  </div>

                  <div class="ai-feedback">
                      <div class="ai-header"><span class="material-icons">auto_awesome</span> AI Assessment</div>
                      <ul class="insight-list">
                          <li class="insight-item text-success">
                              <span class="material-icons insight-icon">check_circle</span>
                              <span><strong>Strengths:</strong> Excellent grasp of array manipulation logic. Code structure is clean and follows best practices.</span>
                          </li>
                          <li class="insight-item text-secondary">
                              <span class="material-icons insight-icon">remove_circle_outline</span>
                              <span><strong>Weaknesses:</strong> Minor inefficiency in the nested loop, but negligible for this dataset size.</span>
                          </li>
                          <li class="insight-item text-primary">
                              <span class="material-icons insight-icon">lightbulb</span>
                              <span><strong>To Improve:</strong> Explore "Big O Notation" to better understand time complexity optimizations for future tasks.</span>
                          </li>
                      </ul>
                  </div>
              </div>

              <div class="student-card reveal-item" style="animation-delay: 0.3s;">
                  <div class="d-flex justify-content-between align-items-center">
                      <div class="d-flex align-items-center gap-3">
                          <div class="d-none d-sm-flex align-items-center justify-content-center bg-light rounded-circle" style="width:55px; height:55px; font-size:1.2rem; font-weight:bold; color:#555;">RC</div>
                          <div>
                              <h5 class="fw-bold mb-1 text-dark">Rovic Cuevas</h5>
                              <div class="text-muted small">Quiz: <strong>Network Topologies</strong></div>
                              <div class="badge bg-light text-dark border mt-1">BSU-104</div>
                          </div>
                      </div>
                      <div class="score-box">
                          <div class="score-val score-mid">78</div>
                          <div class="score-label">Grade</div>
                      </div>
                  </div>

                  <div class="ai-feedback" style="border-left-color: #fd7e14; background: #fff8f3;">
                      <div class="ai-header" style="color: #fd7e14;"><span class="material-icons">auto_awesome</span> AI Assessment</div>
                      <ul class="insight-list">
                          <li class="insight-item text-dark">
                              <span class="material-icons insight-icon text-success">check_circle</span>
                              <span><strong>Strengths:</strong> Correctly identified Star and Bus topologies. Good definition of LAN vs WAN.</span>
                          </li>
                          <li class="insight-item text-dark">
                              <span class="material-icons insight-icon text-warning">warning</span>
                              <span><strong>Weaknesses:</strong> Confused "Mesh Topology" redundancy features with "Ring Topology".</span>
                          </li>
                          <li class="insight-item text-dark">
                              <span class="material-icons insight-icon text-primary">lightbulb</span>
                              <span><strong>To Improve:</strong> Review the diagram comparisons in Module 3 to visualize how data travels in a Mesh network.</span>
                          </li>
                      </ul>
                  </div>
              </div>

              <div class="student-card reveal-item" style="animation-delay: 0.5s;">
                  <div class="d-flex justify-content-between align-items-center">
                      <div class="d-flex align-items-center gap-3">
                          <div class="d-none d-sm-flex align-items-center justify-content-center bg-light rounded-circle" style="width:55px; height:55px; font-size:1.2rem; font-weight:bold; color:#555;">RC</div>
                          <div>
                              <h5 class="fw-bold mb-1 text-dark">Rovic Cuevas</h5>
                              <div class="text-muted small">Assignment: <strong>Database Normalization</strong></div>
                              <div class="badge bg-light text-dark border mt-1">BSU-101</div>
                          </div>
                      </div>
                      <div class="score-box">
                          <div class="score-val score-low">60</div>
                          <div class="score-label">Grade</div>
                      </div>
                  </div>

                  <div class="ai-feedback" style="border-left-color: #dc3545; background: #fff5f5;">
                      <div class="ai-header" style="color: #dc3545;"><span class="material-icons">auto_awesome</span> AI Assessment</div>
                      <ul class="insight-list">
                          <li class="insight-item text-dark">
                              <span class="material-icons insight-icon text-success">check_circle</span>
                              <span><strong>Strengths:</strong> Submitted on time. Basic understanding of Primary Keys is present.</span>
                          </li>
                          <li class="insight-item text-dark">
                              <span class="material-icons insight-icon text-danger">error</span>
                              <span><strong>Weaknesses:</strong> Failed to apply 3rd Normal Form (3NF) correctly. Data redundancy issues found in the schema.</span>
                          </li>
                          <li class="insight-item text-dark">
                              <span class="material-icons insight-icon text-primary">lightbulb</span>
                              <span><strong>To Improve:</strong> Please schedule a consultation. Review the "Normalization Rules" chart and try the practice exercises again.</span>
                          </li>
                      </ul>
                  </div>
              </div>

          </div>
      </div>

    </main>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
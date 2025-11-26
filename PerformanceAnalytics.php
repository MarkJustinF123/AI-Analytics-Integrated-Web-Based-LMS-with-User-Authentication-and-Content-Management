<?php
session_start();
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
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
</head>
<body>
<main class="col-md-9 col-lg-10 ms-sm-auto main-content">
      
      <div class="top-header">
        <h2 class="fw-bold m-0">Performance Insights (Real-Time AI)</h2>
        </div>

      <div class="row justify-content-center">
          <div class="col-lg-9" id="insightsContainer">
              <div class="text-center py-5">
                  <div class="spinner-border text-danger" role="status"></div>
                  <p class="text-muted mt-3">Fetching AI analysis from database...</p>
              </div>
          </div>
      </div>

    </main>
  </div>
</div>

<script>
  const STRAPI_TOKEN = "097902687e71fa3c9d71b0bd7e46a5a998ccf78fea375dd83b0ba138fd83c74818f80a3b4411b02fe72a826206f775ded3c4d86a2de908242d265a39b4e097f064a316880f796c47650f6e88c6326d1dd0df3868929c3301368caa0bd5227ffae6a991867d182f02194dad1fd717c2136b14a23b17d9c6fbe11178d04a45ad4c"; // Same token as before
  const authHeaders = { 'Authorization': `Bearer ${STRAPI_TOKEN}` };

  async function loadAnalytics() {
      const container = document.getElementById('insightsContainer');
      
      try {
          // 1. FETCH GRADED SUBMISSIONS
          // Get items where status is 'graded' (meaning they have AI feedback)
          const url = `http://localhost:1337/api/submissions?filters[submission_status][$eq]=graded&populate=users_permissions_user,assignment,file&sort=updatedAt:desc`;
          
          const res = await fetch(url, { headers: authHeaders });
          if (!res.ok) throw new Error('Failed to fetch data');
          const json = await res.json();

          if (json.data.length === 0) {
              container.innerHTML = `<div class="text-center py-5 text-muted">No graded submissions with AI feedback found.</div>`;
              return;
          }

          let html = '';
          json.data.forEach((sub, index) => {
              const attr = sub.attributes || sub;
              const delay = index * 0.1;

              // Extract Data
              const studentName = attr.users_permissions_user?.data?.attributes?.username || 'Student';
              const assignTitle = attr.assignment?.data?.attributes?.title || 'Assignment';
              const score = attr.grade || 0;
              const aiFeedback = attr.ai_feedback || "Feedback processing...";

              // Score Color Logic
              let scoreColor = 'score-mid';
              if(score >= 90) scoreColor = 'score-high';
              if(score < 75) scoreColor = 'score-low';

              html += `
                <div class="student-card" style="animation-delay: ${delay}s;">
                    <div class="d-flex justify-content-between align-items-center">
                        <div class="d-flex align-items-center gap-3">
                            <div class="d-none d-sm-flex align-items-center justify-content-center bg-light rounded-circle" style="width:55px; height:55px; font-weight:bold; color:#555;">${studentName.substring(0,2).toUpperCase()}</div>
                            <div>
                                <h5 class="fw-bold mb-1 text-dark">${studentName}</h5>
                                <div class="text-muted small">Activity: <strong>${assignTitle}</strong></div>
                            </div>
                        </div>
                        <div class="score-box">
                            <div class="score-val ${scoreColor}">${score}</div>
                            <div class="score-label">Grade</div>
                        </div>
                    </div>
                    
                    <div class="feedback-box">
                        <div class="feedback-header"><span class="material-icons">auto_awesome</span> AI Assessment</div>
                        <div>${marked.parse(aiFeedback)}</div>
                    </div>
                </div>
              `;
          });

          container.innerHTML = html;

      } catch (error) {
          console.error(error);
          container.innerHTML = `<div class="alert alert-danger text-center">Error loading insights.</div>`;
      }
  }

  document.addEventListener('DOMContentLoaded', loadAnalytics);
</script>
</body>
</html>
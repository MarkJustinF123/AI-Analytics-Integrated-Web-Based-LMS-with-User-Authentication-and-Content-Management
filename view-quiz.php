<?php
session_start();
// Check if user is logged in
if (!isset($_SESSION['idToken']) || !isset($_SESSION['email'])) {
    header('Location: login.php');
    exit;
}
// Get user information
$userName = isset($_SESSION['user']['name']) ? $_SESSION['user']['name'] : 'Instructor';
$avatarInitials = 'U';
if (!empty($userName)) {
    $nameParts = explode(' ', $userName);
    $avatarInitials = count($nameParts) >= 2 ? strtoupper(substr($nameParts[0], 0, 1) . substr($nameParts[1], 0, 1)) : strtoupper(substr($userName, 0, 2));
}
// Get the Course and Quiz IDs from the URL
$course_id = isset($_GET['courseId']) ? htmlspecialchars($_GET['courseId']) : '';
$quiz_id = isset($_GET['id']) ? htmlspecialchars($_GET['id']) : '';
if (empty($course_id) || empty($quiz_id)) {
    echo "Missing Course ID or Quiz ID.";
    exit;
}
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>View Quiz — BSU</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <link rel="stylesheet" href="dashboard2.css"> <link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;700&family=Roboto:wght@400;500&display=swap" rel="stylesheet">
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>

  <header class="header">
    <div class="header-left">
      <button class="header-menu" id="hamburger">
        <span class="material-icons">menu</span>
      </button>
      <a href="manage-course.php?id=<?php echo $course_id; ?>" style="text-decoration:none; color:inherit;">
        <span class="header-title">Batangas State University</span>
      </a>
    </div>
    <div class="header-right">
      <div class="profile" id="profileBtn">
        <div class="avatar"><?php echo htmlspecialchars($avatarInitials); ?></div>
        <div class="dropdown" id="profileDropdown">
          <a href="#">View Profile</a>
          <a href="?action=logout" class="logout">Logout</a>
        </div>
      </div>
    </div>
  </header>

  <div class="page-container">
    
    <aside class="sidebar" id="sidebar">
      <a href="InstructorDashboard.php" class="nav-item">
        <span class="material-icons nav-item-icon">home</span>
        <div>Home</div>
      </a>
      <a href="calendar.php" class="nav-item">
        <span class="material-icons nav-item-icon">calendar_today</span>
        <div>Calendar</div>
      </a>
      
      <div class="nav-item collapsible-header" id="teaching-header">
        <span class="material-icons nav-item-icon">school</span>
        <div>Teaching</div>
        <span class="material-icons" id="teaching-icon" style="margin-left:auto; transform: rotate(0deg); transition: transform 0.2s;">keyboard_arrow_down</span>
      </div>
      
      <div id="teaching-classes-list" style="display:block; padding-left: 20px;">
        </div>
    </aside>

    <main class="content-area" style="padding:0;">
      
      <div class="assignment-detail-header" data-quiz-id="<?php echo $quiz_id; ?>">
        <div class="icon-bg">
          <span class="material-icons">quiz</span>
        </div>
        <div>
          <h2 id="quiz-title">Loading...</h2>
          <span class="posted-date" id="quiz-info">Loading...</span>
        </div>
      </div>

      <nav class="assignment-detail-tabs">
        <a class="detail-tab-link active" data-tab="instructions-pane">Instructions</a>
        <a class="detail-tab-link" data-tab="student-work-pane">Student work</a>
      </nav>
      
      <div class="tab-content" style="padding:0;">
        
        <div class="tab-pane active" id="instructions-pane">
            <div class="detail-layout">
                <main class="detail-main">
                    <div class="card">
                        <p id="quiz-description-display">Loading description...</p> 
                        <hr>
                        <p id="quiz-link-display" style="font-size: 14px; margin-bottom: 10px;">Link: Loading...</p> 
                        <div id="quiz-files"></div>
                    </div>
                </main>
                <aside class="detail-sidebar">
                    <div class="card">
                        <p style="padding: 16px; font-size: 14px; color: var(--gg-secondary-text);">
                            Points: <span id="quiz-points-display">...</span>
                        </p>
                    </div>
                </aside>
            </div>
        </div>
        
        <div class="tab-pane" id="student-work-pane">
            <div class="detail-layout">
                <main class="detail-main" style="padding-top:0;">
                    <div class="card">
                        <div class="student-work-stats">
                            <div class="stat-item">
                                <div class="count" id="turned-in-count">0</div>
                                <div class="label">Turned in</div>
                            </div>
                            <div class="stat-item">
                                <div class="count" id="assigned-count">0</div>
                                <div class="label">Assigned</div>
                            </div>
                        </div>
                        <p style="padding:16px; color:var(--muted); text-align:center;">
                            Student submissions will appear here.
                        </p>
                    </div>
                </main>
                <aside class="detail-sidebar">
                    <div class="card">
                        <h3 style="margin-bottom: 0;">Assignment Controls</h3>
                        <p style="font-size: 14px; color: var(--gg-secondary-text); margin-top: 8px;">
                            You can grade and return submissions here.
                        </p>
                    </div>
                </aside>
            </div>
        </div>
        
      </div>
    </main>
  </div>

<script>
  // --- STRAPI API CODE ---
  const STRAPI_API_TOKEN = "097902687e71fa3c9d71b0bd7e46a5a998ccf78fea375dd83b0ba138fd83c74818f80a3b4411b02fe72a826206f775ded3c4d86a2de908242d265a39b4e097f064a316880f796c47650f6e88c6326d1dd0df3868929c3301368caa0bd5227ffae6a991867d182f02194dad1fd717c2136b14a23b17d9c6fbe11178d04a45ad4c";
  const quizId_string = document.querySelector('.assignment-detail-header').dataset.quizId;
  const courseId_string = "<?php echo $course_id; ?>";
  const courseId = <?php echo $course_id; ?>;

  // 1. Function to load the quiz data
  async function loadQuizData() {
    // We use the LIST endpoint and filter by ID, which we know works.
    const url = `http://localhost:1337/api/quizzes?filters[id][$eq]=${quizId_string}&populate=*`;
    
    try {
      const response = await fetch(url, {
        method: 'GET',
        headers: { 'Authorization': `Bearer ${STRAPI_API_TOKEN}` }
      });
      if (!response.ok) throw new Error('Failed to fetch quiz data.');
      
      const result = await response.json();
      
      if (!result.data || result.data.length === 0) {
        throw new Error('Quiz not found.');
      }
      
      const quiz = result.data[0]; 
      
      // Populate Header
      document.getElementById('quiz-title').textContent = quiz.title;
      const postedDate = new Date(quiz.createdAt).toLocaleDateString('en-US', { month: 'short', day: 'numeric' });
      const dueDate = quiz.dueDate ? `Due ${new Date(quiz.dueDate).toLocaleDateString('en-US', { month: 'short', day: 'numeric' })}` : 'No due date';
      document.getElementById('quiz-info').textContent = `Posted ${postedDate} • ${dueDate}`;
      
      // Populate Details
      document.getElementById('quiz-description-display').textContent = quiz.description || 'No description provided.';
      document.getElementById('quiz-points-display').textContent = quiz.points || 0;
      
      // Correctly check for and display the link
      const linkContainer = document.getElementById('quiz-link-display');
      if (quiz.link) {
          linkContainer.innerHTML = `Link: <a href="${quiz.link}" target="_blank" rel="noopener noreferrer">${quiz.link}</a>`;
      } else {
          linkContainer.textContent = 'No external quiz link provided.';
      }
      
      // Populate Files
      const filesContainer = document.getElementById('quiz-files');
      if (quiz.files && quiz.files.length > 0) {
        let filesHtml = '<ul class="attachment-list">';
        for (const file of quiz.files) {
          filesHtml += `
            <li class="attachment-item">
              <span class="material-icons">attachment</span>
              <a href="http://localhost:1337${file.url}" target="_blank" rel="noopener noreferrer">
                ${file.name}
              </a>
            </li>
          `;
        }
        filesHtml += '</ul>';
        filesContainer.innerHTML = filesHtml;
      } else {
        filesContainer.innerHTML = '<p style="color:var(--muted)">No attachments.</p>';
      }
      
    } catch (error) {
      console.error('Error loading quiz:', error);
      document.getElementById('quiz-title').textContent = 'Error loading quiz';
    }
  }

  // --- NEW FUNCTION: Load all classes for the sidebar ---
  async function loadSidebarClasses() {
    const listContainer = document.getElementById('teaching-classes-list');
    listContainer.innerHTML = '';
    const strapiURL = 'http://localhost:1337/api/courses';

    try {
        const response = await fetch(strapiURL);
        if (!response.ok) throw new Error('Could not fetch sidebar courses');
        
        const result = await response.json();
        const courses = result.data;

        courses.forEach(course => {
            const link = document.createElement('a');
            link.href = `manage-course.php?id=${course.id}`;
            link.className = 'nav-item';
            link.style.padding = '8px 24px';
            link.style.fontSize = '14px';
            link.style.fontWeight = '400';
            
            // Highlight the current course
            if (course.id.toString() === courseId_string) {
                link.classList.add('active'); 
            }

            const initials = course.title.substring(0, 2).toUpperCase();
            
            link.innerHTML = `
                <div class="avatar" style="width: 24px; height: 24px; background: #666; font-size: 11px;">${initials}</div>
                <div style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 150px;">${course.title}</div>
            `;
            listContainer.appendChild(link);
        });
    } catch (error) {
        console.error('Error loading sidebar classes:', error);
        listContainer.innerHTML = '<p style="padding: 10px; color:red;">Failed to load classes.</p>';
    }
    // Set up the toggle now that content is loaded
    const header = document.getElementById('teaching-header');
    const list = document.getElementById('teaching-classes-list');
    header.addEventListener('click', () => {
        const isCollapsed = list.style.display === 'none';
        list.style.display = isCollapsed ? 'block' : 'none';
    });
  }

  // --- TAB NAVIGATION SCRIPT ---
  function setupTabs() {
    const tabLinks = document.querySelectorAll('.detail-tab-link');
    const tabPanes = document.querySelectorAll('.tab-pane');

    tabLinks.forEach(link => {
      link.addEventListener('click', () => {
        const tabId = link.dataset.tab;
        tabLinks.forEach(l => l.classList.remove('active'));
        tabPanes.forEach(p => p.classList.remove('active'));
        link.classList.add('active');
        document.getElementById(tabId).classList.add('active');
      });
    });
  }
  
  // --- Standard Dashboard JS (Sidebar, Dropdown, etc.) ---
  const hamburger = document.getElementById('hamburger');
  const sidebar = document.getElementById('sidebar');
  hamburger.addEventListener('click', () => {
    sidebar.style.display = sidebar.style.display === 'none' ? 'block' : 'none';
  });
  const profileBtn = document.getElementById('profileBtn');
  const dropdown = document.getElementById('profileDropdown');
  profileBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    dropdown.style.display = dropdown.style.display === 'block' ? 'none' : 'block';
  });
  document.addEventListener('click', () => {
    dropdown.style.display = 'none';
  });

  // --- Run when page loads ---
  document.addEventListener('DOMContentLoaded', () => {
    loadQuizData();
    loadSidebarClasses();
    setupTabs();
  });
</script>

</body>
</html>
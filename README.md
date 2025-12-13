# AI-Analytics-Integrated-Web-Based-LMS-with-User-Authentication-and-Content-Management

Project Description
  The project is a Web-Based Learning Management System that integrates Artificial Intelligence in assessing the performance of the students. Unlike traditional Learning Management Systems that take record assessments like quizzes and activities, this platform will analyze these results to identify precisely what each student knows and doesn't know to provide useful data to learners and instructors alike.

The platform streamlines the educational process by combining secure user authentication, a robust Content Management System for lesson organization, and intelligent analytics all into one web interface. It aims to offer improved personalized learning experiences while efficiently covering course administration and secure access control.

Technologies & APIs Used
The system is built using a Layered Architecture (Presentation, Business Logic, Data Layers) and utilizes the following key technologies and APIs:

Google Firebase / Keycloak: Used for the User Authentication Service. This handles secure role-based login (Student and Instructor), registration, email verification, and password recovery processes.

Gemini AI: Integrated as the AI Analytics Engine. It processes student performance data to generate summaries, identify learning gaps, and provide personalized recommendations for improvement.

Strapi CMS: Serves as the Content Management System. It manages the storage, organization, and retrieval of lessons, quizzes, and learning materials, making real-time updates accessible to users.

Backend/Frontend: PHP (v8.0+), HTML5, CSS3, JavaScript (ES6).

Database: MySQL (v8.0+) for storing user profiles, records, and course content.

Local Server: XAMPP (Apache, PHP, MySQL).

1. User Authentication Module

Login & Registration: Serves as the primary gateway, requiring encrypted verification of credentials. It supports role-based access to ensure students and instructors see only their relevant interfaces.

Password Recovery: A secure workflow integrated with Firebase to reset forgotten passwords via email verification.

2. Instructor Portal
Designed for teachers to manage content and monitor progress.

Dashboard: Provides a complete overview of teaching activities, including active courses, pending submissions, upcoming deadlines, and a calendar.

Manage Course: A centralized workspace to edit course content, upload files, and view the list of enrolled students.

Lessons Module: Allows instructors to upload and organize instructional materials (PDFs, links, documents) for student access.

Assignments Module: Enables the creation of tasks with specific instructions, reference files, rubrics, and deadlines. It also tracks student submission status.

Quizzes Module: Centralizes quiz management, allowing instructors to post links (e.g., external forms) and monitor participation.

Announcements: A communication tool for posting class updates, reminders, and schedule changes.

Class Insights (Analytics): Displays AI-generated analytics on the entire class's performance, highlighting common errors, learning gaps, and score trends.

3. Student Portal
Designed for learners to access materials and track their own growth.

Student Dashboard: A snapshot of the student's academic workload, displaying enrolled courses, upcoming deadlines, recent grades, and pending tasks.

View Course: An organized view of all content uploaded by the instructor, including a list of classmates.

My Activities: A centralized list that compiles all assignments and quizzes from all enrolled courses into one timeline, preventing missed deadlines.

Lesson & Assignment View: Allows students to view learning units, download resources, read instructions, and submit outputs directly.

Performance Insights: Provides personal AI-based feedback. It breaks down the student's specific strengths and weaknesses based on their quiz and activity results.

ACTION REQUIRED: The following files contain hardcoded API tokens, secret keys, or sensitive credentials. Before you deploy or share this project, you will need to modify these files to use environment variables or a secure configuration file (e.g., secrets.php).

Which, if left open in source code, creates a significant security risk.

1. Core Configuration Files (High Priority)
These files define the primary keys used by the backend.

web-based-lms/api/secrets.php

Contains $GEMINI_API_KEY (Google Gemini AI)

Contains $STRAPI_TOKEN (Strapi CMS Full Access Token)

Contains $STRAPI_URL

web-based-lms/firebase.php

Contains FIREBASE_API_KEY (Google Firebase Auth)

2. Frontend/UI Files with Hardcoded Tokens
The following PHP files contain a JavaScript section with a hardcoded const STRAPI_API_TOKEN. This token is visible to anyone viewing the "Source Code" of the page in a browser.

Instructor Modules:

web-based-lms/InstructorDashboard.php

web-based-lms/manage-course.php

web-based-lms/calendar.php

web-based-lms/instructor-announcements.php

web-based-lms/create-assignment.php

web-based-lms/edit-assignment.php

web-based-lms/view-assignment.php

web-based-lms/create-lesson.php

web-based-lms/edit-lesson.php

web-based-lms/create-quiz.php

web-based-lms/edit-quiz.php

web-based-lms/view-quiz.php

web-based-lms/PerformanceAnalytics.php

Student Modules:

web-based-lms/StudentAnalytics.php (Contains const STRAPI_TOKEN)

web-based-lms/student-view-assignment.php (Contains const STRAPI_API_TOKEN — Note: This appears to be using an Admin/Instructor token in a student view, which is a critical security issue.)

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Register</title>
  <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>" />
  <style>
    .message-container {
      position: fixed;
      top: 20px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 1000;
      max-width: 400px;
      width: 90%;
      animation: slideDown 0.3s ease-out;
    }
    @keyframes slideDown {
      from {
        opacity: 0;
        transform: translateX(-50%) translateY(-20px);
      }
      to {
        opacity: 1;
        transform: translateX(-50%) translateY(0);
      }
    }
    .message-container.fade-out {
      animation: fadeOut 0.5s ease-out forwards;
    }
    @keyframes fadeOut {
      to {
        opacity: 0;
        transform: translateX(-50%) translateY(-20px);
      }
    }
    .error-message {
      color: #b91c1c;
      background: #fee2e2;
      padding: 12px 16px;
      border-radius: 8px;
      border-left: 4px solid #dc2626;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
    .success-message {
      color: #065f46;
      background: #d1fae5;
      padding: 12px 16px;
      border-radius: 8px;
      border-left: 4px solid #10b981;
      box-shadow: 0 4px 12px rgba(0,0,0,0.15);
    }
  </style>
</head>
<body>
  <?php
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    require_once __DIR__ . '/firebase.php';

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $name = trim((string)($_POST['name'] ?? ''));
      $email = trim((string)($_POST['email'] ?? ''));
      $password = (string)($_POST['password'] ?? '');
      $confirmPassword = (string)($_POST['confirmPassword'] ?? ''); // New
      $role = (string)($_POST['role'] ?? '');
      $srCode = trim((string)($_POST['srCode'] ?? '')); // New
      $inviteCode = (string)($_POST['inviteCode'] ?? '');

      $error = '';
      if ($name === '' || strlen($name) < 3) { 
        $error = 'Name must be at least 3 characters.'; 
      }
      elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) { 
        $error = 'Please enter a valid email address.'; 
      }
      elseif (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d).{8,}$/', $password)) { 
        $error = 'Password must be 8+ chars with upper, lower and a number.'; 
      }
      elseif ($password !== $confirmPassword) { // New check
        $error = 'Passwords do not match.';
      }
      elseif ($role !== 'student' && $role !== 'instructor') { // Updated role
        $error = 'Please select a role.'; 
      }
      elseif ($role === 'student' && $srCode === '') { // New check
          $error = 'SR-Code is required for students.';
      }
      elseif ($role === 'instructor' && $inviteCode !== 'TEACHER123') { // Updated role
        $error = 'Invalid or missing instructor invite code.'; 
      }

      if ($error !== '') {
        // Set error in session but DO NOT redirect - stay on register page
        $_SESSION['reg_error'] = $error;
        // Prevent redirect - just continue to display the page with error
      } else {
        try {
          $signup = firebaseSignUp($email, $password);
          $idToken = (string)($signup['idToken'] ?? '');
          if ($idToken === '') { throw new Exception('Missing idToken from sign-up response.'); }

          // Store name and role in displayName (format: "Name|role:student")
          // Map 'instructor' role for consistency
          $roleForDisplay = ($role === 'instructor') ? 'instructor' : $role;
          $displayNameValue = $name . '|role:' . $roleForDisplay;
          // Note: SR-Code is validated but not stored in Firebase Auth profile
          
          try {
            $updateResult = firebaseUpdateProfile($idToken, $displayNameValue);
            // Update idToken if a new one was returned
            if (isset($updateResult['idToken'])) {
              $idToken = $updateResult['idToken'];
            }
          } catch (Exception $e) {
            // If profile update fails, continue - we'll use email pattern as fallback
          }

          // Send verification email
          firebaseSendEmailVerification($idToken);

          // Only redirect on SUCCESS
          $_SESSION['reg_success'] = 'Registration successful! A verification email has been sent. Please verify before logging in.';
          header('Location: login.php');
          exit;
        } catch (Exception $e) {
          // Set error but DO NOT redirect - stay on register page
          $_SESSION['reg_error'] = 'Registration failed: ' . $e->getMessage();
          // No redirect here - continue to display the page with error
        }
      }
    }
  ?>

  <?php if (!empty($_SESSION['reg_error'])): ?>
    <div class="message-container error-message" id="regErrorMsg">
      <?php echo htmlspecialchars($_SESSION['reg_error']); unset($_SESSION['reg_error']); ?>
    </div>
    <script>
      setTimeout(function() {
        var msg = document.getElementById('regErrorMsg');
        if (msg) {
          msg.classList.add('fade-out');
          setTimeout(function() { msg.remove(); }, 500);
        }
      }, 5000);
    </script>
  <?php endif; ?>

  <form id="registerForm" method="POST" action="register.php" novalidate>
    <img src="images/Batangas_State_Logo.png" alt="Batangas State University" class="brand-logo" />
    <h2>Create an Account</h2>

    <div class="form-field">
      <input type="text" id="name" name="name" placeholder="Full Name" required minlength="3" />
      <small id="regNameError" class="error"></small>
    </div>
    <div class="form-field">
      <input type="email" id="email" name="email" placeholder="Email" required />
      <small id="regEmailError" class="error"></small>
    </div>
    <div class="form-field">
      <div class="password-wrapper">
        <input type="password" id="password" name="password" placeholder="Password" required />
        <button type="button" class="password-toggle" id="togglePassword" aria-label="Toggle password visibility">
          <svg id="eyeIcon" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
          </svg>
        </button>
      </div>
      <small id="regPasswordError" class="error"></small>
    </div>

    <div class="form-field">
      <div class="password-wrapper">
        <input type="password" id="confirmPassword" name="confirmPassword" placeholder="Confirm Password" required />
        <button type="button" class="password-toggle" id="toggleConfirmPassword" aria-label="Toggle password visibility">
          <svg id="eyeIconConfirm" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
          </svg>
        </button>
      </div>
      <small id="regConfirmError" class="error"></small>
    </div>

    <select id="role" name="role" required>
      <option value="">Select Role</option>
      <option value="student">Student</option>
      <option value="instructor">Instructor</option>
    </select>
    <small id="regRoleError" class="error"></small>

    <div class="form-field" id="srCodeWrapper" style="display: none;">
      <input
        type="text"
        id="srCode" name="srCode"
        placeholder="SR-Code (e.g., 21-12345)"
      />
      <small id="regSrCodeError" class="error"></small>
    </div>
    
    <div class="form-field" id="inviteWrapper" style="display: none;">
      <input
        type="text"
        id="inviteCode" name="inviteCode"
        placeholder="Instructor Invite Code"
      />
      <small id="regInviteError" class="error"></small>
    </div>

    <button type="submit">Register</button>
    <p class="below-btn">Already have an account? <a href="login.php">Login here</a></p>
    <p id="message"></p>
  </form>

  <script>
    const roleSelect = document.getElementById("role");
    const inviteWrapper = document.getElementById("inviteWrapper");
    const inviteCodeInput = document.getElementById("inviteCode");
    const srCodeWrapper = document.getElementById("srCodeWrapper");
    const srCodeInput = document.getElementById("srCode");

    roleSelect.addEventListener("change", () => {
      if (roleSelect.value === "instructor") {
        if (inviteWrapper) inviteWrapper.style.display = "block";
        inviteCodeInput.style.display = "block";
        if (srCodeWrapper) srCodeWrapper.style.display = "none";
        srCodeInput.style.display = "none";
        srCodeInput.value = "";
      } else if (roleSelect.value === "student") {
        if (inviteWrapper) inviteWrapper.style.display = "none";
        inviteCodeInput.style.display = "none";
        inviteCodeInput.value = "";
        if (srCodeWrapper) srCodeWrapper.style.display = "block";
        srCodeInput.style.display = "block";
      } else {
        if (inviteWrapper) inviteWrapper.style.display = "none";
        inviteCodeInput.style.display = "none";
        inviteCodeInput.value = "";
        if (srCodeWrapper) srCodeWrapper.style.display = "none";
        srCodeInput.style.display = "none";
        srCodeInput.value = "";
      }
    });
    
    // Password visibility toggle helper function
    function setupPasswordToggle(toggleId, inputId, iconId) {
      const toggleButton = document.getElementById(toggleId);
      const passwordInput = document.getElementById(inputId);
      const eyeIcon = document.getElementById(iconId);
      
      if (toggleButton && passwordInput && eyeIcon) {
        toggleButton.addEventListener('click', function(e) {
          e.preventDefault();
          e.stopPropagation();
          
          const isPassword = passwordInput.type === 'password';
          passwordInput.type = isPassword ? 'text' : 'password';
          
          if (isPassword) {
            eyeIcon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path><line x1="1" y1="1" x2="23" y2="23"></line>';
          } else {
            eyeIcon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path><circle cx="12" cy="12" r="3"></circle>';
          }
        });
      }
    }

    // Setup toggles for both password fields
    setupPasswordToggle('togglePassword', 'password', 'eyeIcon');
    setupPasswordToggle('toggleConfirmPassword', 'confirmPassword', 'eyeIconConfirm');
  </script>
</body>
</html>
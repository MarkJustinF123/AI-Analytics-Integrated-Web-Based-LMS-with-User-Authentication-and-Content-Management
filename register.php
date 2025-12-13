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
      $role = (string)($_POST['role'] ?? '');
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
      elseif ($role !== 'student' && $role !== 'teacher') { 
        $error = 'Please select a role.'; 
      }
      elseif ($role === 'teacher' && $inviteCode !== 'TEACHER123') { 
        $error = 'Invalid or missing teacher invite code.'; 
      }

      if ($error !== '') {
        $_SESSION['reg_error'] = $error;
      } else {
        try {
          $signup = firebaseSignUp($email, $password);
          $idToken = (string)($signup['idToken'] ?? '');
          if ($idToken === '') { throw new Exception('Missing idToken from sign-up response.'); }

          $roleForDisplay = ($role === 'teacher') ? 'instructor' : $role;
          $displayNameValue = $name . '|role:' . $roleForDisplay;
          try {
            $updateResult = firebaseUpdateProfile($idToken, $displayNameValue);

            if (isset($updateResult['idToken'])) {
              $idToken = $updateResult['idToken'];
            }
          } catch (Exception $e) {

          }

          firebaseSendEmailVerification($idToken);

          $_SESSION['reg_success'] = 'Registration successful! A verification email has been sent. Please verify before logging in.';
          header('Location: login.php');
          exit;
        } catch (Exception $e) {
          $_SESSION['reg_error'] = 'Registration failed: ' . $e->getMessage();
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
    <img src="images/bsu_logo.png" alt="Batangas State University" class="brand-logo" />
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

    <select id="role" name="role" required>
      <option value="">Select Role</option>
      <option value="student">Student</option>
      <option value="teacher">Teacher</option>
    </select>
    <small id="regRoleError" class="error"></small>

    <div class="form-field" id="inviteWrapper" style="display: none;">
      <input
        type="text"
        id="inviteCode" name="inviteCode"
        placeholder="Teacher Invite Code"
      />
      <small id="regInviteError" class="error"></small>
    </div>

    <button type="submit">Register</button>
    <p class="below-btn">Already have an account? <a href="login.php">Login here</a></p>
    <p id="message"></p>
  </form>

  <script>
    const roleSelect = document.getElementById("role");
    const inviteCodeInput = document.getElementById("inviteCode");
    const inviteWrapper = document.getElementById("inviteWrapper");

    roleSelect.addEventListener("change", () => {
      if (roleSelect.value === "teacher") {
        if (inviteWrapper) inviteWrapper.style.display = "block";
        inviteCodeInput.style.display = "block";
      } else {
        if (inviteWrapper) inviteWrapper.style.display = "none";
        inviteCodeInput.style.display = "none";
        inviteCodeInput.value = "";
      }
    });

    const togglePassword = document.getElementById('togglePassword');
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    
    if (togglePassword && passwordInput && eyeIcon) {
      togglePassword.addEventListener('click', function(e) {
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
  </script>
</body>
</html>
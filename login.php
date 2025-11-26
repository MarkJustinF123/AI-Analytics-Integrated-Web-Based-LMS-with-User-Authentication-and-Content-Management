<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>
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
  <?php if (session_status() === PHP_SESSION_NONE) { session_start(); } ?>
  <?php if (!empty($_SESSION['error'])): ?>
    <div class="message-container error-message" id="errorMsg">
      <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
    </div>
    <script>
      setTimeout(function() {
        var msg = document.getElementById('errorMsg');
        if (msg) {
          msg.classList.add('fade-out');
          setTimeout(function() { msg.remove(); }, 500);
        }
      }, 5000);
    </script>
  <?php endif; ?>
  <?php if (!empty($_SESSION['reg_success'])): ?>
    <div class="message-container success-message" id="successMsg">
      <?php echo htmlspecialchars($_SESSION['reg_success']); unset($_SESSION['reg_success']); ?>
    </div>
    <script>
      setTimeout(function() {
        var msg = document.getElementById('successMsg');
        if (msg) {
          msg.classList.add('fade-out');
          setTimeout(function() { msg.remove(); }, 500);
        }
      }, 5000);
    </script>
  <?php endif; ?>
  <form id="loginForm" method="POST" action="Authentication.php" novalidate>
    <img src="images/bsu_logo.png" alt="Batangas State University" class="brand-logo" />
    <h2>Login</h2>

    <div class="form-field">
      <input type="email" id="email" name="email" placeholder="Email" required />
      <small id="loginEmailError" class="error"></small>
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
      <small id="loginPasswordError" class="error"></small>
    </div>

    <div class="auth-row">
      <label class="remember">
        <input type="checkbox" id="rememberMe" />
        Remember me
      </label>
      <a class="forgot" href="forgot.php">Forgot Password?</a>
    </div>

    <button type="submit">Login</button>
    <p class="below-btn">Don't have an account? <a href="register.php">Register here</a></p>
  </form>
  
  <script>

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
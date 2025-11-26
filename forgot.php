<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Forgot Password</title>
  <link rel="stylesheet" href="style.css?v=<?php echo time(); ?>" />
  <style>
    .alert {
      position: absolute;
      top: 30px;
      left: 50%;
      transform: translateX(-50%);
      padding: 12px 20px;
      border-radius: 8px;
      text-align: center;
      width: 90%;
      max-width: 360px;
      font-size: 15px;
      box-shadow: 0 2px 6px rgba(0,0,0,0.1);
      z-index: 1000;
      animation: fadeOut 1s ease 4s forwards;
    }
    .success {
      color: #065f46;
      background: #d1fae5;
    }
    .error {
      color: #b91c1c;
      background: #fee2e2;
    }
    @keyframes fadeOut {
      to {
        opacity: 0;
        visibility: hidden;
      }
    }
  </style>
</head>
<body>
  <?php
    if (session_status() === PHP_SESSION_NONE) { session_start(); }
    require_once __DIR__ . '/firebase.php';
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
      $email = trim((string)($_POST['email'] ?? ''));
      if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['forgot_error'] = 'Please enter a valid email address.';
      } else {
        try {
          firebaseSendPasswordReset($email, '');
          $_SESSION['forgot_success'] = 'Password reset email sent! Please check your inbox.';
        } catch (Throwable $e) {
          $_SESSION['forgot_error'] = 'Failed to send reset email: ' . $e->getMessage();
        }
      }
    }
  ?>

  <?php if (!empty($_SESSION['forgot_success'])): ?>
    <div class="alert success" id="alert-box">
      <?php echo htmlspecialchars($_SESSION['forgot_success']); unset($_SESSION['forgot_success']); ?>
    </div>
  <?php endif; ?>
  <?php if (!empty($_SESSION['forgot_error'])): ?>
    <div class="alert error" id="alert-box">
      <?php echo htmlspecialchars($_SESSION['forgot_error']); unset($_SESSION['forgot_error']); ?>
    </div>
  <?php endif; ?>

  <form id="forgot-form" method="POST" action="forgot.php">
    <img src="images/bsu_logo.png" alt="Batangas State University" class="brand-logo" />
    <h2>Forgot Password</h2>
    <p>Please enter your registered email address to reset your password.</p>
    <input type="email" id="email" name="email" placeholder="Email" required />
    <button type="submit">Send Reset Link</button>
    <p><a href="login.php">Back to Login</a></p>
  </form>

  <script>
    setTimeout(() => {
      const alert = document.getElementById('alert-box');
      if (alert) alert.style.display = 'none';
    }, 5000);
  </script>
</body>
</html>
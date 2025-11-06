<?php
session_start();

require_once 'firebase.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method Not Allowed';
    exit;
}

$email = isset($_POST['email']) ? trim($_POST['email']) : '';
$password = isset($_POST['password']) ? $_POST['password'] : '';

if (empty($email) || empty($password)) {
    $_SESSION['error'] = 'Email and password are required.';
    header('Location: login.php');
    exit;
}

try {
    // Sign in with Firebase
    $signIn = firebaseSignInWithEmailAndPassword($email, $password);
    $idToken = isset($signIn['idToken']) ? $signIn['idToken'] : '';

    if (empty($idToken)) {
        throw new Exception('Missing idToken from sign-in response.');
    }

    // Get account info to check email verification
    $accountInfo = firebaseGetAccountInfo($idToken);
    $users = isset($accountInfo['users']) ? $accountInfo['users'] : [];
    $firstUser = isset($users[0]) ? $users[0] : [];
    $emailVerified = isset($firstUser['emailVerified']) ? $firstUser['emailVerified'] : false;

    if (!$emailVerified) {
        $_SESSION['error'] = 'Please verify your email before logging in.';
        header('Location: login.php');
        exit;
    }

    // Store session data
    $_SESSION['idToken'] = $idToken;
    $_SESSION['email'] = $email;

    // Get user role and name from displayName or email pattern
    $userRole = null;
    $userName = '';
    $displayName = isset($firstUser['displayName']) ? $firstUser['displayName'] : '';
    
    // Parse displayName if it contains name and role (format: "Name|role:student" or just "role:student")
    if (!empty($displayName)) {
        if (strpos($displayName, '|role:') !== false) {
            // Format: "Name|role:student"
            $parts = explode('|role:', $displayName);
            $userName = trim($parts[0]);
            $userRole = isset($parts[1]) ? trim($parts[1]) : null;
        } elseif (strpos($displayName, 'role:') === 0) {
            // Format: "role:student" (old format, no name)
            $userRole = str_replace('role:', '', $displayName);
        } else {
            // Just a name, no role
            $userName = $displayName;
        }
    }
    
    // If name not found, extract from email
    if (empty($userName)) {
        $emailParts = explode('@', $email);
        $namePart = $emailParts[0];
        if (strpos($namePart, '.') !== false) {
            $nameParts = explode('.', $namePart);
            $userName = ucfirst($nameParts[0]) . ' ' . ucfirst($nameParts[1]);
        } else {
            $userName = ucfirst($namePart);
        }
    }
    
    // Store user name in session
    $_SESSION['user'] = array(
        'name' => $userName,
        'email' => $email
    );
    
    // Fallback: Check email pattern for role
    if (empty($userRole)) {
        $emailLower = strtolower($email);
        if (strpos($emailLower, 'student') !== false) {
            $userRole = 'student';
        } elseif (strpos($emailLower, 'instructor') !== false || strpos($emailLower, 'teacher') !== false) {
            $userRole = 'instructor';
        }
    }

    // Redirect based on user role
    if ($userRole === 'student') {
        header('Location: StudentDashboard.php');
        exit;
    } elseif ($userRole === 'instructor') {
        header('Location: InstructorDashboard.php');
        exit;
    } else {
        $_SESSION['error'] = 'Role not found. Please ensure your email contains "student" or "instructor/teacher", or contact admin.';
        header('Location: login.php');
        exit;
    }
} catch (Exception $e) {
    $_SESSION['error'] = 'Login failed: ' . $e->getMessage();
    header('Location: login.php');
    exit;
}

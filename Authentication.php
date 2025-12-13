<?php
session_start();

require_once 'firebase.php';

// --- HELPER: Function to call Strapi API from PHP ---
function callStrapiAuth($endpoint, $data) {
    $url = 'http://localhost:1337/api/auth/local' . $endpoint; // Adjust port if needed
    
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json'
    ]);
    
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return ['code' => $httpCode, 'body' => json_decode($response, true)];
}

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
    // 1. FIREBASE LOGIN
    $signIn = firebaseSignInWithEmailAndPassword($email, $password);
    $firebaseToken = isset($signIn['idToken']) ? $signIn['idToken'] : ''; 

    if (empty($firebaseToken)) {
        throw new Exception('Invalid email or password (Firebase).');
    }

    // 2. CHECK FIREBASE VERIFICATION
    $accountInfo = firebaseGetAccountInfo($firebaseToken);
    $users = isset($accountInfo['users']) ? $accountInfo['users'] : [];
    $firstUser = isset($users[0]) ? $users[0] : [];
    
    // --- FIX IS HERE: I UNCOMMENTED THIS BLOCK ---
    $emailVerified = isset($firstUser['emailVerified']) ? $firstUser['emailVerified'] : false;
    
    if (!$emailVerified) {
        $_SESSION['error'] = 'Please verify your email before logging in.';
        header('Location: login.php');
        exit;
    }
    // ---------------------------------------------

    // 3. GET USER DETAILS
    $userRole = null;
    $userName = '';
    $displayName = isset($firstUser['displayName']) ? $firstUser['displayName'] : '';

    // Parse Name and Role
    if (!empty($displayName)) {
        if (strpos($displayName, '|role:') !== false) {
            $parts = explode('|role:', $displayName);
            $userName = trim($parts[0]);
            $userRole = isset($parts[1]) ? trim($parts[1]) : null;
        } elseif (strpos($displayName, 'role:') === 0) {
            $userRole = str_replace('role:', '', $displayName);
        } else {
            $userName = $displayName;
        }
    }

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

    if (empty($userRole)) {
        $emailLower = strtolower($email);
        if (strpos($emailLower, 'student') !== false) {
            $userRole = 'student';
        } elseif (strpos($emailLower, 'instructor') !== false || strpos($emailLower, 'teacher') !== false) {
            $userRole = 'instructor';
        }
    }
    
    // A. Try to Log in to Strapi
    $strapiData = [
        'identifier' => $email,
        'password' => $password
    ];
    $strapiResponse = callStrapiAuth('', $strapiData); // Login endpoint
    
    $finalStrapiToken = '';
    $finalStrapiUser = [];

    if ($strapiResponse['code'] === 200) {
        // Login Success! User exists in Strapi.
        $finalStrapiToken = $strapiResponse['body']['jwt'];
        $finalStrapiUser = $strapiResponse['body']['user'];
    } else {
        // Login Failed. User probably doesn't exist in Strapi (DB Reset).
        // B. Register them in Strapi automatically
        $registerData = [
            'username' => $userName . rand(100,999), // Ensure uniqueness
            'email' => $email,
            'password' => $password
        ];
        
        $registerResponse = callStrapiAuth('/register', $registerData);
        
        if ($registerResponse['code'] === 200) {
            // Registration Success!
            $finalStrapiToken = $registerResponse['body']['jwt'];
            $finalStrapiUser = $registerResponse['body']['user'];
        } else {
            // Something went wrong with Strapi
             // OPTIONAL: If Strapi is down, you might want to allow login anyway using Firebase token?
             // For now, we will throw error to ensure data consistency.
            throw new Exception('Could not sync user with Strapi database.');
        }
    }

    // 5. SAVE SESSION (Using STRAPI Token)
    $_SESSION['idToken'] = $finalStrapiToken; 
    $_SESSION['email'] = $email;
    
    $_SESSION['user'] = array(
        'name' => $userName,
        'email' => $email,
        'strapi_id' => $finalStrapiUser['id']
    );

    // 6. REDIRECT
    if ($userRole === 'student') {
        header('Location: StudentDashboard.php');
        exit;
    } elseif ($userRole === 'instructor') {
        header('Location: InstructorDashboard.php');
        exit;
    } else {
        header('Location: StudentDashboard.php');
        exit;
    }

} catch (Exception $e) {
    $_SESSION['error'] = 'Login failed: ' . $e->getMessage();
    header('Location: login.php');
    exit;
}
?>
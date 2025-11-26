<?php

define('FIREBASE_API_KEY', 'AIzaSyBqNqITm_sOk2C8JMfwqDOnXiFqSePkGn8');

function firebaseSignInWithEmailAndPassword($email, $password) {
    $endpoint = 'https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key=' . FIREBASE_API_KEY;
    
    $data = array(
        'email' => $email,
        'password' => $password,
        'returnSecureToken' => true
    );
    
    $response = firebaseHttpPostJson($endpoint, json_encode($data));
    $result = json_decode($response, true);
    
    if (isset($result['error'])) {
        throw new Exception($result['error']['message']);
    }
    
    return $result;
}

function firebaseGetAccountInfo($idToken) {
    $endpoint = 'https://identitytoolkit.googleapis.com/v1/accounts:lookup?key=' . FIREBASE_API_KEY;
    
    $data = array(
        'idToken' => $idToken
    );
    
    $response = firebaseHttpPostJson($endpoint, json_encode($data));
    $result = json_decode($response, true);
    
    if (isset($result['error'])) {
        throw new Exception($result['error']['message']);
    }
    
    return $result;
}

function firebaseHttpPostJson($url, $jsonData) {
    $ch = curl_init($url);
    
    if ($ch === false) {
        throw new Exception('Failed to initialize cURL.');
    }
    
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array(
        'Content-Type: application/json',
        'Accept: application/json'
    ));
    curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
    curl_setopt($ch, CURLOPT_TIMEOUT, 20);
    
    $response = curl_exec($ch);
    
    if ($response === false) {
        $error = curl_error($ch);
        curl_close($ch);
        throw new Exception('cURL error: ' . $error);
    }
    
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    if ($httpCode < 200 || $httpCode >= 300) {
        $decoded = json_decode($response, true);
        $errorMsg = isset($decoded['error']['message']) ? $decoded['error']['message'] : $response;
        throw new Exception('HTTP ' . $httpCode . ' from Firebase: ' . $errorMsg);
    }
    
    return $response;
}

function firebaseSignUp($email, $password) {
    $endpoint = 'https://identitytoolkit.googleapis.com/v1/accounts:signUp?key=' . FIREBASE_API_KEY;
    
    $data = array(
        'email' => $email,
        'password' => $password,
        'returnSecureToken' => true
    );
    
    $response = firebaseHttpPostJson($endpoint, json_encode($data));
    $result = json_decode($response, true);
    
    if (isset($result['error'])) {
        throw new Exception($result['error']['message']);
    }
    
    return $result;
}

function firebaseSendEmailVerification($idToken) {
    $endpoint = 'https://identitytoolkit.googleapis.com/v1/accounts:sendOobCode?key=' . FIREBASE_API_KEY;
    
    $data = array(
        'requestType' => 'VERIFY_EMAIL',
        'idToken' => $idToken
    );
    
    $response = firebaseHttpPostJson($endpoint, json_encode($data));
    $result = json_decode($response, true);
    
    if (isset($result['error'])) {
        throw new Exception($result['error']['message']);
    }
}

function firebaseSendPasswordReset($email, $continueUrl = '') {
    $endpoint = 'https://identitytoolkit.googleapis.com/v1/accounts:sendOobCode?key=' . FIREBASE_API_KEY;
    
    $data = array(
        'requestType' => 'PASSWORD_RESET',
        'email' => $email
    );
    
    if (!empty($continueUrl)) {
        $data['continueUrl'] = $continueUrl;
    }
    
    $response = firebaseHttpPostJson($endpoint, json_encode($data));
    $result = json_decode($response, true);
    
    if (isset($result['error'])) {
        throw new Exception($result['error']['message']);
    }
}

function firebaseUpdateProfile($idToken, $displayName) {
    $endpoint = 'https://identitytoolkit.googleapis.com/v1/accounts:update?key=' . FIREBASE_API_KEY;
    
    $data = array(
        'idToken' => $idToken,
        'displayName' => $displayName,
        'returnSecureToken' => true
    );
    
    $response = firebaseHttpPostJson($endpoint, json_encode($data));
    $result = json_decode($response, true);
    
    if (isset($result['error'])) {
        throw new Exception($result['error']['message']);
    }
    
    return $result;
}
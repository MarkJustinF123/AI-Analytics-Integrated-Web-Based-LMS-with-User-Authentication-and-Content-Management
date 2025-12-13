<?php
session_start();
header('Content-Type: application/json');

// 1. Security Check
if (!isset($_SESSION['idToken'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// --- CONFIGURATION ---
require_once 'secrets.php'; 

// 2. Get Input
$input = json_decode(file_get_contents('php://input'), true);
$scores = $input['scores'] ?? [];
$courseName = $input['courseName'] ?? "Class";

if (empty($scores)) {
    echo json_encode(['feedback' => "No graded data available for analysis."]);
    exit;
}

// 3. Calculate Stats
$count = count($scores);
$avg = round(array_sum($scores) / $count, 2);
$max = max($scores);
$min = min($scores);
$scoresList = implode(", ", array_slice($scores, 0, 30)); 

// 4. Ask Gemini
$aiFeedback = "Analysis unavailable.";

if (isset($GEMINI_API_KEY) && !empty($GEMINI_API_KEY)) {
    $prompt = "
    You are an academic performance analyst.
    Course: $courseName
    Total Students: $count
    Average Score: $avg
    Highest: $max
    Lowest: $min
    Sample Scores: [$scoresList]

    Task:
    Write a short paragraph (3 sentences) summarizing the class performance.
    Mention if the class is doing well or struggling.
    Provide ONE teaching recommendation.
    ";

    $url = "https://generativelanguage.googleapis.com/v1/models/gemini-2.5-flash:generateContent?key=$GEMINI_API_KEY";
    
    $payload = ["contents" => [["parts" => [["text" => $prompt]]]]];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $aiRes = curl_exec($ch);
    
    if(curl_errno($ch)){
        $aiFeedback = "Connection Error: " . curl_error($ch);
    } else {
        $aiJson = json_decode($aiRes, true);
        
        if (isset($aiJson['candidates'][0]['content']['parts'][0]['text'])) {
            $aiFeedback = $aiJson['candidates'][0]['content']['parts'][0]['text'];
        } elseif (isset($aiJson['error'])) {
            $aiFeedback = "AI Error: " . $aiJson['error']['message'];
        } else {
            $aiFeedback = "AI Error: Invalid response structure.";
        }
    }
    // REMOVED: curl_close($ch); 
}

// 5. Return Result
echo json_encode(['success' => true, 'feedback' => $aiFeedback]);
?>
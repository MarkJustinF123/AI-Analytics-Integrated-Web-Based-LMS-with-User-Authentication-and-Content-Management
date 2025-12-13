<?php
session_start();
header('Content-Type: application/json');

// 1. Security Check
if (!isset($_SESSION['idToken'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// --- CONFIGURATION ---
require_once 'secrets.php';

// 2. Get Input from Instructor
$input = json_decode(file_get_contents('php://input'), true);
$submissionId = $input['submissionId'];
$grade = intval($input['grade']);

if (!$submissionId) { echo json_encode(['error' => 'No ID provided']); exit; }

// 3. Fetch the Student's Answer from Strapi
$ch = curl_init("$STRAPI_URL/api/submissions/$submissionId");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Authorization: Bearer $STRAPI_TOKEN"]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$subRes = curl_exec($ch);
// REMOVED: curl_close($ch); 

$subData = json_decode($subRes, true);
$studentText = $subData['data']['attributes']['content'] ?? $subData['data']['content'] ?? "No text answer provided.";

// 4. Ask Gemini for Feedback
$aiFeedback = "Graded manually.";

if (isset($GEMINI_API_KEY) && !empty($GEMINI_API_KEY)) {
    $prompt = "
    You are a supportive academic tutor. 
    A student submitted this answer: \"$studentText\"
    The instructor gave a grade of: $grade / 100.
    
    Task:
    Analyze the answer based on the score.
    Provide feedback in this Markdown format:
    **Strengths:** (1 sentence)
    **Weaknesses:** (1 sentence)
    **Verdict:** (1 short encouraging sentence)
    ";

    $geminiPayload = [
        "contents" => [[
            "parts" => [["text" => $prompt]]
        ]]
    ];

    $ch = curl_init("https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent?key=" . $GEMINI_API_KEY);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($geminiPayload));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

    $aiRes = curl_exec($ch);
    // REMOVED: curl_close($ch);
    
    $aiJson = json_decode($aiRes, true);
    if(isset($aiJson['candidates'][0]['content']['parts'][0]['text'])) {
        $aiFeedback = $aiJson['candidates'][0]['content']['parts'][0]['text'];
    }
}

// 5. Save Grade + AI Feedback to Strapi
$updateData = [
    "data" => [
        "grade" => $grade,
        "submission_status" => "graded",
        "ai_feedback" => $aiFeedback 
    ]
];

$ch = curl_init("$STRAPI_URL/api/submissions/$submissionId");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PUT");
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($updateData));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "Authorization: Bearer $STRAPI_TOKEN",
    "Content-Type: application/json"
]);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

$finalRes = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
// REMOVED: curl_close($ch);

if ($httpCode >= 200 && $httpCode < 300) {
    echo json_encode(['success' => true, 'feedback' => $aiFeedback]);
} else {
    echo json_encode(['error' => 'Failed to save to Strapi', 'details' => $finalRes]);
}
?>
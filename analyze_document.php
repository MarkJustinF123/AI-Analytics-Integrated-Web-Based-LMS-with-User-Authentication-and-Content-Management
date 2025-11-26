<?php
session_start();
header('Content-Type: application/json');

// 1. Security Check
if (!isset($_SESSION['idToken'])) {
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// --- CONFIGURATION ---
// PASTE YOUR GOOGLE GEMINI API KEY HERE
$API_KEY = "AIzaSyB7BZUCe43zFT1c3u11kWyoR_T3uWXjUu8"; 

// Endpoint for Gemini 1.5 Flash
$API_URL = "https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key=" . $API_KEY;

// 2. Validate Upload
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['document'])) {
    echo json_encode(['error' => 'No document uploaded']);
    exit;
}

$file = $_FILES['document'];
$mimeType = mime_content_type($file['tmp_name']);
$filePath = $file['tmp_name'];

// 3. Prepare Data for Gemini
// We convert the image/PDF to Base64 to send it inside the JSON payload
$base64Data = base64_encode(file_get_contents($filePath));

// The AI Prompt
$promptText = "
You are an expert academic evaluator. Analyze the attached student work (image or document).
1. Identify the subject and the student's answers.
2. Evaluate the performance.
3. Provide feedback in this specific format (use Markdown):

**Subject:** [Subject Name]
**Score Estimation:** [e.g., 8/10]

### ✅ Strengths
* [Point 1]
* [Point 2]

### ⚠️ Areas for Improvement
* [Point 1]
* [Point 2]

### 💡 Recommendation
[1 sentence tip]
";

// Construct Payload
$payload = [
    "contents" => [
        [
            "parts" => [
                ["text" => $promptText],
                [
                    "inline_data" => [
                        "mime_type" => $mimeType,
                        "data" => $base64Data
                    ]
                ]
            ]
        ]
    ]
];

// 4. Send Request to Google
$ch = curl_init($API_URL);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// 5. Handle Response
if ($httpCode === 200) {
    $json = json_decode($response, true);
    // Extract the text from Gemini's complex response structure
    $aiText = $json['candidates'][0]['content']['parts'][0]['text'] ?? 'No analysis generated.';
    echo json_encode(['success' => true, 'analysis' => $aiText]);
} else {
    echo json_encode(['error' => 'AI Error: ' . $response]);
}
?>
<?php

header('Content-Type: application/json; charset=utf-8');

$GEMINI_API_KEY = 'YOUR_GEMINI_API_KEY_HERE';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode([
        'error' => 'Invalid request method'
    ]);
    exit;
}

$question = trim($_POST['question'] ?? '');

if ($question === '' && !isset($_FILES['image'])) {
    echo json_encode([
        'error' => 'Question or image is required'
    ]);
    exit;
}

$parts = [];

$prompt = "
তুমি একজন বাংলাদেশি অনলাইন প্রোডাক্ট রিসার্চ অ্যাসিস্ট্যান্ট।

ব্যবহারকারীর দেওয়া প্রশ্ন এবং ছবির ভিত্তিতে:
1. প্রোডাক্টের নাম/মডেল শনাক্ত করো
2. বাংলাদেশে বর্তমান আনুমানিক বাজারমূল্য খুঁজে বের করো
3. Price range দাও
4. নতুন/ব্যবহৃত হলে আলাদা করে বলো
5. কোথায় পাওয়া যায় তা বলো
6. প্রয়োজনীয় গুরুত্বপূর্ণ তথ্য দাও
7. সম্ভব হলে নির্ভরযোগ্য source উল্লেখ করো
8. অনুমান করলে পরিষ্কারভাবে বলো যে এটি আনুমানিক

ব্যবহারকারী:
" . $question;

$parts[] = [
    'text' => $prompt
];


/* IMAGE */
if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {

    $fileSize = $_FILES['image']['size'];

    if ($fileSize > 8 * 1024 * 1024) {
        echo json_encode([
            'error' => 'Image is too large. Maximum 8MB.'
        ]);
        exit;
    }

    $mimeType = mime_content_type($_FILES['image']['tmp_name']);

    $allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];

    if (!in_array($mimeType, $allowedTypes)) {
        echo json_encode([
            'error' => 'Only JPG, PNG and WEBP images are supported.'
        ]);
        exit;
    }

    $imageData = base64_encode(
        file_get_contents($_FILES['image']['tmp_name'])
    );

    $parts[] = [
        'inline_data' => [
            'mime_type' => $mimeType,
            'data' => $imageData
        ]
    ];
}


/* GEMINI REQUEST */

$url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-2.5-flash:generateContent';

$data = [
    'contents' => [
        [
            'parts' => $parts
        ]
    ],
    'tools' => [
        [
            'google_search' => new stdClass()
        ]
    ]
];

$ch = curl_init($url);

curl_setopt_array($ch, [
    CURLOPT_POST => true,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Content-Type: application/json',
        'x-goog-api-key: ' . $GEMINI_API_KEY
    ],
    CURLOPT_POSTFIELDS => json_encode($data),
    CURLOPT_TIMEOUT => 60
]);

$response = curl_exec($ch);

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);

curl_close($ch);


/* CURL ERROR */

if ($response === false || $curlError) {
    echo json_encode([
        'error' => 'Gemini connection failed: ' . $curlError
    ]);
    exit;
}


/* GEMINI ERROR */

$result = json_decode($response, true);

if ($httpCode < 200 || $httpCode >= 300) {

    $message = $result['error']['message']
        ?? 'Unknown Gemini API error';

    echo json_encode([
        'error' => 'Gemini API Error (' . $httpCode . '): ' . $message
    ]);

    exit;
}


/* ANSWER */

$answer = '';

if (isset($result['candidates'][0]['content']['parts'])) {

    foreach ($result['candidates'][0]['content']['parts'] as $part) {

        if (isset($part['text'])) {
            $answer .= $part['text'];
        }
    }
}

if ($answer === '') {

    echo json_encode([
        'error' => 'Gemini returned an empty response.',
        'raw' => $result
    ]);

    exit;
}

echo json_encode([
    'answer' => $answer
], JSON_UNESCAPED_UNICODE);

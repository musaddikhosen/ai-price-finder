<?php

header('Content-Type: application/json; charset=utf-8');


// ============================================================
// GEMINI API KEY
// ============================================================
// এখানে এখনো আসল API key GitHub-এ বসাবি না.
// নিচের YOUR_GEMINI_API_KEY_HERE শুধু placeholder.
// Live server-এ গিয়ে আসল key বসাবি.
// ============================================================

$GEMINI_API_KEY = 'YOUR_GEMINI_API_KEY_HERE';


// ============================================================
// BASIC REQUEST CHECK
// ============================================================

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    echo json_encode([
        'message' => 'Only POST requests are allowed.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ============================================================
// INPUT
// ============================================================

$question = trim(
    $_POST['question'] ?? ''
);


// ============================================================
// IMAGE CHECK
// ============================================================

$imagePart = null;

if (
    isset($_FILES['image']) &&
    $_FILES['image']['error'] === UPLOAD_ERR_OK
) {

    $image = $_FILES['image'];


    // Maximum 8 MB
    if ($image['size'] > 8 * 1024 * 1024) {

        http_response_code(400);

        echo json_encode([
            'message' =>
                'ছবির সর্বোচ্চ সাইজ 8 MB হতে পারবে।'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    $allowedTypes = [
        'image/jpeg',
        'image/png',
        'image/webp'
    ];


    $mimeType = mime_content_type(
        $image['tmp_name']
    );


    if (!in_array($mimeType, $allowedTypes, true)) {

        http_response_code(400);

        echo json_encode([
            'message' =>
                'শুধু JPG, PNG অথবা WEBP ছবি দেওয়া যাবে।'
        ], JSON_UNESCAPED_UNICODE);

        exit;
    }


    $imageData = base64_encode(
        file_get_contents(
            $image['tmp_name']
        )
    );


    $imagePart = [

        'inline_data' => [

            'mime_type' => $mimeType,

            'data' => $imageData

        ]

    ];
}


// ============================================================
// NOTHING PROVIDED
// ============================================================

if ($question === '' && $imagePart === null) {

    http_response_code(400);

    echo json_encode([
        'message' =>
            'Product-এর নাম অথবা Product-এর ছবি দিন।'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ============================================================
// AI INSTRUCTION
// ============================================================

$instruction = <<<PROMPT

তুমি একজন professional Bangladesh product research assistant.

User একটি product-এর নাম, প্রশ্ন অথবা product image দিয়েছে।

তোমার কাজ:

1. Product identify করো।
2. Product-এর সম্ভাব্য exact/closest name বের করো।
3. Product category বলো।
4. যদি ছবি দেওয়া হয়, ছবিতে দেখা যায় এমন specification/features বলো।
5. বর্তমান Bangladesh market price research করো।
6. Google Search ব্যবহার করে current online information খুঁজে দেখো।
7. সম্ভব হলে Bangladesh-এর relevant seller/store/marketplace-এর দাম compare করো।
8. Exact price নিশ্চিত না হলে price range দাও।
9. Original, unofficial, used/refurbished হলে price difference উল্লেখ করো।
10. কোনো seller, price, specification বা তথ্য বানিয়ে বলবে না।
11. Search result না পাওয়া গেলে পরিষ্কারভাবে বলবে যে reliable current price পাওয়া যায়নি।
12. উত্তর অবশ্যই সহজ ও পরিষ্কার বাংলায় দেবে।
13. Currency হিসেবে বাংলাদেশি টাকা (৳) ব্যবহার করবে।

বিশেষ নির্দেশনা:

- Current price-এর ক্ষেত্রে web research-কে priority দাও।
- একাধিক source পাওয়া গেলে compare করো।
- অস্বাভাবিকভাবে কম বা বেশি price দেখলে সেটা উল্লেখ করো।
- User যদি শুধু product name দেয়, নিজে থেকে useful price research করো।
- User যদি image দেয়, আগে image থেকে product identify করার চেষ্টা করো।
- Product-এর exact identity নিশ্চিত না হলে সম্ভাব্য match হিসেবে উল্লেখ করো।

উত্তরের format:

🛍️ Product:
📂 Category:

💰 আনুমানিক বাংলাদেশি দাম:
৳...

📊 সম্ভাব্য Price Range:
৳... - ৳...

🏷️ Condition:
Original / Unofficial / Used / Unknown

🔎 গুরুত্বপূর্ণ তথ্য:
...

📦 কোথায় পাওয়া যেতে পারে:
...

🧠 Price Analysis:
...

⚠️ সতর্কতা:
...

🌐 Sources:
যে গুরুত্বপূর্ণ website/source ব্যবহার করেছো, সেগুলোর নাম ও URL উল্লেখ করো।

PROMPT;


// ============================================================
// BUILD GEMINI CONTENT
// ============================================================

$parts = [];


$parts[] = [
    'text' => $instruction
];


if ($question !== '') {

    $parts[] = [
        'text' =>
            "User-এর প্রশ্ন:\n\n" . $question
    ];
}


if ($imagePart !== null) {

    $parts[] = $imagePart;
}


// ============================================================
// GEMINI API URL
// ============================================================

$model = 'gemini-2.5-flash';


$url =
    'https://generativelanguage.googleapis.com/v1beta/models/'
    . $model
    . ':generateContent';


// ============================================================
// REQUEST BODY
// ============================================================

$payload = [

    'contents' => [

        [

            'parts' => $parts

        ]

    ],

    'tools' => [

        [

            'google_search' => []

        ]

    ]

];


// ============================================================
// CURL
// ============================================================

$ch = curl_init($url);


curl_setopt_array($ch, [

    CURLOPT_RETURNTRANSFER => true,

    CURLOPT_POST => true,

    CURLOPT_POSTFIELDS =>
        json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE
        ),

    CURLOPT_HTTPHEADER => [

        'Content-Type: application/json',

        'x-goog-api-key: ' . $GEMINI_API_KEY

    ],

    CURLOPT_TIMEOUT => 90

]);


$response = curl_exec($ch);


$curlError = curl_error($ch);

$httpCode = curl_getinfo(
    $ch,
    CURLINFO_HTTP_CODE
);


curl_close($ch);


// ============================================================
// CURL ERROR
// ============================================================

if ($response === false || $curlError) {

    http_response_code(500);

    echo json_encode([

        'message' =>
            'AI server-এর সাথে যোগাযোগ করা যাচ্ছে না।',

        'details' =>
            $curlError

    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ============================================================
// GEMINI RESPONSE
// ============================================================

$data = json_decode(
    $response,
    true
);


if ($httpCode < 200 || $httpCode >= 300) {

    http_response_code(500);

    echo json_encode([

        'message' =>
            'Gemini API request failed.',

        'details' =>
            $data

    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ============================================================
// EXTRACT TEXT
// ============================================================

$answer = '';


if (
    isset(
        $data['candidates'][0]['content']['parts']
    )
) {

    foreach (
        $data['candidates'][0]['content']['parts']
        as $part
    ) {

        if (
            isset($part['text'])
        ) {

            $answer .=
                $part['text'];

        }

    }

}


// ============================================================
// NO ANSWER
// ============================================================

if (trim($answer) === '') {

    http_response_code(500);

    echo json_encode([

        'message' =>
            'AI কোনো valid answer দেয়নি।',

        'details' =>
            $data

    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ============================================================
// FINAL RESPONSE
// ============================================================

echo json_encode([

    'answer' =>
        trim($answer)

], JSON_UNESCAPED_UNICODE);

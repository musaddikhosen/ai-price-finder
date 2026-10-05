<?php

header(
    'Content-Type: application/json'
);


if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

    http_response_code(405);

    echo json_encode([
        'message' =>
            'Only POST requests are allowed.'
    ]);

    exit;
}


$question =
    trim(
        $_POST['question'] ?? ''
    );


if (
    $question === '' &&
    empty($_FILES['image'])
) {

    http_response_code(400);

    echo json_encode([
        'message' =>
            'Product name or image is required.'
    ]);

    exit;
}


/*
|--------------------------------------------------------------------------
| AI code will be connected here
|--------------------------------------------------------------------------
*/


echo json_encode([

    'answer' =>
        "Backend successfully received your request.\n\n"
        . "Question: "
        . $question

]);

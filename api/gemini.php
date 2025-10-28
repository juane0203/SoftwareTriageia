<?php
require_once 'config.php';

function callGeminiAI($prompt) {
    $url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-pro:generateContent';
    
    $data = [
        'contents' => [
            [
                'parts' => [
                    ['text' => $prompt]
                ]
            ]
        ],
        'safetySettings' => [
            [
                'category' => 'HARM_CATEGORY_SAFETY',
                'threshold' => 'BLOCK_ONLY_HIGH'
            ]
        ],
        'generationConfig' => [
            'temperature' => 0.2,
            'topK' => 40,
            'topP' => 0.95
        ]
    ];

    $ch = curl_init($url . '?key=' . GEMINI_API_KEY);
    curl_setopt_array($ch, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_HTTPHEADER => ['Content-Type: application/json'],
        CURLOPT_POSTFIELDS => json_encode($data)
    ]);

    $response = curl_exec($ch);
    curl_close($ch);

    return json_decode($response, true);
}
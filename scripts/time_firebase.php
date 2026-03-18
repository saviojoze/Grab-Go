<?php
$firebase_api_key = 'AIzaSyAxNsQWe2L5bhMpr5VfEJhctgciZE7ARso';
$url = "https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword?key=" . $firebase_api_key;

$start = microtime(true);
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'email' => 'invalid@gmail.com',
    'password' => 'invalid',
    'returnSecureToken' => true
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$response = curl_exec($ch);
$end = microtime(true);

echo "Time: " . ($end - $start) . " seconds\n";
echo "Response: " . $response . "\n";
curl_close($ch);
?>

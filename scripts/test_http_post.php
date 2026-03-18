<?php
$data = ['email' => 'staff@grabandgo.com', 'password' => 'password'];
$options = [
    'http' => [
        'header'  => "Content-Type: application/json\r\n",
        'method'  => 'POST',
        'content' => json_encode($data),
    ],
];
$context  = stream_context_create($options);
$result = file_get_contents('http://localhost/Mini%20Project/api/mobile_login.php', false, $context);
if ($result === FALSE) {
    echo "ERROR: Request failed" . PHP_EOL;
} else {
    echo "RESULT: " . $result . PHP_EOL;
}
?>

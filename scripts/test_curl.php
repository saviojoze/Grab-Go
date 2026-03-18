<?php
$url = "https://identitytoolkit.googleapis.com/v1/accounts:signInWithPassword";
$ch = curl_init($url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true); // Keep it true to test real behavior
$response = curl_exec($ch);
if ($response === false) {
    echo "CURL Error: " . curl_error($ch) . "\n";
} else {
    echo "CURL Success (Response received)\n";
}
curl_close($ch);
?>

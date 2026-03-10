<?php
// Mock input for staff
$data = json_encode(['email' => 'staff@grabandgo.com', 'password' => 'password']);
$ch = curl_init('http://localhost/Mini%20Project/api/mobile_login.php');
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
echo $result;
curl_close($ch);
?>

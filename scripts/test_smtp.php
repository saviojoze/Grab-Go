<?php
require_once __DIR__ . '/config.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

require_once __DIR__ . '/phpmailserver/PHPMailer-master/PHPMailer-master/src/Exception.php';
require_once __DIR__ . '/phpmailserver/PHPMailer-master/PHPMailer-master/src/PHPMailer.php';
require_once __DIR__ . '/phpmailserver/PHPMailer-master/PHPMailer-master/src/SMTP.php';

$test_to = 'saviojosekvr@gmail.com'; // change to your test email

echo '<pre style="font-family:monospace;font-size:14px;padding:20px;">';
echo "=== SMTP Diagnostic Test ===\n\n";

echo "SMTP_HOST       : " . SMTP_HOST . "\n";
echo "SMTP_PORT       : " . SMTP_PORT . "\n";
echo "SMTP_USERNAME   : " . SMTP_USERNAME . "\n";
echo "SMTP_PASSWORD   : " . (SMTP_PASSWORD === 'YOUR_APP_PASSWORD_HERE' ? '❌ NOT SET — still placeholder!' : '✅ Set (' . strlen(SMTP_PASSWORD) . ' chars)') . "\n";
echo "SMTP_FROM_EMAIL : " . SMTP_FROM_EMAIL . "\n\n";

if (SMTP_PASSWORD === 'YOUR_APP_PASSWORD_HERE') {
    echo "❌ PROBLEM FOUND: SMTP_PASSWORD is not set in .env\n";
    echo "   Go to https://myaccount.google.com/apppasswords\n";
    echo "   Sign in as grabandgonline@gmail.com\n";
    echo "   Create an App Password and paste it in .env line 13\n\n";
} else {
    echo "Attempting to connect to Gmail SMTP...\n\n";

    $mail = new PHPMailer(true);

    try {
        $mail->SMTPDebug  = SMTP::DEBUG_SERVER; // show full SMTP conversation
        $mail->Debugoutput = function($str, $level) {
            echo htmlspecialchars($str) . "\n";
        };

        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = true;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = (int) SMTP_PORT;

        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($test_to);

        $mail->isHTML(true);
        $mail->Subject = 'Grab & Go — SMTP Test Email';
        $mail->Body    = '<h2>✅ SMTP is working!</h2><p>This is a test email from Grab &amp; Go.</p>';
        $mail->AltBody = 'SMTP is working! This is a test from Grab & Go.';

        $mail->send();
        echo "\n\n✅ SUCCESS! Test email sent to: $test_to\n";
        echo "Check your inbox (and spam folder).\n";

    } catch (Exception $e) {
        echo "\n\n❌ FAILED! Error: " . $mail->ErrorInfo . "\n";
        echo "\nCommon fixes:\n";
        echo "  - Wrong app password → re-generate at myaccount.google.com/apppasswords\n";
        echo "  - 2-Step Verification not enabled on grabandgonline@gmail.com\n";
        echo "  - Less secure app access blocked (use App Password instead)\n";
    }
}

echo '</pre>';
?>

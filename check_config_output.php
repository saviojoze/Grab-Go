<?php
ob_start();
require_once 'config.php';
$output = ob_get_clean();
if (!empty($output)) {
    echo "Unexpected output from config.php: " . bin2hex($output) . PHP_EOL;
    echo $output;
} else {
    echo "config.php is clean." . PHP_EOL;
}
?>

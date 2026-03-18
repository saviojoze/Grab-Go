<?php
/**
 * Local Image Generator (SVG Version)
 * Generates simple placeholder images using SVG (No GD required)
 * 100% Compatible with all PHP versions
 */

$text = isset($_GET['text']) ? $_GET['text'] : 'Product';
// Sanitize for XML
$text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');

// Set content type to SVG
header('Content-Type: image/svg+xml');

// Output XML declaration
echo '<?xml version="1.0" encoding="UTF-8"?>';
?>
<svg width="400" height="400" xmlns="http://www.w3.org/2000/svg">
    <!-- Background -->
    <rect width="100%" height="100%" fill="#f5f5f7"/>
    
    <!-- Border -->
    <rect x="0" y="0" width="400" height="400" fill="none" stroke="#e1e1e1" stroke-width="4"/>
    
    <!-- Text -->
    <text x="50%" y="50%" font-family="Arial, Helvetica, sans-serif" font-size="20" fill="#333333" text-anchor="middle" dominant-baseline="middle">
        <?php echo $text; ?>
    </text>
</svg>

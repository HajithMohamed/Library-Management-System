<?php
// Create placeholder image for missing book covers
$placeholderDir = __DIR__ . '/uploads/books/';

if (!is_dir($placeholderDir)) {
    mkdir($placeholderDir, 0755, true);
}

// Create a simple placeholder image
$width = 300;
$height = 450;
$image = imagecreatetruecolor($width, $height);

// Colors
$bg = imagecolorallocate($image, 102, 126, 234); // Purple gradient color
$textColor = imagecolorallocate($image, 255, 255, 255);

// Fill background
imagefilledrectangle($image, 0, 0, $width, $height, $bg);

// Add text
$text = "No Cover\nAvailable";
$font = 5; // Built-in font
imagestring($image, $font, 100, 200, "No Cover", $textColor);
imagestring($image, $font, 95, 230, "Available", $textColor);

// Save placeholder
imagepng($image, $placeholderDir . 'placeholder.png');
imagedestroy($image);

echo "Placeholder image created successfully!";
?>

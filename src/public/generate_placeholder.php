<?php
/**
 * Generate a book placeholder PNG image
 * Run this once: php generate_placeholder.php
 */

$outputDir = __DIR__ . '/assets/images/';
if (!is_dir($outputDir)) {
    mkdir($outputDir, 0755, true);
}

$pngPath = $outputDir . 'book-placeholder.png';

if (!function_exists('imagecreatetruecolor')) {
    // No GD: create minimal PNG fallback
    $png = base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mN88P/BfwAJhAPk'
        . 'E3lkSgAAAABJRU5ErkJggg=='
    );
    file_put_contents($pngPath, $png);
    echo "Minimal PNG fallback created at: {$pngPath}\n";
    echo "The SVG placeholder at assets/images/book-placeholder.svg is the primary fallback.\n";
} else {
    $width = 200;
    $height = 280;
    $image = imagecreatetruecolor($width, $height);

    $bgColor = imagecolorallocate($image, 229, 231, 235);
    $innerBg = imagecolorallocate($image, 209, 213, 219);
    $iconColor = imagecolorallocate($image, 156, 163, 175);
    $textColor = imagecolorallocate($image, 107, 114, 128);

    imagefilledrectangle($image, 0, 0, $width, $height, $bgColor);
    imagefilledrectangle($image, 30, 40, 170, 200, $innerBg);
    imagefilledrectangle($image, 85, 80, 89, 170, $iconColor);
    imagefilledrectangle($image, 55, 90, 83, 160, $iconColor);
    imagefilledrectangle($image, 91, 90, 145, 160, $iconColor);

    $fontSize = 4;
    $text = "No Cover";
    $textWidth = imagefontwidth($fontSize) * strlen($text);
    imagestring($image, $fontSize, ($width - $textWidth) / 2, 230, $text, $textColor);

    imagepng($image, $pngPath);
    imagedestroy($image);
    echo "Placeholder PNG created at: {$pngPath}\n";
}

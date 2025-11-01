<?php
/**
 * Generate placeholder images for all books
 * Run this once: http://localhost:8080/create-book-placeholders.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

$database = new App\Config\Database();
global $mysqli;

// Create uploads directory if it doesn't exist
$uploadsDir = __DIR__ . '/uploads/books/';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

// Get all books with images
$result = $mysqli->query("SELECT isbn, bookName, bookImage FROM books WHERE bookImage IS NOT NULL AND bookImage != ''");

$created = 0;
$skipped = 0;

while ($book = $result->fetch_assoc()) {
    $imagePath = __DIR__ . '/' . $book['bookImage'];
    
    // Skip if image already exists
    if (file_exists($imagePath)) {
        $skipped++;
        continue;
    }
    
    // Create placeholder image (400x600 - book cover ratio)
    $width = 400;
    $height = 600;
    $image = imagecreatetruecolor($width, $height);
    
    // Random gradient colors
    $colors = [
        ['start' => [102, 126, 234], 'end' => [118, 75, 162]],  // Purple
        ['start' => [16, 185, 129], 'end' => [5, 150, 105]],    // Green
        ['start' => [239, 68, 68], 'end' => [220, 38, 38]],     // Red
        ['start' => [59, 130, 246], 'end' => [37, 99, 235]],    // Blue
        ['start' => [245, 158, 11], 'end' => [217, 119, 6]],    // Orange
    ];
    
    $colorSet = $colors[array_rand($colors)];
    
    // Create gradient background
    for ($y = 0; $y < $height; $y++) {
        $ratio = $y / $height;
        $r = $colorSet['start'][0] + ($colorSet['end'][0] - $colorSet['start'][0]) * $ratio;
        $g = $colorSet['start'][1] + ($colorSet['end'][1] - $colorSet['start'][1]) * $ratio;
        $b = $colorSet['start'][2] + ($colorSet['end'][2] - $colorSet['start'][2]) * $ratio;
        
        $color = imagecolorallocate($image, $r, $g, $b);
        imagefilledrectangle($image, 0, $y, $width, $y + 1, $color);
    }
    
    // Add white text
    $white = imagecolorallocate($image, 255, 255, 255);
    $gray = imagecolorallocate($image, 200, 200, 200);
    
    // Add book icon
    $iconSize = 120;
    imagestring($image, 5, ($width - $iconSize) / 2, $height / 2 - 100, '📚', $white);
    
    // Add book title (wrap text if too long)
    $title = $book['bookName'];
    $maxLength = 30;
    $lines = [];
    
    if (strlen($title) > $maxLength) {
        $words = explode(' ', $title);
        $currentLine = '';
        
        foreach ($words as $word) {
            if (strlen($currentLine . ' ' . $word) <= $maxLength) {
                $currentLine .= ($currentLine ? ' ' : '') . $word;
            } else {
                if ($currentLine) $lines[] = $currentLine;
                $currentLine = $word;
            }
        }
        if ($currentLine) $lines[] = $currentLine;
    } else {
        $lines[] = $title;
    }
    
    // Draw title lines
    $startY = $height / 2 + 20;
    foreach ($lines as $index => $line) {
        $lineWidth = strlen($line) * imagefontwidth(5);
        $x = ($width - $lineWidth) / 2;
        $y = $startY + ($index * 30);
        
        // Add shadow
        imagestring($image, 5, $x + 2, $y + 2, $line, $gray);
        imagestring($image, 5, $x, $y, $line, $white);
    }
    
    // Add "Library" text at bottom
    $libraryText = "University Library";
    $textWidth = strlen($libraryText) * imagefontwidth(3);
    imagestring($image, 3, ($width - $textWidth) / 2, $height - 40, $libraryText, $white);
    
    // Save image
    imagepng($image, $imagePath);
    imagedestroy($image);
    
    $created++;
}

echo "<!DOCTYPE html>
<html>
<head>
    <title>Placeholder Images Created</title>
    <style>
        body { font-family: Arial; padding: 40px; background: #f3f4f6; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0,0,0,0.1); }
        h1 { color: #1f2937; }
        .success { color: #10b981; font-size: 1.2rem; font-weight: bold; }
        .info { color: #6b7280; margin-top: 20px; }
        .btn { display: inline-block; margin-top: 30px; padding: 12px 24px; background: #667eea; color: white; text-decoration: none; border-radius: 8px; }
        .btn:hover { background: #5568d3; }
    </style>
</head>
<body>
    <div class='container'>
        <h1>✅ Book Placeholder Images Created!</h1>
        <p class='success'>Created: {$created} images</p>
        <p class='info'>Skipped: {$skipped} (already exist)</p>
        <p class='info'>All book cover placeholders have been generated in <code>uploads/books/</code></p>
        <a href='/faculty/books' class='btn'>View Books</a>
        <p style='margin-top: 30px; color: #ef4444; font-weight: bold;'>⚠️ Delete this file after use for security!</p>
    </div>
</body>
</html>";

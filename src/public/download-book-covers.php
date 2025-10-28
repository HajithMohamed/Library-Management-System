<?php
/**
 * Download real book covers from Open Library API
 * Run this once: http://localhost:8080/download-book-covers.php
 */

require_once __DIR__ . '/../vendor/autoload.php';

$database = new App\Config\Database();
global $mysqli;

$uploadsDir = __DIR__ . '/uploads/books/';
if (!is_dir($uploadsDir)) {
    mkdir($uploadsDir, 0755, true);
}

$result = $mysqli->query("SELECT isbn, bookName, bookImage FROM books WHERE bookImage IS NOT NULL");

$downloaded = 0;
$failed = 0;

echo "<!DOCTYPE html><html><head><title>Downloading Book Covers</title><style>
body { font-family: Arial; padding: 40px; background: #f3f4f6; }
.log { background: white; padding: 20px; border-radius: 8px; max-width: 800px; margin: 0 auto; }
.success { color: #10b981; }
.error { color: #ef4444; }
</style></head><body><div class='log'><h1>Downloading Book Covers...</h1>";

while ($book = $result->fetch_assoc()) {
    $isbn = str_replace('-', '', $book['isbn']);
    $imagePath = $uploadsDir . $book['bookImage'];
    
    if (file_exists($imagePath)) {
        echo "<p>⏭️ Skipping: {$book['bookName']} (already exists)</p>";
        continue;
    }
    
    // Try Open Library Cover API
    $coverUrl = "https://covers.openlibrary.org/b/isbn/{$isbn}-L.jpg";
    
    echo "<p>🔍 Trying: {$book['bookName']} (ISBN: {$isbn})...</p>";
    flush();
    
    $imageData = @file_get_contents($coverUrl);
    
    if ($imageData && strlen($imageData) > 1000) { // Check if it's not a placeholder
        file_put_contents($imagePath, $imageData);
        echo "<p class='success'>✅ Downloaded: {$book['bookName']}</p>";
        $downloaded++;
    } else {
        echo "<p class='error'>❌ Not found: {$book['bookName']}</p>";
        $failed++;
    }
    
    usleep(500000); // 0.5 second delay to avoid rate limiting
}

echo "<h2>✨ Complete!</h2>
<p class='success'>Downloaded: {$downloaded}</p>
<p class='error'>Failed: {$failed}</p>
<p><a href='/faculty/books'>View Books</a></p>
<p style='color: #ef4444;'>⚠️ Delete this file after use!</p>
</div></body></html>";

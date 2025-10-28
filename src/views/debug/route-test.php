<?php
// Temporary debug page
echo "<h1>Debug Info</h1>";
echo "<h2>Request URI</h2>";
echo "<pre>" . htmlspecialchars($_SERVER['REQUEST_URI']) . "</pre>";

echo "<h2>Parsed URI Path</h2>";
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
echo "<pre>" . htmlspecialchars($path) . "</pre>";

echo "<h2>Pattern Match Test</h2>";
if (preg_match('#/faculty/book/([^/]+)$#', $path, $matches)) {
    echo "<p>Match found!</p>";
    echo "<pre>";
    print_r($matches);
    echo "</pre>";
    echo "<p>ISBN: " . htmlspecialchars($matches[1]) . "</p>";
} else {
    echo "<p>No match</p>";
}

echo "<h2>GET Parameters</h2>";
echo "<pre>";
print_r($_GET);
echo "</pre>";

echo "<h2>Session</h2>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

<?php

namespace App\Helpers;

/**
 * ImageHelper - Centralized book cover image URL resolution
 * 
 * Handles path normalization, placeholder fallback, and file existence checks.
 * Fixes the double-path bug where DB values like "uploads/books/file.jpg"
 * were getting prefixed again with "uploads/books/".
 */
class ImageHelper
{
    /**
     * Get the full URL for a book cover image.
     * 
     * The database may store:
     *   - "uploads/books/book_abc123.jpg"  (full relative path)
     *   - "book_abc123.jpg"                (filename only)
     *   - null or ""                       (no image)
     * 
     * @param string|null $filename  The bookImage value from the database
     * @param bool $checkExists      Whether to verify the file exists on disk
     * @return string                Full URL to the image or placeholder
     */
    public static function getBookCoverUrl(?string $filename, bool $checkExists = true): string
    {
        // Return placeholder if no filename
        if (empty($filename)) {
            return self::getPlaceholderUrl();
        }

        // Normalize: strip any leading "uploads/books/" or "/uploads/books/" prefix
        // so we always work with just the filename
        $cleanFilename = self::normalizeFilename($filename);

        // Build the relative path for URL and disk check
        $relativePath = 'uploads/books/' . $cleanFilename;

        // Check if file actually exists on disk
        if ($checkExists) {
            $diskPath = self::getDiskPath($relativePath);
            if (!file_exists($diskPath)) {
                error_log("ImageHelper: File not found on disk: {$diskPath} (DB value: {$filename})");
                return self::getPlaceholderUrl();
            }
        }

        return rtrim(BASE_URL, '/') . '/' . $relativePath;
    }

    /**
     * Get the placeholder image URL.
     * Falls back to a data URI SVG if the placeholder file doesn't exist.
     *
     * @return string URL to the placeholder image
     */
    public static function getPlaceholderUrl(): string
    {
        // Try SVG first (better quality), then PNG
        foreach (['assets/images/book-placeholder.svg', 'assets/images/book-placeholder.png'] as $placeholderRelative) {
            $placeholderDisk = self::getDiskPath($placeholderRelative);
            if (file_exists($placeholderDisk)) {
                return rtrim(BASE_URL, '/') . '/' . $placeholderRelative;
            }
        }

        // Inline SVG data URI as ultimate fallback
        return 'data:image/svg+xml,' . rawurlencode(
            '<svg xmlns="http://www.w3.org/2000/svg" width="200" height="280" viewBox="0 0 200 280">'
            . '<rect width="200" height="280" rx="8" fill="#e5e7eb"/>'
            . '<rect x="30" y="60" width="140" height="160" rx="4" fill="#d1d5db"/>'
            . '<text x="100" y="150" text-anchor="middle" font-family="Arial,sans-serif" font-size="48" fill="#9ca3af">📖</text>'
            . '<text x="100" y="250" text-anchor="middle" font-family="Arial,sans-serif" font-size="14" fill="#9ca3af">No Cover</text>'
            . '</svg>'
        );
    }

    /**
     * Normalize a database bookImage value to just the filename.
     * Strips any path prefixes like "uploads/books/" that may already be stored.
     *
     * @param string $filename Raw value from DB
     * @return string Just the filename (e.g., "book_abc123.jpg")
     */
    public static function normalizeFilename(string $filename): string
    {
        // Remove leading slashes
        $filename = ltrim($filename, '/');

        // Remove "uploads/books/" prefix if present
        $prefixes = ['uploads/books/', 'assets/uploads/books/'];
        foreach ($prefixes as $prefix) {
            if (strpos($filename, $prefix) === 0) {
                $filename = substr($filename, strlen($prefix));
            }
        }

        return $filename;
    }

    /**
     * Get the absolute disk path for a file relative to the public directory.
     *
     * @param string $relativePath Path relative to public/ (e.g., "uploads/books/file.jpg")
     * @return string Absolute disk path
     */
    public static function getDiskPath(string $relativePath): string
    {
        // PUBLIC_ROOT points to src/public/ (the web root)
        if (defined('PUBLIC_ROOT')) {
            return PUBLIC_ROOT . '/' . ltrim($relativePath, '/');
        }

        // Fallback: APP_ROOT/public/
        if (defined('APP_ROOT')) {
            return APP_ROOT . '/public/' . ltrim($relativePath, '/');
        }

        return $relativePath;
    }

    /**
     * Get the relative path to store in the database for a book cover.
     * Always stores as "uploads/books/filename.ext" for consistency.
     *
     * @param string $filename The filename (without directory)
     * @return string The relative path to store in DB
     */
    public static function getStoragePath(string $filename): string
    {
        return 'uploads/books/' . self::normalizeFilename($filename);
    }

    /**
     * Get the absolute upload directory path and ensure it exists.
     *
     * @return string Absolute path to the upload directory
     */
    public static function getUploadDir(): string
    {
        if (defined('PUBLIC_ROOT')) {
            $dir = PUBLIC_ROOT . '/uploads/books/';
        } elseif (defined('APP_ROOT')) {
            $dir = APP_ROOT . '/public/uploads/books/';
        } else {
            $dir = __DIR__ . '/../public/uploads/books/';
        }

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
            error_log("ImageHelper: Created upload directory: {$dir}");
        }

        return $dir;
    }

    /**
     * Validate an uploaded image file.
     *
     * @param array $file The $_FILES['image'] array
     * @param int $maxSizeMB Maximum file size in MB (default 5)
     * @return array ['valid' => bool, 'error' => string|null, 'extension' => string|null]
     */
    public static function validateUpload(array $file, int $maxSizeMB = 5): array
    {
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errorMessages = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds server upload limit.',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds form upload limit.',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
                UPLOAD_ERR_EXTENSION => 'A PHP extension stopped the upload.',
            ];
            return [
                'valid' => false,
                'error' => $errorMessages[$file['error']] ?? 'Unknown upload error.',
                'extension' => null,
            ];
        }

        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        if (!in_array($extension, $allowedExtensions)) {
            return [
                'valid' => false,
                'error' => 'Invalid image format. Allowed: JPG, JPEG, PNG, GIF, WebP.',
                'extension' => $extension,
            ];
        }

        $maxBytes = $maxSizeMB * 1024 * 1024;
        if ($file['size'] > $maxBytes) {
            return [
                'valid' => false,
                'error' => "Image file is too large. Maximum size is {$maxSizeMB}MB.",
                'extension' => $extension,
            ];
        }

        // Verify it's actually an image
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);

        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (!in_array($mimeType, $allowedMimes)) {
            return [
                'valid' => false,
                'error' => 'File is not a valid image.',
                'extension' => $extension,
            ];
        }

        return [
            'valid' => true,
            'error' => null,
            'extension' => $extension,
        ];
    }

    /**
     * Process and save an uploaded book cover image.
     *
     * @param array $file The $_FILES['image'] array
     * @param string|null $oldImagePath The current DB value for bookImage (to delete old file)
     * @return array ['success' => bool, 'path' => string|null, 'error' => string|null]
     */
    public static function processUpload(array $file, ?string $oldImagePath = null): array
    {
        // Validate
        $validation = self::validateUpload($file);
        if (!$validation['valid']) {
            return ['success' => false, 'path' => null, 'error' => $validation['error']];
        }

        // Ensure upload directory exists
        $uploadDir = self::getUploadDir();

        // Generate unique filename
        $fileName = 'book_' . uniqid() . '.' . $validation['extension'];
        $targetPath = $uploadDir . $fileName;

        // Move uploaded file
        if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
            error_log("ImageHelper: Failed to move uploaded file to: {$targetPath}");
            return ['success' => false, 'path' => null, 'error' => 'Failed to save uploaded image.'];
        }

        // Delete old image if it exists
        if (!empty($oldImagePath)) {
            self::deleteImage($oldImagePath);
        }

        $storagePath = self::getStoragePath($fileName);
        error_log("ImageHelper: Image uploaded successfully: {$storagePath}");

        return ['success' => true, 'path' => $storagePath, 'error' => null];
    }

    /**
     * Delete a book cover image from disk.
     *
     * @param string $dbPath The bookImage value from the database
     * @return bool Whether the file was deleted
     */
    public static function deleteImage(string $dbPath): bool
    {
        if (empty($dbPath)) {
            return false;
        }

        $cleanFilename = self::normalizeFilename($dbPath);
        $relativePath = 'uploads/books/' . $cleanFilename;
        $diskPath = self::getDiskPath($relativePath);

        if (file_exists($diskPath) && is_file($diskPath)) {
            unlink($diskPath);
            error_log("ImageHelper: Deleted image: {$diskPath}");
            return true;
        }

        return false;
    }

    /**
     * Generate HTML for a book cover image with proper fallback.
     * Convenience method for use in views.
     *
     * @param string|null $bookImage The bookImage value from DB
     * @param string $altText Alt text for the image
     * @param string $cssClass CSS class for the img tag
     * @return string HTML img tag
     */
    public static function renderBookCover(?string $bookImage, string $altText = 'Book cover', string $cssClass = 'book-cover'): string
    {
        $url = self::getBookCoverUrl($bookImage);
        $placeholderUrl = self::getPlaceholderUrl();
        $escapedAlt = htmlspecialchars($altText, ENT_QUOTES, 'UTF-8');

        return sprintf(
            '<img src="%s" alt="%s" class="%s" onerror="this.onerror=null;this.src=\'%s\';" loading="lazy">',
            htmlspecialchars($url, ENT_QUOTES, 'UTF-8'),
            $escapedAlt,
            htmlspecialchars($cssClass, ENT_QUOTES, 'UTF-8'),
            htmlspecialchars($placeholderUrl, ENT_QUOTES, 'UTF-8')
        );
    }
}

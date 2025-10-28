<?php

namespace App\Helpers;

use Picqer\Barcode\BarcodeGeneratorPNG;
use Picqer\Barcode\BarcodeGeneratorSVG;

class BarcodeHelper
{
    /**
     * Generate barcode image for a book
     * @param string $isbn Book ISBN
     * @param string $bookName Book name
     * @param string $authorName Author name
     * @return array ['success' => bool, 'filename' => string, 'barcode_value' => string]
     */
    public static function generateBookBarcode($isbn, $bookName, $authorName)
    {
        try {
            // Clean ISBN for barcode (remove hyphens)
            $cleanIsbn = str_replace('-', '', $isbn);
            $barcodeValue = 'BC' . $cleanIsbn;
            
            // Create barcode generator
            $generator = new BarcodeGeneratorPNG();
            
            // Generate barcode image
            $barcodeData = $generator->getBarcode($barcodeValue, $generator::TYPE_CODE_128, 3, 50);
            
            // Create filename
            $filename = $cleanIsbn . '_barcode.png';
            $uploadDir = APP_ROOT . '/public/uploads/barcodes/';
            
            // Create directory if it doesn't exist
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }
            
            // Save barcode image
            $filepath = $uploadDir . $filename;
            file_put_contents($filepath, $barcodeData);
            
            // Generate detailed barcode label with book info
            $labelFilename = self::generateBarcodeLabel($isbn, $bookName, $authorName, $barcodeValue);
            
            return [
                'success' => true,
                'filename' => $filename,
                'label_filename' => $labelFilename,
                'barcode_value' => $barcodeValue,
                'path' => 'public/uploads/barcodes/' . $filename
            ];
            
        } catch (\Exception $e) {
            error_log("Barcode generation error: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Generate barcode label with book details
     */
    private static function generateBarcodeLabel($isbn, $bookName, $authorName, $barcodeValue)
    {
        try {
            $generator = new BarcodeGeneratorPNG();
            $barcodeData = $generator->getBarcode($barcodeValue, $generator::TYPE_CODE_128, 3, 50);
            
            // Create image from barcode
            $barcode = imagecreatefromstring($barcodeData);
            $barcodeWidth = imagesx($barcode);
            $barcodeHeight = imagesy($barcode);
            
            // Create label image (wider and taller for text)
            $labelWidth = max($barcodeWidth + 40, 400);
            $labelHeight = $barcodeHeight + 150;
            $label = imagecreatetruecolor($labelWidth, $labelHeight);
            
            // Colors
            $white = imagecolorallocate($label, 255, 255, 255);
            $black = imagecolorallocate($label, 0, 0, 0);
            $gray = imagecolorallocate($label, 100, 100, 100);
            $blue = imagecolorallocate($label, 102, 126, 234);
            
            // Fill background
            imagefill($label, 0, 0, $white);
            
            // Add border
            imagerectangle($label, 0, 0, $labelWidth - 1, $labelHeight - 1, $blue);
            imagerectangle($label, 1, 1, $labelWidth - 2, $labelHeight - 2, $blue);
            
            // Copy barcode to center
            $barcodeX = ($labelWidth - $barcodeWidth) / 2;
            $barcodeY = 70;
            imagecopy($label, $barcode, $barcodeX, $barcodeY, 0, 0, $barcodeWidth, $barcodeHeight);
            
            // Add text using built-in font (more reliable than TTF)
            $font = 5; // Font size 1-5
            
            // Title: "Library Book"
            $titleText = "LIBRARY BOOK";
            $textWidth = imagefontwidth($font) * strlen($titleText);
            $textX = ($labelWidth - $textWidth) / 2;
            imagestring($label, $font, $textX, 15, $titleText, $blue);
            
            // Book title (truncate if too long)
            $bookTitle = substr($bookName, 0, 35);
            $textWidth = imagefontwidth($font) * strlen($bookTitle);
            $textX = ($labelWidth - $textWidth) / 2;
            imagestring($label, $font, $textX, 35, $bookTitle, $black);
            
            // Author
            $authorText = "By: " . substr($authorName, 0, 30);
            $textWidth = imagefontwidth($font) * strlen($authorText);
            $textX = ($labelWidth - $textWidth) / 2;
            imagestring($label, $font, $textX, 50, $authorText, $gray);
            
            // ISBN below barcode
            $isbnText = "ISBN: " . $isbn;
            $textWidth = imagefontwidth($font) * strlen($isbnText);
            $textX = ($labelWidth - $textWidth) / 2;
            imagestring($label, $font, $textX, $barcodeY + $barcodeHeight + 15, $isbnText, $black);
            
            // Barcode value
            $textWidth = imagefontwidth($font) * strlen($barcodeValue);
            $textX = ($labelWidth - $textWidth) / 2;
            imagestring($label, $font, $textX, $barcodeY + $barcodeHeight + 35, $barcodeValue, $gray);
            
            // Save label
            $cleanIsbn = str_replace('-', '', $isbn);
            $labelFilename = $cleanIsbn . '_label.png';
            $uploadDir = APP_ROOT . '/public/uploads/barcodes/';
            $labelPath = $uploadDir . $labelFilename;
            
            imagepng($label, $labelPath);
            
            // Free memory
            imagedestroy($label);
            imagedestroy($barcode);
            
            return $labelFilename;
            
        } catch (\Exception $e) {
            error_log("Barcode label generation error: " . $e->getMessage());
            return null;
        }
    }
    
    /**
     * Generate SVG barcode (for web display)
     */
    public static function generateSVGBarcode($isbn)
    {
        try {
            $cleanIsbn = str_replace('-', '', $isbn);
            $barcodeValue = 'BC' . $cleanIsbn;
            
            $generator = new BarcodeGeneratorSVG();
            $svgCode = $generator->getBarcode($barcodeValue, $generator::TYPE_CODE_128);
            
            return [
                'success' => true,
                'svg' => $svgCode,
                'barcode_value' => $barcodeValue
            ];
            
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete barcode files
     */
    public static function deleteBarcode($isbn)
    {
        $cleanIsbn = str_replace('-', '', $isbn);
        $uploadDir = APP_ROOT . '/public/uploads/barcodes/';
        
        $barcodeFile = $uploadDir . $cleanIsbn . '_barcode.png';
        $labelFile = $uploadDir . $cleanIsbn . '_label.png';
        
        $deleted = false;
        
        if (file_exists($barcodeFile)) {
            unlink($barcodeFile);
            $deleted = true;
        }
        
        if (file_exists($labelFile)) {
            unlink($labelFile);
            $deleted = true;
        }
        
        return $deleted;
    }
}

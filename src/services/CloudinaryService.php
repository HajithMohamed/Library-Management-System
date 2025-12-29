<?php

namespace App\Services;

use Cloudinary\Configuration\Configuration;
use Cloudinary\Api\Upload\UploadApi;
use Cloudinary\Api\Admin\AdminApi;

class CloudinaryService
{
    private $uploadApi;
    private $adminApi;

    public function __construct()
    {
        // Configure Cloudinary with environment variables
        Configuration::instance([
            'cloud' => [
                'cloud_name' => $_ENV['CLOUDINARY_CLOUD_NAME'] ?? '',
                'api_key'    => $_ENV['CLOUDINARY_API_KEY'] ?? '',
                'api_secret' => $_ENV['CLOUDINARY_API_SECRET'] ?? '',
            ],
            'url' => [
                'secure' => true
            ]
        ]);
    }

    /**
     * Upload a file to Cloudinary
     * 
     * @param string $filePath Path to the file to upload
     * @param string $folder Optional folder in Cloudinary
     * @return array Result containing secure_url and public_id
     */
    public function uploadFile($filePath, $folder = 'library_resources')
    {
        try {
            $uploadApi = new UploadApi();
            $result = $uploadApi->upload($filePath, [
                'folder' => $folder,
                'resource_type' => 'auto' // Auto-detect type (image, video, raw/pdf)
            ]);

            return [
                'success' => true,
                'url' => $result['secure_url'],
                'public_id' => $result['public_id'],
                'format' => $result['format'] ?? '',
                'original_filename' => $result['original_filename'] ?? ''
            ];
        } catch (\Exception $e) {
            error_log("Cloudinary Upload Error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Delete a file from Cloudinary
     * 
     * @param string $publicId The public ID of the resource
     * @return bool Success status
     */
    public function deleteFile($publicId)
    {
        try {
            // Determine resource type if possible, or try deleting as image then raw
            // For simplicity, we might need to know the type. Assuming mostly raw for PDFs or image for covers.
            // But 'upload' API 'destroy' method usually handles it.
            
            $uploadApi = new UploadApi();
            $result = $uploadApi->destroy($publicId, [
                'invalidate' => true
            ]);

            return $result['result'] === 'ok';
        } catch (\Exception $e) {
            error_log("Cloudinary Delete Error: " . $e->getMessage());
            return false;
        }
    }
}

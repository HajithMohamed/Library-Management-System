<?php

namespace App\Controllers;

use App\Models\EResource;
use App\Models\User;
use App\Services\CloudinaryService;

class EResourceController extends BaseController
{
    private $eResourceModel;
    private $cloudinaryService;

    public function __construct()
    {
        parent::__construct();
        $this->eResourceModel = new EResource();
        $this->cloudinaryService = new CloudinaryService();
    }

    /**
     * Display e-resources page
     */
    public function index()
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }

        $userType = $_SESSION['user_type'] ?? 'student';
        $userId = $_SESSION['user_id'];

        $resources = [];

        if ($userType === 'admin') {
            // Admin sees all resources
            $resources = $this->eResourceModel->getAll();
        } elseif ($userType === 'faculty') {
            // Faculty sees approved resources AND their own uploads (pending or otherwise)
            // Ideally we merge them or show in separate tabs. For simplicity, let's show all APPROVED + Own Pending/Rejected
            $allApproved = $this->eResourceModel->getAll('approved');
            $myUploads = $this->eResourceModel->getByUser($userId);

            // Merge uniquely by ID
            $temp = [];
            foreach ($allApproved as $r)
                $temp[$r['resourceId']] = $r;
            foreach ($myUploads as $r)
                $temp[$r['resourceId']] = $r;

            $resources = array_values($temp);

            // Sort by date desc
            usort($resources, function ($a, $b) {
                return strtotime($b['createdAt']) - strtotime($a['createdAt']);
            });

        } else {
            // Students see only approved resources
            $resources = $this->eResourceModel->getAll('approved');
        }

        $this->view('eresources/index', [
            'resources' => $resources,
            'userType' => $userType,
            'title' => 'E-Resources'
        ]);
    }

    /**
     * Show upload form
     */
    public function showUpload()
    {
        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] === 'student') {
            $this->redirect('/e-resources');
            return;
        }

        $this->view('eresources/upload', [
            'title' => 'Upload E-Resource'
        ]);
    }

    /**
     * Handle file upload
     */
    public function upload()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/e-resources/upload');
            return;
        }

        if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] === 'student') {
            $_SESSION['error'] = "Unauthorized access.";
            $this->redirect('/e-resources');
            return;
        }

        $title = $_POST['title'] ?? '';
        $description = $_POST['description'] ?? '';

        if (empty($title) || !isset($_FILES['resourceFile'])) {
            $_SESSION['error'] = "Title and file are required.";
            $this->redirect('/e-resources/upload');
            return;
        }

        $file = $_FILES['resourceFile'];

        // Upload to Cloudinary
        $uploadResult = $this->cloudinaryService->uploadFile($file['tmp_name'], 'library_resources');

        if ($uploadResult['success']) {
            $dbData = [
                'title' => $title,
                'description' => $description,
                'fileUrl' => $uploadResult['url'],
                'publicId' => $uploadResult['public_id'],
                'uploadedBy' => $_SESSION['user_id'],
                'status' => ($_SESSION['user_type'] === 'admin') ? 'approved' : 'pending' // Auto-approve admin uploads
            ];

            if ($this->eResourceModel->create($dbData)) {
                $_SESSION['success'] = "Resource uploaded successfully." . (($_SESSION['user_type'] !== 'admin') ? " It is pending approval." : "");
                $this->redirect('/e-resources');
            } else {
                // If DB fails, try to delete from cloudinary to clean up
                $this->cloudinaryService->deleteFile($uploadResult['public_id']);
                $_SESSION['error'] = "Database error occurred.";
                $this->redirect('/e-resources/upload');
            }
        } else {
            $_SESSION['error'] = "File upload failed: " . $uploadResult['message'];
            $this->redirect('/e-resources/upload');
        }
    }

    /**
     * Approve resource (Admin only)
     */
    public function approve($id)
    {
        if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
            $_SESSION['error'] = "Unauthorized.";
            $this->redirect('/e-resources');
            return;
        }

        if ($this->eResourceModel->updateStatus($id, 'approved')) {
            $_SESSION['success'] = "Resource approved.";
        } else {
            $_SESSION['error'] = "Failed to approve resource.";
        }
        $this->redirect('/e-resources');
    }

    /**
     * Reject resource (Admin only)
     */
    public function reject($id)
    {
        if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
            $_SESSION['error'] = "Unauthorized.";
            $this->redirect('/e-resources');
            return;
        }

        if ($this->eResourceModel->updateStatus($id, 'rejected')) {
            $_SESSION['success'] = "Resource rejected.";
        } else {
            $_SESSION['error'] = "Failed to reject resource.";
        }
        $this->redirect('/e-resources');
    }

    /**
     * Delete resource
     */
    public function delete($id)
    {
        if (!isset($_SESSION['user_id'])) {
            $this->redirect('/login');
            return;
        }

        $resource = $this->eResourceModel->getById($id);
        if (!$resource) {
            $_SESSION['error'] = "Resource not found.";
            $this->redirect('/e-resources');
            return;
        }

        // Only Admin or the Uploader can delete
        if ($_SESSION['user_type'] !== 'admin' && $_SESSION['user_id'] !== $resource['uploadedBy']) {
            $_SESSION['error'] = "Unauthorized.";
            $this->redirect('/e-resources');
            return;
        }

        // Delete from Cloudinary
        $this->cloudinaryService->deleteFile($resource['publicId']);

        // Delete from DB
        if ($this->eResourceModel->delete($id)) {
            $_SESSION['success'] = "Resource deleted.";
        } else {
            $_SESSION['error'] = "Failed to delete resource.";
        }
        $this->redirect('/e-resources');
    }
}

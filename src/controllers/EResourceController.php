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

        // Use standardized role from AuthController (always lowercase: 'admin', 'student', 'faculty'/'teacher')
        $userRole = $_SESSION['role'] ?? 'student';

        // Handle potential legacy session if 'role' is not set but 'userType' is
        if (!isset($_SESSION['role']) && isset($_SESSION['userType'])) {
            $userRole = strtolower($_SESSION['userType']);
        }

        $userId = $_SESSION['user_id'] ?? $_SESSION['userId'];

        $resources = [];

        if ($userRole === 'admin') {
            // Admin sees all resources
            $resources = $this->eResourceModel->getAll();
        } elseif ($userRole === 'faculty' || $userRole === 'teacher') {
            // Faculty sees approved resources AND their own uploads
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

        if ($userRole === 'admin') {
            $this->view('admin/eresources', [
                'resources' => $resources,
                'userType' => $userRole,
                'title' => 'E-Resources Management'
            ]);
        } else {
            $this->view('eresources/index', [
                'resources' => $resources,
                'userType' => $userRole,
                'title' => 'E-Resources'
            ]);
        }
    }

    /**
     * Show upload form
     */
    public function showUpload()
    {
        $userRole = $_SESSION['role'] ?? strtolower($_SESSION['userType'] ?? 'student');

        if (!isset($_SESSION['user_id']) && !isset($_SESSION['userId'])) {
            $this->redirect('/login');
            return;
        }

        if ($userRole === 'student') {
            $this->redirect('/e-resources');
            return;
        }

        if ($userRole === 'admin') {
            $this->view('admin/eresources_upload', [
                'title' => 'Upload E-Resource'
            ]);
        } else {
            $this->view('eresources/upload', [
                'title' => 'Upload E-Resource'
            ]);
        }
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

        $userRole = $_SESSION['role'] ?? strtolower($_SESSION['userType'] ?? 'student');
        $userId = $_SESSION['user_id'] ?? $_SESSION['userId'];

        if (!isset($userId) || $userRole === 'student') {
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
                'status' => ($userRole === 'admin') ? 'approved' : 'pending' // Auto-approve admin uploads
            ];

            if ($this->eResourceModel->create($dbData)) {
                $_SESSION['success'] = "Resource uploaded successfully." . (($userRole !== 'admin') ? " It is pending approval." : "");
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
        $userRole = $_SESSION['role'] ?? strtolower($_SESSION['userType'] ?? 'student');

        if ($userRole !== 'admin') {
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
        $userRole = $_SESSION['role'] ?? strtolower($_SESSION['userType'] ?? 'student');

        if ($userRole !== 'admin') {
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

        $userRole = $_SESSION['role'] ?? strtolower($_SESSION['userType'] ?? 'student');
        $currentUserId = $_SESSION['user_id'] ?? $_SESSION['userId'];

        // Only Admin or the Uploader can delete
        if ($userRole !== 'admin' && $currentUserId !== $resource['uploadedBy']) {
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
    /**
     * Add resource to user's library
     */
    public function obtain($id)
    {
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['userId'])) {
            $this->redirect('/login');
            return;
        }

        $userId = $_SESSION['user_id'] ?? $_SESSION['userId'];

        if ($this->eResourceModel->saveToLibrary($userId, $id)) {
            $_SESSION['success'] = "Resource added to your library.";
        } else {
            $_SESSION['error'] = "Failed to add resource.";
        }

        // Redirect back to browse page
        $this->redirect('/e-resources');
    }

    /**
     * Display user's saved resources (My Library)
     */
    public function myResources()
    {
        if (!isset($_SESSION['user_id']) && !isset($_SESSION['userId'])) {
            $this->redirect('/login');
            return;
        }

        $userRole = $_SESSION['role'] ?? strtolower($_SESSION['userType'] ?? 'student');
        $userId = $_SESSION['user_id'] ?? $_SESSION['userId'];

        $resources = $this->eResourceModel->getSavedResources($userId);

        if ($userRole === 'admin') {
            $this->redirect('/admin/eresources'); // Admins don't have a "personal" library view in this context usually
            return;
        }

        $viewPath = ($userRole === 'faculty') ? 'faculty/eresources' : 'users/eresources';

        $this->view($viewPath, [
            'resources' => $resources,
            'title' => 'My E-Resources Library'
        ]);
    }
}

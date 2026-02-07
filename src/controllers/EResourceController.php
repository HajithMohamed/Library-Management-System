<?php

namespace App\Controllers;

use App\Models\EResource;

class EResourceController extends BaseController
{
    private $eResourceModel;

    public function __construct()
    {
        parent::__construct();
        $this->eResourceModel = new EResource();
    }

    // ========================================================================
    // HELPER: Get consistent user role and ID from session
    // ========================================================================

    private function getUserRole()
    {
        $role = $_SESSION['userType'] ?? 'Student';
        return strtolower($role);
    }

    private function getUserId()
    {
        return $_SESSION['userId'] ?? null;
    }

    private function isAdmin()
    {
        return $this->getUserRole() === 'admin';
    }

    private function isFaculty()
    {
        $role = $this->getUserRole();
        return ($role === 'faculty' || $role === 'teacher');
    }

    // ========================================================================
    // PUBLIC ROUTES (all authenticated users)
    // ========================================================================

    /**
     * GET /eresources - Browse approved e-resources
     */
    public function browse()
    {
        if (!$this->getUserId()) {
            $this->redirect('/login');
            return;
        }

        $category = $_GET['category'] ?? '';
        $search = $_GET['search'] ?? '';

        if (!empty($search)) {
            $resources = $this->eResourceModel->search($search, 'approved');
        } else {
            $resources = $this->eResourceModel->getApproved();
        }

        // Filter by category if specified
        if (!empty($category)) {
            $resources = array_filter($resources, function ($r) use ($category) {
                return $r['category'] === $category;
            });
            $resources = array_values($resources);
        }

        // Get unique categories for filter dropdown
        $allResources = $this->eResourceModel->getApproved();
        $categories = array_unique(array_filter(array_column($allResources, 'category')));
        sort($categories);

        $this->view('eresources/browse', [
            'resources' => $resources,
            'categories' => $categories,
            'currentCategory' => $category,
            'searchQuery' => $search,
            'userType' => $this->getUserRole(),
            'title' => 'E-Resources'
        ]);
    }

    /**
     * GET /eresources/view/{id} - View resource details
     */
    public function viewResource($params)
    {
        if (!$this->getUserId()) {
            $this->redirect('/login');
            return;
        }

        $id = is_array($params) ? ($params['id'] ?? null) : $params;
        if (!$id) {
            $_SESSION['error'] = "Resource not found.";
            $this->redirect('/eresources');
            return;
        }

        $resource = $this->eResourceModel->getById($id);

        if (!$resource) {
            $_SESSION['error'] = "Resource not found.";
            $this->redirect('/eresources');
            return;
        }

        // Only show approved resources to non-admins (unless it's their own)
        if ($resource['status'] !== 'approved' && !$this->isAdmin()) {
            if ($resource['submitted_by'] !== $this->getUserId()) {
                $_SESSION['error'] = "Resource not available.";
                $this->redirect('/eresources');
                return;
            }
        }

        $isSaved = $this->eResourceModel->isSaved($this->getUserId(), $id);

        $this->view('eresources/view', [
            'resource' => $resource,
            'isSaved' => $isSaved,
            'userType' => $this->getUserRole(),
            'title' => $resource['title']
        ]);
    }

    /**
     * GET /eresources/download/{id} - Download/access resource
     */
    public function download($params)
    {
        if (!$this->getUserId()) {
            $this->redirect('/login');
            return;
        }

        $id = is_array($params) ? ($params['id'] ?? null) : $params;
        $resource = $this->eResourceModel->getById($id);

        if (!$resource || $resource['status'] !== 'approved') {
            $_SESSION['error'] = "Resource not available.";
            $this->redirect('/eresources');
            return;
        }

        // Increment download count
        $this->eResourceModel->incrementDownload($id);

        if ($resource['resource_type'] === 'link' || $resource['resource_type'] === 'video') {
            // Redirect to external URL
            header('Location: ' . $resource['resource_url']);
            exit;
        }

        // Serve local PDF file
        if (!empty($resource['file_path'])) {
            $filePath = APP_ROOT . '/public/' . $resource['file_path'];
            if (file_exists($filePath)) {
                header('Content-Type: application/pdf');
                header('Content-Disposition: attachment; filename="' . basename($resource['file_path']) . '"');
                header('Content-Length: ' . filesize($filePath));
                header('Cache-Control: no-cache, must-revalidate');
                readfile($filePath);
                exit;
            }
        }

        // Fallback to resource_url if file_path doesn't work
        if (!empty($resource['resource_url'])) {
            header('Location: ' . $resource['resource_url']);
            exit;
        }

        $_SESSION['error'] = "File not found.";
        $this->redirect('/eresources');
    }

    /**
     * GET /eresources/save/{id} - Save resource to personal library
     */
    public function save($params)
    {
        if (!$this->getUserId()) {
            $this->redirect('/login');
            return;
        }

        $id = is_array($params) ? ($params['id'] ?? null) : $params;

        if (!$id) {
            $_SESSION['error'] = "Invalid resource.";
            $this->redirect('/eresources');
            return;
        }

        $userId = $this->getUserId();
        error_log("Save attempt: userId='$userId', resourceId='$id'");

        if ($this->eResourceModel->saveToLibrary($userId, $id)) {
            $_SESSION['success'] = "Resource saved to your library!";
        } else {
            $_SESSION['error'] = "Failed to save resource. Please try again.";
        }

        $this->redirect('/eresources');
    }

    // ========================================================================
    // FACULTY ROUTES
    // ========================================================================

    /**
     * GET /faculty/eresources/submit - Show submission form
     */
    public function submitForm()
    {
        if (!$this->isFaculty() && !$this->isAdmin()) {
            $_SESSION['error'] = "Unauthorized access.";
            $this->redirect('/eresources');
            return;
        }

        $this->view('faculty/eresources/submit', [
            'title' => 'Submit E-Resource',
            'userType' => $this->getUserRole()
        ]);
    }

    /**
     * POST /faculty/eresources/submit - Handle faculty submission
     */
    public function submitResource()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/faculty/eresources/submit');
            return;
        }

        if (!$this->isFaculty() && !$this->isAdmin()) {
            $_SESSION['error'] = "Unauthorized access.";
            $this->redirect('/eresources');
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $resourceType = $_POST['resource_type'] ?? 'pdf';
        $category = trim($_POST['category'] ?? '');
        $resourceUrl = trim($_POST['resource_url'] ?? '');

        // Validation
        if (empty($title)) {
            $_SESSION['error'] = "Title is required.";
            $this->redirect('/faculty/eresources/submit');
            return;
        }

        $filePath = null;

        // Handle file upload for PDF type
        if ($resourceType === 'pdf') {
            if (!isset($_FILES['resource_file']) || $_FILES['resource_file']['error'] === UPLOAD_ERR_NO_FILE) {
                if (empty($resourceUrl)) {
                    $_SESSION['error'] = "Please upload a PDF file or provide a URL.";
                    $this->redirect('/faculty/eresources/submit');
                    return;
                }
            } else {
                $uploadResult = $this->handleFileUpload($_FILES['resource_file']);
                if ($uploadResult['success']) {
                    $filePath = $uploadResult['path'];
                } else {
                    $_SESSION['error'] = $uploadResult['message'];
                    $this->redirect('/faculty/eresources/submit');
                    return;
                }
            }
        } elseif (empty($resourceUrl)) {
            $_SESSION['error'] = "URL is required for link/video resources.";
            $this->redirect('/faculty/eresources/submit');
            return;
        }

        $dbData = [
            'title' => $this->sanitize($title),
            'description' => $this->sanitize($description),
            'resource_type' => $resourceType,
            'resource_url' => $resourceUrl ?: null,
            'file_path' => $filePath,
            'category' => $this->sanitize($category),
            'submitted_by' => $this->getUserId(),
            'status' => 'pending'  // Faculty submissions always pending
        ];

        if ($this->eResourceModel->create($dbData)) {
            // Notify admins
            $this->notifyAdminsNewSubmission($title);

            $_SESSION['success'] = "Resource submitted successfully! It will be reviewed by an administrator.";
            $this->redirect('/faculty/eresources/my-submissions');
        } else {
            // Clean up uploaded file on DB failure
            if ($filePath) {
                $fullPath = APP_ROOT . '/public/' . $filePath;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
            $_SESSION['error'] = "Failed to submit resource. Please try again.";
            $this->redirect('/faculty/eresources/submit');
        }
    }

    /**
     * GET /faculty/eresources/my-submissions - View faculty's own submissions
     */
    public function mySubmissions()
    {
        if (!$this->isFaculty() && !$this->isAdmin()) {
            $_SESSION['error'] = "Unauthorized access.";
            $this->redirect('/eresources');
            return;
        }

        $resources = $this->eResourceModel->getBySubmitter($this->getUserId());

        $this->view('faculty/eresources/my-submissions', [
            'resources' => $resources,
            'title' => 'My Submissions',
            'userType' => $this->getUserRole()
        ]);
    }

    /**
     * GET /faculty/eresources/edit/{id} - Show edit form for pending submission
     */
    public function editSubmission($params)
    {
        if (!$this->isFaculty()) {
            $_SESSION['error'] = "Unauthorized access.";
            $this->redirect('/eresources');
            return;
        }

        $id = is_array($params) ? ($params['id'] ?? null) : $params;
        $resource = $this->eResourceModel->getById($id);

        if (!$resource || $resource['submitted_by'] !== $this->getUserId()) {
            $_SESSION['error'] = "Resource not found or you don't have permission.";
            $this->redirect('/faculty/eresources/my-submissions');
            return;
        }

        if ($resource['status'] !== 'pending') {
            $_SESSION['error'] = "Only pending submissions can be edited.";
            $this->redirect('/faculty/eresources/my-submissions');
            return;
        }

        $this->view('faculty/eresources/submit', [
            'resource' => $resource,
            'isEdit' => true,
            'title' => 'Edit Submission',
            'userType' => $this->getUserRole()
        ]);
    }

    /**
     * POST /faculty/eresources/edit/{id} - Handle edit of pending submission
     */
    public function updateSubmission($params)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/faculty/eresources/my-submissions');
            return;
        }

        if (!$this->isFaculty()) {
            $_SESSION['error'] = "Unauthorized access.";
            $this->redirect('/eresources');
            return;
        }

        $id = is_array($params) ? ($params['id'] ?? null) : $params;
        $resource = $this->eResourceModel->getById($id);

        if (!$resource || $resource['submitted_by'] !== $this->getUserId() || $resource['status'] !== 'pending') {
            $_SESSION['error'] = "Cannot edit this resource.";
            $this->redirect('/faculty/eresources/my-submissions');
            return;
        }

        $updateData = [
            'title' => $this->sanitize(trim($_POST['title'] ?? $resource['title'])),
            'description' => $this->sanitize(trim($_POST['description'] ?? '')),
            'resource_type' => $_POST['resource_type'] ?? $resource['resource_type'],
            'category' => $this->sanitize(trim($_POST['category'] ?? '')),
            'resource_url' => trim($_POST['resource_url'] ?? $resource['resource_url'])
        ];

        // Handle new file upload
        if (isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = $this->handleFileUpload($_FILES['resource_file']);
            if ($uploadResult['success']) {
                // Delete old file
                if (!empty($resource['file_path'])) {
                    $oldPath = APP_ROOT . '/public/' . $resource['file_path'];
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
                $updateData['file_path'] = $uploadResult['path'];
            } else {
                $_SESSION['error'] = $uploadResult['message'];
                $this->redirect('/faculty/eresources/edit/' . $id);
                return;
            }
        }

        if ($this->eResourceModel->update($id, $updateData)) {
            $_SESSION['success'] = "Submission updated successfully.";
        } else {
            $_SESSION['error'] = "Failed to update submission.";
        }

        $this->redirect('/faculty/eresources/my-submissions');
    }

    // ========================================================================
    // ADMIN ROUTES
    // ========================================================================

    /**
     * GET /admin/eresources/manage - Manage all e-resources
     */
    public function adminManage()
    {
        if (!$this->isAdmin()) {
            $_SESSION['error'] = "Unauthorized.";
            $this->redirect('/eresources');
            return;
        }

        $statusFilter = $_GET['status'] ?? '';
        
        if (!empty($statusFilter)) {
            $resources = $this->eResourceModel->getAll($statusFilter);
        } else {
            $resources = $this->eResourceModel->getAll();
        }

        $stats = $this->eResourceModel->getStats();

        $this->view('admin/eresources/manage', [
            'resources' => $resources,
            'stats' => $stats,
            'currentFilter' => $statusFilter,
            'title' => 'E-Resources Management'
        ]);
    }

    /**
     * GET /admin/eresources/add - Show add resource form
     */
    public function adminAddForm()
    {
        if (!$this->isAdmin()) {
            $_SESSION['error'] = "Unauthorized.";
            $this->redirect('/eresources');
            return;
        }

        $this->view('admin/eresources/add', [
            'title' => 'Add E-Resource'
        ]);
    }

    /**
     * POST /admin/eresources/add - Create resource (auto-approved)
     */
    public function adminAdd()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/eresources/add');
            return;
        }

        if (!$this->isAdmin()) {
            $_SESSION['error'] = "Unauthorized.";
            $this->redirect('/eresources');
            return;
        }

        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $resourceType = $_POST['resource_type'] ?? 'pdf';
        $category = trim($_POST['category'] ?? '');
        $resourceUrl = trim($_POST['resource_url'] ?? '');

        if (empty($title)) {
            $_SESSION['error'] = "Title is required.";
            $this->redirect('/admin/eresources/add');
            return;
        }

        $filePath = null;

        if ($resourceType === 'pdf') {
            if (isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] !== UPLOAD_ERR_NO_FILE) {
                $uploadResult = $this->handleFileUpload($_FILES['resource_file']);
                if ($uploadResult['success']) {
                    $filePath = $uploadResult['path'];
                } else {
                    $_SESSION['error'] = $uploadResult['message'];
                    $this->redirect('/admin/eresources/add');
                    return;
                }
            } elseif (empty($resourceUrl)) {
                $_SESSION['error'] = "Please upload a PDF file or provide a URL.";
                $this->redirect('/admin/eresources/add');
                return;
            }
        } elseif (empty($resourceUrl)) {
            $_SESSION['error'] = "URL is required for link/video resources.";
            $this->redirect('/admin/eresources/add');
            return;
        }

        $dbData = [
            'title' => $this->sanitize($title),
            'description' => $this->sanitize($description),
            'resource_type' => $resourceType,
            'resource_url' => $resourceUrl ?: null,
            'file_path' => $filePath,
            'category' => $this->sanitize($category),
            'submitted_by' => $this->getUserId(),
            'status' => 'approved'  // Admin uploads auto-approved
        ];

        if ($this->eResourceModel->create($dbData)) {
            // Also set approval fields
            $lastId = $this->db->insert_id;
            if ($lastId) {
                $this->eResourceModel->approve($lastId, $this->getUserId());
            }
            $_SESSION['success'] = "Resource added successfully.";
            $this->redirect('/admin/eresources/manage');
        } else {
            if ($filePath) {
                $fullPath = APP_ROOT . '/public/' . $filePath;
                if (file_exists($fullPath)) {
                    unlink($fullPath);
                }
            }
            $_SESSION['error'] = "Failed to add resource.";
            $this->redirect('/admin/eresources/add');
        }
    }

    /**
     * GET /admin/eresources/edit/{id} - Show edit form for any resource
     */
    public function adminEditForm($params)
    {
        if (!$this->isAdmin()) {
            $_SESSION['error'] = "Unauthorized.";
            $this->redirect('/eresources');
            return;
        }

        $id = is_array($params) ? ($params['id'] ?? null) : $params;
        $resource = $this->eResourceModel->getById($id);

        if (!$resource) {
            $_SESSION['error'] = "Resource not found.";
            $this->redirect('/admin/eresources/manage');
            return;
        }

        $this->view('admin/eresources/add', [
            'resource' => $resource,
            'isEdit' => true,
            'title' => 'Edit E-Resource'
        ]);
    }

    /**
     * POST /admin/eresources/edit/{id} - Update any resource
     */
    public function adminEdit($params)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/admin/eresources/manage');
            return;
        }

        if (!$this->isAdmin()) {
            $_SESSION['error'] = "Unauthorized.";
            $this->redirect('/eresources');
            return;
        }

        $id = is_array($params) ? ($params['id'] ?? null) : $params;
        $resource = $this->eResourceModel->getById($id);

        if (!$resource) {
            $_SESSION['error'] = "Resource not found.";
            $this->redirect('/admin/eresources/manage');
            return;
        }

        $updateData = [
            'title' => $this->sanitize(trim($_POST['title'] ?? $resource['title'])),
            'description' => $this->sanitize(trim($_POST['description'] ?? '')),
            'resource_type' => $_POST['resource_type'] ?? $resource['resource_type'],
            'category' => $this->sanitize(trim($_POST['category'] ?? '')),
            'resource_url' => trim($_POST['resource_url'] ?? $resource['resource_url'])
        ];

        // Handle new file upload
        if (isset($_FILES['resource_file']) && $_FILES['resource_file']['error'] !== UPLOAD_ERR_NO_FILE) {
            $uploadResult = $this->handleFileUpload($_FILES['resource_file']);
            if ($uploadResult['success']) {
                // Delete old file
                if (!empty($resource['file_path'])) {
                    $oldPath = APP_ROOT . '/public/' . $resource['file_path'];
                    if (file_exists($oldPath)) {
                        unlink($oldPath);
                    }
                }
                $updateData['file_path'] = $uploadResult['path'];
            } else {
                $_SESSION['error'] = $uploadResult['message'];
                $this->redirect('/admin/eresources/edit/' . $id);
                return;
            }
        }

        if ($this->eResourceModel->update($id, $updateData)) {
            $_SESSION['success'] = "Resource updated successfully.";
        } else {
            $_SESSION['error'] = "Failed to update resource.";
        }

        $this->redirect('/admin/eresources/manage');
    }

    /**
     * GET /admin/eresources/approvals - Show pending approval queue
     */
    public function approvals()
    {
        if (!$this->isAdmin()) {
            $_SESSION['error'] = "Unauthorized.";
            $this->redirect('/eresources');
            return;
        }

        $pendingResources = $this->eResourceModel->getPending();

        $this->view('admin/eresources/approvals', [
            'resources' => $pendingResources,
            'title' => 'Pending Approvals'
        ]);
    }

    /**
     * POST /admin/eresources/approve/{id} - Approve a resource
     */
    public function approve($params)
    {
        if (!$this->isAdmin()) {
            $_SESSION['error'] = "Unauthorized.";
            $this->redirect('/eresources');
            return;
        }

        $id = is_array($params) ? ($params['id'] ?? null) : $params;
        $resource = $this->eResourceModel->getById($id);

        if (!$resource) {
            $_SESSION['error'] = "Resource not found.";
            $this->redirect('/admin/eresources/approvals');
            return;
        }

        if ($this->eResourceModel->approve($id, $this->getUserId())) {
            // Notify the faculty member
            $this->notifySubmitter($resource['submitted_by'], $resource['title'], 'approved');
            $_SESSION['success'] = "Resource '{$resource['title']}' approved.";
        } else {
            $_SESSION['error'] = "Failed to approve resource.";
        }

        $this->redirect('/admin/eresources/approvals');
    }

    /**
     * POST /admin/eresources/reject/{id} - Reject a resource
     */
    public function rejectResource($params)
    {
        if (!$this->isAdmin()) {
            $_SESSION['error'] = "Unauthorized.";
            $this->redirect('/eresources');
            return;
        }

        $id = is_array($params) ? ($params['id'] ?? null) : $params;
        $reason = trim($_POST['rejection_reason'] ?? '');

        $resource = $this->eResourceModel->getById($id);
        if (!$resource) {
            $_SESSION['error'] = "Resource not found.";
            $this->redirect('/admin/eresources/approvals');
            return;
        }

        if ($this->eResourceModel->reject($id, $this->getUserId(), $reason)) {
            // Notify the faculty member
            $this->notifySubmitter($resource['submitted_by'], $resource['title'], 'rejected', $reason);
            $_SESSION['success'] = "Resource '{$resource['title']}' rejected.";
        } else {
            $_SESSION['error'] = "Failed to reject resource.";
        }

        $this->redirect('/admin/eresources/approvals');
    }

    /**
     * POST /admin/eresources/delete/{id} - Delete a resource
     */
    public function deleteResource($params)
    {
        if (!$this->isAdmin()) {
            $_SESSION['error'] = "Unauthorized.";
            $this->redirect('/eresources');
            return;
        }

        $id = is_array($params) ? ($params['id'] ?? null) : $params;

        if ($this->eResourceModel->deleteResource($id)) {
            $_SESSION['success'] = "Resource deleted.";
        } else {
            $_SESSION['error'] = "Failed to delete resource.";
        }

        $this->redirect('/admin/eresources/manage');
    }

    // ========================================================================
    // LEGACY ROUTES (backward compatibility with old /e-resources/* routes)
    // ========================================================================

    /**
     * Legacy: GET /e-resources - Redirect to new route
     */
    public function legacyIndex()
    {
        $this->redirect('/eresources');
    }

    /**
     * Legacy: GET /my-e-resources - User's saved library
     */
    public function myResources()
    {
        if (!$this->getUserId()) {
            $this->redirect('/login');
            return;
        }

        $resources = $this->eResourceModel->getSavedResources($this->getUserId());
        $role = $this->getUserRole();

        if ($role === 'admin') {
            $this->redirect('/admin/eresources/manage');
            return;
        }

        $viewPath = ($this->isFaculty()) ? 'faculty/eresources/library' : 'eresources/library';

        $this->view($viewPath, [
            'resources' => $resources,
            'title' => 'My E-Resources Library',
            'userType' => $role
        ]);
    }

    // ========================================================================
    // FILE UPLOAD HANDLING
    // ========================================================================

    /**
     * Handle PDF file upload
     * @param array $file $_FILES array element
     * @return array ['success' => bool, 'path' => string, 'message' => string]
     */
    private function handleFileUpload($file)
    {
        // Check for upload errors
        if ($file['error'] !== UPLOAD_ERR_OK) {
            $errors = [
                UPLOAD_ERR_INI_SIZE => 'File exceeds server maximum upload size.',
                UPLOAD_ERR_FORM_SIZE => 'File exceeds form maximum upload size.',
                UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
                UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
                UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
                UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            ];
            return ['success' => false, 'message' => $errors[$file['error']] ?? 'Unknown upload error.'];
        }

        // Validate file type - PDF only
        $allowedMimes = ['application/pdf'];
        $finfo = new \finfo(FILEINFO_MIME_TYPE);
        $mimeType = $finfo->file($file['tmp_name']);

        if (!in_array($mimeType, $allowedMimes)) {
            return ['success' => false, 'message' => 'Only PDF files are allowed. Detected: ' . $mimeType];
        }

        // Validate file extension
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        if ($extension !== 'pdf') {
            return ['success' => false, 'message' => 'Only .pdf file extension is allowed.'];
        }

        // Validate file size (max 20MB)
        $maxSize = 20 * 1024 * 1024; // 20MB in bytes
        if ($file['size'] > $maxSize) {
            return ['success' => false, 'message' => 'File size exceeds maximum limit of 20MB.'];
        }

        // Create upload directory if not exists
        $uploadDir = APP_ROOT . '/public/assets/uploads/eresources';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }

        // Generate unique filename
        $filename = time() . '_' . bin2hex(random_bytes(8)) . '.pdf';
        $destination = $uploadDir . '/' . $filename;

        if (move_uploaded_file($file['tmp_name'], $destination)) {
            // Return relative path from public directory
            return [
                'success' => true,
                'path' => 'assets/uploads/eresources/' . $filename,
                'message' => 'File uploaded successfully.'
            ];
        }

        return ['success' => false, 'message' => 'Failed to move uploaded file.'];
    }

    // ========================================================================
    // NOTIFICATIONS
    // ========================================================================

    /**
     * Notify all admins about a new faculty submission
     */
    private function notifyAdminsNewSubmission($resourceTitle)
    {
        try {
            $admins = $this->eResourceModel->getAdminUsers();
            $submitterName = $_SESSION['username'] ?? 'A faculty member';

            foreach ($admins as $admin) {
                $this->createNotification(
                    $admin['userId'],
                    'New E-Resource Submission',
                    "{$submitterName} submitted a new e-resource: \"{$resourceTitle}\". Please review it.",
                    'info'
                );
            }
        } catch (\Exception $e) {
            error_log("Error notifying admins: " . $e->getMessage());
        }
    }

    /**
     * Notify faculty member about approval/rejection
     */
    private function notifySubmitter($submitterId, $resourceTitle, $action, $reason = '')
    {
        try {
            if ($action === 'approved') {
                $title = 'E-Resource Approved';
                $message = "Your e-resource \"{$resourceTitle}\" has been approved and is now available to all users.";
            } else {
                $title = 'E-Resource Rejected';
                $message = "Your e-resource \"{$resourceTitle}\" has been rejected.";
                if ($reason) {
                    $message .= " Reason: {$reason}";
                }
            }

            $this->createNotification($submitterId, $title, $message, $action === 'approved' ? 'success' : 'warning');
        } catch (\Exception $e) {
            error_log("Error notifying submitter: " . $e->getMessage());
        }
    }

    /**
     * Create a notification in the database (uses mysqli directly since NotificationService uses PDO)
     */
    private function createNotification($userId, $title, $message, $type = 'info')
    {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO notifications (userId, title, message, type, isRead, createdAt) 
                VALUES (?, ?, ?, ?, 0, NOW())
            ");
            if ($stmt) {
                $stmt->bind_param("ssss", $userId, $title, $message, $type);
                $stmt->execute();
            }
        } catch (\Exception $e) {
            error_log("Notification creation error: " . $e->getMessage());
        }
    }
}

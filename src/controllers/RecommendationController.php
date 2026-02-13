<?php
namespace App\Controllers;

use App\Models\BookRecommendation;
use App\Helpers\SessionHelper;
use App\Helpers\ValidationHelper;
use App\Helpers\AuthHelper;
use App\Services\NotificationService;

class RecommendationController {
    public function recommendBookForm() {
        // Render faculty recommendation form view
        include __DIR__ . '/../views/faculty/recommend-book.php';
    }

    public function submitRecommendation() {
        $data = $_POST;
        $data['recommended_by'] = AuthHelper::getUserId();
        $model = new BookRecommendation();
        $model->create($data);
        NotificationService::notifyAdmin('New book recommendation submitted.');
        header('Location: /faculty/my-recommendations');
    }

    public function viewMyRecommendations() {
        $userId = AuthHelper::getUserId();
        $model = new BookRecommendation();
        $recommendations = $model->getByFaculty($userId);
        include __DIR__ . '/../views/faculty/my-recommendations.php';
    }

    public function viewAllRecommendations() {
        $model = new BookRecommendation();
        $recommendations = $model->getAll();
        include __DIR__ . '/../views/admin/book-recommendations.php';
    }

    public function approveRecommendation($id) {
        $adminId = AuthHelper::getUserId();
        $model = new BookRecommendation();
        $model->updateStatus($id, 'approved', $adminId);
        NotificationService::notifyFaculty($id, 'Your recommendation was approved.');
        header('Location: /admin/book-recommendations');
    }

    public function rejectRecommendation($id) {
        $adminId = AuthHelper::getUserId();
        $reason = $_POST['rejection_reason'] ?? '';
        $model = new BookRecommendation();
        $model->updateStatus($id, 'rejected', $adminId, null, $reason);
        NotificationService::notifyFaculty($id, 'Your recommendation was rejected. Reason: ' . $reason);
        header('Location: /admin/book-recommendations');
    }

    public function markOrdered($id) {
        $adminId = AuthHelper::getUserId();
        $model = new BookRecommendation();
        $model->updateStatus($id, 'ordered', $adminId);
        header('Location: /admin/book-recommendations');
    }

    public function markReceived($id) {
        $adminId = AuthHelper::getUserId();
        $model = new BookRecommendation();
        $model->updateStatus($id, 'received', $adminId);
        header('Location: /admin/book-recommendations');
    }
}

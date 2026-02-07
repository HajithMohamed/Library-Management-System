<?php
/**
 * E-Resource Detail View
 * Route: GET /eresources/view/{id}
 */
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

$userRole = $userType ?? 'student';
$pageTitle = htmlspecialchars($resource['title'] ?? 'E-Resource');
include APP_ROOT . '/views/layouts/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    body { margin: 0; padding: 0; overflow-x: hidden; }

    .view-container {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        padding: 40px 20px 60px;
        position: relative;
    }

    .content-card {
        max-width: 900px; margin: 0 auto;
        background: rgba(255,255,255,0.95);
        backdrop-filter: blur(20px);
        border-radius: 30px;
        padding: 50px;
        box-shadow: 0 30px 80px rgba(0,0,0,0.25);
        animation: slideUp 0.6s ease-out;
        position: relative; z-index: 1;
    }

    @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

    .back-link {
        display: inline-flex; align-items: center; gap: 8px;
        color: rgba(255,255,255,0.9); text-decoration: none; font-weight: 600;
        margin-bottom: 25px; font-size: 1rem; transition: all 0.3s;
        position: relative; z-index: 1;
    }
    .back-link:hover { color: white; transform: translateX(-5px); }

    .resource-header { margin-bottom: 30px; border-bottom: 2px solid #f0f0f0; padding-bottom: 25px; }

    .resource-type-badge {
        display: inline-flex; align-items: center; gap: 6px; padding: 6px 16px;
        border-radius: 20px; font-size: 0.85rem; font-weight: 700; margin-bottom: 15px;
    }
    .badge-pdf { background: #fee2e2; color: #dc2626; }
    .badge-link { background: #dbeafe; color: #2563eb; }
    .badge-video { background: #d1fae5; color: #059669; }

    .resource-title-main { font-size: 2.2rem; font-weight: 800; color: #1f2937; margin-bottom: 15px; line-height: 1.3; }

    .meta-grid {
        display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
        gap: 15px; margin-bottom: 10px;
    }
    .meta-item { display: flex; align-items: center; gap: 10px; color: #6b7280; font-size: 0.95rem; }
    .meta-item i { color: #667eea; width: 20px; text-align: center; }

    .status-badge {
        display: inline-flex; align-items: center; gap: 6px; padding: 5px 14px;
        border-radius: 20px; font-size: 0.85rem; font-weight: 600;
    }
    .status-approved { background: #d1fae5; color: #065f46; }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-rejected { background: #fee2e2; color: #991b1b; }

    .description-section { margin-bottom: 30px; }
    .description-section h3 { font-size: 1.2rem; font-weight: 700; color: #374151; margin-bottom: 12px; }
    .description-section p { color: #4b5563; line-height: 1.8; font-size: 1rem; }

    .approval-info {
        background: #f9fafb; border-radius: 16px; padding: 20px; margin-bottom: 30px;
        border: 1px solid #e5e7eb;
    }
    .approval-info h4 { font-size: 1rem; font-weight: 700; color: #374151; margin-bottom: 10px; }
    .approval-info p { color: #6b7280; font-size: 0.95rem; margin: 5px 0; }

    .rejection-reason {
        background: #fef2f2; border: 1px solid #fecaca; border-radius: 12px; padding: 15px; margin-top: 10px;
    }
    .rejection-reason strong { color: #991b1b; }

    .action-buttons { display: flex; gap: 15px; flex-wrap: wrap; margin-top: 30px; padding-top: 25px; border-top: 2px solid #f0f0f0; }

    .btn-action {
        padding: 14px 28px; border-radius: 15px; font-weight: 700; text-decoration: none;
        transition: all 0.3s ease; display: inline-flex; align-items: center; gap: 10px;
        font-size: 1rem; border: none; cursor: pointer;
    }
    .btn-download-main {
        background: linear-gradient(135deg, #667eea, #764ba2); color: white;
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
    }
    .btn-download-main:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(102, 126, 234, 0.4); color: white; text-decoration: none; }
    .btn-save-main {
        background: #f0fdf4; color: #166534; border: 2px solid #bbf7d0;
    }
    .btn-save-main:hover { background: #166534; color: white; }
    .btn-back-main { background: #f3f4f6; color: #374151; }
    .btn-back-main:hover { background: #e5e7eb; }

    /* PDF Preview */
    .pdf-preview {
        margin: 30px 0; border-radius: 16px; overflow: hidden;
        border: 2px solid #e5e7eb; background: #f9fafb;
    }
    .pdf-preview iframe { width: 100%; height: 600px; border: none; }
    .preview-placeholder {
        padding: 60px; text-align: center; color: #9ca3af;
    }
    .preview-placeholder i { font-size: 4rem; margin-bottom: 15px; display: block; }

    @media (max-width: 768px) {
        .content-card { padding: 30px 20px; }
        .resource-title-main { font-size: 1.8rem; }
        .action-buttons { flex-direction: column; }
        .btn-action { width: 100%; justify-content: center; }
    }
</style>

<div class="view-container">
    <div style="max-width: 900px; margin: 0 auto;">
        <a href="<?= BASE_URL ?>eresources" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to E-Resources
        </a>
    </div>

    <div class="content-card">
        <div class="resource-header">
            <span class="resource-type-badge badge-<?= $resource['resource_type'] ?>">
                <?php
                $iconMap = ['pdf' => 'fas fa-file-pdf', 'link' => 'fas fa-link', 'video' => 'fas fa-video'];
                ?>
                <i class="<?= $iconMap[$resource['resource_type']] ?? 'fas fa-file' ?>"></i>
                <?= strtoupper($resource['resource_type']) ?>
            </span>

            <h1 class="resource-title-main"><?= htmlspecialchars($resource['title']) ?></h1>

            <div class="meta-grid">
                <div class="meta-item">
                    <i class="fas fa-user"></i>
                    <span>Submitted by: <strong><?= htmlspecialchars($resource['submitterName'] ?? 'Unknown') ?></strong></span>
                </div>
                <div class="meta-item">
                    <i class="fas fa-calendar"></i>
                    <span><?= date('F d, Y', strtotime($resource['created_at'])) ?></span>
                </div>
                <?php if (!empty($resource['category'])): ?>
                <div class="meta-item">
                    <i class="fas fa-tag"></i>
                    <span><?= htmlspecialchars($resource['category']) ?></span>
                </div>
                <?php endif; ?>
                <div class="meta-item">
                    <i class="fas fa-download"></i>
                    <span><?= number_format($resource['download_count']) ?> downloads</span>
                </div>
                <div class="meta-item">
                    <span class="status-badge status-<?= $resource['status'] ?>">
                        <i class="fas fa-<?= $resource['status'] === 'approved' ? 'check-circle' : ($resource['status'] === 'pending' ? 'clock' : 'times-circle') ?>"></i>
                        <?= ucfirst($resource['status']) ?>
                    </span>
                </div>
            </div>
        </div>

        <?php if (!empty($resource['description'])): ?>
        <div class="description-section">
            <h3><i class="fas fa-align-left"></i> Description</h3>
            <p><?= nl2br(htmlspecialchars($resource['description'])) ?></p>
        </div>
        <?php endif; ?>

        <?php if ($resource['status'] === 'approved' && $resource['approved_by']): ?>
        <div class="approval-info">
            <h4><i class="fas fa-check-circle" style="color: #10b981;"></i> Approval Information</h4>
            <p>Approved by: <strong><?= htmlspecialchars($resource['approverName'] ?? 'Admin') ?></strong></p>
            <?php if ($resource['approval_date']): ?>
                <p>Approved on: <?= date('F d, Y \a\t h:i A', strtotime($resource['approval_date'])) ?></p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ($resource['status'] === 'rejected' && !empty($resource['rejection_reason'])): ?>
        <div class="rejection-reason">
            <strong><i class="fas fa-times-circle"></i> Rejection Reason:</strong>
            <p><?= nl2br(htmlspecialchars($resource['rejection_reason'])) ?></p>
        </div>
        <?php endif; ?>

        <!-- PDF Preview -->
        <?php if ($resource['resource_type'] === 'pdf' && !empty($resource['file_path']) && $resource['status'] === 'approved'): ?>
        <div class="pdf-preview">
            <iframe src="<?= BASE_URL . $resource['file_path'] ?>#toolbar=0" title="PDF Preview"></iframe>
        </div>
        <?php elseif ($resource['resource_type'] === 'video' && !empty($resource['resource_url']) && $resource['status'] === 'approved'): ?>
        <div class="pdf-preview">
            <?php
            // Handle YouTube embeds
            $url = $resource['resource_url'];
            if (strpos($url, 'youtube.com') !== false || strpos($url, 'youtu.be') !== false) {
                preg_match('/(?:youtube\.com\/(?:watch\?v=|embed\/)|youtu\.be\/)([a-zA-Z0-9_-]+)/', $url, $matches);
                $videoId = $matches[1] ?? '';
                if ($videoId): ?>
                    <iframe src="https://www.youtube.com/embed/<?= $videoId ?>" allowfullscreen></iframe>
                <?php endif;
            } else { ?>
                <div class="preview-placeholder">
                    <i class="fas fa-video"></i>
                    <p>Video preview not available. Click "Access Resource" to view.</p>
                </div>
            <?php } ?>
        </div>
        <?php endif; ?>

        <!-- Action Buttons -->
        <?php if ($resource['status'] === 'approved'): ?>
        <div class="action-buttons">
            <a href="<?= BASE_URL ?>eresources/download/<?= $resource['id'] ?>" class="btn-action btn-download-main">
                <i class="fas fa-download"></i>
                <?= $resource['resource_type'] === 'link' ? 'Access Resource' : 'Download PDF' ?>
            </a>
            <?php if (empty($isSaved)): ?>
            <a href="<?= BASE_URL ?>eresources/save/<?= $resource['id'] ?>" class="btn-action btn-save-main">
                <i class="fas fa-bookmark"></i> Save to Library
            </a>
            <?php else: ?>
            <span class="btn-action btn-save-main" style="opacity: 0.7; cursor: default;">
                <i class="fas fa-check"></i> Already Saved
            </span>
            <?php endif; ?>
            <a href="<?= BASE_URL ?>eresources" class="btn-action btn-back-main">
                <i class="fas fa-th-large"></i> Browse All
            </a>
        </div>
        <?php else: ?>
        <div class="action-buttons">
            <a href="<?= BASE_URL ?>eresources" class="btn-action btn-back-main">
                <i class="fas fa-arrow-left"></i> Back to Browse
            </a>
        </div>
        <?php endif; ?>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>

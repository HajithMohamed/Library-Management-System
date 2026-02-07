<?php
/**
 * Faculty My Submissions View
 * Route: GET /faculty/eresources/my-submissions
 */
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

$pageTitle = 'My Submissions';
include APP_ROOT . '/views/layouts/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    body { overflow-x: hidden; }

    .submissions-wrapper {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 3rem 1.5rem;
        display: flex; align-items: flex-start; justify-content: center;
        position: relative; overflow: hidden;
    }
    .submissions-wrapper::before {
        content: ''; position: absolute; top: -50%; right: -10%;
        width: 600px; height: 600px;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
        border-radius: 50%; animation: float 20s infinite ease-in-out;
    }
    @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-30px); } }

    .submissions-container { max-width: 1400px; width: 98%; margin: 0 auto; position: relative; z-index: 1; }

    .submissions-header {
        background: rgba(255,255,255,0.98); border-radius: 32px 32px 0 0;
        padding: 2.5rem 3rem; box-shadow: 0 10px 40px rgba(102,126,234,0.15);
        animation: slideDown 0.6s ease-out; border: 1px solid rgba(255,255,255,0.3); border-bottom: none;
    }
    @keyframes slideDown { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }

    .header-content { display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 15px; }
    .header-content h1 {
        font-size: 2rem; font-weight: 900;
        background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        display: inline-flex; align-items: center; gap: 1rem;
    }
    .header-content p { color: #6b7280; font-size: 0.95rem; margin: 5px 0 0; font-weight: 500; }

    .btn-submit-new {
        padding: 12px 24px; border: none; border-radius: 14px; font-weight: 700;
        background: linear-gradient(135deg, #667eea, #764ba2); color: white;
        text-decoration: none; display: inline-flex; align-items: center; gap: 8px;
        transition: all 0.3s; box-shadow: 0 8px 20px rgba(102,126,234,0.3); font-size: 0.95rem;
    }
    .btn-submit-new:hover { transform: translateY(-3px); box-shadow: 0 12px 30px rgba(102,126,234,0.4); color: white; }

    .submissions-body {
        background: rgba(255,255,255,0.98); border-radius: 0 0 32px 32px;
        padding: 2rem 3rem 3rem; box-shadow: 0 30px 80px rgba(0,0,0,0.25);
        animation: slideUp 0.6s ease-out 0.2s both;
        border: 1px solid rgba(255,255,255,0.3); border-top: none;
    }
    @keyframes slideUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

    /* Status summary */
    .status-summary { display: flex; gap: 15px; margin-bottom: 25px; flex-wrap: wrap; }
    .status-card {
        flex: 1; min-width: 120px; padding: 15px 20px; border-radius: 16px;
        text-align: center; font-weight: 700;
    }
    .status-card .count { font-size: 2rem; display: block; }
    .status-card .label { font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px; }
    .status-approved { background: #d1fae5; color: #065f46; }
    .status-pending { background: #fef3c7; color: #92400e; }
    .status-rejected { background: #fee2e2; color: #991b1b; }

    /* Flash messages */
    .flash-msg {
        padding: 1rem; border-radius: 12px; margin-bottom: 1.5rem; font-weight: 600;
        display: flex; align-items: center; gap: 10px;
    }
    .flash-success { background: #d1fae5; color: #065f46; }
    .flash-error { background: #fee2e2; color: #991b1b; }

    .table-wrapper { overflow-x: auto; border-radius: 20px; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }

    .modern-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .modern-table thead th {
        padding: 1.1rem 1.25rem; text-align: left; font-weight: 800; color: white;
        font-size: 0.85rem; text-transform: uppercase; letter-spacing: 0.5px;
        background: linear-gradient(135deg, #667eea, #764ba2); white-space: nowrap; position: sticky; top: 0; z-index: 10;
    }
    .modern-table thead th:first-child { border-radius: 20px 0 0 0; }
    .modern-table thead th:last-child { border-radius: 0 20px 0 0; }
    .modern-table tbody tr { background: white; transition: all 0.3s; border-bottom: 1px solid #f3f4f6; }
    .modern-table tbody tr:hover { background: rgba(102,126,234,0.03); }
    .modern-table tbody td { padding: 1.1rem 1.25rem; color: #374151; font-weight: 600; }

    .resource-info { display: flex; flex-direction: column; gap: 0.4rem; }
    .resource-title { font-weight: 800; color: #1f2937; font-size: 1rem; }
    .resource-desc { font-size: 0.85rem; color: #6b7280; max-width: 250px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .status-badge {
        display: inline-flex; align-items: center; gap: 5px; padding: 5px 14px;
        border-radius: 20px; font-size: 0.85rem; font-weight: 700;
    }
    .badge-approved { background: #d1fae5; color: #065f46; }
    .badge-pending { background: #fef3c7; color: #92400e; }
    .badge-rejected { background: #fee2e2; color: #991b1b; }

    .type-badge {
        display: inline-flex; align-items: center; gap: 5px; padding: 4px 12px;
        border-radius: 10px; font-size: 0.8rem; font-weight: 700;
    }
    .type-pdf { background: #fee2e2; color: #dc2626; }
    .type-link { background: #dbeafe; color: #2563eb; }
    .type-video { background: #d1fae5; color: #059669; }

    .btn-sm {
        padding: 8px 14px; border: none; border-radius: 10px; font-weight: 700;
        cursor: pointer; transition: all 0.3s; display: inline-flex; align-items: center;
        gap: 5px; font-size: 0.85rem; text-decoration: none;
    }
    .btn-edit { background: #dbeafe; color: #2563eb; }
    .btn-edit:hover { background: #2563eb; color: white; }
    .btn-view-sm { background: #d1fae5; color: #065f46; }
    .btn-view-sm:hover { background: #065f46; color: white; }

    .rejection-info { margin-top: 5px; font-size: 0.8rem; color: #991b1b; font-style: italic; max-width: 200px; }

    .empty-state { text-align: center; padding: 4rem 2rem; }
    .empty-state i { font-size: 4rem; color: #d1d5db; margin-bottom: 1rem; display: block; }
    .empty-state h4 { font-size: 1.5rem; font-weight: 800; color: #374151; margin-bottom: 0.75rem; }
    .empty-state p { color: #6b7280; font-size: 1rem; margin-bottom: 1.5rem; }

    @media (max-width: 768px) {
        .submissions-wrapper { padding: 2rem 1rem; }
        .header-content { flex-direction: column; text-align: center; }
        .modern-table thead { display: none; }
        .modern-table tbody tr { display: block; margin-bottom: 1.5rem; border: 2px solid #f3f4f6; border-radius: 16px; padding: 1.5rem; }
        .modern-table tbody td { display: block; padding: 0.5rem 0; }
        .modern-table tbody td::before { content: attr(data-label); font-weight: 800; color: #6b7280; display: block; margin-bottom: 0.3rem; font-size: 0.8rem; text-transform: uppercase; }
        .status-summary { flex-direction: column; }
    }
</style>

<div class="submissions-wrapper">
    <div class="submissions-container">
        <div class="submissions-header">
            <div class="header-content">
                <div>
                    <h1><i class="fas fa-paper-plane"></i> My Submissions</h1>
                    <p>Track the status of your submitted e-resources</p>
                </div>
                <a href="<?= BASE_URL ?>faculty/eresources/submit" class="btn-submit-new">
                    <i class="fas fa-plus"></i> Submit New
                </a>
            </div>
        </div>

        <div class="submissions-body">
            <?php if (isset($_SESSION['success'])): ?>
                <div class="flash-msg flash-success">
                    <i class="fas fa-check-circle"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>
            <?php if (isset($_SESSION['error'])): ?>
                <div class="flash-msg flash-error">
                    <i class="fas fa-exclamation-circle"></i> <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                </div>
            <?php endif; ?>

            <?php
            $approved = array_filter($resources, fn($r) => $r['status'] === 'approved');
            $pending = array_filter($resources, fn($r) => $r['status'] === 'pending');
            $rejected = array_filter($resources, fn($r) => $r['status'] === 'rejected');
            ?>
            <div class="status-summary">
                <div class="status-card status-approved">
                    <span class="count"><?= count($approved) ?></span>
                    <span class="label">Approved</span>
                </div>
                <div class="status-card status-pending">
                    <span class="count"><?= count($pending) ?></span>
                    <span class="label">Pending</span>
                </div>
                <div class="status-card status-rejected">
                    <span class="count"><?= count($rejected) ?></span>
                    <span class="label">Rejected</span>
                </div>
            </div>

            <?php if (!empty($resources)): ?>
                <div class="table-wrapper">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-file-alt"></i> Resource</th>
                                <th><i class="fas fa-tag"></i> Type</th>
                                <th><i class="fas fa-folder"></i> Category</th>
                                <th><i class="fas fa-calendar"></i> Submitted</th>
                                <th><i class="fas fa-info-circle"></i> Status</th>
                                <th><i class="fas fa-cog"></i> Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resources as $resource): ?>
                                <tr>
                                    <td data-label="Resource">
                                        <div class="resource-info">
                                            <span class="resource-title"><?= htmlspecialchars($resource['title']) ?></span>
                                            <span class="resource-desc"><?= htmlspecialchars($resource['description'] ?? '') ?></span>
                                        </div>
                                    </td>
                                    <td data-label="Type">
                                        <span class="type-badge type-<?= $resource['resource_type'] ?>">
                                            <i class="fas fa-<?= $resource['resource_type'] === 'pdf' ? 'file-pdf' : ($resource['resource_type'] === 'video' ? 'video' : 'link') ?>"></i>
                                            <?= strtoupper($resource['resource_type']) ?>
                                        </span>
                                    </td>
                                    <td data-label="Category">
                                        <?= htmlspecialchars($resource['category'] ?? 'N/A') ?>
                                    </td>
                                    <td data-label="Submitted">
                                        <?= date('M d, Y', strtotime($resource['created_at'])) ?>
                                    </td>
                                    <td data-label="Status">
                                        <span class="status-badge badge-<?= $resource['status'] ?>">
                                            <i class="fas fa-<?= $resource['status'] === 'approved' ? 'check-circle' : ($resource['status'] === 'pending' ? 'clock' : 'times-circle') ?>"></i>
                                            <?= ucfirst($resource['status']) ?>
                                        </span>
                                        <?php if ($resource['status'] === 'rejected' && !empty($resource['rejection_reason'])): ?>
                                            <div class="rejection-info">
                                                <i class="fas fa-comment-dots"></i> <?= htmlspecialchars($resource['rejection_reason']) ?>
                                            </div>
                                        <?php endif; ?>
                                    </td>
                                    <td data-label="Actions">
                                        <?php if ($resource['status'] === 'approved'): ?>
                                            <a href="<?= BASE_URL ?>eresources/view/<?= $resource['id'] ?>" class="btn-sm btn-view-sm">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($resource['status'] === 'pending'): ?>
                                            <a href="<?= BASE_URL ?>faculty/eresources/edit/<?= $resource['id'] ?>" class="btn-sm btn-edit">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h4>No Submissions Yet</h4>
                    <p>You haven't submitted any e-resources. Start sharing your knowledge!</p>
                    <a href="<?= BASE_URL ?>faculty/eresources/submit" class="btn-submit-new">
                        <i class="fas fa-plus"></i> Submit Your First Resource
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>

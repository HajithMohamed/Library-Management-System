<?php
/**
 * User's Saved E-Resources Library
 * Route: GET /my-eresources
 */
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

$pageTitle = 'My E-Resources Library';
include APP_ROOT . '/views/layouts/header.php';
?>

<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    body { overflow-x: hidden; }

    .eresources-wrapper {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 3rem 1.5rem;
        display: flex; align-items: center; justify-content: center;
        position: relative; overflow: hidden;
    }

    .eresources-wrapper::before {
        content: ''; position: absolute; top: -50%; right: -10%;
        width: 600px; height: 600px;
        background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%);
        border-radius: 50%; animation: float 20s infinite ease-in-out;
    }
    @keyframes float { 0%, 100% { transform: translateY(0); } 50% { transform: translateY(-30px); } }

    .eresources-container { max-width: 1400px; width: 98%; margin: 0 auto; position: relative; z-index: 1; }

    .eresources-header {
        background: rgba(255,255,255,0.98); backdrop-filter: blur(10px);
        border-radius: 32px 32px 0 0; padding: 2.5rem 3rem;
        box-shadow: 0 10px 40px rgba(102,126,234,0.15);
        animation: slideInDown 0.6s ease-out; border: 1px solid rgba(255,255,255,0.3); border-bottom: none;
    }
    @keyframes slideInDown { from { opacity: 0; transform: translateY(-30px); } to { opacity: 1; transform: translateY(0); } }

    .eresources-header-content { text-align: center; }
    .eresources-header-content h1 {
        font-size: 2.2rem; font-weight: 900;
        background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent;
        display: inline-flex; align-items: center; gap: 1rem;
    }
    .eresources-header-content p { color: #6b7280; font-size: 1rem; margin: 0; font-weight: 500; }

    .eresources-body {
        background: rgba(255,255,255,0.98); backdrop-filter: blur(10px);
        border-radius: 0 0 32px 32px; padding: 2.5rem 3rem 3rem;
        box-shadow: 0 30px 80px rgba(0,0,0,0.25);
        animation: slideInUp 0.6s ease-out 0.2s both;
        border: 1px solid rgba(255,255,255,0.3); border-top: none;
    }
    @keyframes slideInUp { from { opacity: 0; transform: translateY(30px); } to { opacity: 1; transform: translateY(0); } }

    .table-wrapper { overflow-x: auto; border-radius: 20px; margin-bottom: 2rem; box-shadow: 0 5px 20px rgba(0,0,0,0.05); }

    .modern-table { width: 100%; border-collapse: separate; border-spacing: 0; }
    .modern-table thead th {
        padding: 1.25rem; text-align: left; font-weight: 800; color: white;
        font-size: 0.9rem; text-transform: uppercase; letter-spacing: 0.5px;
        background: linear-gradient(135deg, #667eea, #764ba2); white-space: nowrap; position: sticky; top: 0; z-index: 10;
    }
    .modern-table thead th:first-child { border-radius: 20px 0 0 0; }
    .modern-table thead th:last-child { border-radius: 0 20px 0 0; }

    .modern-table tbody tr { background: white; transition: all 0.3s ease; border-bottom: 1px solid #f3f4f6; }
    .modern-table tbody tr:hover { background: linear-gradient(135deg, rgba(102,126,234,0.05), rgba(118,75,162,0.05)); }
    .modern-table tbody td { padding: 1.25rem; color: #374151; border: none; font-weight: 600; }

    .resource-info { display: flex; flex-direction: column; gap: 0.5rem; }
    .resource-title { font-weight: 800; color: #1f2937; font-size: 1.05rem; }
    .resource-desc { font-size: 0.9rem; color: #6b7280; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .date-badge {
        display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.65rem 1rem;
        background: linear-gradient(135deg, rgba(59,130,246,0.1), rgba(37,99,235,0.1));
        color: #3b82f6; border-radius: 10px; font-size: 0.9rem; font-weight: 700; border: 1px solid rgba(59,130,246,0.2);
    }

    .type-badge {
        display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.5rem 0.8rem;
        border-radius: 10px; font-size: 0.85rem; font-weight: 700;
    }
    .type-pdf { background: #fee2e2; color: #dc2626; }
    .type-link { background: #dbeafe; color: #2563eb; }
    .type-video { background: #d1fae5; color: #059669; }

    .btn-view {
        padding: 0.75rem 1.5rem; border: none; border-radius: 12px; font-weight: 800;
        background: linear-gradient(135deg, #10b981, #059669); color: white; cursor: pointer;
        transition: all 0.3s ease; box-shadow: 0 5px 15px rgba(16,185,129,0.3);
        display: inline-flex; align-items: center; gap: 0.5rem; font-size: 0.9rem; text-decoration: none;
    }
    .btn-view:hover { transform: translateY(-3px); box-shadow: 0 8px 25px rgba(16,185,129,0.4); color: white; }

    .empty-state { text-align: center; padding: 5rem 2rem; }
    .empty-state-icon {
        width: 120px; height: 120px; margin: 0 auto 2rem;
        display: flex; align-items: center; justify-content: center;
        background: linear-gradient(135deg, rgba(102,126,234,0.1), rgba(118,75,162,0.1));
        border-radius: 50%; font-size: 3.5rem;
    }
    .empty-state-icon i { background: linear-gradient(135deg, #667eea, #764ba2); -webkit-background-clip: text; -webkit-text-fill-color: transparent; }
    .empty-state h4 { font-size: 2rem; font-weight: 900; color: #1f2937; margin-bottom: 1rem; }
    .empty-state p { color: #6b7280; font-size: 1.1rem; max-width: 450px; margin: 0 auto 2.5rem; line-height: 1.7; }

    .btn-browse {
        padding: 1.25rem 2.5rem; border: none; border-radius: 16px; font-weight: 800;
        background: linear-gradient(135deg, #667eea, #764ba2); color: white; text-decoration: none;
        display: inline-flex; align-items: center; gap: 1rem;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        box-shadow: 0 12px 35px rgba(102,126,234,0.3); font-size: 1.1rem;
    }
    .btn-browse:hover { transform: translateY(-5px) scale(1.05); box-shadow: 0 18px 45px rgba(102,126,234,0.4); color: white; }

    @media (max-width: 768px) {
        .eresources-wrapper { padding: 2rem 1rem; }
        .modern-table thead { display: none; }
        .modern-table tbody tr { display: block; margin-bottom: 1.5rem; border: 2px solid #f3f4f6; border-radius: 16px; padding: 1.5rem; }
        .modern-table tbody td { display: block; padding: 0.75rem 0; border: none; }
        .modern-table tbody td::before { content: attr(data-label); font-weight: 800; color: #6b7280; display: block; margin-bottom: 0.5rem; font-size: 0.85rem; text-transform: uppercase; }
        .btn-view { width: 100%; justify-content: center; }
    }
</style>

<div class="eresources-wrapper">
    <div class="eresources-container">
        <div class="eresources-header">
            <div class="eresources-header-content">
                <h1><i class="fas fa-folder-open"></i> My E-Resources Library</h1>
                <p>Manage and access your saved digital resources</p>
            </div>
        </div>

        <div class="eresources-body">
            <?php if (isset($_SESSION['success'])): ?>
                <div style="background: #d1fae5; color: #065f46; padding: 1rem; border-radius: 12px; margin-bottom: 2rem; font-weight: 600;">
                    <i class="fas fa-check-circle"></i> <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($resources)): ?>
                <div class="table-wrapper">
                    <table class="modern-table">
                        <thead>
                            <tr>
                                <th><i class="fas fa-file-alt"></i> Resource Details</th>
                                <th><i class="fas fa-tag"></i> Type</th>
                                <th><i class="fas fa-calendar-plus"></i> Saved Date</th>
                                <th><i class="fas fa-user"></i> Submitted By</th>
                                <th><i class="fas fa-cog"></i> Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($resources as $resource): ?>
                                <tr>
                                    <td data-label="Resource Details">
                                        <div class="resource-info">
                                            <span class="resource-title"><?= htmlspecialchars($resource['title']) ?></span>
                                            <span class="resource-desc"><?= htmlspecialchars($resource['description'] ?? '') ?></span>
                                        </div>
                                    </td>
                                    <td data-label="Type">
                                        <span class="type-badge type-<?= $resource['resource_type'] ?>">
                                            <?= strtoupper($resource['resource_type']) ?>
                                        </span>
                                    </td>
                                    <td data-label="Saved Date">
                                        <span class="date-badge">
                                            <i class="fas fa-calendar-alt"></i>
                                            <?= date('M d, Y', strtotime($resource['savedAt'])) ?>
                                        </span>
                                    </td>
                                    <td data-label="Submitted By">
                                        <span class="date-badge" style="background: rgba(16,185,129,0.1); color: #10b981; border-color: rgba(16,185,129,0.2);">
                                            <i class="fas fa-user-circle"></i>
                                            <?= htmlspecialchars($resource['submitterName'] ?? 'Unknown') ?>
                                        </span>
                                    </td>
                                    <td data-label="Action">
                                        <a href="<?= BASE_URL ?>eresources/view/<?= $resource['id'] ?>" class="btn-view">
                                            <i class="fas fa-eye"></i> View
                                        </a>
                                        <a href="<?= BASE_URL ?>eresources/download/<?= $resource['id'] ?>" class="btn-view" style="background: linear-gradient(135deg, #667eea, #764ba2); margin-left: 5px;">
                                            <i class="fas fa-download"></i> Download
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="empty-state">
                    <div class="empty-state-icon"><i class="fas fa-folder"></i></div>
                    <h4>Your Library Is Empty!</h4>
                    <p>Browse our collection and save valuable materials to your personal library.</p>
                    <a href="<?= BASE_URL ?>eresources" class="btn-browse">
                        <i class="fas fa-search"></i> Browse E-Resources
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>

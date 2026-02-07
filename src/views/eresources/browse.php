<?php
/**
 * E-Resources Browse View — Clean Modern Design
 * Route: GET /eresources
 */
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

$userRole = $userType ?? 'student';
$isFaculty = ($userRole === 'faculty' || $userRole === 'teacher');
$isAdmin = ($userRole === 'admin');

$pageTitle = 'Digital Library';
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
    /* ===== PAGE ===== */
    .er-page {
        min-height: 100vh;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        padding: 100px 0 80px;
        position: relative;
    }
    .er-page::before {
        content: '';
        position: absolute;
        inset: 0;
        background:
            radial-gradient(circle at 20% 20%, rgba(255,255,255,.08) 0, transparent 50%),
            radial-gradient(circle at 80% 80%, rgba(255,255,255,.06) 0, transparent 40%);
        pointer-events: none;
    }
    .er-wrap {
        max-width: 1280px;
        margin: 0 auto;
        padding: 0 24px;
        position: relative;
        z-index: 1;
    }

    /* ===== FLASH ===== */
    .er-flash {
        max-width: 680px;
        margin: 0 auto 28px;
        padding: 14px 24px;
        border-radius: 14px;
        font-weight: 600;
        font-size: .95rem;
        display: flex;
        align-items: center;
        gap: 10px;
        animation: erSlideIn .4s ease-out;
    }
    .er-flash-ok  { background: rgba(34,197,94,.2); color: #bbf7d0; border: 1px solid rgba(34,197,94,.35); }
    .er-flash-err { background: rgba(239,68,68,.2); color: #fecaca; border: 1px solid rgba(239,68,68,.35); }
    @keyframes erSlideIn { from { opacity:0; transform:translateY(-12px); } to { opacity:1; transform:translateY(0); } }

    /* ===== HEADER ===== */
    .er-header { text-align: center; margin-bottom: 48px; }
    .er-header-icon {
        width: 72px; height: 72px;
        margin: 0 auto 20px;
        background: rgba(255,255,255,.12);
        border-radius: 20px;
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem; color: #fff;
    }
    .er-header h1 {
        font-size: clamp(1.8rem, 4vw, 2.8rem);
        font-weight: 800; color: #fff;
        margin: 0 0 10px; letter-spacing: -.02em;
    }
    .er-header p {
        color: rgba(255,255,255,.8);
        font-size: 1.05rem;
        max-width: 560px;
        margin: 0 auto; line-height: 1.6;
    }

    /* ===== TOOLBAR ===== */
    .er-toolbar {
        display: flex; align-items: stretch;
        gap: 12px; max-width: 720px;
        margin: 0 auto 24px; flex-wrap: wrap;
    }
    .er-search-box { flex: 1; min-width: 240px; position: relative; }
    .er-search-box input {
        width: 100%; padding: 14px 48px 14px 20px;
        border-radius: 14px;
        border: 1px solid rgba(255,255,255,.25);
        background: rgba(255,255,255,.12);
        backdrop-filter: blur(12px);
        color: #fff; font-size: .95rem; font-weight: 500;
        transition: all .25s;
    }
    .er-search-box input::placeholder { color: rgba(255,255,255,.55); }
    .er-search-box input:focus {
        outline: none;
        background: rgba(255,255,255,.2);
        border-color: rgba(255,255,255,.5);
        box-shadow: 0 0 0 4px rgba(255,255,255,.08);
    }
    .er-search-box button {
        position: absolute; right: 6px; top: 50%; transform: translateY(-50%);
        width: 36px; height: 36px;
        border-radius: 10px; border: none;
        background: rgba(255,255,255,.15); color: #fff;
        cursor: pointer; transition: background .2s;
    }
    .er-search-box button:hover { background: rgba(255,255,255,.3); }
    .er-cat-select {
        padding: 14px 16px; border-radius: 14px;
        border: 1px solid rgba(255,255,255,.25);
        background: rgba(255,255,255,.12);
        backdrop-filter: blur(12px);
        color: #fff; font-size: .95rem; font-weight: 500;
        min-width: 160px; cursor: pointer; transition: all .25s;
    }
    .er-cat-select:focus { outline: none; border-color: rgba(255,255,255,.5); box-shadow: 0 0 0 4px rgba(255,255,255,.08); }
    .er-cat-select option { color: #333; background: #fff; }

    /* ===== QUICK LINKS ===== */
    .er-quick-links {
        display: flex; justify-content: center;
        gap: 10px; flex-wrap: wrap; margin-bottom: 44px;
    }
    .er-qlink {
        display: inline-flex; align-items: center; gap: 8px;
        padding: 10px 22px; border-radius: 50px;
        font-weight: 600; font-size: .88rem;
        text-decoration: none; transition: all .25s;
        border: 1.5px solid rgba(255,255,255,.3);
    }
    .er-qlink:hover { transform: translateY(-2px); text-decoration: none; }
    .er-qlink-primary {
        background: #fff; color: #667eea;
        border-color: #fff; box-shadow: 0 4px 16px rgba(0,0,0,.12);
    }
    .er-qlink-primary:hover { background: #f0f0ff; color: #5a3db8; box-shadow: 0 8px 24px rgba(0,0,0,.18); }
    .er-qlink-outline { background: transparent; color: #fff; }
    .er-qlink-outline:hover { background: rgba(255,255,255,.15); color: #fff; }

    /* ===== STATS ===== */
    .er-stats-bar { display: flex; justify-content: center; gap: 32px; margin-bottom: 40px; flex-wrap: wrap; }
    .er-stat { text-align: center; color: rgba(255,255,255,.85); }
    .er-stat-num { font-size: 1.6rem; font-weight: 800; color: #fff; display: block; }
    .er-stat-label { font-size: .78rem; text-transform: uppercase; letter-spacing: .06em; opacity: .7; }

    /* ===== GRID ===== */
    .er-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(340px, 1fr));
        gap: 24px;
    }

    /* ===== CARD ===== */
    .er-card {
        background: rgba(255,255,255,.08);
        backdrop-filter: blur(20px);
        border: 1px solid rgba(255,255,255,.15);
        border-radius: 20px;
        overflow: hidden;
        display: flex; flex-direction: column;
        transition: all .35s cubic-bezier(.22,.68,0,1.1);
    }
    .er-card:hover {
        transform: translateY(-6px);
        background: rgba(255,255,255,.14);
        border-color: rgba(255,255,255,.3);
        box-shadow: 0 24px 48px rgba(0,0,0,.2);
    }
    .er-card-top { padding: 24px 24px 0; display: flex; align-items: flex-start; gap: 16px; }
    .er-card-icon {
        flex-shrink: 0; width: 52px; height: 52px;
        border-radius: 14px;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.35rem;
    }
    .er-icon-pdf  { background: rgba(239,68,68,.2);  color: #fca5a5; }
    .er-icon-link { background: rgba(59,130,246,.2);  color: #93c5fd; }
    .er-icon-video{ background: rgba(16,185,129,.2);  color: #6ee7b7; }

    .er-card-info { flex: 1; min-width: 0; }
    .er-card-title {
        font-size: 1.08rem; font-weight: 700; color: #fff;
        margin: 0 0 6px; line-height: 1.35;
        display: -webkit-box; -webkit-line-clamp: 2;
        -webkit-box-orient: vertical; overflow: hidden;
    }
    .er-card-meta { display: flex; align-items: center; gap: 6px; flex-wrap: wrap; margin-bottom: 4px; }
    .er-badge {
        display: inline-block; padding: 2px 10px;
        border-radius: 6px; font-size: .72rem;
        font-weight: 700; text-transform: uppercase; letter-spacing: .04em;
    }
    .er-badge-pdf  { background: rgba(239,68,68,.25); color: #fecaca; }
    .er-badge-link { background: rgba(59,130,246,.25); color: #bfdbfe; }
    .er-badge-video{ background: rgba(16,185,129,.25); color: #a7f3d0; }
    .er-badge-cat  { background: rgba(255,255,255,.12); color: rgba(255,255,255,.8); }
    .er-card-author { font-size: .78rem; color: rgba(255,255,255,.5); }
    .er-card-author i { margin-right: 3px; }

    .er-card-body { padding: 14px 24px; flex: 1; }
    .er-card-desc {
        color: rgba(255,255,255,.7); font-size: .88rem; line-height: 1.6;
        display: -webkit-box; -webkit-line-clamp: 3;
        -webkit-box-orient: vertical; overflow: hidden; margin: 0;
    }

    .er-card-footer { padding: 0 24px 20px; display: flex; gap: 8px; }
    .er-btn {
        flex: 1; text-align: center; padding: 10px 8px;
        border-radius: 12px; text-decoration: none;
        font-weight: 600; font-size: .82rem;
        display: inline-flex; align-items: center; justify-content: center;
        gap: 5px; transition: all .2s; border: none; cursor: pointer;
    }
    .er-btn:hover { text-decoration: none; transform: translateY(-1px); }
    .er-btn-view { background: rgba(255,255,255,.18); color: #fff; }
    .er-btn-view:hover { background: #fff; color: #667eea; }
    .er-btn-dl { background: rgba(255,255,255,.08); color: rgba(255,255,255,.85); }
    .er-btn-dl:hover { background: rgba(255,255,255,.2); color: #fff; }
    .er-btn-save { background: rgba(255,255,255,.08); color: rgba(255,255,255,.85); }
    .er-btn-save:hover { background: rgba(34,197,94,.3); color: #bbf7d0; }

    .er-dl-count {
        font-size: .75rem; color: rgba(255,255,255,.4);
        text-align: right; padding: 0 24px 14px; margin-top: -6px;
    }

    /* ===== EMPTY ===== */
    .er-empty {
        grid-column: 1 / -1; text-align: center;
        padding: 80px 40px;
        background: rgba(255,255,255,.06);
        border-radius: 24px;
        border: 1px dashed rgba(255,255,255,.2);
    }
    .er-empty i { font-size: 3.5rem; color: rgba(255,255,255,.25); margin-bottom: 16px; display: block; }
    .er-empty h3 { color: #fff; font-size: 1.5rem; font-weight: 700; margin-bottom: 8px; }
    .er-empty p { color: rgba(255,255,255,.6); font-size: 1rem; max-width: 420px; margin: 0 auto; }

    /* ===== RESPONSIVE ===== */
    @media (max-width: 768px) {
        .er-page { padding: 90px 0 60px; }
        .er-grid { grid-template-columns: 1fr; gap: 16px; }
        .er-toolbar { flex-direction: column; }
        .er-card-top { padding: 20px 20px 0; }
        .er-card-body { padding: 12px 20px; }
        .er-card-footer { padding: 0 20px 18px; flex-wrap: wrap; }
        .er-stats-bar { gap: 20px; }
    }
    @media (max-width: 480px) {
        .er-quick-links { flex-direction: column; align-items: center; }
        .er-qlink { width: 100%; max-width: 260px; justify-content: center; }
    }
</style>

<div class="er-page">
<div class="er-wrap">

    <!-- Flash Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="er-flash er-flash-ok">
            <i class="fas fa-check-circle"></i>
            <?= htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <div class="er-flash er-flash-err">
            <i class="fas fa-exclamation-circle"></i>
            <?= htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
        </div>
    <?php endif; ?>

    <!-- Header -->
    <div class="er-header">
        <div class="er-header-icon"><i class="fas fa-layer-group"></i></div>
        <h1>Digital Library</h1>
        <p>Browse and discover research papers, learning materials, and educational resources shared by our community.</p>
    </div>

    <!-- Search + Filter -->
    <form method="GET" action="<?= BASE_URL ?>eresources" class="er-toolbar">
        <div class="er-search-box">
            <input type="text" name="search"
                   placeholder="Search by title, description, or category..."
                   value="<?= htmlspecialchars($searchQuery ?? '') ?>" autocomplete="off">
            <button type="submit"><i class="fas fa-search"></i></button>
        </div>
        <?php if (!empty($categories)): ?>
        <select name="category" class="er-cat-select" onchange="this.form.submit()">
            <option value="">All Categories</option>
            <?php foreach ($categories as $cat): ?>
                <option value="<?= htmlspecialchars($cat) ?>"
                    <?= ($currentCategory ?? '') === $cat ? 'selected' : '' ?>>
                    <?= htmlspecialchars($cat) ?>
                </option>
            <?php endforeach; ?>
        </select>
        <?php endif; ?>
    </form>

    <!-- Quick Links -->
    <div class="er-quick-links">
        <?php if ($isFaculty): ?>
            <a href="<?= BASE_URL ?>faculty/eresources/submit" class="er-qlink er-qlink-primary">
                <i class="fas fa-cloud-upload-alt"></i> Submit Resource
            </a>
            <a href="<?= BASE_URL ?>faculty/eresources/my-submissions" class="er-qlink er-qlink-outline">
                <i class="fas fa-list-check"></i> My Submissions
            </a>
        <?php endif; ?>
        <?php if ($isAdmin): ?>
            <a href="<?= BASE_URL ?>admin/eresources/manage" class="er-qlink er-qlink-primary">
                <i class="fas fa-sliders-h"></i> Manage
            </a>
            <a href="<?= BASE_URL ?>admin/eresources/approvals" class="er-qlink er-qlink-outline">
                <i class="fas fa-clipboard-check"></i> Approvals
            </a>
        <?php endif; ?>
        <a href="<?= BASE_URL ?>my-eresources" class="er-qlink er-qlink-outline">
            <i class="fas fa-bookmark"></i> My Library
        </a>
    </div>

    <!-- Stats -->
    <?php if (!empty($resources)): ?>
    <div class="er-stats-bar">
        <div class="er-stat">
            <span class="er-stat-num"><?= count($resources) ?></span>
            <span class="er-stat-label">Resources</span>
        </div>
        <div class="er-stat">
            <span class="er-stat-num"><?= count($categories ?? []) ?></span>
            <span class="er-stat-label">Categories</span>
        </div>
        <div class="er-stat">
            <span class="er-stat-num"><?= number_format(array_sum(array_column($resources, 'download_count'))) ?></span>
            <span class="er-stat-label">Downloads</span>
        </div>
    </div>
    <?php endif; ?>

    <!-- Resource Grid -->
    <div class="er-grid">
        <?php if (empty($resources)): ?>
            <div class="er-empty">
                <i class="fas fa-folder-open"></i>
                <h3>No Resources Found</h3>
                <p>
                    <?php if (!empty($searchQuery ?? '')): ?>
                        No results match your search. Try different keywords.
                    <?php else: ?>
                        No digital resources are currently available. Check back soon!
                    <?php endif; ?>
                </p>
            </div>
        <?php else: ?>
            <?php foreach ($resources as $resource): ?>
                <?php
                    $type = $resource['resource_type'] ?? 'pdf';
                    $iconMap = [
                        'pdf'   => 'fas fa-file-pdf',
                        'link'  => 'fas fa-link',
                        'video' => 'fas fa-play-circle'
                    ];
                    $icon = $iconMap[$type] ?? 'fas fa-file';
                ?>
                <div class="er-card">
                    <div class="er-card-top">
                        <div class="er-card-icon er-icon-<?= $type ?>">
                            <i class="<?= $icon ?>"></i>
                        </div>
                        <div class="er-card-info">
                            <h3 class="er-card-title"><?= htmlspecialchars($resource['title']) ?></h3>
                            <div class="er-card-meta">
                                <span class="er-badge er-badge-<?= $type ?>">
                                    <?= strtoupper($type) ?>
                                </span>
                                <?php if (!empty($resource['category'])): ?>
                                    <span class="er-badge er-badge-cat">
                                        <?= htmlspecialchars($resource['category']) ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="er-card-author">
                                <i class="fas fa-user-circle"></i>
                                <?= htmlspecialchars($resource['submitterName'] ?? 'Library') ?>
                                &middot;
                                <?= date('M j, Y', strtotime($resource['created_at'])) ?>
                            </div>
                        </div>
                    </div>

                    <div class="er-card-body">
                        <p class="er-card-desc">
                            <?= htmlspecialchars($resource['description'] ?? 'No description provided.') ?>
                        </p>
                    </div>

                    <div class="er-card-footer">
                        <a href="<?= BASE_URL ?>eresources/view/<?= $resource['id'] ?>"
                           class="er-btn er-btn-view">
                            <i class="fas fa-eye"></i> View
                        </a>
                        <?php if ($type === 'pdf' && !empty($resource['file_path'])): ?>
                            <a href="<?= BASE_URL ?>eresources/download/<?= $resource['id'] ?>"
                               class="er-btn er-btn-dl">
                                <i class="fas fa-download"></i> Download
                            </a>
                        <?php elseif ($type === 'link' && !empty($resource['resource_url'])): ?>
                            <a href="<?= htmlspecialchars($resource['resource_url']) ?>"
                               target="_blank" rel="noopener" class="er-btn er-btn-dl">
                                <i class="fas fa-external-link-alt"></i> Open
                            </a>
                        <?php elseif ($type === 'video' && !empty($resource['resource_url'])): ?>
                            <a href="<?= htmlspecialchars($resource['resource_url']) ?>"
                               target="_blank" rel="noopener" class="er-btn er-btn-dl">
                                <i class="fas fa-play"></i> Watch
                            </a>
                        <?php endif; ?>
                        <a href="<?= BASE_URL ?>eresources/save/<?= $resource['id'] ?>"
                           class="er-btn er-btn-save">
                            <i class="fas fa-bookmark"></i> Save
                        </a>
                    </div>

                    <?php if (($resource['download_count'] ?? 0) > 0): ?>
                        <div class="er-dl-count">
                            <i class="fas fa-arrow-down"></i>
                            <?= number_format($resource['download_count']) ?> downloads
                        </div>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

</div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>

<?php
/**
 * E-Resources Browse View - Glassmorphism Design
 */
if (!defined('APP_ROOT')) {
    die('Direct access not permitted');
}

// $userType is passed from controller
$isFaculty = ($userType === 'faculty');

$pageTitle = 'Digital Library';
include APP_ROOT . '/views/layouts/header.php';
?>

<!-- Font Awesome CDN -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<style>
    :root {
        --primary-gradient: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        --glass-bg: rgba(255, 255, 255, 0.1);
        --glass-border: rgba(255, 255, 255, 0.2);
        --card-hover-bg: rgba(255, 255, 255, 0.15);
    }

    body {
        margin: 0;
        padding: 0;
        overflow-x: hidden;
    }

    .eresources-container {
        min-height: 100vh;
        position: relative;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        padding-bottom: 60px;
    }

    /* Animated Background */
    .eresources-container::before {
        content: '';
        position: absolute;
        width: 200%;
        height: 200%;
        top: -50%;
        left: -50%;
        background: radial-gradient(circle at 50% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
        animation: rotate 20s linear infinite;
        pointer-events: none;
    }

    @keyframes rotate {
        0% {
            transform: rotate(0deg);
        }

        100% {
            transform: rotate(360deg);
        }
    }

    .floating-shapes {
        position: absolute;
        width: 100%;
        height: 100%;
        overflow: hidden;
        z-index: 0;
        pointer-events: none;
    }

    .shape {
        position: absolute;
        opacity: 0.1;
        animation: float 15s infinite ease-in-out;
        background: white;
    }

    .shape:nth-child(1) {
        top: 20%;
        left: 10%;
        width: 80px;
        height: 80px;
        border-radius: 50%;
    }

    .shape:nth-child(2) {
        top: 60%;
        right: 15%;
        width: 120px;
        height: 120px;
        border-radius: 30% 70%;
        animation-delay: 2s;
    }

    .shape:nth-child(3) {
        bottom: 20%;
        left: 15%;
        width: 100px;
        height: 100px;
        border-radius: 20px;
        animation-delay: 4s;
        transform: rotate(45deg);
    }

    @keyframes float {

        0%,
        100% {
            transform: translateY(0) rotate(0deg);
        }

        50% {
            transform: translateY(-30px) rotate(180deg);
        }
    }

    .content-wrapper {
        position: relative;
        z-index: 1;
        max-width: 1400px;
        margin: 0 auto;
        padding: 40px 20px;
    }

    /* Hero Section */
    .hero-section {
        text-align: center;
        margin-bottom: 50px;
        padding-top: 40px;
        animation: fadeInUp 0.8s ease-out;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .hero-icon {
        font-size: 80px;
        margin-bottom: 20px;
        background: linear-gradient(135deg, #fff, #f0f0f0);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        animation: pulse 2s ease-in-out infinite;
    }

    .hero-title {
        font-size: clamp(2.5rem, 5vw, 4rem);
        font-weight: 800;
        color: white;
        margin-bottom: 15px;
        text-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
    }

    .hero-subtitle {
        font-size: clamp(1.1rem, 2vw, 1.4rem);
        color: rgba(255, 255, 255, 0.95);
        max-width: 700px;
        margin: 0 auto 30px;
        line-height: 1.6;
    }

    /* Search Bar */
    .search-container {
        max-width: 600px;
        margin: 0 auto 40px;
        position: relative;
    }

    .search-input {
        width: 100%;
        padding: 18px 50px 18px 25px;
        border-radius: 50px;
        border: 2px solid rgba(255, 255, 255, 0.3);
        background: rgba(255, 255, 255, 0.15);
        backdrop-filter: blur(10px);
        color: white;
        font-size: 1.1rem;
        transition: all 0.3s ease;
    }

    .search-input::placeholder {
        color: rgba(255, 255, 255, 0.7);
    }

    .search-input:focus {
        background: rgba(255, 255, 255, 0.25);
        border-color: rgba(255, 255, 255, 0.6);
        outline: none;
        box-shadow: 0 0 20px rgba(255, 255, 255, 0.2);
    }

    .search-icon {
        position: absolute;
        right: 25px;
        top: 50%;
        transform: translateY(-50%);
        color: rgba(255, 255, 255, 0.8);
        font-size: 1.2rem;
    }

    /* Hero Actions */
    .hero-actions {
        display: flex;
        justify-content: center;
        gap: 20px;
        margin-bottom: 50px;
    }

    .btn-hero {
        padding: 14px 30px;
        border-radius: 50px;
        font-weight: 700;
        text-decoration: none;
        transition: all 0.3s ease;
        display: inline-flex;
        align-items: center;
        gap: 10px;
        font-size: 1rem;
        border: none;
        cursor: pointer;
    }

    .btn-primary {
        background: white;
        color: #667eea;
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.15);
    }

    .btn-primary:hover {
        transform: translateY(-3px);
        box-shadow: 0 15px 30px rgba(0, 0, 0, 0.25);
    }

    /* Resources Grid */
    .resources-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
        gap: 30px;
    }

    .resource-card {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        border-radius: 24px;
        padding: 30px;
        transition: all 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        position: relative;
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }

    .resource-card:hover {
        transform: translateY(-10px) scale(1.02);
        background: var(--card-hover-bg);
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.3);
        border-color: rgba(255, 255, 255, 0.4);
    }

    .card-icon {
        font-size: 3rem;
        color: white;
        margin-bottom: 20px;
        width: 70px;
        height: 70px;
        background: rgba(255, 255, 255, 0.1);
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .resource-card:nth-child(4n+1) .card-icon {
        color: #FFD700;
    }

    .resource-card:nth-child(4n+2) .card-icon {
        color: #FF6B6B;
    }

    .resource-card:nth-child(4n+3) .card-icon {
        color: #4ECDC4;
    }

    .resource-card:nth-child(4n+4) .card-icon {
        color: #95E1D3;
    }

    .resource-title {
        font-size: 1.4rem;
        font-weight: 700;
        color: white;
        margin-bottom: 10px;
        line-height: 1.4;
    }

    .resource-meta {
        font-size: 0.9rem;
        color: rgba(255, 255, 255, 0.7);
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .resource-desc {
        color: rgba(255, 255, 255, 0.85);
        font-size: 0.95rem;
        line-height: 1.6;
        margin-bottom: 25px;
        flex: 1;
        display: -webkit-box;
        -webkit-line-clamp: 3;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .btn-download {
        background: rgba(255, 255, 255, 0.2);
        color: white;
        text-align: center;
        padding: 12px;
        border-radius: 15px;
        text-decoration: none;
        font-weight: 600;
        transition: all 0.3s;
        border: 1px solid rgba(255, 255, 255, 0.1);
    }

    .btn-download:hover {
        background: white;
        color: #667eea;
    }

    /* Empty State */
    .empty-state {
        grid-column: 1 / -1;
        text-align: center;
        padding: 60px;
        background: var(--glass-bg);
        border-radius: 30px;
        border: 1px solid var(--glass-border);
    }

    .empty-state i {
        font-size: 5rem;
        color: rgba(255, 255, 255, 0.3);
        margin-bottom: 20px;
    }

    .empty-state h3 {
        font-size: 2rem;
        color: white;
        margin-bottom: 10px;
    }

    .empty-state p {
        color: rgba(255, 255, 255, 0.8);
        font-size: 1.1rem;
    }
</style>

<div class="eresources-container">
    <div class="floating-shapes">
        <div class="shape"></div>
        <div class="shape"></div>
        <div class="shape"></div>
    </div>

    <div class="content-wrapper">
        <div class="hero-section">
            <div class="hero-icon">
                <i class="fas fa-layer-group"></i>
            </div>
            <h1 class="hero-title">Digital Collections</h1>
            <p class="hero-subtitle">
                Explore our curated repository of digital learning materials, research papers, and educational resources
                accessible anytime, anywhere.
            </p>

            <div class="search-container">
                <input type="text" class="search-input" id="resourceSearch" placeholder="Search resources...">
                <i class="fas fa-search search-icon"></i>
            </div>

            <?php if ($isFaculty): ?>
                <div class="hero-actions">
                    <a href="<?= BASE_URL ?>e-resources/upload" class="btn-hero btn-primary">
                        <i class="fas fa-cloud-upload-alt"></i> Upload New Resource
                    </a>
                    <a href="<?= BASE_URL ?>e-resources/list" class="btn-hero btn-secondary"
                        style="background: rgba(255,255,255,0.2); color:white; border: 1px solid rgba(255,255,255,0.3);">
                        <i class="fas fa-list"></i> List View
                    </a>
                </div>
            <?php else: ?>
                <div class="hero-actions">
                    <a href="<?= BASE_URL ?>e-resources/list" class="btn-hero btn-secondary"
                        style="background: rgba(255,255,255,0.2); color:white; border: 1px solid rgba(255,255,255,0.3);">
                        <i class="fas fa-list"></i> List View
                    </a>
                </div>
            <?php endif; ?>
        </div>

        <div class="resources-grid" id="resourcesGrid">
            <?php if (empty($resources)): ?>
                <div class="empty-state">
                    <i class="fas fa-folder-open"></i>
                    <h3>Collection Empty</h3>
                    <p>No digital resources are currently available.</p>
                </div>
            <?php else: ?>
                <?php foreach ($resources as $resource): ?>
                    <div class="resource-card" data-title="<?= strtolower(htmlspecialchars($resource['title'])) ?>">
                        <div class="card-icon" style="padding: 0; overflow: hidden; position: relative;">
                            <?php
                            $thumbUrl = isset($cloudinaryService) ? $cloudinaryService->getThumbnailUrl($resource['fileUrl']) : '';
                            if (!empty($thumbUrl)):
                                ?>
                                <img src="<?= $thumbUrl ?>" alt="<?= htmlspecialchars($resource['title']) ?>"
                                    style="width: 100%; height: 100%; object-fit: cover;"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="fallback-icon"
                                    style="display: none; width: 100%; height: 100%; align-items: center; justify-content: center;">
                                    <i class="far fa-file-pdf"></i>
                                </div>
                            <?php else: ?>
                                <i class="far fa-file-pdf"></i>
                            <?php endif; ?>
                        </div>

                        <h3 class="resource-title"><?= htmlspecialchars($resource['title']) ?></h3>

                        <div class="resource-meta">
                            <span><i class="far fa-user"></i>
                                <?= htmlspecialchars($resource['uploadedBy'] ?? 'Library') ?></span>
                            <span>•</span>
                            <span><?= date('M d, Y', strtotime($resource['createdAt'])) ?></span>
                        </div>

                        <p class="resource-desc">
                            <?= htmlspecialchars($resource['description'] ?? 'No description available for this resource.') ?>
                        </p>

                        <div style="display: flex; gap: 10px; margin-top: auto;">
                            <a href="<?= htmlspecialchars($resource['fileUrl']) ?>" target="_blank" class="btn-download"
                                style="flex: 1;">
                                <span>View</span> <i class="fas fa-external-link-alt" style="margin-left: 5px;"></i>
                            </a>
                            <a href="<?= BASE_URL ?>e-resources/obtain/<?= $resource['resourceId'] ?>" class="btn-download"
                                style="flex: 1; background: rgba(255, 255, 255, 0.3); border-color: rgba(255,255,255,0.4);">
                                <span>Save</span> <i class="fas fa-plus" style="margin-left: 5px;"></i>
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
    // Search Functionality
    document.getElementById('resourceSearch').addEventListener('input', function (e) {
        const searchTerm = e.target.value.toLowerCase();
        const cards = document.querySelectorAll('.resource-card');

        cards.forEach(card => {
            const title = card.getAttribute('data-title');
            if (title.includes(searchTerm)) {
                card.style.display = 'flex';
            } else {
                card.style.display = 'none';
            }
        });
    });
</script>

<!-- Note: Footer intentionally omitted/customized for this full-page design if needed, 
     or include standard footer depending on layout preference. 
     Standard footer follows: -->
<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
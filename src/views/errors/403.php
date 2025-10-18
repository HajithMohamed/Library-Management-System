<?php
$pageTitle = 'Access Denied';
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
    :root {
        --error-primary: #dc2626;
        --error-secondary: #991b1b;
        --text-primary: #1f2937;
        --text-secondary: #6b7280;
        --glass-bg: rgba(255, 255, 255, 0.7);
        --glass-border: rgba(220, 38, 38, 0.2);
        --shadow-color: rgba(220, 38, 38, 0.15);
    }

    .access-denied-page {
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #fee2e2 0%, #fecaca 50%, #fca5a5 100%);
        position: relative;
        overflow: hidden;
        padding: 2rem 1rem;
    }

    /* Animated warning symbols */
    .warning-symbols {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 1;
        opacity: 0.1;
    }

    .warning-icon {
        position: absolute;
        font-size: 2rem;
        color: var(--error-primary);
        animation: floatWarning 8s ease-in-out infinite;
    }

    @keyframes floatWarning {
        0%, 100% {
            transform: translateY(0) rotate(0deg);
            opacity: 0.1;
        }
        50% {
            transform: translateY(-30px) rotate(10deg);
            opacity: 0.2;
        }
    }

    /* Main container */
    .access-denied-container {
        max-width: 700px;
        width: 100%;
        position: relative;
        z-index: 2;
        animation: containerEntrance 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }

    @keyframes containerEntrance {
        from {
            opacity: 0;
            transform: scale(0.9) translateY(30px);
        }
        to {
            opacity: 1;
            transform: scale(1) translateY(0);
        }
    }

    /* Glass card effect */
    .error-card {
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border-radius: 30px;
        padding: 3rem 2rem;
        border: 2px solid var(--glass-border);
        box-shadow: 
            0 20px 60px var(--shadow-color),
            0 0 0 1px rgba(255, 255, 255, 0.5) inset;
        text-align: center;
        position: relative;
        overflow: hidden;
    }

    .error-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: radial-gradient(circle, rgba(220, 38, 38, 0.05) 0%, transparent 70%);
        animation: rotateGradient 15s linear infinite;
    }

    @keyframes rotateGradient {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Shield icon container */
    .shield-container {
        position: relative;
        display: inline-block;
        margin-bottom: 2rem;
        animation: shieldPulse 2s ease-in-out infinite;
    }

    @keyframes shieldPulse {
        0%, 100% {
            transform: scale(1);
        }
        50% {
            transform: scale(1.05);
        }
    }

    .shield-icon {
        font-size: clamp(4rem, 12vw, 6rem);
        color: var(--error-primary);
        display: inline-block;
        position: relative;
        filter: drop-shadow(0 10px 30px rgba(220, 38, 38, 0.3));
        animation: iconBounce 3s ease-in-out infinite;
    }

    @keyframes iconBounce {
        0%, 100% {
            transform: translateY(0) rotate(-5deg);
        }
        25% {
            transform: translateY(-10px) rotate(0deg);
        }
        50% {
            transform: translateY(-15px) rotate(5deg);
        }
        75% {
            transform: translateY(-10px) rotate(0deg);
        }
    }

    /* Ripple effect */
    .ripple {
        position: absolute;
        top: 50%;
        left: 50%;
        width: 100px;
        height: 100px;
        border: 3px solid var(--error-primary);
        border-radius: 50%;
        transform: translate(-50%, -50%);
        opacity: 0;
        animation: rippleEffect 2s ease-out infinite;
    }

    .ripple:nth-child(2) {
        animation-delay: 0.5s;
    }

    .ripple:nth-child(3) {
        animation-delay: 1s;
    }

    @keyframes rippleEffect {
        0% {
            width: 100px;
            height: 100px;
            opacity: 0.6;
        }
        100% {
            width: 250px;
            height: 250px;
            opacity: 0;
        }
    }

    /* Error number */
    .error-number {
        font-size: clamp(5rem, 15vw, 8rem);
        font-weight: 900;
        margin: 0;
        background: linear-gradient(135deg, var(--error-primary) 0%, var(--error-secondary) 100%);
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        line-height: 1;
        letter-spacing: -0.05em;
        position: relative;
        z-index: 1;
        animation: numberGlow 2s ease-in-out infinite;
    }

    @keyframes numberGlow {
        0%, 100% {
            filter: drop-shadow(0 0 20px rgba(220, 38, 38, 0.4));
        }
        50% {
            filter: drop-shadow(0 0 40px rgba(220, 38, 38, 0.6));
        }
    }

    /* Typography */
    .error-title {
        font-size: clamp(2rem, 6vw, 3rem);
        font-weight: 700;
        color: var(--text-primary);
        margin: 1.5rem 0 1rem;
        position: relative;
        z-index: 1;
        animation: slideDown 0.6s ease-out 0.2s both;
    }

    .error-details {
        margin-bottom: 2.5rem;
        position: relative;
        z-index: 1;
    }

    .error-lead {
        font-size: clamp(1.1rem, 3vw, 1.4rem);
        color: var(--text-primary);
        font-weight: 500;
        margin-bottom: 1rem;
        animation: slideDown 0.6s ease-out 0.3s both;
    }

    .error-muted {
        font-size: clamp(0.95rem, 2.5vw, 1.1rem);
        color: var(--text-secondary);
        margin-bottom: 0;
        animation: slideDown 0.6s ease-out 0.4s both;
    }

    @keyframes slideDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Action buttons */
    .error-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: center;
        position: relative;
        z-index: 1;
        animation: slideUp 0.6s ease-out 0.5s both;
    }

    @keyframes slideUp {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .btn-access {
        padding: 1rem 2rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: clamp(0.95rem, 2.5vw, 1.05rem);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        text-decoration: none;
        position: relative;
        overflow: hidden;
        border: 2px solid transparent;
        white-space: nowrap;
    }

    .btn-access::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }

    .btn-access:hover::before {
        width: 400px;
        height: 400px;
    }

    .btn-access i,
    .btn-access span {
        position: relative;
        z-index: 1;
    }

    .btn-access i {
        transition: transform 0.3s ease;
    }

    /* Primary button */
    .btn-primary-access {
        background: linear-gradient(135deg, var(--error-primary), var(--error-secondary));
        color: white;
        border-color: transparent;
        box-shadow: 0 10px 30px rgba(220, 38, 38, 0.3);
    }

    .btn-primary-access:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 15px 40px rgba(220, 38, 38, 0.4);
        color: white;
    }

    .btn-primary-access:hover i {
        transform: scale(1.1);
    }

    /* Secondary button */
    .btn-secondary-access {
        background: white;
        color: var(--error-primary);
        border-color: var(--error-primary);
        box-shadow: 0 5px 20px rgba(220, 38, 38, 0.1);
    }

    .btn-secondary-access:hover {
        background: var(--error-primary);
        color: white;
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(220, 38, 38, 0.3);
    }

    .btn-secondary-access:hover i {
        transform: translateX(-3px);
    }

    /* Divider */
    .error-divider {
        width: 60px;
        height: 4px;
        background: linear-gradient(90deg, transparent, var(--error-primary), transparent);
        margin: 2rem auto;
        border-radius: 2px;
        position: relative;
        z-index: 1;
    }

    /* Tablet responsiveness */
    @media (max-width: 768px) {
        .error-card {
            padding: 2.5rem 1.5rem;
            border-radius: 25px;
        }

        .shield-container {
            margin-bottom: 1.5rem;
        }

        .error-actions {
            width: 100%;
        }

        .btn-access {
            flex: 1 1 calc(50% - 0.5rem);
            min-width: 140px;
            justify-content: center;
        }
    }

    /* Mobile responsiveness */
    @media (max-width: 540px) {
        .access-denied-page {
            padding: 1rem;
        }

        .error-card {
            padding: 2rem 1.25rem;
            border-radius: 20px;
        }

        .shield-container {
            margin-bottom: 1rem;
        }

        .error-title {
            margin: 1rem 0 0.75rem;
        }

        .error-details {
            margin-bottom: 2rem;
        }

        .error-divider {
            margin: 1.5rem auto;
        }

        .error-actions {
            flex-direction: column;
            gap: 0.75rem;
        }

        .btn-access {
            width: 100%;
            padding: 0.875rem 1.5rem;
        }
    }

    /* Dark mode support */
    @media (prefers-color-scheme: dark) {
        :root {
            --text-primary: #f9fafb;
            --text-secondary: #d1d5db;
            --glass-bg: rgba(0, 0, 0, 0.4);
        }
    }

    /* Reduced motion */
    @media (prefers-reduced-motion: reduce) {
        *,
        *::before,
        *::after {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }

    /* High contrast mode */
    @media (prefers-contrast: high) {
        .error-card {
            border-width: 3px;
        }

        .btn-access {
            border-width: 3px;
        }
    }
</style>

<script>
    // Create floating warning symbols
    function createWarningSymbols() {
        const container = document.createElement('div');
        container.className = 'warning-symbols';
        container.setAttribute('aria-hidden', 'true');
        document.querySelector('.access-denied-page').appendChild(container);

        const symbols = ['🚫', '⛔', '🔒', '⚠️'];
        const symbolCount = window.innerWidth < 768 ? 8 : 15;

        for (let i = 0; i < symbolCount; i++) {
            const symbol = document.createElement('div');
            symbol.className = 'warning-icon';
            symbol.textContent = symbols[Math.floor(Math.random() * symbols.length)];
            symbol.style.left = Math.random() * 100 + '%';
            symbol.style.top = Math.random() * 100 + '%';
            symbol.style.animationDelay = Math.random() * 8 + 's';
            symbol.style.animationDuration = (Math.random() * 4 + 6) + 's';
            container.appendChild(symbol);
        }
    }

    // Particle effect on button click
    function createParticles(e) {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

        const button = e.currentTarget;
        const rect = button.getBoundingClientRect();
        const x = e.clientX - rect.left;
        const y = e.clientY - rect.top;

        for (let i = 0; i < 6; i++) {
            const particle = document.createElement('div');
            particle.style.position = 'absolute';
            particle.style.left = x + 'px';
            particle.style.top = y + 'px';
            particle.style.width = '4px';
            particle.style.height = '4px';
            particle.style.background = 'white';
            particle.style.borderRadius = '50%';
            particle.style.pointerEvents = 'none';
            particle.style.zIndex = '1000';
            
            button.appendChild(particle);

            const angle = (Math.PI * 2 * i) / 6;
            const velocity = 50;
            const tx = Math.cos(angle) * velocity;
            const ty = Math.sin(angle) * velocity;

            particle.animate([
                { transform: 'translate(0, 0)', opacity: 1 },
                { transform: `translate(${tx}px, ${ty}px)`, opacity: 0 }
            ], {
                duration: 600,
                easing: 'ease-out'
            }).onfinish = () => particle.remove();
        }
    }

    // Initialize
    window.addEventListener('load', () => {
        createWarningSymbols();

        // Add particle effects to buttons
        document.querySelectorAll('.btn-access').forEach(button => {
            button.addEventListener('click', createParticles);
        });
    });
</script>

<div class="access-denied-page" role="main">
    <div class="access-denied-container">
        <div class="error-card">
            <div class="shield-container">
                <div class="ripple"></div>
                <div class="ripple"></div>
                <div class="ripple"></div>
                <i class="fas fa-shield-alt shield-icon" aria-hidden="true"></i>
            </div>

            <h1 class="error-number" aria-label="Error 403">403</h1>
            
            <div class="error-divider"></div>

            <h2 class="error-title">Access Denied</h2>

            <div class="error-details">
                <p class="error-lead">
                    You don't have permission to access this page.
                </p>
                <p class="error-muted">
                    Please contact the administrator if you believe this is an error.
                </p>
            </div>

            <div class="error-actions">
                <a href="<?= BASE_URL ?>" class="btn-access btn-primary-access">
                    <i class="fas fa-home" aria-hidden="true"></i>
                    <span>Go Home</span>
                </a>
                <a href="javascript:history.back()" class="btn-access btn-secondary-access">
                    <i class="fas fa-arrow-left" aria-hidden="true"></i>
                    <span>Go Back</span>
                </a>
            </div>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
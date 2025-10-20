<?php
$pageTitle = 'Page Not Found';
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
    :root {
        --primary-gradient-start: #4338ca;
        --primary-gradient-end: #5b21b6;
        --text-primary: #ffffff;
        --text-secondary: rgba(255, 255, 255, 0.7);
        --glass-bg: rgba(255, 255, 255, 0.1);
        --glass-border: rgba(255, 255, 255, 0.2);
    }

    .error-page {
        min-height: calc(100vh - 200px);
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, var(--primary-gradient-start) 0%, var(--primary-gradient-end) 100%);
        position: relative;
        overflow: hidden;
        padding: 2rem 1rem;
    }

    /* Animated background particles */
    .stars {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 1;
    }

    .star {
        position: absolute;
        background: white;
        border-radius: 50%;
        animation: twinkling 8s ease-in-out infinite;
    }

    @keyframes twinkling {
        0%, 100% { opacity: 0.3; transform: scale(1); }
        50% { opacity: 0.9; transform: scale(1.2); }
    }

    /* Main content container with glassmorphism */
    .error-content {
        max-width: 800px;
        width: 100%;
        text-align: center;
        color: var(--text-primary);
        position: relative;
        z-index: 2;
        background: var(--glass-bg);
        backdrop-filter: blur(20px);
        border-radius: 30px;
        padding: 3rem 2rem;
        border: 1px solid var(--glass-border);
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        animation: contentSlideUp 0.8s cubic-bezier(0.4, 0, 0.2, 1);
    }

    @keyframes contentSlideUp {
        from {
            opacity: 0;
            transform: translateY(50px) scale(0.95);
        }
        to {
            opacity: 1;
            transform: translateY(0) scale(1);
        }
    }

    /* Emoji animation */
    .error-emoji {
        font-size: clamp(3rem, 8vw, 5rem);
        margin-bottom: 1.5rem;
        display: inline-block;
        animation: floating 4s ease-in-out infinite;
        filter: drop-shadow(0 10px 20px rgba(0, 0, 0, 0.2));
    }

    @keyframes floating {
        0%, 100% {
            transform: translateY(0) rotate(-5deg);
        }
        25% {
            transform: translateY(-15px) rotate(5deg);
        }
        50% {
            transform: translateY(-25px) rotate(-5deg);
        }
        75% {
            transform: translateY(-15px) rotate(5deg);
        }
    }

    /* Responsive 404 number */
    .error-number {
        font-size: clamp(5rem, 15vw, 10rem);
        font-weight: 900;
        margin: 0;
        background: linear-gradient(135deg, #ffffff 0%, #e2e8f0 50%, #ffffff 100%);
        background-size: 200% 200%;
        -webkit-background-clip: text;
        background-clip: text;
        -webkit-text-fill-color: transparent;
        line-height: 1;
        animation: gradientShift 3s ease-in-out infinite, glowing 2s ease-in-out infinite;
        letter-spacing: -0.05em;
    }

    @keyframes gradientShift {
        0%, 100% { background-position: 0% 50%; }
        50% { background-position: 100% 50%; }
    }

    @keyframes glowing {
        0%, 100% {
            filter: drop-shadow(0 0 20px rgba(255, 255, 255, 0.3));
        }
        50% {
            filter: drop-shadow(0 0 40px rgba(255, 255, 255, 0.6));
        }
    }

    /* Typography */
    .error-title {
        font-size: clamp(1.75rem, 5vw, 2.5rem);
        font-weight: 700;
        margin: 1.5rem 0 1rem;
        animation: slideIn 0.6s ease-out 0.2s both;
    }

    .error-message {
        font-size: clamp(1rem, 3vw, 1.25rem);
        line-height: 1.7;
        margin-bottom: 1.5rem;
        opacity: 0.95;
        animation: slideIn 0.6s ease-out 0.3s both;
    }

    .error-suggestion {
        color: var(--text-secondary);
        font-size: clamp(0.9rem, 2.5vw, 1rem);
        margin-bottom: 2.5rem;
        animation: slideIn 0.6s ease-out 0.4s both;
    }

    @keyframes slideIn {
        from {
            opacity: 0;
            transform: translateY(20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    /* Flexible button layout */
    .error-actions {
        display: flex;
        flex-wrap: wrap;
        gap: 1rem;
        justify-content: center;
        animation: slideIn 0.6s ease-out 0.5s both;
    }

    .btn-error {
        padding: 1rem 2rem;
        border-radius: 50px;
        font-weight: 600;
        font-size: clamp(0.9rem, 2.5vw, 1rem);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        display: inline-flex;
        align-items: center;
        gap: 0.75rem;
        position: relative;
        overflow: hidden;
        text-decoration: none;
        cursor: pointer;
        white-space: nowrap;
    }

    .btn-error::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.2);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }

    .btn-error:hover::before {
        width: 300px;
        height: 300px;
    }

    .btn-error i {
        position: relative;
        z-index: 1;
        transition: transform 0.3s ease;
    }

    .btn-error span {
        position: relative;
        z-index: 1;
    }

    .btn-primary-error {
        background: rgba(255, 255, 255, 0.15);
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.3);
        backdrop-filter: blur(10px);
    }

    .btn-primary-error:hover {
        background: rgba(255, 255, 255, 0.25);
        transform: translateY(-3px) scale(1.02);
        border-color: rgba(255, 255, 255, 0.5);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
    }

    .btn-primary-error:hover i {
        transform: translateX(3px);
    }

    .btn-secondary-error {
        background: transparent;
        color: white;
        border: 2px solid rgba(255, 255, 255, 0.3);
    }

    .btn-secondary-error:hover {
        background: rgba(255, 255, 255, 0.1);
        border-color: rgba(255, 255, 255, 0.5);
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
    }

    .btn-secondary-error:hover i {
        transform: translateX(-3px);
    }

    /* Meteor effects */
    .meteor {
        position: absolute;
        width: 2px;
        height: 80px;
        background: linear-gradient(to bottom, rgba(255, 255, 255, 1), transparent);
        opacity: 0;
        transform: rotate(-45deg);
        z-index: 1;
    }

    @keyframes meteor {
        0% {
            transform: translateX(0) translateY(0) rotate(-45deg);
            opacity: 1;
        }
        70% {
            opacity: 1;
        }
        100% {
            transform: translateX(300px) translateY(300px) rotate(-45deg);
            opacity: 0;
        }
    }

    /* Tablet responsiveness */
    @media (max-width: 768px) {
        .error-content {
            padding: 2.5rem 1.5rem;
            border-radius: 20px;
        }

        .error-actions {
            width: 100%;
        }

        .btn-error {
            flex: 1 1 calc(50% - 0.5rem);
            min-width: 140px;
            justify-content: center;
        }
    }

    /* Mobile responsiveness */
    @media (max-width: 540px) {
        .error-page {
            padding: 1rem;
        }

        .error-content {
            padding: 2rem 1.25rem;
        }

        .error-emoji {
            margin-bottom: 1rem;
        }

        .error-number {
            margin-bottom: 0.5rem;
        }

        .error-title {
            margin: 1rem 0 0.75rem;
        }

        .error-message {
            margin-bottom: 1rem;
        }

        .error-suggestion {
            margin-bottom: 2rem;
        }

        .error-actions {
            flex-direction: column;
            gap: 0.75rem;
        }

        .btn-error {
            width: 100%;
            padding: 0.875rem 1.5rem;
        }
    }

    /* Extra small screens */
    @media (max-width: 360px) {
        .error-content {
            padding: 1.5rem 1rem;
        }
    }

    /* Dark mode support */
    @media (prefers-color-scheme: dark) {
        :root {
            --glass-bg: rgba(255, 255, 255, 0.08);
            --glass-border: rgba(255, 255, 255, 0.15);
        }
    }

    /* Reduced motion */
    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }
</style>

<script>
    // Create stars with varied sizes
    function createStars() {
        const starsContainer = document.createElement('div');
        starsContainer.className = 'stars';
        starsContainer.setAttribute('aria-hidden', 'true');
        document.querySelector('.error-page').appendChild(starsContainer);

        const starCount = window.innerWidth < 768 ? 30 : 60;
        
        for (let i = 0; i < starCount; i++) {
            const star = document.createElement('div');
            star.className = 'star';
            star.style.left = Math.random() * 100 + '%';
            star.style.top = Math.random() * 100 + '%';
            const size = Math.random() * 3 + 1;
            star.style.width = size + 'px';
            star.style.height = size + 'px';
            star.style.animationDelay = Math.random() * 8 + 's';
            star.style.animationDuration = (Math.random() * 4 + 6) + 's';
            starsContainer.appendChild(star);
        }
    }

    // Create meteors
    function createMeteor() {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
        
        const meteor = document.createElement('div');
        meteor.className = 'meteor';
        meteor.setAttribute('aria-hidden', 'true');
        meteor.style.left = Math.random() * 100 + '%';
        meteor.style.top = Math.random() * 50 + '%';
        document.querySelector('.error-page').appendChild(meteor);

        const duration = 1.5 + Math.random() * 2;
        meteor.style.animation = `meteor ${duration}s linear`;

        meteor.addEventListener('animationend', () => {
            meteor.remove();
            setTimeout(createMeteor, Math.random() * 3000 + 2000);
        });
    }

    // Initialize animations
    window.addEventListener('load', () => {
        createStars();
        
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            for (let i = 0; i < 3; i++) {
                setTimeout(() => createMeteor(), i * 2500);
            }
        }
    });

    // Cleanup on page unload
    window.addEventListener('beforeunload', () => {
        const stars = document.querySelector('.stars');
        if (stars) stars.remove();
    });
</script>

<div class="error-page" role="main">
    <div class="error-content">
        <div class="error-emoji" role="img" aria-label="Astronaut">
            👨‍🚀
        </div>
        <h1 class="error-number" aria-label="Error 404">404</h1>
        <h2 class="error-title">Page Lost in Space</h2>
        <p class="error-message">
            Houston, we have a problem! The page you're looking for has drifted into deep space.
        </p>
        <p class="error-suggestion">
            Don't worry though - our mission control team is here to guide you back to safety.
        </p>
        
        <div class="error-actions">
            <a href="<?= BASE_URL ?>" class="btn-error btn-primary-error">
                <i class="fas fa-rocket" aria-hidden="true"></i>
                <span>Return to Mission Control</span>
            </a>
            <a href="javascript:history.back()" class="btn-error btn-secondary-error">
                <i class="fas fa-arrow-left" aria-hidden="true"></i>
                <span>Previous Coordinates</span>
            </a>
        </div>
    </div>
</div>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>
<!-- Admin Footer - Inside page-content div -->
        <footer class="admin-footer">
            <div class="admin-footer-content">
                <div class="footer-left">
                    <p class="copyright">
                        <i class="fas fa-copyright"></i> <?= date('Y') ?> Library Management System. All rights reserved.
                    </p>
                </div>
                <div class="footer-right">
                    <a href="#" class="footer-link">Privacy Policy</a>
                    <span class="footer-separator">•</span>
                    <a href="#" class="footer-link">Terms of Service</a>
                    <span class="footer-separator">•</span>
                    <a href="#" class="footer-link">Help</a>
                </div>
            </div>
        </footer>
    </div> <!-- End page-content -->
</main> <!-- End main-content -->
</div> <!-- End admin-layout -->

<!-- Back to Top Button -->
<button class="back-to-top" id="backToTop" title="Back to top">
    <i class="fas fa-arrow-up"></i>
</button>

<style>
    /* Admin Footer Styles */
    .admin-footer {
        background: white;
        border-top: 1px solid #e2e8f0;
        padding: 1.5rem 2rem;
        margin-top: 3rem;
    }

    .admin-footer-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .footer-left .copyright {
        margin: 0;
        color: #64748b;
        font-size: 0.9rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }

    .footer-left .copyright i {
        color: #667eea;
    }

    .footer-right {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        flex-wrap: wrap;
    }

    .footer-link {
        color: #64748b;
        text-decoration: none;
        font-size: 0.9rem;
        transition: color 0.2s ease;
    }

    .footer-link:hover {
        color: #667eea;
    }

    .footer-separator {
        color: #cbd5e1;
        user-select: none;
    }

    /* Back to Top Button */
    .back-to-top {
        position: fixed;
        bottom: 2rem;
        right: 2rem;
        width: 50px;
        height: 50px;
        border-radius: 50%;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        opacity: 0;
        visibility: hidden;
        transform: translateY(20px);
        transition: all 0.3s ease;
        z-index: 1000;
    }

    .back-to-top.show {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .back-to-top:hover {
        transform: translateY(-5px);
        box-shadow: 0 8px 20px rgba(102, 126, 234, 0.5);
    }

    .back-to-top:active {
        transform: translateY(-2px);
    }

    /* Mobile Responsive */
    @media (max-width: 768px) {
        .admin-footer {
            padding: 1rem;
            margin-top: 2rem;
        }

        .admin-footer-content {
            flex-direction: column;
            text-align: center;
            gap: 0.75rem;
        }

        .footer-left,
        .footer-right {
            justify-content: center;
        }

        .back-to-top {
            width: 45px;
            height: 45px;
            bottom: 1.5rem;
            right: 1.5rem;
            font-size: 1.1rem;
        }
    }

    @media (max-width: 480px) {
        .footer-right {
            flex-direction: column;
            gap: 0.5rem;
        }

        .footer-separator {
            display: none;
        }
    }
</style>

<script>
// Back to Top Button Functionality
document.addEventListener('DOMContentLoaded', function() {
    const backToTop = document.getElementById('backToTop');
    
    // Show/hide back to top button based on scroll position
    window.addEventListener('scroll', function() {
        if (window.pageYOffset > 300) {
            backToTop.classList.add('show');
        } else {
            backToTop.classList.remove('show');
        }
    });
    
    // Scroll to top when clicked
    backToTop.addEventListener('click', function(e) {
        e.preventDefault();
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    });
});
</script>

<!-- Bootstrap JS (if not already included) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

</body>
</html>
</main>

    <!-- Modern Footer -->
    <footer class="modern-footer">
        <div class="footer-content">
            <div class="container">
                <div class="row g-4">
                    <!-- About Section -->
                    <div class="col-lg-4 col-md-6">
                        <div class="footer-section">
                            <div class="footer-logo">
                                <i class="fas fa-book-open"></i>
                                <span>University Library</span>
                            </div>
                            <p class="footer-desc">
                                A comprehensive digital solution for managing library resources, 
                                empowering students and faculty with seamless access to knowledge.
                            </p>
                            <div class="social-links">
                                <a href="#" class="social-link" title="Facebook">
                                    <i class="fab fa-facebook-f"></i>
                                </a>
                                <a href="#" class="social-link" title="Twitter">
                                    <i class="fab fa-twitter"></i>
                                </a>
                                <a href="#" class="social-link" title="Instagram">
                                    <i class="fab fa-instagram"></i>
                                </a>
                                <a href="#" class="social-link" title="LinkedIn">
                                    <i class="fab fa-linkedin-in"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Quick Links -->
                    <div class="col-lg-2 col-md-6">
                        <div class="footer-section">
                            <h5 class="footer-title">Quick Links</h5>
                            <ul class="footer-links">
                                <li><a href="<?= BASE_URL ?>"><i class="fas fa-home"></i> Home</a></li>
                                <li><a href="<?= BASE_URL ?>about"><i class="fas fa-info-circle"></i> About Us</a></li>
                                <li><a href="<?= BASE_URL ?>library"><i class="fas fa-book-reader"></i> Library Info</a></li>
                                <li><a href="<?= BASE_URL ?>contact"><i class="fas fa-envelope"></i> Contact</a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Services -->
                    <div class="col-lg-3 col-md-6">
                        <div class="footer-section">
                            <h5 class="footer-title">Our Services</h5>
                            <ul class="footer-links">
                                <li><a href="<?= BASE_URL ?>books"><i class="fas fa-book"></i> Book Lending</a></li>
                                <li><a href="#"><i class="fas fa-search"></i> Research Support</a></li>
                                <li><a href="#"><i class="fas fa-laptop"></i> Digital Resources</a></li>
                                <li><a href="#"><i class="fas fa-users"></i> Study Spaces</a></li>
                            </ul>
                        </div>
                    </div>
                    
                    <!-- Contact Info -->
                    <div class="col-lg-3 col-md-6">
                        <div class="footer-section">
                            <h5 class="footer-title">Contact Us</h5>
                            <ul class="footer-contact">
                                <li>
                                    <i class="fas fa-map-marker-alt"></i>
                                    <span>University of Ruhuna<br>Matara, Sri Lanka</span>
                                </li>
                                <li>
                                    <i class="fas fa-phone"></i>
                                    <span>+94 41 222 2222</span>
                                </li>
                                <li>
                                    <i class="fas fa-envelope"></i>
                                    <span>library@ruh.ac.lk</span>
                                </li>
                                <li>
                                    <i class="fas fa-clock"></i>
                                    <span>Mon-Fri: 8AM - 8PM</span>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Footer Bottom -->
                <div class="footer-bottom">
                    <div class="row align-items-center">
                        <div class="col-md-6 text-center text-md-start">
                            <p class="copyright">
                                <i class="fas fa-copyright"></i> <?= date('Y') ?> University Library. 
                                All rights reserved.
                            </p>
                        </div>
                        <div class="col-md-6 text-center text-md-end">
                            <div class="footer-bottom-links">
                                <a href="#">Privacy Policy</a>
                                <span class="separator">•</span>
                                <a href="#">Terms of Service</a>
                                <span class="separator">•</span>
                                <a href="#">Help Center</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Back to Top Button -->
        <button class="back-to-top" id="backToTop" title="Back to top">
            <i class="fas fa-arrow-up"></i>
        </button>
    </footer>

    <style>
        /* Modern Footer Styles */
        .modern-footer {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-top: 1px solid rgba(102, 126, 234, 0.2);
            box-shadow: 0 -10px 40px rgba(0, 0, 0, 0.1);
            margin-top: 5rem;
            position: relative;
            z-index: 1;
        }
        
        .footer-content {
            padding: 4rem 0 1.5rem;
        }
        
        .footer-section {
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
        
        /* Footer Logo */
        .footer-logo {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 1rem;
            font-size: 1.5rem;
            font-weight: 800;
        }
        
        .footer-logo i {
            font-size: 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .footer-logo span {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .footer-desc {
            color: #6b7280;
            line-height: 1.7;
            margin-bottom: 1.5rem;
            font-size: 0.95rem;
        }
        
        /* Social Links */
        .social-links {
            display: flex;
            gap: 12px;
            margin-top: 1.5rem;
        }
        
        .social-link {
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: rgba(102, 126, 234, 0.1);
            color: #667eea;
            border-radius: 12px;
            text-decoration: none;
            transition: all 0.3s ease;
            font-size: 1.1rem;
        }
        
        .social-link:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            transform: translateY(-5px) rotate(5deg);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        /* Footer Title */
        .footer-title {
            font-size: 1.2rem;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 1.5rem;
            position: relative;
            padding-bottom: 0.75rem;
        }
        
        .footer-title::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 40px;
            height: 3px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 2px;
        }
        
        /* Footer Links */
        .footer-links {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .footer-links li {
            margin-bottom: 0.75rem;
        }
        
        .footer-links a {
            color: #6b7280;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            font-size: 0.95rem;
        }
        
        .footer-links a i {
            font-size: 0.85rem;
            width: 16px;
        }
        
        .footer-links a:hover {
            color: #667eea;
            transform: translateX(5px);
        }
        
        /* Footer Contact */
        .footer-contact {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .footer-contact li {
            display: flex;
            align-items: start;
            gap: 12px;
            margin-bottom: 1rem;
            color: #6b7280;
            font-size: 0.95rem;
        }
        
        .footer-contact i {
            color: #667eea;
            font-size: 1.1rem;
            width: 20px;
            flex-shrink: 0;
            margin-top: 2px;
        }
        
        /* Footer Bottom */
        .footer-bottom {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(102, 126, 234, 0.1);
        }
        
        .copyright {
            color: #6b7280;
            margin: 0;
            font-size: 0.9rem;
        }
        
        .footer-bottom-links {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .footer-bottom-links a {
            color: #6b7280;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.3s ease;
        }
        
        .footer-bottom-links a:hover {
            color: #667eea;
        }
        
        .footer-bottom-links .separator {
            color: #d1d5db;
        }
        
        /* Back to Top Button */
        .back-to-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 50%;
            font-size: 1.2rem;
            cursor: pointer;
            display: none;
            align-items: center;
            justify-content: center;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.4);
            transition: all 0.3s ease;
            z-index: 1000;
            animation: bounceIn 0.5s ease-out;
        }
        
        @keyframes bounceIn {
            0% {
                opacity: 0;
                transform: scale(0.3);
            }
            50% {
                transform: scale(1.05);
            }
            100% {
                opacity: 1;
                transform: scale(1);
            }
        }
        
        .back-to-top:hover {
            transform: translateY(-5px) scale(1.1);
            box-shadow: 0 15px 40px rgba(102, 126, 234, 0.5);
        }
        
        .back-to-top.show {
            display: flex;
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .footer-content {
                padding: 3rem 0 1rem;
            }
            
            .footer-section {
                margin-bottom: 2rem;
            }
            
            .footer-logo {
                justify-content: center;
            }
            
            .footer-desc {
                text-align: center;
            }
            
            .social-links {
                justify-content: center;
            }
            
            .footer-title::after {
                left: 50%;
                transform: translateX(-50%);
            }
            
            .footer-links,
            .footer-contact {
                text-align: center;
            }
            
            .footer-links a,
            .footer-contact li {
                justify-content: center;
            }
            
            .footer-bottom {
                text-align: center;
            }
            
            .footer-bottom-links {
                margin-top: 1rem;
            }
            
            .back-to-top {
                bottom: 20px;
                right: 20px;
                width: 45px;
                height: 45px;
            }
        }
        
        @media (max-width: 576px) {
            .social-link {
                width: 36px;
                height: 36px;
                font-size: 1rem;
            }
        }
    </style>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        $(document).ready(function() {
            // Background Animation
            console.log('Modern footer and animations loaded successfully');
            
            // Back to Top Button
            const backToTop = $('#backToTop');
            
            $(window).scroll(function() {
                if ($(this).scrollTop() > 300) {
                    backToTop.addClass('show');
                } else {
                    backToTop.removeClass('show');
                }
            });
            
            backToTop.click(function() {
                $('html, body').animate({
                    scrollTop: 0
                }, 600, 'swing');
                return false;
            });
            
            // Auto-hide alerts after 5 seconds
            setTimeout(function() {
                $('.alert').fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);

            // Confirm delete actions
            $(document).on('click', '.btn-danger[data-confirm="true"], .delete-btn', function(e) {
                if (!confirm('Are you sure you want to delete this item? This action cannot be undone.')) {
                    e.preventDefault();
                    return false;
                }
            });

            // Enhanced form validation
            $('form').on('submit', function(e) {
                let isValid = true;
                const form = $(this);
                
                // Clear previous validation states
                form.find('.is-invalid').removeClass('is-invalid');
                
                // Check required fields
                form.find('input[required], select[required], textarea[required]').each(function() {
                    const field = $(this);
                    const value = field.val();
                    
                    if (!value || value.trim() === '') {
                        field.addClass('is-invalid');
                        isValid = false;
                        
                        // Add error message if not exists
                        if (!field.next('.invalid-feedback').length) {
                            field.after('<div class="invalid-feedback">This field is required.</div>');
                        }
                    }
                });
                
                // Email validation
                form.find('input[type="email"]').each(function() {
                    const email = $(this);
                    const emailValue = email.val();
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    
                    if (emailValue && !emailRegex.test(emailValue)) {
                        email.addClass('is-invalid');
                        isValid = false;
                        
                        if (!email.next('.invalid-feedback').length) {
                            email.after('<div class="invalid-feedback">Please enter a valid email address.</div>');
                        }
                    }
                });
                
                // Password match validation
                const password = form.find('input[name="password"]');
                const confirmPassword = form.find('input[name="confirm_password"]');
                
                if (password.length && confirmPassword.length) {
                    if (password.val() !== confirmPassword.val()) {
                        confirmPassword.addClass('is-invalid');
                        isValid = false;
                        
                        if (!confirmPassword.next('.invalid-feedback').length) {
                            confirmPassword.after('<div class="invalid-feedback">Passwords do not match.</div>');
                        }
                    }
                }
                
                if (!isValid) {
                    e.preventDefault();
                    
                    // Scroll to first error
                    $('html, body').animate({
                        scrollTop: form.find('.is-invalid').first().offset().top - 100
                    }, 300);
                }
                
                return isValid;
            });
            
            // Remove validation error on input
            $('input, select, textarea').on('input change', function() {
                $(this).removeClass('is-invalid');
                $(this).next('.invalid-feedback').remove();
            });
            
            // Smooth scroll for anchor links
            $('a[href^="#"]').on('click', function(e) {
                const target = $(this.getAttribute('href'));
                if (target.length) {
                    e.preventDefault();
                    $('html, body').stop().animate({
                        scrollTop: target.offset().top - 80
                    }, 600, 'swing');
                }
            });
            
            // Add loading state to buttons on form submit
            $('form').on('submit', function() {
                const submitBtn = $(this).find('button[type="submit"]');
                if (!submitBtn.prop('disabled')) {
                    submitBtn.prop('disabled', true);
                    const originalText = submitBtn.html();
                    submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Processing...');
                    
                    // Re-enable after 5 seconds as fallback
                    setTimeout(function() {
                        submitBtn.prop('disabled', false);
                        submitBtn.html(originalText);
                    }, 5000);
                }
            });
            
            // Tooltip initialization (if Bootstrap tooltips are used)
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
            
            // Add fade-in animation to cards and sections
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };
            
            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '0';
                        entry.target.style.transform = 'translateY(20px)';
                        
                        setTimeout(() => {
                            entry.target.style.transition = 'all 0.6s ease-out';
                            entry.target.style.opacity = '1';
                            entry.target.style.transform = 'translateY(0)';
                        }, 100);
                        
                        observer.unobserve(entry.target);
                    }
                });
            }, observerOptions);
            
            // Observe cards and sections
            document.querySelectorAll('.card, .section-animate').forEach(el => {
                observer.observe(el);
            });
        });
    </script>
</body>
</html>
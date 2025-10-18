    </main>

    <!-- Footer -->
    <footer class="bg-light py-4 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-6">
                    <h5>University Library Management System</h5>
                    <p class="text-muted">A comprehensive solution for managing library resources, users, and transactions.</p>
                </div>
                <div class="col-md-6 text-md-end">
                    <p class="text-muted">
                        <i class="fas fa-copyright"></i> <?= date('Y') ?> University Library. All rights reserved.
                    </p>
                    <p class="text-muted">
                        <i class="fas fa-envelope"></i> library@university.edu
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <!-- Custom JS -->
    <script>
        // Auto-hide alerts after 5 seconds
        $(document).ready(function() {
            setTimeout(function() {
                $('.alert').fadeOut('slow');
            }, 5000);
        });

        // Confirm delete actions
        $('.btn-danger').on('click', function(e) {
            if (!confirm('Are you sure you want to delete this item?')) {
                e.preventDefault();
            }
        });

        // Form validation
        $('form').on('submit', function() {
            var isValid = true;
            $(this).find('input[required], select[required], textarea[required]').each(function() {
                if ($(this).val() === '') {
                    $(this).addClass('is-invalid');
                    isValid = false;
                } else {
                    $(this).removeClass('is-invalid');
                }
            });
            return isValid;
        });
    </script>
</body>
</html>

<?php
$pageTitle = 'Admin Profile';
include APP_ROOT . '/views/layouts/admin-header.php';
?>

<style>
    /* Layout Structure */
    body {
        margin: 0;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        background: #f8fafc;
    }

    .admin-layout {
        display: flex;
        min-height: 100vh;
    }

    /* Main Content Area - Adjusted for dark sidebar */
    .main-content {
        flex: 1;
        margin-left: 280px;
        transition: margin-left 0.3s ease;
        background: #f8fafc;
    }

    .sidebar.collapsed ~ .main-content {
        margin-left: 80px;
    }

    .profile-container {
        padding: 2rem;
        max-width: 1400px;
        margin: 0 auto;
    }

    .page-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 2rem;
        flex-wrap: wrap;
        gap: 1rem;
    }

    .page-title {
        display: flex;
        align-items: center;
        gap: 1rem;
        font-size: 2rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    .page-title i {
        font-size: 2rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }

    .back-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        background: white;
        color: #64748b;
        text-decoration: none;
        border-radius: 12px;
        font-weight: 500;
        border: 2px solid #e2e8f0;
        transition: all 0.3s ease;
    }

    .back-btn:hover {
        background: #f8fafc;
        border-color: #cbd5e1;
        color: #475569;
        transform: translateY(-2px);
    }

    /* Profile Card */
    .profile-card {
        background: white;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        margin-bottom: 2rem;
    }

    .profile-card-header {
        padding: 2rem;
        border-bottom: 2px solid #f1f5f9;
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .profile-card-header h3 {
        display: flex;
        align-items: center;
        gap: 0.75rem;
        font-size: 1.5rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0;
    }

    .profile-card-header i {
        color: #667eea;
        font-size: 1.5rem;
    }

    .profile-card-body {
        padding: 2rem;
    }
    
    /* Profile Image Section */
    .profile-image-section {
        display: flex;
        align-items: center;
        gap: 2rem;
        padding: 2rem;
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.03), rgba(118, 75, 162, 0.03));
        border-radius: 16px;
        margin-bottom: 2.5rem;
        border: 2px dashed rgba(102, 126, 234, 0.2);
        transition: all 0.3s ease;
    }
    
    .profile-image-section:hover {
        border-color: rgba(102, 126, 234, 0.4);
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
    }
    
    .profile-avatar {
        position: relative;
    }
    
    .profile-avatar img {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid white;
        box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        transition: all 0.3s ease;
    }
    
    .profile-avatar:hover img {
        transform: scale(1.05);
        box-shadow: 0 15px 40px rgba(102, 126, 234, 0.4);
    }
    
    .profile-avatar-badge {
        position: absolute;
        bottom: 5px;
        right: 5px;
        width: 32px;
        height: 32px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.9rem;
        border: 3px solid white;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
    }
    
    .profile-image-info {
        flex: 1;
    }
    
    .profile-image-info h4 {
        font-size: 1.2rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }
    
    .profile-image-info p {
        color: #6b7280;
        margin: 0;
        font-size: 0.95rem;
    }
    
    /* Form Layout */
    .form-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 1.5rem;
    }
    
    .form-group {
        margin-bottom: 0;
    }
    
    .form-group.full-width {
        grid-column: 1 / -1;
    }

    .form-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        font-weight: 600;
        color: #475569;
        margin-bottom: 0.5rem;
        font-size: 0.875rem;
    }

    .form-label i {
        color: #667eea;
        font-size: 1rem;
    }

    .required-star {
        color: #ef4444;
        margin-left: 2px;
    }

    .form-input,
    .form-select,
    .form-textarea {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        font-size: 0.95rem;
        transition: all 0.3s ease;
        background: #f8fafc;
        font-family: inherit;
    }

    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
    }

    .form-input:disabled {
        background: #f1f5f9;
        color: #94a3b8;
        cursor: not-allowed;
    }

    .form-textarea {
        min-height: 100px;
        resize: vertical;
    }

    .form-file {
        width: 100%;
        padding: 0.75rem 1rem;
        border: 2px dashed #e2e8f0;
        border-radius: 10px;
        font-size: 0.95rem;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #f8fafc;
    }

    .form-file:hover {
        border-color: #667eea;
        background: white;
    }

    .disabled-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        margin-top: 0.5rem;
        padding: 0.35rem 0.75rem;
        background: #fef3c7;
        color: #92400e;
        border-radius: 8px;
        font-size: 0.8rem;
        font-weight: 600;
    }

    .disabled-badge i {
        font-size: 0.75rem;
    }

    /* Info Box */
    .info-box {
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(147, 51, 234, 0.05));
        border: 2px solid rgba(59, 130, 246, 0.1);
        border-radius: 12px;
        padding: 1.25rem;
        margin-bottom: 1.5rem;
    }

    .info-box-content {
        display: flex;
        gap: 1rem;
        align-items: flex-start;
    }

    .info-box-icon {
        flex-shrink: 0;
        width: 36px;
        height: 36px;
        background: linear-gradient(135deg, #3b82f6 0%, #9333ea 100%);
        color: white;
        border-radius: 8px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.1rem;
    }

    .info-box-text h5 {
        font-size: 1rem;
        font-weight: 700;
        color: #1e293b;
        margin: 0 0 0.4rem 0;
    }

    .info-box-text p {
        color: #64748b;
        margin: 0;
        line-height: 1.5;
        font-size: 0.9rem;
    }

    /* Admin Badge */
    .admin-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 20px;
        font-weight: 600;
        font-size: 0.85rem;
        margin-top: 0.5rem;
    }

    .admin-badge i {
        font-size: 0.9rem;
    }

    /* Action Buttons */
    .form-actions {
        display: flex;
        gap: 1rem;
        justify-content: flex-end;
        margin-top: 1.5rem;
        padding-top: 1.5rem;
        border-top: 2px solid #f1f5f9;
    }

    .btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.75rem 1.5rem;
        border-radius: 10px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        font-size: 0.95rem;
        border: none;
        font-family: inherit;
    }

    .btn-cancel {
        background: #f1f5f9;
        color: #64748b;
    }

    .btn-cancel:hover {
        background: #e2e8f0;
        color: #475569;
        transform: translateY(-2px);
    }

    .btn-submit {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
    }

    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
    }

    .btn-submit:active,
    .btn-cancel:active {
        transform: translateY(0);
    }

    /* Responsive Design */
    @media (max-width: 768px) {
        .main-content {
            margin-left: 0;
        }

        .profile-container {
            padding: 1rem;
        }

        .page-header {
            flex-direction: column;
            align-items: flex-start;
        }

        .profile-card-body {
            padding: 1.5rem;
        }

        .profile-image-section {
            flex-direction: column;
            text-align: center;
        }

        .form-grid {
            grid-template-columns: 1fr;
        }

        .form-actions {
            flex-direction: column-reverse;
        }

        .btn {
            width: 100%;
            justify-content: center;
        }
    }
</style>

<div class="admin-layout">
    <!-- Main Content -->
    <main class="main-content">
        <div class="profile-container">
            <!-- Page Header -->
            <div class="page-header">
                <h1 class="page-title">
                    <i class="fas fa-user-shield"></i>
                    Administrator Profile
                </h1>
                <a href="<?= BASE_URL ?>admin/dashboard" class="back-btn">
                    <i class="fas fa-arrow-left"></i>
                    Back to Dashboard
                </a>
            </div>

            <!-- Profile Card -->
            <div class="profile-card">
                <!-- Card Header -->
                <div class="profile-card-header">
                    <h3>
                        <i class="fas fa-user-circle"></i>
                        Personal Information
                    </h3>
                </div>

                <!-- Card Body -->
                <div class="profile-card-body">
                    <?php
                        // Get admin data from session or database
                        $admin = $_SESSION['admin'] ?? [];

                        // Profile image handling
                        $profilePath = '';
                        if (!empty($admin['userId'])) {
                            $possible = [
                                APP_ROOT . '/public/assets/images/admins/' . $admin['userId'] . '.jpg',
                                APP_ROOT . '/public/assets/images/admins/' . $admin['userId'] . '.jpeg',
                                APP_ROOT . '/public/assets/images/admins/' . $admin['userId'] . '.png'
                            ];
                            foreach ($possible as $p) {
                                if (file_exists($p)) {
                                    $profilePath = $p;
                                    break;
                                }
                            }
                        }
                        $profileUrl = !empty($profilePath)
                            ? BASE_URL . 'assets/images/admins/' . basename($profilePath)
                            : BASE_URL . 'assets/images/profileImg.jfif';
                    ?>

                        <!-- Profile Image Section -->
                        <div class="profile-image-section">
                            <div class="profile-avatar">
                                <img src="<?= htmlspecialchars($profileUrl) ?>" alt="Admin Profile Image" id="profilePreview">
                                <div class="profile-avatar-badge">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                            </div>
                            <div class="profile-image-info">
                                <h4>Administrator Profile Picture</h4>
                                <p>Upload a JPG or PNG image. Maximum file size is 2 MB. Recommended size: 400x400px.</p>
                                <span class="admin-badge">
                                    <i class="fas fa-crown"></i>
                                    Administrator Account
                                </span>
                            </div>
                        </div>

                        <!-- Info Box -->
                        <div class="info-box">
                            <div class="info-box-content">
                                <div class="info-box-icon">
                                    <i class="fas fa-info-circle"></i>
                                </div>
                                <div class="info-box-text">
                                    <h5>Account Security</h5>
                                    <p>Some fields like Admin ID and Username cannot be changed for security reasons. These fields are locked to maintain system integrity and accountability.</p>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="<?= BASE_URL ?>admin/profile" enctype="multipart/form-data">
                            <!-- Basic Info Grid -->
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-id-badge"></i>
                                        Admin ID
                                    </label>
                                    <input type="text" 
                                           class="form-input" 
                                           value="<?= htmlspecialchars($admin['userId'] ?? '') ?>" 
                                           disabled>
                                    <span class="disabled-badge">
                                        <i class="fas fa-lock"></i>
                                        Cannot be changed
                                    </span>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-user"></i>
                                        Username
                                    </label>
                                    <input type="text" 
                                           class="form-input" 
                                           value="<?= htmlspecialchars($admin['username'] ?? '') ?>" 
                                           disabled>
                                    <span class="disabled-badge">
                                        <i class="fas fa-lock"></i>
                                        Cannot be changed
                                    </span>
                                </div>
                            </div>

                            <!-- Profile Image Upload -->
                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <label for="profileImage" class="form-label">
                                        <i class="fas fa-image"></i>
                                        Change Profile Image
                                    </label>
                                    <input id="profileImage" 
                                           name="profileImage" 
                                           type="file" 
                                           class="form-file"
                                           accept="image/jpeg,image/png">
                                </div>
                            </div>

                            <!-- Name Fields -->
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="firstName" class="form-label">
                                        <i class="fas fa-user-circle"></i>
                                        First Name <span class="required-star">*</span>
                                    </label>
                                    <input id="firstName" 
                                           name="firstName" 
                                           type="text" 
                                           class="form-input"
                                           placeholder="Enter first name"
                                           required 
                                           value="<?= htmlspecialchars($admin['firstName'] ?? '') ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="lastName" class="form-label">
                                        <i class="fas fa-user-circle"></i>
                                        Last Name <span class="required-star">*</span>
                                    </label>
                                    <input id="lastName" 
                                           name="lastName" 
                                           type="text" 
                                           class="form-input"
                                           placeholder="Enter last name"
                                           required 
                                           value="<?= htmlspecialchars($admin['lastName'] ?? '') ?>">
                                </div>
                            </div>

                            <!-- Contact Info -->
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="emailId" class="form-label">
                                        <i class="fas fa-envelope"></i>
                                        Email Address <span class="required-star">*</span>
                                    </label>
                                    <input id="emailId" 
                                           name="emailId" 
                                           type="email" 
                                           class="form-input"
                                           placeholder="admin@library.com"
                                           required 
                                           value="<?= htmlspecialchars($admin['emailId'] ?? '') ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="phoneNumber" class="form-label">
                                        <i class="fas fa-phone"></i>
                                        Phone Number <span class="required-star">*</span>
                                    </label>
                                    <input id="phoneNumber" 
                                           name="phoneNumber" 
                                           type="tel" 
                                           class="form-input"
                                           placeholder="+94 XXX XXX XXX"
                                           required 
                                           value="<?= htmlspecialchars($admin['phoneNumber'] ?? '') ?>">
                                </div>
                            </div>

                            <!-- Personal Info -->
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="gender" class="form-label">
                                        <i class="fas fa-venus-mars"></i>
                                        Gender <span class="required-star">*</span>
                                    </label>
                                    <select id="gender" name="gender" class="form-select" required>
                                        <option value="">Select Gender</option>
                                        <option value="Male" <?= (($admin['gender'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
                                        <option value="Female" <?= (($admin['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
                                        <option value="Other" <?= (($admin['gender'] ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="dob" class="form-label">
                                        <i class="fas fa-calendar-alt"></i>
                                        Date of Birth <span class="required-star">*</span>
                                    </label>
                                    <input id="dob" 
                                           name="dob" 
                                           type="date" 
                                           class="form-input"
                                           required 
                                           value="<?= htmlspecialchars($admin['dob'] ?? '') ?>">
                                </div>
                            </div>

                            <!-- Admin Specific Fields -->
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="department" class="form-label">
                                        <i class="fas fa-building"></i>
                                        Department <span class="required-star">*</span>
                                    </label>
                                    <input id="department" 
                                           name="department" 
                                           type="text" 
                                           class="form-input"
                                           placeholder="e.g., Library Management"
                                           required 
                                           value="<?= htmlspecialchars($admin['department'] ?? '') ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label for="position" class="form-label">
                                        <i class="fas fa-briefcase"></i>
                                        Position <span class="required-star">*</span>
                                    </label>
                                    <input id="position" 
                                           name="position" 
                                           type="text" 
                                           class="form-input"
                                           placeholder="e.g., Senior Librarian"
                                           required 
                                           value="<?= htmlspecialchars($admin['position'] ?? '') ?>">
                                </div>
                            </div>

                            <!-- Address -->
                            <div class="form-grid">
                                <div class="form-group full-width">
                                    <label for="address" class="form-label">
                                        <i class="fas fa-map-marker-alt"></i>
                                        Address <span class="required-star">*</span>
                                    </label>
                                    <textarea id="address" 
                                              name="address" 
                                              class="form-textarea"
                                              placeholder="Enter your complete address"
                                              required><?= htmlspecialchars($admin['address'] ?? '') ?></textarea>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="form-actions">
                                <a href="<?= BASE_URL ?>admin/dashboard" class="btn btn-cancel">
                                    <i class="fas fa-times"></i>
                                    Cancel
                                </a>
                                <button type="submit" class="btn btn-submit">
                                    <i class="fas fa-save"></i>
                                    Save Changes
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php include APP_ROOT . '/views/layouts/admin-footer.php'; ?>
        </main>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('collapsed');
        }

        // Image preview functionality
        document.getElementById('profileImage').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                // Validate file size (2MB max)
                if (file.size > 2 * 1024 * 1024) {
                    alert('File size must be less than 2 MB');
                    this.value = '';
                    return;
                }

                // Validate file type
                if (!file.type.match('image/jpeg') && !file.type.match('image/png')) {
                    alert('Only JPG and PNG images are allowed');
                    this.value = '';
                    return;
                }

                const reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('profilePreview').src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const phoneNumber = document.getElementById('phoneNumber').value;
            const phoneRegex = /^[+]?[\d\s-()]+$/;

            if (!phoneRegex.test(phoneNumber)) {
                e.preventDefault();
                alert('Please enter a valid phone number');
                return false;
            }

            return true;
        });
    </script>
</body>
</html>
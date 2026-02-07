<?php
$pageTitle = 'Your Profile';
include APP_ROOT . '/views/layouts/header.php';
?>

<style>
    .profile-container {
        padding: 2rem 0;
        animation: fadeIn 0.6s ease-out;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    /* Profile Header */
    .profile-header {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.1), rgba(118, 75, 162, 0.1));
        border-radius: 20px;
        padding: 2rem;
        margin-bottom: 2rem;
        text-align: center;
        animation: slideInDown 0.6s ease-out;
    }
    
    @keyframes slideInDown {
        from {
            opacity: 0;
            transform: translateY(-20px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .profile-header h1 {
        font-size: clamp(1.75rem, 3vw, 2.5rem);
        font-weight: 800;
        color: #1f2937;
        margin-bottom: 0.5rem;
    }
    
    .profile-header p {
        color: #6b7280;
        font-size: 1.05rem;
        margin: 0;
    }
    
    /* Profile Card */
    .profile-card {
        background: white;
        border-radius: 24px;
        box-shadow: 0 20px 60px rgba(0, 0, 0, 0.1);
        overflow: hidden;
        animation: slideInUp 0.6s ease-out 0.2s both;
    }
    
    @keyframes slideInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .profile-card-header {
        background: linear-gradient(135deg, rgba(102, 126, 234, 0.05), rgba(118, 75, 162, 0.05));
        padding: 2rem;
        border-bottom: 2px solid #f3f4f6;
        display: flex;
        align-items: center;
        gap: 1rem;
    }
    
    .profile-card-header i {
        width: 48px;
        height: 48px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border-radius: 14px;
        font-size: 1.5rem;
    }
    
    .profile-card-header h3 {
        font-size: 1.75rem;
        font-weight: 800;
        color: #1f2937;
        margin: 0;
    }
    
    .profile-card-body {
        padding: 2.5rem;
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
        color: #374151;
        margin-bottom: 0.5rem;
        font-size: 0.95rem;
    }
    
    .form-label i {
        color: #667eea;
        font-size: 0.9rem;
    }
    
    .required-star {
        color: #ef4444;
    }
    
    .form-input,
    .form-select,
    .form-textarea,
    .form-file {
        width: 100%;
        padding: 0.875rem 1rem;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 1rem;
        transition: all 0.3s ease;
        background: #f9fafb;
    }
    
    .form-input:disabled {
        background: #f3f4f6;
        color: #9ca3af;
        cursor: not-allowed;
    }
    
    .form-input:focus,
    .form-select:focus,
    .form-textarea:focus,
    .form-file:focus {
        outline: none;
        border-color: #667eea;
        background: white;
        box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
    }
    
    .form-select {
        cursor: pointer;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%239ca3af' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
        background-repeat: no-repeat;
        background-position: right 1rem center;
        padding-right: 3rem;
    }
    
    .form-textarea {
        resize: vertical;
        min-height: 120px;
        font-family: inherit;
    }
    
    .form-file {
        padding: 0.75rem;
        cursor: pointer;
    }
    
    .form-file::-webkit-file-upload-button {
        padding: 0.5rem 1rem;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        border: none;
        border-radius: 8px;
        font-weight: 600;
        cursor: pointer;
        transition: all 0.3s ease;
        margin-right: 1rem;
    }
    
    .form-file::-webkit-file-upload-button:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    
    /* Disabled Badge */
    .disabled-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        padding: 0.5rem 1rem;
        background: rgba(156, 163, 175, 0.1);
        color: #6b7280;
        border-radius: 8px;
        font-size: 0.85rem;
        font-weight: 600;
        margin-top: 0.5rem;
    }
    
    /* Action Buttons */
    .form-actions {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 2rem;
        border-top: 2px solid #f3f4f6;
    }
    
    .btn-submit {
        flex: 1;
        padding: 1rem 2rem;
        border: none;
        border-radius: 12px;
        font-size: 1.05rem;
        font-weight: 700;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        cursor: pointer;
        transition: all 0.3s ease;
        box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    
    .btn-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 15px 35px rgba(102, 126, 234, 0.4);
    }
    
    .btn-submit:active {
        transform: translateY(0);
    }
    
    .btn-cancel {
        padding: 1rem 2rem;
        border: 2px solid #e5e7eb;
        border-radius: 12px;
        font-size: 1.05rem;
        font-weight: 700;
        background: white;
        color: #6b7280;
        cursor: pointer;
        transition: all 0.3s ease;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    
    .btn-cancel:hover {
        border-color: #667eea;
        color: #667eea;
        background: rgba(102, 126, 234, 0.05);
    }
    
    /* Info Box */
    .info-box {
        padding: 1.25rem;
        background: linear-gradient(135deg, rgba(59, 130, 246, 0.05), rgba(6, 182, 212, 0.05));
        border-left: 4px solid #3b82f6;
        border-radius: 12px;
        margin-bottom: 2rem;
    }
    
    .info-box-content {
        display: flex;
        gap: 1rem;
        align-items: start;
    }
    
    .info-box-icon {
        width: 40px;
        height: 40px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: rgba(59, 130, 246, 0.1);
        color: #3b82f6;
        border-radius: 10px;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    
    .info-box-text h5 {
        font-size: 1.1rem;
        font-weight: 700;
        color: #1f2937;
        margin-bottom: 0.25rem;
    }
    
    .info-box-text p {
        color: #6b7280;
        margin: 0;
        font-size: 0.95rem;
        line-height: 1.5;
    }
    
    /* Responsive Design */
    @media (max-width: 768px) {
        .profile-card-body {
            padding: 1.5rem;
        }
        
        .profile-image-section {
            flex-direction: column;
            text-align: center;
            padding: 1.5rem;
        }
        
        .form-grid {
            grid-template-columns: 1fr;
        }
        
        .form-actions {
            flex-direction: column;
        }
        
        .btn-submit,
        .btn-cancel {
            width: 100%;
        }
    }
    
    @media (max-width: 576px) {
        .profile-header {
            padding: 1.5rem;
        }
        
        .profile-card-header {
            padding: 1.5rem;
        }
        
        .profile-avatar img {
            width: 100px;
            height: 100px;
        }
    }
</style>

<div class="profile-container">
    <div class="container">
        <!-- Profile Header -->
        <div class="profile-header">
            <h1>Your Profile</h1>
            <p>Manage your account information and preferences</p>
        </div>

        <div class="row justify-content-center">
            <div class="col-lg-9">
                <div class="profile-card">
                    <div class="profile-card-header">
                        <i class="fas fa-user-circle"></i>
                        <h3>Profile Information</h3>
                    </div>
                    
                    <div class="profile-card-body">
                        <?php
                            $userIdSafe = htmlspecialchars($user['userId'] ?? '');
                            $uploadsDir = APP_ROOT . '/public/assets/images/users/';
                            $possible = [
                                $uploadsDir . ($user['userId'] ?? '') . '.jpg',
                                $uploadsDir . ($user['userId'] ?? '') . '.jpeg',
                                $uploadsDir . ($user['userId'] ?? '') . '.png'
                            ];
                            $profilePath = '';
                            // Prefer DB path if set and file exists
                            if (!empty($user['profileImage'])) {
                                $candidate = APP_ROOT . '/public/' . ltrim($user['profileImage'], '/');
                                if (file_exists($candidate)) { $profilePath = $candidate; }
                            }
                            if (empty($profilePath)) {
                                foreach ($possible as $p) { 
                                    if ($user && !empty($user['userId']) && file_exists($p)) { 
                                        $profilePath = $p; 
                                        break; 
                                    } 
                                }
                            }
                            $profileUrl = !empty($profilePath)
                                ? BASE_URL . 'assets/images/users/' . basename($profilePath)
                                : BASE_URL . 'assets/images/profileImg.jfif';
                        ?>

                        <!-- Profile Image Section -->
                        <div class="profile-image-section">
                            <div class="profile-avatar">
                                <img src="<?= htmlspecialchars($profileUrl) ?>" alt="Profile Image" id="profilePreview">
                                <div class="profile-avatar-badge">
                                    <i class="fas fa-camera"></i>
                                </div>
                            </div>
                            <div class="profile-image-info">
                                <h4>Profile Picture</h4>
                                <p>Upload a JPG or PNG image. Maximum file size is 2 MB. Recommended size: 400x400px.</p>
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
                                    <p>Some fields like Student ID and Username cannot be changed for security reasons. Contact library administration if you need to update these details.</p>
                                </div>
                            </div>
                        </div>

                        <form method="POST" action="<?= BASE_URL ?>faculty/profile" enctype="multipart/form-data">
                            <input type="hidden" name="update_profile" value="1">
                            
                            <!-- Basic Info Grid -->
                            <div class="form-grid">
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-id-card"></i>
                                        Faculty ID
                                    </label>
                                    <input type="text" 
                                           class="form-input" 
                                           value="<?= htmlspecialchars($user['userId'] ?? '') ?>" 
                                           disabled>
                                    <span class="disabled-badge">
                                        <i class="fas fa-lock"></i>
                                        Cannot be changed
                                    </span>
                                </div>
                                
                                <div class="form-group">
                                    <label class="form-label">
                                        <i class="fas fa-user"></i>
                                        Name <span class="required-star">*</span>
                                    </label>
                                    <input id="name"
                                           name="name" 
                                           type="text" 
                                           class="form-input"
                                           placeholder="Enter your full name"
                                           required 
                                           value="<?= htmlspecialchars($user['username'] ?? '') ?>">
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
                                           accept="image/jpeg,image/png,image/jpg">
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
                                           name="email" 
                                           type="email" 
                                           class="form-input"
                                           placeholder="your.email@example.com"
                                           required 
                                           value="<?= htmlspecialchars($user['emailId'] ?? '') ?>">
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
                                           value="<?= htmlspecialchars($user['phoneNumber'] ?? '') ?>">
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
                                        <option value="Male" <?= (($user['gender'] ?? '') === 'Male') ? 'selected' : '' ?>>Male</option>
                                        <option value="Female" <?= (($user['gender'] ?? '') === 'Female') ? 'selected' : '' ?>>Female</option>
                                        <option value="Other" <?= (($user['gender'] ?? '') === 'Other') ? 'selected' : '' ?>>Other</option>
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
                                           value="<?= htmlspecialchars($user['dob'] ?? '') ?>">
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
                                              required><?= htmlspecialchars($user['address'] ?? '') ?></textarea>
                                </div>
                            </div>

                            <!-- Action Buttons -->
                            <div class="form-actions">
                                <a href="<?= BASE_URL ?>user/dashboard" class="btn-cancel">
                                    <i class="fas fa-times"></i>
                                    Cancel
                                </a>
                                <button type="submit" class="btn-submit">
                                    <i class="fas fa-save"></i>
                                    Save Changes
                                </button>
                            </div>
                        </form>
                        
                        <!-- Change Password Section -->
                        <div style="margin-top: 2rem; padding-top: 2rem; border-top: 2px solid #f3f4f6;">
                            <div class="info-box" style="border-left-color: #f59e0b; background: linear-gradient(135deg, rgba(245, 158, 11, 0.05), rgba(234, 88, 12, 0.05));">
                                <div class="info-box-content">
                                    <div class="info-box-icon" style="background: rgba(245, 158, 11, 0.1); color: #f59e0b;">
                                        <i class="fas fa-shield-alt"></i>
                                    </div>
                                    <div class="info-box-text">
                                        <h5>Password & Security</h5>
                                        <p>Regularly changing your password helps keep your account secure. We recommend updating it every 90 days.</p>
                                    </div>
                                </div>
                            </div>
                            <a href="<?= BASE_URL ?>user/change-password" 
                               style="display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.875rem 1.75rem; 
                                      background: linear-gradient(135deg, #f59e0b 0%, #ea580c 100%); color: white; 
                                      border-radius: 12px; text-decoration: none; font-weight: 700; font-size: 1rem;
                                      transition: all 0.3s ease; box-shadow: 0 8px 20px rgba(245, 158, 11, 0.25);"
                               onmouseover="this.style.transform='translateY(-2px)'; this.style.boxShadow='0 12px 28px rgba(245, 158, 11, 0.35)';"
                               onmouseout="this.style.transform='translateY(0)'; this.style.boxShadow='0 8px 20px rgba(245, 158, 11, 0.25)';">
                                <i class="fas fa-key"></i>
                                Change Password
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="<?= BASE_URL ?>assets/js/form-validation.js"></script>
<script>
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
    let isValid = true;
    
    // Clear errors
    this.querySelectorAll('.is-invalid').forEach(el => {
        el.classList.remove('is-invalid');
        el.style.borderColor = '';
    });
    this.querySelectorAll('.error-message').forEach(el => el.remove());
    
    const emailId = document.getElementById('emailId');
    const phoneNumber = document.getElementById('phoneNumber');
    const dob = document.getElementById('dob');
    const address = document.getElementById('address');
    const gender = document.getElementById('gender');
    
    // Validate email
    if (!emailId.value || !validateEmail(emailId.value)) {
        showError(emailId, 'Please enter a valid email address');
        isValid = false;
    }
    
    // Validate phone
    if (!phoneNumber.value || !validatePhone(phoneNumber.value)) {
        showError(phoneNumber, 'Please enter a valid phone number');
        isValid = false;
    }
    
    // Validate DOB
    if (dob.value && !validateDOB(dob.value)) {
        showError(dob, 'You must be at least 13 years old');
        isValid = false;
    }
    
    // Validate address
    if (!address.value.trim() || address.value.trim().length < 10) {
        showError(address, 'Address must be at least 10 characters');
        isValid = false;
    }
    
    // Validate gender
    if (!gender.value) {
        showError(gender, 'Please select your gender');
        isValid = false;
    }
    
    if (!isValid) {
        e.preventDefault();
        const firstError = this.querySelector('.is-invalid');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
            firstError.focus();
        }
    }
});

// Real-time validation
document.getElementById('emailId').addEventListener('blur', function() {
    if (this.value && !validateEmail(this.value)) {
        showError(this, 'Please enter a valid email address');
    } else {
        clearError(this);
    }
});

document.getElementById('phoneNumber').addEventListener('blur', function() {
    if (this.value && !validatePhone(this.value)) {
        showError(this, 'Please enter a valid phone number');
    } else {
        clearError(this);
    }
});

document.getElementById('dob').addEventListener('blur', function() {
    if (this.value && !validateDOB(this.value)) {
        showError(this, 'You must be at least 13 years old');
    } else {
        clearError(this);
    }
});

document.getElementById('address').addEventListener('blur', function() {
    if (this.value && this.value.trim().length < 10) {
        showError(this, 'Address must be at least 10 characters');
    } else {
        clearError(this);
    }
});
</script>

<?php include APP_ROOT . '/views/layouts/footer.php'; ?>